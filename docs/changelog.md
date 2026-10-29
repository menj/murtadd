# Changelog — Murtadd

## 1.43.3 (2026-10-29)

### Changed (seed data)
- The default terms are JSON in `inc/data/seed/`, like the rest of the seed data: the five schools (`schools.json`, was `murtadd_seed_schools()`), the four doubt categories with their descriptions (`doubt-categories.json`, was `murtadd_seed_doubt_categories()`) and the six default topics (`default-topics.json`, was `murtadd_seed_topics()`, whose names were built from the slugs with `ucwords()`; the file spells out the same names).
- A file that cannot be read, or a malformed entry, now leaves the "seeded" flag unset, so the terms are made on a later load instead of never.
- Checked against the old code with stubbed WordPress calls: the same terms, names, descriptions and options. `SeedDataTest` covers the three new files. The PHPUnit suite was not run here.

## 1.43.2 (2026-10-29)

### Changed (wording)
- The guide The Islamic Dilemma: A Fair Guide for Doubters (`islamic-dilemma`, `inc/data/seed/guides.json`) now reads "it is not caught by either claim of the Islamic dilemma", the term the sister sites use.
- `FARUQI.md` sets out the Faruqian core, al-tawḥīd as al-Fārūqī set it out in *Al Tawhid: Its Implications for Thought and Life*, which every site in the network rests on. `CLAUDE.md` points contributors to it first.

### Existing sites
- The guide seeder never rewrites a guide that exists, so the new wording reaches new installs only. On murtadd.org, edit the guide by hand: in the paragraph that ends "it is not caught by either … of the Islamic dilemma", replace the word after "either" with "claim".

## 1.43.1 (2026-10-27)

### Removed (open letters)
- The open letters 1.43.0 added are gone. The owner did not ask for them. Removed: `inc/data/seed/letters.json`, `murtadd_seed_letters()`, `murtadd_seed_letter_posts()`, the `letters` count in the seeder's report and the activation notice, the letter kind (`_murtadd_letter_kind`, the Kind meta box, the open-letter frame on the single view, "Open letter" in the dateline), and the open-letter tests in `tests/SeedTest.php`. Letters are no longer seeded, as before 1.43.0. The Letters post type, its templates and `letter.css` are unchanged.
- Copy: the About page and the Letters archive no longer mention open letters. The About paragraph on letters is back to its 1.42 wording. `docs/ssot.md` matches.

