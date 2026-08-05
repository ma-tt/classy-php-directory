# Classy PHP Directory

A directory listing you can drop into any folder to browse files and subfolders without
looking like it's stuck in 2001. One PHP file, no build step, nothing to install.

## Quick Start

1. Copy `index.php` into the folder you want to serve.
2. Open it in your browser.

That's it.

## Requirements

PHP, any recent version. The `zip` extension if you want zip downloads (on by default,
everything else still works fine without it). Icons and fonts load from a CDN, so it
looks best with internet access, but it still works fine offline, just plainer.

## How to use

Click a filename to open it, a folder to browse into it (click anywhere in the row, not
just the text). Each file row has info and download buttons on the right; folders get a
zip button instead. Click a column header to sort by name, size, or date. Dark mode
toggle is top right, follows your OS setting until you touch it.

## Configuration

Edit the `$CL_CONFIG` array at the top of `index.php`:

- `hidden_files` / `hide_dot_files`: hide files from the listing (glob patterns)
- `items_per_page`: pagination, `0` to disable
- `hash_size_limit`: max file size for checksums
- `zip_enabled`: turn zip downloads off

## Security

No login. Anyone with the URL can browse and download. Put it behind HTTP auth if that's
a problem.

Hiding a file only hides it from the listing, not from someone requesting it directly. If
the folder has anything sensitive in it, copy the `.htaccess` from this repo alongside
`index.php`. It blocks dotfiles, symlink tricks, and adds a few security headers. Apache
only; nginx ignores `.htaccess`, so use this in your server block instead:

```nginx
location ~ /\. { deny all; }
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
