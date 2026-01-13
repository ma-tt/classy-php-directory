# Deployment and Security Recommendations

This document lists recommended server configuration, hardening, and UX/accessibility considerations when deploying Classy PHP Directory.

## Server configuration

- Run under a dedicated user and restrict filesystem access with `open_basedir` to the directory you expose. Note: some hosts ignore or disallow `ini_set('open_basedir', ...)` at runtime.
- Disable dangerous PHP functions where possible: `exec`, `shell_exec`, `popen`, `proc_open` if not required. The script contains a fallback to a safe shell zip path but prefers `ZipArchive`.
- Ensure `ZipArchive` extension is available for better portability and security. If missing, the code attempts a safe shell fallback.
- Configure your webserver to deny execution in upload/media directories (e.g., `php_flag engine off` for Apache) if you allow file uploads.
- Use HTTPS and enable HSTS in your server configuration.

## PHP settings

- `disable_functions` should include dangerous functions on shared hosts.
- `memory_limit` and `max_execution_time` should be set with consideration for hashing large files; the app limits hash size with `hash_size_limit` in `_resources/config.php`.

## Headers and hardening

Add security headers from the webserver or via a top-level include:

- `Content-Security-Policy` (start strict, loosen as needed for external CDN fonts/scripts)
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: no-referrer-when-downgrade`

## Input validation and access control

- Prefer whitelisting sub-directories that the lister may expose. Avoid relying solely on `..` checks.
- If exposing to the public, rate-limit heavy endpoints (`?hash=`, `?zip=`), via webserver or application-layer throttling.

## UX & accessibility suggestions

- Ensure UTF-8 handling for filenames and use `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` (already applied).
- Add `aria` attributes to interactive controls and ensure keyboard accessibility for file actions.
- Consider paginating very large directories or virtualizing the list for performance.
- Provide a local fallback for external CDN assets (Bootstrap/FontAwesome/jquery) for privacy and offline use.

## Logging and monitoring

- Log suspicious requests (attempts at directory traversal, repeated expensive operations).
- Monitor disk and CPU usage; hashing and zipping can be expensive.

## Final notes

If you want, I can add a small rate-limit (in-memory per-IP) for the `?hash=` endpoint and detect/disable zip functionality automatically when `ZipArchive` is missing.
