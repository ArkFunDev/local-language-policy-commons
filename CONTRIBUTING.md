# Contributing

Thank you for helping. This project exists to support speakers of Indonesia's local languages, so the most valuable contributions are often not code.

## Ways to contribute

1. **Language review (native speakers).** The sample proposals are synthetic and have not been reviewed by native speakers. Corrections, better phrasing, and additional *clearly synthetic* examples in other local languages are very welcome. Open an issue with the **Language correction** template.
2. **Accessibility and usability testing.** Try the interface with a screen reader, keyboard only, a small phone, or a slow connection, and tell us what breaks.
3. **Translation of the interface.** UI text lives in `lang/en.php` and `lang/id.php`. To add a language, copy `lang/en.php`, translate the values (never the keys), and register the code in `llpc_supported_langs()` in `src/i18n.php`.
4. **Code, tests and documentation.** See below.

## Ground rules

- Be kind and follow the [Code of Conduct](CODE_OF_CONDUCT.md).
- **Never post real personal data** in issues, pull requests or sample data.
- Do not add content you do not have the right to share. Sample data must be clearly synthetic and marked `is_synthetic: true`.
- Keep the project's principles: original wording is preserved, software suggests while people decide, no ranking of ideas, and suggestions stay explainable.

## Development setup

```bash
php bin/set-moderator-password.php
php tests/run.php
php -S localhost:8000 -t public
```

Requirements: PHP 7.4+ (the code must keep working on 7.4 — avoid `match`, `str_contains`, union types, constructor promotion, etc.), extensions `json`, `mbstring`, `curl`.

## Code guidelines

- Escape **all** output with `h()` (or `th()` for translations). No exceptions.
- Read user input only through `llpc_get()` / `llpc_post()` so values are always clean strings.
- Every state-changing form needs `llpc_csrf_field()` and a server-side `llpc_csrf_valid()` check. Moderator actions use `llpc_check_moderator_post()`.
- Write JSON only through `llpc_update_json()` (locked read-modify-write).
- No inline `<script>`, inline `style=""` attributes, or third-party resources: the Content-Security-Policy forbids them on purpose.
- Add every new UI string to **both** `lang/en.php` and `lang/id.php`. `php tests/run.php` fails if they differ.
- Add or update tests in `tests/run.php` for new logic.

## Pull requests

1. Fork and create a branch from `main`.
2. Make a focused change; update docs and `CHANGELOG.md` when behaviour changes.
3. Run `php tests/run.php` and `find . -name '*.php' -print0 | xargs -0 -n1 php -l`.
4. Open a pull request using the template. CI runs the same checks on PHP 7.4, 8.1 and 8.3.

## Reporting security issues

Please do **not** open a public issue. See [SECURITY.md](SECURITY.md).
