# Upgrading — Murtadd

## 1.38.0 → 1.39.0

Content edits to existing bodies, so the seeder will not apply them to a live site. Either paste each revised entry from `inc/seed-data.php`, or, if the site is not yet public, clear the seeded posts and re-seed. Revised: `religion-by-registration`, `apostasy-and-the-classical-law`, `is-islam-a-cult`, `the-head-cover-and-modesty`, `what-leaving-actually-costs`, `the-age-of-aisha`, `marital-consent-and-the-rape-charge`, `where-was-god-when-i-was-hurt`, `i-was-hurt-and-nobody-helped`, `are-all-non-muslims-damned`, `polygamy-and-the-condition`, `afraid-of-what-happens-if-i-leave`, plus the style sweep, which touches most entries lightly.

## 1.37.1 → 1.38.0

Content only.

1. Replace the theme directory with the 1.38.0 build.
2. **Theme Options → Setup → Create anything missing** creates the three rebuttals and the doubt, and files them under the existing Theodicy term. If the term was ever deleted, setup recreates it.

## 1.36.1 → 1.37.0

Additive. No content or template-structure changes.

1. Replace the theme directory with the 1.37.0 build. Include `assets/icons/social/` and `inc/social.php`.
2. Go to **Theme Options → Profiles**. Set the author name and paste a URL for each platform the author is on. Save.
3. The icons appear in the footer immediately, and the `sameAs` set appears in the page structured data. Confirm with any structured-data validator on an article URL: the author Person node should list every URL you entered.
4. Nothing renders for a platform left blank, so there is no need to clear unused rows.

## 1.36.0 → 1.36.1

Drop-in. Two SVG path strings changed in `inc/share.php`; no content, option, or template structure touched. Replace the theme directory and the share row renders the cleaner marks. If 1.36.0's content edits were not yet pasted on the live site, see the 1.35.0 → 1.36.0 notes below; they still apply.

## 1.35.0 → 1.36.0

Content only, and all of it edits to existing bodies.

1. Replace the theme directory with the 1.36.0 build.
2. The seeder never rewrites an existing post, so on a live site none of these nine revisions apply automatically. Paste each from `inc/seed-data.php`: `the-age-of-aisha`, `is-islam-a-cult`, `the-challenge`, `what-leaving-actually-costs`, `captives-and-slavery`, `apostasy-and-the-classical-law`, `are-all-non-muslims-damned`, `the-head-cover-and-modesty`, `religion-by-registration`.
3. If the site is not yet public, clearing the seeded posts and re-seeding is faster.

## 1.34.0 → 1.35.0

1. Replace the theme directory with the 1.35.0 build.
2. `the-age-of-aisha` is an edit to an existing body, so the seeder will not apply it to a live site. Paste it from `inc/seed-data.php`, along with its three replacement sources.

## 1.33.0 → 1.34.0

1. Replace the theme directory with the 1.34.0 build.
2. Check **Theme Options → Linked sites** has the Compelling Evidence URL set. No URL means no counterpart asides render anywhere.
3. On a live site the seeder will not rewrite existing posts, so the 49 mappings apply to fresh installs only. To adopt them on an existing site, open each entry and fill the counterpart field, or clear the seeded posts and re-seed if the site is not yet public.

## 1.32.0 → 1.33.0

Content only.

1. Replace the theme directory with the 1.33.0 build.
2. **Theme Options → Setup → Create anything missing** creates the six rebuttals and the doubt.
3. `i-was-never-asked` links to `religion-by-registration`, so seed both or neither; the seeder handles the order.

## 1.31.0 → 1.32.0

1. Replace the theme directory with the 1.32.0 build.
2. **Theme Options → Setup → Create anything missing** creates the two new rebuttals.
3. The front-page cross-link card text changed in the template, so it updates on upload with no action needed. Check **Theme Options → Linked sites** if the Compelling Evidence URL was ever cleared; the card only renders when a URL is set.

## 1.30.0 → 1.31.0

Content only.

1. Replace the theme directory with the 1.31.0 build.
2. **Theme Options → Setup → Create anything missing** creates the five new rebuttals.
3. The three sharpened entries are edits to existing bodies, so the seeder will not apply them to a live site. Paste them from `inc/seed-data.php` if you want them: `captives-and-slavery`, `is-islam-a-cult`, `the-head-cover-and-modesty`.

## 1.29.0 → 1.30.0

Content only.

1. Replace the theme directory with the 1.30.0 build.
2. **Theme Options → Setup → Create anything missing** creates the three new entries. Nothing existing is touched.
3. The new rebuttal fills the Testimony Patterns topic further; the two new doubts appear under Identity.

