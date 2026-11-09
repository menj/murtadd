# Murtadd

A classic-template child theme of Twenty Twenty-Five for **murtadd.org**.

| Field | Value |
|---|---|
| Theme slug | `murtadd` |
| Text domain | `murtadd` |
| Parent | `twentytwentyfive` |
| Version | 1.58.5 |
| Author | Langgam Fikir Enterprise |
| Licence | GNU General Public License v3 or later; the full text is in `LICENSE` |
| Requires WP | 6.4+ |
| Requires PHP | 8.1+ |
| Web server | Apache gets its rules written by the theme; Nginx needs the rules under Security below |
| Multisite | Works, with one limit: the `.htaccess` blocks are shared by the network, and any site's theme switch takes them out while other sites may still run Murtadd (see the roadmap). The error pages stay |

## What it is

Three custom post types answer three different needs.

**Doubts** give a short, sourced answer to a question a reader is actually carrying. **Rebuttals** state a circulated claim fairly and answer it at length with citations. **Fatwa** entries summarise scholarly positions in the site's own words, each linked to its primary source.

A shared **Topic** taxonomy spans all three. A locked four-term **Doubt Category** taxonomy (intellectual, scriptural, emotional, identity) drives the homepage triage grid. A **School** taxonomy filters the fatwa index.

The theme ships its own content: 38 Doubts and 47 Rebuttals, published on activation with their claims and sources, their topics and categories, and their cross-links already wired.

Long-form **Guides** (`/guides/`, 12 of them) are a post type of their own. The **Journal** (`/journal/`) holds dated standard posts (8 ship), seeded from `inc/data/seed/journal.json` by `inc/seed-journal.php` with fixed dates: a past date is published and a future one is scheduled.

## The home page as a story

`front-page.php` reads as one path for a reader who arrives carrying a doubt. Each section answers the question the one before it leaves open.

| Step | Section | Its job |
| --- | --- | --- |
| 1 | Hero, "Doubts, taken seriously." | The claim: every doubt is stated fairly and answered from sources. |
| 2 | "Where does your doubt start?" (the four-door doubt category grid) | The reader's question. A one-line lede says what each door opens on. |
| 3 | "Not sure where to start?" (the starting-point card, `template-parts/cards/card-quiz.php`) | Help for a reader who cannot name the doubt yet. |
| 4 | "When the doubt runs deeper" (the two cross-link cards) | The older questions some doubts rest on, at length elsewhere. |
| 5 | Footer columns (Browse, Topics, Site) | The next step: the Journal, the doubts, the rebuttals and the site pages. |

The order already ran this way; 1.55.6 rewrote the headings and lines to read as steps. Sections 3 and 4 hide as their settings say (Linked sites tab). Keep a new home-page section inside this path, and write its heading as the step it takes.

## Directory structure

```
murtadd/
├── assets/
│   ├── css/           Stylesheets, each with a .min counterpart
│   ├── fonts/         Source Serif 4 and Public Sans (OFL), self-hosted
│   ├── icons/         Site-icon mark: SVG source, PNG sizes, favicon.ico, touch and maskable icons, site.webmanifest
│   └── js/            Scripts, each with a .min counterpart
├── bin/               build-error-pages.php (ships; exits outside the command line)
├── config/            Lighthouse settings (development only)
├── docs/              All documentation, including this file
├── dropins/           Sources of the wp-content error pages
├── errors/            Static 403, 500, 502, 503 and 504 pages for a site at the root
├── inc/               Feature modules (CPTs, taxonomies, meta, settings, schema, seeding)
├── languages/         murtadd.pot
├── tests/             PHPUnit suite (development only)
├── vendor/            Parsedown for the Docs tab; Composer's development packages in a working copy
├── template-parts/
│   ├── cards/         card-cross-link, card-doubt-category, card-quiz
│   ├── notices/       notice-struggling
│   ├── rail/          rail-single
│   ├── rows/          row-fatwa-entry, row-list-entry
│   └── counterpart.php
├── templates/         Page templates (page-start-here.php)
├── theme.json         Editor palette, typography, layout (v3)
├── style.css          Theme header only; never enqueued
├── readme.txt         WordPress-format readme (must stay at the root)
├── LICENSE            The GNU General Public License, version 3, in full
├── license.txt        The licence notice and the bundled fonts' attributions
└── *.php              Classic template hierarchy
```

