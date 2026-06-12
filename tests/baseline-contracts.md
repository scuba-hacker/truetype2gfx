# Baseline Server Contracts — truetype2gfx

> **Purpose**: This document is a byte-for-byte snapshot of the server contracts, DOM
> hooks, and persistence semantics produced by the **original** `index.php` (and consumed
> by `image.php`) *before* the UI redesign. It is the parity reference for tasks 10.1 and
> 10.2 (contract-stability verification).
>
> **Captured from**: `index.php` (869 lines) and `image.php`, original/pre-redesign state.
>
> Validates against Requirements **13.1, 13.2, 13.3, 14.1**.
>
> ⚠️ Nothing in this file should change. After the redesign, the live UI must reproduce
> these exact strings, field names, IDs, and keys.

---

## 1. `image.php` Preview Request (GET) — built by `updateImage()`

### 1.1 Exact source expression

The `updateImage()` function sets `document.getElementById("image").src` to the following
concatenation (reproduced exactly from `index.php`):

```js
document.getElementById("image").src =
  "image.php?device=" + encodeURIComponent(device())
  + "&font=" + encodeURIComponent(font())
  + "&size=" + encodeURIComponent(document.getElementById("sizefield").value)
  + "&text=" + encodeURIComponent(previewText())
  + "&background=" + encodeURIComponent(background())
  + "&foreground=" + encodeURIComponent(foreground())
  + "&vertical=" + encodeURIComponent(vertical())
  + "&horizontal=" + encodeURIComponent(horizontal())
  + "&word_wrap=" + encodeURIComponent(wordWrap())
  + "&pixelate=" + encodeURIComponent(pixelate())
  + "&output_pixelate=" + encodeURIComponent(outputPixelate())
  + "&output_width=<?php echo $output_width; ?>"
  + "&output_height=<?php echo $output_height; ?>"
  + "&rotation=" + encodeURIComponent(rotation())
  + "#" + new Date().getTime();
```

### 1.2 Query parameter order and source (MUST be preserved exactly)

| # | Param | Source value | Encoding |
|---|-------|--------------|----------|
| 1 | `device` | `device()` → `#device`.value | `encodeURIComponent` |
| 2 | `font` | `font()` → checked radio `name="font"`.value (or `""`) | `encodeURIComponent` |
| 3 | `size` | `#sizefield`.value (raw, not parsed) | `encodeURIComponent` |
| 4 | `text` | `previewText()` (see §1.3) | `encodeURIComponent` |
| 5 | `background` | `background()` → `#background`.value | `encodeURIComponent` |
| 6 | `foreground` | `foreground()` → `#foreground`.value | `encodeURIComponent` |
| 7 | `vertical` | `vertical()` → `checkedValue("vertical")` (default `"centre"`) | `encodeURIComponent` |
| 8 | `horizontal` | `horizontal()` → `checkedValue("horizontal")` (default `"centre"`) | `encodeURIComponent` |
| 9 | `word_wrap` | `wordWrap()` → `#word-wrap`.checked ? `"on"` : `"off"` | `encodeURIComponent` |
| 10 | `pixelate` | `pixelate()` → `#pixelate`.value | `encodeURIComponent` |
| 11 | `output_pixelate` | `outputPixelate()` → `#output-pixelate`.checked ? `"on"` : `"off"` | `encodeURIComponent` |
| 12 | `output_width` | **PHP-rendered literal** `<?php echo $output_width; ?>` (NOT encoded, baked at render) | none |
| 13 | `output_height` | **PHP-rendered literal** `<?php echo $output_height; ?>` (NOT encoded, baked at render) | none |
| 14 | `rotation` | `rotation()` → `#rotation`.value | `encodeURIComponent` |

- A cache-busting **fragment** `#<timestamp>` is appended via `"#" + new Date().getTime()`.
  It uses `#` (a fragment, not a `&` query param) and is NOT URL-encoded.
- `output_width` / `output_height` are server-computed at page render time and embedded as
  literal numbers. They are **not** read from the DOM by JS. (Param names use underscores.)
- Note the param names use **underscores** (`word_wrap`, `output_pixelate`, `output_width`,
  `output_height`), matching the `$_GET[...]` keys read by `image.php`.

