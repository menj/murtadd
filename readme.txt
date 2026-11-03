=== Murtadd ===
Contributors: murtadd.org
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.50.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Doubts, taken seriously. A child theme of Twenty Twenty-Five: short answers to doubts, rebuttals of circulated claims, and an on-site fatwa index.

== Description ==
Murtadd is a classic-template child theme of Twenty Twenty-Five. It registers three custom post types: Doubts (triage short answers), Rebuttals (claim + rebuttal + sources), and Fatwa (scholarly positions summarized in the site's own words, each linked to its primary source). A shared Topic taxonomy spans all three; a fixed four-term Doubt Category taxonomy powers the homepage grid; a School taxonomy drives the fatwa index filters.

Settings live under Appearance → Theme Options in a tabbed interface: Colours (with live WCAG contrast check), Cross-links, Content display, and Taxonomies (deep links to the native term screens).

All CSS lives in /css/, all JavaScript in /js/. Colour overrides are injected through wp_add_inline_style; no inline style blocks appear in templates.

== Setup ==
1. Install Twenty Twenty-Five (parent) and activate Murtadd.

That is the whole required step. On activation the theme scaffolds itself:

* Creates the pages it needs (home, blog, start-here, irtidad, about, faq, contact, privacy), assigns the "Start Here" template, and gives each one its written copy. Contact and Privacy carry notes, visible only in the editor, listing what the owner must add.
* Creates a "Site" menu from About / FAQ / Contact / Privacy and assigns it to the "Footer — Site column" location.
* Points Reading at the new pages: static front page = Home, posts page = Blog.
* Registers the CPTs, taxonomies, and seed terms, then flushes rewrite rules.
* Publishes the starter content: 19 doubts and 37 rebuttals, with their sources, topics, categories, and cross-links already wired.

Nothing is ever overwritten. A page, menu, or Reading option you have already set is left exactly as it is, so re-activating is safe.

Two things remain yours:
* Replace the placeholder copy on the new pages before announcing the site.
* Set the favicon at Settings → General → Site Icon (upload icons/site-icon-512.png), and set colours and cross-link URLs at Appearance → Theme Options.

== Changelog ==
= 1.50.0 =
* A small pixel book beside a rebuttal's sources and a fatwa's primary source, and an envelope on the contact form. Still for readers who ask for reduced motion.
= 1.49.0 =
* The JSON-LD graph describes the whole page: the page type, the sidebar, rail and footer, the verses shown, the glossary terms defined and the featured image. Every type printed before stays, doubts stay QAPages and rebuttals keep their Claim. Letters become open-letter Articles.
* A glossary term gets a DefinedTerm only where the page shows its tooltip. A term found only in a heading, a quotation or Arabic text gets none.
* Search results and the blog page carry their own address as url and @id. Before, both took the home page's.
* The sidebar node lists the Topics, Fatwa and Doubts dropdown links that sidebar.php prints.
* Word counts and reading times count Arabic words. str_word_count() saw only Latin letters.
* A doubt links its rebuttal as significantLink only once the rebuttal is published, as the page does.

= 1.48.0 =
* The doubt and the rebuttal on Qur'an 4:34 move to /doubts/quran-wife-beating-verse/ and /rebuttals/quran-wife-beating-verse-in-context/. A live site renames them in place once, and the old addresses answer with a 301.

= 1.47.0 =
* Every title tag reads "page title | site name" and stays under 60 characters, with or without Rank Math.
* All schema.org markup stays, as the owner decided on 2026-11-03 for GEO, AEO and AIO. Rebuttal and fatwa Articles gain an image, and the author a url.
* Archives print a canonical link, descriptions stop at 160 characters, and an image sitemap lists featured and attached images.

= 1.46.0 =
* The home page and the Start Here page carry a "Not sure where to start?" card. Its button opens the Compelling Evidence quiz in the same tab, at the evidential case base URL plus /quiz/?from=murtadd.
* A switch on the Linked sites tab turns the card off. It is on by default.

See docs/changelog.md for earlier releases.

== Data removal ==
WordPress does not execute an uninstall routine for themes. If the theme is removed permanently, delete these options manually (WP-CLI: wp option delete <name>): murtadd_colours, murtadd_cross_links, murtadd_contextual_links, murtadd_content_display, murtadd_security, murtadd_upkeep, murtadd_dropins_stamp, murtadd_dropins_status, murtadd_placeholder_copy_retired, murtadd_topics_seeded, murtadd_schools_seeded, murtadd_doubt_categories_seeded, murtadd_rewrite_version, murtadd_needs_setup, murtadd_template_paths_migrated, murtadd_content_seeded, murtadd_seeded_slugs, murtadd_profiles, murtadd_author_name. All CPT posts and taxonomy terms persist by design.