Only files WordPress requires at the theme root stay there: `style.css`, `theme.json`, `functions.php`, `screenshot.png`, `readme.txt`, and the template hierarchy. Everything else is filed.

## Architecture

**A classic theme.** The child ships `index.php` and no `templates/index.html`, so WordPress treats it as a classic theme: the parent's block templates and the Site Editor are bypassed, while the parent still satisfies the `Template:` header and supplies block-library CSS for editor-authored content. `theme.json` is present so the editor renders content the way the front end does.

**Assets are separated.** All CSS under `assets/css`, all JS under `assets/js`. No template contains a `<style>` or `<script>` block.

Colour overrides inject through `wp_add_inline_style()` only. Every source file ships with a `.min` counterpart, and `MURTADD_ASSET_SUFFIX` serves sources under `SCRIPT_DEBUG` and minified files otherwise. **Editing a source without regenerating its `.min` pair changes nothing on a live site.**

**Loading is conditional.** Per-template CSS enqueues only on its own views. Admin JS enqueues only on the theme's own screens.

**JavaScript is an enhancement.** Every page reads, and its menus and forms work, with JS disabled. The fatwa filters are a plain GET form; the script only marks the form as changed (it never submits on change, WCAG 3.2.2). The reading progress bar and the glossary's tap and Escape handling are the parts that need it.

**Settings use the Settings API.** Settings are grouped by tab, each tab saving its own options, in a tabbed screen at Appearance → Theme Options. The tabs run Setup, Content, Topics & schools, Linked sites, Contextual links, Colours (with a live WCAG contrast check), Profiles, Security, Upkeep and Docs. `inc/settings-page.php` registers them; [settings.md](settings.md) describes every field.

## Boot sequence

1. `functions.php` defines `MURTADD_VERSION` and `MURTADD_ASSET_SUFFIX`.
2. `murtadd_theme_files()` lists the files in `inc/` that `functions.php` loads, in order. Each, and `inc/seed-data.php`, is checked before any loads, and the listed files load inside a `try`. `inc/seed-data.php` comes in through `inc/seed-content.php`, whose `require_once` runs inside that same `try`, so a half-written copy of it is caught too. A missing or half-written file gives `murtadd_incomplete_theme()`, the upload guard below.
3. `init` runs `murtadd_cleanup_on_version_change()` once per version, which removes development files from a live site (below).
4. Activation (`after_switch_theme`) only raises the `murtadd_needs_setup` flag. `murtadd_activate()` then runs on `init` at priority 90, after the post types and taxonomies exist: it scaffolds the pages and the footer menu, sets the Reading options and rewrite rules, and inserts the starter content (`inc/activation.php`). It never overwrites and never deletes.
5. `admin_init` registers the settings, writes or repairs the error pages for an administrator, and keeps the `.htaccess` blocks in step at most once an hour.
6. Front-end views are the PHP templates at the theme root, and the Start Here page template in `templates/`. Per-template CSS loads only on its own views.

## Build, test and release

```bash
composer install          # PHPUnit and its packages, development only
composer lint             # php -l over the root templates, inc/, template-parts/, dropins/, bin/ and tests/ (not templates/)
php -l templates/page-start-here.php
composer test             # vendor/bin/phpunit; runs without WordPress
php tests/security-detector-test.php
php bin/build-error-pages.php          # rebuild errors/ after a text change
php bin/build-error-pages.php --check  # fails when a page is stale
```

GitHub Actions runs `composer lint` and `composer test`. A release zip comes from `git archive`, which leaves out `.github`, `.claude`, `tests`, `config`, Composer's and PHPUnit's files and the git files (`.gitattributes`). `vendor/parsedown` ships, since the Docs tab loads it.

```bash
git archive --format=zip --prefix=murtadd/ -o murtadd-$(sed -n 's/^Version:[[:space:]]*//p' style.css).zip HEAD
```

Six files state the version: `style.css`, `functions.php`, `readme.txt`, this file, `ssot.md` and the top entry of `changelog.md`. Bump all six, and every edited source asset's `.min` twin with it.

## The Journal post page

`single.php` shows a Journal post with the pieces in `inc/article.php`, in reading order: the breadcrumb, the meta row (`murtadd_article_meta_row()`: the date, "Last reviewed" from `murtadd_the_reviewed_date()` once the post is edited more than a day later, and the reading time), the title, the dek (the excerpt), "In short" (`murtadd_article_answer_box()`), the contents (`murtadd_article_contents()`), the text, the sources panel, the next step and related posts. Guides share `single.php` and keep their own page.