## 1.28.0 → 1.29.0

Additive. No content, setting, or template changes.

1. Replace the theme directory with the 1.29.0 build.
2. The redirects depend on two things setup creates: the **Irtidad** page and the **Apostasy Law** topic term. If either is missing, its redirects are skipped and those URLs keep 404ing — run **Create anything missing** on the Setup tab to be sure.
3. Verify with any old URL, e.g. `/definition/` should land on `/irtidad/`.
4. If you later rename the Irtidad page slug, the map follows the descriptor rather than a stored URL, so nothing breaks. Renaming the `apostasy-law` term slug does break it; add a filter on `murtadd_legacy_redirect_map` if you do.

## 1.27.0 → 1.28.0

Additive. No content, setting, or template changes.

1. Replace the theme directory with the 1.28.0 build. Make sure `vendor/` and `docs/` are both uploaded: the Docs tab reads the markdown at runtime, and a build stripped of either shows an error notice explaining what is missing.
2. Open **Appearance → Murtadd → Docs**. No setup step, no option to enable.

## 1.26.x → 1.27.0

Seeder behaviour only. No content, template, or asset changes beyond 1.26.1.

1. Replace the theme directory with the 1.27.0 build.
2. On the live site the safe order is now genuinely order-independent, but the intended sequence is: delete the two placeholder letters (they are visible to readers at /letters/), then press **Create anything missing** on the Setup tab. The run creates the six new entries, adopts the 31 existing posts into the seeded-slug record, and does not resurrect the letters — before this build, it would have.
3. If you deleted seeded content in the past and it kept coming back, that is this bug; it will not recur. To deliberately re-create a seeded post after deletion, remove its slug from the `murtadd_seeded_slugs` option (WP-CLI: `wp option get murtadd_seeded_slugs`) and press the button again.
4. The expanded `is-islam-a-cult` body still requires the manual paste described in the 1.25.0 → 1.26.0 notes; the seeder never overwrites.

## 1.26.0 → 1.26.1

Content taxonomy only. If you never deployed 1.26.0, go straight to this build and follow the 1.25.0 → 1.26.0 steps below; they are unchanged.

1. If 1.26.0's seeder already ran, the four retopiced entries exist with the old terms. Re-file them by hand in the editor (Apostasy Law on the apostasy pair, Testimony Patterns on the echo-chamber pair); the seeder will not touch existing posts.
2. The live site's empty **Apostasy Law** and **Testimony Patterns** topic pages fill as soon as the entries carry the terms.

## 1.25.0 → 1.26.0

Content only. No template, asset, or setting changes.

1. Replace the theme directory with the 1.26.0 build.
2. Go to **Theme Options → Setup**. The starter-content row will report 6 entries missing; press **Create anything missing**. The six new posts (3 rebuttals, 3 doubts) are created and cross-linked. Nothing existing is touched.
3. The expanded `is-islam-a-cult` body does not auto-apply, because the seeder never overwrites an existing post. To adopt it, open the rebuttal in the editor and replace the body with the version in `inc/seed-data.php` (the new "cult-studies criteria" and closing sections). Skipping this step is safe; the 1.24.0 body remains valid.
4. The main RSS feed will carry the six new entries as new items once.

## 1.24.0 → 1.25.0

1. Replace the theme directory with the 1.25.0 build. Two new stylesheets ship: `assets/css/tokens.css` and `assets/css/logo.css` (with their `.min` pairs).
2. If any custom CSS depended on `main.css` defining `:root` tokens or the `@font-face` rules, they now live in `tokens.css`, which loads first and is a declared dependency of `main.css`. Nothing on the front end changes visually.
3. Set a Site Icon at **Settings → General** to have it appear in the Theme Options hero; without one, the theme's own mark is used.

## 1.24.0 → 1.24.1

Admin labels and ordering. No settings are lost: the tab keys are unchanged, so every stored option stays bound to its tab. Replace the theme directory.

Bookmarks to `?page=murtadd&tab=…` still work; only the labels and the order changed.

## 1.23.0 → 1.24.0

Admin styling only. No front-end, database, option, or URL changes. Replace the theme directory and open **Appearance → Murtadd**.

## 1.22.0 → 1.23.0

1. Replace the theme directory with the 1.23.0 build. Rewrite rules flush automatically (the card endpoint needs it).
2. Verify a card renders: `/murtadd-card/{id}.svg` for any published doubt.
3. Featured images are now available on the content types. Use them for documentary images only — a folio, a court document, a screenshot of a claim as it circulates. Where none is set, the generated card carries the doubt.
4. Run the tests: `phpunit` (config in `phpunit.xml.dist`).

