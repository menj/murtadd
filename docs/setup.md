# Setup

## The short version

Install Twenty Twenty-Five (parent), activate Murtadd. The theme scaffolds itself on activation.

## What activation does

`inc/activation.php` runs once on `after_switch_theme`, deferred to `init` so the post types exist first. It:

1. **Creates missing pages**, published, with placeholder text: `home`, `blog`, `start-here` (with the "Start Here" template assigned), `irtidad`, `about`, `faq`, `contact`, `privacy`.
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

* **Replace the placeholder copy.** The new pages are public immediately and say so plainly. The `home`, `blog`, and `start-here` templates do not render page content at all, so their copy is only a note to whoever opens them in the editor. `irtidad`, `about`, `faq`, `contact`, and `privacy` do render it.
* **Favicon**: Settings → General → Site Icon, uploading `icons/site-icon-512.png`. See docs/settings.md.
* **Colours and cross-link URLs**: Appearance → Murtadd.