- The contents is built on the server from the level-two headings, on a post with three or more. `murtadd_article_filter_content()` (`the_content`, priority 12, after the contextual links) gives each heading the id the list links to, keeps an id the author set, and makes each table a labelled scroll region. `longform.js`, now loaded on Journal posts too, marks the section being read and adds the back-to-top button.
- "In short" shows the post's key points, one a line, from the meta key `_murtadd_takeaways`. The sources are `_murtadd_sources`, the rebuttal repeater, saved by `murtadd_save_meta()`. Both are in the Journal fields box on the post screen.
- The next step offers the next post in the Journal (the one before it on the newest post), "Check the sources" when the post has any, and "Ask a question" when the Contact page is published. The one-line version before the third heading is off by default.
- Related posts are three cards, chosen from every post that shares a topic, a tag or a category, and the three newest, and scored by `murtadd_article_rank()`: 3 for each shared topic, 2 for each shared tag, 1 for each shared category (the default category is left out), newest first on a tie. The post in the next step is never repeated.
- The FAQ is a Details block in the FAQ style (`is-style-faq`), with the pattern `murtadd/faq`. Only those blocks feed the FAQPage, so it appears only on a post that shows questions.
- Styles are `assets/css/article.css`, loaded on Journal posts after `journal.css`. No byline shows: the author is in the structured data only.

## Settings and options

Appearance → Theme Options, capability `manage_options`. Each tab keeps its own option, registered in `inc/settings-page.php`:

| Tab | Option |
|---|---|
| Setup | none; reports what exists and creates what is missing |
| Content | `murtadd_content_display` (with the Journal posts switches `journal_answer_box`, `journal_contents`, `journal_mid_next` and `journal_related`), `murtadd_search` |
| Topics & schools | none; links to the term screens |
| Linked sites | `murtadd_cross_links` |
| Contextual links | `murtadd_contextual_links` |
| Colours | `murtadd_colours` |
| Profiles | `murtadd_profiles`, `murtadd_author_name` |
| Security | `murtadd_security` (`inc/security.php`, `inc/hardening.php`, `inc/hide-login.php`) |
| Upkeep | `murtadd_upkeep` (`inc/maintenance.php`, `inc/gone.php`) |
| Docs | none; renders `docs/*.md` through `inc/docs.php` |

The tab keys are load-bearing: never rename one. [settings.md](settings.md) has every field.

## Hooks, filters and constants

| Filter | Effect |
|---|---|
| `murtadd_contextual_links_map`, `murtadd_contextual_links_enabled` | The phrase map, and the links on a post |
| `murtadd_glossary_terms`, `murtadd_glossary_enabled` | The glossary, and its tooltips on a post |
| `murtadd_legacy_redirect_map` | The pre-2024 addresses and their targets |
| `murtadd_slug_renames` | Renamed slugs and their 301s |
| `murtadd_gone_match` | Whether a path answers 410 Gone |
| `murtadd_root_php_allow` | Root PHP files the web server may run |
| `murtadd_sec_rest_allow`, `murtadd_sec_types` | REST routes exempt from the write lock, and the post types the spam gate watches |
| `murtadd_security_headers` | The response headers the Security tab sends |
| `murtadd_short_titles` | Short page parts for long title tags |
| `murtadd_share_image`, `murtadd_contact_limits` | The share image and the contact form's limits |
| `murtadd_unlist_enabled`, `unlist_posts_enabled` | Unlisting; the second is the Unlist Posts & Pages plugin's own name, honoured too |

| Constant | Set in | Effect |
|---|---|---|
| `MURTADD_VERSION`, `MURTADD_ASSET_SUFFIX` | `functions.php` | Version; `.min` unless `SCRIPT_DEBUG` |
| `MURTADD_HIDE_LOGIN` | `wp-config.php` | False turns the private login address off, whatever is saved |
| `MURTADD_SECURITY_LOCK` | `wp-config.php` | False turns off the remote-publishing locks |
| `MURTADD_SPAM_THRESHOLD` | `wp-config.php` | The score at which the spam gate holds a post |
| `MURTADD_KEEP_DOTFILES` | `wp-config.php` | True keeps the development files on a live site |
| `MURTADD_ALLOW_ROOT_PHP` | `wp-config.php` | True leaves out "Murtadd: only WordPress root PHP" |
| `MURTADD_ALLOW_CORE_FILES` | `wp-config.php` | True leaves out "Murtadd: deny direct core files" |

