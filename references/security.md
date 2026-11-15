# Security reference

## What each delivery target already gives (say this to the user in simple words)

| Protection | Static host zip | PHP + admin panel | WordPress package |
|---|---|---|---|
| Hardened `.htaccess` (no folder listing, hidden/DB/log files blocked, security headers, compression, caching, HTTPS redirect ready) | yes | yes | snippet to paste |
| Content-Security-Policy with per-request nonce, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, HSTS (on HTTPS) | partial (headers via .htaccess) | yes | headers via plugin (no CSP) |
| Passwords hashed (bcrypt/argon), min 10 chars with letters+digits | - | yes | WordPress core |
| Login limit (5 tries, 15 min lock), generic error messages, constant-time checks | - | yes | plugin |
| Optional 2-factor login (authenticator app) + file-based recovery | - | yes | recommend a 2FA plugin |
| CSRF tokens on every form, prepared SQL statements, output escaping (XSS) | - | yes | nonces + WP escaping |
| Safe image upload (type + size checks, re-encode to WebP, random name, no script execution in uploads) | - | yes | snippet for uploads/.htaccess |
| Contact form: honeypot, rate limit, length limits, stored not trusted | - | yes | yes |
| Session cookie: HttpOnly, Secure, SameSite=Strict, idle + absolute timeout, bound to browser | - | yes | WordPress core |
| One-time installer protected by a secret key, deletes itself | - | yes | - |
| Hide WP version, XML-RPC off, no user enumeration, no file editor | - | - | plugin |
| Security status page with fix hints | - | dashboard warnings | Tools > Security status |

## What no package can promise (be honest)
- It does not replace a firewall/DDoS protection: recommend Cloudflare (free plan) in front of the site, and tick "behind Cloudflare" in the installer.
- Hosting security (server updates, other sites on the same shared host) is the host's job.
- Online payment is not included in the PHP package; for payments use WordPress + WooCommerce + a trusted gateway plugin.
- Backups are the owner's job: monthly copy of `data/` + `uploads/` (PHP) or a daily backup plugin (WordPress).
- The PHP and WordPress code is checked for structure and rendered logic by the build scripts, but it could not be executed inside the build sandbox. The installer shows a requirements checklist and writes errors to `data/error.log`; if the user reports a problem, ask for that error text or a screenshot and fix only the file concerned.

## Rules for you when writing templates (so the page stays safe)
- Never put user-controlled text into the page with anything other than the engine tags (they escape).
- No inline handlers, no external scripts, no `javascript:` URLs (the validator rejects them).
- Never include real secrets (API keys, passwords) in templates or schema.
- Do not ask the user for hosting passwords or FTP details; the user uploads the zip themselves.
- The install key lives only in the GUIDE file that stays on the user's computer. Never upload the guide to the site.

## Owner checklist (already printed in the guide files)
SSL on and HTTPS redirect enabled; strong unique admin password; 2FA on; delete `install.php` if it still exists; keep PHP 8.1+; update WordPress/plugins; monthly or daily backups; never use cracked themes/plugins.

## Recovery recipes
- Lost 2FA device (PHP package): create an empty file `data/DISABLE_2FA` with the host's File Manager, log in once with the password.
- Forgot admin password (PHP package): re-run is not possible after install. Tell the user to delete `data/config.php` and the `.sqlite` file only if they accept losing content, or ask Claude for a small reset script. Prefer the "change password" page while logged in.
- White page: open `data/error.log`.
