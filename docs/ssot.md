# Murtadd — Single Source of Truth

Version 1.39.0 · Child theme of Twenty Twenty-Five · murtadd.org

## What this theme is

A classic-template (PHP) child theme of the block theme Twenty Twenty-Five. Because the child ships its own `index.php` and no `templates/index.html`, WordPress treats it as a classic theme: the parent's block templates and Site Editor are bypassed entirely, while the parent remains the declared `Template:` in `style.css` and supplies block-library and global-styles CSS for editor-authored content. Every front-end view is rendered by the child's own template hierarchy.

## Documentation

All Markdown lives in `docs/`, lowercase: `readme.md`, `changelog.md`, `upgrading.md`, `ssot.md`, `setup.md`, `settings.md`, `content-model.md`. `readme.txt` and `license.txt` stay at the theme root because WordPress and the licence convention require them there.

Six files state the version: `style.css`, `functions.php`, `readme.txt`, `docs/readme.md`, `docs/ssot.md`, and the top entry of `docs/changelog.md`. Bump them with an assertion, never a bare string replace. A replace that matches nothing fails silently, which is how this file sat at 1.10.0 for six releases while claiming to be the single source of truth.

## Architectural rules (binding)

0. **Every source asset has a `.min` twin.** `MURTADD_ASSET_SUFFIX` serves sources under `SCRIPT_DEBUG` and minified files otherwise. Editing a source without regenerating its `.min` file changes nothing on a live site. This is the single most likely way to waste an hour on this codebase.

1. **Asset separation.** All stylesheets live in `/assets/css/`, all scripts in `/assets/js/`. Templates never contain `<style>` or `<script>` blocks. Colour overrides are injected exclusively through `wp_add_inline_style()` on the `murtadd-main` handle.
2. **Conditional loading.** Per-template CSS (`doubt-template.css`, `rebuttal-template.css`, `fatwa-index.css`, `comments.css`) enqueues only on its views. Admin JS enqueues only on `appearance_page_murtadd` and `murtadd_*` edit screens.
3. **Progressive enhancement.** Every front-end behaviour works with JavaScript disabled. The fatwa filters are a plain GET form; JS only auto-submits on change and hides the button.
4. **Escaped output.** All front-end output flows through the helpers in `inc/template-tags.php` or direct `esc_*` calls.
5. **Settings.** Settings API only. One option array per tab: `murtadd_colours`, `murtadd_cross_links`, `murtadd_content_display`. Tabbed screen at Appearance → Murtadd; the Taxonomies tab links to native term screens and duplicates no CRUD.
6. **Doubt Categories are locked.** Exactly four terms (intellectual, scriptural, emotional, identity), capabilities set to `do_not_allow`, seeded once. They power the homepage grid and the support-notice logic.
7. **Word caps are soft.** The short-response cap warns in the editor and never blocks saving.
8. **Cross-links state no relationship.** Outbound cards and deep links open in a new tab with `rel="noopener"`; the front end never declares an affiliation.

## File structure

Asset directories are root-level: `css/`, `js/`, `fonts/`, `assets/icons/`. Feature modules live in `inc/`. Template parts are grouped by kind under `template-parts/{cards,rows,notices}/`. Page templates live in `templates/` and must stay exactly one directory deep, because that is as far as WordPress scans when building the page-template dropdown.

## Function naming

Every function is prefixed `murtadd_`, and the prefix alone does not prevent collisions inside the theme. `inc/taxonomy-topic.php` owns `murtadd_seed_topics()`; the starter content uses `murtadd_starter_topics()`. A redeclaration is a fatal error that takes down the front end and the admin together, and it passes a per-file lint, because each file is valid on its own. Load the whole of `functions.php` before shipping.

## Search

Front-end main-query search joins the meta keys listed in `murtadd_searchable_meta_keys()`. Any new meta field that carries reader-facing substance must be added there, or content stored in it is invisible to the site's own search — which is how Doubts shipped unsearchable in the first place.

## Markup and CSS must ship together

