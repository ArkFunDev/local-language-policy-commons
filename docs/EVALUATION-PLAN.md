# Evaluation Plan

Status: **plan** — no evaluation with real contributors has been run yet. Targets and thresholds are set with BASAibu after a pilot, not decided here.

## Goals

1. Find out whether cross-language suggestions actually help reviewers find related proposals.
2. Measure **how well each language is served**, and make any disparity visible and actionable.
3. Test usability and accessibility with young contributors in real conditions.

## Research questions

- **RQ1.** How accurate are suggested pairs (as judged by bilingual speakers) for the transparent baseline and for each candidate model?
- **RQ2.** Does accuracy differ between languages, dialects or levels of Indonesian-reference quality?
- **RQ3.** Do suggestions change what reviewers find or how long it takes (compared with manual search)?
- **RQ4.** Is the interface usable and accessible for the intended contributors?

## Methods

**Pair judgement (RQ1, RQ2).** Sample proposal pairs from each language combination, including suggested pairs and random non-suggested pairs. At least two native/bilingual reviewers per language judge each pair independently: *related / not related / unsure*, with notes.

**Candidates.** (a) the current word-overlap baseline; (b) open multilingual sentence-embedding models that can run locally. Models are chosen after checking which Indonesian local languages they claim to cover, and are *also* tested on languages they do not claim to cover, to document failure honestly.

**Metrics.**

| Metric | Meaning |
|---|---|
| Precision@k | Share of the top-k suggestions judged related |
| Recall on seeded pairs | Share of known related pairs (identified by reviewers) that the method surfaces |
| Per-language gap | Difference in precision/recall between best- and worst-served languages |
| Inter-rater agreement | Cohen's/Fleiss' kappa between reviewers |
| Reviewer burden | Time per decision; share of suggestions rejected |

**Task study (RQ3).** Reviewers complete matching tasks with and without suggestions; compare time and findings.

**Usability and accessibility (RQ4).** Moderated sessions with young contributors on phones and low-bandwidth connections; System Usability Scale (SUS); keyboard-only and screen-reader checks against WCAG 2.2 AA criteria.

## Ethics and practice

- Reviewers are compensated for their time; participation is voluntary and consent is recorded.
- Evaluation uses consented, moderated data (or clearly synthetic data); no personal data is collected.
- Reviewers' disagreement is reported, not averaged away.

## Reporting

Publish in this repository: protocol, anonymised judgement data (where consented), analysis scripts, per-language results, and a candid account of what did not work. Decisions about adopting any model follow the [Responsible AI policy](RESPONSIBLE-AI.md).

## Timeline

Aligned with the [roadmap](../ROADMAP.md): protocol in Q1, baseline measurements in Q2, model comparison and reviewer studies in Q3, usability testing and final report in Q4.
