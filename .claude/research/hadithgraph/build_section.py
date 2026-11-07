import sys, html, json
sys.path.insert(0, '.')
import isnad_data as d

def row(grp, idx, r, notes):
    coll, no, g, names = r
    chain = ' عن '.join(names)
    note = notes.get(idx, '')
    grade = d.GRADE[g]
    extra = f'<br><small>{html.escape(note)}</small>' if note else ''
    return (f'<tr><td>{html.escape(d.COLL[coll])} {no}</td>'
            f'<td><span lang="ar" dir="rtl">{html.escape(chain)}</span>{extra}</td>'
            f'<td>{html.escape(grade)}</td></tr>')

def table(grp, rows, notes, label):
    body = '\n'.join(row(grp, i, r, notes) for i, r in enumerate(rows))
    return ('<div class="murtadd-table murtadd-isnad-table" tabindex="0" role="region" aria-label="' + html.escape(label, quote=True) + '"><table>\n<thead><tr><th scope="col">Collection and number</th>'
            '<th scope="col">Chain, from the collector&#8217;s teacher to the Companion</th>'
            '<th scope="col">Grade printed</th></tr></thead>\n<tbody>\n' + body + '\n</tbody></table></div>')

nA, nB, nC, nD = len(d.A), len(d.B), len(d.C), len(d.D)
total = nA + nB + nC + nD
ayyub = sum('أيوب' in r[3] for r in d.A)
qat = sum('قتادة' in r[3] for r in d.A)
ikr = sum('عكرمة' in r[3] for r in d.A)
anas = sum(any(n.startswith('أنس') for n in r[3]) for r in d.A)
amash = sum(any('الأعمش' in n for n in r[3]) for r in d.B)
assert (ayyub, qat, ikr, anas, amash) == (14, 6, 15, 4, 21) and total == 62, (ayyub, qat, ikr, anas, amash, total)

H = []
H.append('<h2>The chains of narration</h2>')
H.append(f'<p>A report is only as strong as the line of people who carried it. This section sets out every chain that the standard indexes of the ten main collections list for the reports on leaving Islam: {nA} for the saying "whoever changes his religion, kill him", {nB} for Ibn Mas\'ud\'s three grounds, {nC} for the report from \'Uthman and {nD} for the report from \'A\'isha, {total} in all. They are taken from the printed index of the ten collections on shamela.ws, which lists each chain under its hadith with the collection and number, and the grade that al-Albani gives where he gives one. Numbers are as the compiler prints them, and editions can number differently. Each chain below runs from the collector\'s teacher back to the Companion, or to the Successor where the chain is mursal.</p>')
H.append('<figure class="murtadd-isnad-figure"><img src="{{theme:/assets/images/isnad-leaving-islam.svg}}" width="1180" height="978" loading="lazy" alt="Two chains drawn as trees. Left: the Prophet, Ibn Abbas, Ikrima, Ayyub, then three branches ending at al-Bukhari 3017 and 6922 and Abu Dawud 4351. Right: the Prophet, Ibn Masud, Masruq, Abd Allah ibn Murra, al-A\'mash, then branches ending at al-Bukhari 6878 and Muslim 1676. Each narrator carries a mark for what the biographical dictionaries record about them."><figcaption>Two of the routes, drawn out. The marks record what the biographical dictionaries say about each narrator, taken from the Hadith Transmitters Encyclopedia.</figcaption></figure>')
H.append('<h3>"Whoever changes his religion, kill him" (Ibn \'Abbas)</h3>')
H.append(table('A', d.A, d.A_NOTES, 'Chains: whoever changes his religion, kill him'))
H.append('<h3>The three grounds (Ibn Mas\'ud)</h3>')
H.append(table('B', d.B, d.B_NOTES, 'Chains: the three grounds, Ibn Mas\'ud'))
H.append('<h3>The report from \'Uthman</h3>')
H.append(table('C', d.C, d.C_NOTES, 'Chains: the report from \'Uthman'))
H.append('<h3>The report from \'A\'isha</h3>')
H.append(table('D', d.D, d.D_NOTES, 'Chains: the report from \'A\'isha'))