Every `murtadd-` class a template emits must have a rule in a stylesheet that is actually enqueued on that view. A template shipping markup with no CSS passes `php -l`, passes the load harness, and looks fine to anyone who never opens that particular page. The Start Here page shipped that way for nineteen releases. Check the class list against the stylesheets before packaging.

## Head output

`inc/meta-tags.php` owns the description/OG/Twitter tags and the robots rules; `inc/schema.php` owns JSON-LD. If a general SEO plugin is ever installed, disable the former, keep the latter (its QAPage/Claim markup is content-model-specific and no plugin will replicate it).

## Starter content

`inc/seed-data.php` holds the content, `inc/seed-content.php` inserts it, and both run from the same setup routine as the page scaffolding, under the same two invariants: never overwrite, never destroy. Rebuttals must be inserted before Doubts, since a Doubt stores its related Rebuttal by ID.

Two constraints on the data. Every Doubt's `related` slug must name a Rebuttal that exists, and all four doubt categories must be populated, because the homepage grid renders a count for each of the four and an empty one reads as a dead door.

## Letters, and why they are separate

`murtadd_letter` (`/letters/`) archives correspondence published elsewhere. It is deliberately outside the triage architecture: absent from the homepage grid, from site search, from the main feed, and from the four doors; below a divider in the sidebar; carrying a mandatory dateline and an archival frame on the single view.

The reasoning is binding and must not be quietly undone. The other three post types exist to meet a reader who arrived carrying a question, and the site's whole design assumes that reader is frightened and undecided. A letter is the author addressing an editor at a fixed past moment. Handing an archived letter to someone who searched their doubt would answer a question they did not ask, in a voice not written for them. Any change that surfaces Letters inside the triage path breaks that.

Where an archived position needs framing for a present-day reader, that is what the Editor's note field is for.

## Content model

| CPT | Slug | Meta (all `_murtadd_`-prefixed) | Taxonomies |
|---|---|---|---|
| `murtadd_doubt` | /doubts/ | doubt_statement, short_response, related_rebuttal, external_deep_link | topic, doubt_category |
| `murtadd_rebuttal` | /rebuttals/ | claim, claim_source_type, sources (repeater), related_fatwa, related_doubt | topic |
| `murtadd_fatwa` | /fatwa/ | scholar_or_body, era, position_summary, citation_link | topic, school |

Taxonomies: `murtadd_topic` (shared, flat, seeded 6 terms, `/topic/`), `murtadd_school` (fatwa, seeded 5 terms, `/school/`), `murtadd_doubt_category` (fixed 4 terms, locked, `/doubts/kind/`).

**URL note.** Doubt categories sit at `/doubts/{slug}/` with no interior segment: `/doubts/intellectual/`. This works only because the taxonomy is four locked terms, so the four URLs are explicit `top` rewrite rules ahead of the CPT rules; a wildcard there would shadow single doubts. The generated rewrite is off and the `term_link` filter is the single source of the public shape. The four category slugs are reserved against doubt posts in code (`wp_unique_post_slug`). Retired shapes 301 on `template_redirect`, 404s only: `/doubt-category/{slug}/` (pre-1.7.0) and `/doubts/kind/{slug}/` (1.7.0 to 1.17.0), both landing on the clean URL in one hop. If a fifth category is ever unlocked, the rule set must be extended with it — the URLs do not follow the terms automatically.

## Options

| Option | Keys | Defaults |
|---|---|---|
| murtadd_colours | accent, accent_tint | #0f6e56, #eefaf7 |
| murtadd_cross_links | ce_base_url, bismika_base_url | empty (links hidden) |
| murtadd_content_display | short_response_word_cap, show_struggling_notice, fatwa_index_default_sort, footer_heritage_note | 400, 1, scholar_az, "online since 2004" |
| murtadd_topics_seeded / murtadd_schools_seeded / murtadd_doubt_categories_seeded | flag | 1 after first init |

## Template map

