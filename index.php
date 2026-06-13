<?php

session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_GET['reset'])) unset($_SESSION['fonts']);

function format_mm($value) {
	$formatted = number_format($value, 1, '.', '');
	return rtrim(rtrim($formatted, '0'), '.');
}

$devices = array(
	"m5stack" => array(
		"name" => "M5Stack Core",
		"image" => "M5Stack-bg.png",
		"width" => 320,
		"height" => 240,
		"frame_width" => 425,
		"frame_height" => 429,
		"screen_left" => 50,
		"screen_top" => 90,
		"screen_width" => 320,
		"screen_height" => 240,
		"physical_width_mm" => 54.0,
		"physical_height_mm" => 54.0,
		"screen_physical_width_mm" => 40.6,
		"screen_physical_height_mm" => 30.5
	),
	"m5stickcplus" => array(
		"name" => "M5Stick-CPlus",
		"image" => "M5StickC-Plus.png",
		"width" => 135,
		"height" => 240,
		"frame_width" => 443,
		"frame_height" => 892,
		"screen_left" => 83,
		"screen_top" => 65,
		"screen_width" => 278,
		"screen_height" => 460,
		"physical_width_mm" => 24.0,
		"physical_height_mm" => 48.0
	),
	"lilygot4s3amoled" => array(
		"name" => "Lily Go T4-S3 Amoled",
		"image" => "Lily Go T4 Amoled.png",
		"width" => 450,
		"height" => 600,
		"frame_width" => 1199,
		"frame_height" => 1496,
		"screen_left" => 103,
		"screen_top" => 102,
		"screen_width" => 994,
		"screen_height" => 1286,
		"physical_width_mm" => 44.0,
		"physical_height_mm" => 57.0,
		"screen_physical_width_mm" => 36.2,
		"screen_physical_height_mm" => 49.0
	)
);

$colors = array(
	"black" => array("name" => "Black", "rgb" => array(0, 0, 0)),
	"white" => array("name" => "White", "rgb" => array(255, 255, 255)),
	"red" => array("name" => "Red", "rgb" => array(255, 0, 0)),
	"green" => array("name" => "Green", "rgb" => array(0, 255, 0)),
	"blue" => array("name" => "Blue", "rgb" => array(0, 0, 255)),
	"cyan" => array("name" => "Cyan", "rgb" => array(0, 255, 255)),
	"magenta" => array("name" => "Magenta", "rgb" => array(255, 0, 255)),
	"yellow" => array("name" => "Yellow", "rgb" => array(255, 255, 0))
);

$vertical_positions = array(
	"top" => "Top",
	"centre" => "Centre",
	"bottom" => "Bottom"
);

$horizontal_positions = array(
	"left" => "Left",
	"centre" => "Centre",
	"right" => "Right"
);

$display_scales = array(
	"standard" => array("name" => "1920x1200 24 inch", "scale" => 1.0),
	"imac5k" => array("name" => "iMac 5K Retina", "scale" => 1.18),
	"custom" => array("name" => "Custom Scaling", "scale" => 1.0)
);

$device_state_file = "fonts/user/.selected-device";
$persisted_device = "";
if (file_exists($device_state_file)) {
	$persisted_device = trim(file_get_contents($device_state_file));
}

if (!isset($_SESSION['device']) || !isset($devices[$_SESSION['device']])) {
	if (isset($devices[$persisted_device])) {
		$_SESSION['device'] = $persisted_device;
	} else {
		$_SESSION['device'] = "m5stack";
	}
}

if (isset($_POST["set-device"])) {
	if (isset($_POST["device"]) && isset($devices[$_POST["device"]])) {
		$_SESSION['device'] = $_POST["device"];
		file_put_contents($device_state_file, $_POST["device"]);
	}
	exit();
}

$selected_device = $_SESSION['device'];

$settings_state_file = "fonts/user/.preview-settings.json";
$preview_settings = array("background" => "white", "foreground" => "black", "vertical" => "centre", "horizontal" => "centre", "size_mode" => "half", "display_scale" => "standard", "custom_scale" => 100, "word_wrap" => "off", "pixelate" => "off", "rotation" => "0");
if (file_exists($settings_state_file)) {
	$saved_settings = json_decode(file_get_contents($settings_state_file), true);
	if (is_array($saved_settings)) {
		if (isset($saved_settings["background"]) && isset($colors[$saved_settings["background"]])) $preview_settings["background"] = $saved_settings["background"];
		if (isset($saved_settings["foreground"]) && isset($colors[$saved_settings["foreground"]])) $preview_settings["foreground"] = $saved_settings["foreground"];
		if (isset($saved_settings["vertical"]) && isset($vertical_positions[$saved_settings["vertical"]])) $preview_settings["vertical"] = $saved_settings["vertical"];
		if (isset($saved_settings["horizontal"]) && isset($horizontal_positions[$saved_settings["horizontal"]])) $preview_settings["horizontal"] = $saved_settings["horizontal"];
		if (isset($saved_settings["size_mode"]) && in_array($saved_settings["size_mode"], array("half", "full", "physical"))) $preview_settings["size_mode"] = $saved_settings["size_mode"];
		if (isset($saved_settings["physical_size"]) && $saved_settings["physical_size"] == "on") $preview_settings["size_mode"] = "physical";
		if (isset($saved_settings["display_scale"]) && isset($display_scales[$saved_settings["display_scale"]])) $preview_settings["display_scale"] = $saved_settings["display_scale"];
		if (isset($saved_settings["custom_scale"]) && is_numeric($saved_settings["custom_scale"])) $preview_settings["custom_scale"] = max(50, min(1000, intval($saved_settings["custom_scale"])));
		if (isset($saved_settings["word_wrap"]) && in_array($saved_settings["word_wrap"], array("off", "on"))) $preview_settings["word_wrap"] = $saved_settings["word_wrap"];
		if (isset($saved_settings["pixelate"]) && in_array($saved_settings["pixelate"], array("off", "on"))) $preview_settings["pixelate"] = $saved_settings["pixelate"];
		if (isset($saved_settings["rotation"]) && in_array($saved_settings["rotation"], array("0", "90", "180", "270"))) $preview_settings["rotation"] = $saved_settings["rotation"];
	}
}

if (!isset($_SESSION["preview-settings"])) $_SESSION["preview-settings"] = $preview_settings;
if (!isset($colors[$_SESSION["preview-settings"]["background"]])) $_SESSION["preview-settings"]["background"] = $preview_settings["background"];
if (!isset($colors[$_SESSION["preview-settings"]["foreground"]])) $_SESSION["preview-settings"]["foreground"] = $preview_settings["foreground"];
if (!isset($vertical_positions[$_SESSION["preview-settings"]["vertical"]])) $_SESSION["preview-settings"]["vertical"] = $preview_settings["vertical"];
if (!isset($horizontal_positions[$_SESSION["preview-settings"]["horizontal"]])) $_SESSION["preview-settings"]["horizontal"] = $preview_settings["horizontal"];
if (!isset($_SESSION["preview-settings"]["size_mode"]) || !in_array($_SESSION["preview-settings"]["size_mode"], array("half", "full", "physical"))) $_SESSION["preview-settings"]["size_mode"] = $preview_settings["size_mode"];
if (!isset($_SESSION["preview-settings"]["display_scale"]) || !isset($display_scales[$_SESSION["preview-settings"]["display_scale"]])) $_SESSION["preview-settings"]["display_scale"] = $preview_settings["display_scale"];
if (!isset($_SESSION["preview-settings"]["custom_scale"]) || !is_numeric($_SESSION["preview-settings"]["custom_scale"]) || $_SESSION["preview-settings"]["custom_scale"] < 50 || $_SESSION["preview-settings"]["custom_scale"] > 1000) $_SESSION["preview-settings"]["custom_scale"] = $preview_settings["custom_scale"];
if (!isset($_SESSION["preview-settings"]["word_wrap"]) || !in_array($_SESSION["preview-settings"]["word_wrap"], array("off", "on"))) $_SESSION["preview-settings"]["word_wrap"] = $preview_settings["word_wrap"];
if (!isset($_SESSION["preview-settings"]["pixelate"]) || !in_array($_SESSION["preview-settings"]["pixelate"], array("off", "on"))) $_SESSION["preview-settings"]["pixelate"] = $preview_settings["pixelate"];
if (!isset($_SESSION["preview-settings"]["rotation"]) || !in_array($_SESSION["preview-settings"]["rotation"], array("0", "90", "180", "270"))) $_SESSION["preview-settings"]["rotation"] = $preview_settings["rotation"];

