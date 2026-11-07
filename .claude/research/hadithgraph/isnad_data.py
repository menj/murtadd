# Curated chains for the reports on leaving the religion.
# Source: al-Musnad al-Mawduʿi al-Jamiʿ lil-Kutub al-ʿAshara (shamela.ws/book/13285), pages 8144 (the "kill him" saying),
# 38 and 8282 (the "three grounds" saying). Names are Arabic exactly as printed, except that the Companion's name is
# completed where the collection prints only "ʿAbd Allah" (Ibn Masʿud) or "Sufyan" is left as printed.
# Each list runs from the collector's teacher to the Companion (or, for a mursal chain, the Successor).

# Collection labels in the site's house transliteration.
COLL = {
    'kh': 'al-Bukhari', 'm': 'Muslim', 'd': 'Abu Dawud', 't': 'al-Tirmidhi', 's': "al-Nasa'i",
    'j': 'Ibn Majah', 'h': 'Ahmad', 'b': 'Ibn Hibban',
}

IB = 'ابن عباس'
MASUD_TAIL = ['الأعمش', 'عبد الله بن مرة', 'مسروق', 'عبد الله بن مسعود']
MASUD_SH = ['شعبة', 'سليمان (الأعمش)', 'عبد الله بن مرة', 'مسروق', 'عبد الله بن مسعود']

# grade codes: S = sahih, SL = sahih li-ghayrihi, D = daʿif al-isnad mawquf, '' = none printed
A = [  # "whoever changes his religion, kill him" (Ibn ʿAbbas)
    ('kh', '3017', '', ['علي بن عبد الله', 'سفيان', 'أيوب', 'عكرمة', IB]),
    ('kh', '6922', '', ['أبو النعمان محمد بن الفضل', 'حماد بن زيد', 'أيوب', 'عكرمة', IB]),
    ('t', '1458', 'S', ['أحمد بن عبدة الضبي', 'عبد الوهاب الثقفي', 'أيوب', 'عكرمة', IB]),
    ('s', '4059', 'S', ['عمران بن موسى', 'عبد الوارث', 'أيوب', 'عكرمة', IB]),
    ('s', '4060', 'S', ['محمد بن عبد الله بن المبارك', 'أبو هشام', 'وهيب', 'أيوب', 'عكرمة', IB]),
    ('s', '4061', 'S', ['محمود بن غيلان', 'محمد بن بكر', 'ابن جريج', 'إسماعيل', 'معمر', 'أيوب', 'عكرمة', IB]),
    ('s', '4062', 'S', ['هلال بن العلاء', 'إسماعيل بن عبد الله بن زرارة', 'عباد بن العوام', 'سعيد', 'قتادة', 'عكرمة', IB]),
    ('s', '4064', 'S', ['الحسين بن عيسى', 'عبد الصمد', 'هشام', 'قتادة', 'أنس', IB]),
    ('s', '4065', 'S', ['محمد بن المثنى', 'عبد الصمد', 'هشام', 'قتادة', 'أنس', IB]),
    ('d', '4351', 'S', ['أحمد بن محمد بن حنبل', 'إسماعيل بن إبراهيم', 'أيوب', 'عكرمة', IB]),
    ('j', '2535', 'S', ['محمد بن الصباح', 'سفيان بن عيينة', 'أيوب', 'عكرمة', IB]),
    ('h', '1871', '', ['إسماعيل', 'أيوب', 'عكرمة', IB]),
    ('h', '1901', '', ['سفيان', 'أيوب', 'عكرمة', IB]),
    ('h', '2551', '', ['عفان', 'حماد بن زيد', 'أيوب', 'عكرمة', IB]),
    ('h', '2552', '', ['عفان', 'وهيب', 'أيوب', 'عكرمة', IB]),
    ('h', '2966', '', ['عبد الصمد', 'هشام بن أبي عبد الله', 'قتادة', 'أنس', IB]),
    ('b', '4475', 'S', ['أحمد بن الحسن بن عبد الجبار الصوفي', 'يحيى بن معين', 'عبد الصمد بن عبد الوارث', 'هشام', 'قتادة', 'أنس بن مالك', IB]),
    ('b', '4476', 'S', ['المفضل بن محمد بن إبراهيم الجندي', 'علي بن زياد اللحجي', 'أبو قرة', 'ابن جريج', 'إسماعيل ابن علية', 'معمر', 'أيوب', 'عكرمة', IB]),
    ('b', '5606', 'S', ['الحسن بن سفيان', 'محمد بن عبيد بن حساب', 'حماد بن زيد', 'أيوب', 'عكرمة', IB]),
    ('s', '4063', 'SL', ['موسى بن عبد الرحمن', 'محمد بن بشر', 'سعيد', 'قتادة', 'الحسن']),
]
# notes on single chains (key: (coll, no)) shown in the table
A_NOTES = {  # row index -> note
    7: 'Anas reports what Ibn Abbas said.',
    8: "Anas reports that 'Ali burned some idol-worshippers of the Zutt, and what Ibn Abbas said.",
    12: 'The wording given is the prohibition of punishing with fire only.',
    15: "Anas reports that 'Ali burned some idol-worshippers of the Zutt, and what Ibn Abbas said.",
    16: 'Anas reports what Ibn Abbas said.',
    19: "Mursal: al-Hasan, with no Companion. Al-Nasa'i adds that this is more correct than the report of 'Abbad (4062).",
}

