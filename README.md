# Classy PHP Directory

A directory listing you can drop into any folder to browse files and subfolders without
looking like it's stuck in 2001. No database, no build step, no dependencies to install.
Just PHP making your directory a bit more classy.

## Overview

`Classy PHP Directory` serves a browsable index of the directory where you place `index.php`.
Drop it in a folder full of stuff and it turns into a clean, file-manager-style page instead of
whatever your web server's default directory listing looks like.

## Quick Start

1. Copy `index.php`, `.htaccess`, and the `_classy/` folder (which has its own
   `.htaccess`) into the directory you want to serve.
2. Add files or folders to that directory.
3. Open the folder in your browser (e.g. `http://localhost/your-folder/`).

That's it. No build steps required.

## Requirements

- Required: PHP (any supported version on your host).

## How to use

- Click files to download, folders to navigate.
- Click the info (i) icon to pull up checksums (MD5/SHA1) for a file.
- The moon/sun icon in the top right toggles dark mode. It also follows your OS
  preference automatically if you never touch it.
- Icons are per-file-type (Bootstrap Icons under the hood), so a `.pdf` doesn't look like
  a `.zip`.

## Configuration

Edit `_classy/config.php`:
- `hidden_files`: glob patterns or filenames to hide (example: `_classy`, `.env`).
  Heads up: this only hides entries from the *listing*. It doesn't stop someone from
  requesting the file's URL directly if they already know it's there. The shipped
  `.htaccess` files close that gap on Apache (see below); if your host doesn't read
  `.htaccess`, add equivalent rules yourself.
- `index_files`: files that make a folder a direct link (e.g. `index.php`).
- `hash_size_limit`: max bytes allowed for checksum generation.
- `zip_dirs`: lets people download a whole folder as a zip via the download icon.
  Off by default; flip it to `true` if you want it.

## Restricting access

This tool doesn't have a login screen. Anyone who can reach the URL can browse and
download. If the folder shouldn't be public, put it behind your web server's own HTTP
auth (e.g. Apache `.htpasswd` via `AuthType Basic`, or nginx `auth_basic`) or a
network-level restriction. Don't rely on the URL just being "hard to guess."

## Server hardening

On **Apache**, the shipped `.htaccess` files deny direct requests to dotfiles (`.env`,
`.git/`, etc.) and to the `_classy/*.php` sources, turn off directory listing and plain
symlink-following (only same-owner symlinks are followed, so a symlink pointing at a
root-owned file like `/etc/passwd` won't be served), set a few security headers
(`X-Content-Type-Options`, `X-Frame-Options`, a light `Content-Security-Policy`) so a
stray `.svg`/`.html` someone left in the folder can't do much if it's opened directly,
and let browsers cache the theme's CSS/JS/fonts for a week instead of refetching them on
every visit. They only take effect if your Apache config allows `.htaccess` overrides
(`AllowOverride All` or at least `AllowOverride AuthConfig Limit Options Indexes` for the
directives used here, plus `mod_headers`/`mod_expires` for the header and caching lines).

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

add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Content-Security-Policy "object-src 'none'; frame-ancestors 'self'" always;

location ~* ^/_classy/themes/.*\.(css|js|woff2?|ttf|eot|png)$ {
    expires 1w;
}
```

## Contributions

Thanks to [Chris Kankiewicz](http://www.chriskankiewicz.com/)

## License

[Classy PHP Directory](https://github.com/ma-tt/classy-php-directory) © 2016 by [ma-tt](https://github.com/ma-tt) is licensed by the [MIT License](http://www.opensource.org/licenses/mit-license.php)

## Credits

Created by [ma-tt](https://github.com/ma-tt)