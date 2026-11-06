# Murtadd: single source of truth

Version 1.57.0 · Child theme of Twenty Twenty-Five · murtadd.org

## What this theme is

A classic-template (PHP) child theme of the block theme Twenty Twenty-Five. Because the child ships its own `index.php` and no `templates/index.html`, WordPress treats it as a classic theme: the parent's block templates and Site Editor are bypassed entirely, while the parent remains the declared `Template:` in `style.css` and supplies block-library and global-styles CSS for editor-authored content. Every front-end view is rendered by the child's own template hierarchy.

## Documentation

All Markdown lives in `docs/`, lowercase: `readme.md`, `changelog.md`, `upgrading.md`, `ssot.md`, `setup.md`, `settings.md`, `content-model.md`. `readme.txt`, `LICENSE` and `license.txt` stay at the theme root because WordPress and the licence convention require them there.

**Licence.** The theme is licensed under the GNU General Public License, version 3 or later (owner decision, 2026-11-05). `LICENSE` holds the full, unmodified text; `style.css` and `readme.txt` name it; the fonts keep their own licences, listed in `license.txt`.

Six files state the version: `style.css`, `functions.php`, `readme.txt`, `docs/readme.md`, `docs/ssot.md`, and the top entry of `docs/changelog.md`. Bump them with an assertion, never a bare string replace. A replace that matches nothing fails silently, which is how this file sat at 1.10.0 for six releases while claiming to be the single source of truth.

## Architectural rules (binding)

0. **Every source asset has a `.min` twin.** `MURTADD_ASSET_SUFFIX` serves sources under `SCRIPT_DEBUG` and minified files otherwise. Editing a source without regenerating its `.min` file changes nothing on a live site. This is the single most likely way to waste an hour on this codebase.

1. **Asset separation.** All stylesheets live in `/assets/css/`, all scripts in `/assets/js/`. Templates never contain `<style>` or `<script>` blocks. Colour overrides are injected exclusively through `wp_add_inline_style()` on the `murtadd-main` handle.
2. **Conditional loading.** Per-template CSS (`doubt-template.css`, `rebuttal-template.css`, `fatwa-index.css`, `comments.css`) enqueues only on its views. Admin JS enqueues only on `appearance_page_murtadd` and `murtadd_*` edit screens.
3. **Progressive enhancement.** Every front-end behaviour works with JavaScript disabled. The fatwa filters are a plain GET form; JS only auto-submits on change and hides the button.
4. **Escaped output.** All front-end output flows through the helpers in `inc/template-tags.php` or direct `esc_*` calls.
5. **Settings.** Settings API only. One option array per tab: `murtadd_colours`, `murtadd_cross_links`, `murtadd_contextual_links`, `murtadd_content_display`, `murtadd_security`, `murtadd_upkeep`. Tabbed screen at Appearance → Theme Options; the Taxonomies tab links to native term screens and duplicates no CRUD.
6. **Doubt Categories are locked.** Exactly four terms (intellectual, scriptural, emotional, identity), capabilities set to `do_not_allow`, seeded once. They power the homepage grid and the support-notice logic.
7. **Word caps are soft.** The short-response cap warns in the editor and never blocks saving.
8. **Cross-links state no relationship.** Outbound cards and deep links open in a new tab with `rel="noopener"`; the front end never declares an affiliation. Contextual links inside the text are the one exception to the new tab: they open in the same tab, like any link in running prose, carry `rel="noopener"` when they leave the site, and look the same whichever site they lead to. The "Not sure where to start?" quiz card is the second exception: it opens in the same tab and carries only `?from=murtadd`.

## File structure

Asset directories are root-level: `css/`, `js/`, `fonts/`, `assets/icons/`. Feature modules live in `inc/`. Template parts are grouped by kind under `template-parts/{cards,rows,notices}/`. Page templates live in `templates/` and must stay exactly one directory deep, because that is as far as WordPress scans when building the page-template dropdown.

## Function naming

Every function is prefixed `murtadd_`, and the prefix alone does not prevent collisions inside the theme. `inc/taxonomy-topic.php` owns `murtadd_seed_topics()`; the starter content uses `murtadd_starter_topics()`. A redeclaration is a fatal error that takes down the front end and the admin together, and it passes a per-file lint, because each file is valid on its own. Load the whole of `functions.php` before shipping.

## Search

Front-end main-query search joins the meta keys listed in `murtadd_searchable_meta_keys()`. Any new meta field that carries reader-facing substance must be added there, or content stored in it is invisible to the site's own search: which is how Doubts shipped unsearchable in the first place.

## Markup and CSS must ship together

Every `murtadd-` class a template emits must have a rule in a stylesheet that is actually enqueued on that view. A template shipping markup with no CSS passes `php -l`, passes the load harness, and looks fine to anyone who never opens that particular page. The Start Here page shipped that way for nineteen releases. Check the class list against the stylesheets before packaging.

## Head output

`inc/meta-tags.php` owns the description/OG/Twitter tags and the robots rules; `inc/schema.php` owns JSON-LD, built by `murtadd_structured_data_node()` and printed by `murtadd_structured_data()`. The graph is the view's main node (`murtadd_structured_data_primary_node()`) plus the BreadcrumbList (`murtadd_breadcrumb_node()`); a view with one node prints it alone, as before. The owner keeps all schema.org markup, Google-supported or not, for GEO, AEO and AIO (decided 2026-11-03): a doubt is a QAPage, the FAQ page and doubt category archives carry an FAQPage, and the WebSite carries a SearchAction. `inc/schema-graph.php` adds the page frame (a WebPage of the right subtype, the sidebar and rail as WPSideBar, the footer as WPFooter), verses as Quotations of the Qur'an, the glossary terms an entry defines as a DefinedTermSet, and the featured image as an ImageObject; it marks only what the page shows. Pinned in `tests/StructuredDataTest.php`, `tests/FaqSchemaTest.php` and `tests/SchemaGraphTest.php`.