B = [  # Ibn Masʿud, "except one of three cases"
    ('kh', '6878', '', ['عمر بن حفص', 'حفص بن غياث (أبوه)'] + MASUD_TAIL),
    ('m', '1676 (25)', '', ['أبو بكر بن أبي شيبة', 'حفص بن غياث، وأبو معاوية، ووكيع'] + MASUD_TAIL),
    ('m', '1676', '', ['ابن نمير', 'عبد الله بن نمير (أبوه)', 'الأعمش'] + MASUD_TAIL[1:]),
    ('m', '1676', '', ['ابن أبي عمر', 'سفيان', 'الأعمش'] + MASUD_TAIL[1:]),
    ('m', '1676', '', ['إسحاق بن إبراهيم، وعلي بن خشرم', 'عيسى بن يونس', 'الأعمش'] + MASUD_TAIL[1:]),
    ('m', '1676 (26)', '', ['أحمد بن حنبل، ومحمد بن المثنى', 'عبد الرحمن بن مهدي', 'سفيان'] + MASUD_TAIL),
    ('m', '1676', '', ['حجاج بن الشاعر، والقاسم بن زكريا', 'عبيد الله بن موسى', 'شيبان', 'الأعمش'] + MASUD_TAIL[1:]),
    ('t', '1402', 'S', ['هناد', 'أبو معاوية'] + MASUD_TAIL),
    ('s', '4721', 'S', ['بشر بن خالد', 'محمد بن جعفر'] + MASUD_SH),
    ('s', '4016', 'S', ['إسحاق بن منصور', 'عبد الرحمن (بن مهدي)', 'سفيان'] + MASUD_TAIL),
    ('d', '4352', 'S', ['عمرو بن عون', 'أبو معاوية'] + MASUD_TAIL),
    ('j', '2534', 'S', ['علي بن محمد، وأبو بكر بن خلاد الباهلي', 'وكيع'] + MASUD_TAIL),
    ('h', '3621', '', ['أبو معاوية'] + MASUD_TAIL),
    ('h', '4065', '', ['أبو معاوية'] + MASUD_TAIL),
    ('h', '4245', '', ['وكيع'] + MASUD_TAIL),
    ('h', '4429', '', ['محمد بن جعفر'] + MASUD_SH),
    ('h', '25475', '', ['عبد الرحمن', 'سفيان'] + MASUD_TAIL),
    ('b', '4407', 'S', ['حاجب بن أركين', 'أحمد بن إبراهيم الدورقي', 'عبد الرحمن بن مهدي', 'سفيان'] + MASUD_TAIL),
    ('b', '4408', 'S', ['أبو يعلى', 'أبو خيثمة', 'محمد بن خازم (أبو معاوية)'] + MASUD_TAIL),
    ('b', '5976', 'S', ['الفضل بن الحباب الجمحي', 'محمد بن كثير العبدي', 'سفيان الثوري'] + MASUD_TAIL),
    ('b', '5977', 'S', ['محمد بن عمر بن يوسف', 'بشر بن خالد', 'محمد بن جعفر'] + MASUD_SH),
]
B_NOTES = {
    2: 'Muslim prints this and the next two rows as three chains that join al-A\'mash, with the words "with this isnad, the like".',
    5: "Muslim adds: al-A'mash said he narrated it to Ibrahim, who narrated from al-Aswad from 'A'isha the like.",
    6: 'Muslim: "with both isnads together, like the report of Sufyan", without the words "by Him besides whom there is no god".',
    7: "Al-Tirmidhi calls Ibn Mas'ud's report hasan sahih and adds that the subject is also reported from 'Uthman, 'A'isha and Ibn Abbas.",
    9: "Adds the same note on Ibrahim, al-Aswad and 'A'isha.",
    17: "Adds the same note on Ibrahim, al-Aswad and 'A'isha.",
}