- `front-page.php` — hero, four-category grid, cross-link block, four-column footer (renders whenever a static front page is set).
- `home.php` — blog index with right rail (ayah box + recently added).
- `single.php` / `page.php` / `index.php` — blog post, generic page, fallback.
- `page-start-here.php` — "Start Here" template (assign to slug `start-here`).
- `archive-murtadd_doubt.php`, `archive-murtadd_rebuttal.php`, `archive-murtadd_fatwa.php` — CPT archives; the fatwa archive carries the GET filter form and table layout.
- `single-murtadd_doubt.php`, `single-murtadd_rebuttal.php`, `single-murtadd_fatwa.php` — CPT singles.
- `taxonomy-murtadd_doubt_category.php`, `taxonomy-murtadd_topic.php`, `taxonomy-murtadd_school.php` — term archives. The school archive reuses the fatwa index table.
- `search.php`, `searchform.php`, `404.php`, `archive.php` — every fallback view is templated; nothing renders through a bare `index.php`.
- `template-parts/` — card-cross-link, card-doubt-category, notice-struggling, row-fatwa-entry, row-list-entry (shared mixed-type list row).
- `comments.php` — threaded comment list and response form; called from `single.php` and `page.php`. The three CPTs do not declare `comments` support.
- `header.php` / `sidebar.php` / `footer.php` — two-column shell. The sidebar is the primary navigation and carries routing only: brand, Start here, the content types, search. Utility links (About / FAQ / Contact / Privacy) belong in the footer, never the sidebar — they compete with triage and duplicate the front-page footer column. The `footer_site` menu location renders in exactly one place per view: the columns on the front page, the slim bar on inner pages.

## Activation

`inc/activation.php` scaffolds the site on `after_switch_theme`: pages, footer menu, Reading options, rewrite flush. Two invariants govern it and must not be broken: **it never overwrites** (existing pages, an assigned menu, and Reading options already chosen are all skipped) and **it never destroys** (no deletes, no trashing, no unpublishing). Re-activation is therefore idempotent.

It is re-runnable from Appearance → Murtadd → Setup, because upgrading the theme in place does not fire `after_switch_theme` and would otherwise leave an existing site unscaffolded. Any template that depends on a page must degrade visibly, not silently: when a required page is absent, `murtadd_setup_needed_notice()` says so in the admin.

Ordering matters. `after_switch_theme` fires on `after_setup_theme` (99), before `init`, so the routine only raises the `murtadd_needs_setup` flag there. The work itself runs on `init` at 90 — after the CPTs and taxonomies register — and clears `murtadd_rewrite_version` so the `init`/99 flusher writes a complete rule set.

## The wordmark

One component: `murtadd_the_logo( $variant )`, with `on-dark` (green panel) and `on-light` (ivory ground) variants. The strike and the full stop are the identity and are constant; only the ground changes, through the `--murtadd-logo-ink`, `--murtadd-logo-strike`, and `--murtadd-logo-stop-ink` custom properties. Generic rules must not reach into the component: `.murtadd-footer-col a` was outranking `.murtadd-logo` on specificity and stripping its typeface, which is why the footer link rule carries `:not(.murtadd-logo)`. Sizing is set only through `--murtadd-logo-size` (sidebar 24px, footer 34px) and the strike is specified in `em`, so it holds its weight against the letterforms at any size. The sidebar mark links home; the footer mark does not, and renders as a `span` so that nothing unclickable behaves as though it were. Hover and focus states are scoped to `a.murtadd-logo` and act on the strike, not the letters. Never write the wordmark markup inline — that is how the sidebar and footer marks diverged before 1.8.0.

## Colour on the green panel

Anything rendered inside `.murtadd-sidebar` sits on the accent green, so it must opt out of the global link and focus colours, which are tuned for the ivory ground. Every anchor in the sidebar declares its own `:hover`, and focus rings there are white. A new sidebar link without a `:hover` rule will inherit the dark-green hover and disappear into the panel.

## Prose measure

The wide views (front page, blog, fatwa index, school archive) run a 1000px column because grids and tables need it. Prose must never inherit that width: a line of 18px type across 1000px runs to about 110 characters, and reading breaks down past roughly 75. Where prose sits inside a wide view, give it a column of its own rather than a `max-width` that leaves dead space beside it. The hero does this: headline and lede side by side, each with a short measure, the pair filling the row. Measures are specified in `ch`, not px.

