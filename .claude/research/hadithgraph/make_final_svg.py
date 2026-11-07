import html, os, sys
# Output: the path given as the first argument, else the theme figure two levels up from .claude/research/hadithgraph/.
OUT = sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '..', '..', 'assets', 'images', 'isnad-leaving-islam.svg')
W = 1180
BH = 50
Y0 = 70           # tree starts below the headings
nodes, edges = {}, []
def node(k, x, y, name, mark=None, status='', kind='n', w=166):
    nodes[k] = dict(x=x, y=y + Y0, name=name, mark=mark, status=status, kind=kind, w=w)
def edge(a, b): edges.append((a, b))
R = [0, 90, 190, 290, 390, 490, 590, 690]
# left route
node('p1', 300, R[0], 'The Prophet', kind='root')
node('ibA', 300, R[1], 'Ibn ʿAbbās', status='Companion', kind='c')
node('ik', 300, R[2], 'ʿIkrima', 'diamond', 'disputed')
node('ay', 300, R[3], 'Ayyūb al-Sakhtiyānī', 'full', 'praised')
edge('p1', 'ibA'); edge('ibA', 'ik'); edge('ik', 'ay')
cols = [(110, 'Sufyān ibn ʿUyayna', 'full', 'praised', 'ʿAlī ibn al-Madīnī', 'full', 'praised', 'al-Bukhārī 3017'),
        (300, 'Ḥammād ibn Zayd', 'full', 'praised', 'Abū al-Nuʿmān ʿĀrim', 'half', 'truthful; changed late', 'al-Bukhārī 6922'),
        (490, 'Ismāʿīl ibn Ibrāhīm', 'full', 'praised', 'Aḥmad ibn Ḥanbal', 'full', 'praised', 'Abū Dāwūd 4351')]
for i, (x, n1, m1, s1, n2, m2, s2, bk) in enumerate(cols):
    node(f'l{i}a', x, R[4], n1, m1, s1); node(f'l{i}b', x, R[5], n2, m2, s2, w=176 if i == 1 else 166)
    node(f'l{i}c', x, R[6], bk, kind='book')
    edge('ay', f'l{i}a'); edge(f'l{i}a', f'l{i}b'); edge(f'l{i}b', f'l{i}c')
# right route
node('p2', 880, R[0], 'The Prophet', kind='root')
node('ibM', 880, R[1], 'Ibn Masʿūd', status='Companion', kind='c')
node('ms', 880, R[2], 'Masrūq', 'ring', 'not checked here')
node('im', 880, R[3], 'ʿAbd Allāh ibn Murra', 'full', 'praised')
node('am', 880, R[4], 'al-Aʿmash', 'ring', 'not checked here')
edge('p2', 'ibM'); edge('ibM', 'ms'); edge('ms', 'im'); edge('im', 'am')
node('r1a', 772, R[5], 'Ḥafṣ ibn Ghiyāth', 'half', 'praised; memory shifted', w=178)
node('r1b', 772, R[6], 'ʿUmar ibn Ḥafṣ', 'ring', 'not checked here')
node('r1c', 772, R[7], 'al-Bukhārī 6878', kind='book')
node('r2a', 990, R[5], 'Sufyān al-Thawrī', 'half', 'tadlīs noted', w=178)
node('r2b', 990, R[6], 'ʿAbd al-Raḥmān ibn Mahdī', 'full', 'precise (al-Qaṭṭān)', w=190)
node('r2c', 990, R[7], 'Aḥmad ibn Ḥanbal', 'full', 'praised')
node('r2d', 990, R[7] + 100, 'Muslim 1676', kind='book')
edge('am', 'r1a'); edge('r1a', 'r1b'); edge('r1b', 'r1c')
edge('am', 'r2a'); edge('r2a', 'r2b'); edge('r2b', 'r2c'); edge('r2c', 'r2d')
H = Y0 + R[7] + 100 + 40 + 78
out = [f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" role="img" aria-labelledby="t d" font-family="system-ui, -apple-system, Segoe UI, sans-serif">',
 '<title id="t">Two chains of narration for the reports on leaving Islam</title>',
 '<desc id="d">Left: the route Ibn ʿAbbās, ʿIkrima, Ayyūb, to al-Bukhārī 3017 and 6922 and Abū Dāwūd 4351. Right: the route Ibn Masʿūd, Masrūq, ʿAbd Allāh ibn Murra, al-Aʿmash, to al-Bukhārī 6878 and Muslim 1676. Each narrator is marked with what the biographical dictionaries record.</desc>',
 '<style>:root{--fg:#1f2933;--muted:#5b6770;--line:#9aa5ad;--bg:#fff;--accent:#0f6e56}.bg{fill:var(--bg)}.ln{stroke:var(--line);stroke-width:1.4;fill:none}.bx{fill:var(--bg);stroke:var(--fg);stroke-width:1.3}.bk{fill:var(--bg);stroke:var(--accent);stroke-width:1.6}.rt{fill:var(--bg);stroke:var(--line);stroke-width:1.3;stroke-dasharray:4 3}.nm{fill:var(--fg);font-size:13.5px;font-weight:600;text-anchor:middle}.st{fill:var(--muted);font-size:11.5px}.hd{fill:var(--accent);font-size:15px;font-weight:700;text-anchor:middle}.lg{fill:var(--muted);font-size:12px}.mk{fill:var(--fg);stroke:var(--fg);stroke-width:1.2}.mo{fill:none;stroke:var(--fg);stroke-width:1.2}</style>',
 f'<rect class="bg" width="{W}" height="{H}"/>']
