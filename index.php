<?php

session_start();

if (isset($_GET['reset'])) unset($_SESSION['fonts']);

$devices = array(
	"m5stack" => array(
		"name" => "M5Stack",
		"image" => "M5Stack-bg.png",
		"width" => 320,
		"height" => 240,
		"frame_width" => 425,
		"frame_height" => 429,
		"screen_left" => 50,
		"screen_top" => 90,
		"screen_width" => 320,
		"screen_height" => 240
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
	"standard" => array("name" => "Standard display", "scale" => 1.0),
	"imac5k" => array("name" => "iMac 5K Retina", "scale" => 1.18)
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
$preview_settings = array("background" => "white", "foreground" => "black", "vertical" => "centre", "horizontal" => "centre", "size_mode" => "comfortable", "display_scale" => "standard", "word_wrap" => "off", "pixelate" => "off", "output_pixelate" => "off", "rotation" => "0");
if (file_exists($settings_state_file)) {
	$saved_settings = json_decode(file_get_contents($settings_state_file), true);
	if (is_array($saved_settings)) {
		if (isset($saved_settings["background"]) && isset($colors[$saved_settings["background"]])) $preview_settings["background"] = $saved_settings["background"];
		if (isset($saved_settings["foreground"]) && isset($colors[$saved_settings["foreground"]])) $preview_settings["foreground"] = $saved_settings["foreground"];
		if (isset($saved_settings["vertical"]) && isset($vertical_positions[$saved_settings["vertical"]])) $preview_settings["vertical"] = $saved_settings["vertical"];
		if (isset($saved_settings["horizontal"]) && isset($horizontal_positions[$saved_settings["horizontal"]])) $preview_settings["horizontal"] = $saved_settings["horizontal"];
		if (isset($saved_settings["size_mode"]) && in_array($saved_settings["size_mode"], array("comfortable", "large", "actual"))) $preview_settings["size_mode"] = $saved_settings["size_mode"];
		if (isset($saved_settings["physical_size"]) && $saved_settings["physical_size"] == "on") $preview_settings["size_mode"] = "actual";
		if (isset($saved_settings["display_scale"]) && isset($display_scales[$saved_settings["display_scale"]])) $preview_settings["display_scale"] = $saved_settings["display_scale"];
		if (isset($saved_settings["word_wrap"]) && in_array($saved_settings["word_wrap"], array("off", "on"))) $preview_settings["word_wrap"] = $saved_settings["word_wrap"];
		if (isset($saved_settings["pixelate"]) && in_array($saved_settings["pixelate"], array("off", "on"))) $preview_settings["pixelate"] = $saved_settings["pixelate"];
		if (isset($saved_settings["output_pixelate"]) && in_array($saved_settings["output_pixelate"], array("off", "on"))) $preview_settings["output_pixelate"] = $saved_settings["output_pixelate"];
		if (isset($saved_settings["rotation"]) && in_array($saved_settings["rotation"], array("0", "90", "180", "270"))) $preview_settings["rotation"] = $saved_settings["rotation"];
	}
}

if (!isset($_SESSION["preview-settings"])) $_SESSION["preview-settings"] = $preview_settings;
if (!isset($colors[$_SESSION["preview-settings"]["background"]])) $_SESSION["preview-settings"]["background"] = $preview_settings["background"];
if (!isset($colors[$_SESSION["preview-settings"]["foreground"]])) $_SESSION["preview-settings"]["foreground"] = $preview_settings["foreground"];
if (!isset($vertical_positions[$_SESSION["preview-settings"]["vertical"]])) $_SESSION["preview-settings"]["vertical"] = $preview_settings["vertical"];
if (!isset($horizontal_positions[$_SESSION["preview-settings"]["horizontal"]])) $_SESSION["preview-settings"]["horizontal"] = $preview_settings["horizontal"];
if (!isset($_SESSION["preview-settings"]["size_mode"]) || !in_array($_SESSION["preview-settings"]["size_mode"], array("comfortable", "large", "actual"))) $_SESSION["preview-settings"]["size_mode"] = $preview_settings["size_mode"];
if (!isset($_SESSION["preview-settings"]["display_scale"]) || !isset($display_scales[$_SESSION["preview-settings"]["display_scale"]])) $_SESSION["preview-settings"]["display_scale"] = $preview_settings["display_scale"];
if (!isset($_SESSION["preview-settings"]["word_wrap"]) || !in_array($_SESSION["preview-settings"]["word_wrap"], array("off", "on"))) $_SESSION["preview-settings"]["word_wrap"] = $preview_settings["word_wrap"];
if (!isset($_SESSION["preview-settings"]["pixelate"]) || !in_array($_SESSION["preview-settings"]["pixelate"], array("off", "on"))) $_SESSION["preview-settings"]["pixelate"] = $preview_settings["pixelate"];
if (!isset($_SESSION["preview-settings"]["output_pixelate"]) || !in_array($_SESSION["preview-settings"]["output_pixelate"], array("off", "on"))) $_SESSION["preview-settings"]["output_pixelate"] = $preview_settings["output_pixelate"];
if (!isset($_SESSION["preview-settings"]["rotation"]) || !in_array($_SESSION["preview-settings"]["rotation"], array("0", "90", "180", "270"))) $_SESSION["preview-settings"]["rotation"] = $preview_settings["rotation"];

if (isset($_POST["set-preview-settings"])) {
	if (isset($_POST["background"]) && isset($colors[$_POST["background"]])) $_SESSION["preview-settings"]["background"] = $_POST["background"];
	if (isset($_POST["foreground"]) && isset($colors[$_POST["foreground"]])) $_SESSION["preview-settings"]["foreground"] = $_POST["foreground"];
	if (isset($_POST["vertical"]) && isset($vertical_positions[$_POST["vertical"]])) $_SESSION["preview-settings"]["vertical"] = $_POST["vertical"];
	if (isset($_POST["horizontal"]) && isset($horizontal_positions[$_POST["horizontal"]])) $_SESSION["preview-settings"]["horizontal"] = $_POST["horizontal"];
	if (isset($_POST["size_mode"]) && in_array($_POST["size_mode"], array("comfortable", "large", "actual"))) $_SESSION["preview-settings"]["size_mode"] = $_POST["size_mode"];
	if (isset($_POST["display_scale"]) && isset($display_scales[$_POST["display_scale"]])) $_SESSION["preview-settings"]["display_scale"] = $_POST["display_scale"];
	if (isset($_POST["word_wrap"]) && in_array($_POST["word_wrap"], array("off", "on"))) $_SESSION["preview-settings"]["word_wrap"] = $_POST["word_wrap"];
	if (isset($_POST["pixelate"]) && in_array($_POST["pixelate"], array("off", "on"))) $_SESSION["preview-settings"]["pixelate"] = $_POST["pixelate"];
	if (isset($_POST["output_pixelate"]) && in_array($_POST["output_pixelate"], array("off", "on"))) $_SESSION["preview-settings"]["output_pixelate"] = $_POST["output_pixelate"];
	if (isset($_POST["rotation"]) && in_array($_POST["rotation"], array("0", "90", "180", "270"))) $_SESSION["preview-settings"]["rotation"] = $_POST["rotation"];
	file_put_contents($settings_state_file, json_encode($_SESSION["preview-settings"]));
	exit();
}

$selected_background = $_SESSION["preview-settings"]["background"];
$selected_foreground = $_SESSION["preview-settings"]["foreground"];
$selected_vertical = $_SESSION["preview-settings"]["vertical"];
$selected_horizontal = $_SESSION["preview-settings"]["horizontal"];
$selected_size_mode = $_SESSION["preview-settings"]["size_mode"];
if ($selected_device != "m5stickcplus" && $selected_size_mode == "actual") {
	$selected_size_mode = "comfortable";
	$_SESSION["preview-settings"]["size_mode"] = $selected_size_mode;
}
$selected_display_scale = $_SESSION["preview-settings"]["display_scale"];
$selected_word_wrap = $_SESSION["preview-settings"]["word_wrap"];
$selected_pixelate = $_SESSION["preview-settings"]["pixelate"];
$selected_output_pixelate = $_SESSION["preview-settings"]["output_pixelate"];
$selected_rotation = $_SESSION["preview-settings"]["rotation"];
$physical_scale = $display_scales[$selected_display_scale]["scale"];
$use_physical_size = ($selected_device == "m5stickcplus" && $selected_size_mode == "actual");
$device_scale = ($selected_size_mode == "large") ? 1.0 : 0.5;
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
$output_width = $screen_width;
$output_height = $screen_height;
$background_width = intval($frame_width_raw * $device_scale);
$background_height = intval($frame_height_raw * $device_scale);
$background_left = intval(($frame_width - $background_width) / 2);
$background_top = intval(($frame_height - $background_height) / 2);
if ($use_physical_size) {
	$physical_width = $devices[$selected_device]["physical_width_mm"] * $physical_scale;
	$physical_height = $devices[$selected_device]["physical_height_mm"] * $physical_scale;
	$scale_x = $physical_width / $frame_width_raw;
	$scale_y = $physical_height / $frame_height_raw;
	$frame_width = ($rotation == 90 || $rotation == 270) ? $physical_height . "mm" : $physical_width . "mm";
	$frame_height = ($rotation == 90 || $rotation == 270) ? $physical_width . "mm" : $physical_height . "mm";
	$screen_left = ($rotated_screen_left_raw * (($rotation == 90 || $rotation == 270) ? $scale_y : $scale_x)) . "mm";
	$screen_top = ($rotated_screen_top_raw * (($rotation == 90 || $rotation == 270) ? $scale_x : $scale_y)) . "mm";
	$screen_width = ($rotated_screen_width_raw * (($rotation == 90 || $rotation == 270) ? $scale_y : $scale_x)) . "mm";
	$screen_height = ($rotated_screen_height_raw * (($rotation == 90 || $rotation == 270) ? $scale_x : $scale_y)) . "mm";
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
		body {
			background-color: #000000;
			color: #ffffff;
			margin: 100px;
			margin-top: 100px;
			margin-left: 100px;
			font-family: Verdana, sans-serif;
		}
		a {
			text-decoration: none;
			font-weight: bold;
			color: #8080FF;
		}
		td {
			vertical-align: top;
		}
		table {
			width: 960px;
		}
		td#first {
			margin: 0px;
			width: <?php echo $frame_width; ?>;
			height: <?php echo $frame_height; ?>;
		}
		#device-frame {
			position: relative;
			width: <?php echo $frame_width; ?>;
			height: <?php echo $frame_height; ?>;
			overflow: visible;
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
		#image {
			position: absolute;
			left: <?php echo $screen_left; ?>;
			top: <?php echo $screen_top; ?>;
			width: <?php echo $screen_width; ?>;
			height: <?php echo $screen_height; ?>;
			image-rendering: <?php echo ($selected_pixelate == "on") ? "pixelated" : "auto"; ?>;
			z-index: 2;
		}
		td#second {
			width: 270px;
		}
		td#third {
			width: 210px;
		}
		#textfield {
			width: 66%;
			height: 8em;
		}
		#background, #foreground {
			width: 120px;
		}
		#sizefield {
			width: 35px;
			text-align: center;
		}
		#get-font {
			width: 200px;
			height: 30px;
			font-size: 20px;
			font-weight: bold;
		}
	</style>