## 1.21.1 → 1.22.0

1. Replace the theme directory with the 1.22.0 build.
2. Doubt and rebuttal singles now carry a rail. Set the verse and reference under **Appearance → Murtadd → Content display → Scripture box**, or clear the checkbox to hide it.
3. The rendering of the verse is yours to choose. The theme ships a short default and does not impose a translation.

## 1.21.0 → 1.21.1

1. Replace the theme directory with the 1.21.1 build.
2. **Appearance → Murtadd → Setup → Create anything missing** publishes the two placeholder letters.
3. They are lorem ipsum and are titled as placeholders. Replace or delete them before the site is announced.

## 1.20.0 → 1.21.0

Adds the Letters post type. No existing content, option, or URL is touched.

1. Replace the theme directory with the 1.21.0 build. Rewrite rules flush automatically, so `/letters/` works on the first request.
2. **Letters** appears in the admin menu. The date of original publication is required: it drives the dateline that appears on every surface.
3. Letters are deliberately absent from site search, the main feed, and the homepage triage grid. This is by design, not an oversight — see the changelog.

## 1.19.1 → 1.20.0

Content only. Replace the theme directory, then **Appearance → Murtadd → Setup → Create anything missing** to publish the five new entries. Everything already present is skipped, and nothing you have edited is touched.

## 1.19.0 → 1.19.1

`assets/css/` and one conditional enqueue. Replace the theme directory. New file: `assets/css/start-here.css` (and its `.min` pair).

## 1.18.0 → 1.19.0

1. Replace the theme directory with the 1.19.0 build.
2. Appearance → Murtadd → Setup → **Create anything missing** publishes the three new entries ("The fear itself", "The broken ummah", "The state of the Muslims"). Everything already present is skipped.
3. Search behaviour changes: site search now matches doubt statements, responses, claims, and fatwa summaries. Expect more results, correctly.
4. The main RSS feed now carries doubts, rebuttals, and fatwa entries alongside posts. Subscribers will see the starter content appear as new items once.
5. If any SEO plugin is later installed that emits its own description/OG tags, disable `inc/meta-tags.php`'s output first — two sets of tags is worse than either alone.

## 1.17.0 → 1.18.0

A public URL change, in the right direction. No content or term data is touched.

1. Replace the theme directory with the 1.18.0 build. Rewrite rules flush automatically on the first request.
2. Verify: `/doubts/intellectual/` loads the term archive; `/doubts/kind/intellectual/` 301s to it; `/doubt-category/intellectual/` 301s to it in one hop.
3. Update the sitemap submission in Search Console. The 301s protect existing equity, but the sooner crawlers see the clean URLs as canonical, the better.
4. Doubt posts can no longer take the slugs `intellectual`, `scriptural`, `emotional`, or `identity`; WordPress appends `-2` automatically.

## 1.16.1 → 1.17.0

Documentation only. No code, template, database, or option changes.

1. **Delete the old theme directory before uploading**, or the four root-level Markdown files (`README.md`, `CHANGELOG.md`, `UPGRADING.md`, `SSOT.md`) linger beside their replacements in `docs/`.
2. Documentation now lives entirely in `docs/`, in lowercase. `readme.txt` and `license.txt` remain at the theme root.

## 1.16.0 → 1.16.1

1. Replace the theme directory with the 1.16.1 build.
2. `murtadd_the_logo()` takes a second argument: `murtadd_the_logo( 'on-light', false )` renders the mark as a `span` rather than a link. Existing calls without it keep the linked behaviour.

## 1.15.2 → 1.16.0

`assets/css/main.css` and its minified pair only. No template, database, or option changes. Replace the theme directory.

## 1.15.1 → 1.15.2

`assets/css/main.css` and its minified pair only. Replace the theme directory; asset version strings bumped, so caches invalidate automatically.

## 1.15.0 → 1.15.1

**Install this immediately if 1.15.0 is on the server.** 1.15.0 declares a function twice and takes the whole site down with a fatal error.

1. Replace the theme directory with the 1.15.1 build. If the admin is unreachable, replace the folder over SFTP or the host's file manager; no database access is needed.
2. No data was touched by the fault. Nothing needs repairing.

## 1.14.0 → 1.15.0

Adds content seeding. No importer plugin is needed any more.

