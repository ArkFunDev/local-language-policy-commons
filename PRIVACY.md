# Privacy Notice

*Applies to the software in this repository and to installations that keep its defaults. Each operator (for example BASAibu) is responsible for publishing a notice that matches how they run it.*

## What the application stores

| Data | Why | Visible publicly? |
|---|---|---|
| Proposal text, Indonesian reference text, language, topic | The core purpose of the service | Only after a moderator accepts it |
| Optional source URL and optional attribution (a pseudonym is fine) | Provenance and credit | After acceptance |
| Licence-consent flag and timestamps | To publish and export only what contributors agreed to share | No (consent is enforced on export) |
| Feedback text and type | Corrections and context from the community | Only after a moderator approves it |
| Moderator audit log (action, item id, time) | Accountability | No |
| Rate-limit counters (HMAC-hashed, expire within 24 hours) | Abuse prevention | No |
| A session cookie | Security (CSRF) and your language choice | No |

## What it does **not** do

No public accounts, no advertising, no analytics, no tracking pixels, no third-party scripts or fonts, no sale of data. IP addresses are not stored: rate limiting uses a keyed hash. Your web server may keep its own access logs; operators should configure those according to local law.

## Please do not submit personal information

Do not include names, contact details, identifiers, or sensitive information about yourself or others in proposals or feedback. Moderators may reject or remove such content.

## Open data

Accepted proposals whose contributors consented may be exported publicly under the installation's data licence (default CC BY 4.0). Because open data is hard to recall once copied, submit only text you are comfortable sharing openly.

## Your choices

You can ask the operator to correct or remove your contribution. For this repository's demonstration installation, open an issue (without personal details) or contact the maintainer. Operators in Indonesia should also consider obligations under the Personal Data Protection Law (Law No. 27 of 2022) and obtain local legal advice before running a public service.

## Third-party services

The MediaWiki Explorer makes server-side requests to the configured MediaWiki API. Your browser does not contact it directly. By default this is Indonesian Wikipedia, used only as a demo source.
