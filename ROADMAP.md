# Roadmap — Local Language Policy Commons

**Project duration (proposed):** 12 months, June 2027 – May 2028, aligned with a host-organisation partnership with BASAibu. Dates and scope will be confirmed with BASAibu.

## Foundation work — completed (prototype v0.2, November 2026)

Working prototype, independently developed before any partnership: explorer, detail pages, submission with consent, moderation workflow, MediaWiki read-only client, transparent similarity baseline with pair review, open-data export, bilingual accessible interface, security hardening, tests and CI, and full documentation. See [CHANGELOG.md](CHANGELOG.md).

## Quarter 1 — Research and design
**June – August 2027**

Objectives
- Work with BASAibu and young contributors; learn the real wiki workflows and data structures
- Run user research; identify needs, languages and moderation capacity
- Agree data licence, privacy and moderation rules with BASAibu
- Finalise the evaluation protocol

Deliverables
- User research summary
- MediaWiki integration plan (see [docs/MEDIAWIKI-INTEGRATION.md](docs/MEDIAWIKI-INTEGRATION.md))
- Updated wireframes and architecture
- Evaluation protocol

## Quarter 2 — Core development
**September – November 2027**

Objectives
- Integrate with BASAibu's MediaWiki structure
- Move storage to a database; per-moderator accounts and roles
- Strengthen validation and security; independent security review
- Measure the baseline per language

Deliverables
- Integrated application running against BASAibu's wiki (staging)
- Database-backed storage and migration scripts
- Security review report and fixes
- Baseline measurements

## Quarter 3 — Similarity and human review
**December 2027 – February 2028**

Objectives
- Evaluate candidate open, locally-run multilingual models against the baseline, language by language
- Build the community review interface for suggestions and language corrections
- Test with contributors from different language communities
- Evaluate linguistic representation and potential AI bias

Deliverables
- Evaluation report with per-language results, including negative findings
- Community review interface
- Model cards for any model proposed for use (or a documented decision not to use one)

## Quarter 4 — Validation and open release
**March – May 2028**

Objectives
- Community usability and accessibility testing
- Refine based on feedback
- Publish code, documentation, and (where consented) an open dataset
- Prepare long-term maintenance with BASAibu

Deliverables
- Validated working system
- Public release with user and technical documentation
- Final evaluation report
- Maintenance and sustainability plan, with handover to BASAibu

## Success indicators

Progress is measured by:

- Integration with BASAibu's wiki running reliably, with original-language text preserved
- Reviewers confirming or rejecting suggestions in real use
- Published per-language evaluation results
- Documented usability and accessibility feedback
- Public release of reusable code, documentation and data

Quantitative targets will be set with BASAibu after Q1 research. *Proposed starting points, to be confirmed:* at least five local languages reviewed by at least two native or bilingual reviewers each; every adopted AI feature accompanied by published per-language results.

## Long-term vision

Reusable, open-source public-interest infrastructure that helps multilingual communities take part in civic discussion while keeping control over their language, knowledge and priorities.

---

**Project:** Local Language Policy Commons · **Developer:** Helmy Ashari · **Status:** Working prototype (v0.2); partnership and funding proposed.