if (isset($_POST["set-preview-settings"])) {
	if (isset($_POST["background"]) && isset($colors[$_POST["background"]])) $_SESSION["preview-settings"]["background"] = $_POST["background"];
	if (isset($_POST["foreground"]) && isset($colors[$_POST["foreground"]])) $_SESSION["preview-settings"]["foreground"] = $_POST["foreground"];
	if (isset($_POST["vertical"]) && isset($vertical_positions[$_POST["vertical"]])) $_SESSION["preview-settings"]["vertical"] = $_POST["vertical"];
	if (isset($_POST["horizontal"]) && isset($horizontal_positions[$_POST["horizontal"]])) $_SESSION["preview-settings"]["horizontal"] = $_POST["horizontal"];
	if (isset($_POST["size_mode"]) && in_array($_POST["size_mode"], array("half", "full", "physical"))) $_SESSION["preview-settings"]["size_mode"] = $_POST["size_mode"];
	if (isset($_POST["display_scale"]) && isset($display_scales[$_POST["display_scale"]])) $_SESSION["preview-settings"]["display_scale"] = $_POST["display_scale"];
	if (isset($_POST["custom_scale"]) && is_numeric($_POST["custom_scale"])) $_SESSION["preview-settings"]["custom_scale"] = max(50, min(1000, intval($_POST["custom_scale"])));
	if (isset($_POST["word_wrap"]) && in_array($_POST["word_wrap"], array("off", "on"))) $_SESSION["preview-settings"]["word_wrap"] = $_POST["word_wrap"];
	if (isset($_POST["pixelate"]) && in_array($_POST["pixelate"], array("off", "on"))) $_SESSION["preview-settings"]["pixelate"] = $_POST["pixelate"];
	if (isset($_POST["rotation"]) && in_array($_POST["rotation"], array("0", "90", "180", "270"))) $_SESSION["preview-settings"]["rotation"] = $_POST["rotation"];
	file_put_contents($settings_state_file, json_encode($_SESSION["preview-settings"]));
	exit();
}

$selected_background = $_SESSION["preview-settings"]["background"];
$selected_foreground = $_SESSION["preview-settings"]["foreground"];
$selected_vertical = $_SESSION["preview-settings"]["vertical"];
$selected_horizontal = $_SESSION["preview-settings"]["horizontal"];
$selected_size_mode = $_SESSION["preview-settings"]["size_mode"];
$device_supports_physical_size = isset($devices[$selected_device]["physical_width_mm"]) && isset($devices[$selected_device]["physical_height_mm"]);
if (!$device_supports_physical_size && $selected_size_mode == "physical") {
	$selected_size_mode = "half";
	$_SESSION["preview-settings"]["size_mode"] = $selected_size_mode;
}
$selected_display_scale = $_SESSION["preview-settings"]["display_scale"];
$selected_custom_scale = $_SESSION["preview-settings"]["custom_scale"];
$selected_word_wrap = $_SESSION["preview-settings"]["word_wrap"];
$selected_pixelate = $_SESSION["preview-settings"]["pixelate"];
$selected_rotation = $_SESSION["preview-settings"]["rotation"];
$physical_scale = $display_scales[$selected_display_scale]["scale"];
if ($selected_display_scale == "custom") $physical_scale = $selected_custom_scale / 100;
$use_physical_size = ($device_supports_physical_size && $selected_size_mode == "physical");
$device_scale = ($selected_size_mode == "full") ? 1.0 : 0.5;
$rotation = intval($selected_rotation);
$frame_width_raw = $devices[$selected_device]["frame_width"];
$frame_height_raw = $devices[$selected_device]["frame_height"];
$screen_left_raw = $devices[$selected_device]["screen_left"];
$screen_top_raw = $devices[$selected_device]["screen_top"];
$screen_width_raw = $devices[$selected_device]["screen_width"];
$screen_height_raw = $devices[$selected_device]["screen_height"];

if ($rotation == 90) {
	$rotated_frame_width_raw = $frame_height_raw;
	$rotated_frame_height_raw = $frame_width_raw;
	$rotated_screen_left_raw = $frame_height_raw - ($screen_top_raw + $screen_height_raw);
	$rotated_screen_top_raw = $screen_left_raw;
	$rotated_screen_width_raw = $screen_height_raw;
	$rotated_screen_height_raw = $screen_width_raw;
} elseif ($rotation == 180) {
	$rotated_frame_width_raw = $frame_width_raw;
	$rotated_frame_height_raw = $frame_height_raw;
	$rotated_screen_left_raw = $frame_width_raw - ($screen_left_raw + $screen_width_raw);
	$rotated_screen_top_raw = $frame_height_raw - ($screen_top_raw + $screen_height_raw);
	$rotated_screen_width_raw = $screen_width_raw;
	$rotated_screen_height_raw = $screen_height_raw;
} elseif ($rotation == 270) {
	$rotated_frame_width_raw = $frame_height_raw;
	$rotated_frame_height_raw = $frame_width_raw;
	$rotated_screen_left_raw = $screen_top_raw;
	$rotated_screen_top_raw = $frame_width_raw - ($screen_left_raw + $screen_width_raw);
	$rotated_screen_width_raw = $screen_height_raw;
	$rotated_screen_height_raw = $screen_width_raw;
} else {
	$rotated_frame_width_raw = $frame_width_raw;
	$rotated_frame_height_raw = $frame_height_raw;
	$rotated_screen_left_raw = $screen_left_raw;
	$rotated_screen_top_raw = $screen_top_raw;
	$rotated_screen_width_raw = $screen_width_raw;
	$rotated_screen_height_raw = $screen_height_raw;
}

$frame_width = intval($rotated_frame_width_raw * $device_scale);
$frame_height = intval($rotated_frame_height_raw * $device_scale);
$screen_left = intval($rotated_screen_left_raw * $device_scale);
$screen_top = intval($rotated_screen_top_raw * $device_scale);
$screen_width = intval($rotated_screen_width_raw * $device_scale);
$screen_height = intval($rotated_screen_height_raw * $device_scale);
$background_width = intval($frame_width_raw * $device_scale);
$background_height = intval($frame_height_raw * $device_scale);
$background_left = intval(($frame_width - $background_width) / 2);
$background_top = intval(($frame_height - $background_height) / 2);
if ($use_physical_size) {
	$physical_width = $devices[$selected_device]["physical_width_mm"] * $physical_scale;
	$physical_height = $devices[$selected_device]["physical_height_mm"] * $physical_scale;
	if (isset($devices[$selected_device]["screen_physical_width_mm"]) && isset($devices[$selected_device]["screen_physical_height_mm"])) {
		$screen_physical_width = $devices[$selected_device]["screen_physical_width_mm"] * $physical_scale;
		$screen_physical_height = $devices[$selected_device]["screen_physical_height_mm"] * $physical_scale;
		$left_margin_raw = $screen_left_raw;
		$right_margin_raw = $frame_width_raw - ($screen_left_raw + $screen_width_raw);
		$top_margin_raw = $screen_top_raw;
		$bottom_margin_raw = $frame_height_raw - ($screen_top_raw + $screen_height_raw);
		$horizontal_margin_total = $physical_width - $screen_physical_width;
		$vertical_margin_total = $physical_height - $screen_physical_height;

		if (($left_margin_raw + $right_margin_raw) > 0) {
			$screen_left_physical = $horizontal_margin_total * ($left_margin_raw / ($left_margin_raw + $right_margin_raw));
		} else {
			$screen_left_physical = $horizontal_margin_total / 2;
		}
		if (($top_margin_raw + $bottom_margin_raw) > 0) {
			$screen_top_physical = $vertical_margin_total * ($top_margin_raw / ($top_margin_raw + $bottom_margin_raw));
		} else {
			$screen_top_physical = $vertical_margin_total / 2;
		}

		if ($rotation == 90) {
			$rotated_screen_left_physical = $physical_height - ($screen_top_physical + $screen_physical_height);
			$rotated_screen_top_physical = $screen_left_physical;
			$rotated_screen_width_physical = $screen_physical_height;
			$rotated_screen_height_physical = $screen_physical_width;
		} elseif ($rotation == 180) {
			$rotated_screen_left_physical = $physical_width - ($screen_left_physical + $screen_physical_width);
			$rotated_screen_top_physical = $physical_height - ($screen_top_physical + $screen_physical_height);
			$rotated_screen_width_physical = $screen_physical_width;
			$rotated_screen_height_physical = $screen_physical_height;
		} elseif ($rotation == 270) {
			$rotated_screen_left_physical = $screen_top_physical;
			$rotated_screen_top_physical = $physical_width - ($screen_left_physical + $screen_physical_width);
			$rotated_screen_width_physical = $screen_physical_height;
			$rotated_screen_height_physical = $screen_physical_width;
		} else {
			$rotated_screen_left_physical = $screen_left_physical;
			$rotated_screen_top_physical = $screen_top_physical;
			$rotated_screen_width_physical = $screen_physical_width;
			$rotated_screen_height_physical = $screen_physical_height;
		}

		$screen_left = $rotated_screen_left_physical . "mm";
		$screen_top = $rotated_screen_top_physical . "mm";
		$screen_width = $rotated_screen_width_physical . "mm";
		$screen_height = $rotated_screen_height_physical . "mm";
	} else {
		$scale_x = $physical_width / $frame_width_raw;
		$scale_y = $physical_height / $frame_height_raw;
		$screen_left = ($rotated_screen_left_raw * (($rotation == 90 || $rotation == 270) ? $scale_y : $scale_x)) . "mm";
		$screen_top = ($rotated_screen_top_raw * (($rotation == 90 || $rotation == 270) ? $scale_x : $scale_y)) . "mm";
		$screen_width = ($rotated_screen_width_raw * (($rotation == 90 || $rotation == 270) ? $scale_y : $scale_x)) . "mm";
		$screen_height = ($rotated_screen_height_raw * (($rotation == 90 || $rotation == 270) ? $scale_x : $scale_y)) . "mm";
	}
	$frame_width = ($rotation == 90 || $rotation == 270) ? $physical_height . "mm" : $physical_width . "mm";
	$frame_height = ($rotation == 90 || $rotation == 270) ? $physical_width . "mm" : $physical_height . "mm";
	$background_width = $physical_width . "mm";
	$background_height = $physical_height . "mm";
	$background_left = "calc((" . $frame_width . " - " . $background_width . ") / 2)";
	$background_top = "calc((" . $frame_height . " - " . $background_height . ") / 2)";
} else {
	$frame_width .= "px";
	$frame_height .= "px";
	$screen_left .= "px";
	$screen_top .= "px";
	$screen_width .= "px";
	$screen_height .= "px";
	$background_width .= "px";
	$background_height .= "px";
	$background_left .= "px";
	$background_top .= "px";
}

