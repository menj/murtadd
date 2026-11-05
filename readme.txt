=== Murtadd ===
Contributors: murtadd.org
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.55.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Doubts, taken seriously. A child theme of Twenty Twenty-Five: short answers to doubts, rebuttals of circulated claims, and an on-site fatwa index.

== Description ==
Murtadd is a classic-template child theme of Twenty Twenty-Five, built for murtadd.org. It answers doubts about Islam in short, sourced entries, rebuts claims as they circulate, and keeps an index of scholarly positions on apostasy.

The parent supplies the block library styles. Every front-end view is a PHP template in this theme.

== Features ==
* Doubts: short answers, grouped into four fixed categories (intellectual, scriptural, emotional, identity) that drive the home page grid.
* Rebuttals: the claim, the answer and its sources, with a table of contents and a reading-progress bar.
* Fatwa index: positions by scholar or body, filterable by school, topic and era with a plain GET form.
* Guides at /guides/ and a Journal of dated posts at /journal/.
* Letters: an archive of past correspondence, kept outside search and the home page.
* JSON-LD for every view (QAPage, FAQPage, Article, BlogPosting, BreadcrumbList and more), handed to Rank Math's graph when Rank Math is active.
* Contextual links from the first mention of a subject to the page that treats it, here or on another site, and a glossary of Arabic and Malay terms.
* A contact form shortcode, [murtadd_contact], which mails messages and stores nothing.
* Security switches, a private login address, a spam scan, maintenance mode, branded error pages and 410 answers for removed spam.
* Starter content: the pages, 38 doubts, 47 rebuttals, 12 guides and 8 Journal posts, inserted once and never overwritten.

== Installation ==
Install Twenty Twenty-Five first and keep it installed. It is the parent theme and need not be active.

With the WordPress uploader: go to Appearance, Themes, Add New, Upload Theme, choose the murtadd zip, install it and activate it.

With FTP:
1. Turn on hidden files in your FTP program first. In FileZilla it is Server, Force showing hidden files. The theme has files whose names start with a dot (.htaccess), and they must go up too.
2. Upload the murtadd folder into wp-content/themes under a temporary name, such as murtadd-new.
3. When every file is up, rename the old folder (if there is one) to murtadd-old, then at once rename murtadd-new to murtadd. Do the two renames one straight after the other, with Appearance, Themes closed: if WordPress opens that screen while the folder is missing, it switches the site to another theme.
4. Activate Murtadd under Appearance, Themes if it is not active yet. Delete murtadd-old once the site looks right.

The folder must be called murtadd, all lower case.

Uploading into the live folder file by file also works for most files. While one of them is missing or half written, visitors see the "Back shortly" page instead of an error. The one file that page cannot cover is functions.php, since it is the file that shows it; the temporary folder and the rename avoid that.

Never upload WordPress itself (wp-admin, wp-includes and the files beside them) to a live folder that has no wp-config.php. In that state the WordPress installer answers anyone who finds it. Put wp-config.php in place first, or upload WordPress to a temporary folder and rename it.

On activation the theme creates the pages it needs, a footer menu, and the starter content, and points Settings, Reading at the Home and Journal pages if the site still shows its latest posts. Nothing you have already set is overwritten, so activating again is safe. Upgrading in place does not re-run this; use Appearance, Theme Options, Setup.

Set the Site Icon at Settings, General (assets/icons/site-icon-512.png is ready to upload).

== Updating ==
Upload the new version the same way, then open any admin page as an administrator. The theme writes its error pages and server rules again if they changed, and removes the development files a working copy carries.

Entries you have edited keep your text. Upgrading does not re-run the activation steps: if something is missing, use Appearance, Theme Options, Setup, Create anything missing.

docs/upgrading.md has a note for each version and says what is planned next. It is also on the Docs tab.

== Usage ==
Everything is at Appearance, Theme Options. Pick a tab, or use the arrow keys on the tab row; each tab saves on its own.