</head>

<body onload = 'setFont()'>

	
	&nbsp;<br>

	<table>
		<tr>
			<td colspan=3>
				<h2>truetype2gfx - Converting fonts from TrueType to Adafruit GFX</h2>
				&nbsp;<br>
				&nbsp;<br>
			</td>
		</tr>

		<tr>
			<td id="first">
				<div id="device-frame">
					<img id="device-background" src="<?php echo $devices[$selected_device]["image"]; ?>">
					<img id="image" src="image.php">
				</div>
			</td>
			<td id="second">
			
				<form action="" method="post" enctype="multipart/form-data">
			
				<h3>FreeFonts</h3>
				<input type="radio" name="font" value="FreeSans.ttf" checked onChange="updateImage()">&nbsp;FreeSans<br>
				<input type="radio" name="font" value="FreeSansBold.ttf" onChange="updateImage()">&nbsp;FreeSansBold<br>
				<input type="radio" name="font" value="FreeSansBoldOblique.ttf" onChange="updateImage()">&nbsp;FreeSansBoldOblique<br>
				<input type="radio" name="font" value="FreeSansOblique.ttf" onChange="updateImage()">&nbsp;FreeSansOblique<br>
				<input type="radio" name="font" value="FreeSerif.ttf" onChange="updateImage()">&nbsp;FreeSerif<br>
				<input type="radio" name="font" value="FreeSerifBold.ttf" onChange="updateImage()">&nbsp;FreeSerifBold<br>
				<input type="radio" name="font" value="FreeSerifBoldItalic.ttf" onChange="updateImage()">&nbsp;FreeSerifBoldItalic<br>
				<input type="radio" name="font" value="FreeSerifItalic.ttf" onChange="updateImage()">&nbsp;FreeSerifItalic<br>
				<input type="radio" name="font" value="FreeMono.ttf" onChange="updateImage()">&nbsp;FreeMono<br>
				<input type="radio" name="font" value="FreeMonoBold.ttf" onChange="updateImage()">&nbsp;FreeMonoBold<br>
				<input type="radio" name="font" value="FreeMonoBoldOblique.ttf" onChange="updateImage()">&nbsp;FreeMonoBoldOblique<br>
				<input type="radio" name="font" value="FreeMonoOblique.ttf" onChange="updateImage()">&nbsp;FreeMonoOblique<br>
				
				<h3>Your fonts</h3>
				<input type="hidden" name="font-to-delete" id="font-to-delete" value="">
				<?php
					foreach ($user_fonts as $font) {
						$font_label = str_replace(".TTF", "", str_replace(".ttf", "", $font));
						echo "<input type=\"radio\" name=\"font\" value=\"user/$font\" onChange=\"updateImage()\"> $font_label ";
						echo "<button type=\"submit\" name=\"delete-font\" value=\"1\" onClick=\"return deleteFont(" . htmlspecialchars(json_encode($font), ENT_QUOTES, 'UTF-8') . ");\">Delete</button><br>\n";
					}
				?>
				
				&nbsp;<br>
				
				<input type="submit" value="Upload" name="submit-file" onClick="return validateUpload();"> <input type="file" name="fileToUpload" id="fileToUpload"> 
			</td>
			<td id="third">
				<h3>Device</h3>
				<select name="device" id="device" onChange="updateDevice()">
					<?php
						foreach ($devices as $device_id => $device) {
							$selected = ($device_id == $selected_device) ? " selected" : "";
							echo "<option value=\"$device_id\"$selected>" . $device["name"] . " (" . $device["width"] . "x" . $device["height"] . ")</option>\n";
						}
					?>
				</select>

				&nbsp;<br>
				&nbsp;<br>

				Size<br>
				<select name="size-mode" id="size-mode" onChange="savePreviewSettings(true)">
					<option value="comfortable"<?php if ($selected_size_mode == "comfortable") echo " selected"; ?>>Comfortable (50%)</option>
					<option value="large"<?php if ($selected_size_mode == "large") echo " selected"; ?>>Large (100%)</option>
					<option value="actual"<?php if ($selected_size_mode == "actual") echo " selected"; ?><?php if ($selected_device != "m5stickcplus") echo " disabled"; ?>>Actual physical size</option>
				</select>

				&nbsp;<br>
				&nbsp;<br>

				Display<br>
				<select name="display-scale" id="display-scale" onChange="savePreviewSettings(true)"<?php if ($selected_device != "m5stickcplus" || $selected_size_mode != "actual") echo " disabled"; ?>>
					<?php
						foreach ($display_scales as $display_id => $display) {
							$selected = ($display_id == $selected_display_scale) ? " selected" : "";
							echo "<option value=\"$display_id\"$selected>" . $display["name"] . " (" . intval($display["scale"] * 100) . "%)</option>\n";
						}
					?>
				</select>

				&nbsp;<br>
				&nbsp;<br>

				<input type="button" id="rotate-button" value="Rotate <?php echo $selected_rotation; ?> deg" onClick="rotateDevice()">
				<input type="hidden" id="rotation" value="<?php echo $selected_rotation; ?>">

				&nbsp;<br>
				&nbsp;<br>

				<h3>Font Size</h3>
				<input type="text" name="size" id="sizefield" value="20" onInput="updateImage()"> points
				
				&nbsp;<br>
				&nbsp;<br>

				<h3>Demo text</h3>
				<textarea name="text" id="textfield" rows="8" onInput="updateImage()">Testing 123...</textarea>

				&nbsp;<br>
				<input type="checkbox" id="word-wrap" value="on" onChange="updatePreviewSettings()"<?php if ($selected_word_wrap == "on") echo " checked"; ?>> Word wrap

				&nbsp;<br>
				<input type="button" id="pixelate-button" value="<?php echo ($selected_pixelate == "on") ? "Smooth preview" : "Pixelate preview"; ?>" onClick="togglePixelate()">
				<input type="hidden" id="pixelate" value="<?php echo $selected_pixelate; ?>">

				&nbsp;<br>
				<input type="checkbox" id="output-pixelate" value="on" onChange="updatePreviewSettings()"<?php if ($selected_output_pixelate == "on") echo " checked"; ?>> Pixel-lock output

				&nbsp;<br>
				&nbsp;<br>

				<h3>Colours</h3>
				Background<br>
				<select name="background" id="background" onChange="updatePreviewSettings()">
					<?php
						foreach ($colors as $color_id => $color) {
							$selected = ($color_id == $selected_background) ? " selected" : "";
							echo "<option value=\"$color_id\"$selected>" . $color["name"] . "</option>\n";
						}
					?>
				</select>

				&nbsp;<br>
				&nbsp;<br>

				Foreground<br>
				<select name="foreground" id="foreground" onChange="updatePreviewSettings()">
					<?php
						foreach ($colors as $color_id => $color) {
							$selected = ($color_id == $selected_foreground) ? " selected" : "";
							echo "<option value=\"$color_id\"$selected>" . $color["name"] . "</option>\n";
						}
					?>
				</select>

				&nbsp;<br>
				&nbsp;<br>

				<h3>Position</h3>
				Vertical<br>
				<?php
					foreach ($vertical_positions as $position_id => $position_name) {
						$checked = ($position_id == $selected_vertical) ? " checked" : "";
						echo "<input type=\"radio\" name=\"vertical\" value=\"$position_id\"$checked onChange=\"updatePreviewSettings()\"> $position_name<br>\n";
					}
				?>

				&nbsp;<br>

				Horizontal<br>
				<?php
					foreach ($horizontal_positions as $position_id => $position_name) {
						$checked = ($position_id == $selected_horizontal) ? " checked" : "";
						echo "<input type=\"radio\" name=\"horizontal\" value=\"$position_id\"$checked onChange=\"updatePreviewSettings()\"> $position_name<br>\n";
					}
				?>
				
				&nbsp;<br>
				&nbsp;<br>
				&nbsp;<br>
				&nbsp;<br>
				
				<input type="submit" id="get-font" value="Get GFX font file" name="get-font">
				
				</form>
				
			</td>
		</tr>
		
		<tr>
			<td colspan=3>
	