$preview_device_dimensions = "";
$preview_display_dimensions = "";
$preview_resolution = (($rotation == 90 || $rotation == 270) ? $devices[$selected_device]["height"] . " x " . $devices[$selected_device]["width"] : $devices[$selected_device]["width"] . " x " . $devices[$selected_device]["height"]) . " px";
if ($use_physical_size) {
	$preview_device_dimensions = format_mm($devices[$selected_device]["physical_width_mm"]) . " x " . format_mm($devices[$selected_device]["physical_height_mm"]) . " mm";
	if (isset($devices[$selected_device]["screen_physical_width_mm"]) && isset($devices[$selected_device]["screen_physical_height_mm"])) {
		$preview_display_dimensions = format_mm($devices[$selected_device]["screen_physical_width_mm"]) . " x " . format_mm($devices[$selected_device]["screen_physical_height_mm"]) . " mm";
	} else {
		$preview_display_dimensions = "not configured";
	}
}

if (isset($_POST["get-font"])) {

	if (!isset($_POST['size']) || !is_numeric($_POST['size']) || $_POST['size'] < 3 || $_POST['size'] > 200) exit();
	$size = intval($_POST['size']);

	if (!isset($_POST['font'])) exit();	
	$font = escapeshellarg("fonts/" . $_POST['font']);
	$fontconvert = getenv('FONTCONVERT_PATH');
	if ($fontconvert === false || $fontconvert === '') $fontconvert = './fontconvert';
	$fontconvert = escapeshellcmd($fontconvert);
	
	
	exec("$fontconvert $font $size", $output, $retval);
	if ($retval != 0) exit();
	
	$filename = $output[count($output) - 6];
	$filename = str_replace("const GFXfont ", "", $filename);
	$filename = str_replace(" PROGMEM = {", ".h", $filename);
	

	header("Content-Disposition: attachment; filename=\"$filename\"");
	
	foreach ($output as $line) echo "$line\n";

	exit();
}

if (isset($_POST["delete-font"])) {
	if (isset($_POST["font-to-delete"])) {
		$delete_font = basename($_POST["font-to-delete"]);
		if (preg_match('/^[a-zA-Z0-9 ._-]+\\.ttf$/i', $delete_font)) {
			$delete_file = "fonts/user/" . $delete_font;
			if (file_exists($delete_file)) unlink($delete_file);
			foreach ($_SESSION['fonts'] as $index => $font) {
				if ($font == $delete_font) unset($_SESSION['fonts'][$index]);
			}
		}
	}
	header("Location: " . $_SERVER["PHP_SELF"]);
	exit();
}


if (!isset($_SESSION['fonts'])) $_SESSION['fonts'] = array();

// Delete fonts from session variable if the disk file is not there anymore
foreach ($_SESSION['fonts'] as $index => $font) {
	if (!file_exists("fonts/user/$font")) unset($_SESSION['fonts'][$index]);
}

$user_fonts = array();
foreach (glob("fonts/user/*.ttf") as $font_file) {
	$user_fonts[] = basename($font_file);
}
foreach (glob("fonts/user/*.TTF") as $font_file) {
	$font_name = basename($font_file);
	if (!in_array($font_name, $user_fonts)) $user_fonts[] = $font_name;
}
sort($user_fonts);


$select_font = "";
if (isset($_POST["submit-file"])) {
	$target_dir = "fonts/user/";
	$filename = basename($_FILES["fileToUpload"]["name"]);
	$target_file = $target_dir . $filename;
	$select_font = "user/$filename";
	
	if (strtolower(substr($target_file, -4)) == ".ttf") {
		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			if (!in_array($filename, $_SESSION['fonts'])) {
				array_push($_SESSION['fonts'], $filename);
				if (count($_SESSION['fonts']) > 5) array_shift($_SESSION['fonts']);
			}
			if (!in_array($filename, $user_fonts)) {
				$user_fonts[] = $filename;
				sort($user_fonts);
			}
		}
	}
}

?>

<html>