def marker(kind, cx, cy):
    if kind == 'full': return f'<circle class="mk" cx="{cx:.1f}" cy="{cy}" r="4.6"/>'
    if kind == 'ring': return f'<circle class="mo" cx="{cx:.1f}" cy="{cy}" r="4.3"/>'
    if kind == 'half': return f'<circle class="mo" cx="{cx:.1f}" cy="{cy}" r="4.6"/><path class="mk" d="M{cx:.1f} {cy-4.6} A4.6 4.6 0 0 0 {cx:.1f} {cy+4.6} Z"/>'
    if kind == 'diamond': return f'<path class="mk" d="M{cx:.1f} {cy-5.6} L{cx+5.6:.1f} {cy} L{cx:.1f} {cy+5.6} L{cx-5.6:.1f} {cy} Z"/>'
    return ''
def box(n):
    w = n['w']; x = n['x'] - w / 2; y = n['y']; h = BH if (n['status']) else 40
    cls = {'book': 'bk', 'root': 'rt', 'c': 'rt', 'n': 'bx'}[n['kind']]
    s = f'<rect class="{cls}" x="{x:.0f}" y="{y}" width="{w}" height="{h}" rx="7"/>'
    s += f'<text class="nm" x="{n["x"]}" y="{y + (20 if n["status"] else 25)}">{html.escape(n["name"])}</text>'
    if n['status']:
        tw = len(n['status']) * 6.0
        if n['mark']:
            s += marker(n['mark'], n['x'] - tw / 2 - 2, y + 34)
            s += f'<text class="st" x="{n["x"] - tw / 2 + 11:.1f}" y="{y + 38}">{html.escape(n["status"])}</text>'
        else:
            s += f'<text class="st" x="{n["x"] - tw / 2:.1f}" y="{y + 38}">{html.escape(n["status"])}</text>'
    return s
def bottom(n): return n['y'] + (BH if n['status'] else 40)
for a, b in edges:
    A, B = nodes[a], nodes[b]; x1, y1, x2, y2 = A['x'], bottom(A), B['x'], B['y']; my = (y1 + y2) / 2
    out.append(f'<path class="ln" d="M{x1} {y1} C{x1} {my} {x2} {my} {x2} {y2}"/>')
# second route for the saying: through Qatada (6 of 20 chains), drawn as a strip under the left tree
node('q0', 100, R[7], 'Ibn ʿAbbās', status='Companion', kind='c', w=150)
node('q1', 300, R[7], 'ʿIkrima or Anas', 'ring', 'two routes', w=150)
node('q2', 500, R[7], 'Qatāda ibn Diʿāma', 'full', 'called the hāfiẓ', w=170)
node('q3', 500, R[7] + 80, 'al-Nasāʾī, Aḥmad, Ibn Ḥibbān', kind='book', w=230)
for n in nodes.values(): out.append(box(n))
def hlink(a, b):
    A, B = nodes[a], nodes[b]; y = A['y'] + (BH if A['status'] else 40) / 2
    out.append(f'<path class="ln" d="M{A["x"] + A["w"] / 2} {y} L{B["x"] - B["w"] / 2} {y}"/>')
hlink('q0', 'q1'); hlink('q1', 'q2')
out.append(f'<path class="ln" d="M500 {nodes["q2"]["y"] + BH} L500 {nodes["q3"]["y"]}"/>')
out.append('<text class="lg" x="30" y="' + str(nodes["q0"]["y"] - 10) + '">Second route for the same saying</text>')
out.append('<text class="hd" x="300" y="30">The wording "whoever changes his religion, kill him"</text>')
out.append('<text class="hd" x="880" y="30">The report of the three grounds (Ibn Masʿūd)</text>')
ly = H - 52
x = 30
for kind, label in [('full', 'praised in the dictionaries'), ('half', 'praised, with a point noted'), ('diamond', 'disputed: praise and criticism both recorded'), ('ring', 'not checked in this figure')]:
    out.append(marker(kind, x + 6, ly - 4)); out.append(f'<text class="lg" x="{x + 18}" y="{ly}">{label}</text>'); x += 18 + len(label) * 6.4 + 26
out.append(f'<text class="lg" x="30" y="{H - 34}">The strip under the left tree is the second route for the saying, through Qatāda. Every chain is listed in the tables below.</text>')
out.append(f'<text class="lg" x="30" y="{H - 14}">Marks record what the biographical dictionaries say. They are not a ruling on either report.</text>')
out.append('</svg>')
open(OUT, 'w', encoding='utf-8').write('\n'.join(out))
print('ok', H)
