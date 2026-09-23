# Changelog — Murtadd

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