&nbsp<br>	
			
<h3>Introducing truetype2gfx</h3>

<p>Many Arduino projects and ready-built devices come with a display. And the Adafruit GFX display driver is used by many of them to display variable-width fonts. Some fonts usually are included with the driver, and then there's a complicated procedure for adding your own fonts. It involves compiling tools and a trial-and-error process for figuring out how big the font will turn out on your display.</p>
			
<p>But now you can skip all that and convert the fonts your Arduino project needs with ease. No need to compile tools, no more guessing how big a font will be. Simply select a FreeFont or upload any TrueType font, select a size, download the include file and you're ready to use the font in your project.</p>

<h3>The size thing</h3>

<p>Font sizes are given in points, where a point is 1/72 of an inch, describing the actual size on a display. Or that's what it's supposed to mean, but pretty much everyone that uses the Adafruit software keeps the setting of 141 pixels per inch. In the Adafruit software it says:</p>

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

			</td>
		</tr>
	</table>
	
	
	
	<script>		
	
		function updateImage() {
			saveFormState();
			document.getElementById("image").src = "image.php?device=" + encodeURIComponent(device()) + "&font=" + encodeURIComponent(font()) + "&size=" + encodeURIComponent(document.getElementById("sizefield").value) + "&text=" + encodeURIComponent(previewText()) + "&background=" + encodeURIComponent(background()) + "&foreground=" + encodeURIComponent(foreground()) + "&vertical=" + encodeURIComponent(vertical()) + "&horizontal=" + encodeURIComponent(horizontal()) + "&word_wrap=" + encodeURIComponent(wordWrap()) + "&pixelate=" + encodeURIComponent(pixelate()) + "&output_pixelate=" + encodeURIComponent(outputPixelate()) + "&output_width=<?php echo $output_width; ?>&output_height=<?php echo $output_height; ?>&rotation=" + encodeURIComponent(rotation()) + "#" + new Date().getTime();
		}

		function previewText() {
			return document.getElementById("textfield").value.replace(/\r\n/g, "\\n").replace(/\r/g, "\\n").replace(/\n/g, "\\n");
		}

		function updateDevice() {
			var request = new XMLHttpRequest();
			request.open("POST", "index.php", true);
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			request.onload = function() {
				window.location.reload();
			};
			request.send("set-device=1&device=" + encodeURIComponent(device()));
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
					window.location.reload();
				} else {
					updateImage();
				}
			};
			request.send("set-preview-settings=1&background=" + encodeURIComponent(background()) + "&foreground=" + encodeURIComponent(foreground()) + "&vertical=" + encodeURIComponent(vertical()) + "&horizontal=" + encodeURIComponent(horizontal()) + "&size_mode=" + encodeURIComponent(sizeMode()) + "&display_scale=" + encodeURIComponent(displayScale()) + "&word_wrap=" + encodeURIComponent(wordWrap()) + "&pixelate=" + encodeURIComponent(pixelate()) + "&output_pixelate=" + encodeURIComponent(outputPixelate()) + "&rotation=" + encodeURIComponent(rotation()));
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

		function wordWrap() {
			return document.getElementById("word-wrap").checked ? "on" : "off";
		}

		function outputPixelate() {
			return document.getElementById("output-pixelate").checked ? "on" : "off";
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
			if(!file.match(reg)) {
				alert("You can only upload a TrueType font (.ttf or .TTF extension)");
				return false;
			}
		}
		
	</script>
</body>

</html>
