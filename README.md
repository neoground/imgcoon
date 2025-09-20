# Imgcoon

A tiny PHP 8 library to generate preview images / thumbnails from
many file formats — images, videos, PDFs, office docs, and even audio waveforms.

[![PHP 8+](https://img.shields.io/badge/PHP-8.0%2B-777bb4)](https://www.php.net/)
[![GD/Imagick](https://img.shields.io/badge/Image%20Backend-GD%20or%20Imagick-informational)]()
[![FFmpeg | LibreOffice | Ghostscript | SoX](https://img.shields.io/badge/Deps-ffmpeg%20%7C%20libreoffice%20%7C%20ghostscript%20%7C%20sox-lightgrey)]()
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](./LICENSE)

Imgcoon is a pragmatic, production-tested helper to create consistent preview images for files users upload to your app.
It wraps common system tools and PHP imaging extensions into a single, fluent API.

Used reliably across our own SaaS and platforms — **stable in production**.

---

## ✨ Features

* **Many sources → one image**: images, videos, PDFs, office docs, and audio into preview images.
* **Two calling styles**: static one-liner or fluent instance API.
* **Sizing made simple**: pick a mode and width, optional aspect ratio lock (e.g. `4:3`).
* **Modern formats**: output to WebP (recommended), JPEG, PNG, etc.
* **Predictable results**: sensible defaults designed for app thumbnails and file galleries.

---

## ⚙️ Requirements

### PHP

* **PHP**: 8.0+
* **Extensions**: **GD** (built-in) and not required but well recommended **Imagick** (`php-imagick`)

### Shell access

* `shell_exec` must be enabled — Imgcoon spawns external tools.

### External tools (pick per feature)

* **Image processing**: GD or Imagick
* **Video thumbnails**: `ffmpeg`
* **PDF thumbnails**: `ghostscript`, `imagemagick`, `php-imagick`
* **Office docs (docx/odt/rtf/ppt/txt…)**: `libreoffice` (headless mode)
* **Audio waveforms**: `sox`, `imagemagick`, `php-imagick`

### Package examples

Below are “everything in one go” installs for popular distros. Adjust package names for your PHP version (e.g. `php8.2-*` vs `php8.3-*`).

#### Debian / Ubuntu (APT)

```bash
sudo apt update
sudo apt install -y \
  php-cli php-gd php-imagick \
  imagemagick ghostscript \
  ffmpeg libreoffice \
  sox
```

#### Arch / Manjaro (Pacman)

```bash
sudo pacman -S --needed \
  php php-gd php-imagick \
  imagemagick ghostscript \
  ffmpeg libreoffice-fresh \
  sox
```

#### Fedora / RHEL (DNF)

```bash
sudo dnf install -y \
  php-cli php-gd php-pecl-imagick \
  ImageMagick ghostscript \
  ffmpeg libreoffice \
  sox
```

#### openSUSE (Zypper)

```bash
sudo zypper install -y \
  php8 php8-gd php8-imagick \
  ImageMagick ghostscript \
  ffmpeg libreoffice \
  sox
```

#### macOS (Homebrew)

```bash
brew install php imagemagick ghostscript ffmpeg libreoffice sox
pecl install imagick   # PHP extension
```

#### Windows

* Install PHP 8.x with GD enabled (default).
* Grab [ImageMagick](https://imagemagick.org/script/download.php), [Ghostscript](https://ghostscript.com/), [FFmpeg](https://ffmpeg.org/), [LibreOffice](https://www.libreoffice.org/download/download/), and [SoX](http://sox.sourceforge.net/) manually.
* Add each binary folder to your **PATH** so PHP can run them with `shell_exec`.
* For Imagick, install the [Imagick DLL](https://mlocati.github.io/articles/php-windows-imagick.html) matching your PHP build.

---

## 📦 Installation

Via Composer (recommended):

```bash
composer require neoground/imgcoon
```

Or include the class in your autoloader if you vendor the source directly.

---

## 🚀 Quick Start

### Static one-liner

```php
use Neoground\Imgcoon\Imgcoon;

$src_file          = './input.odt';
$image_destination = './output.webp';
$mime              = 'image/webp';
$mode              = 'bestfit'; // sizing preset
$override          = true;      // overwrite destination if it exists

Imgcoon::create($src_file, $image_destination, $mime, $mode, $override);
```

### Fluent API

```php
use Neoground\Imgcoon\Imgcoon;

$src_file          = './input.pdf';
$image_destination = './preview.webp';

$ic = new Imgcoon();
$ic->setSource($src_file)
   ->setDestination($image_destination, 'image/webp')
   ->setMode('bestfit')
   ->setWidth(600, '4:3') // target width 600px, lock aspect ratio to 4:3
   ->setQuality(75)       // encoder quality (0-100)
   ->generate(true);      // override if destination exists
```

---

## 📚 What Imgcoon can render

* **Images** supported by GD/Imagick (JPEG, PNG, WebP, etc.)
* **Videos / media** handled by FFmpeg (MP4, MOV, MKV, …)
* **Documents** opened by LibreOffice (DOCX, ODT, RTF, PPT/PPTX, TXT, …)
* **PDFs** via Ghostscript + ImageMagick
* **Audio** files processed by SoX (waveform preview images)

For audio: Imgcoon renders a waveform visualization as your preview image.

---

## 🔒 Security & Operations

* **Untrusted input**: Do not pass raw user input directly to file paths. Sanitize and store uploads safely.
* **Process constraints**: Ensure your PHP worker has permission to execute the required CLI tools.
* **Resource limits**: Large PDFs/videos can be heavy. Consider timeouts and memory limits at the web server / FPM
  layer.
* **Sandboxing**: For multi-tenant apps, run converters in isolated workers/containers when possible.

---

## ✅ Production Notes

* **Idempotent writes**: Use `$override = false` in `generate()` or `create()` to avoid accidental overwrites.
* **Deterministic thumbnails**: Fix width and aspect to keep grid UIs neat.
* **Format choice**: Prefer `image/webp` for size/quality; fall back to JPEG/PNG for broader compatibility.

---

## 🧪 Troubleshooting

* **“Command not found”**: Verify the external tool (`ffmpeg`, `libreoffice`, `gs`, `sox`, `convert`) is installed and
  on the PATH of the PHP process user.
* **“shell\_exec disabled”**: Enable `shell_exec` or move thumbnailing into a trusted worker/queue where it’s permitted.
* **Blank / black frames from video**: Provide a longer input or ensure codecs are supported by your FFmpeg build.
* **PDF pages wrong**: Ensure Ghostscript is recent and ImageMagick policies allow PDF reading.
* **Fonts in office docs**: Install appropriate system fonts (LibreOffice rendering quality depends on installed fonts).

---

## 🤝 Contributing

Issues and PRs are welcome. Please:

1. Describe the use case & environment (PHP version, extensions, OS).
2. Include minimal repro steps and sample files if possible.
3. Keep changes small and focused; add tests when practical.

---

## 🗺️ Roadmap (indicative)

* Optional CLI wrapper for queue workers
* Pluggable temp directory / caching
* More sizing modes (explicit crop/cover, focal point)
* Metadata hooks (e.g., first page selection for PDFs)

---

## 📄 License

**MIT** — see [LICENSE](./LICENSE.md).

---

## 🧭 About Neoground

We build **intelligent strategy**, **digital solutions**, **hosting & infrastructure**, and **digital presence** for
modern organizations — plus our own SaaS. Imgcoon is one of the small utilities we open-source to make robust file
handling easier for everyone.
