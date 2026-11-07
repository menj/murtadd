# HadithGraph run: the two reports on leaving Islam

Research notes. Nothing here ships with the theme (`.claude/` is export-ignored) and nothing here is site copy.

## What was run

The chains were taken from shamela.ws and entered into https://hawramani.com/hadithgraph/ through a headless browser.

| File | What it holds |
|---|---|
| `chains-ikrima.txt`, `diagram-ikrima.png` | "Whoever changes his religion, kill him": al-Bukhari 3017, al-Bukhari 6922, Abu Dawud 4351. Route: Ibn Abbas, Ikrima, Ayyub al-Sakhtiyani. |
| `chains-masud.txt`, `diagram-masud.png` | The "three grounds" report of Ibn Masud: al-Bukhari 6878, Muslim 1676. Route: Masruq, Abd Allah ibn Murra, al-Amash. |
| `chains-both.txt`, `diagram-both.png` | Both together, with the two Ahmad ibn Hanbal nodes named apart. |

HadithRank, as the tool printed it: Ikrima route 15.94%, Ibn Masud route 6.46%, both together 21.37%.

## Do not quote those scores

- No transmitter grades were entered, and the tool says the score is meaningful only when lower-quality transmitters are given grades below 60%.
- Only three and two chains were entered. Both reports have more routes in other collections.
- The tool merges nodes that share a name. Before the Ahmad node was renamed in the Muslim chain, the combined score read 41.43%, and the Muslim 1676 node hung under the Ikrima branch. That is a drawing artefact, not a finding.
- The two reports are different hadiths with different wording. A combined score is not a score for either.

## What the diagram does show

In the chains entered, the wording "whoever changes his religion, kill him" reaches the collections through one route: Ibn Abbas, Ikrima, Ayyub. The Ibn Masud report is a separate text with a separate route. The site's apostasy entries should keep the two apart.

## What the Hadith Transmitters Encyclopedia says

Source: https://hadithtransmitters.hawramani.com, which compiles entries from 62 biographical dictionaries. It does not grade anyone itself. The lines below are short statements found in those dictionaries, picked by searching each entry for grading words. They are not a ruling, and an entry I did not read in full may say more.

| Transmitter | What the dictionaries record |
|---|---|
| Ikrima, mawla of Ibn Abbas | Disputed. The entry in Ahmad's *al-Jami li-ulum*: Sa'id ibn al-Musayyab told his own freedman not to lie on him "as Ikrima lies on Ibn Abbas"; Ahmad is quoted that he was "among the most learned of people" but was said to hold the Sufri view of the Khawarij; al-Khallal's note says that for Ahmad he is reliable and his hadith is used. Ibn Manzur's abridgement of Ibn Asakir: "an ocean of knowledge", not all take his hadith as proof, most early imams did, some later scholars dropped it. |
| Ayyub al-Sakhtiyani | Ibn Sa'd: reliable, firm in hadith, a proof. Shu'ba: "master of the jurists". Ibn Hajar (*Tabaqat al-mudallisin*): one of the imams, agreed upon as a proof. |
| Sufyan ibn Uyayna | al-Ijli: reliable, firm in hadith; some of the hadith scholars called him the firmest of people in hadith. |
| Hammad ibn Zayd | Ibn Sa'd: reliable, firm, a proof, much hadith. al-Ijli: reliable, firm in hadith. |
| Isma'il ibn Ibrahim (Ibn Ulayya) | Ibn Sa'd: reliable, firm in hadith, a proof. |
| Ali ibn al-Madini | al-Dhahabi, as quoted by Ibn Abd al-Hadi: the hafiz, the imam, one of the luminaries. The entries also record criticism of his conduct, which I did not read in full. |
| Abu al-Nu'man Arim (Muhammad ibn al-Fadl) | Abu Hatim: "truthful, trustworthy"; if Arim narrates to you, close the matter. al-Bukhari (in al-Kalabadhi): Arim changed late in life. |
| Ahmad ibn Hanbal | Ibn Sa'd: reliable, firm, truthful, much hadith. |
| Abd Allah ibn Murra | Yahya ibn Ma'in (in Ibn Abi Hatim): reliable. |
| al-A'mash | Not isolated cleanly. al-Khatib's entry calls him among the best readers and most knowing of inheritance, and the best memorisers of hadith. |
| Masruq | Not isolated cleanly. |
| Hafs ibn Ghiyath | Ibn Sa'd (in Ibn al-Kayyal): reliable, trustworthy, firm, except that he practised tadlis. Burhan al-Din al-Halabi: reliable jurist, relied on by the authors of the six books, his memory changed slightly at the end. |
| Umar ibn Hafs | No entry matched. |
| Sufyan al-Thawri | al-Ka'bi's entry: Yahya al-Qattan withheld judgement on some of his hadith fearing tadlis, "because he was a mudallis". |
| Abd al-Rahman ibn Mahdi | Yahya al-Qattan (in Ibn Abi Hatim): what Ibn Mahdi read to Malik was firmer than what others heard. |

## Reading it

Most of the chain is made of transmitters the dictionaries praise. The route that carries the "kill him" wording passes through Ikrima, about whom the dictionaries record both praise and criticism. al-Bukhari included him, and Ahmad is recorded as relying on him. How much weight that carries is for the scholars of hadith, and a site entry should say that and not pick a side.

Two points to carry into any entry:
- The Ibn Masud report is a different text, through a different route, and the classical ruling on leaving the community does not rest on the Ikrima wording alone.
- The single-route point applies only to the chains entered here. Other collections have more routes, and they have not been drawn.

## How these were made

The page is a browser tool, so the diagrams were captured with the pre-installed headless Chromium, cropped to `#diagramOuterContainer`. The encyclopedia was read through its public search and per-person pages, spaced out, because it answers with a 503 "high traffic" page when asked quickly.

## The clean figure

`chains-leaving-islam.svg` is drawn from the verified chains and the dictionary lines above, and not from the tool's output. `make_figure.py` regenerates it (`python3 make_figure.py` writes the SVG beside it); `chains-leaving-islam-preview.png` is a 2x render.

- Each narrator carries a shape marker, so the meaning does not depend on colour: filled circle for praised, half circle for praised with a point noted, diamond for disputed, ring for not checked in this figure.
- It uses CSS variables with a dark-mode rule, so it can take the theme's colour tokens when placed.
- Three narrators are marked "not checked here" (Masruq, al-A'mash, Umar ibn Hafs) because their verdicts were not collected cleanly. Fill them in before any public use, or keep the marker.
- The figure carries its own caption line. It says it is a document of two chains and not a ruling.

Draft caption for the page that would hold it: "The chains by which two reports on leaving Islam reach the collections named. The marks record what the biographical dictionaries say of each narrator, as quoted in the Hadith Transmitters Encyclopedia. The figure is a document, not a ruling on either report."

Placing it in the theme is a separate change: an image under `assets/images/`, a rule in a stylesheet that loads on that view, a version bump, and the docs. The seeder never rewrites a post that already exists, so a live site would need the new text pasted into the existing entry by hand.