* Setup: shows which pages, menus and starter entries exist, and creates anything missing.
* Content: the soft word cap for a doubt's short answer, the "If you are struggling" notice, breadcrumbs, the glossary, the scripture box in the rail, the fatwa index's default order (three cards), the contact form's recipient, the footer note, the reading progress bar and readable search addresses.
* Topics & schools: links to the WordPress screens for topics and schools. The 4 doubt categories are locked.
* Linked sites: the Compelling Evidence address, the counterpart link, the "Not sure where to start?" card, and the polemics address.
* Contextual links: switches for links to this site and to other sites, sliders for how many a page gets, extra phrases and pages to skip.
* Colours: the accent and its tint, with WordPress's colour picker and a live contrast check against white.
* Profiles: the author's name and a URL for each of 42 platforms. Each one shows as a footer icon and as sameAs in the structured data.
* Security: author privacy, XML-RPC, application passwords, REST changes, headers, the spam gate, the private login address and the login screen design. Below them: the spam scan, sessions, attack counts and the security log.
* Upkeep: maintenance mode, the branded error pages, and addresses to answer with 410 Gone.
* Docs: the manual in docs/, rendered in the admin.

== Frequently Asked Questions ==
= I set a private login address and cannot log in. =
Add define( 'MURTADD_HIDE_LOGIN', false ); to wp-config.php, log in at wp-login.php, clear the address on the Security tab, then remove the line.

= My front page shows the latest posts instead of the home page. =
That is what Settings, Reading asks for. Choose "A static page" there and pick Home. The theme follows whichever you choose.

= I see "Back shortly" on the site. =
The theme is incomplete on the server, usually because an upload is still running or stopped part-way. Upload the missing files, or the whole folder again. The login screen keeps working.

= My host runs Nginx. =
The theme writes its server rules only on Apache. On Nginx, give your host the rules in docs/readme.md (the Overview on the Docs tab) to add to the server settings.

= Does the theme work with Rank Math? =
Yes. The theme's meta tags stand down and its schema nodes go into Rank Math's graph without overwriting anything there.

= Can I add a fifth doubt category? =
No. The 4 categories are locked, and their addresses are fixed rules in the code.

== Privacy ==
The theme loads nothing from another server. Fonts, icons and scripts ship inside it, and it adds no analytics or tracking.

* The contact form sends each message by email and keeps no copy. To limit abuse it stores a keyed hash of the sender's IP address for 1 hour; the address itself is never written.
* The security module records failed logins for a day by account name, with the IP addresses they came from, counts attacks per day for 30 days, and keeps each account's last login time and IP address. Only administrators see these, on the Security tab.
* Comment avatars come from Gravatar when Settings, Discussion, Show Avatars is on, which is WordPress's own setting. Turn it off to stop those requests.
* Links to other sites and the Compelling Evidence quiz are plain links. Nothing is fetched from them.

== Copyright ==
Murtadd WordPress Theme, Copyright (C) 2026 Langgam Fikir Enterprise.
Murtadd is distributed under the terms of the GNU GPL v2 or later (license.txt).