**Title tag.** `inc/seo-title.php` builds every title as `<page title> | <site name>`, under 60 characters (`MURTADD_TITLE_MAX`, 59), and runs the same builder on `rank_math/frontend/title`. Beside " | murtadd.org" a page part has 45 characters. A page whose title is longer, or shared with another page, gets a page part in `inc/data/seo-titles.json`, keyed `post_type/slug`; check a new or changed title against `tests/TitleTest.php` before it is committed. The same file prints the canonical link on archives and the image sitemap.

**Breadcrumbs.** `murtadd_breadcrumb_trail()` (`inc/template-tags.php`) builds the trail and `murtadd_breadcrumbs()` prints it at the top of the entry or archive header, styled by `assets/css/breadcrumbs.css`. Doubt: Doubts, its doubt category. Rebuttal: Rebuttals, its topic. Fatwa entry: the fatwa index, its school; the index is left out while it has no published entry, as the navigation leaves it out. Letter: Letters. Page: its parent pages. Post: the posts page. Taxonomy and post-type archives: the archive above them. The BreadcrumbList is built from the same trail, so the two cannot disagree; switching breadcrumbs off on the Content tab removes both. With Rank Math's breadcrumbs on (`murtadd_rank_math_breadcrumbs_active()`), Rank Math's trail is shown and its BreadcrumbList stands. Pinned in `tests/BreadcrumbsTest.php`.