H.append('<h3>What the biographical dictionaries say about the narrators</h3>')
H.append('<p>The Hadith Transmitters Encyclopedia gathers the entries of the classical dictionaries under each narrator. The lines below are the ones this guide was able to read there. Where no verdict line was retrieved, the guide says so and leaves the narrator unmarked.</p>')
V = [
 ("'Ikrima, freedman of Ibn 'Abbas", "Disputed. The entries collect both praise and criticism, and the dictionaries differ on how far to rely on him.", "Ibn 'Abbas route, 15 of 20 chains"),
 ("Qatada ibn Di'ama", "Called the hafiz by al-Dhahabi. Abu 'Amr ibn al-'Ala' said he was never faulted for anything except that he reported everything he heard.", "6 of 20 chains"),
 ("'Abd Allah ibn Murra", "Ibn Ma'in: reliable.", "All 21 Ibn Mas'ud chains"),
 ("Abu Umama ibn Sahl", "Ibn Sa'd: reliable, with much hadith. Al-'Ijli: Madinan, a Successor, reliable. Ibn 'Abd al-Barr: a jurist, reliable and firm. Born in the Prophet's lifetime without hearing from him, according to al-Baghawi.", "'Uthman chains"),
 ("Shu'ba ibn al-Hajjaj", "Ibn Sa'd: reliable, trustworthy, firm, and a proof. Al-'Ijli: reliable and devout, though he sometimes erred in names.", "Ibn Mas'ud chains"),
 ("Ibrahim ibn Tahman", "Ahmad: reliable in hadith. Ibn Ma'in and al-'Ijli: no harm in him. He was also charged with irja', and Ibn al-Jawzi lists a weak verdict on him.", "'A'isha chains"),
]
rows = '\n'.join(f'<tr><td>{html.escape(a)}</td><td>{html.escape(b)}</td><td>{html.escape(c)}</td></tr>' for a, b, c in V)
H.append('<div class="murtadd-table" tabindex="0" role="region" aria-label="What the dictionaries say about the narrators"><table>\n<thead><tr><th scope="col">Narrator</th><th scope="col">What the entries say</th><th scope="col">Where he stands</th></tr></thead>\n<tbody>\n' + rows + '\n</tbody></table></div>')
H.append('<p>The encyclopedia returned no verdict line in this pass for Ayyub, al-A\'mash, Masruq, Abu Ishaq al-Sabi\'i, \'Amr ibn Ghalib, \'Abd al-\'Aziz ibn Rufay\', \'Ubayd ibn \'Umayr or Yahya ibn Sa\'id al-Ansari. This guide records nothing for them. A reader who wants the full picture should open each entry and read the dictionaries directly.</p>')

H.append('<h3>What the chains show</h3>')
H.append('<ul>')
H.append(f'<li>The "kill him" saying has {nA} chains, but they narrow to one Companion and two routes. {ayyub} pass through Ayyub al-Sakhtiyani and {qat} through Qatada. {ikr} pass through \'Ikrima, {anas} come by Anas, and one is mursal from al-Hasan, who names no Companion. Al-Nasa\'i himself says the mursal form is more correct than one of the connected ones.</li>')
H.append(f'<li>All {nB} chains for the three grounds pass through al-A\'mash, \'Abd Allah ibn Murra and Masruq, from Ibn Mas\'ud. Both al-Bukhari and Muslim print it.</li>')
H.append(f'<li>The \'Uthman report is set during the siege of his house, and most of its {nC} chains pass through Abu Umama ibn Sahl. The \'A\'isha report passes through \'Amr ibn Ghalib or \'Ubayd ibn \'Umayr, and one of its printed chains is graded weak and mawquf.</li>')
H.append('<li>The two reports that carry a third case differ in wording. Ibn Mas\'ud has the one who leaves the religion and abandons the community. \'Uthman has the one who disbelieves after his belief. \'A\'isha has the one who goes out from Islam and fights God and His Messenger.</li>')
H.append('</ul>')
H.append('<p>These chains answer the question whether the Prophet said it. For the reports from Ibn Mas\'ud they answer it firmly, and this guide does not contest it. The chains do not answer the questions that decide the ruling: whether the saying is general or tied to those who took up arms and left the community, as the wording of \'A\'isha\'s report and the setting of \'Uthman\'s suggest, and how it sits beside the Qur\'anic verses that set no worldly penalty. Those are questions of meaning, and the next sections take them up.</p>')
open('section.html', 'w', encoding='utf-8').write('\n\n'.join(H))
print(len('\n'.join(H)), 'chars')
