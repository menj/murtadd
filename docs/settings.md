# Settings — Appearance → Theme Options

Settings API throughout, one option array per tab. Every tab is on one screen: click a tab, or use the arrow keys on the tab row, and its settings appear without a reload. Switches are toggles, the link caps on the Contextual links tab are sliders that show their number as you drag, the fatwa sort on the Content tab is three cards to pick from, and the two accents open WordPress's own colour picker. Each tab has its own Save Changes button and saves only that tab.

## Colours (`murtadd_colours`)
- **Accent colour** — links, buttons, sidebar panel. Live WCAG AA contrast check against white (warns below 4.5:1, never blocks). Injected via `wp_add_inline_style` as `--murtadd-accent`.
- **Accent tint** — light wash backgrounds; `--murtadd-accent-tint`.

## Cross-links (`murtadd_cross_links`)
- **Evidential case base URL** — homepage "full argument" card and doubt-page deep links. Cleared = hidden.
- **Counterpart link** (`show_counterpart`, on by default) — the "Read it on Compelling Evidence" aside on doubts and rebuttals that carry a `_murtadd_ce_slug`, linking to `<base>/articles/<slug>/`. Unticked = hidden.
- **Starting-point card** (`show_quiz_card`, on by default). The "Not sure where to start?" card on the home page and Start Here, linking to `<base>/quiz/?from=murtadd` in the same tab. Unticked = hidden.
- **Polemics base URL** — second homepage card. All outbound links open in a new tab with `rel="noopener"`; no relationship is stated on the front end.

## Contextual links (`murtadd_contextual_links`)
In the running text of a single entry, the first mention of a known phrase links to the page that treats it, here or on a sister site. The phrase list is the shared `inc/data/network-links.json`, identical in all seven themes. Stored text is never changed. Emotional and Identity doubts get no links.
- **Links to this site** — on/off, and at most how many a page (default 4, 0 to 15).
- **Links to sister sites** — on/off, and at most how many a page (default 3, 0 to 10). Same tab, `rel="noopener"`, styled like any other link.
- **Extra phrases** — one per line, `phrase | /path/` for this site or `phrase | https://…` for a sister site. Tried before the shared list, so they win. Any other host, or a line in another shape, is dropped on save.
- **Never link on** — slugs of entries or pages that get no contextual links.

## Content display (`murtadd_content_display`)
- **Short response word cap** — soft cap for the doubt editor's live word count (default 400; warns, never blocks).
- **"If you are struggling" notice** — toggle for the crisis-support block on Emotional and Identity doubt pages.
- **Breadcrumbs** — the trail above entries, pages and archives, and the BreadcrumbList that mirrors it (on by default). With Rank Math's breadcrumbs switched on, Rank Math's trail is shown in its place.
- **Glossary of terms** — on by default. The first mention of an Arabic or Malay term on an entry gets a short definition and its source, on hover, focus or tap. Never on Emotional or Identity doubts. The terms are in `inc/data/glossary.json`.
- **Fatwa index default sort** — Scholar A–Z / Era / Recently added.
- **Footer heritage note** — free text in the slim footer bar.
- **Contact form recipient** (`contact_recipient`, empty) — where messages from the `[murtadd_contact]` form on the Contact page are sent. Empty, or not a valid address, uses the administration email under Settings → General. Saved only if it is a valid address. It is never printed on the site. See the Contact form section below.

- **Reading progress bar** (`progress_bar`, on): a thin bar that fills as the reader moves through an entry. **Bar colour** (`progress_colour`, empty = the accent), **Height** (`progress_height`, 2 to 12 pixels, default 3), **Edge** (`progress_position`, top or bottom; at the top it starts below the admin bar) and **Entry types** (`progress_types`, default rebuttals, guides, posts and pages). Doubts are never offered and are refused if saved: they are short by design. The bar is `role="progressbar"`, labelled, with its values, and cannot be focused. A static front page counts as a page. Until the bar is saved here, the plugin's saved options (`rp_settings`) are used. Stands down while the Reading Progress Bar plugin is active.

