# Settings — Appearance → Murtadd

Four tabs, Settings API throughout, one option array per tab.

## Colours (`murtadd_colours`)
- **Accent colour** — links, buttons, sidebar panel. Live WCAG AA contrast check against white (warns below 4.5:1, never blocks). Injected via `wp_add_inline_style` as `--murtadd-accent`.
- **Accent tint** — light wash backgrounds; `--murtadd-accent-tint`.

## Cross-links (`murtadd_cross_links`)
- **Evidential case base URL** — homepage "full argument" card and doubt-page deep links. Cleared = hidden.
- **Polemics base URL** — second homepage card. All outbound links open in a new tab with `rel="noopener"`; no relationship is stated on the front end.

## Content display (`murtadd_content_display`)
- **Short response word cap** — soft cap for the doubt editor's live word count (default 400; warns, never blocks).
- **"If you are struggling" notice** — toggle for the crisis-support block on Emotional and Identity doubt pages.
- **Fatwa index default sort** — Scholar A–Z / Era / Recently added.
- **Footer heritage note** — free text in the slim footer bar.

## Taxonomies
Read-only tab: deep links to the native term screens for Topics and Schools. Doubt Categories are locked — four fixed terms power the homepage grid.

## Setup

Status of every page, menu, and Reading option the templates depend on, plus a button to create anything missing. Use it after upgrading the theme in place, since a file replacement does not fire the activation hook. It never overwrites and never deletes; pressing it on a configured site changes nothing.

The sidebar's "Start here" button renders only when the `start-here` page exists. If it is absent from your site, that page is missing.

## Comments (Settings → Discussion)

Comments are handled by WordPress core; the theme supplies the markup and styling only. Relevant core settings:

- **Comment must be manually approved** — recommended. Unapproved comments show an "Awaiting moderation" notice to their author and to no one else.
- **Enable threaded comments** — the theme styles nesting to any depth; core's `comment-reply` script loads only when threading is on.
- **Comment author must fill out name and email** — the form adapts automatically.
- **Show comments cookies opt-in checkbox** — styled if enabled.

Comments appear on blog posts and pages. The three custom post types do not declare `comments` support; add `'comments'` to a CPT's `supports` array in `inc/cpt-*.php` to enable them there, and `comments.php` handles the rest with no further changes.
