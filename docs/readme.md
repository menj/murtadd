# Murtadd

A classic-template child theme of Twenty Twenty-Five for **murtadd.org**.

| Field | Value |
|---|---|
| Theme slug | `murtadd` |
| Text domain | `murtadd` |
| Parent | `twentytwentyfive` |
| Version | 1.39.0 |
| Author | Langgam Fikir Enterprise |
| Licence | GPLv2 or later |
| Requires WP | 6.4+ |
| Requires PHP | 8.1+ |

## What it is

Three custom post types answer three different needs. **Doubts** give a short, sourced answer to a question a reader is actually carrying. **Rebuttals** state a circulated claim fairly and answer it at length with citations. **Fatwa** entries summarise scholarly positions in the site's own words, each linked to its primary source.

A shared **Topic** taxonomy spans all three. A locked four-term **Doubt Category** taxonomy (intellectual, scriptural, emotional, identity) drives the homepage triage grid. A **School** taxonomy filters the fatwa index.

The theme ships its own content: 12 Doubts and 17 Rebuttals, published on activation with their claims, sources, topics, categories, and cross-links already wired.

## Directory structure

```
murtadd/
├── assets/
│   ├── css/           Stylesheets, each with a .min counterpart
│   ├── fonts/         Source Serif 4 and Public Sans (OFL), self-hosted
│   ├── icons/         Site-icon mark: SVG source, PNG sizes, favicon.ico
│   └── js/            Scripts, each with a .min counterpart
├── docs/              All documentation, including this file
├── inc/               Feature modules (CPTs, taxonomies, meta, settings, schema, seeding)
├── languages/         murtadd.pot
├── template-parts/
│   ├── cards/         card-cross-link, card-doubt-category
│   ├── notices/       notice-struggling
│   └── rows/          row-fatwa-entry, row-list-entry
├── templates/         Page templates (page-start-here.php)
├── theme.json         Editor palette, typography, layout (v3)
├── style.css          Theme header only; never enqueued
├── readme.txt         WordPress-format readme (must stay at the root)
├── license.txt        GPLv2 and OFL attributions
└── *.php              Classic template hierarchy
```

Only files WordPress requires at the theme root stay there: `style.css`, `theme.json`, `functions.php`, `screenshot.png`, `readme.txt`, and the template hierarchy. Everything else is filed.

## Architecture

**Classic, not block.** The child ships `index.php` and no `templates/index.html`, so WordPress treats it as a classic theme: the parent's block templates and the Site Editor are bypassed, while the parent still satisfies the `Template:` header and supplies block-library CSS for editor-authored content. `theme.json` is present so the editor renders content the way the front end does.

**Assets are separated.** All CSS under `assets/css`, all JS under `assets/js`. No template contains a `<style>` or `<script>` block. Colour overrides inject through `wp_add_inline_style()` only. Every source file ships with a `.min` counterpart, and `MURTADD_ASSET_SUFFIX` serves sources under `SCRIPT_DEBUG` and minified files otherwise. **Editing a source without regenerating its `.min` pair changes nothing on a live site.**

**Loading is conditional.** Per-template CSS enqueues only on its own views. Admin JS enqueues only on the theme's own screens.

**JavaScript is an enhancement.** Every front-end behaviour works with JS disabled. The fatwa filters are a plain GET form; the script only auto-submits on change.

**Settings use the Settings API.** One option array per tab, in a tabbed screen at Appearance → Murtadd: Colours (with a live WCAG contrast check), Cross-links, Content display, Taxonomies, Setup.

## Setup

Activate the theme. It scaffolds itself: pages, footer menu, Reading options, rewrite rules, and the starter content. Nothing is ever overwritten and nothing is deleted, so re-activation is safe.

Upgrading in place does **not** fire the activation hook. On a site already running the theme, use **Appearance → Murtadd → Setup → Create anything missing**, which reports what is present and creates only what is absent.

Full detail in [setup.md](setup.md).

## Structured data

`inc/schema.php` emits JSON-LD: Doubts as `QAPage` with an `acceptedAnswer`, Rebuttals as `Article` with the claim under `about` and their sources under `citation`, Fatwa entries as `Article`, and `WebSite` plus `Organization` on the front page. Readers arrive at this site by typing a question into a search box, so the markup is load-bearing rather than decorative.

## Documentation

| File | Contents |
|---|---|
| [ssot.md](ssot.md) | Architecture and the binding rules. Read before changing anything. |
| [changelog.md](changelog.md) | Every release. |
| [upgrading.md](upgrading.md) | Migration steps, version to version. |
| [setup.md](setup.md) | Installation and what activation does. |
| [settings.md](settings.md) | Appearance → Murtadd, tab by tab. |
| [content-model.md](content-model.md) | The three post types and their fields. |

`readme.txt` at the theme root is the WordPress-format readme and stays there by convention.
