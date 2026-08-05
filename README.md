# Classy PHP Directory

A directory listing you can drop into any folder to browse files and subfolders without
looking like it's stuck in 2001. No database, no build step, no dependencies to install.
Just PHP making your directory a bit more classy.

## Overview

`Classy PHP Directory` serves a browsable index of the directory where you place `index.php`.
Drop it in a folder full of stuff and it turns into a clean, file-manager-style page instead of
whatever your web server's default directory listing looks like.

## Quick Start

1. Copy `index.php` into the directory you want to serve. That's the whole app, one file.
2. Add files or folders to that directory.
3. Open the folder in your browser (e.g. `http://localhost/your-folder/`).

That's it. No build steps, no other files required. (Optionally also copy the `.htaccess`
in this repo alongside it, see [Server hardening](#server-hardening) below for what it buys you.)

## Requirements

- Required: PHP (any supported version on your host).
- Optional: the `zip` PHP extension, needed for the zip-download feature (`zip_enabled`
  in the config block, on by default). Without it, everything else still works, zip
  downloads just show an error.
- `index.php` loads its icon font and body font from cdnjs/Google Fonts. If that request
  is blocked (offline server, strict corporate firewall), the page still works, it just
  falls back to a plain file glyph and your system font instead of looking as polished.

## How to use

- Click a file's name to open it, folders to navigate. Click anywhere in that row, not
  just the text, it's one big click target.
- Each file row has its own info (i) and download icons at the end: info pulls up
  checksums (MD5/SHA1), download forces a save instead of opening the file in the
  browser. Folder rows get a zip icon instead, to grab just that folder.
- The Name / Size / Modified column headers are clickable, to sort by any of the three.
- The moon/sun icon in the top right toggles dark mode. It also follows your OS
  preference automatically if you never touch it.
- Icons are per-file-type (Bootstrap Icons under the hood), so a `.pdf` doesn't look like
  a `.zip`.
- Large folders paginate automatically (250 entries per page by default, see below).

## Configuration

Everything's in the `$CL_CONFIG` block near the top of `index.php`, no other file to
touch:
- `hidden_files`: glob patterns or filenames to hide from the listing (example: `.env`,
  `*.log`). Heads up: this only hides entries from the *listing*, it doesn't stop
  someone from requesting the file's URL directly if they already know it's there. The
  optional `.htaccess` in this repo closes that gap on Apache (see below); if your host
  doesn't read `.htaccess`, add equivalent rules yourself.
- `hide_dot_files`: hides dotfiles (`.env`, `.git`, ...) from the listing on top of
  whatever's in `hidden_files`. Same listing-only caveat applies.
- `items_per_page`: how many entries show per page before pagination kicks in. `0`
  shows everything on one page.
- `hash_size_limit`: max bytes allowed for checksum generation.
- `zip_enabled`: lets people download a folder as a zip via the zip icon. On by default;
  set to `false` if you don't want it.

## Restricting access

This tool doesn't have a login screen. Anyone who can reach the URL can browse and
download. If the folder shouldn't be public, put it behind your web server's own HTTP
auth (e.g. Apache `.htpasswd` via `AuthType Basic`, or nginx `auth_basic`) or a
network-level restriction. Don't rely on the URL just being "hard to guess."

## Server hardening

`index.php` only hides files from its own listing, it can't stop your web server from
serving a `.env` or `.git/` sitting in the same folder if someone requests it directly by
URL. This repo ships an **optional** `.htaccess` for exactly that. Not required to run
the tool, but worth copying alongside `index.php` if the folder you're serving might have
anything sensitive in it.

On **Apache**, that `.htaccess` denies direct requests to dotfiles, turns off directory
listing and plain symlink-following (only same-owner symlinks are followed, so a symlink
pointing at a root-owned file like `/etc/passwd` won't be served), and sets a few
security headers (`X-Content-Type-Options`, `X-Frame-Options`, a light
`Content-Security-Policy`) so a stray `.svg`/`.html` someone left in the folder can't do
much if it's opened directly. It only takes effect if your Apache config allows
`.htaccess` overrides (`AllowOverride All` or at least `AllowOverride AuthConfig Limit
Options Indexes`, plus `mod_headers` for the header lines).

On **nginx**, `.htaccess` is ignored, so add the equivalent to your site's server block:

```nginx
location ~ /\. {
    deny all;
}

add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Content-Security-Policy "object-src 'none'; frame-ancestors 'self'" always;
```

## Contributions

Thanks to [Chris Kankiewicz](http://www.chriskankiewicz.com/)

## License

[Classy PHP Directory](https://github.com/ma-tt/classy-php-directory) © 2016 by [ma-tt](https://github.com/ma-tt) is licensed by the [MIT License](http://www.opensource.org/licenses/mit-license.php)

## Credits

Created by [ma-tt](https://github.com/ma-tt)