1. Replace the theme directory with the 1.15.0 build.
2. Go to **Appearance → Murtadd → Setup** and press **Create anything missing**. This publishes the 8 doubts and 13 rebuttals along with anything else absent. (Upgrading in place does not fire the activation hook, so the button is the route on an existing site.)
3. If you already imported `murtadd-content.xml`, the seeder skips every post it finds and adds only the one rebuttal that was missing from that file.
4. Nothing you have edited is overwritten. A post at a given slug is skipped whatever state it is in.

## 1.13.0 → 1.14.0

Asset paths change. No database or option changes.

1. **Delete the old theme directory before uploading.** Do not merge, or the old root-level `css/`, `js/`, `fonts/`, `icons/`, and `rtl.css` linger as orphans.
2. Custom code referencing `/css/`, `/js/`, `/fonts/`, or `/icons/` must now use `/assets/css/`, `/assets/js/`, `/assets/fonts/`, `/assets/icons/`.
3. RTL overrides moved from the root `rtl.css` to `assets/css/main-rtl.css` and are now actually loaded. If the site runs an RTL locale, expect the layout to flip for the first time.

## 1.12.0 → 1.13.0

Files move. No content or setting is lost, but the paths change.

1. **Delete the old theme directory before uploading the new one.** Do not merge. Uploading over the top leaves the old `assets/icons/`, the flat `template-parts/*.php`, and the root `page-start-here.php` behind as orphans, and WordPress will happily keep serving some of them.
2. Load any admin page once. The Start Here page's template path migrates itself from `page-start-here.php` to `templates/page-start-here.php`.
3. If custom code calls `get_template_part()` on a part, the paths are now `template-parts/cards/card-*`, `template-parts/rows/row-*`, `template-parts/notices/notice-*`.
4. If custom code references `/assets/icons/`, it is now `/icons/`.

## 1.11.1 → 1.12.0

No database or option changes. No URL changes.

1. Replace the theme directory with the 1.12.0 build.
2. **Editor appearance will change.** `theme.json` now supplies the palette and type scale, so the block editor renders posts and pages the way the front end does. Any custom colour previously picked from the WordPress default palette will no longer be offered; the theme's own palette replaces it.
3. Minified assets are served by default. To debug CSS or JS, set `define( 'SCRIPT_DEBUG', true );` in `wp-config.php` and the unminified sources load instead.
4. **If editing CSS or JS, edit the source and regenerate the `.min` file.** A source edit alone has no effect on a live site, because the minified file is what gets served.

## 1.11.0 → 1.11.1

`css/main.css` only. Replace the theme directory; asset version strings bumped, so caches invalidate automatically.

## 1.10.0 → 1.11.0

Adds the relation resolver needed by the content import. No database or option changes.

1. Replace the theme directory with the 1.11.0 build.
2. Import `murtadd-content.xml` via Tools → Import → WordPress.
3. Load any admin page once. The doubt-to-rebuttal links resolve themselves, and the temporary meta keys are removed.

## 1.9.1 → 1.10.0

Templates and CSS only. No database, option, or URL changes.

1. Replace the theme directory with the 1.10.0 build.
2. The sidebar no longer carries About / FAQ / Contact / Privacy. Those links are in the footer: a column on the front page, a row in the slim bar everywhere else. The `footer_site` menu location is unchanged — assign a menu there and both placements pick it up.
3. If custom CSS targeted `.murtadd-sidebar-secondary`, it is retired. `.murtadd-secondary-list` still exists but is now styled for the light ground.

## 1.9.0 → 1.9.1

1. Replace the theme directory with the 1.9.1 build.
2. Go to **Appearance → Murtadd → Setup** and press **Create anything missing**. This creates the pages, the footer menu, and the Reading settings that the templates depend on — including `start-here`, without which the sidebar's "Start here" button cannot render.
3. The button never overwrites: existing pages, an assigned menu, and Reading options you have already chosen are left untouched.
4. Replace the placeholder copy on the new pages.

## 1.8.0 → 1.9.0

Adds activation scaffolding. Safe on a configured site.

1. Replace the theme directory with the 1.9.0 build.
2. Upgrading in place does **not** trigger the routine — it runs on `after_switch_theme`, not on file replacement. To scaffold an existing site, switch to another theme and back, or create the pages by hand.
3. If you do re-activate: nothing you have already set is overwritten. Existing pages are skipped, an assigned footer menu is left alone, and a front page or posts page you have already chosen is not reassigned.
4. Any page created carries placeholder text and is public immediately. Replace the copy.

## 1.7.2 → 1.8.0

No database, option, or URL changes.

