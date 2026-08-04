# Classy PHP Directory

Simple PHP directory listing you can drop into any folder to browse files and subfolders via a tidy UI.

## Overview

`Classy PHP Directory` serves a browsable index of the directory where you place `index.php`.

## Quick Start

1. Copy `index.php`, `.htaccess`, and the `_classy/` folder (which has its own
   `.htaccess`) into the directory you want to serve.
2. Add files or folders to that directory.
3. Open the folder in your browser (e.g. `http://localhost/your-folder/`).

No build steps required.

## Requirements

- Required: PHP (any supported version on your host).

## How to use

- Click files to download, folders to navigate.
- Click the info (i) icon to request checksums (MD5/SHA1).

## Configuration

Edit `_classy/config.php`:
- `hidden_files` — glob patterns or filenames to hide (example: `_classy`, `.env`).
  **This only hides entries from the generated listing — it does not stop someone from
  requesting the file's URL directly.** The shipped `.htaccess` files close that gap on
  Apache (see below); if your host doesn't read `.htaccess`, add equivalent rules
  yourself.
- `index_files` — files that make a folder a direct link (e.g. `index.php`).
- `hash_size_limit` — max bytes allowed for checksum generation.

## Restricting access

This tool has no login/allowlist of its own — anyone who can reach the URL can browse
and download. If the folder shouldn't be public, put it behind your web server's own
HTTP auth (e.g. Apache `.htpasswd` via `AuthType Basic`, or nginx `auth_basic`) or a
network-level restriction.

## Server hardening

On **Apache**, the shipped `.htaccess` files deny direct requests to dotfiles (`.env`,
`.git/`, etc.) and to the `_classy/*.php` sources, and turn off directory listing and
plain symlink-following (only same-owner symlinks are followed, so a symlink pointing at
a root-owned file like `/etc/passwd` won't be served). They only take effect if your
Apache config allows `.htaccess` overrides (`AllowOverride All` or at least
`AllowOverride AuthConfig Limit Options Indexes` for the directives used here).

On **nginx**, `.htaccess` is ignored, so add the equivalent to your site's server block:

```nginx
location ~ /\. {
    deny all;
}

location ~ ^/_classy/.*\.php$ {
    deny all;
}

location /_classy/ {
    autoindex off;
}

# Symlinks are followed by default and nginx has no per-owner equivalent
# of Apache's SymLinksIfOwnerMatch — avoid placing symlinks to files
# outside the served directory in a folder this tool serves.
```

## Contributions

Thanks to [Chris Kankiewicz](http://www.chriskankiewicz.com/).

## License

[Classy PHP Directory](https://github.com/ma-tt/classy-php-directory) © 2026 by [ma-tt](https://github.com/ma-tt) is licensed by the [MIT License](http://www.opensource.org/licenses/mit-license.php)

## Credits

Created by [ma-tt](https://github.com/ma-tt).