## Security

### The upload guard

While one of the files `murtadd_theme_files()` lists, or `inc/seed-data.php`, is missing or half written, visitors get the 503 "Back shortly" page with `Retry-After: 300`, and the error log one line per problem every ten minutes. The login screen, AJAX, cron and the command line never get it (`murtadd_incomplete_serves()`), and a missing file leaves the admin alone.

### Accounts and XML-RPC

Visitors get no REST user routes, and author archives and `?author=` answer with a 301 to the home page. Account names leave the feeds, oEmbed and the users sitemap. XML-RPC is off, with its pingback header and RSD link.

Every switch is on the Security tab and reads through `murtadd_security( $key )`.

### The private login address

The private login address is a port of WPS Hide Login. It is off until an address is saved, and stands down while that plugin is active. `define( 'MURTADD_HIDE_LOGIN', false );` is the way back in.

### The theme folder's rules

`.htaccess` in the theme folder refuses every dot entry (`.well-known` open), the `tests`, `tools`, `bin`, `docs`, `config` and `vendor` folders, Markdown, and `composer.json`, `composer.lock`, `phpunit.xml` and `phpunit.xml.dist`. `tests/`, `bin/`, `docs/`, `config/` and `vendor/` each carry a deny file of its own; `tools/` has none, since the theme has no such folder, and is covered by the rule alone. The Docs tab reads `docs/*.md` from disk, never by address.

### Development files on a live site

On the first request of any kind after the version changes, `murtadd_cleanup_on_version_change()` removes the dot entries, `tests`, `config`, Composer's and PHPUnit's files and everything in `vendor/` but Parsedown, under a lease so only one request does it. A `local` or `development` site and `MURTADD_KEEP_DOTFILES` keep everything. `bin/` and `docs/` ship and stay.

### The blocks in the site's .htaccess

On Apache with a writable `.htaccess`, `murtadd_sec_sync_htaccess()` keeps three marked blocks, at most once an hour, and at once when a Security setting is changed and saved (WordPress fires no update hook for a save that changes nothing), and `murtadd_sec_remove_htaccess()` takes them out on a theme switch. For WordPress at the root of the `.htaccess` folder they read:

```apache
# BEGIN Block xmlrpc.php
<Files xmlrpc.php>
<IfModule mod_authz_core.c>
Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
Order Deny,Allow
Deny from all
</IfModule>
</Files>
# END Block xmlrpc.php
# BEGIN Murtadd: deny direct core files
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$ - [F,L]
RewriteRule ^(wp-settings|wp-config-sample)\.php$ - [F,L]
RewriteRule ^readme\.html$ - [F,L]
RewriteRule ^wp-admin/install\.php$ - [F,L]
</IfModule>
# END Murtadd: deny direct core files
# BEGIN Murtadd: only WordPress root PHP
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^(?!(?:index|wp\-activate|wp\-blog\-header|wp\-comments\-post|wp\-cron|wp\-links\-opml|wp\-load|wp\-login|wp\-mail|wp\-signup|wp\-trackback|xmlrpc)\.php$)[^/]+\.php$ - [F,L,NC]
</IfModule>
# END Murtadd: only WordPress root PHP
```

"Block xmlrpc.php" is there while XML-RPC is off; the marker name is a common one, so a switch between themes that use it never writes it twice.

With WordPress in its own subfolder, the core rules carry that folder in front, and the root rule is written for both folders. With WordPress outside the `.htaccess` folder neither Murtadd block (core files, root PHP) is written; "Block xmlrpc.php" still is while XML-RPC is off. The Security tab lists the root PHP files the root block refuses, so leftovers can be deleted by FTP.

### Nginx

Nginx reads no `.htaccess`. Put these in the server block above every other regex `location`, the `location ~ \.php$` block that hands PHP to PHP-FPM and any static-file block among them: Nginx takes the first regex location that matches, so a rule further down never runs.

Where the host keeps such blocks in an include, merge the denials into them instead. Check for a matching `location ^~` or exact `location =` block too (for `/wp-content/` or the themes folder, say): Nginx skips every regex location after one of those matches, so put the denials inside it.