Bundled resources:
* Source Serif 4, Copyright 2014-2023 Adobe, SIL Open Font License 1.1. Source: https://github.com/adobe-fonts/source-serif (assets/fonts/OFL-SourceSerif4.md).
* Public Sans, Copyright 2015 The Public Sans Project Authors, SIL Open Font License 1.1. Source: https://github.com/uswds/public-sans (assets/fonts/OFL-PublicSans.txt).
* KFGQPC HAFS Uthmanic Script, King Fahd Glorious Quran Printing Complex, under the Complex's own licence (assets/fonts/kfgqpc-hafs-uthmanic-script-LICENSE.txt). Served as a WOFF2 conversion by the owner's decision of 2026-10-26.
* Parsedown 1.8.0, Copyright 2013-2018 Emanuil Rusev, MIT. Source: https://github.com/erusev/parsedown (vendor/parsedown/LICENSE.txt).
* Unlisting, readable search addresses and the reading progress bar options are adapted from Unlist Posts & Pages by Nikhil Chavan, Pretty Search Permalinks by Angel Costa and Reading Progress Bar by Jean-Baptiste Audras, all GPLv2 or later.
* Social icons (assets/icons/social): Simple Icons, CC0 1.0 (https://simpleicons.org); LinkedIn, Scribd and ORCID from Font Awesome Free, CC BY 4.0 (https://fontawesome.com); SoundCloud from IcoMoon Free, CC BY 4.0 or GPL (https://icomoon.io); WordPress from Bootstrap Icons, MIT (https://icons.getbootstrap.com); ISNI, VIAF, Suno and OCLC Entities redrawn for the pack. Details in assets/icons/social/PACK-README.txt.
* The site icon, the pixel book and envelope images, the calligraphy in assets/images and screenshot.png were made for this theme and are GPL v2 or later with it.

== Changelog ==
= 1.55.8 =
* Documentation only. The readme covers updating over FTP, the overview gains the technical reference (boot, commands, options, filters, constants, server rules with their Nginx form), and the upgrade notes gain a roadmap. The counts of shipped entries are corrected.

= 1.55.7 =
* Fixes from the live logs. The spam addresses the logs show (/?items/….sthml, /index.php/sitemap…, /sitemapimages…, /contents/… and /goods.php) answer 410 Gone, and the Upkeep list takes query strings and wildcards.
* On Apache the web server runs only WordPress's own PHP files at the top of the site, and refuses WordPress's internal files. The Security tab lists any other PHP file it finds there.
* A theme caught half uploaded shows the maintenance page with a 503 status instead of a fatal error. Development files a working copy carries (.git, tests, Composer's packages) are refused and removed from a live site.

= 1.55.6 =
* The home page reads as one story, from the claim to the reader's question, the help for a reader who cannot name it, and the questions underneath, with headings that read as steps.
* The sidebar and the starting-point card are named for screen readers, and the reading progress bar sits inside the main content.

= 1.55.5 =
* CLAUDE.md and FARUQI.md move into the .claude folder, which the release zip leaves out and the web server never serves.
* On a site installed in a subfolder, the static error pages linked the home page and their stylesheets at the domain root. The theme now writes a copy of each into wp-content/murtadd-errors/ with the site's own addresses, and the .htaccess lines on the Upkeep tab point there.

= 1.55.4 =
* On a multisite install, a theme switch on the main site leaves the error pages in place while another site still runs Murtadd.
* The Upkeep tab shows a failed write over another theme's error page as failed.

= 1.55.3 =
* A real PHP error gets the 500 page again. A fault raised through a hook was shown as "Back shortly" with a 503.
* Error pages another theme marked as safe to delete are replaced. Any other file is left alone.
* A static 504 page in the errors folder, with its .htaccess line.

= 1.55.2 =
* Three guides no longer describe other websites as related to this one. Every link stays.
* The structured data names the author as Mohd Elfie Nieshaem Juferi, at https://menj.bio. Nothing visible changes.

= 1.55.1 =
* On a multisite subsite the Upkeep tab shows a network note in place of the Write the error pages now button, which gave a false success notice there.
* A file that could not be written no longer always blames wp-content: the source file may be unreadable.

= 1.55.0 =
* The database error page now says "We cannot reach our database", and the PHP error page is titled "Something went wrong".
* The Upkeep tab tells an outdated copy of an error page from a current one, and lists the .htaccess lines for the new static 403, 500, 502 and 503 pages in the errors folder.

= 1.54.0 =
* Unlist any entry from its editor box, Quick Edit or Bulk Edit. It still opens from its own link, but no listing, search, feed or sitemap shows it, and it is marked noindex. Post lists gain an Unlisted column and an Unlisted view.
* Searches move to readable addresses such as /search/apostasy/ with a permanent redirect. Set the base word in Settings, Permalinks or on the Content tab. An empty search is not redirected.
* The reading progress bar, which only rebuttals had, is set on the Content tab: on or off, colour, height, top or bottom, and the entry types. It now runs on guides, Journal posts and pages too by default. Doubts never get it.
* The [murtadd_social_icons] shortcode and the Profile icons pattern put the profile icons anywhere. Each icon is a 32-pixel target with a visible focus ring.

= 1.53.1 =
* With Rank Math on, an Article it prints where the theme has none of its own (the front page, a doubt, the FAQ page) gains an image: the featured image where the post has one, else the share image. On a single post it also gains the post's publish and modified dates where it lacks them. Nothing Rank Math set is changed.

= 1.53.0 =
* Theme Options is rebuilt as cards with tabs. The tabs switch without a reload and work from the keyboard. Booleans are toggle switches, the link caps are sliders that show their value, the fatwa sort is three cards, and the accents use WordPress's colour picker. Every option key and saved value is unchanged.
* A refused login address now shows its error after saving. The screen never printed settings messages before.
* The skip link moves focus into the page content, and the footer sits outside the main content, as its own landmark.
* Theme Options builds only the tab you open. Other tabs load when you choose them.
* With "Your latest posts" chosen under Settings, Reading, the front page shows the posts, with the footer links.
* The structured data escapes "<" and ">", so text in a title cannot close its script tag.
* The missing-pages notice can be dismissed, and the security warning can be closed.
* The contrast check's messages are translatable, and languages/murtadd.pot is regenerated.

= 1.52.0 =
* Doubt and rebuttal pages were missing one ">" in their markup, so a stray character showed above the title, the side rail sat below the article, and the footer bar ran 20 to 48 pixels past the screen edge. Fixed, and the rail sits beside the article again on wide screens.
* Phones get 44-pixel tap targets in the menu and footer, and 16-pixel search fields, so iOS no longer zooms in.
* Escape closes the phone menu and any open submenu. Submenu links report aria-expanded.
* The contact form ties each error to its field and marks the message as required in text.
* 4 colour pairs that failed WCAG AA contrast now pass.

= 1.51.0 =
* A Journal at /journal/: dated posts on doubt, leaving and the rulings on apostasy, beside the guides, which stay as they are. The menu and the footer link to it.
* 8 posts ship with fixed dates: 3 from October, 1 on 16 November and 4 in December, one of them for 1 Rajab. A past date is published, a future date is scheduled, and a second run of the seeder changes nothing.
* Each post is a BlogPosting in the page's one graph, part of the Journal as a Blog.
* A site set up before this release keeps its posts page: the seeded Blog page becomes the Journal page, and /blog/ redirects to /journal/.
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

== Upgrade Notice ==
= 1.55.8 =
Documentation only. Nothing needs doing.

= 1.55.7 =
Upload with hidden files shown, so the new .htaccess files go up. Then check the Security tab for PHP files at the top of the site that are not WordPress's own.

= 1.54.0 =
Readable search addresses and the reading progress bar are on by default. Check both on the Content tab.

= 1.53.1 =
Structured data fix. Nothing needs doing.

= 1.53.0 =
Theme Options has a new layout. Settings keep their values; nothing needs doing.

== Data removal ==
WordPress does not execute an uninstall routine for themes. If the theme is removed permanently, delete these options manually (WP-CLI: wp option delete <name>): murtadd_colours, murtadd_cross_links, murtadd_contextual_links, murtadd_content_display, murtadd_security, murtadd_upkeep, murtadd_dropins_stamp, murtadd_dropins_status, murtadd_placeholder_copy_retired, murtadd_topics_seeded, murtadd_schools_seeded, murtadd_doubt_categories_seeded, murtadd_rewrite_version, murtadd_needs_setup, murtadd_template_paths_migrated, murtadd_content_seeded, murtadd_seeded_slugs, murtadd_profiles, murtadd_author_name. Then delete the user meta murtadd_setup_notice_dismissed, which is user meta, stored per user (WP-CLI: wp user meta delete <user> murtadd_setup_notice_dismissed, for each administrator). All CPT posts and taxonomy terms persist by design.
