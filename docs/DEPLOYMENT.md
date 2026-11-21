# Deployment Guide

## Requirements

PHP 7.4+ (8.x recommended) with `json`, `mbstring`, `curl`. Apache, nginx, or the PHP built-in server. No database.

## 1. Local (PHP built-in server)

```bash
php bin/set-moderator-password.php
php -S localhost:8000 -t public
```

## 2. XAMPP (Windows)

1. Copy the project to `C:\xampp\htdocs\local-language-policy-commons`.
2. In a terminal inside it, run `C:\xampp\php\php.exe bin\set-moderator-password.php`.
3. Enable `extension=curl` and `extension=mbstring` in `php.ini`, restart Apache.
4. Open `http://localhost/local-language-policy-commons/public/`.

The `.htaccess` files in `config/`, `src/`, `lang/`, `bin/`, `tests/` and `storage/` deny direct access when the project root is served. For anything public, point the document root at `public/` instead.

## 3. Apache (production)

```apache
<VirtualHost *:443>
    ServerName commons.example.org
    DocumentRoot /var/www/llpc/public
    <Directory /var/www/llpc/public>
        AllowOverride All
        Require all granted
    </Directory>
    # TLS configuration …
</VirtualHost>
```

Keep `/var/www/llpc/storage` writable by the web user (`chown -R www-data: storage && chmod 750 storage`) and **outside** `DocumentRoot` (it is, with the layout above).

## 4. nginx (production)

nginx ignores `.htaccess`, so the web root must be `public/`:

```nginx
server {
    listen 443 ssl http2;
    server_name commons.example.org;
    root /var/www/llpc/public;
    index index.php;

    location / { try_files $uri $uri/ =404; }
    location ~ ^/.*\.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }
    location ~ /\. { deny all; }
}
```

## 5. Docker

```bash
cp .env.example .env
docker compose run --rm app php bin/set-moderator-password.php --print-hash
# paste the printed hash into .env, keeping the single quotes
docker compose up --build
```

Open <http://localhost:8080>. Data persists in the `llpc-storage` volume. For HTTPS, put a reverse proxy (Caddy, Traefik, nginx) in front and set `trust_proxy_headers` only if that proxy is trusted.

## 6. Configuration

See `config/config.example.php`. Environment variables override the file: `LLPC_MODERATOR_PASSWORD_HASH`, `LLPC_STORAGE_DIR`, `LLPC_DEFAULT_LANG`, `LLPC_DATA_LICENSE`, `LLPC_MEDIAWIKI_API_URL`, `LLPC_MEDIAWIKI_PAGE_BASE`, `LLPC_MEDIAWIKI_CATEGORY`, `LLPC_DEBUG`.

## 7. Production hardening checklist

- [ ] Serve only `public/`; HTTPS everywhere (the app sends HSTS on HTTPS)
- [ ] Moderator password set with `bin/set-moderator-password.php` (≥ 12 characters, unique)
- [ ] Log in and open **System check**: every line should read PASS
- [ ] `storage/` writable by the web user, not readable by others, not inside the web root
- [ ] `display_errors` off (the app disables it unless `debug` is on); `LLPC_DEBUG` unset
- [ ] Regular backups of `storage/` (`submissions.json`, `feedback.json`, `similarity_reviews.json`, `audit.log`)
- [ ] Publish an operator privacy notice that matches your logging (see `PRIVACY.md`)
- [ ] Confirm the data licence with the community organisation before opening submissions
- [ ] Review `max_records` and rate limits for your expected traffic
- [ ] Keep PHP updated; subscribe to repository security advisories

## 8. Backups and updates

Back up by copying `storage/`. Update with `git pull`, run `php tests/run.php`, and read `CHANGELOG.md` for breaking changes.
