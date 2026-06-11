# truetype2gfx

### Converting fonts from TrueType to Adafruit GFX

[![](truetype2gfx-screenshot.png)](https://rop.nl/truetype2gfx)

Many Arduino projects and ready-built devices come with a display. And the Adafruit GFX display driver is used by many of them to display variable-width fonts. Some fonts usually are included with the driver, and then there's a complicated procedure for adding your own fonts. It involves compiling tools and a trial-and-error process for figuring out how big the font will be on your display as well as relative to the other fonts.

But now you can skip all that and convert the fonts your Arduino project needs with ease. No need to compile tools, no need to find out how big a font will be by trial and error. Simply select a FreeFont or upload any TrueType font, select a size, download the include file and you're ready to use the font in your project.

### If you just want to use truetype2gfx [click here](https://rop.nl/truetype2gfx)

This is the github repository. The tool itself is a server thing that works with your webbrowser. It is available for your use [**here**](https://rop.nl/truetype2gfx), no need to install anything, just click. That webpage has not only the tool but also all the information you will need to use it. 

This repository has the PHP/Javascript source and documents how to install it if you want to run a copy on your own server, or just see how it was done.

### Issues, requests, help

If you open an issue on this repository, I'll see what I can do.

### Running your own copy

If you are not content with running the version that's on my server because:

 * You want to change or add something
 * You're working with highly classified TrueType fonts
 * of some other reason

.. then here's how you make it work:
 
1. Copy the files from this repository to a directory on a server that has PHP enabled. You will need support for `gd` and `freetype` enabled in the PHP installation, check with `phpinfo()` to see if they are there.

2. In this directory, add a compiled version of the Adafruit `fontconvert` tool (see [here](https://github.com/adafruit/Adafruit-GFX-Library/tree/master/fontconvert)) and make sure it it executable to the user that runs your webserver. 

3. Make sure the fonts/user directory is writable for the webserver user.

### Running with Docker

This repository includes a Docker setup with lighttpd and PHP enabled.
It builds a Linux `fontconvert` binary from the local `fontconvert/` source directory,
so you do not need a host `fontconvert` executable for container use. The binary is
built for the architecture where the image is built, such as macOS Docker Desktop or
a Raspberry Pi.

Build and start the container:

```sh
mkdir -p /tmp/truetype2gfx
docker compose up --build -d
```

On a Mac, open:

```text
http://localhost:8088/
```

On a Raspberry Pi, open the Pi address from another machine:

```text
http://raspberrypi.local:8088/
```

or replace `raspberrypi.local` with the Pi's IP address.

Port `8088` is used so it does not conflict with an existing lighttpd on the host.
Uploaded fonts, the last selected device, and preview settings are stored in
`/tmp/truetype2gfx` on the host through a bind mount, so they survive container
rebuilds.

To stop the container:

```sh
docker compose down
```

If you want the Pi to serve this directly on port 80, change the port mapping in
`docker-compose.yml` to:

```yaml
ports:
  - "80:80"
```

### Preview controls

The preview can target multiple devices, including M5Stack and M5Stick-CPlus. The
M5Stick-CPlus can be displayed at comfortable size, large 100% size, or actual
physical size with a display calibration preset.

The demo text box supports multiple lines. Actual line breaks are rendered as line
breaks, and typed `\n` sequences are also interpreted as line breaks.

Preview options include foreground/background colours, text alignment, word wrap,
rotation, pixelated glyph rendering, and pixel-locked output scaling. Custom uploaded fonts are listed from
`/tmp/truetype2gfx` and can be deleted from the page.
