<?php
// Set the enviroment variable for GD
putenv('GDFONTPATH=' . realpath('./fonts'));

// Set the content-type
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');

$devices = array(
    "m5stack" => array("width" => 320, "height" => 240),
    "m5stickcplus" => array("width" => 135, "height" => 240)
);

$colors = array(
    "black" => array(0, 0, 0),
    "white" => array(255, 255, 255),
    "red" => array(255, 0, 0),
    "green" => array(0, 255, 0),
    "blue" => array(0, 0, 255),
    "cyan" => array(0, 255, 255),
    "magenta" => array(255, 0, 255),
    "yellow" => array(255, 255, 0)
);

$vertical_positions = array("top", "centre", "bottom");
$horizontal_positions = array("left", "centre", "right");

$device = "m5stack";
if (isset($_GET["device"]) && isset($devices[$_GET["device"]])) {
    $device = $_GET["device"];
}

$background_color = "white";
if (isset($_GET["background"]) && isset($colors[$_GET["background"]])) {
    $background_color = $_GET["background"];
}

$foreground_color = "black";
if (isset($_GET["foreground"]) && isset($colors[$_GET["foreground"]])) {
    $foreground_color = $_GET["foreground"];
}

$vertical = "centre";
if (isset($_GET["vertical"]) && in_array($_GET["vertical"], $vertical_positions)) {
    $vertical = $_GET["vertical"];
}

$horizontal = "centre";
if (isset($_GET["horizontal"]) && in_array($_GET["horizontal"], $horizontal_positions)) {
    $horizontal = $_GET["horizontal"];
}

$word_wrap = false;
if (isset($_GET["word_wrap"]) && $_GET["word_wrap"] === "on") {
    $word_wrap = true;
}

$pixelate = false;
if (isset($_GET["pixelate"]) && $_GET["pixelate"] === "on") {
    $pixelate = true;
}

$pattern = "";
if (isset($_GET["pattern"]) && in_array($_GET["pattern"], array("cross", "grid", "moire"))) {
    $pattern = $_GET["pattern"];
}

$pattern_lines = 0;
if (isset($_GET["lines"]) && is_numeric($_GET["lines"])) {
    $pattern_lines = max(0, min(100, intval($_GET["lines"])));
}

$rotation = "0";
if (isset($_GET["rotation"]) && in_array($_GET["rotation"], array("0", "90", "180", "270"))) {
    $rotation = $_GET["rotation"];
}

// Create the image
$image_width = $devices[$device]["width"];
$image_height = $devices[$device]["height"];
if ($rotation === "90" || $rotation === "270") {
    $image_width = $devices[$device]["height"];
    $image_height = $devices[$device]["width"];
}
$im = imagecreatetruecolor($image_width, $image_height);

$dpi = 141;
if (isset($_GET["dpi"]) && is_numeric($_GET["dpi"]) && $_GET["dpi"] > 0 && $_GET["dpi"] <= 300) {
    $dpi = intval($_GET["dpi"]);
}

$text = '';
if (isset($_GET["text"])) {
    $text = htmlspecialchars($_GET["text"], ENT_QUOTES, 'UTF-8');
    if (strlen($text) > 100) $text = substr($text, 0, 100);
}
$text = str_replace("\\n", "\n", $text);
$text = str_replace(array("\r\n", "\r"), "\n", $text);

$size = (20 * $dpi) / 96;
if (isset($_GET["size"]) && is_numeric($_GET["size"]) && $_GET["size"] >= 3 && $_GET["size"] <= 200) {
    $size = (intval($_GET["size"]) * $dpi) / 96;
}

$font = 'FreeSans.ttf';
if (isset($_GET["font"])) {
    $requested_font = $_GET["font"];
    // Only allow specific font files
    $allowed_fonts = [
        'FreeSans.ttf', 'FreeSansBold.ttf', 'FreeSansBoldOblique.ttf', 'FreeSansOblique.ttf',
        'FreeSerif.ttf', 'FreeSerifBold.ttf', 'FreeSerifBoldItalic.ttf', 'FreeSerifItalic.ttf',
        'FreeMono.ttf', 'FreeMonoBold.ttf', 'FreeMonoBoldOblique.ttf', 'FreeMonoOblique.ttf'
    ];
    
    // Check if it's a user font (starts with 'user/')
    if (strpos($requested_font, 'user/') === 0) {
        $user_font = basename(substr($requested_font, 5));
        if (preg_match('/^[a-zA-Z0-9 ._-]+\\.ttf$/i', $user_font) && file_exists('./fonts/user/' . $user_font)) {
            $font = 'user/' . $user_font;
        } else {
            $font = 'FreeSans.ttf';
        }
    } elseif (in_array(basename($requested_font), $allowed_fonts)) {
        $font = basename($requested_font);
    } else {
        $font = 'FreeSans.ttf';
    }
}