## Column width

`--murtadd-measure` on `.murtadd-main` sets the content column for the whole view; every direct child inherits it, so all blocks share the same left and right edges. Default 780px (prose). The front page, blog, fatwa index, and school archive take 1000px, since they carry grids and tables. Never cap an individual block with its own `max-width` — that is what broke alignment before 1.7.1. The slim footer bar is the sole exception and deliberately bleeds full width.

## The rail, and what it is quoting

Doubt and rebuttal singles carry a sticky right-hand rail of framed cards. This is deliberate détournement: the site murtadd.org answers used a stacked right-hand column of panels, and the silhouette is quoted so that a returning visitor recognises the skeleton and then registers that everything inside it has been turned. The scripture box is the payload — the same verse, in the same position, answering the opposite way.

The rule governing this: **echo the structure, never the styling.** Period tells (serif navigation, filled header strips, olive attribution, beveled chrome, centred foot rules) are not to be introduced; they turn an argument into pastiche. Nothing of the original's assets, copy, or markup is reproduced. The execution stays current.

## Imagery

Featured images are for **documentary** images only: a folio, a court document, a screenshot of a claim as it actually circulates. Never mood photography — no praying hands, no rain-streaked windows, no silhouettes. A reader in genuine distress can smell a stock photo from the first scroll, and it would cheapen the one page where they decide whether to trust this site.

Where no image is set, `inc/social-card.php` generates a card carrying the doubt itself. That is the honest equivalent of the photograph the answered site used: not a face, but the reader's own sentence, set as an object.

## Tests

`tests/` runs without a WordPress installation. Every assertion corresponds to a defect that actually shipped. When a new invariant is discovered the hard way, add a test for it in the same release — that is the entire point of the suite. Mutation-check new tests by re-introducing the defect: a test that cannot fail is worth nothing.

## Stylesheet ownership

`assets/css/tokens.css` owns the `:root` design tokens and both `@font-face` declarations. `assets/css/logo.css` owns the wordmark component. **Both are loaded by the front end and by the Theme Options screen** (`tokens → logo → main`, `tokens → logo → admin`).

