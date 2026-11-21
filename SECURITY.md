# Security Policy

## Status of this software

Local Language Policy Commons is a **prototype**. It is designed with security in mind (see below) but has not had an independent security audit, and it stores data in JSON files with a single shared moderator password. **Do not use it to hold sensitive or personal data.** A security review is a planned milestone (see [ROADMAP.md](ROADMAP.md)).

## Reporting a vulnerability

Please **do not** open a public issue for security problems.

Use GitHub's private reporting: **Security → Report a vulnerability** on this repository. Include steps to reproduce, the affected version, and the impact. We aim to acknowledge reports within 7 days and to publish a fix or mitigation as quickly as is reasonable. Coordinated disclosure is appreciated; reporters are credited unless they prefer otherwise.

## Supported versions

Only the latest release on `main` receives fixes.

## Built-in protections

- Output escaping everywhere; strict Content-Security-Policy with no JavaScript, no inline styles, no third-party resources
- CSRF tokens on every state-changing form; logout requires POST
- Session cookies: `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS, strict mode, ID regeneration on login, 60-minute idle timeout for moderators
- Moderator password stored only as a hash; common weak passwords rejected; failed logins rate-limited
- Rate limiting and honeypot on public forms; rate-limit keys are HMAC hashes, not IP addresses
- Input restricted to strings, UTF-8 validated, control characters stripped, length limits enforced
- Locked, atomic JSON writes; corrupt files are never overwritten
- CSV export neutralises spreadsheet formula injection
- MediaWiki client: HTTPS only, redirect limits, timeouts, caching, fixed (admin-configured) endpoint
- Runtime data lives in `storage/`, outside `public/`

## Known limitations

- Single shared moderator account (no per-user accounts, 2FA or roles yet)
- File-based storage is not suited to high traffic or multiple servers
- No automated malware or spam classification beyond rate limits and a honeypot
- Rate limiting trusts `REMOTE_ADDR` unless `trust_proxy_headers` is enabled behind a trusted proxy

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for the production hardening checklist.
