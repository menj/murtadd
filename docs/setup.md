# Setup

## The short version

Install Twenty Twenty-Five (parent), activate Murtadd. The theme scaffolds itself on activation.

## What activation does

`inc/activation.php` runs once on `after_switch_theme`, deferred to `init` so the post types exist first. It:

1. **Creates missing pages**, published, with their written copy: `home`, `blog`, `start-here` (with the "Start Here" template assigned), `irtidad`, `about`, `faq`, `contact`, `privacy`.
2. **Creates a "Site" menu** from About / FAQ / Contact / Privacy and assigns it to the "Footer — Site column" location.
3. **Sets Reading options**: static front page = Home, posts page = Blog.
4. **Flushes rewrite rules**, after the CPTs and taxonomies have registered.

It reports what it did in an admin notice.

## What activation will not do

Two rules govern the routine, and both matter if you ever re-activate the theme on a live site:

* **It never overwrites.** A page that already exists at that slug is skipped. A menu already assigned to the footer location is left alone. A front page you have already chosen is not reassigned, and a posts page already set is not touched. It only fills gaps.
* **It never destroys.** Nothing is deleted, trashed, or unpublished.

Re-activating is therefore safe, and running on a site that is already configured changes nothing.

## What is still yours

* **Contact and Privacy.** Every page ships written, but two need facts only the owner has. **Contact** needs a contact method (a form or an address); **Privacy** needs the host and its server logs, any plugins that store data, the legal basis, retention, and a contact route. Each page carries an HTML comment, visible only in the editor, listing what to add. The `home`, `blog`, and `start-here` templates do not render page content at all; `irtidad`, `about`, `faq`, `contact`, and `privacy` do.
* **The fatwa index starts empty.** Until the first Fatwa entry is published, `/fatwa/` says the index is in preparation and is `noindex`, and the sidebar, front-page footer, search page and 404 page leave its link out. Nothing is invented to fill it.
* **Letters are not seeded.** Add them under Letters as they are written.
* **Favicon**: Settings → General → Site Icon, uploading `icons/site-icon-512.png`. See docs/settings.md.
* **Colours and cross-link URLs**: Appearance → Murtadd.