$font_path = realpath('./fonts/' . $font);
if ($font_path === false) {
    $font_path = realpath('./fonts/FreeSans.ttf');
}

function text_width($size, $font_path, $text) {
    if ($text === "") return 0;
    $bbox = imagettfbbox($size, 0, $font_path, $text);
    return abs($bbox[4] - $bbox[0]);
}

function split_word_to_width($word, $max_width, $size, $font_path) {
    $chunks = array();
    while ($word !== "") {
        $low = 1;
        $high = strlen($word);
        $fit = 0;
        while ($low <= $high) {
            $mid = intval(($low + $high) / 2);
            $candidate = substr($word, 0, $mid);
            if (text_width($size, $font_path, $candidate) <= $max_width) {
                $fit = $mid;
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        $split = $fit + 1;
        if ($split > strlen($word)) $split = strlen($word);
        if ($split < 1) $split = 1;
        $chunks[] = substr($word, 0, $split);
        $word = substr($word, $split);
    }
    return $chunks;
}

function wrap_line_to_width($line, $max_width, $size, $font_path) {
    if ($line === "") return array("");
    $wrapped = array();
    $words = preg_split('/\\s+/', trim($line));
    $current = "";

    foreach ($words as $word) {
        if ($word === "") continue;
        if (text_width($size, $font_path, $word) > $max_width) {
            if ($current !== "") {
                $wrapped[] = $current;
                $current = "";
            }
            $chunks = split_word_to_width($word, $max_width, $size, $font_path);
            foreach ($chunks as $index => $chunk) {
                if ($index === count($chunks) - 1) {
                    $current = $chunk;
                } else {
                    $wrapped[] = $chunk;
                }
            }
            continue;
        }

        $candidate = ($current === "") ? $word : $current . " " . $word;
        if (text_width($size, $font_path, $candidate) <= $max_width) {
            $current = $candidate;
        } else {
            if ($current !== "") $wrapped[] = $current;
            $current = $word;
        }
    }

    if ($current !== "") $wrapped[] = $current;
    if (count($wrapped) === 0) $wrapped[] = "";
    return $wrapped;
}

// Create some colors
$bg_rgb = $colors[$background_color];
$fg_rgb = $colors[$foreground_color];
$background = imagecolorallocate($im, $bg_rgb[0], $bg_rgb[1], $bg_rgb[2]);
$foreground = imagecolorallocate($im, $fg_rgb[0], $fg_rgb[1], $fg_rgb[2]);
imagefilledrectangle($im, 0, 0, imagesx($im) - 1, imagesy($im) - 1, $background);

// Pattern tests: 1px-wide foreground lines with a 1px gap between them.
// "cross" = checkerboard, "grid" = horizontal + vertical grid,
// "moire" = ZX-Spectrum-style XOR (OVER 1) interference pattern, drawn
// progressively (the frontend requests an increasing "lines" count).
if ($pattern !== "") {
    $w = imagesx($im);
    $h = imagesy($im);
    if ($pattern === "grid") {
        for ($x = 0; $x < $w; $x += 2) imageline($im, $x, 0, $x, $h - 1, $foreground);
        for ($y = 0; $y < $h; $y += 2) imageline($im, 0, $y, $w - 1, $y, $foreground);
    } else if ($pattern === "moire") {
        // Classic ZX-Spectrum XOR (OVER 1) moire. Lines are full diagonals whose
        // two endpoints sweep along opposite edges in opposite directions,
        // pivoting to form an X. First half: left endpoint moves down the left
        // edge while the right endpoint moves up the right edge (start with the
        // main diagonal 0,0 -> maxX,maxY). Second half: top endpoint moves right
        // along the top edge while the bottom endpoint moves left along the
        // bottom edge. "lines" is a 0..100 progress percentage so the frontend
        // can animate the screen building up.
        $segments = array();
        for ($y = 0; $y < $h; $y++) $segments[] = array(0, $y, $w - 1, $h - 1 - $y);       // first half
        for ($x = 0; $x < $w; $x++) $segments[] = array($x, 0, $w - 1 - $x, $h - 1);        // second half
        $total = count($segments);
        $draw = intval(($pattern_lines / 100) * $total);
        if ($draw > $total) $draw = $total;
        // Toggle a pixel between foreground/background (XOR / "OVER 1").
        $xor_pixel = function ($x, $y) use ($im, $foreground, $background, $fg_rgb, $w, $h) {
            if ($x < 0 || $y < 0 || $x >= $w || $y >= $h) return;
            $cur = imagecolorat($im, $x, $y);
            $r = ($cur >> 16) & 0xFF; $g = ($cur >> 8) & 0xFF; $b = $cur & 0xFF;
            $is_fg = ($r === $fg_rgb[0] && $g === $fg_rgb[1] && $b === $fg_rgb[2]);
            imagesetpixel($im, $x, $y, $is_fg ? $background : $foreground);
        };
        // Bresenham line with XOR plotting.
        $xor_line = function ($x0, $y0, $x1, $y1) use ($xor_pixel) {
            $dx = abs($x1 - $x0); $dy = -abs($y1 - $y0);
            $sx = $x0 < $x1 ? 1 : -1; $sy = $y0 < $y1 ? 1 : -1;
            $err = $dx + $dy;
            while (true) {
                $xor_pixel($x0, $y0);
                if ($x0 === $x1 && $y0 === $y1) break;
                $e2 = 2 * $err;
                if ($e2 >= $dy) { $err += $dy; $x0 += $sx; }
                if ($e2 <= $dx) { $err += $dx; $y0 += $sy; }
            }
        };
        for ($i = 0; $i < $draw; $i++) {
            $xor_line($segments[$i][0], $segments[$i][1], $segments[$i][2], $segments[$i][3]);
        }
    } else { // checkerboard: alternating single foreground/background pixels
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ((($x + $y) & 1) === 0) imagesetpixel($im, $x, $y, $foreground);
            }
        }
    }
    imagepng($im);
    imagedestroy($im);
    exit();
}

