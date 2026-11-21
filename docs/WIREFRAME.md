# Screens and Wireframes

Each planned screen from the original concept now exists in the prototype. Page names refer to files in `public/`.

| # | Screen | Page | Purpose |
|---|---|---|---|
| 1 | Dashboard | `dashboard.php` | Overview of the installation |
| 2 | Proposal Explorer | `index.php` | Search and filter proposals across languages |
| 3 | Proposal Detail | `proposal.php` | Original text, Indonesian reference, provenance, related proposals, approved feedback |
| 4 | Similarity Explorer | `discovery.php` | Suggested related proposals with shared terms and review status |
| 5 | Community Review | `review.php`, `verify.php`, `feedback-queue.php` | Moderator verification, acceptance and feedback moderation |
| 6 | Submit | `submit.php` | Contributor submission with consent |
| 7 | MediaWiki Explorer | `mediawiki.php` | Browse a wiki; import as pending |
| 8 | Open Data | `export.php` | JSON / CSV download |
| 9 | About | `about.php` | Purpose, status, AI role, privacy, licences |

## Explorer (sketch)

```
┌────────────────────────────────────────────────────────────────────┐
│ Local Language Policy Commons                           EN | ID    │
│ Explorer · Topic Discovery · Submit · Dashboard · MediaWiki · Data │
├────────────────────────────────────────────────────────────────────┤
│ [Search…………]  [Language ▾]  [Topic ▾]  [Filter]                  │
│ 6 results · samples and community submissions are labeled apart    │
│ ┌────────────────────────────────────────────────────────────────┐ │
│ │ P002  Javanese · Healthcare access                             │ │
│ │ Warga desa butuh layanan kesehatan sing luwih cedhak…  (lang=jv)│ │
│ │ Indonesian reference: Warga desa membutuhkan layanan…          │ │
│ │ [Synthetic sample]                                             │ │
│ └────────────────────────────────────────────────────────────────┘ │
│                    ‹ Previous   Page 1 of 1   Next ›               │
└────────────────────────────────────────────────────────────────────┘
```

## Proposal Detail / Similarity (sketch)

```
Proposal SUB-1A2B3C4D          [Community] [Accepted] [Verification: Verified]
Original (lang="min")  ……………………………………………………………
Indonesian reference   ……………………………………………………………
Source · Attribution · Submitted
[ Give feedback or a correction ]

Potentially related proposals   (exploratory – never a ranking)
  P003  Overlap score 100% · Suggestion — not yet reviewed
  Shared terms: desa, layanan, kesehatan, …
  Moderators only: [Confirm as related] [Reject suggestion] [Reset review]
```

## User journey

1. A contributor opens the platform and browses or searches proposals.
2. They read proposals in their original languages, with the Indonesian reference alongside.
3. The platform suggests potentially related proposals and shows *why*.
4. Moderators and speakers review and confirm or reject suggestions.
5. Contributors can send language corrections and cultural-context notes, which moderators approve before publication.

## Design principles

- Preserve original language expressions.
- Make multilingual exploration accessible (keyboard, screen reader, small screens, dark mode, no JavaScript).
- Clearly distinguish automated suggestions from human decisions.
- Prioritise transparency and community control.

**Status:** Implemented in the v0.2 prototype. Interface details will be refined through user research with BASAibu and young contributors.
