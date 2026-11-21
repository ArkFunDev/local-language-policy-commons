# Mozilla Foundation Fellowship 2027–2028 — Application Reference

**Track:** I — Embedded Fellow · **Host organisation:** BASAibu (Indonesia) · **Project:** Local Language Policy Commons
**Fellowship period:** 12 months from 1 June 2027 · **Repository:** https://github.com/ArkFunDev/local-language-policy-commons

This document collects ready-to-paste answers and supporting evidence. The repository is the evidence: every claim below can be checked in the code and documents linked.

---

## 1. One-liner (≤ 30 words)

An open-source, human-in-the-loop tool that helps BASAibu surface related youth policy proposals across Indonesia's local languages, preserving original wording and leaving every judgment to the community.

## 2. Project description (≤ 200 words)

Young Indonesians write policy proposals in their own local languages on BASAibu's community wikis. The volume and linguistic diversity make it hard to see where proposals overlap, and most tools expect everything to be translated into one dominant language.

I will build Local Language Policy Commons with BASAibu: an open-source platform that reads proposals from BASAibu's MediaWiki infrastructure, shows each one in its original language beside an Indonesian reference, and suggests potentially related proposals across languages. Every suggestion is explainable, labelled as automated, and must be confirmed or rejected by moderators and speakers. The system never ranks policy importance.

A working prototype already exists (v0.2, MIT licence): a MediaWiki client, moderation workflow, transparent similarity baseline, open-data export, bilingual accessible interface, automated tests and CI. During the fellowship I will integrate with BASAibu's real wiki structure; evaluate small, locally run multilingual models against the baseline language by language with native-speaker reviewers, to find which communities they serve poorly; and publish code, documentation and findings openly.

Success is a tool BASAibu's community actually uses to review proposals, with privacy protected, bias measured and published, and maintenance handed to BASAibu.

## 3. Milestones (≤ 150 words)

**Q1 (Jun–Aug 2027):** Research with BASAibu and young contributors; map wiki structure; agree data licence, privacy and moderation rules; integration plan and evaluation protocol.

**Q2 (Sep–Nov 2027):** Integrate with BASAibu's MediaWiki; original-language views; database-backed storage and per-moderator accounts; independent security review; baseline measurements.

**Q3 (Dec 2027–Feb 2028):** Evaluate similarity methods per language with native-speaker reviewers; build the community review interface; publish interim evaluation and bias findings.

**Q4 (Mar–May 2028):** Community usability and accessibility testing; open release of code, documentation and consented data; final report; maintenance plan and handover to BASAibu.

## 4. Fit with the host organisation

BASAibu's challenge, as stated in the fellowship call, is that young people across Indonesia write policy proposals in their own local languages, and the volume and diversity of languages make them hard to engage with at scale. This project answers that directly: it keeps each proposal in its original language, helps reviewers and policymakers see related ideas across languages, and keeps moderators in control. It is built to fit BASAibu's existing MediaWiki infrastructure rather than replace it.

## 5. Evidence of progress (verifiable in the repository)

| Claim | Where to verify |
|---|---|
| Working prototype with explorer, detail pages, submission, moderation, feedback queue | `public/`, [README](../README.md) |
| Read-only MediaWiki integration with import-to-queue | `src/mediawiki.php`, [MEDIAWIKI-INTEGRATION.md](MEDIAWIKI-INTEGRATION.md) |
| Transparent similarity with human review of every pair | `src/similarity.php`, `public/discovery.php` |
| Bilingual interface (EN/ID), accessible, no JavaScript, strict CSP | `lang/`, `public/assets/style.css`, `src/security.php` |
| Automated tests and CI on PHP 7.4/8.1/8.3 | `tests/run.php`, `.github/workflows/ci.yml` |
| Open licence, privacy notice, security policy, code of conduct | `LICENSE`, `PRIVACY.md`, `SECURITY.md`, `CODE_OF_CONDUCT.md` |

**Honest limits (stated in the repository too):** no production deployment and no real users yet; sample data is synthetic and not reviewed by native speakers; similarity is a word-overlap baseline, not an ML model; storage is JSON with a single moderator password. The fellowship work closes exactly these gaps.

## 6. Responsible AI

AI would only assist exploration. It would never rank policy importance or replace human judgment; every suggestion is labelled, explainable and subject to review. Models would be considered only if open, locally runnable (so contributors' text never goes to third-party services), evaluated per language with published results including negative ones, documented in a model card, and switchable off per language. Full statement: [RESPONSIBLE-AI.md](RESPONSIBLE-AI.md); evaluation design: [EVALUATION-PLAN.md](EVALUATION-PLAN.md).

## 7. Open source and open data commitment

- Code under the MIT licence from day one; documentation and sample data under CC BY 4.0
- Community-submitted text published only with explicit contributor consent, under a data licence agreed with BASAibu (default CC BY 4.0)
- All evaluation protocols, scripts and per-language results published in the repository
- Licences for fellowship outputs to be confirmed in consultation with Mozilla, as the fellowship terms provide

## 8. Risks and mitigations

| Risk | Mitigation |
|---|---|
| Models serve some languages poorly | Per-language evaluation; transparent baseline retained as fallback; model may be disabled per language |
| Moderation capacity is limited | Review queues designed for small teams; prioritisation tools that never rank importance; clear role definitions |
| Wiki structure differs from assumptions | Q1 is dedicated to research; the integration layer is isolated in one module |
| Privacy and re-identification | Consent, pseudonymous attribution, moderator review, no trackers, no raw IP storage |
| Sustainability after the fellowship | Simple dependency-free stack, documentation, handover and maintenance plan with BASAibu |
| Time-zone and communication | Based in Indonesia, in BASAibu's time zone |

## 9. Indicative use of the project budget

*Indicative allocation of the Embedded Fellowship project budget, to be agreed with Mozilla and BASAibu. The personal stipend is not itemised.*

| Item | Share of project budget (USD 25,000 assumed) |
|---|---|
| Native-speaker linguistic reviewers (compensated) | 8,000 |
| Community usability and accessibility testing sessions | 5,000 |
| Independent security review and accessibility audit | 4,000 |
| Hosting, small-model compute and infrastructure | 3,000 |
| Documentation, translation and open-data publication | 2,000 |
| Contingency, travel and community events | 3,000 |
| **Total** | **25,000** |

## 10. About the applicant

Helmy Ashari — developer of Local Language Policy Commons ([@ArkFunDev](https://github.com/ArkFunDev)), based in Indonesia.
*(Add 2–3 sentences on relevant experience, languages you speak, and prior community or open-source work.)*

## 11. Mozilla values alignment (short form)

| Value | How this project reflects it |
|---|---|
| Open source and open by design | MIT code, open documentation, open evaluation, consented open data |
| People-centred technology | Communities decide; software only assists |
| Trustworthy, fit-for-purpose AI | Small, explainable, locally run, evaluated per language, optional |
| Inclusion and linguistic diversity | Original wording preserved; local languages first-class |
| Privacy | No trackers or accounts; data minimisation; consent |