$mask = null;
if ($pixelate) {
    $mask = imagecreatetruecolor(imagesx($im), imagesy($im));
    $mask_background = imagecolorallocate($mask, 0, 0, 0);
    imagefilledrectangle($mask, 0, 0, imagesx($mask) - 1, imagesy($mask) - 1, $mask_background);
    $mask_foreground = imagecolorallocate($mask, 255, 255, 255);
}

$lines = explode("\n", $text);
if ($word_wrap) {
    $wrapped_lines = array();
    $max_text_width = imagesx($im) - 8;
    foreach ($lines as $line) {
        $wrapped_lines = array_merge($wrapped_lines, wrap_line_to_width($line, $max_text_width, $size, $font_path));
    }
    $lines = $wrapped_lines;
}
$line_metrics = array();
$block_width = 0;
$block_height = 0;

foreach ($lines as $line) {
    $metric_line = ($line === "") ? " " : $line;
    $bbox = imagettfbbox($size, 0, $font_path, $metric_line);
    $line_width = abs($bbox[4] - $bbox[0]);
    $line_height = abs($bbox[5] - $bbox[1]);
    if ($word_wrap && $line_width > imagesx($im) - 8) $line_width = imagesx($im) - 8;
    $line_metrics[] = array("text" => $line, "bbox" => $bbox, "width" => $line_width, "height" => $line_height);
    if ($line_width > $block_width) $block_width = $line_width;
    $block_height += $line_height;
}

$line_gap = max(2, intval($size * 0.25));
if (count($line_metrics) > 1) $block_height += $line_gap * (count($line_metrics) - 1);

$padding = 4;
if ($vertical === "top") {
    $y = $padding;
} elseif ($vertical === "bottom") {
    $y = imagesy($im) - $block_height - $padding;
} else {
    $y = (imagesy($im) / 2) - ($block_height / 2);
}

foreach ($line_metrics as $line_metric) {
    $bbox = $line_metric["bbox"];
    if ($horizontal === "left") {
        $x = $padding - $bbox[0];
    } elseif ($horizontal === "right") {
        $x = imagesx($im) - $line_metric["width"] - $padding - $bbox[0];
    } else {
        $x = (imagesx($im) / 2) - ($line_metric["width"] / 2) - ($bbox[0] / 2);
    }
    $baseline = $y - $bbox[5];
    if ($pixelate) {
        imagettftext($mask, $size, 0, $x, $baseline, $mask_foreground, $font_path, $line_metric["text"]);
    } else {
        imagettftext($im, $size, 0, $x, $baseline, $foreground, $font_path, $line_metric["text"]);
    }
    $y += $line_metric["height"] + $line_gap;
}

if ($pixelate) {
    for ($y = 0; $y < imagesy($mask); $y++) {
        for ($x = 0; $x < imagesx($mask); $x++) {
            $pixel = imagecolorat($mask, $x, $y);
            $r = ($pixel >> 16) & 0xFF;
            $g = ($pixel >> 8) & 0xFF;
            $b = $pixel & 0xFF;
            if (($r + $g + $b) >= 384) {
                imagesetpixel($im, $x, $y, $foreground);
            }
        }
    }
    imagedestroy($mask);
}

// Using imagepng() results in clearer text compared with imagejpeg()
imagepng($im);
imagedestroy($im);
?>
