=== Murtadd ===
Contributors: murtadd.org
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.39.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Doubts, taken seriously. A child theme of Twenty Twenty-Five: short answers to doubts, rebuttals of circulated claims, and an on-site fatwa index.

== Description ==
Murtadd is a classic-template child theme of Twenty Twenty-Five. It registers three custom post types: Doubts (triage short answers), Rebuttals (claim + rebuttal + sources), and Fatwa (scholarly positions summarized in the site's own words, each linked to its primary source). A shared Topic taxonomy spans all three; a fixed four-term Doubt Category taxonomy powers the homepage grid; a School taxonomy drives the fatwa index filters.

Settings live under Appearance → Murtadd in a tabbed interface: Colours (with live WCAG contrast check), Cross-links, Content display, and Taxonomies (deep links to the native term screens).

All CSS lives in /css/, all JavaScript in /js/. Colour overrides are injected through wp_add_inline_style; no inline style blocks appear in templates.

== Setup ==
1. Install Twenty Twenty-Five (parent) and activate Murtadd.

That is the whole required step. On activation the theme scaffolds itself:

* Creates the pages it needs (home, blog, start-here, irtidad, about, faq, contact, privacy), assigns the "Start Here" template, and gives each one placeholder text.
* Creates a "Site" menu from About / FAQ / Contact / Privacy and assigns it to the "Footer — Site column" location.
* Points Reading at the new pages: static front page = Home, posts page = Blog.
* Registers the CPTs, taxonomies, and seed terms, then flushes rewrite rules.
* Publishes the starter content: 19 doubts and 37 rebuttals, with their sources, topics, categories, and cross-links already wired.

Nothing is ever overwritten. A page, menu, or Reading option you have already set is left exactly as it is, so re-activating is safe.

Two things remain yours:
* Replace the placeholder copy on the new pages before announcing the site.
* Set the favicon at Settings → General → Site Icon (upload icons/site-icon-512.png), and set colours and cross-link URLs at Appearance → Murtadd.

== Data removal ==
WordPress does not execute an uninstall routine for themes. If the theme is removed permanently, delete these options manually (WP-CLI: wp option delete <name>): murtadd_colours, murtadd_cross_links, murtadd_content_display, murtadd_topics_seeded, murtadd_schools_seeded, murtadd_doubt_categories_seeded, murtadd_rewrite_version, murtadd_needs_setup, murtadd_template_paths_migrated, murtadd_content_seeded, murtadd_seeded_slugs, murtadd_profiles, murtadd_author_name. All CPT posts and taxonomy terms persist by design.