### 1.3 `previewText()` transformation (applied before encoding param 4)

```js
function previewText() {
  return document.getElementById("textfield").value
    .replace(/\r\n/g, "\\n")
    .replace(/\r/g, "\\n")
    .replace(/\n/g, "\\n");
}
```

All real line breaks (`\r\n`, `\r`, `\n`) are converted to the literal two-character
sequence `\n` **before** `encodeURIComponent`. Typed literal `\n` sequences are left as-is.

### 1.4 GET parameters consumed by `image.php` (for cross-reference)

`image.php` reads exactly these `$_GET` keys: `device`, `background`, `foreground`,
`vertical`, `horizontal`, `word_wrap`, `pixelate`, `output_pixelate`, `output_width`,
`output_height`, `rotation`, `dpi`, `text`, `size`, `font`.

- `word_wrap`/`pixelate`/`output_pixelate` are truthy only on the exact string `"on"`.
- `rotation` is validated against `{"0","90","180","270"}`.
- `text` is `htmlspecialchars`-escaped and truncated to 100 chars.
- `size` valid range `3..200`; `dpi` valid range `>0..300` (default 141). `dpi` is **not**
  sent by `updateImage()`, so the server default applies.
- `font` is checked against a server-side allow-list.

---

## 2. POST Bodies

### 2.1 `set-device` (XHR) — built by `updateDevice()`

```js
var request = new XMLHttpRequest();
request.open("POST", "index.php", true);
request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
request.send("set-device=1&device=" + encodeURIComponent(device()));
```

Exact body (parameter order preserved):

```
set-device=1&device=<encodeURIComponent(device())>
```

- Method: `POST` to `index.php`.
- Content-Type: `application/x-www-form-urlencoded`.
- On `onload`: `window.location.reload()`.
- Server reads `$_POST["set-device"]` and `$_POST["device"]`; persists to session and to
  `fonts/user/.selected-device`, then `exit()`.

### 2.2 `set-preview-settings` (XHR) — built by `savePreviewSettings(reload)`

```js
request.send(
  "set-preview-settings=1&background=" + encodeURIComponent(background())
  + "&foreground=" + encodeURIComponent(foreground())
  + "&vertical=" + encodeURIComponent(vertical())
  + "&horizontal=" + encodeURIComponent(horizontal())
  + "&size_mode=" + encodeURIComponent(sizeMode())
  + "&display_scale=" + encodeURIComponent(displayScale())
  + "&custom_scale=" + encodeURIComponent(customScale())
  + "&word_wrap=" + encodeURIComponent(wordWrap())
  + "&pixelate=" + encodeURIComponent(pixelate())
  + "&output_pixelate=" + encodeURIComponent(outputPixelate())
  + "&rotation=" + encodeURIComponent(rotation())
);
```

Exact body (parameter order preserved):

```
set-preview-settings=1&background=<bg>&foreground=<fg>&vertical=<v>&horizontal=<h>&size_mode=<sm>&display_scale=<ds>&custom_scale=<cs>&word_wrap=<ww>&pixelate=<px>&output_pixelate=<op>&rotation=<rot>
```

| Param | Source | Notes |
|-------|--------|-------|
| `set-preview-settings` | literal `1` | |
| `background` | `#background`.value | |
| `foreground` | `#foreground`.value | |
| `vertical` | `checkedValue("vertical")` | default `"centre"` |
| `horizontal` | `checkedValue("horizontal")` | default `"centre"` |
| `size_mode` | `#size-mode`.value | **underscore** in param name; **hyphen** in DOM id |
| `display_scale` | `#display-scale`.value | **underscore** in param; **hyphen** in id |
| `custom_scale` | `customScale()` (clamped 50..400, NaN→100, writes back to field) | **underscore** in param; **hyphen** in id |
| `word_wrap` | `#word-wrap`.checked ? `"on"` : `"off"` | |
| `pixelate` | `#pixelate`.value | |
| `output_pixelate` | `#output-pixelate`.checked ? `"on"` : `"off"` | |
| `rotation` | `#rotation`.value | |