## Search addresses (`murtadd_search`, on the Content tab)
- **Readable addresses** (`pretty`, on): `/?s=term` answers with a 301 to `/search/term/`, keeping the page number (`/search/term/page/2/`) and any other argument. An empty search is never redirected.
- **Base word** (`base`, `search`): lowercase letters, numbers, hyphens and underscores (`sanitize_key`). A word the site already routes, such as `doubts` or `topic`, is refused and the old base kept. Also editable as **Search base** in Settings, Permalinks, as the Pretty Search Permalinks plugin had it. The plugin's saved base is imported. A changed base rebuilds the rewrite rules once; otherwise they are rebuilt only on a version change. Stands down while the plugin is active.

## Unlisting (editor sidebar, Quick Edit, Bulk Edit)
An **Unlist** box on every public post type. An unlisted entry opens from its own link but is left out of the front page, archives, feeds, search, the core and Rank Math sitemaps, the image sitemap, previous and next links, related doubts, the rail, page lists and REST listings, and is `noindex` (through Rank Math's robots filter when Rank Math is active; Yoast's own tag is emptied). Post lists show an Unlisted column and an Unlisted view. Each entry's state is a meta flag, `_murtadd_unlisted`; the option `murtadd_unlisted` and the plugin's `unlist_posts` are rebuilt from the flags. The plugin's list is imported on any request, and again when it changes, and the theme stands down while the plugin is active.

## Profile icons anywhere
`[murtadd_social_icons]`, or the **Profile icons** block pattern, prints the profile row from the Profiles tab in running text. It takes the text colour; the footer row keeps the footer ink. Each icon is a 32-pixel target with a visible focus ring.

## Security (`murtadd_security`)
Each switch is independent.
- **Author names** (on) — closes the REST user list to visitors (logged-in users keep it; the block editor needs it), `?author=` addresses and author archives (301 to the home page), and the users sitemap. Feeds and oEmbed name the author from the Profiles tab, or the site. wp-admin keeps the real names.
- **XML-RPC** (off) — `xmlrpc.php` answers no method, and the `X-Pingback` header and RSD link go. Switch it back on only for the WordPress mobile app or Jetpack.
- **Security headers** (on) — `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-Frame-Options: SAMEORIGIN`, `Permissions-Policy` (camera, microphone, geolocation, payment, USB off). A header the server already sends is not sent again. Filter: `murtadd_security_headers`.
- **Private login address** (empty, so off) — once saved, the login screen moves there and `wp-login.php`, `wp-register.php` and wp-admin show a page not found to anyone not logged in. A reserved, malformed or page-colliding address is refused on save and the previous one kept; the addresses of the pages the theme creates (Home, Start Here, Irtidad, About, FAQ, Contact, Privacy) are reserved even before the page exists. **Locked out:** add `define( 'MURTADD_HIDE_LOGIN', false );` to `wp-config.php`, log in at `wp-login.php`, clear the field, remove the line. Stands down while the WPS Hide Login plugin is active.
- **Login page** (on) — the login screen in the site's colours and type: ivory ground, a white card with the accent on its upper edge, the Source Serif and Public Sans pair. The mark is the custom logo, then the Site Icon, then the theme's own mark, links to the site, and carries the site name. The accents follow the Colours tab. Styled through WordPress's login hooks only, so it works at the private login address too. Off returns WordPress's own screen.
- **Note above the form** (empty) — plain text, up to 500 characters, on the log-in screen only.