<head>
	<title>truetype2gfx - Converting fonts from TrueType to Adafruit GFX</title>

	<style>
		/*
		 * Theme token system (ThemeTokens model — see design.md "Data Models").
		 * All component colour/spacing/shape/motion values derive from these tokens;
		 * no hard-coded literals should live outside this :root block.
		 * Body text (--color-text) on the app background (--color-bg) measures ~16:1,
		 * comfortably exceeding the WCAG AA 4.5:1 minimum for body text.
		 */
		:root {
			/* Colour — dark, modern surface palette with a single vibrant accent */
			--color-bg: #0d1117;             /* app background (deep neutral) */
			--color-surface: #161b22;        /* card/panel background */
			--color-surface-raised: #1c2230; /* elevated surface */
			--color-border: #30363d;         /* subtle borders */
			--color-text: #e6edf3;           /* primary text */
			--color-text-muted: #9da7b3;     /* secondary text */
			--color-accent: #4f9cf9;         /* brand/interactive accent */
			--color-accent-text: #0a0f16;    /* text on accent */
			--color-focus: #58a6ff;          /* focus ring */
			--color-danger: #f85149;         /* delete/destructive */

			/* Typography */
			--font-sans: "Segoe UI", Roboto, Helvetica, Arial, system-ui, sans-serif;
			--font-mono: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
			--font-size-sm: 0.875rem;
			--font-size-md: 1rem;
			--font-size-lg: 1.25rem;
			--font-size-xl: 1.75rem;
			--line-height: 1.5;

			/* Spacing scale */
			--space-1: 4px;
			--space-2: 8px;
			--space-3: 12px;
			--space-4: 16px;
			--space-5: 24px;
			--space-6: 32px;
			--space-7: 48px;

			/* Shape */
			--radius: 8px;
			--radius-sm: 4px;

			/* Elevation */
			--shadow: 0 4px 16px rgba(0, 0, 0, 0.45);
			--shadow-sm: 0 1px 4px rgba(0, 0, 0, 0.35);

			/* Motion */
			--transition-fast: 100ms ease;
			--transition-base: 200ms ease;
		}
		body {
			background-color: var(--color-bg);
			color: var(--color-text);
			margin: 0;
			font-family: var(--font-sans);
			line-height: var(--line-height);
		}
		/*
		 * App Shell (see design.md "Component: App Shell").
		 * Root container that centres page content within a max width with
		 * fixed page padding (desktop-only layout).
		 */
		.app-shell {
			max-width: 1600px;
			margin: 0 auto;
			padding: var(--space-6);
		}
		/* Header brand bar: product name + tagline */
		.app-header {
			margin-bottom: var(--space-6);
			padding-bottom: var(--space-4);
			border-bottom: 1px solid var(--color-border);
		}
		.app-brand-name {
			margin: 0;
			font-size: var(--font-size-xl);
			color: var(--color-text);
		}
		.app-brand-tagline {
			margin: var(--space-2) 0 0;
			font-size: var(--font-size-md);
			color: var(--color-text-muted);
		}
		a {
			text-decoration: none;
			font-weight: bold;
			color: var(--color-accent);
		}
		/*
		 * Fixed desktop layout. Three zones in a single row: Font Library (left,
		 * fixed width), Controls (centre, sized to content), Live Preview (right,
		 * the device's natural width). Flexbox never overlaps items, so the
		 * preview can never paint over the controls. No responsive breakpoints.
		 */
		.workspace {
			display: flex;
			gap: var(--space-6);
			align-items: flex-start;
		}
		/*
		 * The <form> wraps the Library + Controls zones in the DOM while the
		 * Preview zone is a sibling. display:contents removes the form's own box
		 * from the layout so its children (zone-library, zone-controls) become
		 * direct participants in the workspace flex row — all without altering the
		 * DOM structure, form submission, or any id/name/handler hooks.
		 */
		.workspace > form {
			display: contents;
		}
		.zone-library {
			flex: 0 0 240px;
		}
		/* Explicit metrics on the FreeFonts heading so the other zones can be
		 * offset to line up with the first font row. */
		.zone-library h3:first-child {
			margin-top: 0;
			margin-bottom: var(--space-3);
			font-size: var(--font-size-md);
			line-height: var(--line-height);
		}
		.zone-controls {
			flex: 0 0 auto;
			min-width: 0;
			margin-top: calc(var(--font-size-md) * var(--line-height) + var(--space-3));
		}
		.zone-preview {
			flex: 0 0 auto;
			margin-top: calc(var(--font-size-md) * var(--line-height) + var(--space-3));
		}
		/* Info / documentation section sits below the workspace */
		.info-section {
			margin-top: var(--space-7);
			padding-top: var(--space-5);
			border-top: 1px solid var(--color-border);
			max-width: 70ch;
		}
		.info-section h3 {
			color: var(--color-text);
			margin-top: var(--space-6);
		}
		.info-section p {
			color: var(--color-text);
		}
		.info-section blockquote {
			margin: var(--space-4) 0;
			padding: var(--space-3) var(--space-4);
			border-left: 3px solid var(--color-accent);
			background-color: var(--color-surface);
			border-radius: var(--radius-sm);
		}
		.info-section code,
		.info-section pre {
			font-family: var(--font-mono);
			font-size: var(--font-size-sm);
		}
		.info-section img {
			max-width: 100%;
			height: auto;
		}
		/*
		 * Font Library list (see design.md "Component: Font Library Panel").
		 * Each font is a labelled, fully-clickable selectable row. The radio
		 * input is preserved verbatim (name="font", original value, the
		 * FreeSans.ttf checked default, and onChange="updateImage()") for
		 * contract stability; the wrapping <label> supplies the accessible name
		 * and makes the whole row a hit target. The active row is indicated via
		 * :has(input:checked) using the accent token.
		 */
		.font-list {
			margin: 0 0 var(--space-4);
			padding: 0;
			display: flex;
			flex-direction: column;
			gap: var(--space-1);
		}
		.font-row {
			display: flex;
			align-items: center;
			gap: var(--space-2);
			padding: var(--space-2) var(--space-3);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			background-color: var(--color-surface);
			cursor: pointer;
			transition: background-color var(--transition-fast), border-color var(--transition-fast);
		}
		.font-row:hover {
			background-color: var(--color-surface-raised);
			border-color: var(--color-accent);
		}
		/* Selected-state indication on the currently active font row (2A.4) */
		.font-row:has(input[name="font"]:checked) {
			border-color: var(--color-accent);
			background-color: var(--color-surface-raised);
			box-shadow: inset 3px 0 0 var(--color-accent);
		}
		.font-row:focus-within {
			border-color: var(--color-focus);
			box-shadow: 0 0 0 2px var(--color-focus);
		}
		.font-row input[type="radio"] {
			accent-color: var(--color-accent);
			margin: 0;
			flex: 0 0 auto;
		}
		/* User-font rows pair a clickable select area with the delete button */
		.font-row--user {
			cursor: default;
		}
		.font-row-select {
			display: flex;
			align-items: center;
			gap: var(--space-2);
			flex: 1 1 auto;
			min-width: 0;
			cursor: pointer;
		}
		.font-name {
			flex: 1 1 auto;
			min-width: 0;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
		/*
		 * Per-row delete button (Requirement 4.2): danger-tokened and kept
		 * visually quiet until the row is hovered or the button is focused.
		 */
		.font-delete {
			flex: 0 0 auto;
			border: 1px solid transparent;
			border-radius: var(--radius-sm);
			padding: var(--space-1) var(--space-2);
			background: transparent;
			color: var(--color-danger);
			font: inherit;
			line-height: 1;
			cursor: pointer;
			opacity: 0;
			transition: opacity var(--transition-fast), background-color var(--transition-fast), border-color var(--transition-fast);
		}
		.font-row--user:hover .font-delete,
		.font-row--user:focus-within .font-delete,
		.font-delete:focus-visible {
			opacity: 1;
		}
		.font-delete:hover {
			border-color: var(--color-danger);
			background-color: color-mix(in srgb, var(--color-danger) 12%, transparent);
		}
		.font-delete:focus-visible {
			outline: none;
			border-color: var(--color-focus);
			box-shadow: 0 0 0 2px var(--color-focus);
		}
		/* Friendly empty-state when no custom fonts are present (Requirement 4.5) */
		.font-empty-state {
			margin: 0 0 var(--space-4);
			padding: var(--space-3);
			border: 1px dashed var(--color-border);
			border-radius: var(--radius-sm);
			color: var(--color-text-muted);
			font-size: var(--font-size-md);
			text-align: center;
		}
		/* Upload control (Requirement 3.1) styled consistently with the theme */
		.upload-control {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: var(--space-2);
			margin-bottom: var(--space-4);
		}
		.upload-control input[type="file"] {
			flex: 1 1 auto;
			min-width: 0;
			color: var(--color-text-muted);
			font-size: var(--font-size-md);
		}
		.upload-control input[type="file"]::file-selector-button {
			padding: var(--space-3) var(--space-5);
			margin-right: var(--space-3);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			background-color: var(--color-surface);
			color: var(--color-text);
			font-family: var(--font-sans);
			font-size: var(--font-size-md);
			cursor: pointer;
			transition: background-color var(--transition-base),
				border-color var(--transition-base);
		}
		.upload-control input[type="file"]::file-selector-button:hover {
			background-color: var(--color-surface-raised);
			border-color: var(--color-accent);
		}
		#device-frame {
			position: relative;
			width: <?php echo $frame_width; ?>;
			height: <?php echo $frame_height; ?>;
			overflow: hidden;
			background-color: transparent;
		}
		#device-background {
			position: absolute;
			left: <?php echo $background_left; ?>;
			top: <?php echo $background_top; ?>;
			width: <?php echo $background_width; ?>;
			height: <?php echo $background_height; ?>;
			transform: rotate(<?php echo $rotation; ?>deg);
			transform-origin: center center;
			z-index: 1;
		}
		/* Fill slightly beyond the active area so browser subpixel tolerances
		 * do not let the source PNG's white screen show at some scalings. */
		#screen-fill {
			position: absolute;
			left: calc(<?php echo $screen_left; ?> - 1px);
			top: calc(<?php echo $screen_top; ?> - 1px);
			width: calc(<?php echo $screen_width; ?> + 2px);
			height: calc(<?php echo $screen_height; ?> + 2px);
			background-color: <?php echo htmlspecialchars($selected_background, ENT_QUOTES, 'UTF-8'); ?>;
			z-index: 2;
		}
		#image {
			position: absolute;
			left: <?php echo $screen_left; ?>;
			top: <?php echo $screen_top; ?>;
			width: <?php echo $screen_width; ?>;
			height: <?php echo $screen_height; ?>;
			display: block;
			object-fit: contain;
			object-position: center center;
			image-rendering: <?php echo ($selected_pixelate == "on") ? "pixelated" : "auto"; ?>;
			z-index: 3;
		}
		/* Preview Stage: framed, elevated, left-aligned (design.md "Live Preview Stage") */
		.preview-stage {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			gap: var(--space-3);
			padding: var(--space-5);
			background-color: var(--color-surface);
			border: 1px solid var(--color-border);
			border-radius: var(--radius);
			box-shadow: var(--shadow);
		}
		.preview-status {
			font-size: var(--font-size-sm);
			color: var(--color-text-muted);
			padding: var(--space-1) var(--space-3);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			background-color: var(--color-surface-raised);
		}
		.preview-status-stack {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			gap: var(--space-2);
		}
		.preview-status-meta {
			font-size: var(--font-size-sm);
		}
		/* Compositor-friendly loading affordance (opacity/transform only) */
		.preview-loading {
			position: absolute;
			inset: 0;
			z-index: 4;
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: 0;
			pointer-events: none;
			transition: opacity var(--transition-base);
		}
		.preview-loading.is-loading {
			opacity: 1;
		}
		.preview-spinner {
			width: 28px;
			height: 28px;
			border: 3px solid var(--color-border);
			border-top-color: var(--color-accent);
			border-radius: 50%;
			animation: preview-spin 0.8s linear infinite;
		}
		@keyframes preview-spin {
			to { transform: rotate(360deg); }
		}
		/* Error/placeholder state shown in place of a broken-image icon */
		.preview-placeholder {
			position: absolute;
			inset: 0;
			z-index: 5;
			display: flex;
			align-items: center;
			justify-content: center;
			text-align: center;
			padding: var(--space-4);
			color: var(--color-text-muted);
			background-color: var(--color-surface-raised);
		}
		.preview-placeholder[hidden] {
			display: none;
		}
		/* Inline upload notice replacing the alert() (task 8.2) */
		.upload-notice {
			margin-top: var(--space-2);
			color: var(--color-danger);
			font-size: var(--font-size-sm);
		}
		/*
		 * Controls Panel (see design.md "Component: Controls Panel").
		 * The settings are grouped into themed cards laid out on a fixed two-column
		 * grid. All spacing/colour/shape values derive from theme tokens.
		 */
		.controls-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: var(--space-4) var(--space-5);
			align-items: stretch;
			width: max-content;
		}
		/*
		 * Each setting group is a themed, bordered, rounded surface with a clear
		 * heading and consistent internal spacing.
		 */
		.control-group {
			border: 1px solid var(--color-border);
			border-radius: var(--radius);
			background-color: var(--color-surface);
			padding: var(--space-4);
			box-shadow: var(--shadow-sm);
		}
		.control-group h3 {
			margin-top: 0;
			margin-bottom: var(--space-3);
			font-size: var(--font-size-md);
			color: var(--color-text);
		}
		.control-group.span-2 {
			grid-column: 1 / -1;
		}
		/* Two-column split: options (left) beside positioning (right) */
		.options-split {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: var(--space-3) var(--space-5);
			align-items: stretch;
		}
		.options-col {
			display: flex;
			flex-direction: column;
		}
		/* Push the download button to the bottom of its column so its bottom
		 * lines up with the Horizontal radio row in the adjacent column. */
		.options-col .control-row:last-child {
			margin-top: auto;
		}
		.control-row {
			margin-bottom: var(--space-3);
		}
		.control-row:last-child {
			margin-bottom: 0;
		}
		.control-label {
			display: block;
			margin-bottom: var(--space-1);
			font-size: var(--font-size-sm);
			color: var(--color-text-muted);
		}
		/*
		 * Consistently themed form controls inside the Controls Panel: selects,
		 * text/number inputs and the textarea share surface/border/radius tokens
		 * with a clear focus-visible ring using the focus token (Requirement 16.3).
		 */
		.zone-controls select,
		.zone-controls input[type="text"],
		.zone-controls input[type="number"],
		.zone-controls textarea {
			background-color: var(--color-surface-raised);
			color: var(--color-text);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			padding: var(--space-2) var(--space-3);
			font-family: var(--font-sans);
			font-size: var(--font-size-md);
			line-height: var(--line-height);
			transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
		}
		.zone-controls select:hover,
		.zone-controls input[type="text"]:hover,
		.zone-controls input[type="number"]:hover,
		.zone-controls textarea:hover {
			border-color: var(--color-accent);
		}
		.zone-controls select:focus-visible,
		.zone-controls input[type="text"]:focus-visible,
		.zone-controls input[type="number"]:focus-visible,
		.zone-controls textarea:focus-visible {
			outline: none;
			border-color: var(--color-focus);
			box-shadow: 0 0 0 2px var(--color-focus);
		}
		/* Toggle-style buttons (rotate / pixelate) use a secondary surface treatment. */
		.zone-controls input[type="button"] {
			padding: var(--space-2) var(--space-3);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			background-color: var(--color-surface-raised);
			color: var(--color-text);
			font-family: var(--font-sans);
			font-size: var(--font-size-sm);
			cursor: pointer;
			transition: background-color var(--transition-fast), border-color var(--transition-fast);
		}
		.zone-controls input[type="button"]:hover {
			background-color: var(--color-surface);
			border-color: var(--color-accent);
		}
		.zone-controls input[type="button"]:focus-visible {
			outline: none;
			border-color: var(--color-focus);
			box-shadow: 0 0 0 2px var(--color-focus);
		}
		.zone-controls input[type="checkbox"],
		.zone-controls input[type="radio"] {
			accent-color: var(--color-accent);
		}
		.zone-controls input[type="checkbox"]:focus-visible,
		.zone-controls input[type="radio"]:focus-visible {
			outline: 2px solid var(--color-focus);
			outline-offset: 2px;
		}
		/* Inline checkbox / radio option labels make the whole option a hit target. */
		.option-label {
			display: inline-flex;
			align-items: center;
			gap: var(--space-2);
			margin-right: var(--space-3);
			cursor: pointer;
		}
		/*
		 * Disabled styling for the calibration controls.
		 * The size-mode "physical" option, the display-scale select and the
		 * custom-scale input carry a server-rendered `disabled` attribute unless
		 * the selected device has physical dimensions and the size mode is
		 * physical (and, for custom-scale, the display scale is custom).
		 */
		.zone-controls select:disabled,
		.zone-controls input:disabled,
		.zone-controls option:disabled {
			opacity: 0.45;
			cursor: not-allowed;
			background-color: var(--color-surface);
			color: var(--color-text-muted);
			border-color: var(--color-border);
		}
		.control-row.is-disabled {
			opacity: 0.6;
		}
		.control-row.is-disabled .control-label {
			color: var(--color-text-muted);
		}
		#textfield {
			width: 100%;
			height: 8em;
			box-sizing: border-box;
			resize: none;
		}
		/* Pattern Tests: small square push buttons (cross-hatch / grid) */
		.pattern-tests {
			display: flex;
			gap: var(--space-2);
		}
		.pattern-button {
			width: 28px;
			height: 28px;
			padding: 0;
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			background-color: var(--color-surface-raised);
			color: var(--color-text);
			font-size: var(--font-size-md);
			line-height: 1;
			cursor: pointer;
			transition: background-color var(--transition-fast), border-color var(--transition-fast);
		}
		.pattern-button:hover {
			border-color: var(--color-accent);
		}
		.pattern-button:active {
			background-color: var(--color-accent);
			color: var(--color-accent-text);
		}
		.pattern-button:focus-visible {
			outline: none;
			border-color: var(--color-focus);
			box-shadow: 0 0 0 2px var(--color-focus);
		}
		#background, #foreground, #device, #size-mode, #display-scale {
			min-width: 140px;
		}
		#sizefield {
			width: 4.6em;
			text-align: center;
		}
		#custom-scale {
			width: 4em;
			text-align: center;
		}
		/*
		 * Primary download action (Requirement 5.1, 5.3): the "Get GFX font file"
		 * submit input is the prominent primary action. It uses the accent theme
		 * tokens, full-width sizing, rounded corners, and clear hover/focus states
		 * so it reads as visually distinct from the secondary Upload/Delete actions.
		 */
		#get-font {
			display: inline-block;
			width: auto;
			box-sizing: border-box;
			padding: var(--space-3) var(--space-4);
			border: 1px solid transparent;
			border-radius: var(--radius);
			background-color: var(--color-accent);
			color: var(--color-accent-text);
			font-family: var(--font-sans);
			font-size: var(--font-size-lg);
			font-weight: bold;
			line-height: var(--line-height);
			text-align: center;
			cursor: pointer;
			box-shadow: var(--shadow-sm);
			transition: background-color var(--transition-base),
				box-shadow var(--transition-base),
				transform var(--transition-base);
		}
		#get-font:hover {
			background-color: color-mix(in srgb, var(--color-accent) 85%, #fff);
			box-shadow: var(--shadow);
		}
		#get-font:active {
			transform: translateY(1px);
		}
		#get-font:focus-visible {
			outline: none;
			box-shadow: 0 0 0 3px var(--color-focus);
		}
		/*
		 * Secondary action treatment (Requirement 5.3): the Upload button reads as
		 * a lower-emphasis surface button so the primary download action stands out.
		 * (The per-row Delete buttons use the .font-delete danger treatment above.)
		 */
		.upload-control input[type="submit"] {
			flex: 0 0 auto;
			padding: var(--space-3) var(--space-5);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-sm);
			background-color: var(--color-surface);
			color: var(--color-text);
			font-family: var(--font-sans);
			font-size: var(--font-size-md);
			cursor: pointer;
			transition: background-color var(--transition-base),
				border-color var(--transition-base);
		}
		.upload-control input[type="submit"]:hover {
			background-color: var(--color-surface-raised);
			border-color: var(--color-accent);
		}
		.upload-control input[type="submit"]:focus-visible {
			outline: none;
			border-color: var(--color-focus);
			box-shadow: 0 0 0 2px var(--color-focus);
		}
	</style>