C = [  # ʿUthman
    ('t', '2158', 'S', ['أحمد بن عبدة الضبي', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل بن حنيف', 'عثمان بن عفان']),
    ('s', '4019', 'S', ['إبراهيم بن يعقوب', 'محمد بن عيسى', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل، وعبد الله بن عامر بن ربيعة', 'عثمان بن عفان']),
    ('s', '4057', 'S', ['أبو الأزهر أحمد بن الأزهر النيسابوري', 'إسحاق بن سليمان الرازي', 'المغيرة بن مسلم', 'مطر الوراق', 'نافع', 'ابن عمر', 'عثمان بن عفان']),
    ('s', '4058', 'S', ['مؤمل بن إهاب', 'عبد الرزاق', 'ابن جريج', 'أبو النضر', 'بسر بن سعيد', 'عثمان بن عفان']),
    ('d', '4502', 'S', ['سليمان بن حرب', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل', 'عثمان بن عفان']),
    ('j', '2533', 'S', ['أحمد بن عبدة', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل بن حنيف', 'عثمان بن عفان']),
    ('h', '437', '', ['سليمان بن حرب، وعفان', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل', 'عثمان بن عفان']),
    ('h', '438', '', ['عبد الله بن أحمد', 'عبيد الله بن عمر القواريري', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل بن حنيف', 'عثمان بن عفان']),
    ('h', '452', '', ['إسحاق بن سليمان', 'المغيرة بن مسلم', 'مطر', 'نافع', 'ابن عمر', 'عثمان بن عفان']),
    ('h', '468', '', ['عفان', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل', 'عثمان بن عفان']),
    ('h', '509', '', ['سليمان بن حرب', 'حماد بن زيد', 'يحيى بن سعيد', 'أبو أمامة بن سهل بن حنيف', 'عثمان بن عفان']),
    ('h', '1402', '', ['يزيد بن عبد ربه', 'الحارث بن عبيدة', 'محمد بن عبد الرحمن بن مجبر', 'عبد الرحمن بن مجبر (أبوه)', 'جده', 'عثمان بن عفان']),
]
C_NOTES = {
    1: "Two Successors report together; the setting is the siege of 'Uthman.",
    11: "The setting is the siege. 'Uthman puts the saying to Talha, who confirms it; the third case is worded 'disbelieves after his belief'.",
}

D = [  # ʿA'isha
    ('s', '4017', 'SL', ['عمرو بن علي', 'يحيى', 'سفيان', 'أبو إسحاق', 'عمرو بن غالب', 'عائشة']),
    ('s', '4018', 'D', ['هلال بن العلاء', 'حسين', 'زهير', 'أبو إسحاق', 'عمرو بن غالب', 'عائشة']),
    ('s', '4048', 'S', ['العباس بن محمد الدوري', 'أبو عامر العقدي', 'إبراهيم بن طهمان', 'عبد العزيز بن رفيع', 'عبيد بن عمير', 'عائشة']),
    ('s', '4743', 'S', ['أحمد بن حفص بن عبد الله', 'حفص بن عبد الله (أبوه)', 'إبراهيم (بن طهمان)', 'عبد العزيز بن رفيع', 'عبيد بن عمير', 'عائشة']),
    ('d', '4353', 'S', ['محمد بن سنان الباهلي', 'إبراهيم بن طهمان', 'عبد العزيز بن رفيع', 'عبيد بن عمير', 'عائشة']),
    ('h', '24304', '', ['ابن نمير', 'يونس بن أبي إسحاق', 'أبو إسحاق', 'عمرو بن غالب', 'عائشة']),
    ('h', '25477', '', ['عبد الرحمن', 'سفيان', 'أبو إسحاق', 'عمرو بن غالب', 'عائشة']),
    ('h', '25700', '', ['وكيع', 'سفيان، وإسرائيل', 'أبو إسحاق', 'عمرو بن غالب', 'عائشة']),
    ('h', '25794', '', ['وكيع', 'سفيان', 'أبو إسحاق', 'عمرو بن غالب', 'عائشة']),
]
D_NOTES = {
    0: 'Third case: a man who disbelieves after his Islam.',
    1: "'A'isha addresses 'Ammar. The printed grade is da'if al-isnad, mawquf.",
    2: 'Third case: a man who goes out from Islam and fights God and His Messenger.',
    3: 'Third case: a man who goes out from Islam and fights God and His Messenger; he is killed, crucified or exiled.',
    4: 'Third case: a man who goes out fighting God and His Messenger; he is killed, crucified or exiled.',
    8: 'Third case: a man who apostatises after his Islam.',
}

GRADE = {'S': 'sahih', 'SL': 'sahih li-ghayrihi', 'D': "da'if al-isnad, mawquf", '': 'none printed'}