```nginx
location ~ /\.(git|claude) { return 403; }
location ~ ^/wp-content/themes/murtadd[^/]*/(.+/)?\.(?!well-known(/|$)) { return 403; }
location ~ ^/wp-content/themes/murtadd[^/]*/(tests|tools|bin|docs|config|vendor)(/|$) { return 403; }
location ~* ^/wp-content/themes/murtadd[^/]*/.+\.md$ { return 403; }
location ~ ^/wp-content/themes/murtadd[^/]*/(composer\.(json|lock)|phpunit\.xml(\.dist)?)$ { return 403; }
location ~ ^/wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$ { return 403; }
location ~ ^/(wp-settings\.php|wp-config-sample\.php|readme\.html|wp-admin/install\.php)$ { return 403; }
location ~* ^/(?!(index|wp-activate|wp-blog-header|wp-comments-post|wp-cron|wp-links-opml|wp-load|wp-login|wp-mail|wp-signup|wp-trackback|xmlrpc)\.php$)[^/]+\.php$ { return 403; }
location = /xmlrpc.php { return 403; }
```

The first line goes further than the Apache rules, which refuse dot entries only inside the theme folder: it refuses `.git` and `.claude` anywhere on the site. The second matches the theme folder's own `.htaccess`, `.well-known` left open.

The `murtadd[^/]*` in the theme rules also covers the `murtadd-new` and `murtadd-old` folders of an FTP update, which Nginx would otherwise serve while both sit on the server.

Leave out the last line while XML-RPC is on. For a site in a subfolder, put its path in front of every rule but the first (the `.git` and `.claude` rule stays global), as in `^/blog/wp-includes/`, and the theme-folder rules with it (`^/blog/wp-content/themes/murtadd/`).

With WordPress in its own folder below the site (`/wp/`), the Apache blocks carry that folder, so add the same rules with it in front: `location ~ ^/wp/wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$ { return 403; }` and `location ~ ^/wp/(wp-settings\.php|wp-config-sample\.php|readme\.html|wp-admin/install\.php)$ { return 403; }`. While XML-RPC is off, add `location = /wp/xmlrpc.php { return 403; }` as well.

With `wp-content` under that folder as well, the theme-folder rules take the prefix too (`^/wp/wp-content/themes/murtadd[^/]*/`). Repeat the root-PHP rule for that folder too: `location ~* ^/wp/(?!(index|wp-activate|wp-blog-header|wp-comments-post|wp-cron|wp-links-opml|wp-load|wp-login|wp-mail|wp-signup|wp-trackback|xmlrpc)\.php$)[^/]+\.php$ { return 403; }`.

### The installer

The theme cannot protect a site whose WordPress core sits on the server without `wp-config.php`. Upload `wp-config.php` first, or upload core to a folder the web server does not serve and rename it into place.

## Error pages

The Upkeep tab writes `wp-content/maintenance.php` (503, `Retry-After: 600`), `php-error.php` (500, or 503 with `Retry-After: 120` for an upload in progress) and `db-error.php` (503, `Retry-After: 300`) from `dropins/`, with `murtadd-error-page.css`, and never over a file without the marker `Murtadd error page drop-in`, unless another theme marked it "Written by the ... theme; safe to delete". Its table reads each file as written, outdated, missing or kept.

Static pages for the errors the web server answers before WordPress runs are in `errors/` (`php bin/build-error-pages.php`). They assume a site at the domain root, with the theme in `/wp-content/themes/murtadd/`.

Each page links the 32px icon by the same path rule as its stylesheets: `{{ICON_URL}}` in the drop-ins, and the theme prefix rewritten in the copies.

The Upkeep tab also writes a copy of each into `wp-content/murtadd-errors/`, with the site's own home address and theme folder, under the same rules as the drop-ins.

A site installed in a subfolder, such as `example.com/blog/`, gets links that work. Switching theme, or the Error pages switch off, removes the copies.

The Upkeep tab shows the `.htaccess` lines with this site's addresses. For a site at the root they read:

```
ErrorDocument 403 /wp-content/murtadd-errors/403.html
ErrorDocument 500 /wp-content/murtadd-errors/500.html
ErrorDocument 502 /wp-content/murtadd-errors/502.html
ErrorDocument 503 /wp-content/murtadd-errors/503.html
ErrorDocument 504 /wp-content/murtadd-errors/504.html
```

On Nginx the same pages go in with `error_page 403 /wp-content/murtadd-errors/403.html;` and so on for each code. These cover the errors Nginx answers itself, such as a 502 or 504 from a stalled PHP-FPM. A 500 from PHP passes through unless `fastcgi_intercept_errors on;` is set in the PHP location, and it is better left off: the theme's `php-error.php` drop-in already answers those in the site's design.