</head>

<body onload = 'setFont()'>

	<div class="app-shell">

		<header class="app-header">
			<h1 class="app-brand-name">truetype2gfx</h1>
			<p class="app-brand-tagline">Convert TrueType fonts into Adafruit GFX header files</p>
		</header>

	<div class="workspace">

		<form action="" method="post" enctype="multipart/form-data">

			<div class="zone-library">

				<h3>FreeFonts</h3>
				<div class="font-list">
					<label class="font-row"><input type="radio" name="font" value="FreeSans.ttf" checked onChange="updateImage()"><span class="font-name">FreeSans</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSansBold.ttf" onChange="updateImage()"><span class="font-name">FreeSansBold</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSansBoldOblique.ttf" onChange="updateImage()"><span class="font-name">FreeSansBoldOblique</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSansOblique.ttf" onChange="updateImage()"><span class="font-name">FreeSansOblique</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSerif.ttf" onChange="updateImage()"><span class="font-name">FreeSerif</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSerifBold.ttf" onChange="updateImage()"><span class="font-name">FreeSerifBold</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSerifBoldItalic.ttf" onChange="updateImage()"><span class="font-name">FreeSerifBoldItalic</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeSerifItalic.ttf" onChange="updateImage()"><span class="font-name">FreeSerifItalic</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeMono.ttf" onChange="updateImage()"><span class="font-name">FreeMono</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeMonoBold.ttf" onChange="updateImage()"><span class="font-name">FreeMonoBold</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeMonoBoldOblique.ttf" onChange="updateImage()"><span class="font-name">FreeMonoBoldOblique</span></label>
					<label class="font-row"><input type="radio" name="font" value="FreeMonoOblique.ttf" onChange="updateImage()"><span class="font-name">FreeMonoOblique</span></label>
				</div>
				
				<h3>Your fonts</h3>
				<input type="hidden" name="font-to-delete" id="font-to-delete" value="">
				<div class="font-list">
				<?php
					if (empty($user_fonts)) {
						echo "<p class=\"font-empty-state\">No custom fonts yet &mdash; upload a .ttf to get started.</p>\n";
					} else {
						foreach ($user_fonts as $font) {
							$font_label = str_replace(".TTF", "", str_replace(".ttf", "", $font));
							echo "<div class=\"font-row font-row--user\">";
							echo "<label class=\"font-row-select\"><input type=\"radio\" name=\"font\" value=\"user/$font\" onChange=\"updateImage()\"><span class=\"font-name\">" . htmlspecialchars($font_label, ENT_QUOTES, 'UTF-8') . "</span></label>";
							echo "<button type=\"submit\" class=\"font-delete\" name=\"delete-font\" value=\"1\" onClick=\"return deleteFont(" . htmlspecialchars(json_encode($font), ENT_QUOTES, 'UTF-8') . ");\">Delete</button>";
							echo "</div>\n";
						}
					}
				?>
				</div>
				
				&nbsp;<br>
				
				<div class="upload-control">
					<input type="submit" value="Upload TTF File" name="submit-file" onClick="return validateUpload();"> <input type="file" name="fileToUpload" id="fileToUpload">
				</div>
				<p id="upload-notice" class="upload-notice" hidden></p>
			</div>
			<div class="zone-controls">
				<div class="controls-grid">

					<div class="control-group">
						<h3>Device</h3>
						<div class="control-row">
							<label class="control-label" for="device">Device</label>
							<select name="device" id="device" onChange="updateDevice()">
								<?php
									foreach ($devices as $device_id => $device) {
										$selected = ($device_id == $selected_device) ? " selected" : "";
										echo "<option value=\"$device_id\"$selected>" . $device["name"] . " (" . $device["width"] . "x" . $device["height"] . ")</option>\n";
									}
								?>
							</select>
						</div>
						<div class="control-row">
							<span class="control-label">Rotation</span>
							<input type="button" id="rotate-button" value="Rotate <?php echo $selected_rotation; ?> deg" onClick="rotateDevice()">
							<input type="hidden" id="rotation" value="<?php echo $selected_rotation; ?>">
						</div>
						<div class="control-row">
							<label class="control-label" for="background">Background</label>
							<select name="background" id="background" onChange="updatePreviewSettings()">
								<?php
									foreach ($colors as $color_id => $color) {
										$selected = ($color_id == $selected_background) ? " selected" : "";
										echo "<option value=\"$color_id\"$selected>" . $color["name"] . "</option>\n";
									}
								?>
							</select>
						</div>
						<div class="control-row">
							<label class="control-label" for="foreground">Foreground</label>
							<select name="foreground" id="foreground" onChange="updatePreviewSettings()">
								<?php
									foreach ($colors as $color_id => $color) {
										$selected = ($color_id == $selected_foreground) ? " selected" : "";
										echo "<option value=\"$color_id\"$selected>" . $color["name"] . "</option>\n";
									}
								?>
							</select>
						</div>
					</div>

					<div class="control-group">
						<h3>Image Size</h3>
						<div class="control-row">
							<label class="control-label" for="size-mode">Size mode</label>
							<select name="size-mode" id="size-mode" onChange="savePreviewSettings(true)">
								<option value="half"<?php if ($selected_size_mode == "half") echo " selected"; ?>>Half (50%)</option>
								<option value="full"<?php if ($selected_size_mode == "full") echo " selected"; ?>>Full (100%)</option>
								<option value="physical"<?php if ($selected_size_mode == "physical") echo " selected"; ?><?php if (!$device_supports_physical_size) echo " disabled"; ?>>Match physical size</option>
							</select>
						</div>
						<div class="control-row<?php if (!$device_supports_physical_size || $selected_size_mode != "physical") echo " is-disabled"; ?>">
							<label class="control-label" for="display-scale">Display scale</label>
							<select name="display-scale" id="display-scale" onChange="savePreviewSettings(true)"<?php if (!$device_supports_physical_size || $selected_size_mode != "physical") echo " disabled"; ?>>
								<?php
									foreach ($display_scales as $display_id => $display) {
										$selected = ($display_id == $selected_display_scale) ? " selected" : "";
										$label = ($display_id == "custom") ? $display["name"] : $display["name"] . " (" . intval($display["scale"] * 100) . "%)";
										echo "<option value=\"$display_id\"$selected>" . $label . "</option>\n";
									}
								?>
							</select>
						</div>
						<div class="control-row<?php if (!$device_supports_physical_size || $selected_size_mode != "physical" || $selected_display_scale != "custom") echo " is-disabled"; ?>">
							<label class="control-label" for="custom-scale">Custom scale</label>
							<input type="text" name="custom-scale" id="custom-scale" value="<?php echo $selected_custom_scale; ?>" onChange="savePreviewSettings(true)"<?php if (!$device_supports_physical_size || $selected_size_mode != "physical" || $selected_display_scale != "custom") echo " disabled"; ?>> %
						</div>
					</div>

					<div class="control-group span-2">
						<h3>Preview Text</h3>
						<div class="control-row">
							<textarea name="text" id="textfield" rows="8" onInput="updateImage()"></textarea>
						</div>
						<div class="options-split">
							<div class="options-col">
								<div class="control-row">
									<label class="control-label" for="sizefield">Font size (points)</label>
									<input type="number" name="size" id="sizefield" value="20" min="3" max="200" step="1" onInput="updateImage()"> points
								</div>
								<div class="control-row">
									<span class="control-label">Vertical</span>
									<?php
										foreach ($vertical_positions as $position_id => $position_name) {
											$checked = ($position_id == $selected_vertical) ? " checked" : "";
											echo "<label class=\"option-label\" for=\"vertical-$position_id\"><input type=\"radio\" id=\"vertical-$position_id\" name=\"vertical\" value=\"$position_id\"$checked onChange=\"updatePreviewSettings()\"> $position_name</label>";
										}
									?>
								</div>
								<div class="control-row">
									<span class="control-label">Horizontal</span>
									<?php
										foreach ($horizontal_positions as $position_id => $position_name) {
											$checked = ($position_id == $selected_horizontal) ? " checked" : "";
											echo "<label class=\"option-label\" for=\"horizontal-$position_id\"><input type=\"radio\" id=\"horizontal-$position_id\" name=\"horizontal\" value=\"$position_id\"$checked onChange=\"updatePreviewSettings()\"> $position_name</label>";
										}
									?>
								</div>
							</div>
							<div class="options-col">
								<div class="control-row">
									<label class="option-label" for="word-wrap"><input type="checkbox" id="word-wrap" value="on" onChange="updatePreviewSettings()"<?php if ($selected_word_wrap == "on") echo " checked"; ?>> Word wrap</label>
								</div>
								<div class="control-row">
									<span class="control-label">Glyph rendering</span>
									<input type="button" id="pixelate-button" value="<?php echo ($selected_pixelate == "on") ? "Smooth preview" : "Pixelate preview"; ?>" onClick="togglePixelate()">
									<input type="hidden" id="pixelate" value="<?php echo $selected_pixelate; ?>">
								</div>
								<div class="control-row">
									<span class="control-label">Pattern Tests</span>
									<span class="pattern-tests">
										<button type="button" class="pattern-button" title="Cross-hatch pattern" onMouseDown="showPattern('cross')" onMouseUp="clearPattern()" onMouseLeave="clearPattern()" onTouchStart="showPattern('cross'); return false;" onTouchEnd="clearPattern()">&times;</button>
										<button type="button" class="pattern-button" title="Grid pattern" onMouseDown="showPattern('grid')" onMouseUp="clearPattern()" onMouseLeave="clearPattern()" onTouchStart="showPattern('grid'); return false;" onTouchEnd="clearPattern()">+</button>
										<button type="button" class="pattern-button" title="Moire animation" onClick="playMoire()">&lowast;</button>
									</span>
								</div>
								<div class="control-row">
									<input type="submit" id="get-font" value="Get GFX font file" name="get-font">
								</div>
							</div>
						</div>
					</div>

				</div>

			</div>

			</form>

			<div class="zone-preview">
				<div class="preview-stage">
					<div id="device-frame">
						<img id="device-background" src="<?php echo $devices[$selected_device]["image"]; ?>">
						<div id="screen-fill" aria-hidden="true"></div>
						<img id="image" src="image.php" onload="previewLoaded()" onerror="previewError()">
						<div id="preview-loading" class="preview-loading" aria-hidden="true"><span class="preview-spinner"></span></div>
						<div id="preview-placeholder" class="preview-placeholder" hidden>Preview unavailable</div>
					</div>
						<div class="preview-status-stack">
							<div class="preview-status" id="preview-status">
								Size: <?php echo htmlspecialchars($selected_size_mode, ENT_QUOTES, 'UTF-8'); ?> &middot; Rotation: <?php echo htmlspecialchars($selected_rotation, ENT_QUOTES, 'UTF-8'); ?>&deg;
							</div>
							<div class="preview-status preview-status-meta" id="preview-resolution">
								Resolution: <?php echo htmlspecialchars($preview_resolution, ENT_QUOTES, 'UTF-8'); ?>
							</div>
							<?php if ($use_physical_size) { ?>
							<div class="preview-status preview-status-meta" id="preview-dimensions">
								Device: <?php echo htmlspecialchars($preview_device_dimensions, ENT_QUOTES, 'UTF-8'); ?> &middot; Display: <?php echo htmlspecialchars($preview_display_dimensions, ENT_QUOTES, 'UTF-8'); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>

		</div>

		<div class="info-section">
	
&nbsp<br>	
			
<h3>Introducing truetype2gfx</h3>

<p>Many Arduino projects and ready-built devices come with a display. And the Adafruit GFX display driver is used by many of them to display variable-width fonts. Some fonts usually are included with the driver, and then there's a complicated procedure for adding your own fonts. It involves compiling tools and a trial-and-error process for figuring out how big the font will turn out on your display.</p>
			
<p>But now you can skip all that and convert the fonts your Arduino project needs with ease. No need to compile tools, no more guessing how big a font will be. Simply select a FreeFont or upload any TrueType font, select a size, download the include file and you're ready to use the font in your project.</p>

<h3>The size thing</h3>

<p>Font sizes are given in points, where a point is 1/72 of an inch, describing the physical size on a display. Or that's what it's supposed to mean, but pretty much everyone that uses the Adafruit software keeps the setting of 141 pixels per inch. In the Adafruit software it says:</p>

<blockquote><code>#define DPI 141 // Approximate res. of Adafruit 2.8" TFT </code></blockquote>

<p>But since everyone keeps the setting, a certain font at 20 points is going to take up the same number of pixels on a 
lot of devices. And then there's the different fonts displaying at radically different sizes due to various metrics 
included in the font. (See <a href="https://iamvdo.me/en/blog/css-font-metrics-line-height-and-vertical-align">here</a> 
for details.) But I don't have to care about that: when I make gfx fonts and include them on my device, they are the 
same size as they are on the virtual device on the screen above. (This only works if your screen is 320x240 pixels. If your screen dimensions are different, you can still see the size relative to the FreeFonts of a given size.)</p>

<h3>Your own fonts</h3>

<p>TrueType fonts are everywhere online. At the time of writing this, you can get loads and loads of pretty TrueType fonts <a href="https://www.1001freefonts.com">here</a> but you can also pick up fonts at any of <a href = "https://www.google.de/search?q=truetype+free+fonts">these sites</a>. (Beware of malware: do not unpack ".exe archives" or do anything else silly with files downloaded from these sites.)</p>

<p>Using this tool, you can upload and then view and convert up to five fonts (which are only available to you). If you upload a sixth font, the first one disappears. Also note that these fonts will only last as long as your PHP session does, so whenever you come back a day later, your fonts may be gone. It's really only meant to be a short-term buffer.</p>

<h3>Example</h3>

<p>I found a nice font on this website listed above. It was called "Black Street" and the font file I uploaded was 
"Black Street.ttf". I fiddled with the size until it filled the display nicely, at 35 points. I then hit the "Get GFX font file" button and my browser downloaded a file called "Black_Street35pt7b.h". I created a new Arduino sketch with the following content:</p>

<blockquote><pre>
#include &lt;M5Stack.h&gt;
#include "Black_Street35pt7b.h"

void setup
  m5.begin();
  m5.lcd.fillScreen(TFT_WHITE);
  m5.lcd.setTextColor(TFT_BLACK);
  m5.lcd.setTextDatum(CC_DATUM);
  m5.lcd.setFreeFont(&Black_Street35pt7b);
  m5.lcd.drawString("Testing 123...", 160, 120);
}

