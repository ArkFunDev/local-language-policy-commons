# Changelog

All notable changes are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/), and the project uses [Semantic Versioning](https://semver.org/).

## [0.2.0] — 2026-11-21

First public-ready release. A complete restructuring of the original prototype for open publication.

### Added
- `LICENSE` (MIT), `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, `SECURITY.md`, `PRIVACY.md`
- Bilingual interface (English / Bahasa Indonesia) with language switcher
- Proposal detail page, pagination, language-tagged text for assistive technology
- Moderator feedback queue; public pages now show only approved feedback
- Moderator confirm/reject review of suggested related pairs, with shared terms shown for every suggestion
- Public open-data export (JSON and CSV) of accepted, consented records
- Licence-consent checkbox on the submission form; language and topic selectors replace free text
- MediaWiki client: configurable endpoint, HTTPS-only, caching, category mode, moderator import as pending submission
- Audit log, rate limiting (hashed keys), honeypot, login throttling
- `bin/set-moderator-password.php`, `tests/run.php`, GitHub Actions CI, issue/PR templates, Dockerfile and `docker-compose.yml`
- Documents: system architecture, wireframes, MediaWiki integration, responsible AI, evaluation plan, deployment guide, Mozilla Fellowship reference, MySQL schema (planned)

### Changed
- Source split into `public/` (web root) and `src/`; runtime data moved to `storage/` outside the web root
- Public pages show only synthetic samples and **moderator-accepted** community submissions
- Strict Content-Security-Policy; all inline styles and scripts removed; dashboards use `<progress>`
- Hardened sessions (HttpOnly, SameSite, strict mode, idle timeout); logout is POST + CSRF
- JSON writes are now locked read-modify-write and refuse to overwrite corrupt files
- Roadmap and status text updated to describe a working prototype honestly

### Fixed
- Passing an array in a query parameter (e.g. `?q[]=x`) could cause a fatal error; all input is now coerced to strings
- `docs/SYSTEM-ARCHITECTURE.md` was a copy of the wireframe document; it now describes the architecture
- Login message referred to a non-existent `config.local.example.php`
- Orphaned `reviews.json` (never written by the app) removed
- Read-modify-write race when two moderators or visitors saved at the same time

### Security
- The original archive shipped `config.local.php` containing a weak default moderator password and test data; both were removed. If you ever ran the old version on a public host, change that password.

## [0.1.0] — 2026

Initial concept and early prototype (documentation, wireframes, PHP prototype with JSON storage).