Where `wp-content` sits below a subfolder, use its real address, as in `/blog/wp-content/murtadd-errors/403.html`; the tab's lines show it.

On a network every site shares `wp-content`. The main site writes the files; while it runs another theme, the subsite with the lowest ID that runs Murtadd writes them (`murtadd_dropins_writer()`). The last site to leave the theme removes them.

## Browser icons

`inc/site-icon.php` prints `site-icon-32.png`, `site-icon-192.png`, `site-icon.svg`, `apple-touch-icon.png` and `site.webmanifest` on `wp_head`, `admin_head` and `login_head` while `has_site_icon()` is false, and `theme-color` (the Colours accent) on `wp_head` and `login_head`. With a core Site Icon, core prints its own tags.

`/favicon.ico` reaches WordPress when no such file sits in the site root. `murtadd_serve_favicon()` answers it on `template_redirect` (priority -1, ahead of maintenance mode, so HEAD and a site in maintenance are covered; with a Site Icon it redirects there for every method) and `do_faviconico` with `assets/icons/favicon.ico`, `Cache-Control: public, max-age=86400` and an ETag, with a 304 on a match. The manifest is a static file with relative icon paths. Its `theme_color` stays at the default accent, #0f6e56, while the page's `theme-color` follows the Colours setting; a site with another accent can edit the file, and a later release may build it from the setting. The theme `.htaccess` refuses dot entries, Markdown, Composer and PHPUnit files and the development folders only, so the icons are served. On a site in a subfolder, such as `example.com/blog/`, browsers still ask for `example.com/favicon.ico`, which WordPress never sees: put a copy of `assets/icons/favicon.ico` at the domain root there. The head tags cover the pages either way. If a Site Icon is set, put that icon there, converted to an ICO file named favicon.ico, instead.

## Setup

Activate the theme. It scaffolds itself: the pages and the footer menu, the Reading options and rewrite rules, and the starter content. Nothing is ever overwritten and nothing is deleted, so re-activation is safe.

Upgrading in place does **not** fire the activation hook. On a site already running the theme, use **Appearance → Theme Options → Setup → Create anything missing**, which reports what is present and creates only what is absent.

Full detail in [setup.md](setup.md).

## Structured data

`inc/schema.php` emits JSON-LD: Journal posts as `BlogPosting` with their sources under `citation`, `lastReviewed` on the page once "Last reviewed" shows, speakable parts (the title, the dek and "In short") and an `FAQPage` only when the post shows FAQ-style Details blocks; Doubts as `QAPage`, Rebuttals as `Article` with the claim under `about` and their sources under `citation`, Fatwa entries as `Article`, `WebSite` with a `SearchAction` plus `Organization` on the front page, an `FAQPage` on the FAQ page and the doubt category archives (Emotional and Identity excepted), and a `BreadcrumbList` on inner views. Rebuttal and fatwa Articles carry an image, and the author a url. The owner keeps all schema.org markup, Google-supported or not, for GEO, AEO and AIO (decided 2026-11-03). `inc/seo-title.php` builds the title tag (`<page title> | <site name>`, under 60 characters, page parts for long or shared titles in `inc/data/seo-titles.json`), prints the canonical link on archives and serves the image sitemap at `/murtadd-image-sitemap.xml`.

## Performance

- Every stylesheet and script ships with a `.min` twin, and per-template CSS loads only on its own views.
- Fonts are self-hosted WOFF2 files; the Qur'an face is fetched only on a page that shows a verse.
- The glossary's CSS and JS load only on an entry that mentions a term.
- Theme Options builds only the open tab, and fills the others over admin-ajax when chosen.
- JavaScript is an enhancement: every page reads without it. The reading progress bar, the glossary's tap and Escape handling and the fatwa filters' auto-submit need it; the rest is plain HTML and CSS.

## Documentation

| File | Contents |
|---|---|
| [ssot.md](ssot.md) | Architecture and the binding rules. Read before changing anything. |
| [changelog.md](changelog.md) | Every release. |
| [upgrading.md](upgrading.md) | Migration steps, version to version, and the roadmap. |
| [setup.md](setup.md) | Installation and what activation does. |
| [settings.md](settings.md) | Appearance → Theme Options, tab by tab. |
| [content-model.md](content-model.md) | The three post types and their fields. |

`readme.txt` at the theme root is the WordPress-format readme and stays there by convention.
