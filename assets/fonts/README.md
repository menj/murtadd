# Fonts

Both variable fonts ship with the theme and are self-hosted; `css/main.css` loads them from here.

| File | Family | Licence |
|---|---|---|
| `SourceSerif4-Variable.woff2` | Source Serif 4 (weights 200–900) | SIL Open Font License 1.1 — `OFL-SourceSerif4.md` |
| `PublicSans-Variable.woff2` | Public Sans (weights 100–900) | SIL Open Font License 1.1 — `OFL-PublicSans.txt` |

Source Serif 4 is from Adobe (github.com/adobe-fonts/source-serif); Public Sans is from the U.S. Web Design System (github.com/uswds/public-sans). Both are OFL, so redistribution inside a GPL theme is fine. Keep the licence files alongside the fonts.

The stylesheet still declares Georgia and system-ui fallbacks, so nothing breaks if these files are ever removed.