1. Replace the theme directory with the 1.8.0 build.
2. `.murtadd-logo-small` is retired. If any custom CSS targeted it, retarget to `.murtadd-footer-about .murtadd-logo`.
3. To place the wordmark anywhere else, call `murtadd_the_logo( 'on-dark' )` or `murtadd_the_logo( 'on-light' )` rather than writing the markup again.
4. The footer "Site" column now hides itself when empty. To show it, assign a menu to "Footer — Site column" or create the about / faq / contact / privacy pages.

## 1.7.1 → 1.7.2

`css/main.css` only. No database, option, URL, or template changes. Replace the theme directory; asset version strings bumped, so caches invalidate automatically.

## 1.7.0 → 1.7.1

Layout fixes. No database changes; no option changes; no URL changes.

1. Replace the theme directory with the 1.7.1 build.
2. `css/main.css` and two fatwa templates changed. Asset version strings bumped, so caches invalidate automatically.
3. If you add a new top-level block to any template, it now inherits the view's column width automatically — do not add a per-element `max-width`. To change a view's width, set `--murtadd-measure` on `.murtadd-main` for that body class.

## 1.6.0 → 1.7.0

A public URL change. No database migration; no term or content data is altered.

1. Replace the theme directory with the 1.7.0 build.
2. Rewrite rules flush automatically on the first request after the update.
3. Verify: `/doubts/kind/emotional/` should load, and `/doubt-category/emotional/` should 301 to it.
4. Update any external links or sitemap submissions still pointing at `/doubt-category/`. The redirect covers them, but a 301 is a courtesy, not a permanent plan.
5. Do not create a doubt post with the slug `kind` — it is now reserved by the taxonomy path.
6. New option written: `murtadd_rewrite_version`. Add it to the manual-removal list in readme.txt if the theme is ever retired.

## 1.5.0 → 1.6.0

Assets only. No database changes; no option changes.

1. Replace the theme directory with the 1.6.0 build.
2. The fonts are now bundled — no manual upload step. Expect a visible change: the site finally renders in Source Serif 4 and Public Sans rather than the Georgia / system-ui fallback.
3. Set the real favicon at **Settings → General → Site Icon**. That is the correct route: an icon set there also drives the admin bar, the login screen, and the mobile apps. `icons/site-icon-512.png` is ready to upload. Until you do, the theme's fallback mark is served automatically.
4. Asset version strings bumped to 1.6.0.

## 1.4.0 → 1.5.0

Comments support. No database changes; no option changes.

1. Replace the theme directory with the 1.5.0 build.
2. New files: `comments.php`, `css/comments.css`.
3. Settings → Discussion governs everything: enable "Comment must be manually approved" (recommended), set threading depth, and decide on the cookies-consent checkbox. The theme styles whatever core emits.
4. Comments are off for the three custom post types by design. To enable them on rebuttals, add `'comments'` to the `supports` array in `inc/cpt-rebuttal.php`; the same template handles it with no further work.
5. Asset version strings bumped to 1.5.0 — caches invalidate automatically.

## 1.3.1 → 1.4.0

New templates only. No database changes, no option changes, no renamed functions.

1. Replace the theme directory with the 1.4.0 build.
2. Six new files: `search.php`, `searchform.php`, `404.php`, `archive.php`, `taxonomy-murtadd_school.php`, `template-parts/row-list-entry.php`.
3. Any custom code that duplicated the post-type label map can now call `murtadd_type_label( $post_type )`.
4. Asset version strings bumped to 1.4.0 — caches invalidate automatically.

## 1.3.0 → 1.3.1

Layout fixes only, all in `css/main.css` plus one new template tag. No database or option changes.

1. Replace the theme directory with the 1.3.1 build.
2. `murtadd_doubt_categories_ordered()` is new in `inc/template-tags.php`; `front-page.php` and `taxonomy-murtadd_doubt_category.php` now call it instead of raw `get_terms()`.
3. Asset version strings bumped to 1.3.1 — caches invalidate automatically.

## 1.2.0 → 1.3.0

No database changes. No option keys renamed. No template signature changes.

1. Replace the theme directory with the 1.3.0 build.
2. `uninstall.php` no longer exists — nothing to do; it never ran.
3. If any custom code called `murtadd_secondary_fallback()`, it still works: the function now loads from `inc/template-tags.php` on every request instead of only after `sidebar.php` was included.
4. Add the two woff2 files to the new `/fonts/` directory if not already present on the server (see `fonts/docs/readme.md`).
5. Hard-refresh: asset version strings bumped from 1.2.0 to 1.3.0, so caches invalidate automatically.