There is exactly one definition of the mark and one of the brand colours in this codebase. Never restate either. The wordmark has now drifted twice by being hand-copied into a second context — once into the footer, once into the admin hero — and this structure is what makes a third time impossible. A rule that styles a *placement* (the footer's `:not(.murtadd-logo)` exclusion, a size override) belongs with its consumer; a rule that defines the *mark* belongs in `logo.css`.

## Theme Options

**Profiles and sameAs.** `inc/social.php` is the registry of 42 platforms (label + `currentColor` glyph). The Profiles settings tab saves a URL per platform into `murtadd_profiles`, plus `murtadd_author_name`. `footer.php` renders the non-empty URLs as an icon row; `inc/schema.php` emits the same URLs as `sameAs` on both the Organization and an author Person node (`#author`). One save drives both surfaces, and `tests/SocialTest.php` pins that the two sets are identical. Adding a platform means adding one glyph to the registry; nothing else changes.

**Overlap doctrine.** Roughly two thirds of this site's entries share a topic with the companion site, deliberately. Sharing a topic is fine; sharing an approach is not, and a paired entry that merely restates the other in a different tone is a duplicate whatever its register. The division that holds:

| | This site | Companion site |
|---|---|---|
| Direction | Shows the objection fails | Builds the positive case |
| Posture | Forensic. Names the fallacy, reaches a verdict | Pastoral. Walks the reader through the question |
| Scripture | Cited as evidence, with grades and numbers | Cited as grounding |
| Ground | Comparative: the specific charge, its primary evidence, and the law as it stands across Muslim-majority states, with any one country as an example among several | Universal and pastoral |
| Length | 350-500 words | 900-1500 words |
| Ending | The verdict | The framework, and the honest limits |

Malaysia is the site's home, and it is not its frame. Malaysian law or usage may appear as one example among several; no entry should be built on it alone, because the reader may be in Cairo, Jakarta, Lahore, or London.

The operative test before publishing a paired entry: **if the companion article already makes this argument, do not make it again — compress it to a sentence, point across, and spend the entry on what only this site can say.** `the-age-of-aisha` is the worked example. The companion piece runs the source criticism and the presentism argument at length; this one compresses both to two paragraphs and spends itself on how the same report is used by both the polemicist and the child-marriage apologist, and on what Muslim-majority legislatures have since enacted, which is forensic and legal ground the companion site's pastoral register does not occupy.

**Counterpart links.** `_murtadd_ce_slug` on rebuttals and doubts stores a bare article slug on the companion site; `template-parts/counterpart.php` renders it as `{ce_base_url}/articles/{slug}/`. Both the slug and the base URL must be present or nothing renders. The two sites overlap on roughly two thirds of this site's entries by design: this one answers charges in a forensic register, the other works the same questions through pastorally.

**Legacy redirects** (`inc/legacy-redirects.php`) map the pre-2024 murtadd.org paths, recovered from sitemap caches in an old backup, onto current pages and topic archives. Descriptors resolve at request time; a missing target is skipped, never chained. Depends on the `irtidad` page and the `apostasy-law` topic term both existing. Filter: `murtadd_legacy_redirect_map`.

The **Docs tab** is the exception to the Settings API pattern: it registers no setting group and renders `/docs/*.md` through `inc/docs.php` (Parsedown 1.8.0, vendored at `vendor/parsedown/`, safe mode + `wp_kses()`). Adding or renaming a document means editing `murtadd_docs_manifest()`; nothing else knows the filenames. The docs directory and `vendor/` must both ship in the build, or the tab renders an explanatory notice instead of the manual.

Appearance → Murtadd is the Settings API with a skin (`assets/css/admin.css`), not a custom framework. The markup stays `form-table`, so fields, nonces, sanitisers, and capability checks keep working; only the surface is restyled, and everything is scoped under `.murtadd-admin` so nothing leaks into the rest of wp-admin.

The gradient appears in exactly three places: the hero, the active tab, and the primary button. Do not spread it further. Restraint governs the admin as it governs the front end.

The version badge reads `MURTADD_VERSION` directly. It cannot drift, unlike the six files that state a version in text.

Tabs run in the order the work is done: Setup, Content, Topics & schools, Linked sites, Colours. **The tab keys are load-bearing**: `settings_fields( 'murtadd_' . $active )` maps each key to its registered settings group, so relabel and reorder freely, but never rename a key — a renamed key silently detaches the tab from its settings and the fields will appear to save while going nowhere. Labels must name what a person has, not what WordPress calls it internally.

## Exclusions (binding)

**No dark mode.** Do not add a `prefers-color-scheme: dark` variant, a toggle, or dark-mode tokens. The ivory ground and green panel are the design in full.

**No testimonials.** Do not add a testimonials section, post type, or archive: no wall of personal accounts from people who doubted, left, or stayed. The site answers questions; it does not collect people.

Both are owner decisions, recorded 2026-07-13. Do not re-propose either in reviews or enhancement lists.

## Design tokens

Defined once in `css/main.css` `:root`. Accent `#0f6e56` (green), tint `#eefaf7`, secondary `#5bc9bc`, ground `#fcf8ee` (warm ivory), serif Source Serif 4, sans Public Sans. Accent and tint are user-configurable from the Colours tab; everything else derives from the stylesheet.

## Branding assets

`screenshot.png` (1200×900) is the Appearance → Themes preview. `assets/icons/` holds the site-icon mark: `site-icon.svg` is the source, with 512/192/180/32/16 PNGs and `favicon.ico` derived from it. `inc/site-icon.php` serves them only while no core Site Icon is configured; `has_site_icon()` short-circuits the whole module the moment one is set. Fonts are self-hosted in `/fonts/` under the OFL.

## Data removal

Themes have no uninstall hook. The six `murtadd_*` options listed in readme.txt must be deleted manually if the theme is retired. Content persists by design.
