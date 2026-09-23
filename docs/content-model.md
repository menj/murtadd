# Content model

## Doubt (`murtadd_doubt`, /doubts/)
Fields: doubt statement (renders as the quoted page title), short response (150–400 words, soft cap), related rebuttal (primary "go deeper" target), external deep link (optional, secondary). Taxonomies: Topic, Doubt Category (exactly one of the four). Emotional and Identity doubts show the support notice when enabled.

## Rebuttal (`murtadd_rebuttal`, /rebuttals/)
Fields: the claim (stated fairly, as circulated, no named individuals), claim source type (online commentary / academic / forum / pamphlet / other), sources repeater (citation text required, URL optional), related fatwa, related doubt. Body content is the rebuttal itself.

## Fatwa entry (`murtadd_fatwa`, /fatwa/)
Fields: scholar or body, era (classical/modern), position summary (own words, never verbatim, ~80-word soft cap), citation link to the primary source (required; renders as an outbound button). The archive is the filterable, sortable index; single pages carry the standing disclaimer that positions are summarized, not issued.


## Letter (`murtadd_letter`, /letters/)

Editorial correspondence and opinion pieces published in other outlets, archived in their original form.

Fields: **Published in** (outlet), **Date of original publication** (required — drives the dateline printed on every surface), **Replying to** (the piece being answered), **Link to the original**, **Editor's note** (optional; rendered above the letter in the site's present voice).

Taxonomy: Topic (shared).

**Not** in the triage path: no Doubt Category, absent from the homepage grid, site search, and the main feed, and separated in the sidebar. An archived letter is a record of a position taken at a date, and the templates say so before the reader reaches the text. See `docs/ssot.md`.