- Method/headers identical to §2.1 (`POST index.php`, urlencoded).
- `savePreviewSettings(true)` calls `saveFormState()` then on `onload` does
  `window.location.reload()`. `savePreviewSettings(false)` calls `updateImage()` on `onload`.
- Callers: `updatePreviewSettings()` → `savePreviewSettings(false)`; `size-mode`,
  `display-scale`, `custom-scale` changes → `savePreviewSettings(true)`;
  `togglePixelate()` and `rotateDevice()` → `savePreviewSettings(true)`.
- `customScale()` has a **side effect**: it clamps the value (NaN→100, >400→400, <50→50)
  and writes the clamped value back to `#custom-scale.value` before returning it.
- Server reads `$_POST["set-preview-settings"]`, then `background`, `foreground`,
  `vertical`, `horizontal`, `size_mode`, `display_scale`, `custom_scale`, `word_wrap`,
  `pixelate`, `output_pixelate`, `rotation`; persists to `fonts/user/.preview-settings.json`,
  then `exit()`.

### 2.3 `get-font`, `submit-file`, `delete-font` — native HTML form submits

These three are **not** XHR. They are submitted by the single enclosing form:

```html
<form action="" method="post" enctype="multipart/form-data">
```

- `action=""` (posts to the same URL, `index.php`), `method="post"`,
  `enctype="multipart/form-data"`.
- The form **wraps both** the Font Library column (`<td id="second">`) and the Controls
  column (`<td id="third">`). Therefore a native submit serializes **every named control
  inside the form**, in DOM order.

**Named controls inside the form (DOM order), i.e. the fields present in any native submit body:**

| Order | `name` | Element | id |
|-------|--------|---------|----|
| 1 | `font` | radio group (12 FreeFonts + N user fonts) | — |
| 2 | `font-to-delete` | hidden | `font-to-delete` |
| 3 | `delete-font` | submit button (one per user-font row) | — |
| 4 | `submit-file` | submit | — |
| 5 | `fileToUpload` | file | `fileToUpload` |
| 6 | `get-font` | submit | `get-font` |
| 7 | `device` | select | `device` |
| 8 | `size-mode` | select | `size-mode` |
| 9 | `display-scale` | select | `display-scale` |
| 10 | `custom-scale` | text | `custom-scale` |
| 11 | `size` | text | `sizefield` |
| 12 | `background` | select | `background` |
| 13 | `foreground` | select | `foreground` |
| 14 | `text` | textarea | `textfield` |
| 15 | `vertical` | radio group | — |
| 16 | `horizontal` | radio group | — |

> Only the activated submit button's `name=value` is included (browsers submit only the
> clicked submit control). The non-submit named fields above are always included.

**Controls that have NO `name` (so are NEVER in any POST body)** — id-only, JS-driven:
`rotate-button`, `rotation`, `word-wrap`, `pixelate-button`, `pixelate`, `output-pixelate`.

#### 2.3.1 `get-font` (download)
- Triggered by: `<input type="submit" id="get-font" value="Get GFX font file" name="get-font">`.
- Server reads only: `$_POST["get-font"]`, `$_POST['size']` (validated `3..200`),
  `$_POST['font']`. Runs `fontconvert`, streams the `.h` as an attachment download.
- Relevant submitted pairs: `get-font=Get GFX font file`, `font=<value>`, `size=<value>`.
  (Other named fields in §2.3 are also serialized but ignored by this endpoint.)

#### 2.3.2 `submit-file` (upload)
- Triggered by: `<input type="submit" value="Upload" name="submit-file" onClick="return validateUpload();">`.
- Client validation `validateUpload()` requires the file value to match `/(.*?)\.(ttf|TTF)$/`;
  otherwise an `alert()` fires and submission is blocked (`return false`).
- Server reads `$_POST["submit-file"]` and `$_FILES["fileToUpload"]`. Accepts only files whose
  lowercased last 4 chars equal `.ttf`; moves into `fonts/user/`; tracks up to 5 in session
  (`array_shift` when exceeding 5). Sets `$select_font = "user/<filename>"`.
- Relevant submitted parts: `submit-file=Upload` and the multipart file part
  `fileToUpload` (filename + binary).