**With Rank Math active** (`inc/rank-math.php`, detected by `RANK_MATH_VERSION`) nothing needs switching off by hand. The meta tags stand down, and the page carries one graph, Rank Math's. The theme's nodes go into it through `rank_math/json_ld` by one rule: a node is added only when the graph has no node of its family, and where it has one the theme fills empty fields (`about`, `citation`, `image`, and the author's `url`) and overwrites nothing. It removes nothing from Rank Math's graph. In practice a doubt adds its QAPage, a rebuttal's Article gains its Claim and sources, a fatwa entry's Article gains its subject and primary source, the front page adds nothing, and a BreadcrumbList is added only when Rank Math's graph has none. An empty Rank Math description is filled from the content model; search results and 404s stay noindex. With Rank Math's Schema module off, the theme prints its own graph as before. Pinned in `tests/RankMathTest.php`.

## Starter content

The content is JSON in `inc/data/seed/` (`topics.json`, `rebuttals.json`, `doubts.json`, `pages.json`, `guides.json`, `journal.json`, and the default terms: `schools.json`, `doubt-categories.json`, `default-topics.json`), read and decoded by the loaders in `inc/seed-data.php` and never executed as PHP. `inc/seed-content.php` inserts it, and both run from the same setup routine as the page scaffolding, under the same two invariants: never overwrite, never destroy. Rebuttals must be inserted before Doubts, since a Doubt stores its related Rebuttal by ID.

Links in `pages.json` are written as `{{url:/path/}}` tokens; `murtadd_seed_page_bodies()` resolves each with `esc_url( home_url() )` when the copy is loaded, so a site in a subdirectory links correctly. A seed file that is missing, not valid JSON or not the expected shape loads as an empty array, and the seeder then creates nothing from it.

Two constraints on the data. Every Doubt's `related` slug must name a Rebuttal that exists, and all four doubt categories must be populated, because the homepage grid renders a count for each of the four and an empty one reads as a dead door.

## Guides

`murtadd_guide` (`/guides/{slug}/`, `inc/cpt-guide.php`) holds long-form guides (1,500 words and more) for a reader working through a question at length. Guides stay a post type of their own, separate from the Journal, because they are evergreen and the sister sites link to fixed addresses: `/guides/islamic-dilemma/` is linked from all six. Never change a guide's slug. Guides render through `single.php` and `archive.php` and share the `murtadd_topic` terms. Their copy is `inc/data/seed/guides.json`, loaded by `murtadd_seed_guides()` and inserted by `murtadd_seed_guide_posts()` (`inc/seed-guides.php`) under the same invariants as the rest of the starter content: never overwrite, never destroy, never resurrect (slugs recorded under `murtadd_guide` in `murtadd_seeded_slugs`). Links are `{{url:/path/}}` tokens, resolved when a guide is inserted, and the sources list is appended to the stored content. The guide rules arrived inside 1.43.0, so `murtadd_guide_rewrite_once()` clears the rewrite stamp once and the existing flusher does the flush. Guides are written care-first and carry safety guidance wherever they touch risk; every hadith carries a shamela.ws link, with al-Albani's grading where scholars differ. Pinned in `tests/GuidesTest.php`.

## Journal

The Journal (`/journal/`) holds dated posts: standard `post` entries on doubt, leaving and the rulings on apostasy. Guides stay separate and keep their addresses; nothing moves between the two. The posts page is the `journal` page, rendered by `home.php`; a post renders through `single.php`. Styles are in `assets/css/journal.css` (with `.min`), loaded only on those two views, light only. The sidebar and the footer bar link to the Journal through `murtadd_journal_url()`, which reads the posts page and falls back to `/journal/`.

A post is a BlogPosting in the one graph (`inc/schema.php`), `isPartOf` the WebSite and the Journal as a Blog, and the WebPage's `mainEntity`.

The copy is `inc/data/seed/journal.json`, inserted by `murtadd_seed_journal_posts()` (`inc/seed-journal.php`) under the starter-content invariants, with slugs recorded under `post` in `murtadd_seeded_slugs`. Every post carries a fixed `post_date` in site time: a past date is published, and a future one is stored as `future` and published by WordPress on its day. Never give a relative date, which drifts with the day the seeder runs. A second run changes nothing. Posts run 700 to 1,100 words, cite the Qur'an by surah and verse and hadith by collection and number, link to this site's pages and to one sister page, and have slugs without digits. Pinned in `tests/JournalTest.php`.

Before 1.51.0 the posts page was a seeded `blog` page. `murtadd_journal_page_once()` renames that page to `journal` in place and moves the posts page with it only when the posts page was unset or still that page; an editor's own Blog page is kept. Where a Journal page already exists beside the seeded Blog page, the Blog page goes back to draft and the posts page moves to the Journal. `/blog/` 301s to the posts page wherever it is set (legacy map kind `posts`).

**The post page** (`inc/article.php`, `assets/css/article.css`). A Journal post shows, in this order: the breadcrumb, the date with "Last reviewed" once edited more than a day later and the reading time, the title, the excerpt as the dek, "In short" with the key points (`_murtadd_takeaways`), the contents from three level-two headings, the text, the sources (`_murtadd_sources`, the rebuttal repeater), the next step and three related posts. The date stays above the title, as everywhere in the Journal. **No byline**: the author is named in the structured data only. **No next step sells**: it is the next post, the sources or a question through the Contact page. The FAQPage is built only from Details blocks in the FAQ style that the post shows. Guides share `single.php` and do not get these parts. Pinned in `tests/ArticleTest.php`.

The seeder runs under a lock (`murtadd_journal_seeding`, taken with `add_option()`, released in a `finally`, taken over after ten minutes), and stores the list hash only when every post was created or found, so a failed insert is tried again. With Rank Math, its Article becomes a BlogPosting and gains the Journal in `isPartOf`; nothing else is overwritten. `single.php` links a post back to the Journal and a guide back to Guides.

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
| `murtadd_guide` | /guides/ | sources | topic |
| `post` (Journal) | /{slug}/ | takeaways, sources (repeater) | topic, category, post_tag |

Taxonomies: `murtadd_topic` (shared with Journal posts and guides, flat, seeded 6 terms, `/topic/`), `murtadd_school` (fatwa, seeded 5 terms, `/school/`), `murtadd_doubt_category` (fixed 4 terms, locked, `/doubts/kind/`).

**URL note.** Doubt categories sit at `/doubts/{slug}/` with no interior segment: `/doubts/intellectual/`. This works only because the taxonomy is four locked terms, so the four URLs are explicit `top` rewrite rules ahead of the CPT rules; a wildcard there would shadow single doubts. The generated rewrite is off and the `term_link` filter is the single source of the public shape. The four category slugs are reserved against doubt posts in code (`wp_unique_post_slug`). Retired shapes 301 on `template_redirect`, 404s only: `/doubt-category/{slug}/` (pre-1.7.0) and `/doubts/kind/{slug}/` (1.7.0 to 1.17.0), both landing on the clean URL in one hop. If a fifth category is ever unlocked, the rule set must be extended with it: the URLs do not follow the terms automatically.

## Options

| Option | Keys | Defaults |
|---|---|---|
| murtadd_colours | accent, accent_tint | #0f6e56, #eefaf7 |
| murtadd_cross_links | ce_base_url, bismika_base_url, show_counterpart, show_quiz_card | empty (links hidden; the quiz card alone falls back to https://compelling-evidence.com until the option is saved), empty, 1, 1 |
| murtadd_contextual_links | own_enabled, sister_enabled, own_max, sister_max, extra, excluded | 1, 1, 4, 3, empty, empty |
| murtadd_security | hide_authors, xmlrpc_off, app_passwords_off, rest_lock, spam_gate, spam_gone, headers, login_slug, login_design, login_message, login_tagline, login_privacy | 1, 1, 1, 1, 1, 1, 1, empty (private login off), 1, empty, empty, 1 |
| murtadd_upkeep | gone_paths, gone_presets, maintenance, error_pages | empty, 1, 0, 1 |
| murtadd_content_display | short_response_word_cap, show_struggling_notice, fatwa_index_default_sort, footer_heritage_note, contact_recipient, journal_answer_box, journal_contents, journal_mid_next, journal_related | 400, 1, scholar_az, "online since 2004", empty (administration email), 1, 1, 0, 1 |
| murtadd_topics_seeded / murtadd_schools_seeded / murtadd_doubt_categories_seeded | flag | 1 after first init |

## Template map

- `front-page.php`: hero, four-category grid, cross-link block, four-column footer (renders whenever a static front page is set).
- `home.php`: the Journal index at `/journal/`, with right rail (ayah box + recently added).
- `single.php` / `page.php` / `index.php`: Journal post, generic page, fallback.
- `page-start-here.php`: "Start Here" template (assign to slug `start-here`).
- `archive-murtadd_doubt.php`, `archive-murtadd_rebuttal.php`, `archive-murtadd_fatwa.php`: CPT archives; the fatwa archive carries the GET filter form and table layout.
- `single-murtadd_doubt.php`, `single-murtadd_rebuttal.php`, `single-murtadd_fatwa.php`: CPT singles.
- `taxonomy-murtadd_doubt_category.php`, `taxonomy-murtadd_topic.php`, `taxonomy-murtadd_school.php`: term archives. The school archive reuses the fatwa index table.
- `search.php`, `searchform.php`, `404.php`, `archive.php`: every fallback view is templated; nothing renders through a bare `index.php`.
- `template-parts/`: card-cross-link, card-doubt-category, card-quiz, notice-struggling, rail-single, row-fatwa-entry, row-list-entry (shared mixed-type list row) and counterpart.
- `comments.php`: threaded comment list and response form; called from `single.php` and `page.php`. The three CPTs do not declare `comments` support.
- `header.php` / `sidebar.php` / `footer.php`: two-column shell. `.murtadd-main` is the column; `<main id="murtadd-content">` inside it holds the content only. `murtadd_open_site_footer()` closes `<main>` and opens the one `<footer class="murtadd-site-footer">`, which holds the front page's columns and the bar, so the footer is the contentinfo landmark on every view. The sidebar is the primary navigation and carries routing only: brand, Start here, the content types, search. Utility links (About / FAQ / Contact / Privacy) belong in the footer, never the sidebar: they compete with triage and duplicate the front-page footer column. The `footer_site` menu location renders in exactly one place per view: the columns on the front page, the slim bar on inner pages.

## Activation

`inc/activation.php` scaffolds the site on `after_switch_theme`: pages, footer menu, Reading options, rewrite flush. Two invariants govern it and must not be broken: **it never overwrites** (existing pages, an assigned menu, and Reading options already chosen are all skipped) and **it never destroys** (no deletes, no trashing, no unpublishing). Re-activation is therefore idempotent.

**The one exception: retiring the placeholders (1.41.0).** Before 1.41.0 activation published five pages reading "Placeholder. Replace this text." and seeded two lorem-ipsum letters. `murtadd_retire_placeholder_copy()` runs on `admin_init` until every write has landed (flag `murtadd_placeholder_copy_retired`, set only then): a page whose text, compared as a reader sees it, is still exactly the placeholder gets its written copy, and a letter whose text still matches the seeded lorem goes back to draft. Anything an editor has changed fails the comparison, and so does copy carrying markup the theme never seeded (a link, an image), and is left as it is. A page activated under another locale stored that locale's placeholder, which the English and current-locale forms do not match; it is matched against the placeholder as translated in every language installed on the site (`murtadd_legacy_placeholder_strings()` under `switch_to_locale()`), so only a genuine translation can match; a page that is no translation of the placeholder, however short or unedited, keeps its content. Lorem letters were never translated, so they have no such case. No other code may overwrite or unpublish. Letters are no longer seeded at all, and the fatwa index renders an "in preparation" state, `noindex`, while it has no published entry (`murtadd_fatwa_index_has_entries()`).

**The second pass (1.43.0).** The Contact and Privacy copy seeded from 1.41.0 carried an HTML-comment note asking the owner for an address and a host. 1.43.0 ships a contact form and a Privacy page written from the code, so the same function replaces that copy too, when the stored page is exactly it: the comparison is an md5 of `murtadd_normalise_markup()` (block-editor comments dropped, whitespace collapsed, every element and comment kept) against the hashes in `murtadd_legacy_placeholder_copy()['written']`. Any edit, including deleting only the note, fails it. The flag now holds a version (`murtadd_placeholder_retire_version()`, 2); a site at 1 runs the pass once more, harmlessly.

It is re-runnable from Appearance → Theme Options → Setup, because upgrading the theme in place does not fire `after_switch_theme` and would otherwise leave an existing site unscaffolded. Any template that depends on a page must degrade visibly, not silently: when a required page is absent, `murtadd_setup_needed_notice()` says so in the admin.

Ordering matters. `after_switch_theme` fires on `after_setup_theme` (99), before `init`, so the routine only raises the `murtadd_needs_setup` flag there. The work itself runs on `init` at 90: after the CPTs and taxonomies register: and clears `murtadd_rewrite_version` so the `init`/99 flusher writes a complete rule set.

## The wordmark

One component: `murtadd_the_logo( $variant )`, with `on-dark` (green panel) and `on-light` (ivory ground) variants. The strike and the full stop are the identity and are constant; only the ground changes, through the `--murtadd-logo-ink`, `--murtadd-logo-strike`, and `--murtadd-logo-stop-ink` custom properties. Generic rules must not reach into the component: `.murtadd-footer-col a` was outranking `.murtadd-logo` on specificity and stripping its typeface, which is why the footer link rule carries `:not(.murtadd-logo)`. Sizing is set only through `--murtadd-logo-size` (sidebar 24px, footer 34px) and the strike is specified in `em`, so it holds its weight against the letterforms at any size. The sidebar mark links home; the footer mark does not, and renders as a `span` so that nothing unclickable behaves as though it were. Hover and focus states are scoped to `a.murtadd-logo` and act on the strike, not the letters. Never write the wordmark markup inline: that is how the sidebar and footer marks diverged before 1.8.0.

## Colour on the green panel

Anything rendered inside `.murtadd-sidebar` sits on the accent green, so it must opt out of the global link and focus colours, which are tuned for the ivory ground. Every anchor in the sidebar declares its own `:hover`, and focus rings there are white. A new sidebar link without a `:hover` rule will inherit the dark-green hover and disappear into the panel.

## Prose measure

The wide views (front page, blog, fatwa index, school archive) run a 1000px column because grids and tables need it. Prose must never inherit that width: a line of 18px type across 1000px runs to about 110 characters, and reading breaks down past roughly 75. Where prose sits inside a wide view, give it a column of its own rather than a `max-width` that leaves dead space beside it. The hero does this: headline and lede side by side, each with a short measure, the pair filling the row. Measures are specified in `ch`, not px.

## Column width

`--murtadd-measure` on `.murtadd-main` sets the content column for the whole view; every direct child inherits it, so all blocks share the same left and right edges. Default 780px (prose). The front page, blog, fatwa index, and school archive take 1000px, since they carry grids and tables. Never cap an individual block with its own `max-width`: that is what broke alignment before 1.7.1. The slim footer bar is the sole exception and deliberately bleeds full width.

## The rail, and what it is quoting

Doubt and rebuttal singles carry a sticky right-hand rail of framed cards. This is deliberate détournement: the site murtadd.org answers used a stacked right-hand column of panels, and the silhouette is quoted so that a returning visitor recognises the skeleton and then registers that everything inside it has been turned. The scripture box is the payload: the same verse, in the same position, answering the opposite way.

The rule governing this: **echo the structure, never the styling.** Period tells (serif navigation, filled header strips, olive attribution, beveled chrome, centred foot rules) are not to be introduced; they turn an argument into pastiche. Nothing of the original's assets, copy, or markup is reproduced. The execution stays current.

## Imagery

Featured images are for **documentary** images only: a folio, a court document, a screenshot of a claim as it actually circulates. Never mood photography: no praying hands, no rain-streaked windows, no silhouettes. A reader in genuine distress can smell a stock photo from the first scroll, and it would cheapen the one page where they decide whether to trust this site.

Where no image is set, `inc/social-card.php` generates a card carrying the doubt itself. That is the honest equivalent of the photograph the answered site used: not a face, but the reader's own sentence, set as an object.

## Tests

`tests/` runs without a WordPress installation. Every assertion corresponds to a defect that actually shipped. When a new invariant is discovered the hard way, add a test for it in the same release: that is the entire point of the suite. Mutation-check new tests by re-introducing the defect: a test that cannot fail is worth nothing.

## Stylesheet ownership

`assets/css/tokens.css` owns the `:root` design tokens and the three `@font-face` declarations (Source Serif 4, Public Sans, and the Qur'an face). `assets/css/logo.css` owns the wordmark component. **Both are loaded by the front end and by the Theme Options screen** (`tokens → logo → main`, `tokens → logo → admin`). The login screen loads `tokens → login`, and the standalone error pages link `tokens.min.css` from the theme. The block editor canvas loads `tokens → editor` through `add_editor_style()` (`assets/css/editor.css`: the body, links, headings, quotations and Arabic of the front end), so an editor writes in the faces a reader sees; it never sets the Qur'an face, because the editor shows verse text the mapper has not fitted.

There is exactly one definition of the mark and one of the brand colours in this codebase. Never restate either. The wordmark has now drifted twice by being hand-copied into a second context: once into the footer, once into the admin hero: and this structure is what makes a third time impossible. A rule that styles a *placement* (the footer's `:not(.murtadd-logo)` exclusion, a size override) belongs with its consumer; a rule that defines the *mark* belongs in `logo.css`.

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

The operative test before publishing a paired entry: **if the companion article already makes this argument, do not make it again; compress it to a sentence, point across, and spend the entry on what only this site can say.** `the-age-of-aisha` is the worked example. The companion piece runs the source criticism and the presentism argument at length; this one compresses both to two paragraphs and spends itself on how the same report is used by both the polemicist and the child-marriage apologist, and on what Muslim-majority legislatures have since enacted, which is forensic and legal ground the companion site's pastoral register does not occupy.

**Counterpart links.** `_murtadd_ce_slug` on rebuttals and doubts stores a bare article slug on the companion site; `template-parts/counterpart.php` renders it as `{ce_base_url}/articles/{slug}/`. Both the slug and the base URL must be present or nothing renders. The two sites overlap on roughly two thirds of this site's entries by design: this one answers charges in a forensic register, the other works the same questions through pastorally.

**Contextual links** (`inc/contextual-links.php`). In the running text of a single entry, the first mention of a known phrase links to the page that treats it, on this site or on one of the six sister sites. The phrases are in `inc/data/network-links.json`, **a shared file kept byte-for-byte identical across all seven themes** (abrahamic, bestofislam, injil.help, paulus, compelling-evidence, murtadd, lailatulqadar). Never edit it here alone: change it in all seven or in none. Its `sites` table names each site's address; entries for `murtadd` resolve through `home_url()`, so staging links stay on staging, and every other entry resolves against its site's address. Each phrase belongs to exactly one page across the network.

Rules, all enforced in code and pinned in `tests/ContextualLinksTest.php`: a phrase is matched whole, capitals and apostrophe form aside; each address is linked once a page, at its first mention; never the page itself; at most one added link a paragraph or list item; at most four links to this site and three to sister sites a page, set separately on the tab. Nothing is linked inside a link, heading, quotation, figure, caption, citation, code, table, a `lang="ar"` (or Hebrew, Greek, Syriac) element, a verse or ayah box, the sources panel, or the fatwa disclaimer.

Three render paths: `the_content` at priority 20 (after `wpautop` and shortcodes, so paragraphs exist to count; the theme runs no other `the_content` filter), the doubt short response in `murtadd_the_short_response()`, and the fatwa position summary in `single-murtadd_fatwa.php`. The last two never pass through `the_content`, which is why they call `murtadd_ctx_render()` themselves. Only the queried entry is linked: never a feed, an excerpt, an archive, the front page, or another post rendered on the same request.

**Emotional and Identity doubts get no contextual links,** and no doubt in either category may be a target in the shared map (the test fails if one is). They are written care-first and left deliberately unlinked, the precedent set by the fear and grief doubts: a grief is not a claim awaiting refutation. The counterpart box and the homepage cross-link cards are separate and unchanged.

Extra phrases from the tab ("phrase | /path/" for this site, "phrase | https://…" for a sister site) are tried before the shared map, so they win; a full address on any host outside the `sites` table is dropped on save. Filters: `murtadd_contextual_links_map` (the entries), `murtadd_contextual_links_enabled` (per post).

**Glossary of terms** (`inc/glossary.php`, `inc/data/glossary.json`, `assets/css/glossary.css`, `assets/js/glossary.js`). The entries use Arabic and Malay terms (irtidad, nafaqah, 'ayb, tudung, hudud) without stopping to define them. The first mention of each on a page carries a short definition and its source, shown on hover, keyboard focus or tap. The definition says what the word means in the sense the entries use it, never what a ruling is, and its source is a work the seed already cites or the entry that explains the term ("(this site)"); a test fails if a term appears nowhere in the seed or a "(this site)" source names no entry. Rules, pinned in `tests/GlossaryTest.php`: one definition per term per page across every render path (the_content at 21, after the contextual links; the doubt short response; the fatwa summary); never inside a link, heading, quotation, verse, the sources panel, the disclaimer, a button or any region the contextual links skip; a mention the text glosses itself ("hudud (fixed prescribed penalties)") is left as written and a later one may carry the definition; **never on Emotional or Identity doubts**, for the reason they carry no contextual links. The markup is a `<button>` with `aria-describedby` and `aria-expanded` and a `role="tooltip"` span marked `hidden`, positioned over the text so nothing moves; hover and focus work through CSS alone, the script adds tap, Escape and keeping the box inside the viewport. The stylesheet and script load only on an entry whose content, short response or fatwa summary mentions a term. Switch: Content tab, Glossary of terms (on by default). Filters: `murtadd_glossary_terms`, `murtadd_glossary_enabled` (per post).

**Security** (`inc/hardening.php`, `inc/hide-login.php`). The site at this domain was compromised in August 2024, so the defaults close what WordPress leaves open: author enumeration (REST users for visitors; logged-in users keep them for the block editor, `?author=`, author archives, the users sitemap, account names in feeds and oEmbed), XML-RPC, and four response headers. Every switch reads through `murtadd_security( $key )`, whose defaults live in `murtadd_security_defaults()`. The private login address is a port of the WPS Hide Login request handling and is **off until an address is saved**; `define( 'MURTADD_HIDE_LOGIN', false );` in `wp-config.php` turns the module off whatever is saved, which is the documented way back in. It stands down while the WPS Hide Login plugin is active. Pinned in `tests/HardeningTest.php`. The login screen (`inc/login.php`, `assets/css/login.css`) is styled through WordPress's own login hooks and builds no login address, so it works at the private address; it loads `tokens → login` on `login_enqueue_scripts` only, every rule is scoped under `body.murtadd-login`, and the Colours tab's accents and the mark (custom logo, Site Icon, theme mark) arrive as custom properties through `wp_add_inline_style`. Pinned in `tests/LoginTest.php`.

**Legacy redirects** (`inc/legacy-redirects.php`) map the pre-2024 murtadd.org paths, recovered from sitemap caches in an old backup, onto current pages and topic archives. Descriptors resolve at request time; a missing target is skipped, never chained. Depends on the `irtidad` page and the `apostasy-law` topic term both existing. Filter: `murtadd_legacy_redirect_map`.

**Renamed slugs** (`inc/slug-renames.php`). Addresses carry no verse number, count or year (owner's rule, 2026-11-03); the number stays in the title. A renamed doubt or rebuttal is renamed in place on live sites, once per change to the map, and its new slug inherits the old one's seeded record. The old address answers with a 301 from the map, which never relies on `_wp_old_slug`. Entries stay in the map for good. Filter: `murtadd_slug_renames`.

**410 Gone** (`inc/gone.php`, `410.php`) answers the paths on the Upkeep tab with `410 Gone`. Two rules keep it from ever retiring a real page, and both are pinned in `tests/GoneTest.php`: a path pattern runs only on a 404, and at `template_redirect` priority 20, after the legacy redirects at 10, which exit when they redirect. The owner's list ships empty: no spam path from the 2024 compromise survives in the record. From 1.55.7 a pattern may carry `*` anywhere and a query string (`murtadd_gone_matches( $path, $patterns, $query )`); a query string may not start with a key WordPress or a tracking link uses (`murtadd_gone_reserved_keys()`). The spam the November 2026 logs show ships as presets behind the `gone_presets` switch (`murtadd_gone_presets()`): `/?items/*.sthml`, `/index.php/sitemap*` (left out under `/index.php/` permalinks), `/sitemapimages*`, `/contents/*`, `/goods.php` and, from 1.56.1, `/?cateitems/*`. WordPress served most of them its home page, so `murtadd_send_gone_spam()` answers them, and the owner's query-string patterns, at priority 1 on a 404 and on the home page alike, before the legacy redirects and the canonical redirect. The home page answers 410 only for an address that matches one of these patterns (`/?items/1.sthml`, never `/` or `/?s=x`); a request WordPress resolved to a post, page or archive is left alone. Pinned in `tests/LiveLogsTest.php`.

**Files at the top of the site** (`inc/security.php`). On Apache the `.htaccess` sync keeps two blocks beside "Block xmlrpc.php", whatever the XML-RPC switch, and removes them on a theme switch. "Murtadd: only WordPress root PHP" (`murtadd_sec_root_lines()`) answers 403 for any root PHP file other than `index.php`, `wp-login.php`, `wp-cron.php`, `wp-comments-post.php`, `wp-signup.php`, `wp-activate.php`, `wp-trackback.php`, `wp-links-opml.php`, `wp-mail.php`, `wp-load.php`, `wp-blog-header.php` and `xmlrpc.php` (`murtadd_sec_root_php_names()`, filter `murtadd_root_php_allow`), in the WordPress folder too when WordPress sits in its own subfolder; `MURTADD_ALLOW_ROOT_PHP` takes it out. "Murtadd: deny direct core files" (`murtadd_sec_core_lines()`) answers 403 for the PHP files in `wp-includes` (TinyMCE and `ms-files.php` excepted), `wp-settings.php`, `wp-config-sample.php`, `readme.html` and `wp-admin/install.php`; `MURTADD_ALLOW_CORE_FILES` takes it out. The Security tab lists the root PHP files the first block refuses (`murtadd_sec_unknown_root_php()`).

**Development files** (`inc/dev-files.php`, `.htaccess`). The theme `.htaccess` refuses every dot entry (`.well-known` excepted), the `tests`, `tools`, `bin`, `docs`, `config` and `vendor` folders, Markdown, `composer.*` and `phpunit.xml*`; each of those folders carries its own deny file. `murtadd_cleanup_on_version_change()` on `init` removes the dot entries, `tests`, `config`, Composer's and PHPUnit's files and everything in `vendor/` but Parsedown, once per version, under a lease (`add_option()`, renewed once a minute, taken over after five minutes by one compare-and-set UPDATE). The version is recorded only once nothing is left. A `local` or `development` site and `MURTADD_KEEP_DOTFILES` keep everything. `bin/` and `docs/` ship and stay.

**A theme caught half uploaded** (`functions.php`). `murtadd_theme_files()` lists every file in `inc/` in load order. Each is checked before any loads, and each loads inside a `try`, so a missing or half-written file gives `murtadd_incomplete_theme()`: the 503 page with `Retry-After`, and one log line per problem every ten minutes. A missing file leaves the admin alone; the login screen, AJAX, cron and the command line never get the page (`murtadd_incomplete_serves()`).

**Maintenance and error pages** (`inc/maintenance.php`, `dropins/`, `assets/css/error-page.css`). The three `dropins/*.php` files are the sources of WordPress drop-ins that `murtadd_maybe_install_dropins()` copies into `wp-content`, filling `{{SITE_NAME}}`, `{{HOME_URL}}`, `{{TOKENS_URL}}` and `{{CSS_URL}}`. Rules, pinned in `tests/MaintenanceTest.php`: each file runs on its own with nothing from WordPress; every file the theme writes carries the marker `Murtadd error page drop-in`, and nothing without it is overwritten or removed; writes happen only in wp-admin for an administrator, by temporary file and rename, and on a network from the main site, or, while the main site runs another theme, from the one subsite with the lowest ID that runs this one (`murtadd_dropins_writer()`), whose Error pages switch then rules the network. The last site to leave the theme removes the files. `murtadd_dropin_files()` also lists a copy of each static page in `errors/`, written to `wp-content/murtadd-errors/` with the site's home address and theme folder, so the `ErrorDocument` lines work on a site installed in a subfolder. The pages link the theme's `tokens.min.css` for the brand colours and fonts, and `error-page.css` falls back to the same values when that file cannot load, which is the one place a token value is restated, deliberately: the pages appear exactly when the theme may be half uploaded. Light only, per the Exclusions. Maintenance mode serves the same template from the theme at `template_redirect` priority 0.

The **Docs tab** is the exception to the Settings API pattern: it registers no setting group and renders `/docs/*.md` through `inc/docs.php` (Parsedown 1.8.0, vendored at `vendor/parsedown/`, safe mode + `wp_kses()`). Adding or renaming a document means editing `murtadd_docs_manifest()`; nothing else knows the filenames. The docs directory and `vendor/` must both ship in the build, or the tab renders an explanatory notice instead of the manual.

Appearance → Theme Options is the Settings API under the owner's admin design system of 2026-11-04 (`assets/css/admin.css`, `assets/js/settings-tabs.js`, both with `.min` twins), not a custom framework. Cards and field rows replace `form-table`: label left and control right, stacked below 782px. Booleans are toggle switches (a real checkbox with `role="switch"`, posting "1" as before), the contextual-link caps are range sliders with a live `<output>`, the fatwa sort is a set of radio selector cards, and the two accents use core's `wp-color-picker` in a grid. The server renders the active panel only, so only its queries run; the other panels are empty shells that `settings-tabs.js` fills once over admin-ajax (`murtadd_settings_panel`, nonce and `manage_options`) the first time their tab is chosen, and fires `murtadd:panel` so `security.js` can bind its tools. The tabs are an ARIA tab list (arrow keys, Home, End) that switch panels without a reload and keep `?tab=` (and a Docs `?doc=`) in the address; without the script each tab is a plain link that renders its panel alone. Each settings tab keeps its own form to `options.php` with its own group; `murtadd_settings_form_fields()` prints the fields `settings_fields()` would, without ids and with the tab's own address as the referer, so a save returns to its tab. The screen calls `settings_errors()` itself, since core does so only on Settings screens. The assets load only on the hook suffix `add_theme_page()` returned (`murtadd_settings_hook()`), and the Security tab's script loads there too. The page keeps `manage_options`: the Security tab writes `.htaccess` and moves the login screen. Everything is scoped under `.murtadd-admin` so nothing leaks into the rest of wp-admin. No admin markup carries a `style` attribute (`tests/AssetsTest.php`): states use `murtadd-status-missing` and `murtadd-status-ok`, and the dashboard's content-gaps widget, the one rule set outside `.murtadd-admin`, is scoped under `#dashboard-widgets` (`murtadd-gaps-*`).

The gradient appears in exactly three places: the hero, the active tab's indicator, and the primary button. Do not spread it further. Restraint governs the admin as it governs the front end.

The version badge reads `MURTADD_VERSION` directly. It cannot drift, unlike the six files that state a version in text.

Tabs run in the order the work is done: Setup, Content, Topics & schools, Linked sites, Contextual links, Colours, Profiles, Security, Upkeep, Docs. **The tab keys are load-bearing**: `settings_fields( 'murtadd_' . $active )` maps each key to its registered settings group, so relabel and reorder freely, but never rename a key: a renamed key silently detaches the tab from its settings and the fields will appear to save while going nowhere. Labels must name what a person has, not what WordPress calls it internally.

## Unlisting, search addresses and the progress bar

Three features rebuilt from plugins in 1.54.0. Each stands down while its plugin is active and imports what the plugin saved: the unlisted list (`unlist_posts`), the search base (`wpseosearch_base`) and the bar's options (`rp_settings`).

- **Unlisting** (`inc/unlist.php`, `assets/js/unlist-admin.js`). Filters at `pre_get_posts`, because `get_posts()` suppresses the `posts_where` filter the plugin used and the related doubts and the rail are built with it. A query naming its posts keeps only the listed ones. Admin screens and editors' REST and admin AJAX requests see everything. An editor's chosen cross-link (`murtadd_related_link()`) still names an unlisted entry, as any direct link may. Pinned in `tests/UnlistTest.php`.
- **Search addresses** (`inc/search-permalinks.php`). `murtadd_search` is registered in the Content tab's group and is also saved from Settings, Permalinks. The base is applied at `init` 1; a changed base clears `murtadd_rewrite_version`, so the existing flusher rebuilds once. Pinned in `tests/SearchPermalinksTest.php`.
- **Reading progress bar** (`inc/reading-progress.php`, `assets/css/progress.css`, `assets/js/progress.js`). It moved out of `longform.js`, which no longer draws one. **Never on doubts**, for the reason long-form furniture is not on doubts. Pinned in `tests/ReadingProgressTest.php`.

## Owner decisions (record)

- 2026-07-13: no dark mode and no testimonials (see Exclusions).
- 2026-10-26: serve the Qur'an face as WOFF2 (see Arabic and the Qur'an face).
- 2026-11-03: keep every schema.org type, and give addresses no verse number, count or year.
- 2026-11-03: the site has a Journal. Guides stay as they are, a post type of their own at fixed addresses, and the Journal at `/journal/` holds dated standard posts beside them (see Journal).
- 2026-11-05: every theme is licensed under the GNU General Public License, version 3 or later. This came in the owner's brief for the session and is not yet in `.claude/CLAUDE.md`; when the owner adds it there, this line is its copy.

## Exclusions (binding)

**No dark mode.** Do not add a `prefers-color-scheme: dark` variant, a toggle, or dark-mode tokens. The ivory ground and green panel are the design in full.

**No testimonials.** Do not add a testimonials section, post type, or archive: no wall of personal accounts from people who doubted, left, or stayed. The site answers questions; it does not collect people.

Both are owner decisions, recorded 2026-07-13. Do not re-propose either in reviews or enhancement lists. The 1.41.0 pages (login, error, maintenance, 410) follow the first: light only.

## Design tokens

Defined once in `css/main.css` `:root`. Accent `#0f6e56` (green), tint `#eefaf7`, secondary `#5bc9bc`, ground `#fcf8ee` (warm ivory), serif Source Serif 4, sans Public Sans. Accent and tint are user-configurable from the Colours tab; everything else derives from the stylesheet.

## Branding assets

`screenshot.png` (1200×900) is the Appearance → Themes preview. `assets/icons/` holds the site-icon mark: `site-icon.svg` is the source, with 512/192/180/32/16 PNGs and `favicon.ico` derived from it; `apple-touch-icon.png` and `site-icon-maskable-512.png` are composited from the 512 PNG onto solid #0f6e56, and `site.webmanifest` lists the 192, 512 and maskable files. `inc/site-icon.php` serves them, and answers `/favicon.ico`, only while no core Site Icon is configured. Once one is set, `has_site_icon()` stops every icon tag and the theme's `/favicon.ico` answer (which becomes a redirect to the Site Icon, HEAD included); only `theme-color`, from the Colours accent, stays. The owner asked for the complete set on 2026-11-05: "make sure you have favicon too." Fonts are self-hosted in `assets/fonts/`: Source Serif 4 and Public Sans under the OFL, and the Qur'an face below under its own licence.

## Arabic and the Qur'an face

**Qur'anic verses in Arabic, and nothing else, are set in KFGQPC HAFS Uthmanic Script** (King Fahd Glorious Quran Printing Complex), shared with the six sister themes. Token `--murtadd-font-quran`, used only by `.murtadd-ayah[data-quran-encoding="kfgqpc"]:lang(ar)` (`main.css`) and `.murtadd-verse p[data-quran-encoding="kfgqpc"]:lang(ar)` (`rail.css`, when the owner sets the rail verse in Arabic): the face goes only on verse text `inc/quran-text.php` has fitted to its encoding, so a verse rendered where the mapper does not run keeps the Arabic stack rather than showing round sukun and stray ornaments. Every other Arabic run, a hadith or a term in a sentence, takes `--murtadd-font-arabic`, a system Arabic stack, through `:lang(ar)`. `tests/QuranTextTest.php` fails if the Qur'an token appears on any other selector or the face is named outside `tokens.css`. Verses are set at line-height 2.2, right to left, with no letter-spacing. Not preloaded: the file is fetched only on a page that shows a verse.

**The licence decision.** The owner chose on 2026-10-26 to serve a WOFF2 conversion of the font (`assets/fonts/kfgqpc-hafs-uthmanic-script.woff2`, 107 KB, from the 298 KB TrueType file), accepting that the KFGQPC licence forbids modifying the font. The conversion is lossless for glyphs, character map and OpenType layout; the digital signature (DSIG) is dropped. The licence text ships beside it.

**Verse encoding** (`inc/quran-text.php`). The face follows the Complex's encoding (Madani sukun U+06E1, rounded zero U+0652) and draws U+06DF as a spacing ornament and Arabic-Indic digits as the ayah medallion. Verse text in the common Tanzil encoding (sukun U+0652, rounded zero U+06DF) is mapped as it renders, and ornate brackets round an ayah number are dropped; the stored text is never changed. Text already holding U+06E1 is left alone, and a mapped element is marked `data-quran-encoding="kfgqpc"` so no later pass maps it twice. Runs on `the_content` at 6, inside the doubt short response and the fatwa position summary, on the homepage ayah, and on an Arabic rail verse. Write a verse as `<p class="murtadd-ayah" lang="ar" dir="rtl">`, with bare digits for the ayah number and no U+06DD.

## Contact form

`inc/contact.php` owns the `[murtadd_contact]` shortcode, on the seeded Contact page. The owner publishes no address, so the theme publishes none: messages go by `wp_mail()` to the Content tab's recipient or the administration email, and are never stored. A nonce, a honeypot and a per-connection hourly limit protect it; the limit is a transient keyed by an HMAC of the IP address (`wp_salt( 'nonce' )`), and the address itself is written nowhere. Handled on `template_redirect` (post, redirect, notice), so it works without JavaScript; `assets/js/contact.js` only prevents a double send. Styles in `assets/css/contact.css`, loaded only where the shortcode is. Light only. Pinned in `tests/ContactTest.php`. The Privacy page describes the form; change one and the other must follow.

## Data removal

Themes have no uninstall hook. The `murtadd_*` options and the two user meta keys listed in readme.txt must be deleted manually if the theme is retired. Content persists by design.

## Public wording

`readme.txt` is served by the web server, and the pages are public. Neither says that the site is related to other sites or belongs to a group of them. Links to other sites are ordinary links, and the readme calls them links to other sites.

The Theme Options screen, which only administrators see, still uses an older label for them.

On Apache the theme's `.htaccess` refuses the `docs/` files, and the Docs tab reads them from disk. Nginx reads no `.htaccess`, so there they stay reachable until the host adds the Nginx rules in `docs/readme.md`, under Security.

## Release checklist

1. The six files that state the version agree (see Documentation, above).
2. `docs/changelog.md` has the new entry, and `readme.txt` has the short form and, where the owner must act, an Upgrade Notice.
3. `docs/upgrading.md` has a note for the version, and its roadmap drops what shipped.
4. `docs/readme.md` covers any new hook, filter, constant, setting, file or server rule.
5. This file covers any new fixed fact or owner decision.
6. Every path named in a document exists.
7. `composer lint`, `composer test` and `php bin/build-error-pages.php --check` pass, and every edited source asset has its `.min` twin regenerated.