void loop() {
}
</pre></blockquote>
			
<p>I then added the "Black_Street35p7b.h" from my "Download" directory as a second tab with "Sketch / Add file..." in the Arduino IDE, ran the program et voila:</p>

<img src="truetype2gfx_demo.png">

<p><i>(If you do not have an M5Stack but some other device your library will not be called M5Stack.h and your display will not be at "m5.lcd", but you'll figure it out...)</i></p>

<h3>Source code, bug reports, questions, etc..</h3>

<p>This tool has a <a href="https://github.com/ropg/truetype2gfx">github repository</a> that has the (quick-hack-style) PHP/Javascript code behind all this. And if you have any questions, bug reports or suggestions, simply <a href="https://github.com/ropg/truetype2gfx/issues/new">open a new issue</a> there and I will see what I can do. </p>

			</div>

	</div>
	
	
	
	<script>		
	
		function updateImage() {
			syncScreenFill();
			saveFormState();
			var loading = document.getElementById("preview-loading");
			if (loading) loading.classList.add("is-loading");
			document.getElementById("image").src = "image.php?device=" + encodeURIComponent(device()) + "&font=" + encodeURIComponent(font()) + "&size=" + encodeURIComponent(document.getElementById("sizefield").value) + "&text=" + encodeURIComponent(previewText()) + "&background=" + encodeURIComponent(background()) + "&foreground=" + encodeURIComponent(foreground()) + "&vertical=" + encodeURIComponent(vertical()) + "&horizontal=" + encodeURIComponent(horizontal()) + "&word_wrap=" + encodeURIComponent(wordWrap()) + "&pixelate=" + encodeURIComponent(pixelate()) + "&rotation=" + encodeURIComponent(rotation()) + "#" + new Date().getTime();
		}

		function syncScreenFill() {
			var fill = document.getElementById("screen-fill");
			if (fill) fill.style.backgroundColor = background();
		}

		function previewText() {
			return document.getElementById("textfield").value.replace(/\r\n/g, "\\n").replace(/\r/g, "\\n").replace(/\n/g, "\\n");
		}

		function previewLoaded() {
			var loading = document.getElementById("preview-loading");
			if (loading) loading.classList.remove("is-loading");
			var placeholder = document.getElementById("preview-placeholder");
			if (placeholder) placeholder.hidden = true;
			var img = document.getElementById("image");
			if (img) img.style.visibility = "visible";
		}

		function previewError() {
			var loading = document.getElementById("preview-loading");
			if (loading) loading.classList.remove("is-loading");
			var placeholder = document.getElementById("preview-placeholder");
			if (placeholder) placeholder.hidden = false;
			var img = document.getElementById("image");
			if (img) img.style.visibility = "hidden";
		}

		// Pattern tests: while a button is held, show a cross-hatch or grid
		// test pattern on the device display; restore the normal preview on release.
		function showPattern(pattern) {
			syncScreenFill();
			document.getElementById("image").src = "image.php?device=" + encodeURIComponent(device()) + "&background=" + encodeURIComponent(background()) + "&foreground=" + encodeURIComponent(foreground()) + "&pixelate=" + encodeURIComponent(pixelate()) + "&rotation=" + encodeURIComponent(rotation()) + "&pattern=" + encodeURIComponent(pattern) + "#" + new Date().getTime();
		}

		function clearPattern() {
			updateImage();
		}

		// ZX-Spectrum-style XOR moire: build the pattern line-by-line over 5s,
		// hold for 5s, then restore the normal preview. Single click; auto-completes.
		var moirePlaying = false;
		function playMoire() {
			if (moirePlaying) return;
			moirePlaying = true;
			var img = document.getElementById("image");
			var total = 100;
			var buildMs = 5000;
			var step = 0;
			var base = "image.php?device=" + encodeURIComponent(device()) + "&background=" + encodeURIComponent(background()) + "&foreground=" + encodeURIComponent(foreground()) + "&pixelate=" + encodeURIComponent(pixelate()) + "&rotation=" + encodeURIComponent(rotation()) + "&pattern=moire";
			function drawNext() {
				step++;
				img.src = base + "&lines=" + step + "#" + new Date().getTime();
				if (step < total) {
					setTimeout(drawNext, buildMs / total);
				} else {
					setTimeout(function() {
						moirePlaying = false;
						clearPattern();
					}, 5000);
				}
			}
			drawNext();
		}

		function updateDevice() {
			var request = new XMLHttpRequest();
			request.open("POST", "index.php", true);
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			request.onload = function() {
				reloadPage();
			};
			request.send("set-device=1&device=" + encodeURIComponent(device()));
		}

		function reloadPage() {
			var url = new URL(window.location.href);
			url.searchParams.set("_reload", new Date().getTime());
			window.location.replace(url.toString());
		}

		function device() {
			return document.getElementById("device").value;
		}

		function updatePreviewSettings() {
			savePreviewSettings(false);
		}

		function savePreviewSettings(reload) {
			saveFormState();
			var request = new XMLHttpRequest();
			request.open("POST", "index.php", true);
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			request.onload = function() {
				if (reload) {
					reloadPage();
				} else {
					updateImage();
				}
			};
			request.send("set-preview-settings=1&background=" + encodeURIComponent(background()) + "&foreground=" + encodeURIComponent(foreground()) + "&vertical=" + encodeURIComponent(vertical()) + "&horizontal=" + encodeURIComponent(horizontal()) + "&size_mode=" + encodeURIComponent(sizeMode()) + "&display_scale=" + encodeURIComponent(displayScale()) + "&custom_scale=" + encodeURIComponent(customScale()) + "&word_wrap=" + encodeURIComponent(wordWrap()) + "&pixelate=" + encodeURIComponent(pixelate()) + "&rotation=" + encodeURIComponent(rotation()));
		}

		function background() {
			return document.getElementById("background").value;
		}

		function foreground() {
			return document.getElementById("foreground").value;
		}

		function sizeMode() {
			return document.getElementById("size-mode").value;
		}

		function displayScale() {
			return document.getElementById("display-scale").value;
		}

		function customScale() {
			var field = document.getElementById("custom-scale");
			var value = parseInt(field.value, 10);
			if (isNaN(value)) value = 100;
			if (value > 1000) value = 1000;
			if (value < 50) value = 50;
			field.value = value;
			return value;
		}

		function wordWrap() {
			return document.getElementById("word-wrap").checked ? "on" : "off";
		}

		function togglePixelate() {
			var current = document.getElementById("pixelate").value;
			document.getElementById("pixelate").value = (current == "on") ? "off" : "on";
			savePreviewSettings(true);
		}

		function pixelate() {
			return document.getElementById("pixelate").value;
		}

		function rotateDevice() {
			var rotations = ["0", "90", "180", "270"];
			var current = document.getElementById("rotation").value;
			var index = rotations.indexOf(current);
			document.getElementById("rotation").value = rotations[(index + 1) % rotations.length];
			savePreviewSettings(true);
		}

		function rotation() {
			return document.getElementById("rotation").value;
		}

		function vertical() {
			return checkedValue("vertical");
		}

		function horizontal() {
			return checkedValue("horizontal");
		}

		function checkedValue(name) {
			var options = document.getElementsByName(name);
			for (var i = 0, length = options.length; i < length; i++) {
				if (options[i].checked) return options[i].value;
			}
			return "centre";
		}
	
		function font() {
			var fonts = document.getElementsByName('font');
			for (var i = 0, length = fonts.length; i < length; i++) {
				if (fonts[i].checked) {
					return fonts[i].value;
				}
			}
			return "";
		}
		
		function setFont() {
			restoreFormState();
			var e = document.getElementsByName("font");
			for (var i = 0; i < e.length; i++) {
				if (e[i].value == "<?php echo $select_font?>") {
					e[i].checked = true;
					break;
				}
			}
			updateImage();
		}

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
			if (storedFont !== null) {
				var fonts = document.getElementsByName("font");
				for (var i = 0; i < fonts.length; i++) {
					if (fonts[i].value == storedFont) {
						fonts[i].checked = true;
						break;
					}
				}
			}
		}

		function deleteFont(fontName) {
			document.getElementById("font-to-delete").value = fontName;
			if (window.localStorage && localStorage.getItem("truetype2gfx.font") == "user/" + fontName) {
				localStorage.removeItem("truetype2gfx.font");
			}
			return confirm("Delete " + fontName + "?");
		}
		
		function validateUpload() {
  			var file = document.getElementById("fileToUpload").value;
			var reg = /(.*?)\.(ttf|TTF)$/;
			var notice = document.getElementById("upload-notice");
			if(!file.match(reg)) {
				if (notice) {
					notice.textContent = "You can only upload a TrueType font (.ttf or .TTF extension)";
					notice.hidden = false;
				}
				return false;
			}
			if (notice) notice.hidden = true;
		}
		
	</script>
</body>

</html>