## Upkeep (`murtadd_upkeep`)
- **Removed for good (410)** (empty) — addresses that never belonged to the site, such as spam pages from the 2024 compromise, answered `410 Gone` with `noindex` so search engines drop them. One per line: an exact path (`/cheap-pills/`, trailing slash and capitals ignored) or a prefix ending in `*` (`/shop/*` covers `/shop` and everything under it, not `/shopping`). Applies only where the site has nothing to serve, and after the legacy redirects, so a live page or a recovered pre-2024 address is never answered 410. `/` and `/*` alone are refused; at most 200 lines. The page is `410.php`, built from the 404's classes. From 1.55.7 a `*` may stand anywhere (`/feed-*.xml`), and a line may carry a query string (`/?items/*.sthml`); such a line also applies on the home page, and its key may not be one WordPress or a tracking link uses (`p`, `s`, `page_id`, `utm_source` and the like). `/?*` alone is refused.
- **Spam presets** (on) — answers 410 for the spam addresses the November 2026 logs show: `/?items/*.sthml`, `/index.php/sitemap*`, `/sitemapimages*`, `/contents/*` and `/goods.php`, on a 404 and on the home page WordPress served for them. An address that leads to a real post, page or archive is never touched.

- **Maintenance mode** (off) — visitors get the "Back shortly" page with `503` and `Retry-After: 600`, so search engines keep the pages and return. Anyone who can change the theme's options sees the site, and the admin bar says maintenance is on. The login screen, wp-admin, REST and cron stay open.
- **Error pages** (on) — the theme writes `maintenance.php`, `php-error.php` and `db-error.php` (with `murtadd-error-page.css`, and copies of the static pages in `murtadd-errors/`) into `wp-content`, where WordPress looks for them: one branded page for core, plugin and theme updates, fatal PHP errors (`503` when it looks like an upload in progress, `500` otherwise) and database failures. Written the first time an administrator opens wp-admin, and again whenever the version, site name or addresses change. A file the theme did not write is never overwritten. Below the form, a table shows each file's state, with a button to write them now. Switching this off, or switching theme, removes the theme's copies.

## Taxonomies
Read-only tab: deep links to the native term screens for Topics and Schools. Doubt Categories are locked — four fixed terms power the homepage grid.

## Setup

Status of every page, menu, and Reading option the templates depend on, plus a button to create anything missing. Use it after upgrading the theme in place, since a file replacement does not fire the activation hook. It never overwrites and never deletes; pressing it on a configured site changes nothing.

The sidebar's "Start here" button renders only when the `start-here` page exists. If it is absent from your site, that page is missing.

## Contact form (`[murtadd_contact]`, `inc/contact.php`)

The Contact page carries the shortcode; put it on any page to show the form there. Fields: name (optional), email (optional, for a reply) and message (required, up to 5,000 characters). It sends a plain-text email with `wp_mail()` to the recipient above, with the reader's address as `Reply-To` when one is given. Nothing is stored in the database.

- **Protection:** a nonce; a honeypot field that people never see (a filled one is answered as sent and nothing is mailed); and at most 3 messages an hour from one connection, counted in a transient keyed by an HMAC of the IP address, so the address itself is never stored. A refused message (empty, or a malformed email) does not count.
- **Notices:** a success notice after the redirect, and error notices (expired form, limit reached, empty message, malformed email, mail failure) with the reader's words kept in the fields.
- **Caching:** a page with the form is sent with `nocache_headers()`. If a caching plugin or CDN caches pages regardless, exclude the Contact page, or the nonce will expire for visitors after a day.
- **Mail delivery:** `wp_mail()` uses the server's mailer. If test messages do not arrive, configure an SMTP plugin; nothing in the theme needs to change.
- **Filter:** `murtadd_contact_limits` (`per_hour`, `name`, `message`).

## Comments (Settings → Discussion)

Comments are handled by WordPress core; the theme supplies the markup and styling only. Relevant core settings:

- **Comment must be manually approved** — recommended. Unapproved comments show an "Awaiting moderation" notice to their author and to no one else.
- **Enable threaded comments** — the theme styles nesting to any depth; core's `comment-reply` script loads only when threading is on.
- **Comment author must fill out name and email** — the form adapts automatically.
- **Show comments cookies opt-in checkbox** — styled if enabled.

Comments appear on blog posts and pages. The three custom post types do not declare `comments` support; add `'comments'` to a CPT's `supports` array in `inc/cpt-*.php` to enable them there, and `comments.php` handles the rest with no further changes.
