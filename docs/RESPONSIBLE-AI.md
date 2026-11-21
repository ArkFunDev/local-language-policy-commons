# Responsible AI Statement

## Stance

AI, where used here, **assists discovery and exploration**. It does not determine policy priorities, rank ideas, speak for communities, or replace human judgment. Communities keep control over their language, their knowledge and their priorities.

## Where the project stands today

**Version 0.2 uses no machine-learning model.** Suggestions come from a transparent word-overlap method on the Indonesian reference text (see [SYSTEM-ARCHITECTURE.md](SYSTEM-ARCHITECTURE.md)). This is deliberate: it provides an explainable baseline to measure any future model against.

## What the system will never do

- Score, rank or "trend" proposals by importance or popularity
- Alter the original text of a proposal
- Publish an automated suggestion without marking it as automated
- Send contributors' text to third-party AI services
- Profile individual contributors

## Principles and how they are implemented

| Principle | In practice |
|---|---|
| **Human oversight** | Moderators and speakers confirm or reject every suggested pair; accepted/rejected states are stored and visible |
| **Explainability** | Every suggestion shows its shared terms, score and method; the *How does this work?* panel explains the method in plain language |
| **Original meaning first** | Original text is stored verbatim and always displayed; Indonesian text is labelled a *reference* |
| **Privacy and data minimisation** | No accounts, analytics or trackers; no raw IP storage; contributors are warned not to submit personal data; consent recorded for publication and export |
| **Consent and licensing** | Explicit licence consent per submission; only consented, accepted records are exported |
| **Honest labelling** | Synthetic data is labelled synthetic; the UI and README state that no real-world participation is claimed |
| **Accountability** | Moderator actions are written to an audit log; corrections are accepted through issues and the feedback queue |

## Risks specific to low-resource languages

| Risk | Why it matters | Mitigation |
|---|---|---|
| Models perform unevenly across languages | Languages with little online text (many Indonesian local languages) are usually served worse, so related proposals in those languages could be missed or mismatched | Evaluate **per language** with native-speaker reviewers; publish disparities; keep the transparent baseline as a fallback; never present model output as authoritative |
| Reference text bias | Similarity on Indonesian reference text depends on the quality of that text and can favour languages whose contributors write fluent Indonesian | Record who wrote the reference; allow speaker corrections; evaluate original-text methods where feasible |
| Dialect and register variation | One language name hides many varieties and registers | Allow dialect notes in feedback; do not force normalisation of the original |
| Over-trust in suggestions | People may treat suggestions as endorsements | Persistent labels, visible shared terms, "exploratory" wording, no scores framed as importance |
| Harmful or personal content | Open submission invites misuse | Moderation before publication, honeypot and rate limits, feedback queue, takedown path |
| Re-identification through open data | Small communities, distinctive text | Pseudonymous attribution, consent, moderator review, guidance not to include personal details |

## Policy for adopting any model (planned)

A model is considered only if **all** apply:

1. It is open (code and weights under a licence that permits this use) and can run **locally** on modest hardware, so contributor text never leaves the operator's infrastructure.
2. It has been **evaluated per language** against the baseline using the [evaluation plan](EVALUATION-PLAN.md), and results (including negative ones) are published.
3. A short **model card** documents its source, training-data caveats, known weaknesses and the languages where it should not be used.
4. It can be **switched off** per language without breaking the platform.
5. Moderators and BASAibu's community agree to its use after seeing the evaluation.

## Governance

Decisions about whether to adopt AI features, which languages to enable, and how data is licensed belong to the host community organisation (BASAibu) with its contributors, not to the developer alone. The developer's role is to build, document and evaluate openly.

## Reporting concerns

Open an issue (without personal data) or follow [SECURITY.md](../SECURITY.md) for vulnerabilities.