### Existing sites
- `murtadd_retire_open_letters()` (on `admin_init`) moves each of the four seeded open letters back to draft, only when the seeder recorded its slug in `murtadd_seeded_slugs`, it is still published, and its body still matches what 1.43.0 seeded (an md5 of the stored markup after `murtadd_normalise_markup()`, with each link's scheme and host dropped). A letter edited in any way stays as it is. Nothing is deleted. The flag `murtadd_open_letters_retired` is set once every write has landed; a failed write is retried on the next admin request.

### Tests
- `tests/PageCopyTest.php` (+6): the recorded hashes match the 1.43.0 bodies at any host (`tests/fixtures/open-letters-1.43.json`); only unedited letters are drafted, and an edited body or link is kept; a slug the seeder did not record is skipped; the pass is idempotent; a failed write is retried; the hook is registered.
- `tests/SeedTest.php`: the seeder creates no letters again.

## 1.43.0 (2026-10-26)

- Prose revised to the house style.
- No named helplines. Specific Malaysian helpline names and numbers are removed from the doubts, the Is Islam False guide and the pages; readers are pointed to local emergency services, a helpline in their own country and findahelpline.com, as the Where to Find Support guide does.

### Added (guide: Is Islam false?)
- `/guides/is-islam-false/`, "Is Islam False? A Fair Guide for Doubters" (`inc/data/seed/guides.json`, Rank Math focus keyword "is Islam false" and SEO title). A care-first frame for the question, with a short summary of each angle (born into it, the Islamic dilemma, scientific miracles, hadith, morals, suffering, disagreement, hurt, the echo chamber) that links out to the page owning it rather than repeating it, and to the evidence article at `https://compelling-evidence.com/articles/is-islam-false/`. Safety and helpline guidance up front and on leaving. Hadith checked on shamela.ws (Muslim 132, al-Bukhari 1385); Qur'an 2:111, 2:170, 2:256, 3:190, 4:82, 17:36, 39:53 checked against api.quran.com. No existing page owned the question, so nothing was retitled.
- `tests/GuidesTest.php`: the guide count bound is now 9 to 12.

### Added (laʿnat Allāh mark)
- `[murtadd_lanah]` (`inc/lanah.php`): لعنة الله, "the curse of God", set after the name of Paul of Tarsus as calligraphy. `.murtadd-lanah` (`assets/css/main.css` and `main.min.css`) draws `assets/images/lanat-allah.svg` (copied from injil.help) as a mask in `--murtadd-accent-strike`, the palette's red; light only, with no dark-mode rule, per the Exclusions; `CanvasText` in forced-colours mode. The words "laʿnat Allāh, the curse of God" stay inside, visually hidden, for screen readers, search and a copy and paste, and show as written where the browser cannot draw a mask. The contextual-link engine skips `.murtadd-lanah`.
- No placement. The site is care-first and no seeded doubt, rebuttal, page or glossary entry discusses Paul, so nothing uses the shortcode. It is for the owner to place by hand, only where the text gives the site's own verdict on Paul, never in titles, headings, excerpts, descriptions, menus, alt text, structured data or quotations.
- Font licence: the calligraphy is drawn from the outlines of Dubidam Arabic (NamelaType), whose metadata names a free personal-use licence. A web licence is the site owner's to hold.

### Added (cross-links with Compelling Evidence)
- Counterpart switch. Theme Options → Cross-links → "Show the counterpart link" (`murtadd_cross_links[show_counterpart]`, on by default) turns the "Read it on Compelling Evidence" aside off on every doubt and rebuttal without clearing the base URL. The aside still renders on both single types and links to `<ce_base_url>/articles/<ce_slug>/` with `rel="noopener"`.
- ce_slug backfill. `murtadd_backfill_ce_slugs()` (on `admin_init`) writes the seed data's `ce_slug` into `_murtadd_ce_slug` on posts the seeder created (`murtadd_seeded_slugs`), only where the meta is empty, so a counterpart added to the seed data reaches existing installs without touching an editor's value. It records a hash of the mapping in `murtadd_ce_slug_backfill` and does nothing until the mapping changes.
- Every `ce_slug` in the seed data was checked against the Compelling Evidence article batches: all exist. The six entries without one (sects, the movement, the dare, music) have no clearly matching article and stay unmapped. Compelling Evidence now links back from each mapped article (its `inc/data/murtadd-counterparts.json`).
- Tests: `CeSlugBackfillTest`, and counterpart rendering, switch and sanitiser tests in `CounterpartTest`.

### Changed (login screen)
- Rebuilt login screen, in line with the sister themes. A green brand panel (the wordmark, a line of text, a soft gradient and ring) sits beside the form on wide screens and becomes a rounded band above it on phones. The form is a rounded card with 48px rounded inputs, pill buttons, a pill show-password toggle and accessible focus rings in the accent. Every screen wp-login.php serves uses it: log in, lost password, reset password, registration. The interim (session expired) login hides the panel.
- Notices: the owner's note, information, success and error messages are rounded cards with a coloured dot. Errors are tinted in the strike red, and core's flagged fields take a red border.
- Privacy note under the log-in, lost-password and registration forms (Theme Options → Security → Login page, on by default). It is always the last item in the card.
- New setting: "Line on the brand panel" (plain text, up to 160 characters). When it is empty, the site tagline is used.
- The links under the card are pills, and the language switcher is styled to match. Colours still come from tokens.css and the Colours tab through `wp_add_inline_style`. The wordmark's `logo.css` is now enqueued on the login screen.
- Still light only, per the Exclusions in docs/ssot.md: the screen declares `color-scheme: light`, so a device set to dark gets the same ivory and green screen. The hide-login address keeps working, because the screen is styled only through core's login hooks and builds no login URL of its own.

### Fixed (content review, High findings)
- The monkeys report (`were-monkeys-stoned-for-adultery`, doubt `hadith-contain-inhumane-things`): 'Amr ibn Maymun is a Successor who lived through the Jahiliyya, not a Companion, so the report is *maqtu'*, not *mawquf*. Cited as al-Bukhari 3849. New glossary term `maqtu'`.
- The first glance (`the-head-cover-and-modesty`): the source was al-Bukhari 2285, an unrelated hadith. Now Abu Dawud 2149 and al-Tirmidhi 2777 (al-Albani: hasan; al-Tirmidhi: gharib).
- The challenge verses (`the-challenge`): the order now runs from the whole book (17:88, 52:34) to ten suras (11:13) to one (10:38, 2:23).
- Qur'an 4:34 (`quran-4-34-in-context`): the dead bismikaallahuma.org source is removed; Muslim 1218 (*ghayr mubarrih*) replaces it. "a English" corrected.
- Guardian compulsion (`marriage-requires-consent`, `married-off-without-consent`, `the-age-of-aisha`): *ijbar* is stated as the Maliki, Shafi'i and better-known Hanbali position, with the Hanafi and Ibn Taymiyya dissent, after Ibn Rushd's *Bidayat al-Mujtahid*. Khansa bint Khidam is identified as previously married (al-Bukhari 5138); the virgin case is Ibn Majah 1875 / Abu Dawud 2096. Mixed USC-MSA numbers replaced.
- Women's testimony (`women-as-witnesses`, `womens-testimony-half`): the majority did not accept women's testimony in *hudud*; the entries now concede this instead of saying 2:282 extends to it.
- Morality police (`modesty-and-the-morality-police`): the classical *hisba* office and modern Sunni bodies are acknowledged; the sectarian line is gone.
- Tone: the fear doubt and the Irtidad page no longer read fear as attachment or promise that "nobody is coming for you"; they acknowledge real danger and point to help. The credential entries no longer say a person who left "never met Islam".
- Seeding never overwrites, so these corrections reach new installs; an existing site keeps its posts until an editor updates them.

### Added (content)
- Nine doubts: `if-it-was-all-written`, `why-so-much-suffering`, `born-into-it`, `verses-that-were-cancelled`, `the-music-i-love`, `written-down-centuries-later`, `alone-on-the-other-side` (Identity), `can-i-come-back` (Identity), `doubting-through-ramadan` (Emotional).
- Eight rebuttals: `qadar-and-the-charge-of-fatalism`, `the-argument-from-evil`, `why-this-religion-and-not-another`, `abrogation-and-the-edited-book`, `music-images-and-the-schools`, `return-after-leaving`, `hadith-and-the-two-centuries-gap`, `finite-sins-and-the-fire`. Each is paired with its companion-site article through `ce_slug` where one exists, and compresses what that article argues.
- Ten Emotional and Identity doubts, written care-first: `my-family-will-not-let-me-stop`, `my-spouse-does-not-know`, `i-stopped-praying-and-feel-guilty`, `i-cannot-bear-to-disappoint-my-parents`, `doubts-after-a-death`, `hurt-by-a-religious-teacher` (Emotional); `the-community-let-me-down-as-a-convert`, `i-feel-like-a-hypocrite-at-the-mosque`, `how-do-i-raise-my-children`, `what-the-law-says-in-malaysia` (Identity). Each points at an existing rebuttal through `related`, except where a new one was needed. Every doubt touching danger, abuse, self-harm or crisis carries a safety line and helpline guidance. Counterparts: `social-cost-of-leaving`, `the-dual-life` (two), `religious-trauma`, `apostasy-and-freedom`. 14 Emotional and Identity doubts became 24; 38 doubts, 47 rebuttals.
- Two rebuttals: `doubt-is-not-hypocrisy` (the hypocrite is defined by knowing deception, 63:1 and al-Bukhari 33; Hanzala, Muslim 2750; "that is clear faith", Muslim 132; 2:260; 49:14) and `leaving-islam-under-malaysian-law` (the claim that leaving is punished by death, answered from Articles 3, 11 and 121(1A), Act 355's sentencing cap, the Negeri Sembilan and Perlis procedures, the unapplied Kelantan and Terengganu provisions, and *Soon Singh*, *Lina Joy*, *Indira Gandhi* and *Rosliza*). It describes law, gives no advice to evade it, and points the reader elsewhere to his own jurisdiction. Hadith checked on shamela.ws and verses on api.quran.com.

### Added (contact form, pages, glossary, letters)
- Contact form (`inc/contact.php`, shortcode `[murtadd_contact]`, `assets/css/contact.css`, `assets/js/contact.js`, each with a `.min` twin). Name and email optional, message required. A nonce, a honeypot and a per-connection limit of 3 an hour, kept in a transient keyed by an HMAC of the IP address, so no address is stored. Every field is sanitised and capped, and the email field cannot inject a header. Sent with `wp_mail()` to the administration email, or to the new **Contact form recipient** on the Content tab (`murtadd_content_display[contact_recipient]`). Success and error notices; the reader's words survive an error. Works without JavaScript, which only stops a double send. Light only, pill button, rounded fields. No address is published anywhere.
- Contact page rewritten around the form, care-first: a reader in danger is sent to local emergency services or a helpline before anything else, and the page says what happens to a message.
- Privacy page written from what the theme does: no analytics (the reading time is computed on the server), self-hosted fonts, share and search, the contact form and what it sends and keeps, comments, cookies, Rank Math, and the host's server logs in general terms. The "Site owner" note is gone, and nothing on the page needs an operator's name.
- About page expanded to about 700 words from the site's stated purpose: who it is for, the four kinds of doubt, the two kinds of letter, where to begin, how it is written, what it does not do, corrections. It makes no claim about people or credentials.
- Existing sites: `murtadd_retire_placeholder_copy()` now also replaces the Contact and Privacy copy 1.41.0 to 1.43.0 seeded (the pages with the owner's HTML-comment note), matched by an md5 of the stored markup after `murtadd_normalise_markup()`, so a page converted to blocks still matches and any edit, even removing the note, does not. `murtadd_placeholder_copy_retired` now records a version (2); a site at 1 runs the pass once more.
- Glossary: 50 new terms (92 in all), from words the doubts, rebuttals and pages use: the schools of law and creed, the hadith collections, grades and genres, places and rites, *hisbah* and the *muhtasib*, *ijbar*, *ma'ruf*, *fitra*, *la darar wa la dirar*, *ghayr mubarrih*. Terms that appear only in Emotional or Identity doubts (*iftar*, Ramadan, *deen*) are left out, since those pages carry no definitions. Ibn Majah 2340 (al-Albani: sahih), Muslim 1218, and al-Bukhari 1385 and 5590 were checked against the Shamela editions.
- Open letters from the site (`inc/data/seed/letters.json`, loaded by `murtadd_seed_letters()`, inserted by `murtadd_seed_letter_posts()`): *To a parent whose child has stopped believing*, *To someone who has just left*, *To a convert who feels let down*, *To someone afraid to say it out loud*. Each is signed by the site and carries an editor's note saying it is an open letter from the site. No reader is invented. A new letter kind, `_murtadd_letter_kind` = `open` (meta box: Kind), changes the frame on the single view and adds "Open letter" to the dateline. Seeded like everything else: recorded in `murtadd_seeded_slugs`, never overwritten, never resurrected.

### Added (long-form guides)
- Guides post type (`murtadd_guide`, `inc/cpt-guide.php`) at `/guides/{slug}/`, index at `/guides/`. Guides are evergreen reference of 1,500 words and more, and the sister sites link to a fixed address, so they get their own type and skip the blog's permalink structure. Singles render through `single.php`, the index through `archive.php`; topics are the shared `murtadd_topic` terms. The rules reach sites already stamped 1.43.0 because `murtadd_guide_rewrite_once()` (init, 98) clears `murtadd_rewrite_version` once (flag `murtadd_guide_rules`) and the existing flusher at init 99 rewrites the full rule set.
- Eleven guides in `inc/data/seed/guides.json`: `islamic-dilemma` (at `/guides/islamic-dilemma/`, linked from the six sister sites, with the network cross-link block), `what-is-apostasy`, `the-apostasy-penalty`, `how-to-assess-a-doubt`, `hadith-grading-explained`, `reading-the-quran-in-context`, `doubt-and-the-inner-life`, `supporting-someone-who-doubts`, `where-to-find-support`, `leaving-and-the-law`, `coming-back-tawbah`. Hadith and classical texts are cited with shamela.ws book and page links; Qur'an citations were checked against api.quran.com. No guide gives a verdict on Paul of Tarsus, so none carries `[murtadd_lanah]`.
- Loader and seeder (`inc/seed-guides.php`): `murtadd_seed_guides()` validates the file all or nothing and caches it; `murtadd_seed_guide_posts()` never overwrites, adopts an existing post, records every slug under `murtadd_guide` in `murtadd_seeded_slugs` and never recreates a deleted guide. `murtadd_maybe_seed_guides()` runs it on `admin_init` once per change to the slug list (`murtadd_guides_seeded`). The excerpt is the meta description (and fills Rank Math's when empty); the Islamic dilemma guide also carries `rank_math_focus_keyword` and `rank_math_title`.

### Tests
- `tests/ContactTest.php` (new, 17): mailing, optional fields, nonce, honeypot, rate limit across connections, only a keyed hash stored, sanitising and caps, header injection, refused messages not counted, mail failure, recipient override and its sanitiser, markup, notices, page copy, assets.
- `tests/PageCopyTest.php`: Contact and Privacy need no operator; About within bounds; the 1.41.0 Contact and Privacy copy is replaced when unedited (block-converted included) and kept when edited; the pass is idempotent; the recorded hashes match the shipped copy (`tests/fixtures/pages-1.41.json`).
- `tests/SeedTest.php`: open letters are seeded once, marked open, dated, signed, never resurrected, never written over an editor's letter, and their links resolve.
- `tests/LoginTest.php` (+3): the brand panel escapes its line, falls back to the tagline, adds no second link home and goes away with the switch; the privacy note is on all three forms and can be switched off; the stylesheet has pills and focus rings, stays light, and its `.min` twin is current.
- `tests/GuidesTest.php` (new, 13): count, length, excerpts, the Islamic dilemma route, keyword density and network links, one rewrite flush, internal links resolve, topics exist, idempotent seeding, no resurrection, adoption, bad data.

## 1.42.1 — 2026-10-26
Security hardening. The starter content is data again, not code: no seeded article or page is executed as PHP to be loaded. Nothing that is seeded changes.

### Changed
- **Seed data is JSON.** The topics, rebuttals, doubts and page copy that lived as PHP arrays and strings in `inc/seed-data.php` (some 1,200 lines) are now `inc/data/seed/topics.json`, `rebuttals.json`, `doubts.json` and `pages.json`. `inc/seed-data.php` keeps only the loaders, with the same names and return shapes as before (`murtadd_starter_topics()`, `murtadd_seed_rebuttals()`, `murtadd_seed_doubts()`, `murtadd_seed_page_bodies()`), so `inc/seed-content.php`, `inc/activation.php` and the tests are unchanged. Each file is read with `file_get_contents()`, decoded with `json_decode()` and cached for the request. The same change the sister theme Abrahamic made to its seed files.
- **Links in the page copy are tokens.** `pages.json` writes each internal link as `{{url:/path/}}`; `murtadd_seed_page_bodies()` resolves it with `esc_url( home_url() )` on each call, as the `home_url()` closure did before, so a site in a subdirectory still links correctly.
- **A broken seed file seeds nothing instead of failing.** A file that is missing, unreadable, not valid JSON, or not the expected shape (an entry without its slug, a page body that is not a string) loads as an empty array. Activation then skips the written page it cannot fill, so it is created with its copy on a later run instead of empty and then never overwritten.
- **Verified identical.** The old functions and the new loaders return byte-identical values (compared with `serialize()` under WordPress stubs, at three `home_url()` bases with `esc_url()` marked on every link), so pages, entries, the placeholder retirement and its md5 comparisons behave exactly as in 1.42.0.

### Tests
- `tests/SeedDataTest.php` (4 tests): the seed files decode and the data directory holds no PHP; the loaders return what the files hold; every `{{url:}}` token resolves through `home_url()`; a missing file or a malformed entry loads as nothing.

## 1.42.0 — 2026-10-26
Breadcrumbs, FAQ structured data, a glossary of terms, and the tooling to test the theme in CI. The settings screen takes the name the other themes of the network use: Theme Options.

### Added (search engines)
- **Breadcrumbs:** a visible trail above entries, pages and archives (doubt through its category, rebuttal through its topic, fatwa through its school, letter, page parents) and the BreadcrumbList that mirrors it. Switch on Theme Options → Content display (on by default). With Rank Math's breadcrumbs switched on, Rank Math's trail and BreadcrumbList are used instead. Styles in `assets/css/breadcrumbs.css` with its `.min` twin.
- **FAQPage structured data** on /faq/ (from its question headings and the answers beneath them) and on doubt-category archives (each doubt's statement and short response). The Emotional and Identity categories get none: they are written care-first, not as a question set. `tests/FaqSchemaTest.php`.

### Added (Glossary of terms)
- **Glossary of terms.** The first mention of an Arabic or Malay term on an entry (irtidad, riddah, nafaqah, mahr, 'ayb, tudung, hudud, nushuz, i'jaz, dhimma, jizya and some thirty more) carries a short definition and its source, shown on hover, keyboard focus or tap. Each definition says what the word means in the sense the entries use it, never what a ruling is, and cites a work the seed already cites or the entry that explains it. Data in `inc/data/glossary.json`; engine in `inc/glossary.php`.
- **Where it applies:** the_content (21, after the contextual links), the doubt short response and the fatwa summary, once per term per page across all three. Never inside a link, heading, quotation, verse, the sources panel, the disclaimer or a button; a mention the text already glosses in parentheses is left as written. Emotional and Identity doubts get none.
- **Accessible and still:** a `<button>` with `aria-describedby` and a `role="tooltip"` definition laid over the text, so opening it never moves a line. Hover and focus need no script; `assets/js/glossary.js` adds tap to open and close, Escape to dismiss, and keeps the box inside the viewport. `assets/css/glossary.css` and the script, with their `.min` twins, load only on entries that mention a term.
- **Switch:** Appearance → Theme Options → Content display, "Glossary of terms" (on by default).

### Tests
- `tests/GlossaryTest.php` (19 tests): data complete and in house style; every term used in the seed; every "(this site)" source names a real entry; first mention only; accessible markup with unique ids; skip regions; self-glossed mentions; apostrophe forms and capitals; whole words; one definition per entry across render paths; queried entry, feeds, the switch and the care-first doubts; assets only where a term appears; the setting's sanitiser; the `.min` twins. Mutation-checked: removing the care-first guard, the once-per-page record, the gloss skip, the skip regions, the queried-entry check or the malformed-UTF-8 fallback each fails the suite.

### Changed (admin and tooling)
- **No inline styles in the admin.** The ten `style` attributes in `inc/settings-page.php` and the gaps widget in `inc/dashboard.php` are classes in `assets/css/admin.css` (`murtadd-gaps-none`, `murtadd-gaps-label`, `murtadd-gaps-list`, `murtadd-admin-note`, `murtadd-admin-spaced`, with the existing `murtadd-admin-table` and `murtadd-status-missing`), scoped so they still win over core admin CSS. The dashboard loads `admin.css` for the widget. Rendered against WordPress 6.6.2's admin CSS, every element computes as before.
- **The long-form script's words are translatable.** "Reading progress", "On this page" and "Back to top" in `assets/js/longform.js` come from `wp_localize_script()` (`murtaddLongform`), with the English kept as a fallback, and are in `languages/murtadd.pot`.
- **The block editor shows the front end's type.** `add_editor_style()` loads `tokens.css` and a new `assets/css/editor.css` into the editor canvas: ivory ground, Source Serif body at 17px/1.75, the green link, the serif and sans headings, the tinted quotation and the Arabic stack with right-to-left verse layout. The Qur'an face stays front-end only, because the editor shows verse text before `inc/quran-text.php` fits it.
- **`.gitignore`.** Test caches, `node_modules/`, `composer.lock` and Composer's `vendor/` are ignored; the vendored `vendor/parsedown/` still ships. The committed `.phpunit.result.cache` is removed from the repository.
- **Composer and CI.** `composer.json` (development only: PHPUnit) with `composer lint` and `composer test`, and `.github/workflows/checks.yml`, which runs the PHP syntax check and the PHPUnit suite on PHP 8.1 and 8.3 for every pull request and every push to main.

### Changed
- **The settings screen is called Theme Options** (Appearance → Theme Options), as in the other themes of the network. Its address (`themes.php?page=murtadd`) and its tabs are unchanged, so saved links keep working; the docs, notices and comments name it accordingly.

### Fixed
- **Glossary:** a two-word term (usul al-fiqh, ahl al-fatrah, fard al-'ayn) written with a non-breaking space, a line break or a double space is now defined; it was matched and then dropped. A definition added from text the stored entry does not hold (a synced pattern, a shortcode) now loads the glossary stylesheet and script, in the footer, instead of showing a bare button.
- **Structured data:** breadcrumb names and FAQ questions and answers are plain text, with entities decoded (`Don’t`, `Law & state`), not `Don&#8217;t` or `Law &amp; state`.
- **Breadcrumbs with Rank Math:** Rank Math's trail is printed as a paragraph inside the theme's `<nav>`, so the page has one breadcrumb landmark, not one nested in another.
- **Private login address:** a personal-data confirmation email now links to `wp-login.php` with the request's key, whatever the permalink structure. Without a closing slash, or without pretty permalinks, it carried the private address. `HardeningTest::test_confirmation_email_never_reveals_the_address`.

### Not in this release
- Speakable markup (the third search-engine item in the gap list) was not started; it remains on the list.

## 1.41.0 — 2026-10-26
Hardening, Rank Math, upkeep pages, written pages, and the Qur'an face. The placeholders are retired. Activation published five pages reading "Placeholder. Replace this text." and two lorem-ipsum letters, all public the moment the theme was switched on, and /irtidad/ is both the second route from Start Here and the target of the legacy redirects. Every page now ships with its copy written, and nothing the theme seeds says it is a placeholder.

### Added
- **Security tab** (`murtadd_security`, `inc/hardening.php`). The site at this domain was compromised in August 2024; the defaults now close what WordPress leaves open, each behind its own switch.
  - **Author names kept private** (on): the REST user list, `?author=` addresses and author archives (301 to the home page), and the users sitemap are closed. Feeds and oEmbed name the author from the Profiles tab, or the site. wp-admin keeps the real names.
  - **XML-RPC off** (on): no method answers, and the `X-Pingback` header and RSD link are removed.
  - **Security headers** (on): `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options` and `Permissions-Policy` on every front-end response. A header the server already sends is not sent twice. Filter: `murtadd_security_headers`.
- **Private login address** (`inc/hide-login.php`), a port of the WPS Hide Login request handling. Off until an address is saved. A reserved, malformed or page-colliding address is refused and the previous one kept. `define( 'MURTADD_HIDE_LOGIN', false );` in `wp-config.php` turns it off whatever is saved. It stands down while the WPS Hide Login plugin is active.
- **Rank Math compatibility** (`inc/rank-math.php`). With Rank Math active, the page used to carry two descriptions, two Open Graph blocks and two JSON-LD graphs, and the docs asked the owner to switch the theme's tags off by hand. Now the meta tags stand down by themselves and the page carries one graph, Rank Math's. A theme node is added only when Rank Math's graph has no node of its family, and otherwise fills empty fields without overwriting any: a doubt adds its QAPage; a rebuttal's Article gains its Claim and sources; a fatwa entry's Article gains its subject and primary source. An empty Rank Math description is filled from the content model, and search results and 404s stay noindex. With Rank Math's Schema module off, the theme prints its own graph as before.

### Changed
- **`inc/schema.php` builds the graph apart from printing it.** `murtadd_structured_data_node()` returns the node; `murtadd_structured_data()` prints it unless Rank Math is printing structured data.

### Added (Upkeep)
- **Upkeep tab** (`murtadd_upkeep`) with **Removed for good (410)**. Addresses that never belonged to this site, such as spam pages left by the 2024 compromise, are answered `410 Gone` with `noindex` and a branded page (`410.php`, built from the 404's classes), so search engines drop them instead of returning. One per line, exact or a prefix ending in `*`. It runs only on a 404, and after the legacy redirects, so a live page or a recovered pre-2024 address is never retired. The list ships empty, because no spam path from the compromise survives in the record.
- **Maintenance mode** (Upkeep tab, off). Visitors get a branded "Back shortly" page with `503` and `Retry-After`, so search engines keep the pages; anyone who can change the theme's options sees the site, and the admin bar says maintenance is on. The login screen stays open.
- **Branded error pages** (Upkeep tab, on, `inc/maintenance.php`, `dropins/`). After a compromise, a bare "critical error" screen reads as the site being hacked again. WordPress updates, fatal PHP errors (`503` for an upload in progress, `500` otherwise) and database failures now show one calm page in the theme's colours and fonts. The theme writes the three drop-ins and their stylesheet into `wp-content` from wp-admin, refreshes them when the version, site name or addresses change, never overwrites a file it did not write, and removes its copies when switched off or when the theme changes. A table on the tab shows each file's state, with a button to write them now. The pages carry no inline style or script.
- **Branded login screen** (Security tab, on, `inc/login.php`, `assets/css/login.css`). The login screen now looks like the site: ivory ground, a white card with the accent on its upper edge, and the self-hosted Source Serif and Public Sans. The mark is the custom logo, then the Site Icon, then the theme's own mark. It links to the site rather than WordPress.org and carries the site name, and the accents follow the Colours tab. It covers every screen `wp-login.php` serves and works at the private login address. An optional plain-text note can be set to show above the form.

### Changed
- **`/irtidad/` written.** The two words and their root; what the classical law meant by apostasy, from the confessional-state framing and the conditions the jurists attached; what does not take a person out of Islam (a question, a verdict pronounced over oneself, a registry entry, the fear); the law as it stands across Muslim-majority states; and a closing word for the reader tonight. It links into `apostasy-and-the-classical-law`, `religion-by-registration`, `i-was-never-asked`, `afraid-of-what-happens-if-i-leave`, `what-leaving-actually-costs`, `the-hadith-about-leaving`, `the-death-threat-charge` and the Apostasy law topic. Every claim rests on a source the seed already cites.
- **`/about/` and `/faq/` written** from what `docs/ssot.md` says the site is and does: the four content types, the forensic register, no individuals named, Malaysia as home and not frame, no testimonials, no tracking. The FAQ answers only questions the docs answer, and its support answer reuses the struggling notice's wording and the Talian Kasih example from the seed.
- **`/contact/` written** for readers (corrections with sources, questions not yet answered, and do not wait for a reply in a crisis). It invents no address: an HTML comment, which does not render, tells the owner to add a form or an address.
- **`/privacy/` written** from what the theme demonstrably does: no analytics or tracking scripts, self-hosted fonts, share buttons as plain links, generated share images, on-site search kept out of search engines, WordPress's own comment storage and Gravatar avatars, no theme cookies. An HTML comment lists what the owner must add (host and server logs, plugins, legal basis, retention, a contact route).
- **Letters are no longer seeded.** `murtadd_seed_letters()` and `murtadd_seed_letter_posts()` are gone, the seeder's report drops its `letters` count, and the Setup tab counts doubts and rebuttals only.
- **The empty fatwa index says so.** With no published entry, `/fatwa/` shows that the index is in preparation, links to the rebuttals and to Irtidad, hides the filter form, and is `noindex`. The sidebar, the front-page footer, the search page and the 404 page leave the Fatwa link out until the first entry is published (`murtadd_fatwa_index_has_entries()`). No fatwa entry is invented to fill it.
- **Activation and Setup notices** now ask for a contact method and the Privacy details in place of "replace the placeholder copy".
- **WordPress coding standards on the new code:** the contextual-links pattern cache keys on `wp_json_encode()` in place of `serialize()`; the private login's post-password check unslashes the posted action before its exact comparison (deliberately not `sanitize_key()`, which would lowercase "POSTPASS" and pass it to the login handler); the core constant `WP_USE_THEMES` carries a documented exception to the prefix rule.

### Upgrading
- **`murtadd_retire_placeholder_copy()`** runs on `admin_init` until every write has landed (flag `murtadd_placeholder_copy_retired`). A page that still holds the exact placeholder text, compared as a reader sees it so that one opened and saved unchanged in the block editor still counts, gets its written copy. Each lorem-ipsum letter whose text still matches the seeded body goes back to draft. Any page or letter an editor has changed fails the comparison and is left exactly as it is. This is the one narrow exception to "never overwrite, never destroy", and `inc/activation.php` says so. A placeholder stored under another locale is matched against the placeholder as translated in every language installed on the site (`murtadd_legacy_placeholder_strings()` under `switch_to_locale()`), so only a genuine translation of the placeholder can match and no page an editor wrote is ever mistaken for it; a translation whose language has since been removed is left alone.

### Tests
- `tests/PageCopyTest.php` (15 tests): no seeded text is placeholder or lorem; every rendered page has copy of substance; house style (no em dash, no contraction, no "actually"); every link in the page copy leads to seeded content; Contact and Privacy carry owner notes and no invented address; a fresh install gets the written copy; retirement replaces only unedited pages, drafts only unedited letters, is idempotent, and does nothing on an empty site. Mutation-checked: removing either comparison guard, the letter status check, or restoring a placeholder each fails the suite.
- `tests/SeedTest.php` updated for the retired letters.

### Added (Qur'an face)
- **Qur'anic verses in KFGQPC HAFS Uthmanic Script**, the face all seven sister themes use for the Arabic of a verse and for nothing else. Token `--murtadd-font-quran` on a mapped `.murtadd-ayah:lang(ar)` and on a mapped Arabic rail verse (`data-quran-encoding="kfgqpc"`); every other Arabic run, a hadith or a term, takes a system Arabic stack (`--murtadd-font-arabic`). Verses are set right to left at line-height 2.2 with no letter-spacing. Not preloaded.
- **Served as WOFF2** (`assets/fonts/kfgqpc-hafs-uthmanic-script.woff2`, 107 KB), with its licence text beside it. The owner chose this on 2026-10-26, accepting that the KFGQPC licence forbids modifying the font; the conversion keeps every glyph, the character map and the OpenType layout, and drops the digital signature.
- **`inc/quran-text.php`** maps verse text in the Tanzil encoding to the face's own as it renders (sukun U+0652 to U+06E1, rounded zero U+06DF to U+0652) and drops ornate brackets round an ayah number, which the face already draws as a medallion. Without it every sukun draws as a circle and every silent letter carries a stray ornament. Stored text is unchanged; a mapped element is marked `data-quran-encoding="kfgqpc"` so no pass maps it twice. Runs on `the_content` (6), the doubt short response, the fatwa position summary, the homepage ayah and an Arabic rail verse.

### Changed (housekeeping)
- **`languages/murtadd.pot`** regenerated from the code: 389 strings (was 296, last built at 1.23.0).
- **readme.txt** lists the new options to delete when the theme is retired: `murtadd_security`, `murtadd_upkeep`, `murtadd_dropins_stamp`, `murtadd_dropins_status`, `murtadd_placeholder_copy_retired`.

### Fixed (review)
- **The private login address can no longer take a page the theme creates.** `home`, `start-here`, `irtidad`, `about`, `faq`, `contact` and `privacy` join the reserved list. Before, a saved address matching one of those pages while the page was missing was accepted; when setup later created the page, the login screen answered at its address and the page could not be reached. A saved address that is now reserved is ignored, so the login screen returns to `wp-login.php` until a new one is saved.
- **Placeholder retirement finishes before it marks itself done.** The flag `murtadd_placeholder_copy_retired` is set after the writes, and only when every one landed; a failed `wp_update_post()` is retried on the next admin request.
- **Added markup counts as an edit.** A placeholder page or lorem letter that carries a link, an image or any element beyond the seeded paragraphs is left alone even when its visible text still matches (`murtadd_copy_has_added_markup()`).
- **Switching Error pages off cleans up after a partial install.** A first install that wrote some drop-ins and failed on another saved a status but no stamp, so switching the pages off removed nothing and the written drop-ins stayed active. Removal now runs when either option is present.
- **The login note is cut to 500 characters without mbstring too** (PCRE in UTF-8 mode in place of `mb_substr()`).

### Fixed (bug hunt)
- **A malformed byte no longer blanks a page.** Every pattern here with the `u` flag gives up on malformed UTF-8, and text imported from the old site can carry such a byte. The verse mapper returned an empty string for the whole post body when one was present (`murtadd_quran_verses()`, and the rail verse through `murtadd_quran_text_for_font()`); the contextual links emptied the paragraph that held it. Both now keep the text as it came.
- **Contextual links survive a phrase list too large for one pattern.** Past the PCRE size limit (about 58 KB of pattern, reachable through extra phrases on the tab) the single pattern failed to compile and every paragraph lost its text. The phrases are now compiled in as many patterns as compile (`murtadd_ctx_patterns()`), their matches merged in text order with the longer one winning at the same place (`murtadd_ctx_matches()`).
- **Saving the Contextual links tab with a malformed byte** no longer stops with a fatal error ("Never link on") or wipes every saved line ("Extra phrases"). Lines are split without the `u` flag, as `sanitize_text_field()` and `sanitize_title()` already drop bad bytes.
- **Phrases in non-ASCII capitals link without mbstring.** "IBN ISḤĀQ" missed its key when only ASCII letters could be lower-cased; a missed key is now compared phrase by phrase without regard to capitals (`murtadd_ctx_lookup()`).
- **Editors and authors keep the block editor's author panel.** "Author names kept private" removed the REST user routes for everyone but administrators, and the editor reads them for the author selector and the current user. They are now removed for visitors only; core already limits what each role may see there.
- **The Qur'an face goes only on verse text fitted to its encoding.** It was set on every `.murtadd-ayah` in Arabic, so a verse rendered on a path the mapper does not run showed round sukun and stray ornaments. The face now needs `data-quran-encoding="kfgqpc"`, which the mapper adds; an unmapped verse keeps the system Arabic stack at the same size.

### Fixed (second bug hunt)
- **Split phrase patterns no longer hide a link.** When the phrases are compiled in several patterns, each pattern's own scan consumed a match the merge then dropped as overlapping, and with it any phrase inside that match: in "alpha beta gamma" with "alpha beta" in one pattern and "beta gamma" and "gamma" in another, "gamma" was never found. The patterns are now scanned together from the end of the last match, as one pattern would be (`murtadd_ctx_matches()`).
- **A malformed byte elsewhere in a post no longer drops its verses out of the Qur'an face.** The verse mapper's pattern had the `u` flag, so one bad byte anywhere returned the markup unmapped and unmarked, and the face (which needs `data-quran-encoding`) fell back to Naskh on every verse. The pattern is now byte-wise: all its literals are ASCII and the mapping is `strtr()`.
- **Percent-encoded addresses in Extra phrases survive a save.** Each line went whole through `sanitize_text_field()`, which removes every percent-encoded octet, so "/gospels/%D8%A7/" was saved as "/gospels//". The phrase and the address are now cleaned apart: the phrase with `sanitize_text_field()`, the address with `esc_url_raw()`.
- **A match passed over no longer hides a phrase that overlaps it.** When a match was passed over (its page already linked, this page, a full limit, a used paragraph), the scan resumed after its end, so "Paul of Tarsus" in "Peter and Paul of Tarsus" never linked once "Peter and Paul" had been. The search now resumes one character after a match it passes over (`murtadd_ctx_next()`); a phrase wholly inside it ("Barnabas" in "Gospel of Barnabas") stays unlinked. Output on the seeded rebuttals and doubts is unchanged.
- **Phrases match across a non-breaking space.** A space in a phrase now matches a space, `&nbsp;`, `&#160;` or U+00A0, and the lookup key folds all of them to one space, so "Ibn&nbsp;Isḥāq" links like "Ibn Isḥāq".
- **A ">" inside a quoted attribute value no longer ends the tag.** The tag splitter stopped at the first ">", so the rest of a `title` or `alt` value became text that could take a link and break the markup; a tag now ends at the first ">" outside quotes.

### Tests
- Suite: 166 tests. New: `RobustnessTest` (malformed bytes in the mapper, the rail verse, the links and the tab; a phrase list past the PCRE limit; the no-mbstring lookup; REST routes for logged-in staff). New: `HardeningTest`, `RankMathTest`, `GoneTest`, `MaintenanceTest`, `LoginTest`, `PageCopyTest`, `QuranTextTest`. The `has_term()` stub now answers only for `murtadd_doubt_category`, so a guard querying the wrong taxonomy fails.

## 1.40.0 — 2026-10-26
Contextual links. In the running text of a single entry, the first mention of a known phrase now links to the page that treats it: another rebuttal or doubt here, or the inner page on one of the six sister sites. No section, widget or list is added; the links sit in the prose.

### Added
- **`inc/contextual-links.php`** — the link pass. Whole-phrase, case-insensitive matching that survives texturised apostrophes and diacritics; each address once a page at its first mention; never the page itself; one added link a paragraph or list item; separate caps for this site (4) and sister sites (3). Never inside links, headings, quotations, figures, captions, citations, code, tables, Arabic, Hebrew, Greek or Syriac text, the verse and ayah boxes, the sources panel or the fatwa disclaimer. Runs on `the_content` (priority 20) and on the two meta-field render paths that bypass it: the doubt short response and the fatwa position summary.
- **`inc/data/network-links.json`** — the phrase map shared byte-for-byte by all seven themes, 404 inner pages and 913 phrases, each phrase owned by exactly one page. Entries for this site resolve through `home_url()`.
- **Theme Options → Contextual links** — switches and caps for each kind, extra phrases that override the map, and slugs that never get links.
- **`.murtadd-ctx-link`** in `main.css` and its `.min` twin: body-link colour, underline set clear of descenders. Sister links look the same; the front end states no relationship.

### Unchanged by design
- **Emotional and Identity doubts get no contextual links,** and none may be a target in the map, following the fear and grief precedent. The counterpart box and the homepage cross-link cards are untouched.

### Tests
- `ContextualLinksTest`: 15 tests (markup, headings, skipped regions including a nested element inside one, first mention, self page, paragraph cap, both caps, diacritics and apostrophes, whole-phrase match, admin override and host refusal, care-first and excluded entries, every own-site map path seeded here, one owner per phrase, seed rebuttals within the caps with text unchanged). Mutation-checked.
- Suite green: 61 tests.

## 1.39.0 — 2026-07-18
Wider frame. The content leaned too heavily on Malaysia, partly because the overlap doctrine made "Malaysian ground" this site's differentiator from the companion site. That rule is replaced: the differentiator is now comparative and forensic, the specific charge answered from its primary evidence and from the law as it stands across Muslim-majority states. Malaysia stays as one example among several and is no longer the frame.

### Changed
- **Rewritten to a comparative frame:** `religion-by-registration` (religion on identity documents in Egypt, Indonesia, and Malaysia; claim generalised), `apostasy-and-the-classical-law` (the legal position across Muslim-majority states, with the reader directed to his own jurisdiction), `is-islam-a-cult` (al-Azhar, national fatwa councils, and the classical heresiographers in place of one country's register), `the-head-cover-and-modesty` (states that compel and states that forbid, both violating 2:256), `what-leaving-actually-costs` (the legal column wherever family law is administered by religion), `the-age-of-aisha` (minimum-age legislation in Morocco, Egypt, and Indonesia), `marital-consent-and-the-rape-charge` (the uneven statutory picture, Turkey to Malaysia).
- **Widened in place:** `where-was-god-when-i-was-hurt` (*'ayb* as a pan-Muslim pattern of silence), `i-was-hurt-and-nobody-helped` (helplines generally, Talian Kasih as one example), `are-all-non-muslims-damned` (neighbours of other faiths anywhere), `polygamy-and-the-condition` (Tunisia, Pakistan, Morocco, Malaysia), `afraid-of-what-happens-if-i-leave` (law varies by country).
- **Sources broadened** on the rewritten entries to cite the other jurisdictions now named in their bodies.
- **Overlap doctrine** in `docs/ssot.md` and the counterpart-field hint updated to match.
- **House-style sweep** across all seed prose: banned filler words and every remaining em dash removed. Two titles lose "actually" (`What the testimonies do`, `What leaving costs`); their slugs are unchanged so live URLs hold.

### Tests
- Suite green: 46 tests, 1,069 assertions.

## 1.38.0 — 2026-07-18
Three rebuttals and one doubt answering arguments circulating among Malaysian apostate bloggers, and the Theodicy topic brought into the seed. The live site already carried a Theodicy topic with nothing filed under it; it now has two entries. 19 doubts, 37 rebuttals.

As with every entry on this site, the arguments are answered as claims. No individual is named, described, or profiled, and the source material was used only to identify which arguments are in circulation.

### Added
- **`where-was-god-when-i-was-hurt`** (Theodicy, Identity) — the argument from childhood abuse. Opens by naming the abuse a crime and refusing to reach for theology first. The forensic move: the complaint is addressed to God, and its evidence concerns men who abused and adults who stayed silent, each of whom Islamic law names as wrongdoers (the hadith on restraining the wrongdoer; 4:75; 4:135). The Malaysian move: the culture of *aib* that keeps abuse inside the family has no warrant in the religion, and the Sexual Offences Against Children Act 2017 exists partly because that silence had to be broken by statute. The general problem of suffering is left to the companion site. Counterpart: `suffering-and-god`.
- **`the-slide-from-hadith-to-nothing`** (Hadith authenticity) — the route from rejecting hadith, to Qur'an-only, to unbelief. The slide proves the hadith are load-bearing: the Qur'an commands obedience to the Messenger (59:7, 4:80, 16:44) and gives no form for the prayer it commands. Notes the Malaysian Qur'an-only position and its 1986 founding text, cited in the sources only. Counterpart: `hadith-reliability`.
- **`the-dare`** (Theodicy) — "I challenged God to punish me and nothing happened." The Qur'an records the same dare from the Makkan opposition (8:32) and answers it; the doctrine of *imhal*, respite (16:61, 35:45, 3:178), was stated before the challenge, so the challenge tests a proposition Islam never held. No counterpart.
- **`i-was-hurt-and-nobody-helped`** (Emotional) — the doubt beneath the first rebuttal, written care-first, with Talian Kasih (15999) named and no theological demand made of the reader. Deliberately unlinked to the rebuttal, following the grief-doubt precedent. Counterpart: `religious-trauma`.
- **`theodicy`** added to the starter topics, matching the live term.

### Tests
- Suite green: 46 tests, 1,069 assertions.

## 1.37.1 — 2026-07-18
Second source on `what-the-testimonies-actually-do`. The peer-reviewed speech-acts study that grounds the entry is now joined by a book-length one making the same finding: Radzuwan Ab Rashid and Azweed Mohamad, *New Media Narratives and Cultural Influence in Malaysia* (Springer, 2020), which extends the pattern from social-media postings to a sustained blog. The entry's argument is unchanged; its footing is stronger. Cited for the genre-level finding only, not as a reading of the individual the book studies.

### Tests
- Suite green: 46 tests, 1,049 assertions.

## 1.37.0 — 2026-07-18
Author profiles, shown and structured from one source. A new **Profiles** settings tab holds a URL for each of 42 platforms and identifier services; every URL entered does two jobs at once — it renders as an icon in the footer, and it is emitted as a `sameAs` on the author in the structured data. A footer link and a schema entry are the same datum and cannot drift apart.

### Added
- **`inc/social.php`** — the registry: 42 platforms, each a label plus a 24×24 `currentColor` glyph, as the single source of truth. Covers the major social networks, the messaging apps already used by the share row, and the scholarly identifiers that matter for an author: ORCID, ISNI, VIAF, OCLC Entities, Academia.edu, Goodreads, Wikidata, WordPress.
- **Profiles settings tab** — an author-name field plus a URL field per platform, each row labelled with its glyph. Blank fields appear nowhere.
- **Footer profile row** — the entered links as a quiet monochrome strip, `rel="me noopener"`, accent on hover.
- **`sameAs` in the structured data** — the same saved URLs attached to the publisher Organization and to a new author **Person** node (`#author`), which is set as the `author` on every rebuttal and doubt Article. With no author name configured the Person node is omitted rather than emitted empty, and authorship falls back to the Organization.
- **Icons vendored** at `assets/icons/social/` (Simple Icons, CC0; identifier and wordmark marks redrawn to match), with the pack README alongside. All 42 SVGs share the `0 0 24 24` viewBox; nine multi-element marks (ORCID, SoundCloud, OCLC Entities, LinkedIn, Scribd, and the redrawn wordmarks) are stored as raw markup with fills normalised to `currentColor`, the rest as single paths.

### Notes
- `rel="me"` on the footer links makes them usable for IndieAuth and identity verification, which is why the row is real anchors rather than icon buttons.
- Two options added: `murtadd_profiles`, `murtadd_author_name`. Both in the manual-removal list in readme.txt.

### Tests
- **`tests/SocialTest.php`** — six invariants: the registry is complete, every glyph renders as a single-viewBox `currentColor` SVG with no leaked hardcoded fill, unknown slugs render empty, the profiles helper filters blanks and orders by the registry, and — the one the feature exists for — the footer link set and the Person `sameAs` set are asserted identical. Suite: 46 tests, 1,049 assertions.

## 1.36.1 — 2026-07-18
Cleaner share-row icons. The WhatsApp and Telegram marks in the share row were hand-drawn approximations; the WhatsApp one in particular read as a rough handset rather than the recognised glyph. Both are now the Simple Icons versions (CC0) from the minimalist social pack, matched to the same 24x24 viewBox and `currentColor` fill as the email and copy icons beside them.

### Changed
- `murtadd_share_icon()` — WhatsApp and Telegram paths replaced with the standard glyphs. Email and copy stay hand-drawn: they are function icons, not brand marks, and did not need replacing.

### Not done, on purpose
- The pack ships 84 icons. None of the other 82 were vendored. This is a private share row for a site with no social accounts of its own, so a library of platform logos would be weight with no use. If a footer with real social or scholarly links (ORCID, Academia, Mastodon) is ever wanted, the pack is the right source and the glyphs are already the correct shape and viewBox to drop in.

### Tests
- Suite green: 40 tests, 535 assertions. No behaviour changed, only two path strings.

## 1.36.0 — 2026-07-18
The overlap doctrine applied across the whole catalogue. Every pairing was audited at the level of argument moves rather than vocabulary, because two pieces on the hijab share the word "hijab" whatever their approach and a keyword score cannot tell duplication from subject matter.

### The audit
Most pairings were already distinct, and for a structural reason worth recording: this site answers specific charges where the companion site surveys a field. Three entries on individual hadith against one article on hadith science; six entries on particular rulings about women against one overview. Different objects, not merely different tones. Eight pairs were genuinely running the same moves, and those are rewritten below.

### Changed
- **`is-islam-a-cult`** now leaves the cult-studies criteria to the companion article and turns on the fact that Malaysia operates a functioning apparatus for designating cults, through state fatwa committees and the *ajaran sesat* determinations. A closed group does not staff an office whose function is to identify closed groups and publish its reasoning.
- **`the-challenge`** cedes the literary argument, which the companion site sets out at four times the length, and takes the narrower forensic question instead: an argument from silence requires the silence to be established, and the objector is asked which treatment of the challenge he has actually read.
- **`what-leaving-actually-costs`** keeps the ledger and adds the Malaysian column the companion article cannot write: religion as a field on an identity document, the syariah court as the route to changing it, and the consequences that follow for marriage, inheritance, and the registration of children. The prospectus promising there is nothing to lose was written in countries where leaving costs a family rather than a legal status.
- **`captives-and-slavery`** drops the trajectory narrative for the legal mechanism: *kaffara*, *mukataba*, *umm al-walad*, and the *zakat* category for freeing necks, set against a supply restricted to captivity in lawful war. Several exits and one narrow entrance is what legal engineering looks like when abolition at a stroke would be disobeyed.
- **`apostasy-and-the-classical-law`** now leads with what actually applies in Malaysia, including that no state imposes death for apostasy, that the hudud enactments were never brought into force, and that the real difficulty is jurisdictional and administrative. The global political history is left to the companion site.
- **`are-all-non-muslims-damned`** takes the question in the form a Malaysian meets it, which is not about exclusivism in the abstract but about the neighbour whose wedding he ate at.
- **`the-head-cover-and-modesty`** keeps the order of the verses and spends the rest on what is actually being resisted here: school regulations, workplace expectation, and enforcement by gossip, none of which the Qur'an legislates.
- **`religion-by-registration`** leads with Article 160 and the argument that Islam is not an ethnicity, and compresses the compulsion material the companion site covers in full.

### Tests
- Suite green: 40 tests, 535 assertions.

## 1.35.0 — 2026-07-18
Pairing a topic with the companion site is not enough; the approach has to differ too. A comparison of the two Aisha treatments showed they ran the same two arguments, source criticism and presentism, with the companion piece doing it at more than double the length. Different tone, same article. This release fixes the exemplar and writes down the rule.

### Changed
- **`the-age-of-aisha` re-angled.** The source-critical and presentism material is compressed to two paragraphs with a pointer across, and the entry is spent on ground the companion site's universal register cannot occupy: how this report is used inside Malaysian child-marriage arguments. The move it makes is that the polemicist and the local apologist read the report identically and differ only on whether to be pleased about it, and that both have mistaken a report of a particular circumstance for a general licence, which no school has ever held. Closes on s. 8 of the Islamic Family Law Act and the syariah court exception, which is a question about judicial discretion under a statute rather than about whether the Qur'an is true.
- **Sources** on that entry replaced to match the new angle: the transmission chain noted and delegated, the Malaysian statute added.

### Added
- **The overlap doctrine, in `docs/ssot.md`** — a table setting direction, posture, use of scripture, ground, length, and ending for each site, and one operative test: if the companion article already makes an argument, do not make it again. Compress, point across, and spend the entry on what only this site can say.
- The same rule, in short, on the counterpart field in the meta box, where it will be read at the moment a pairing is created rather than in a document nobody opens.

### Notes
- The remaining 48 pairings have not been audited against this rule. On the evidence of the structural comparison the forensic and pastoral registers already diverge, but several pairs are likely to be running the same argument twice, and `is-islam-a-cult` is the most probable: both sites work through the cult-studies criteria.

### Tests
- Suite green: 40 tests, 535 assertions.

## 1.34.0 — 2026-07-18
Entry-level relay to the companion site. A catalogue diff found that 35 of this site's 52 entries have a counterpart on Compelling Evidence, in one case under an identical slug. The two treatments do different jobs and both are wanted; what was missing was any way for a reader to get from one to the other.

### Added
- **`ce_slug` on rebuttals and doubts.** A bare article slug, stored in `_murtadd_ce_slug`, editable in the meta box under the claim field. 49 entries mapped.
- **`template-parts/counterpart.php`** renders a quiet aside beneath the sources panel on both single templates: this page answers the charge, the companion site works the question through at length. Renders only when the entry names a counterpart *and* a base URL is set under Theme Options → Linked sites, so clearing the link removes every aside at once.
- **`assets/css/counterpart.css`** and its `.min` twin. Deliberately flatter than the front-page cross-link cards: this is a pointer for a reader who has finished reading, not an advertisement competing with the argument above it.
- The seeder now persists `ce_slug`, so the mapping reaches the database on a fresh install rather than living only in the source file.

### Notes
- The URL is assembled as `{base}/articles/{slug}/`. If the companion site's permalink structure changes, that concatenation in `template-parts/counterpart.php` is the single place to edit.
- The mapping is one-directional. The companion site linking back is work on that side, and until it happens the relay only runs one way.
- `is-islam-a-cult` exists on both sites under the same slug. That is now a deliberate pair rather than an accident, and each links to the other's treatment.

### Tests
- **`tests/CounterpartTest.php`** — four invariants: counterpart slugs are bare and URL-shaped (a stored URL or stray slash produces a broken link that nothing else would catch), the mapping stays populated, an identical slug on both sites is legitimate rather than a loop, and a doubt with a counterpart still carries its own response, because the link is an addition and never a substitute. Suite: 40 tests, 535 assertions.

## 1.33.0 — 2026-07-18
Six rebuttals closing the gaps a survey of the ex-Muslim testimony corpus exposed, and one doubt written to the grievance underneath all of them. 18 doubts, 34 rebuttals.

### Why these six
A thematic pass over roughly 173,000 words of published apostate testimony ranked the arguments actually in circulation. Six recurring ones had no entry on this site at all, including the single most circulated charge against the Prophet in English. The register analysis pointed somewhere else again: the corpus presents as intellectual (reason and evidence are its densest vocabulary) while its emotional substrate is compulsion, fear, and shame, and its stated outcome is liberation by a factor of more than twenty over peace of mind. The doctrinal objections are the vocabulary of the grievance. Conscription is the grievance.

### Added
- **`religion-by-registration`** — Article 160(2), religion assigned by ethnicity, no consent sought. Concedes the legal fact without qualification, then files the objection where it belongs: tying Islam to Malayness would have excluded Bilal, Salman, and Suhayb, and *iman* requires *tasdiq*, which no ministry can issue. The strongest position available to this site, and it is only available by agreeing with the complainant about the facts.
- **`the-age-of-aisha`** — answered in two parts: the transmission is more contested than the polemic allows (the Ibn Ishaq conversion list, al-Tabari on Asma), and even granting the reports, the standard being applied is younger than most of recorded history while the Prophet's own enemies, who attacked everything else, never raised it.
- **`polygamy-and-the-condition`** — 4:3 quoted to its end, alongside 4:129, which states the condition is beyond ordinary capacity. Concedes the abuse and cites Malaysia's own s. 23 permission requirement as Muslims legislating against Muslims.
- **`the-inheritance-shares`** — *faraid* is thirty-odd configurations, not one ratio; the differential share is encumbered by *nafaqah* while hers is absolute. Concedes that the maintenance premise is doing heavy lifting and that jurists are debating it.
- **`dhimmi-status-and-jizya`** — separates the tax, the category, and the abuses, and concedes the third without hedging.
- **`blasphemy-and-the-rushdie-case`** — describes, advocates nothing. The 1989 pronouncement was one state's act, rejected at al-Azhar and by the Islamic Conference on grounds internal to the law, and the Qur'an's own instruction on meeting mockery is to leave the gathering.
- **`i-was-never-asked`** — the doubt beneath the whole corpus, linked to the registration rebuttal.

### Tests
- Suite green: 36 tests, 367 assertions.

## 1.32.0 — 2026-07-18
The first two entries that argue for something rather than against something, and a correction to how this site describes its sibling. 17 doubts, 28 rebuttals.

### Added
- **`the-challenge`** — the *tahaddi*. The Qur'an stakes itself on a falsifiable dare (2:23, 10:38, 11:13, 17:88) issued to the audience best equipped and most motivated to meet it, and the Makkan opposition answered with boycott, exile and war rather than with a sura. Locates the claim where the classical literature located it, in *nazm*, and closes by stating its limits: it is a judgment about language, most available to those with deep Arabic, and it does not reach the prior question of whether God exists at all.
- **`what-leaving-actually-costs`** — answers the prospectus that leaving costs nothing. Runs the ledger in both directions, states plainly that cost has no bearing on truth, and lands on the narrow thing it does establish: the decision is being taken on a false description of what is on the other side.

### Changed
- **The Compelling Evidence cross-link card was inaccurate.** It described that site as "manuscripts, history, prophecy", which is not what it publishes: its lead articles are the ontological argument, the argument from reason, the hard problem of consciousness, fine-tuning, and the moral argument. The card now reads "The questions beneath this one", and the section blurb states the division of labour outright: this site answers what is said against Islam, the linked sites carry the arguments it sits on top of.

### Notes
- Both entries use the existing rebuttal type rather than a new one. A positive-case CPT was considered and rejected at this scale: two entries do not justify a post type, an archive template, and its own tests. If the positive case grows past four or five, revisit — the "what this does not establish" section is doing the work a dedicated field would do, and it is doing it in prose.
- Neither entry's claim was invented to be knocked down. Both are in circulation: that Muslims never make a positive case, and that there is nothing on the other side to lose.

### Tests
- Suite green: 36 tests, 350 assertions. `test_every_rebuttal_has_at_least_one_source` caught the second entry shipping unsourced while making empirical claims about ex-Muslim accounts; the sources were added rather than the test relaxed.

## 1.31.0 — 2026-07-18
Five rebuttals answering arguments currently in circulation among former Malaysian Muslims, and a sharpening pass on three existing entries. 17 doubts, 26 rebuttals.

### Added
Each of these answers an argument documented in Hashmi et al.'s speech-act study of 291 postings, so the claims are current, local, and quoted from the discourse rather than reconstructed from the 2006 book.

- **`the-fragility-argument`** — that a religion needing protection from criticism cannot be true. The inference fails on its own form: adherents' conduct is evidence about adherents. The premise fails too, against a tradition that produced *kalam*, the Ghazali-Ibn Rushd exchange, and an entire science for auditing its own transmission.
- **`the-death-threat-charge`** — the fact is conceded at the outset, then the inference is cut: *hudud* belong to constituted authority alone, vigilantism is itself an offence, and the threatener and the polemicist agree with each other against fourteen centuries of jurists.
- **`marital-consent-and-the-rape-charge`** — the most serious argument in the set. Answered from 4:19, 30:21, 2:187 and the maxim *la darar wa la dirar*; a duty owed has never licensed private force in any chapter of *fiqh*. Concedes what is true: Malaysia's s. 375 exception is real and its scholars are divided, which indicts legislators rather than the Qur'an.
- **`modesty-and-the-morality-police`** — separates the command in 24:31 from a post-1979 state apparatus with no classical counterpart, and declines the premise that uncovering releases a woman into neutrality.
- **`the-movement-and-its-self-description`** — checks "we only want to be left alone" against the coded record: argument, persuasion, warning, directives, and organised outreach to those still inside. Names it a missionary movement, grants its right to exist, and refuses it the double posture.

### Changed
Three entries whose closings trailed into concession now land the verdict last. `captives-and-slavery` dispatches the origination charge on the historical record. `is-islam-a-cult` keeps its concession and then takes the word away from the objector. `the-head-cover-and-modesty` names the substitution of difficulty for truth outright.

### Tests
- Suite green: 36 tests, 346 assertions.

## 1.30.0 — 2026-07-18
Three entries drawn from outside the 2006 book: the first seed content on this site with a source other than its author. 17 doubts, 21 rebuttals.

### Added
- **`what-the-testimonies-actually-do`** (Testimony Patterns) retires a claim from our own side. Older apologetic writing on apostasy, this author's included, described those who left as psychologically insecure and their public statements as projection. A peer-reviewed speech-act analysis of 291 postings by former Malaysian Muslims (Hashmi et al., *3L*, 2022) measured what that discourse actually does: at both sentence and posting level the dominant act is **argument**, ahead of rejection and denial. The insecurity framing is not merely uncharitable, it is inaccurate, and the entry drops it while keeping the study's live distinction between arguing, denying, and asserting without evidence. The entry also states plainly that the study's closing suggestion — that authorities could use such findings to identify this discourse — is not what the citation is for, and that a doubting reader is not a target.
- **`leaving-would-cost-me-my-family`** and **`losing-the-whole-world-not-just-the-belief`** answer the gap a thematic survey of ex-Muslim memoir made obvious: family rupture and loss of belonging dominate those accounts, far ahead of any scriptural or scientific argument, and the seed had no entry for either. The 2006 book waved this away as a handful of relatives being unwelcoming. Both responses hold the cost apart from the truth question rather than resolving one with the other, and neither is linked to a rebuttal, following the precedent set by the fear doubt: a grief is not a claim awaiting refutation.

### Notes
- Both new doubts are filed under Identity and left deliberately unlinked. `afraid-of-what-happens-if-i-leave` remains distinct: that one is about safety, these are about loss.

### Tests
- Suite green: 36 tests, 336 assertions. Word-cap, category, slug-uniqueness, and source invariants hold for the new entries.

## 1.29.0 — 2026-07-18
Recovers the pre-2024 URL structure. The site that stood at this domain until August 2024 was lost when its database was replaced during a compromise, and no dump of the posts survives — but the URL list does, left behind in Rank Math sitemap caches under `wp-content/uploads`. Nine posts and two utility pages, all still requested by search engines and inbound links, all currently returning 404.

### Added
- **`inc/legacy-redirects.php`** — a 301 map from the recovered paths to their closest live equivalent. The definitional cluster (`/definition/`, `/related-definitions/`, `/irtidad-ridda/`, `/who-murtadd/`) lands on the Irtidad page; `/conclusion/` on Start Here; `/privacy-policy/` on Privacy; `/sitemap/` on the front page.
- The four pages that carried named scholars' positions on apostasy (`/yasir-qadhi-on-murtadd/`, `/bilal-philips-on-murtadd/`, `/zakir-naik-on-murtadd/`, `/sheikh-assim-al-hakeem-on-murtadd/`) resolve to the **Apostasy Law** topic archive — the one place on this site that treats the subject as legal history with the contemporary debate stated as open, rather than as one man's ruling reproduced without context. That traffic is arriving with a real question; the topic page is the honest answer to it.

### Notes
- Targets are stored as descriptors (`page`, `topic`, `home`) and resolved at request time, so the map holds no hard-coded URLs and survives a renamed page or term. A target that does not exist is skipped rather than chained into a second 404.
- Runs on `template_redirect` and returns immediately unless the request is already a 404, so normal traffic never touches it. The map is filterable via `murtadd_legacy_redirect_map`.

### Tests
- **`tests/RedirectTest.php`** — six invariants: every recovered URL is mapped, descriptors are well-formed and carry a stated reason, page targets are pages setup actually scaffolds, topic targets exist in the starter taxonomy, the scholar pages land on Apostasy Law, and nothing redirects onto another redirect. Suite: 36 tests, 326 assertions.

## 1.28.0 — 2026-07-18
The manual moved inside the theme. **Appearance → Murtadd → Docs** renders `/docs/*.md` in the admin, so the documentation is readable where the settings are, by whoever is holding the site — no repository checkout, no second copy to fall out of date.

### Added
- **Docs tab** (`inc/docs.php`), listing the seven shipped documents in reading order: Overview, Setup, Content model, Settings, Reference, Upgrading, Changelog. The sidebar is sticky; the reading surface is capped at 72ch and set in the theme's own serif, because this tab exists to be read rather than filled in.
- **Parsedown 1.8.0 vendored** at `vendor/parsedown/` (MIT, Emanuil Rusev), with its licence file alongside. Run in safe mode, output passed through `wp_kses()` with an explicit tag whitelist. Belt and braces: the docs are files on disk, and anything able to rewrite them can already do worse, but a viewer that prints disk content into wp-admin should not be the weak link.
- **`assets/css/docs.css`** and its `.min` twin, scoped under `.murtadd-docs`, inheriting the `--ma-*` variables from the admin skin and the owner's accent.

### Notes
- The manifest in `murtadd_docs_manifest()` is the one place to edit when a document is added or renamed. `murtadd_docs_path()` whitelists the slug against that manifest and then `realpath()`-checks the result against the docs directory, so a manifest edit cannot reach outside it.
- The tab keys in the settings page carry a comment noting that `setup`, `taxonomies`, and now `docs` register no settings group; they render their own markup.

### Tests
- **`tests/DocsTest.php`** — six invariants: every manifest document ships and is readable, manifest entries are complete and URL-safe, unknown slugs resolve to nothing, traversal attempts are refused, rendered output carries no script or `javascript:` payload, and the changelog renders with the current version in it. Suite: 30 tests, 220 assertions.

## 1.27.0 — 2026-07-18
Deployment-safety release, driven by a live-site audit of murtadd.org ahead of the 1.26.x rollout.

### Audited
- **Every live slug verified against the seed.** All 17 rebuttals, all 12 doubts, and both placeholder letters on murtadd.org match the seed slug-for-slug, with category distribution identical (5/4/1/2 across the four doors). Running setup on the live site therefore creates exactly the six new 1.26.x entries and touches nothing else.

### Fixed
- **The seeder can no longer resurrect deleted content.** Its documentation has promised since 1.19.0 that deleting a seeded post is permanent and setup will not undo it — but the skip check was `get_page_by_path()` alone, which lies twice: WordPress renames a trashed post's slug to `{slug}__trashed`, and a deleted post is simply gone, so both read as "missing" and got rebuilt. The failure was about to ship in the live deployment sequence itself, whose two steps are "delete the placeholder letters" and "press Create anything missing." Every slug the seeder creates (or adopts, on installs that predate the record) is now written to the `murtadd_seeded_slugs` option, and a recorded slug is never seeded again.
- **`murtadd_seed_missing_count()` honours the record**, so the Setup tab no longer nags forever about entries an editor removed on purpose.

### Tests
- **`tests/SeedTest.php`** — four new invariants: seeding is idempotent; deleted content stays deleted; the missing-count ignores deliberate deletions; and a pre-record install (the live-site case exactly) is adopted without duplicates, creating only the three genuinely new rebuttals. The bootstrap gains a fake post store so `inc/seed-content.php` runs under test. Suite: 24 tests, 145 assertions.

### Note
- New option written: `murtadd_seeded_slugs`. Added to the manual-removal list in readme.txt.

## 1.26.1 — 2026-07-18
Taxonomy alignment against the live site. An audit of murtadd.org found two topics sitting in the main navigation with nothing filed under them: **Apostasy Law** and **Testimony Patterns** — dead doors, the exact defect class the dashboard gap-widget exists to catch, except these terms were created on the site and the seed never knew about them.

### Changed
- `murtadd_starter_topics()` now carries `apostasy-law` and `testimony-patterns`, named exactly as production names them, so fresh installs and the live site agree.
- `apostasy-and-the-classical-law` and its doubt `the-hadith-about-leaving` are filed under **Apostasy Law**; `the-polemical-echo-chamber` and `websites-confirmed-my-doubts` under **Testimony Patterns** (with Reason and faith as the secondary). Running the seeder on the live site therefore populates both empty topic pages in the same stroke that creates the entries.

## 1.26.0 — 2026-07-18
Second wave of starter content from the source book: 15 doubts, 20 rebuttals. Seeded on activation like everything else; existing installs pick it up from **Theme Options → Setup → Create anything missing**, which never overwrites.

### Added
- **Three rebuttals.** `the-polemical-echo-chamber` (the book's ch. 6.1–6.2 as a single entry on method: confirmation by echo, the anecdote offered as evidence, the inherited polemic); `the-scientific-miracles-genre` (concedes the post-hoc critique of i'jaz 'ilmi openly, citing the internal Muslim criticism, then separates the failed genre from the classical inimitability case); `apostasy-and-the-classical-law` (Hamidullah's confessional-state analysis, the Byzantine parallel, and the live contemporary debate — written as history, advocating nothing, and closing with the line the frightened reader needs first).
- **Three doubts**, each wired to its rebuttal: `websites-confirmed-my-doubts`, `the-miracles-argument-collapsed`, and `the-hadith-about-leaving`. The last carries the scriptural form of the fear question; `afraid-of-what-happens-if-i-leave` is untouched and stays deliberately unlinked.

### Changed
- **`is-islam-a-cult` expanded** with a cult-studies section (Lifton's totalism markers, Hassan's BITE model), two new source rows, and a closing concession: a family or community can behave cultically, and a person who lived that is entitled to the word — for their experience, which the religion does not share. Because the seeder never overwrites, the expanded body reaches fresh installs only; on a live site, paste it into the editor by hand (see UPGRADING).

### Tests
- Suite green against the merged data: 20 tests, 131 assertions. The word-cap, category-coverage, slug-uniqueness, source-presence, and related-link invariants all hold for the new entries.

## 1.25.0 — 2026-07-13
### Added
- **The site icon now appears in Theme Options**, beside the wordmark in the hero. It uses core's Site Icon where one is set, and falls back to the theme's own mark otherwise — the same precedence `inc/site-icon.php` already applies on the front end.

### Fixed
- **The admin hero contained a third hand-written copy of the wordmark.** Georgia fallback, a hardcoded strike, hardcoded colours. `murtadd_the_logo()` was built in 1.8.0 precisely so the mark could not drift, and then a fresh copy was hand-rolled for the admin anyway — the identical mistake that produced the footer-mark drift, made a second time.
- The hero now renders **the component itself**, `murtadd_the_logo( 'on-dark', false )`, in its on-dark variant, exactly as the sidebar does.

### Changed — the root fix
The mark could drift because the admin could not reach the front-end stylesheet. So the shared parts were extracted:

- **`assets/css/tokens.css`** — the `:root` design tokens and the two `@font-face` declarations.
- **`assets/css/logo.css`** — the wordmark component, in full.

Both are now loaded by **the front end and the Theme Options screen**, in the order `tokens → logo → main` and `tokens → logo → admin`. There is exactly one definition of the mark and one definition of the brand colours in the codebase, and both contexts read from them. Hand-writing a fourth copy is now impossible without deleting the component first.

- The owner's accent colour also applies to the admin screen, injected through `wp_add_inline_style` as it is on the front end. Change the accent under Colours and Theme Options changes with the site.
- The logo's `prefers-reduced-motion` rule moved into `logo.css` with the rest of the component. Genuine consumer rules (the footer's `:not(.murtadd-logo)` exclusion, the footer sizing) correctly remain in `main.css`, since they style placements rather than the mark.

## 1.24.1 — 2026-07-13
### Changed
- **Theme Options tabs now run in the order the work is actually done.** Setup scaffolds the site and was sitting last; Colours is the final polish and was the default landing tab. The sequence was backwards.

| | Was | Now |
|---|---|---|
| 01 | Colours | **Setup** — build the site |
| 02 | Cross-links | **Content** — decide what it shows |
| 03 | Content display | **Topics & schools** — file the content |
| 04 | Taxonomies | **Linked sites** — point outward |
| 05 | Setup | **Colours** — make it yours |

- **Labels stopped being jargon.** "Taxonomies" is a WordPress implementation word, not a thing a person has; it is now "Topics & schools", which names what is actually on the tab. "Cross-links" became "Linked sites". "Content display" became "Content".
- The default landing tab is now the first tab rather than a hardcoded `colours`, so it follows the array and cannot fall out of step with it.
- Tabs are numbered `01`–`05` by a CSS counter rather than by markup, so reordering the PHP array renumbers them automatically and the labels never have to carry the sequence.

### Note
- **The tab keys are unchanged and must stay that way.** `settings_fields( 'murtadd_' . $active )` maps each key to its registered settings group; renaming a key silently detaches a tab from its settings. Labels and order are free to change, keys are not. Recorded in `docs/ssot.md`.

## 1.24.0 — 2026-07-13
### Added
- **Theme Options, properly designed.** Appearance → Murtadd has always used the Settings API with five tabs, but it rendered in stock WordPress admin chrome: grey tables and 2011 form rows. `assets/css/admin.css` restyles the whole screen.
- **Gradient hero** carrying the wordmark (strike and full stop intact), a **version badge reading the live `MURTADD_VERSION`**, and three live stat cards: doubts, rebuttals, and content gaps. The gaps card turns amber when the dashboard gap-checker finds anything, so the screen tells you the state of the site before you touch a control.
- **Pill tabs** on a rounded rail, the active tab carrying the accent gradient. **Rounded panel card** with generous spacing, restyled form fields with soft focus rings, gradient primary buttons, and rounded notices.

### Design note
The gradient is confined to the hero, the active tab, and the primary button. Everything else is flat, spacious, and rounded. A gradient on every control would be a 2013 admin theme rather than a 2026 one, and the restraint that governs the front end governs here too. Motion is suppressed under `prefers-reduced-motion`.

### Note
- **The Settings API is untouched.** The markup is still `form-table`, so every field, nonce, sanitiser, and capability check works exactly as before. Only the surface changed. Everything is scoped under `.murtadd-admin`, so nothing leaks into the rest of wp-admin.

## 1.23.0 — 2026-07-13
Ten items: three design, six adapted from Book-WP 3.6.0, one adapted rather than copied.

### The doubts index is now a table
The site murtadd.org answers led with a table of names — people who had left, each row a link, each row a story. That table was the emotional engine of the page; everything else was scaffolding around it. The doubts index now takes the same furniture: solid header strip, alternating rows, every row a link, the accumulating weight of row after row. Where theirs listed people who walked out, this lists questions that were met, each one in the reader's own voice, so that a person scrolling it finds their own sentence already written down and already answered. It closes with a count: *N doubts answered so far. If yours is not here, it is not because it cannot be answered.*

### Added
- **Generated social cards** (`inc/social-card.php`). Every doubt, rebuttal, and fatwa entry gets its own share image, built from content already in the database and served at `/murtadd-card/{id}.svg`. No stock photography: the doubt itself, set as an object. When a page is sent to a friend who is struggling, the doubt arrives in the message thread rather than a bare site icon. A featured image, where one is set, outranks the card.
- **Featured image support** on all four content types — for **documentary** images that earn their place (a folio, a court document, the claim as it actually circulates), never mood photography. Captions render beneath.
- **Reading time** (`inc/analytics.php`), adapted from Book-WP. It reads postmeta, because a doubt's substance is its statement and short response and its `post_content` is empty; counting the editor body would report every doubt as a nought-minute read. "3 min read" tells a frightened reader at 2am that the thing in front of them is finite.
- **Share row** (`inc/share.php`) on doubts and rebuttals. Plain anchor links and inline SVG. No SDKs, no pixels, nothing that phones home — the support notice on this site promises "no preaching, no tracking", and a share widget loading a third-party script would make that a lie on the very page it is printed on. WhatsApp first, since that is how a link actually travels between two people who trust each other.
- **Mini table of contents, reading progress, and back-to-top** — **rebuttals only**. Doubts are capped at a few hundred words by design; giving them a table of contents would advertise a length they must never have.
- **Dashboard content-gap widget** (`inc/dashboard.php`), adapted rather than copied: it flags empty doubt categories, doubts with no rebuttal attached, and rebuttals with no sources filed. Every defect it looks for actually shipped on this site and was caught by a person.
- **`config/lighthouserc.json`** — a performance, accessibility, and SEO budget for CI.

### Tests
- **`tests/`** — a PHPUnit suite with WordPress stubs, modelled on Book-WP's bootstrap. 20 tests, 97 assertions.
- Every assertion corresponds to a defect that actually shipped and was found by a person rather than by the software: a doubt pointing at a rebuttal that did not exist; a doubt category left empty, so one of the four doors on the homepage read "0 responses"; a rebuttal with no sources on a site whose entire promise is that the reader can check; a doubt slug colliding with a category URL and becoming unreachable; the eleven-way routing table that was previously verified by hand.
- The suite was mutation-checked: each historical defect was re-introduced, and the suite caught **4 of 4**. A test that cannot fail is worth nothing.

## 1.22.0 — 2026-07-13
### Added
- **The rail.** Doubt and rebuttal singles gain a sticky right-hand column of framed cards: a scripture box, a route into Start Here, and a recently-added list. The stacked right-hand column is the most recognisable feature of the site this one answers, and it is quoted as a silhouette — same position, same rhythm of panels.
- **The scripture box is the point of the gesture.** The site being answered placed a verse on freedom of belief in that exact position and read it as a licence to leave. The same furniture here answers the other way. Verse text, reference, and visibility are all editable under Appearance → Murtadd → Content display; the choice of rendering is an editorial decision and belongs to the site owner rather than to the theme.
- `assets/css/rail.css`, conditionally enqueued. `template-parts/rail/rail-single.php`.

### Design note
The echo is structural. Execution is current, and the period tells were deliberately not carried across:

- **No serif navigation.** The recognition lives in the panel's position, not in its typeface; a serif nav would read as pastiche.
- **No filled header strips** on the cards. Hairline borders and small tracked labels instead.
- **No olive attribution lines, no centred foot rule, no beveled chrome.**
- The rail is sticky, the grid is fluid, the cards are rounded and generously set, motion respects `prefers-reduced-motion`, and the rail drops below the article under 900px and disappears in print.

Same bones. Different body. Nothing of the original's assets, copy, or markup is reproduced — this is an echo of a layout, not a copy of a site.

### Changed
- Doubt and rebuttal singles widen to a 1060px column to carry the rail. The prose keeps its own measure (68ch) inside the grid, so only the column widens and the reading line does not.

## 1.21.1 — 2026-07-13
### Added
- **Two placeholder letters** (lorem ipsum), seeded with the rest of the starter content, so the Letters archive and single templates render and the layout can be reviewed before any real correspondence is filed. One carries an Editor's note and one does not, exercising both template paths. Each has an outlet, a dateline, and one has a "replying to" line, so every field is visible in situ.
- Both publish immediately and are titled as placeholders. Replace or delete them before announcing the site.
- The Setup tab counts and the activation notice now report letters alongside doubts and rebuttals, and `murtadd_seed_missing_count()` includes them — without which the Setup tab would have reported "all present" while the Letters archive sat empty.

### Note
- Same invariant as everything else the seeder touches: a letter already at that slug is skipped whatever state it is in. Deleting a placeholder is permanent; setup will not resurrect it against you.

## 1.21.0 — 2026-07-13
### Added
- **Letters** — a fourth post type (`murtadd_letter`, `/letters/`) for editorial correspondence and opinion pieces published in other outlets and archived here in their original form.
- Fields: **Published in** (outlet), **Date of original publication** (required; it drives the dateline), **Replying to**, **Link to the original**, and an optional **Editor's note** rendered above the letter in the site's present voice, for framing an archived position for a reader arriving at it today.
- Templates: `archive-murtadd_letter.php`, `single-murtadd_letter.php`, `assets/css/letter.css` (conditionally enqueued). The letter body is inset behind a rule so it reads as a reproduced document rather than as the site speaking now.

### Structural separation
Letters are walled off from the triage architecture on purpose. Doubts, Rebuttals, and Fatwa entries exist to meet a reader who arrived carrying a question; a letter is the author addressing an editor at a fixed moment, often years ago. Presenting the two as though they did the same job would be a category error, and on this site a harmful one. Concretely, Letters:

- do **not** appear on the homepage triage grid;
- carry **no** Doubt Category, so they cannot enter the four doors;
- are **excluded from site search**, so a reader searching their doubt is never handed an archived letter as though it were an answer;
- are **excluded from the main feed**;
- sit **below a divider** in the sidebar, outside the triage spine;
- carry a **mandatory dateline** on every surface, and an archival frame above the text on the single view, stating what the reader is looking at before they read a word of it.

They remain fully public, indexable, and reachable from `/letters/` and from the sidebar. The separation is about not answering a question nobody asked, rather than about hiding anything.

### Note
- The Topic taxonomy is shared, so letters can be filed by topic alongside everything else.
- Rewrite rules flush automatically on first load via the version-stamped flusher; `/letters/` is live immediately.

## 1.20.0 — 2026-07-13
Closes the last content gaps against the source book. Every substantive chapter is now represented: 12 doubts, 17 rebuttals.

### Added
- **"The credential claim"** (ch. 2.1) with its doubt **"I studied it, and I still left"**. The claim is a credential offered to close the argument, and it is testable: a person who knows the religion well describes its rulings accurately. The rulings usually advanced under this credential are each contradicted by the primary sources. What was mastered was a syllabus, often a badly taught one. The failure belongs to the teaching.
- **"Sects and division"** (ch. 6.4b) with its doubt **"Nobody agrees"**. The seventy-three is a Semitic idiom for multiplicity rather than a census; the division was predicted rather than denied; most of what is counted as schism is jurisprudence, and four schools held each other valid for a millennium. The argument also fails its own test, since every tradition and every school of philosophy has fractured comparably.
- **"The visual argument"** (ch. 4), written against the grain of its source chapter. The Emory fMRI finding is verified and holds (Hamann et al., *Nature Neuroscience* 7, 2004): men show stronger amygdala and hypothalamic activation than women to identical stimuli, even when women report greater arousal. Two claims built on top of it do not survive checking and are not published: that the female brain plays no role in arousal (the same study found similar activation across multiple regions, including reward circuitry), and an inference drawn from reporting about brain activity during orgasm, which is a different phenomenon. The rebuttal also argues that the head-cover should not be defended on male biology at all, since that implies men cannot govern their eyes — which contradicts Qur'an 24:30, where the duty is placed on the man first.

### Not built, deliberately
- Ch. 1.1 and 1.2 (apostasy punishment, the Apostasy Bill): excluded.
- Ch. 1.4 and 6.2 (the psychological insecurity of apostates, projection): the ad hominem register, incompatible with a site addressed to the doubting reader rather than to an opponent.
- Ch. 6.1: a 2006 list of websites, now dead links.

## 1.19.1 — 2026-07-13
### Fixed
- **The Start Here page had no stylesheet.** Eight classes emitted by `templates/page-start-here.php` — `murtadd-start-routes`, `murtadd-start-route`, `murtadd-route-num`, `murtadd-route-title`, `murtadd-route-desc`, `murtadd-route-arrow`, `murtadd-start-lede` — matched nothing in any stylesheet. The three routes therefore rendered as bare anchors run together into a single underlined paragraph. This is the page the sidebar's primary call to action points at and the first page a frightened reader sees. `assets/css/start-here.css` supplies the rules, enqueued conditionally on the page template like every other per-view stylesheet.
- Each route is now a numbered door: a large tap target, the reader's own sentence as the title, a plain description beneath, and an arrow that advances on hover. Motion is suppressed under `prefers-reduced-motion`; the arrow drops on narrow screens and in print.
- `murtadd-blog-main` gained `min-width: 0`. Without it a long unbroken string in a post blows the blog grid column out. `murtadd-blog-rail` and `murtadd-category-grid-wrap` were also emitting without rules.

### Process
- Every `murtadd-` class emitted by every template is now checked against the stylesheets before packaging. A template that ships markup with no matching CSS is invisible to lint, to the load harness, and to a page that is never opened during development — which is exactly how this shipped.

## 1.19.0 — 2026-07-13
The full enhancement list from the theme review, plus two new doubts and a rebuttal.

### Fixed
- **Site search can now see the content.** A Doubt's statement and short response are postmeta and its post_content is empty, so WordPress search returned nothing for the site's own core material. `inc/search.php` joins the searchable meta (`_murtadd_doubt_statement`, `_murtadd_short_response`, `_murtadd_claim`, `_murtadd_position_summary`, `_murtadd_scholar_or_body`) into the front-end main search, per term, keeping multi-word AND behaviour, with DISTINCT to prevent duplicate rows.
- The sidebar "Topics" link pointed at `/blog/` — a stray placeholder. It points at the doubts archive.

### Added
- **Meta description, Open Graph, and Twitter cards** (`inc/meta-tags.php`). Descriptions come from the fields the content model already forces: the Doubt statement, the Rebuttal claim, the Fatwa position summary; archives and the front page carry written ones. Skipped on search and 404, which are now `noindex,follow` via `wp_robots`.
- **Current-page state in the sidebar** — `aria-current="page"` plus an `.is-current` treatment on the active section. The nav previously rendered identically on every page.
- **Font preload** for both variable woff2 files, with `crossorigin` (required even same-origin, or the preload is discarded and the font fetched twice). First paint no longer swaps out of Georgia.
- **"More in this category"** on single doubts: three sibling doubts from the same door, so the page stops dead-ending after the rebuttal link.
- **Visible review date** on doubt and rebuttal singles ("Last reviewed" when meaningfully after publication, "Published" otherwise). `dateModified` was in the schema and never shown to a human.
- **Doubts, Rebuttals, and Fatwa entries in the main RSS feed** (`inc/feeds.php`), with meta-driven types rendered into real feed bodies — a Doubt item carries its statement and response, since its post_content is empty. Per-type feeds keep their own scope.
- **Print stylesheet.** Navigation, search, and interactive affordances drop; external source links print their URLs after the anchor text; the support notice deliberately stays.

### Content
- **"The fear itself"** — Identity door, support notice on. The doubt the site was silent about: what happens to me if I leave. Plain about the law in Malaysia, honest that the classical position exists and that scholars have disputed its scope for centuries, and clear that doubting and reading endanger no one. It routes the reader to a person, and it has no rebuttal by design — there is no claim to defeat, only a fear to meet.
- **"The broken ummah"** (Intellectual door) with its rebuttal **"The state of the Muslims"** — the chapter 1 diagnostic material recast: al-Attas on stunted religious education, the fard al-'ayn / fard al-kifayah imbalance, and why a civilisation's nadir indicts its stewardship rather than its creed. Starter content now totals 10 doubts and 14 rebuttals; the Setup tab counts are computed rather than hardcoded.

## 1.18.0 — 2026-07-13
### Changed
- **Doubt category URLs are now `/doubts/intellectual/`** — the `/kind/` segment introduced in 1.7.0 is gone. It was a junk token in the middle of every category URL: it carried no meaning, diluted the keyword path, and added a directory level that told crawlers nothing.
- The reason 1.7.0 avoided this shape was rule collision: a wildcard one segment after `/doubts/` matches both a term and a single doubt. The taxonomy is exactly four locked terms, which is the case explicit rules solve outright. `murtadd_doubt_category_rewrites()` registers the four URLs (plus pagination) as `top` rules ahead of the CPT rules; only those exact slugs match, and everything else under `/doubts/` falls through to the single rule as before.
- The taxonomy's generated rewrite is disabled and a `term_link` filter is the single source of the public shape, so the sidebar, cards, tabs, JSON-LD, and both legacy redirects all emit the clean URL.
- The four slugs are reserved against doubt posts via `wp_unique_post_slug`: a doubt titled "Emotional" gets `emotional-2`, because at `emotional` the term rule would shadow it and the post would be unreachable.

### Redirects
- `/doubts/kind/{slug}/` → 301 → `/doubts/{slug}/`, on `template_redirect`, only when the request is already a 404.
- The older `/doubt-category/{slug}/` redirect resolves through `get_term_link()`, which passes through the new filter — so it lands on the clean URL in a single hop, not a chain.
- Rewrite rules flush automatically on first load via the existing version-stamped flusher.

### Note
- Routing verified against eleven URL shapes: archive, archive pagination, all four term URLs, term pagination, two single doubts, a reserved-slug collision, and the legacy path.
- The 1.7.0 note reserving the slug `kind` is obsolete; the reserved slugs are now the four category names, enforced in code rather than by documentation.

## 1.17.0 — 2026-07-13
### Changed
- **All Markdown documentation moved into `docs/` and renamed to lowercase**: `docs/readme.md`, `docs/changelog.md`, `docs/upgrading.md`, `docs/ssot.md`, alongside the existing `setup.md`, `settings.md`, and `content-model.md`. Four documents were sitting at the theme root in SCREAMING case for no reason, while a `docs/` directory already existed.
- Only files WordPress requires at the theme root remain there: `style.css`, `theme.json`, `functions.php`, `screenshot.png`, `readme.txt`, `license.txt`, and the template hierarchy. `readme.txt` is the WordPress-format readme and stays by convention.
- All cross-references between documents repointed.

### Fixed
- **`ssot.md` had been stranded at version 1.10.0 for six releases.** The version line was being bumped by a string replace with no assertion, so once the header text drifted the replace silently matched nothing and did nothing, release after release. The content sections were current; the stated version was not.
- **`readme.md` was three releases behind**, still describing 1.14.0. It never gained the starter-content seeding, the Setup tab, or the current directory structure. Rewritten.
- Version bumps are now asserted at every one of the six locations that state a version, and a consistency check compares them before packaging. A bump that matches nothing now fails loudly instead of passing quietly, which is the whole reason the drift went unnoticed.

## 1.16.1 — 2026-07-13
### Changed
- **The footer wordmark is no longer a link.** It closes the page; it is a statement rather than a way out of one, and a second link home sitting directly above a footer already full of links is noise. `murtadd_the_logo()` takes a `$linked` argument and renders a `span` when it is false. The span carries `cursor: default` and no hover state, so nothing that is not clickable behaves as though it is.
- **The sidebar rollover now acts on the strike.** It was a colour nudge on the letters, which reads as a rendering glitch rather than as a response to the pointer. On hover the strike carries further through the word (from 3% overhang to 11%) and thickens, on a 0.22s ease. The strike is the mark's whole idea, so it is the part that should answer.
- Hover and focus states are scoped to `a.murtadd-logo`, so they cannot apply to the footer span.
- The rollover is mirrored on `:focus-visible`, so keyboard users get the same signal as pointer users.
- Motion is suppressed under `prefers-reduced-motion`. The state change still reads without the slide.

## 1.16.0 — 2026-07-13
### Changed
- **Hero is now two columns**: headline left, lede right, bottom-aligned so the two blocks close on the same line. The hero shares the 1000px column with the category grid below it, and the lede carried a 560px cap, which protected its line length and left 440px of dead space to its right.
- Removing the cap was not the fix. A single run of prose across 1000px reaches roughly 110 characters per line, well past the 75 where the eye starts losing its place on the return sweep; the space would have been filled at the cost of the sentence. Setting the lede beside the headline uses the full width and keeps both measures short.
- The lede measure is now specified in `ch` (46ch) rather than px, so it stays a measure rather than a guess if the type scale ever changes.
- Headline up to 46px, now that it has a column of its own.
- On narrow screens the hero stacks and the lede runs full width, which is correct: the column is already short there.

### Note
- No template change. `h1` and `p` were already siblings inside `.murtadd-hero`, so the grid applies to the existing markup.

## 1.15.2 — 2026-07-13
### Fixed
- **The footer wordmark was not rendering as the wordmark.** `.murtadd-footer-col a` (specificity 0,1,1) outranked `.murtadd-logo` (0,1,0), so the generic footer link rule forced the mark to 13px, weight 400, sans-serif. It lost its size, its weight and its typeface, and the `--murtadd-logo-size` set for it was being read by a rule that had already lost the cascade. The link rule is now `.murtadd-footer-col a:not(.murtadd-logo)`, so the component keeps ownership of its own typography.
- Footer mark enlarged to 34px, above the sidebar's 24px. It is the closing brand statement and was previously specified smaller than the sidebar mark for no reason.
- The strike was a fixed 2px, so it thinned out visually as the mark grew. It is now `max(2px, 0.075em)` and holds the same weight relative to the letterforms at any size.

## 1.15.1 — 2026-07-13
### Fixed
- **Fatal error on every request (HTTP 500).** `inc/seed-data.php` declared `murtadd_seed_topics()`, a name already taken by `inc/taxonomy-topic.php`, which has seeded the six default topics since 1.0.0. PHP cannot redeclare a function, so the theme killed the site the moment it loaded, front end and admin alike. The starter-content function is renamed `murtadd_starter_topics()`.

### Process
- A name collision of this kind passes `php -l` on every file individually, because each file is syntactically valid; the fatal only exists once both are loaded together. The theme is now checked by loading `functions.php` in full under a WordPress stub, which reproduces the failure the way PHP does and additionally verifies that all 31 registered hook callbacks resolve to real functions.

## 1.15.0 — 2026-07-13
### Added
- **The theme populates its own content on activation.** `inc/seed-content.php` and `inc/seed-data.php` publish 8 Doubts and 13 Rebuttals with their claims, short responses, source lists, topics, doubt categories, and doubt-to-rebuttal cross-links, all wired. Requiring a separate importer plugin to load a site with its own content was a step that should not have existed.
- Rebuttals are inserted before Doubts, because each Doubt stores its related Rebuttal by ID and the target must exist before the pointer can be written. The WXR route could not do this at all, which is why `inc/relations.php` existed; it is retained for anyone importing the XML by hand.
- Setup tab now reports starter-content status and creates anything missing, so a site already running the theme can pull the content in without switching themes.

### Fixed
- **A doubt pointed at a rebuttal that was never written.** "Belief without knowing" referenced the slug `is-islamic-faith-irrational`, which did not exist, so its "go deeper" link would have arrived empty. The rebuttal is now written (chapter 7.1, on faith with an intellectual basis), bringing the total to 13.
- **The Identity category had no doubts in it.** All four doubt categories drive the homepage triage grid, so one of the four doors would have displayed "0 responses" on a freshly seeded site. "Friends, and hell" is about belonging and its topic was already `identity`; it is now filed there.
- Both defects were present in the `murtadd-content.xml` shipped earlier. That file has been regenerated.

### Note
- Seeding obeys the same two invariants as the page scaffolding: it never overwrites (a post already at that slug is skipped whatever state it is in, so editing a seeded Doubt and re-running setup will not undo the edit) and it never destroys.
- New option written: `murtadd_content_seeded`. Added to the manual-removal list in readme.txt.

## 1.14.0 — 2026-07-13
### Changed
- **All build assets moved under `assets/`**: `assets/css/`, `assets/js/`, `assets/fonts/`, `assets/icons/`. This adopts the Book-WP v2.0.0 layout. Only files WordPress requires at the theme root remain there: `style.css`, `theme.json`, `functions.php`, `screenshot.png`, and the template hierarchy.
- The `@font-face` rules use `url('../fonts/…')`, which resolved to the theme root before the move and resolves to `assets/fonts/` after it. The relative path required no change.

### Fixed
- **`rtl.css` had never loaded.** It was added at the theme root in 1.12.0 on the assumption that WordPress picks it up automatically, which it does only for a stylesheet enqueued from `style.css`. This theme never enqueues `style.css`; it enqueues `assets/css/main.css` under its own handle, so the RTL file was inert from the moment it shipped. It is now `assets/css/main-rtl.css`, enqueued explicitly under `is_rtl()`, with a minified counterpart.

## 1.13.0 — 2026-07-13
File structure, aligned with the reference themes.

### Changed
- **`assets/icons/` → `assets/icons/`.** The icons directory was the only asset folder nested under `assets/` while `css/`, `js/`, and `fonts/` sat at the root. Asset directories are now uniformly root-level. (Book-WP v2.0.0 nests assets; UP6 does not. UP6 is the closer analogue, being a Twenty Twenty-Five child theme, and root-level directories are the project standard.)
- **`template-parts/` grouped by kind**, following the Book-WP v2.0.0 pattern of subdivided parts: `cards/`, `rows/`, `notices/`. The directory was flat with five files and no grouping.
- **Page templates moved to `templates/`.** `page-start-here.php` now lives at `templates/page-start-here.php`, matching Book-WP v2.0.0. WordPress scans one directory deep for page templates, so it remains discoverable in the editor.

### Fixed
- Migration for the page-template path. WordPress stores the template in `_wp_page_template`, so a Start Here page assigned the old root-level path would silently fall back to the default page template and lose its three routes. `murtadd_migrate_template_paths()` rewrites the meta once, on `admin_init`.

### Note
- New option written: `murtadd_template_paths_migrated`. Added to the manual-removal list in readme.txt.

## 1.12.0 — 2026-07-13
Brings the theme in line with the conventions established in the UP6 Suara Semasa child theme and the Book-WP v2.0.0 rebuild. Six were missing here.

### Added
- **`theme.json`** (schema v3). The block editor was showing WordPress default colours and type while the front end used the theme's own, so editor-authored content looked nothing like the published page. The palette, type scale, and layout widths now come from one place.
- **`inc/schema.php`** — JSON-LD, following the `schema.php` pattern from UP6. Doubts emit `QAPage` with an `acceptedAnswer`, Rebuttals emit `Article` with the claim under `about` and the sources under `citation`, Fatwa entries emit `Article`, and the front page emits `WebSite` plus `Organization`. Readers reach this site by typing a question into a search box, which makes the markup load-bearing rather than decorative.
- **Minified assets and a `SCRIPT_DEBUG` gate.** Every file in `/css` and `/js` now ships as a source and a `.min` pair. `MURTADD_ASSET_SUFFIX` serves sources when `SCRIPT_DEBUG` is on and minified files otherwise. `main.css` drops from 20.9 KB to 16.1 KB.
- **`languages/murtadd.pot`** — 226 translatable strings. `load_theme_textdomain()` was being called against a directory that did not exist, so the theme was untranslatable in practice.
- **`rtl.css`** — the shell, comment nesting, quote rules, and directional affordances flip correctly under an RTL locale.
- **`license.txt`** — GPLv2, with the OFL attributions for both bundled typefaces.
- **`docs/readme.md`** — completing the document set (SSOT, CHANGELOG, UPGRADING, readme.txt, docs/).

### Security
- `defined( 'ABSPATH' ) || exit;` on every PHP file. 16 of 41 files carried the guard; the remaining 25 did not, so template files were directly reachable if the server ever served them outside WordPress.

## 1.11.1 — 2026-07-13
### Changed
- The sidebar "Start here" call to action is set in caps, with tracking added since caps at that size crowd without it. Applied through `text-transform` rather than by rewriting the string, so the label remains translatable and scripts without letter case are not handed a hardcoded English convention.

## 1.11.0 — 2026-07-13
### Added
- `inc/relations.php`. A WXR import cannot carry a post ID, because WordPress assigns new ones on the way in, so imported Doubts would land with their "related rebuttal" link empty. The import writes the target's slug into a temporary meta key; this resolves each slug into the ID it now refers to, then deletes the temporary key behind it. It runs once and then does nothing.

## 1.10.0 — 2026-07-13
### Changed
- Removed the secondary link list from the bottom of the sidebar. The `footer_site` menu location was being rendered twice — once in `sidebar.php` and once in the front page's footer columns — so About / FAQ / Contact / Privacy appeared twice on the homepage. It also put utility links inside the triage navigation, which is meant to carry one job: routing a doubting reader to the right door.
- Those links now live in the footer only: as a column on the front page, as a centred row in the slim bar on inner pages. That bar renders on every inner page, which is why the sidebar copy existed in the first place; it now covers the case properly.
- `.murtadd-secondary-list` was styled for the green panel (white links at 60% opacity) while also being the class the front-page footer column used. Only source order kept those links legible. The class is now footer-scoped and styled for the ivory ground.
- The slim footer bar stacks on narrow screens instead of crowding three items onto one line.

### Fixed
- Mobile menu toggle. Removing the sidebar block left a dangling selector that absorbed the following rule, so `.murtadd-sidebar.is-open .murtadd-nav` lost its `display: block` and the hamburger menu opened nothing. Caught before release; the rule is restored.

## 1.9.1 — 2026-07-13
### Fixed
- The "Start here" button was absent from the sidebar. It renders only when a page with the slug `start-here` exists, and on a site upgraded in place that page was never created: activation fires on `after_switch_theme`, which a file replacement does not trigger. Two defects, both now fixed.
- **The theme no longer fails silently.** When a required page is missing, an admin notice names which ones and links to the fix. Previously the button simply did not render, with nothing to explain why.

### Added
- **Setup tab** at Appearance → Murtadd. Shows the status of every required page, the footer menu, and the Reading settings, then runs the scaffolding on demand — no need to switch themes away and back. Nonce-protected, `manage_options` only.
- `murtadd_run_setup()` and `murtadd_missing_pages()` in `inc/activation.php`. The activation routine is now reusable rather than fused to the activation hook.

### Note
- Setup remains idempotent: it never overwrites an existing page, an assigned menu, or Reading options already chosen, and it never deletes anything. Pressing the button on a configured site changes nothing.

## 1.9.0 — 2026-07-13
### Added
- Theme activation scaffolding (`inc/activation.php`). Activating the theme now creates the pages the templates assume (home, blog, start-here with its template assigned, irtidad, about, faq, contact, privacy), builds a "Site" menu from About / FAQ / Contact / Privacy and assigns it to the footer location, points Reading at the new Home and Blog pages, and flushes rewrite rules. An admin notice reports exactly what it did. The manual setup checklist is gone.
- **Never overwrites, never destroys.** Existing pages, an already-assigned footer menu, and Reading options you have already chosen are all left untouched. The routine only fills gaps, so re-activating on a live site is safe and changes nothing.

### Fixed
- `after_switch_theme` fires from `after_setup_theme` (priority 99), before `init` — so flushing rewrite rules there would write an incomplete rule set, and the post types would not yet exist. Activation now only raises a flag at that point; the work runs on `init` at priority 90, after the CPTs register, and clears the rewrite stamp so the existing `init`/99 flusher writes the complete set.

### Note
- New pages are published immediately and carry placeholder text. Replace it before announcing the site.
- New option written: `murtadd_needs_setup` (transient flag, deleted as soon as it is consumed). Added to the manual-removal list in readme.txt.

## 1.8.0 — 2026-07-13
### Fixed
- The footer wordmark was a different mark from the sidebar wordmark. It was hand-written as a separate `.murtadd-logo-small` span: no strike at all, an accent-green full stop instead of the teal one, and 19px against the sidebar's 24px. The two had been built twice and drifted.
- `--murtadd-accent-strike` (`#c0392b`) had been sitting in the tokens since 1.0.0 commented "logo strike on light bg" and was never used by anything. The footer mark was always meant to carry a strike on the ivory ground and never got one. It does now.
- The footer "Site" column rendered as a bare heading with nothing under it when no menu is assigned and the About/FAQ/Contact/Privacy pages do not exist. The column is now suppressed when it would be empty.

### Added
- `murtadd_the_logo( $variant )` in `inc/template-tags.php` — one wordmark component, used by both the sidebar and the footer. Variants `on-dark` and `on-light` set the ground; the strike and full stop are constant. A future placement cannot drift again.
- `murtadd_secondary_links()` and `murtadd_has_secondary_links()`, so the secondary-link list is derived once rather than rebuilt in the fallback.

### Changed
- Footer wordmark rendered at 22px (from 19px) via `--murtadd-logo-size`, a step below the sidebar rather than an unrelated size.
- `.murtadd-logo-small` is retired. No references remain.

## 1.7.2 — 2026-07-13
### Fixed
- Logo hover. `.murtadd-logo` set a white colour but declared no `:hover`, so the global `a:hover` rule (a dark green intended for links on the ivory ground) took over and turned the wordmark to mud against the green sidebar. The word now holds white on hover; the strike brightens and the full stop goes white instead, both on a short transition. It was the only link in the sidebar missing its own hover rule.
- Focus ring in the sidebar. `:focus-visible` drew a 2px outline in the accent green — invisible against the accent-green panel. Sidebar focus rings are now white, so keyboard navigation is actually visible there.

## 1.7.1 — 2026-07-13
### Fixed
- Column alignment. The 1.3.1 centering fix capped individual blocks (`.murtadd-archive-header` at 760px) while leaving others at 960px, and centered each one independently — so on the fatwa index the header, the filter form, and the table each landed on a different left edge. Width is now set once per view through `--murtadd-measure` on `.murtadd-main`, and every direct child inherits it. Blocks cannot drift out of alignment, and a new block needs no rule to stay in line. Prose views take 780px; the front page, blog, fatwa index, and school archive take 1000px.
- The fatwa "no positions match" message rendered inside the table grid, producing a tall hollow box under a stranded header row. The table is now suppressed entirely when empty, and the empty state carries a "Clear filters" route. Same fix on the school archive.
- Sidebar search field was truncated ("Search the s…") because the button sat beside it in a 230px column. Field and button now stack.

## 1.7.0 — 2026-07-12
### Changed
- Doubt category URLs move from `/doubt-category/emotional/` to `/doubts/kind/emotional/`. The old slug was WordPress admin vocabulary describing the database object; the new one nests the taxonomy under the content type it belongs to and reads as part of the doubts section. `with_front` is off, so no blog base is prefixed.
- Old `/doubt-category/…` URLs are 301-redirected to the matching term. The hook runs on `template_redirect` and returns immediately unless the request is already a 404, so normal traffic pays nothing.
- Rewrite rules now flush once automatically after a version change, tracked by the `murtadd_rewrite_version` option. No manual trip to Settings → Permalinks after an update.

### Note
- `kind` is now a reserved slug under `/doubts/`. Do not give a doubt post that slug.
- Only the public URL changed; `inc/taxonomy-doubt-category.php` and `template-parts/card-doubt-category.php` keep their filenames, and the taxonomy key remains `murtadd_doubt_category`. No content or term data is touched.

## 1.6.0 — 2026-07-12
### Added
- `screenshot.png` (1200×900, the size WordPress requires). The theme previously showed a grey placeholder in Appearance → Themes. Rendered from the theme's own tokens and real fonts, showing the sidebar, hero, triage grid, and fatwa table.
- Site icon fallback: `assets/icons/` carries the mark as SVG plus 512/192/180/32/16 PNGs and a multi-resolution `favicon.ico`. `inc/site-icon.php` prints the tags on the front end, admin, and login screen, and outputs nothing at all once a core Site Icon is set — core always wins.
- `theme-color` meta tag, following the accent set on the Colours tab.
- The two variable fonts are now shipped: `fonts/SourceSerif4-Variable.woff2` and `fonts/PublicSans-Variable.woff2`, with their OFL licences. `css/main.css` had referenced these paths since 1.0.0 with nothing behind them, so the theme had been silently falling back to Georgia and system-ui.

## 1.5.0 — 2026-07-12
### Added
- Comments support on blog posts and pages. `comments.php` renders a threaded, paginated thread plus the response form; `murtadd_render_comment()` in `inc/template-tags.php` supplies the per-comment markup. Previously comments could be submitted through the REST API and appeared nowhere, since no template called `comments_template()`.
- `css/comments.css` — thread and form styling in the theme's own register (serif comment bodies, accent-tint highlight on author replies, moderation notice). Enqueued only on singular views that have an open form or an existing thread.
- `comment-form` and `comment-list` added to the `html5` theme support declaration, so core emits modern markup rather than the legacy XHTML fallback.
- The core `comment-reply` script enqueues only where threaded replies are actually enabled.

### Changed
- `single.php` and `page.php` call `comments_template()` behind a `comments_open() || get_comments_number()` guard.

## 1.4.0 — 2026-07-12
### Added
- `search.php` — search results were falling through to the bare `index.php`: no header, no query echoed, no result count, no type labels. Results now render as the theme's standard list with a header, a live result count, a refine form, and a routed empty state.
- `searchform.php` — one form shape for both the sidebar and the results page. `get_search_form()` was previously emitting WordPress default markup, which is why the sidebar showed a raw browser-default button.
- `404.php` — previously fell to `index.php` and printed "Nothing found." with no way out. Now carries a header, a search field, and four routes.
- `archive.php` — generic date archives had no template and fell through bare.
- `taxonomy-murtadd_school.php` — school term pages are linked from the sidebar dropdown yet fell to `index.php` as a plain title list. They now reuse the fatwa index table, so `/school/hanafi/` matches `/fatwa/` exactly.
- `template-parts/row-list-entry.php` — one shared mixed-type list row (type chip, title, summary, topic chip). Search, the topic archive, the date archive, and the fallback template all use it.
- `murtadd_type_label()` in `inc/template-tags.php`; the label map was previously inlined in `taxonomy-murtadd_topic.php`.

### Changed
- `index.php` now renders an archive header rather than an unheaded list.
- The `pre_get_posts` sort and filter handler extends to `is_tax( 'murtadd_school' )`, so the school archive honours the default-sort setting instead of falling back to date order.
- Doubt rows in mixed lists show the doubt statement in quotation marks, matching the doubt archive.

## 1.3.1 — 2026-07-12
### Fixed
- Content column now centers in the main area (`width: 100%; max-width: 960px; margin-inline: auto`). Previously the 760px column pinned to the left edge, leaving a large dead zone on wide monitors.
- Footer columns no longer escape the content width (`max-width: 100%` removed); Browse/Topics/Site now align with the column above them.
- Prose views (singles, pages, archives, Start Here) keep a 760px reading measure inside the centered column; grid-bearing sections (hero, category grid, cross-links, footer columns, blog layout) share the 960px cap.
- Sidebar search submit button is now styled to match the input; previously it rendered as an unstyled browser-default button.
- Doubt category grid and category tabs render in triage order — Intellectual, Scriptural, Emotional, Identity — via `murtadd_doubt_categories_ordered()`. `get_terms()` alphabetical order had scrambled the 01–04 numbering.
- Slim footer bar padding restructured so its full-bleed background survives the centered column.

## 1.3.0 — 2026-07-12
### Fixed
- Removed `uninstall.php`. WordPress never executes it for themes, and readme.txt claimed options were removed on uninstall when they were not. Data-removal steps are now documented honestly in readme.txt and docs/ssot.md.
- Moved `murtadd_secondary_fallback()` out of `sidebar.php` into `inc/template-tags.php`. The function is used by both the sidebar and the front-page footer; defining it inside a template made the front page depend on the sidebar having loaded first.
- Sanitized the `tab` query argument in the settings screen before it is used as an array key.
- readme.txt stable tag was stuck at 1.1.0 while the theme was at 1.2.0; the two now move together.

### Added
- `/fonts/` directory with a README naming the two required woff2 files and their sources. `css/main.css` already referenced `../fonts/`; the directory now exists.
- docs/ssot.md, docs/changelog.md, docs/upgrading.md, and `docs/` (setup, settings, content model).

## 1.2.0
- Fatwa archive GET filters and sorting via `pre_get_posts`; works with JS disabled.
- Live WCAG contrast check on the Colours tab.
- Editor word count with soft cap on the doubt short response.

## 1.1.0
- Tabbed settings screen (Colours, Cross-links, Content display, Taxonomies).
- Sources repeater on rebuttals.

## 1.0.0
- Initial structure: three CPTs, three taxonomies with seed terms, two-column shell, full classic template set.
