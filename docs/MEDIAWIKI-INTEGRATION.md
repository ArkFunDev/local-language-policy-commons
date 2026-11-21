# MediaWiki Integration

## What exists today

`src/mediawiki.php` is a small **read-only** client for the MediaWiki Action API:

- `generator=search` for keyword search and `generator=categorymembers` for a configured category, both with `prop=extracts` (plain-text introductions)
- HTTPS only, 20 s timeout, ≤ 3 redirects, response cache (default 5 minutes)
- Descriptive `User-Agent` including a contact URL, as Wikimedia's API etiquette asks
- Moderators can **import** a page as a *pending* submission (source type `mediawiki`, source URL, attribution). Imports go through the same verification and review as any other submission.

The demonstration endpoint is Indonesian Wikipedia. Its pages are encyclopaedia articles, **not** community policy proposals, so results are labelled as external reference material.

## Pointing it at a BASAibu wiki

Once access is agreed, set in `config/config.local.php`:

```php
'mediawiki' => [
    'api_url'      => 'https://<wiki-host>/w/api.php',
    'page_base'    => 'https://<wiki-host>/wiki/',
    'source_label' => 'BASAibu <wiki name>',
    'category'     => 'Category:<proposal category>',   // as named on that wiki
    'contact'      => 'https://github.com/ArkFunDev/local-language-policy-commons',
],
```

## To be defined with BASAibu in Q1 (research phase)

These depend on how the real wikis are structured, so they are deliberately **not** guessed here:

1. How proposals are represented (namespace, category, templates, one page per proposal or per language?)
2. How language and topic are recorded on each page (template parameters, categories, Semantic MediaWiki properties?)
3. Whether wikitext should be parsed (via `action=parse` or `prop=revisions`) rather than plain-text extracts
4. Sync model: periodic pull, on-demand import, or both; handling of edits and deletions
5. Licence of wiki content and how it maps to the data licence used here
6. Whether authenticated access is required (bot password / OAuth), and who owns the credentials

## Principles

- **Read-only by default.** The platform never edits the wiki.
- **Provenance is kept.** Every imported record links back to its source page.
- **Original text is preserved verbatim.**
- **Be a good API citizen:** caching, identifiable User-Agent, low request volume.
