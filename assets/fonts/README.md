# Fonts

The fonts ship with the theme and are self-hosted; `assets/css/tokens.css` declares them.

| File | Family | Licence |
|---|---|---|
| `SourceSerif4-Variable.woff2` | Source Serif 4 (weights 200–900) | SIL Open Font License 1.1 — `OFL-SourceSerif4.md` |
| `PublicSans-Variable.woff2` | Public Sans (weights 100–900) | SIL Open Font License 1.1 — `OFL-PublicSans.txt` |
| `kfgqpc-hafs-uthmanic-script.woff2` | KFGQPC HAFS Uthmanic Script, Qur'anic verses in Arabic only | KFGQPC end-user licence, `kfgqpc-hafs-uthmanic-script-LICENSE.txt` |

Source Serif 4 is from Adobe (github.com/adobe-fonts/source-serif); Public Sans is from the U.S. Web Design System (github.com/uswds/public-sans). Both are OFL, so redistribution inside a GPL theme is fine. Keep the licence files alongside the fonts.

The stylesheet still declares Georgia and system-ui fallbacks, so nothing breaks if these files are ever removed.

The Qur'an face is a WOFF2 conversion of the King Fahd Glorious Quran Printing Complex's TrueType file. Its licence forbids modifying the font; the owner chose on 2026-10-26 to serve the conversion anyway, accepting that. The conversion keeps every glyph, the character map and the OpenType layout, and drops the digital signature. See docs/ssot.md, Arabic and the Qur'an face.