#### 2.3.3 `delete-font`
- Triggered per user-font row by:
  `<button type="submit" name="delete-font" value="1" onClick="return deleteFont(<json font name>);">Delete</button>`.
- `deleteFont(fontName)`: sets `#font-to-delete`.value = fontName; if
  `localStorage["truetype2gfx.font"] == "user/" + fontName` it removes that key; returns
  `confirm("Delete " + fontName + "?")`. If the user cancels, the submit is aborted (no request).
- Server reads `$_POST["delete-font"]` and `$_POST["font-to-delete"]`. Validates name against
  `/^[a-zA-Z0-9 ._-]+\.ttf$/i`, unlinks `fonts/user/<name>`, removes from session, then
  `header("Location: ...")` redirect.
- Relevant submitted pairs: `delete-font=1`, `font-to-delete=<font name>`.

---

## 3. `localStorage` Keys (persistence) — Requirement 14.1

Exactly three keys, read/written by `saveFormState()` / `restoreFormState()`:

| Key | Written from | Restored to | Notes |
|-----|--------------|-------------|-------|
| `truetype2gfx.font` | `font()` (checked `name="font"` value) | matching `name="font"` radio → `.checked = true` | Removed by `deleteFont()` if it equals `"user/" + deletedName` |
| `truetype2gfx.size` | `#sizefield`.value | `#sizefield`.value | Restored only if stored value `!== null` |
| `truetype2gfx.text` | `#textfield`.value | `#textfield`.value | Restored only if stored value `!== null` |

Semantics to preserve exactly:

```js
function saveFormState() {
  if (!window.localStorage) return;
  localStorage.setItem("truetype2gfx.font", font());
  localStorage.setItem("truetype2gfx.size", document.getElementById("sizefield").value);
  localStorage.setItem("truetype2gfx.text", document.getElementById("textfield").value);
}

function restoreFormState() {
  if (!window.localStorage) return;
  var storedSize = localStorage.getItem("truetype2gfx.size");
  var storedText = localStorage.getItem("truetype2gfx.text");
  var storedFont = localStorage.getItem("truetype2gfx.font");
  if (storedSize !== null) document.getElementById("sizefield").value = storedSize;
  if (storedText !== null) document.getElementById("textfield").value = storedText;
  if (storedFont !== null) { /* set matching name="font" radio checked */ }
}
```

- `saveFormState()` is called at the start of `updateImage()` and `savePreviewSettings()`.
- On page load, `<body onload='setFont()'>` runs `setFont()`, which calls
  `restoreFormState()`, then selects the `$select_font` (uploaded font) radio if present,
  then calls `updateImage()`.
- `localStorage` guard: every access is gated by `if (!window.localStorage) return;` /
  `if (window.localStorage && ...)`.

---

## 4. DOM Hook Inventory — Requirement 13.3

These IDs and names MUST continue to exist with unchanged semantics.

### 4.1 Element IDs read/used by JavaScript (`getElementById`)

| id | Read by | Type |
|----|---------|------|
| `image` | `updateImage()` (sets `.src`) | `<img>` (preview, `src="image.php"`) |
| `sizefield` | `updateImage()`, `saveFormState()`, `restoreFormState()` | text input (`name="size"`) |
| `textfield` | `previewText()`, `saveFormState()`, `restoreFormState()` | textarea (`name="text"`) |
| `device` | `device()` | select (`name="device"`) |
| `background` | `background()` | select (`name="background"`) |
| `foreground` | `foreground()` | select (`name="foreground"`) |
| `size-mode` | `sizeMode()` | select (`name="size-mode"`) |
| `display-scale` | `displayScale()` | select (`name="display-scale"`) |
| `custom-scale` | `customScale()` (reads + writes back clamped) | text input (`name="custom-scale"`) |
| `word-wrap` | `wordWrap()` (`.checked`) | checkbox (no `name`) |
| `output-pixelate` | `outputPixelate()` (`.checked`) | checkbox (no `name`) |
| `pixelate` | `pixelate()`, `togglePixelate()` (reads + writes `.value`) | hidden (no `name`) |
| `rotation` | `rotation()`, `rotateDevice()` (reads + writes `.value`) | hidden (no `name`) |
| `font-to-delete` | `deleteFont()` (writes `.value`) | hidden (`name="font-to-delete"`) |
| `fileToUpload` | `validateUpload()` (reads `.value`) | file input (`name="fileToUpload"`) |

