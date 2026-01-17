# Classy PHP Directory

Simple PHP directory listing you can drop into any folder to browse files and subfolders via a tidy UI.

## Overview

`Classy PHP Directory` serves a browsable index of the directory where you place `index.php`.

## Quick Start

1. Copy `index.php` and the `_resources/` folder into the directory you want to serve.
2. Add files or folders to that directory.
3. Open the folder in your browser (e.g. `http://localhost/your-folder/`).

No build steps required.

## Requirements

- Required: PHP (any supported version on your host).

## How to use

- Click files to download, folders to navigate.
- Click the info (i) icon to request checksums (MD5/SHA1).

## Configuration

Edit `_resources/config.php`:
- `hidden_files` — glob patterns or filenames to hide (example: `_resources`, `.env`).
- `index_files` — files that make a folder a direct link (e.g. `index.php`).
- `hash_size_limit` — max bytes allowed for checksum generation.

## Contributions

Thanks to [Chris Kankiewicz](http://www.chriskankiewicz.com/).

## License

[Classy PHP Directory](https://github.com/ma-tt/classy-php-directory) © 2026 by [ma-tt](https://github.com/ma-tt) is licensed by the [MIT License](http://www.opensource.org/licenses/mit-license.php)

## Credits

Created by [ma-tt](https://github.com/ma-tt).