### 4.2 IDs referenced by inline handlers / CSS (structural hooks)

| id | Role |
|----|------|
| `device-frame` | Preview frame container (CSS-positioned) |
| `device-background` | `<img>` device mockup, `src` = device image |
| `get-font` | Primary download submit (styled by id) |
| `rotate-button` | `onClick="rotateDevice()"`; label `"Rotate <rot> deg"` |
| `pixelate-button` | `onClick="togglePixelate()"`; label toggles `"Smooth preview"`/`"Pixelate preview"` |

### 4.3 Names read by JavaScript (`getElementsByName`)

| name | Read by | Notes |
|------|---------|-------|
| `font` | `font()`, `setFont()`, `restoreFormState()` | radio group; values `FreeSans.ttf` … `FreeMonoOblique.ttf` and `user/<name>.ttf` |
| `vertical` | `checkedValue("vertical")` via `vertical()` | radio group `top`/`centre`/`bottom`, default `"centre"` |
| `horizontal` | `checkedValue("horizontal")` via `horizontal()` | radio group `left`/`centre`/`right`, default `"centre"` |

### 4.4 Built-in `font` radio values (must be preserved verbatim)

```
FreeSans.ttf, FreeSansBold.ttf, FreeSansBoldOblique.ttf, FreeSansOblique.ttf,
FreeSerif.ttf, FreeSerifBold.ttf, FreeSerifBoldItalic.ttf, FreeSerifItalic.ttf,
FreeMono.ttf, FreeMonoBold.ttf, FreeMonoBoldOblique.ttf, FreeMonoOblique.ttf
```

First radio (`FreeSans.ttf`) is `checked` by default. User-font values have the form
`user/<name>.ttf` (label = name with `.ttf`/`.TTF` stripped).

### 4.5 Inline event-handler bindings (functional parity map)

| Control | Event | Handler |
|---------|-------|---------|
| `name="font"` radios | `onChange` | `updateImage()` |
| `#sizefield` | `onInput` | `updateImage()` |
| `#textfield` | `onInput` | `updateImage()` |
| `#device` | `onChange` | `updateDevice()` |
| `#size-mode` | `onChange` | `savePreviewSettings(true)` |
| `#display-scale` | `onChange` | `savePreviewSettings(true)` |
| `#custom-scale` | `onChange` | `savePreviewSettings(true)` |
| `#background` | `onChange` | `updatePreviewSettings()` |
| `#foreground` | `onChange` | `updatePreviewSettings()` |
| `#word-wrap` | `onChange` | `updatePreviewSettings()` |
| `#output-pixelate` | `onChange` | `updatePreviewSettings()` |
| `name="vertical"` radios | `onChange` | `updatePreviewSettings()` |
| `name="horizontal"` radios | `onChange` | `updatePreviewSettings()` |
| `#rotate-button` | `onClick` | `rotateDevice()` |
| `#pixelate-button` | `onClick` | `togglePixelate()` |
| `delete-font` button | `onClick` | `deleteFont(<name>)` (returns confirm result) |
| `submit-file` button | `onClick` | `validateUpload()` (returns false to block) |
| `<body>` | `onload` | `setFont()` |

---

## 5. Conditional Enablement Rules (PHP-driven, for reference)

The following `disabled` attributes are rendered server-side and must be reproduced:

- `size-mode` option `physical`: `disabled` when `$selected_device != "m5stickcplus"`.
- `display-scale` select: `disabled` unless device == `m5stickcplus` **and** size mode == `physical`.
- `custom-scale` input: `disabled` unless device == `m5stickcplus` **and** size mode == `physical`
  **and** display scale == `custom`.
- If device != `m5stickcplus` while size mode == `physical`, the server coerces size mode to `half`.

---

## 6. Rotation cycle (reference)

`rotateDevice()` advances `#rotation`.value through the cycle
`["0", "90", "180", "270"]` (wraps with modulo) then calls `savePreviewSettings(true)`.
Valid rotation set accepted by both `index.php` and `image.php`: `{"0","90","180","270"}`.
