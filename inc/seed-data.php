<?php
/**
 * Starter content data.
 *
 * Doubts and Rebuttals covering every substantive chapter of the author's 2006
 * book, recast for this site, plus the second-wave entries: the echo-chamber
 * method, the scientific-miracles audit, and the classical apostasy law as
 * history. Data only; inc/seed-content.php inserts.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Topic terms the starter content needs.
 *
 * NOT murtadd_seed_topics(): that name belongs to inc/taxonomy-topic.php and
 * redeclaring it is fatal.
 *
 * @return array<string,string>
 */
function murtadd_starter_topics() {
	return array(
		'women-in-islam' => 'Women in Islam',
		'head-cover' => 'Head cover',
		'war-and-violence' => 'War and violence',
		'reason-and-faith' => 'Reason and faith',
		'moral-scriptural' => 'Moral scriptural',
		'hadith-authenticity' => 'Hadith authenticity',
		'identity' => 'Identity',
		'apostasy-law' => 'Apostasy Law',
		'testimony-patterns' => 'Testimony Patterns',
		'theodicy' => 'Theodicy',
	);
}


/**
 * Rebuttals. Inserted before Doubts, since each Doubt points at one by ID.
 *
 * @return array<int,array>
 */
function murtadd_seed_rebuttals() {
	return array(
		array(
			'slug'        => 'marriage-requires-consent',
			'title'       => 'Marriage requires consent',
			'ce_slug'     => 'women-in-islam',
			'claim'       => 'Islam permits a father or grandfather to marry a girl or woman to whomever he chooses, without her permission.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Sahih al-Bukhari, hadith no. 7:69 (Khansa bint Khidam al-Ansariya; the Prophet declared the marriage invalid).', 'url' => '' ),
				array( 'citation_text' => 'al-Albani, Sahih Abi Dawud (2096) and Sahih Ibn Majah (1532).', 'url' => '' ),
				array( 'citation_text' => 'Fatima Umar Naseef, Women in Islam: A Discourse in Rights and Obligations (IMMA, 1999), ch. 7, p. 89ff.', 'url' => '' ),
				array( 'citation_text' => 'Rukaiyah Hill Abdulsalam, op. cit., p. 130.', 'url' => '' ),
			),
			'body'        => '<p>The claim is that Islamic law hands a woman over in marriage as a piece of property, at the discretion of a male relative, and that her consent is legally irrelevant. It circulates widely and it is asserted with confidence.</p>
<h3>What the sources establish</h3>
<p>The Prophet invalidated a marriage contracted without the woman\'s consent. Khansa bint Khidam al-Ansariya was given in marriage by her father, disliked the marriage, brought the matter to him, and he declared the marriage void. The report is preserved in al-Bukhari at 7:69.</p>
<p>The case is not isolated. Abu Dawud and Ibn Majah both record that a girl who had not previously married came to the Prophet and told him her father had married her off without her consent. He gave her the choice to uphold the marriage or dissolve it.</p>
<p>The governing standard was stated by the Prophet in general terms: a previously married woman is not to be given in marriage except after consulting her, and a woman who has not married before is not to be given in marriage without her permission.</p>
<h3>What the scholars say</h3>
<p>Forcing a woman to marry without her consent is a violation of Islamic law and a transgression of the Prophet\'s teaching. A Muslim woman cannot be compelled to marry. Widowed and divorced women are free to remarry as they choose once their prescribed waiting period has passed. Where forced marriage has been practised, it has been practised in ages and places where Muslims were ignorant of their own religion.</p>
<h3>Where the claim comes from</h3>
<p>Forced marriage exists. It exists in Muslim families and it is a genuine injury to real people. The move being made here is to take a practice that the sources annul and present it as the practice the sources require. A tradition that voids the marriage is being blamed for the marriage.</p>
<p>The distinction matters most to the person facing the coercion, because the sources are on her side, and she is entitled to know it.</p>',
		),
		array(
			'slug'        => 'quran-4-34-in-context',
			'title'       => 'Qur\'an 4:34 in context',
			'ce_slug'     => 'women-in-islam',
			'claim'       => 'Qur\'an 4:34 gives a husband permission to beat his wife when she does not do what he wants.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'moral-scriptural', 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 4:34.', 'url' => '' ),
				array( 'citation_text' => 'Shibli Zaman, commentary on Sura an-Nisa 4:34, bismikaallahuma.org.', 'url' => 'https://www.bismikaallahuma.org/' ),
			),
			'body'        => '<p>The claim rests almost entirely on a English rendering of a single word, presented without the sequence it belongs to and without the practice that defines it.</p>
<h3>The verse as a sequence</h3>
<p>Rendered according to the Prophet\'s own explanation, how his Companions understood it, and how the jurists ruled on it, the passage addresses a husband confronting <em>nushuz</em> (insolence, a serious breach of the marital bond) and sets out an ordered response: first, counsel her; second, withdraw from the shared bed; and lastly, the third measure. The verse then closes by forbidding hostility if she seeks reconciliation, and by naming God as Exalted and Great.</p>
<p>The architecture is a ladder of de-escalation with an exit at every rung, and its stated destination is reconciliation. A reading that isolates the third rung has discarded the verse to quote it.</p>
<h3>The third measure</h3>
<p>The jurists who worked directly from the Prophet\'s practice constrained the third measure severely: it may leave no mark, cause no pain, and avoid the face. It is symbolic. The man who delivered this verse never struck a woman. A husband who beats his wife is not applying Qur\'an 4:34. He is contradicting the only person qualified to explain it.</p>
<h3>The method being used</h3>
<p>A verse can be made to carry a heinous meaning by anyone willing to cut it from its context and set the community\'s own understanding aside. That is a dishonest polemic. It proves nothing about the text, and it is available against any scripture in any language.</p>
<p>Men have used this verse to excuse violence. They were wrong, and the tradition gives every tool required to say so.</p>',
		),
		array(
			'slug'        => 'women-as-witnesses',
			'title'       => 'Women as witnesses',
			'ce_slug'     => 'women-in-islam',
			'claim'       => 'Women cannot serve as witnesses in a Shari\'ah court, and where they are permitted, a woman\'s testimony counts for half of a man\'s.',
			'source_type' => 'forum',
			'topics'      => array( 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 2:282.', 'url' => '' ),
				array( 'citation_text' => 'Fatima Umar Naseef, Women in Islam: A Discourse in Rights and Obligations (IMMA, 1999), p. 139.', 'url' => '' ),
			),
			'body'        => '<p>Two claims travel together here, and they undercut each other. The first says women cannot testify at all. The second says their testimony is halved. The verse cited in support of both appoints women as witnesses.</p>
<h3>The verse</h3>
<p>Qur\'an 2:282 concerns the recording of a debt. It directs the parties to call in two male witnesses, and if two men cannot be found, then one man and two women <strong>whom you judge fit to act as witnesses</strong>, so that if one of them errs, the other can remind her.</p>
<p>The claim that women cannot be witnesses is refuted by the text that is produced to prove it. The verse names them, qualifies them, and assigns them a function.</p>
<h3>Scope</h3>
<p>The arrangement in 2:282 is specific. Its clear text addresses economic affairs, and it extends to criminal cases where <em>hudud</em> (fixed prescribed penalties) apply. It is not a general theory of women\'s credibility, and it was never treated as one.</p>
<p>The scope cuts the other way as well. In affairs concerning women exclusively, such as pregnancy, birth, physical defect, and puberty, the evidence of a single woman is sufficient. A rule that discounted a woman\'s word by half could not produce that outcome.</p>
<h3>What the claim omits</h3>
<p>The stated rationale in the verse is reminding, in a commercial setting from which women were customarily excluded, concerning transactions they had little occasion to handle. Whether that arrangement should be applied to a contemporary courtroom, and how, is a live question that Muslim jurists discuss.</p>
<p>That is a real conversation. It cannot be had by anyone who has misdescribed the verse before it starts.</p>',
		),
		array(
			'slug'        => 'the-head-cover-and-modesty',
			'title'       => 'The head-cover and modesty',
			'ce_slug'     => 'hijab-male-control-or-divine-command',
			'claim'       => 'Women are forced to cover while men are not, which shows that Islam places the burden of male desire on women.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'head-cover', 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 24:31.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 24:30.', 'url' => '' ),
				array( 'citation_text' => 'Sahih Muslim, hadith no. 3960; Sahih al-Bukhari, no. 2285 (the first glance is excused; the second is not).', 'url' => '' ),
				array( 'citation_text' => 'France, Law No. 2004-228 on conspicuous religious symbols in state schools.', 'url' => '' ),
			),
			'body'        => '<p>The companion site answers the male-control argument on its merits and sets out the theology of <em>haya</em> (modesty). This entry takes up what the objection usually turns out to be about when it is examined, which is rarely the verse itself.</p>
<h3>The order of the verses</h3>
<p>This point must not be skipped. The instruction to lower the gaze is given to the believing men in 24:30, before the women are addressed at all in 24:31. Modesty is legislated as a shared obligation with the burden placed first on the man. Any account of the head-cover that begins with women has begun in the wrong verse.</p>
<h3>States that compel and states that forbid</h3>
<p>Look at how the garment has been treated by governments and the pattern is instructive. Some states have compelled it through morality police. Others have banned it from schools and public offices, France in its state schools since 2004 among them. Both treat a woman\'s head as a matter for the state, and both violate the same principle from opposite directions: an act of worship performed because the state requires it, or abandoned because the state forbids it, is no longer the act the verse commands. The Qur\'an states that there is no compulsion in religion (2:256), and that applies to the policeman in either uniform.</p>
<h3>What is being resisted</h3>
<p>For many women the objection is to what arrives attached to the garment: family pressure, school rules, workplace expectation, the social cost for the girl who does not and the credit for the girl who does. None of that is revelation, and all of it is felt long before the verse is understood. A community that enforces a religious obligation through gossip has substituted a social mechanism for a spiritual one and taught the girl that her audience is her neighbours. The verse names God as the reason.</p>
<h3>The remaining objection</h3>
<p>Strip that away and something remains: a woman who understands the command, knows it is addressed to her by her Lord, and does not want to keep it. That position deserves better than a sermon. It is also no longer an argument that the command is unjust or invented by men. It is difficulty, and difficulty is what obligations are made of.</p>',
		),
		array(
			'slug'        => 'islam-and-terrorism',
			'title'       => 'Islam and terrorism',
			'ce_slug'     => 'muhammad-and-warfare',
			'claim'       => 'Islam is a violent religion, and the attacks carried out in its name are the proof.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'war-and-violence' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 5:32 (whoever kills one innocent soul has killed all humanity).', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 2:109, 3:159, 5:8 (on forbearance and justice toward opponents).', 'url' => '' ),
				array( 'citation_text' => 'Council on American-Islamic Relations, statements condemning the attacks of 11 September 2001.', 'url' => '' ),
			),
			'body'        => '<p>The claim is made by two constituencies who agree about the reading and disagree only about whether to celebrate it: the polemicist, and the extremist he is describing.</p>
<h3>The text</h3>
<p>Qur\'an 5:32 states that whoever has killed one innocent soul has killed all humanity, and whoever has saved one soul has saved all humanity. The Prophet explicitly forbade the killing of non-combatants, of women, and of children. The Qur\'an instructs Muslims toward forbearance and justice even toward those who have wronged them.</p>
<p>Muslim organisations across the world condemned the attacks in New York and in London as violations of Islam\'s fundamental principles. The condemnation was ignored, because it was inconvenient to the thesis.</p>
<h3>The conditions on force</h3>
<p>Islamic law permits the use of force under three conditions: where Muslims are persecuted for the practice of their faith, where people are oppressed and silenced in the pursuit of freedom, and where people\'s land is forcibly taken from them. Force operates within a system of law, enforced by a legitimate authority. War is never described as holy. It is described as a necessary instrument for the restoration of peace, and it is fenced accordingly.</p>
<p>None of that authorises a private individual to appoint himself an army and kill civilians on a train.</p>
<h3>The verses in dispute</h3>
<p>The verses produced as proof of perpetual war are verses about war, revealed in specific circumstances, addressed to specific adversaries, and read by the overwhelming majority of Muslims across fourteen centuries as such.</p>
<p>The extremist requires the polemicist\'s reading of these verses to justify himself. The polemicist requires the extremist\'s actions to justify his reading. Each is the other\'s best witness. Neither is a reliable guide to the text.</p>',
		),
		array(
			'slug'        => 'jihad-and-qital',
			'title'       => 'Jihad and qital',
			'ce_slug'     => 'did-islam-spread-by-the-sword',
			'claim'       => 'Jihad and qital cannot be distinguished, since the means and the goal of fighting are the same in both: submission or death.',
			'source_type' => 'academic',
			'topics'      => array( 'war-and-violence' ),
			'sources'     => array(
				array( 'citation_text' => 'Sheikh Sami al-Majid, "Let There Be No Compulsion in Religion", english.islamtoday.net.', 'url' => '' ),
				array( 'citation_text' => 'Hadith: the mujahid is the one who performs jihad against his own self.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 2:256.', 'url' => '' ),
			),
			'body'        => '<p>The claim collapses two distinct terms into one, and the collapse is the whole argument.</p>
<h3>The terms</h3>
<p><em>Jihad</em> derives from <em>jahada</em>, and it means struggle or exertion. It is rendered "holy war" by Orientalists and by the press, and that rendering imports a concept the word does not carry. <em>Qital</em> means fighting, battle, armed combat. The two are related and they are not identical, and Muslims have never treated them as identical.</p>
<h3>The test</h3>
<p>The Prophet said that the mujahid is the one who performs jihad against his own self. If jihad were simply another word for armed combat, that saying would instruct Muslims to conduct a suicide mission against themselves, or to choke themselves to death. It plainly does not, and no Muslim in fourteen centuries has read it that way.</p>
<p>The equation therefore fails on its own terms. A word that can take the self as its object is not a synonym for killing other people.</p>
<h3>Why jihad was permitted</h3>
<p>Jihad may be waged in Islamic law for a number of reasons, and compelling people to accept Islam is not among them. It was first permitted so that Muslims could defend themselves against persecution and against expulsion from their homes.</p>
<p>Qur\'an 2:256 states that there is no compulsion in religion. A doctrine of forced conversion cannot be constructed on a text that forbids compulsion, and the attempt requires that the verse be left out.</p>',
		),
		array(
			'slug'        => 'captives-and-slavery',
			'title'       => 'Captives and slavery',
			'ce_slug'     => 'slavery-in-islamic-sources',
			'claim'       => 'Islam legalised slavery and permitted the keeping of captives, which shows that it is a moral system rooted in a barbaric age.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'moral-scriptural' ),
			'sources'     => array(
				array( 'citation_text' => 'A. D. Ajijola, op. cit., p. 45.', 'url' => '' ),
			),
			'body'        => '<p>The companion site gives the historical trajectory: the world revelation entered, what was changed, and where the direction of travel pointed. This entry does the mechanical question that treatment leaves aside, which is how a legal system extinguishes an institution it has declined to abolish outright, because that is where the objection is answerable in detail rather than in narrative.</p>
<h3>The exits, named</h3>
<p>Islamic law did not denounce slavery and leave it in place. It built drains. <em>Kaffara</em>, expiation, made manumission the prescribed remedy for a broken oath, for a broken fast, for accidental killing, and for the pre-Islamic formula of repudiating a wife, so that the ordinary failures of an ordinary Muslim life discharged into freeing people. <em>Mukataba</em> gave the slave a contractual right to purchase himself, and the Qur\'an instructs masters to grant it where good is known in him and to give from God\'s wealth toward the price (24:33). <em>Umm al-walad</em> made a woman who bore her master\'s child unsaleable and free at his death, and her child free from birth. <em>Zakat</em> has a designated category for freeing necks (9:60).</p>
<p>Set those beside the sources of supply, which were narrowed to captivity in lawful war alone, and the arithmetic is a system with several exits and one restricted entrance. That is what a legal engineer does when abolition at a stroke would be disobeyed.</p>
<h3>What the objection presumes</h3>
<p>It presumes that the seventh century had the option of abolition and declined it. No polity anywhere had that option, and the ones that eventually took it needed another twelve hundred years and a civil war. The charge as levelled is not that Islam moved too slowly; it is that Islam originated the institution and stands condemned by it, which is false on the plain record. Greece, Rome, Persia, and the societies of the Book all practised it before Islam legislated on it.</p>
<h3>The verdict</h3>
<p>A person may still wish the abolition had been immediate and absolute, and that is a serious moral response. It indicts every legal order of the ancient world equally, which is why singling out the one that built the exits is a rhetorical choice rather than a historical finding.</p>',
		),
		array(
			'slug'        => 'is-it-illegal-to-sell-dogs',
			'title'       => 'The sale of dogs',
			'ce_slug'     => 'hadith-reliability',
			'claim'       => 'A hadith forbidding the price of a dog is a high level of nonsense, and shows the arbitrariness of the tradition.',
			'source_type' => 'forum',
			'topics'      => array( 'hadith-authenticity' ),
			'sources'     => array(
				array( 'citation_text' => 'Sahih al-Bukhari, vol. 3, book 34, no. 299.', 'url' => '' ),
			),
			'body'        => '<p>The report is produced as self-evidently absurd, with no argument attached. That is the whole of the objection, and it is worth noticing that no criterion is ever supplied.</p>
<h3>The report</h3>
<p>Al-Bukhari records, in vol. 3, book 34, no. 299, that the Prophet forbade the acceptance of the price of a dog, and also forbade the profession of tattooing and the receiving or giving of <em>riba</em> (usury).</p>
<h3>The coherence</h3>
<p>Islam discourages the keeping of dogs as household pets, on grounds of ritual purity that are consistent across the tradition. A prohibition on trading in dogs follows directly from that discouragement, and it functions to give the discouragement legal effect. Whatever one makes of the underlying position, the ruling is coherent with it. It is not a stray absurdity, and it is not arbitrary.</p>
<h3>The burden</h3>
<p>The objection names no standard by which the report is nonsense. It offers no principle, identifies no contradiction, and points to no internal inconsistency. It asserts, and expects the assertion to do the work.</p>
<p>The word "nonsense" is not an argument. It is a way of skipping one. A reader is entitled to ask by what criterion the ruling has been judged, and to notice that no answer is forthcoming.</p>',
		),
		array(
			'slug'        => 'were-monkeys-stoned-for-adultery',
			'title'       => 'The monkeys report',
			'ce_slug'     => 'hadith-authenticity',
			'claim'       => 'A hadith in al-Bukhari has the Prophet participating in the stoning of a monkey for adultery, which is plainly insane.',
			'source_type' => 'forum',
			'topics'      => array( 'hadith-authenticity' ),
			'sources'     => array(
				array( 'citation_text' => 'Sahih al-Bukhari, vol. 5, book 58, no. 188 (narrated by \'Amru bin Maimun).', 'url' => '' ),
			),
			'body'        => '<p>This report is the showpiece of the genre, and it is misread in a way that the collection itself corrects on the same page.</p>
<h3>The report</h3>
<p>Al-Bukhari, vol. 5, book 58, no. 188. The narrator states: during the pre-Islamic period of ignorance, I saw a she-monkey surrounded by a number of monkeys. They were all stoning it, because it had committed illegal sexual intercourse. I too stoned it along with them.</p>
<h3>Who is speaking</h3>
<p>The speaker is not the Prophet. It is a Companion, \'Amru bin Maimun. He is describing what he witnessed, and what he then believed, <strong>during the age of ignorance</strong>, before Islam reached him.</p>
<p>In the sciences of hadith the report is classified <em>mawquf</em> (stopped), meaning a saying traced to a Companion and not attributed to the Prophet. It is therefore not a prophetic saying, it is not ascribed to him, and it cannot serve as the basis for any ruling in Islam. The Prophet is not present in the account and does not participate in it.</p>
<h3>What the report shows</h3>
<p>It is a record of the superstition of pre-Islamic Arabia, preserved by a man who had lived in it and left it. The same society buried infant daughters alive and performed the <em>tawaf</em> (circumambulation of the Ka\'bah) naked. The report belongs to the catalogue of what Islam displaced.</p>
<p>Anyone who has read as far as the narrator\'s name has the answer. The claim survives only among readers who have not.</p>',
		),
		array(
			'slug'        => 'inheritance-across-faiths',
			'title'       => 'Inheritance across faiths',
			'ce_slug'     => 'hadith-reliability',
			'claim'       => 'A hadith stating that a Muslim may not inherit from a non-Muslim is discriminatory nonsense.',
			'source_type' => 'forum',
			'topics'      => array( 'hadith-authenticity' ),
			'sources'     => array(
				array( 'citation_text' => 'Sahih Muslim, book 011, no. 3928 (reported by Usama b. Zaid).', 'url' => '' ),
			),
			'body'        => '<p>The report is quoted in half, and the half that is dropped is the half that answers the objection.</p>
<h3>The report in full</h3>
<p>Sahih Muslim, book 011, no. 3928. The Prophet said: a Muslim is not entitled to inherit from a non-Muslim, <strong>and a non-Muslim is not entitled to inherit from a Muslim</strong>.</p>
<p>The rule runs in both directions. It is symmetrical on its face, in a single sentence, and the polemic works by citing the first clause and stopping.</p>
<h3>The principle</h3>
<p>Islamic inheritance operates under <em>fara\'id</em> (the fixed shares of inheritance law), a defined system that governs the transfer of wealth within the community it binds. Judaism and Christianity have historically maintained comparable confessional boundaries around inheritance and communal obligation. Every legal order that recognises a religious community defines who stands inside it for such purposes.</p>
<p>The rule imposes no disability on the non-Muslim that it does not impose equally on the Muslim. It is a boundary, and boundaries by their nature face both ways.</p>
<h3>The method</h3>
<p>Truncating a sentence to reverse its sense is a specific technique, and it recurs across this material. It is worth learning to recognise, because once recognised it is difficult to unsee, and it substantially reduces the force of the rest of the case.</p>
<p>Read the second clause. Then ask why it was not shown to you.</p>',
		),
		array(
			'slug'        => 'is-islam-a-cult',
			'title'       => 'Is Islam a cult',
			'ce_slug'     => 'is-islam-a-cult',
			'claim'       => 'Islam is not a religion at all, merely a cult.',
			'source_type' => 'pamphlet',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Max Weber, Sociology of Religion, p. xxxvii.', 'url' => '' ),
				array( 'citation_text' => 'The Advanced Learner\'s Dictionary of Current English, 2nd edn (Oxford, 1968), p. 828.', 'url' => '' ),
				array( 'citation_text' => 'Robert Jay Lifton, Thought Reform and the Psychology of Totalism (Norton, 1961), ch. 22.', 'url' => '' ),
				array( 'citation_text' => 'Steven Hassan, Combating Cult Mind Control (Park Street Press, 1988), the BITE model.', 'url' => '' ),
				array( 'citation_text' => 'On institutional determinations of deviant teaching: al-Azhar\'s Islamic Research Academy; the Indonesian Ulama Council (MUI); and the classical heresiographical literature, e.g. al-Shahrastani.', 'url' => '' ),
			),
			'body'        => '<p>The companion site works through the cult-studies criteria at length and shows the label fails every one of them. That argument does not need making twice. What is worth noticing here is a structural fact the accusation cannot absorb: Islam maintains, in public, its own machinery for identifying groups that behave cultically.</p>
<h3>The tradition polices the category</h3>
<p>Muslim scholarly bodies across the world issue determinations about deviant teaching. Al-Azhar\'s Islamic Research Academy reviews publications; national fatwa councils in Indonesia and Malaysia issue findings on specific groups; the classical heresiographers catalogued sects and set out their reasoning at length. Whatever one thinks of any particular finding, and Muslims argue about many of them, the existence of the machinery establishes something the polemic has no answer to. The tradition accused of cultic structure is the tradition that maintains the criteria, applies them to claimants, and publishes its reasons for challenge.</p>
<p>Cults do not do this. A closed group does not staff offices whose function is to identify closed groups and invite scholarly dispute of their findings.</p>
<h3>What the accusation is doing</h3>
<p>Forensically, the move is reclassification in place of argument. Applying the word converts adherents into subjects requiring explanation, and a subject requiring explanation does not have to be answered. That is its function, and it is why the term usually arrives without criteria attached: criteria would invite a test.</p>
<p>Ask the person using it which marker Islam satisfies. Milieu control, in a tradition whose scholars have disputed in public for fourteen centuries. A living infallible authority, in a religion with no clergy and a Prophet who died in 632. Enforced ignorance, in a tradition that built the science of <em>isnad</em> (chains of transmission) so that students could audit claims for themselves. The questions go unanswered because answering them is where the charge ends.</p>
<h3>The distinction that matters</h3>
<p>A particular family or group can behave cultically, in this religion as in any other, and a person who has lived that is entitled to the word for what was done to them. It describes their experience. It does not describe the religion, and the religion\'s own machinery for identifying such groups exists because it draws exactly that line.</p>',
		),
		array(
			'slug'        => 'are-all-non-muslims-damned',
			'title'       => 'Are all non-Muslims damned',
			'ce_slug'     => 'do-good-non-muslims-go-to-hell',
			'claim'       => 'Islam teaches that every non-Muslim goes to hell, which is why teachers say so in school.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'identity', 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 2:256 (there is no compulsion in religion).', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 48:13.', 'url' => '' ),
			),
			'body'        => '<p>The companion site sets out the theology of accountability and the breadth of mercy in the sources. This entry takes the version of the objection that circulates most widely, which is not a question about the afterlife in general. It is a question about a specific street.</p>
<h3>The form the question takes</h3>
<p>Most Muslims do not meet this question in the abstract. They grow up beside neighbours of other faiths, eat at their weddings, sit examinations and work shifts beside them, and are told at some point by someone that all of them are going to the Fire. The objection is not really "does Islam teach exclusivism". It is "am I required to believe that about the woman next door".</p>
<p>The answer is that he was told something the sources do not support, by somebody with no standing to say it. <em>Kaafir</em> denotes rejection of a truth recognised and refused, an act of the will. Whether any particular person has done that is knowledge no human being possesses, and the Qur\'an reserves the judgement of individuals to God without exception. The Muslim\'s position on any named person is that he does not know, and the confident sorting he was taught in a classroom was a failure of transmission rather than the doctrine itself.</p>
<h3>What the tradition does hold</h3>
<p>It holds that the dispute between belief and rejection is real and that it matters, and this site is not going to soften that into a shrug. It also holds that God does not punish before a message has reached a person, that the <em>ahl al-fatrah</em> discussion exists precisely for those it has not reached, and that Muslims themselves face punishment for their own failures. This is not a scheme in which membership is the whole of the accounting.</p>
<h3>Where the argument turns on itself</h3>
<p>The testimony that carries this objection usually adds that the writer\'s non-Muslim friends were the good ones and the Muslims were not. That is the same sorting by label, run in the opposite direction, by someone who has just condemned it. Nothing in Islam prevents a Muslim from holding non-Muslims among his closest friends while keeping his religion entire, and the man who wrote the book behind this site said exactly that about his own life.</p>',
		),
		array(
			'slug'        => 'is-islamic-faith-irrational',
			'title'       => 'Is Islamic faith irrational',
			'ce_slug'     => 'doubt-permitted-in-islam',
			'claim'       => 'Islam requires belief without knowledge, and is therefore irrational at its root.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'A. D. Ajijola, The Essence of Faith in Islam (Islamic Publications Ltd, Lahore, 1978), p. 17.', 'url' => '' ),
				array( 'citation_text' => 'A. D. Ajijola, ibid., p. 55.', 'url' => '' ),
			),
			'body'        => '<p>The claim usually rests on an anecdote: a teacher, asked whether belief is required without knowing, answered yes. The anecdote may well be accurate. It settles nothing about the tradition, and the tradition answers it directly.</p>
<h3>What faith is held to be</h3>
<p>Faith in the Islamic sense has never been defined as intellectual assent to a dogma, nor as an emotional response to a compelling personality. It has been defined as a volitional and dynamic reaction to belief in one God, a conviction that assimilates both the intellectual and the emotional response. It is at once an affirmation of a truth and a surrender to the truth affirmed. Absent the first, it is blind. Absent the second, it is inert.</p>
<p>Both halves are required. That is a demanding standard, and it is the opposite of a demand to stop thinking.</p>
<h3>The buried premise</h3>
<p>The objection assumes that what cannot be perceived directly may reasonably be assumed not to exist. Stated plainly, the principle collapses. A great deal of what is known is inferred rather than observed, and the absence of one kind of evidence is not evidence of absence.</p>
<p>Clearing that premise away does not prove the case for God. It establishes only that the argument offered against it does not work, which is a smaller claim and a defensible one.</p>
<h3>The teacher</h3>
<p>A religious instructor who meets a child\'s question with an instruction to stop asking has failed at the single task he was given. Islam produced centuries of scholars who argued over metaphysics, jurisprudence, logic, and the natural world, and who were honoured for precisely that. A classroom that treats a question as insubordination is not passing on that inheritance. It is losing it.</p>
<p>The reader who asked the question was doing what the tradition asks. The person who shut it down was not.</p>',
		),
		array(
			'slug'        => 'the-state-of-muslims-argument',
			'title'       => 'The state of the Muslims',
			'ce_slug'     => 'islam-and-enlightenment',
			'claim'       => 'Look at the condition of the Muslim world: the backwardness, the illiteracy, the decline. That is what the religion produces, and it is reason enough to leave.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Syed Muhammad Naquib al-Attas, Islam and Secularism (ABIM, 1978), p. 113.', 'url' => '' ),
				array( 'citation_text' => 'International Institute of Islamic Thought, Islamization of Knowledge: General Principles and Work Plan (IIIT, 1989), pp. 1-4.', 'url' => '' ),
			),
			'body'        => '<p>The argument moves from the condition of a people to a verdict on their religion. The movement feels natural, and it does not survive inspection.</p>
<h3>The inference</h3>
<p>Run the same logic elsewhere and watch it work. Christianity is falsified by the Thirty Years\' War, secular rationalism by the twentieth century\'s industrial slaughters, Buddhism by any period of decline in any Buddhist polity. Every tradition, religious or otherwise, has presided over a nadir. If a community\'s worst centuries refute its creed, every creed on earth is refuted, and the argument proves too much to prove anything.</p>
<h3>What the decline is</h3>
<p>The malaise is real, and Muslim thinkers have diagnosed it more sharply than the polemic does. The educational imbalance is a leading account: Muslim societies pour their effort into <em>fard al-kifayah</em> (collective duties, the technical and professional knowledge a community needs) while leaving <em>fard al-\'ayn</em> (the knowledge binding on each individual person) at an infantile level. The result, in al-Attas\'s description, is an adult who knows a great deal about the world and almost nothing about his religion, and leaderships whose comprehension of Islam is stunted at the level of immaturity, so that Islam itself is made to appear undeveloped, or left to stagnate.</p>
<p>That is a diagnosis of Muslims who abandoned their own intellectual tradition. During the centuries when a lay Muslim was educated in at least the basics of <em>aqidah</em> (creed) and <em>usul al-fiqh</em> (legal theory), and scholars were held in esteem for knowledge across the sciences, the problems now cited as evidence were marginal.</p>
<h3>The distinction the argument needs and lacks</h3>
<p>A religion and the condition of its adherents at a given hour of history are two different objects. Judging the first by the second requires showing that the teaching produced the decline, and the record runs the other way: the decline tracks the abandonment of the teaching, its educational system, and its scholarly culture. The observation is damning. It is damning of a civilisation\'s stewardship, and the polemic has aimed it at the wrong target.</p>',
		),
		array(
			'slug'        => 'i-studied-it-in-school',
			'title'       => 'The credential claim',
			'ce_slug'     => 'faith-was-just-conditioning',
			'claim'       => 'I studied Islam in school for years, so I know it well, and knowing it well is precisely why I reject it.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Pendidikan Islam is rendered \'Islamic Studies\' in English, per the Ministry of Education, Malaysia.', 'url' => '' ),
				array( 'citation_text' => 'Sahih al-Bukhari, hadith no. 7:69; al-Albani, Sahih Abi Dawud (2096): the sources contradicting the claims advanced under this credential.', 'url' => '' ),
			),
			'body'        => '<p>The claim is a credential, offered to close the argument before it starts. It deserves a fair hearing, and it collapses on contact with what is then asserted.</p>
<h3>What the credential is worth</h3>
<p>Sitting a school subject for several years is real exposure. It is not scholarly training, any more than school biology makes a geneticist, and nobody would accept the equivalent claim in any other field.</p>
<p>More to the point, the claim is testable. A person who knows the religion well will describe its rulings accurately. The rulings advanced under this credential, that a woman may be married off without her consent, that she cannot testify in court, that the Qur\'an licenses beating a wife into obedience, are each contradicted by the primary sources, and the contradictions are not obscure. They sit in al-Bukhari, in the plain text of Qur\'an 2:282, in the verse\'s own sequence.</p>
<h3>What happened</h3>
<p>What such a person mastered was a syllabus, and often a poorly taught one, delivered by an instructor who could not answer a hard question and did not welcome it. That is a real education in something. It is not an education in Islam, and the gap between the two is the entire subject of this site.</p>
<p>The failure being described is the failure of the teaching, and Muslims should own it rather than deflect it. A person who left because the version handed to them was indefensible left something indefensible. They did not leave Islam. They have never met it.</p>
<h3>The honest position</h3>
<p>Anyone is entitled to examine the religion and reject it. What is not available is rejecting a caricature and reporting it as an informed verdict on the original. The invitation is simple: bring the specific ruling, and check it against the source.</p>',
		),
		array(
			'slug'        => 'muslims-cannot-agree',
			'title'       => 'Sects and division',
			'claim'       => 'Muslims cannot even agree among themselves: dozens of sects, each calling the others wrong. A true religion would not fracture like that.',
			'source_type' => 'forum',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Prophetic tradition on the division of the ummah into seventy-three groups; see the exegesis at islamtoday.net.', 'url' => '' ),
				array( 'citation_text' => 'Isma\'il Raji al-Faruqi, Al-Tawhid: Its Implications for Thought and Life (IIIT, 1992), pp. 153-154.', 'url' => '' ),
			),
			'body'        => '<p>The argument assumes that a true religion would produce uniform agreement among its adherents. Stated that plainly, it is worth asking who ever promised that.</p>
<h3>What the tradition says</h3>
<p>The Prophet foretold that the community would divide into many groups. The number given, seventy-three, is a figure of speech for multiplicity in the idiom of the time, in the way that "forty" or "a thousand" functions across Semitic languages. Reading it as a census is a category error, and the polemic that counts sects and matches them against the number has misunderstood the sentence it is quoting.</p>
<p>The relevant point is that the division was predicted rather than denied. A religion whose founder said the community would fracture is not embarrassed by the fracture.</p>
<h3>Disagreement is not schism</h3>
<p>Most of what gets counted as sectarian division is <em>fiqh</em> (jurisprudence): differences over how a ruling is derived and applied. Four Sunni legal schools coexisted for over a millennium, each holding the others valid, and a Muslim may follow any of them. That is a structured method for handling disagreement rather than evidence of collapse, and the tradition built it deliberately.</p>
<p>The serious divisions are narrow, and Muslims have argued about where the boundaries fall with more rigour than the objection credits.</p>
<h3>The test the argument fails</h3>
<p>Apply it elsewhere. Christianity numbers its denominations in the thousands. Judaism, Buddhism, and every school of secular philosophy have fractured comparably. If internal disagreement falsifies a position, nothing anyone has ever held survives, including the objection itself.</p>
<p>Disagreement among people who hold a thing is evidence about people. It is not evidence about the thing.</p>',
		),
		array(
			'slug'        => 'modesty-and-the-visual-argument',
			'title'       => 'The visual argument',
			'ce_slug'     => 'hijab-male-control-or-divine-command',
			'claim'       => 'The head-cover is justified by claims about male biology, which either excuses men or rests on shaky science.',
			'source_type' => 'academic',
			'topics'      => array( 'head-cover', 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Stephan Hamann, Rebecca Herman, Carla Nolan and Kim Wallen, \'Men and women differ in amygdala response to visual sexual stimuli\', Nature Neuroscience 7 (2004), pp. 411-416.', 'url' => 'https://www.nature.com/articles/nn1208' ),
				array( 'citation_text' => 'Qur\'an 24:30 (the instruction to men, which precedes the instruction to women).', 'url' => '' ),
			),
			'body'        => '<p>Muslim writers, this author included, have sometimes reached for neuroscience to justify the head-cover. The move deserves scrutiny, because it is weaker than the case it is meant to support.</p>
<h3>What the research does establish</h3>
<p>The finding is real and it replicates. Using fMRI, researchers at Emory found that the amygdala and hypothalamus activate more strongly in men than in women when both view identical sexual images, and that this held even when the women reported greater subjective arousal. Men are, on average, more visually responsive. That much is solid.</p>
<h3>What it does not establish</h3>
<p>Two claims have been built on top of it that the research does not carry. The first is that the female brain plays no role in arousal; the same study found men and women activating similar patterns across multiple regions, including the reward circuitry. The second draws on reporting about brain activity during orgasm, which is a different phenomenon from response to visual stimuli and cannot be transferred across.</p>
<p>Anyone defending the head-cover on those two claims has staked it on assertions that a competent critic will dismantle in an afternoon.</p>
<h3>Why the argument should not be made this way at all</h3>
<p>There is a deeper problem, and it is a problem of the religion\'s own teaching. An argument that grounds covering in male biology implies that men cannot govern their eyes and that women must therefore compensate. Qur\'an 24:30 says the opposite, and it says it first: men are to lower their gaze and guard their chastity. The obligation is placed on the man\'s restraint. A defence that relieves him of it has surrendered the verse to win the argument.</p>
<p>The command stands on the text and on the shared standard of modesty it establishes. It does not need a brain scan, and it should not be hostage to the next one.</p>',
		),
		array(
			'slug'        => 'the-polemical-echo-chamber',
			'title'       => 'The echo chamber',
			'ce_slug'     => 'the-algorithm-that-deconverted-you',
			'claim'       => 'After reading the criticism websites and checking their claims against a translation of the Qur\'an, I realised the book is full of problems, and Muslims themselves privately know their religion is nonsense.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'testimony-patterns', 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Fred Halliday, \'Islam in the West: The Causes of Prejudice\', interview by Abdullah Humouda, Arab News, 22 June 1995, p. 11.', 'url' => '' ),
			),
			'body'        => '<p>Two methods are at work in this claim, and each refutes itself once it is named.</p>
<h3>Confirmation by echo</h3>
<p>The sequence described is: read the polemical sites, then read a translation with their claims already in hand, then find the claims "confirmed". A conclusion carried into the evidence will be found in the evidence. That is what carrying it there accomplishes. Run the same circuit through the classical commentaries and the Muslim scholarship instead, and the circuit "confirms" the opposite. Circularity is indifferent to direction, which is exactly what disqualifies it as a method.</p>
<p>The test of whether a reading was checking or confirming is simple: did the reader ever place the objection next to the tradition\'s answer to it? The objections circulating online are old. Nearly all of them are the Orientalist and missionary polemic of a previous century recirculated through newer channels, and the answers have been in print for as long as the objections have. A search that found only one side was not a search.</p>
<h3>The anecdote</h3>
<p>The second method convicts a billion people on the word of one unnamed man in one room: a husband is reported to have said that Muslims know the religion is nonsense, and the private conviction of the entire ummah is inferred. An anecdote of this kind establishes, at most, what one man said. The claim built on it would require evidence that Muslims generally hold this belief in secret. No such evidence is offered, and the daily, public, costly practice of ordinary Muslims everywhere is evidence in the other direction.</p>
<p>None of this proves the tradition true. It establishes that the reading which felt like discovery was recognition: the reader met an inherited polemic and mistook the meeting for a finding. The honest next step is the one the method skipped, which is to read both shelves.</p>',
		),
		array(
			'slug'        => 'the-scientific-miracles-genre',
			'title'       => 'The scientific miracles genre',
			'ce_slug'     => 'scientific-miracles-quran',
			'claim'       => 'The scientific miracles claimed for the Qur\'an are post-hoc retrofitting: the discovery comes first and the verse is bent to fit it. Once that argument collapses, the case for the Qur\'an goes with it.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Muhammad Asad, The Message of the Qur\'an (Dar al-Andalus, 1980), the translator\'s handling of cosmological passages.', 'url' => '' ),
			),
			'body'        => '<p>The first half of this claim is correct, and we say so plainly, because an apologetics that cannot concede a sound point forfeits the right to be believed on any other.</p>
<h3>The concession</h3>
<p>Reading modern discoveries back into Qur\'anic verses is methodologically post-hoc. The discovery arrives first; the verse is conformed to it afterward; and when the science is revised, the "miracle" is stranded on the abandoned theory. Muslim scholars raised this objection from inside the house before any polemicist found it. Asad\'s translation deliberately declines the genre, and the embarrassments the genre has produced are real. Where our own earlier work leaned on this pattern, we retire the lean.</p>
<h3>What the concession does not carry</h3>
<p>The collapse of a bad argument for a claim is not the collapse of the claim. The Qur\'an never staked itself on predicting laboratory results. The challenge the text issues, the <em>tahaddi</em>, concerns its own inimitability, and the classical <em>i\'jaz</em> literature is about language, structure, and the circumstances of revelation, a body of argument the scientific-miracles genre displaced rather than represented.</p>
<p>The defensible position is more modest and more durable: the Qur\'an does not contradict established science. That claim is weaker than the genre\'s boast and stronger than the genre, because no revision of a theory can strand it.</p>
<h3>Why this entry exists</h3>
<p>A tradition confident of its ground audits its own arguments in public. The reader who lost their footing when the miracles argument failed lost an argument, and arguments are replaceable. What deserved their weight was never resting on it.</p>',
		),
		array(
			'slug'        => 'apostasy-and-the-classical-law',
			'title'       => 'Apostasy and the classical law',
			'ce_slug'     => 'apostasy-political-history',
			'claim'       => 'Islam keeps its members by threat. The punishment for leaving is death, which proves the religion holds people the way a cult holds them.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'apostasy-law' ),
			'sources'     => array(
				array( 'citation_text' => 'M. Hamidullah, The Muslim Conduct of State (Sheikh Muhammad Ashraf, Lahore, 1977), p. 6, para. 330.', 'url' => '' ),
				array( 'citation_text' => 'Muhammad Hamidullah, Introduction to Islam (Sheikh Muhammad Ashraf, Lahore, 1974), para. 119.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 2:256.', 'url' => '' ),
			),
			'body'        => '<p>This entry describes law. It advocates nothing. The political history of these rulings is set out on the companion site; what follows is the legal position as it stands today, because a reader asking this question is rarely asking in the abstract.</p>
<h3>What applies in practice</h3>
<p>Most Muslim-majority states attach no criminal penalty to leaving Islam. A minority retain the classical ruling in statute or in judicial practice, and a larger number attach civil consequences instead: to marriage, to inheritance, to the religion recorded on identity documents, or to the court with jurisdiction over family matters. In several countries the real difficulty is administrative and jurisdictional rather than penal, and it is real. A reader who needs to know his own position should look at the law of his own country, which varies more than the material circulating online suggests.</p>
<h3>Where the classical rulings came from</h3>
<p>The framework is confessional-state law, forged in the <em>riddah</em> (apostasy) wars against armed secession from the nascent polity, and it treated apostasy as politico-religious rebellion. Every pre-modern confessional state ran the same logic; Byzantine law in the Prophet\'s own epoch punished apostasy from the imperial sect with death, and Christendom did so for a thousand years. Hamidullah\'s own judgement on the classical provision is that the necessity for it scarcely arose. How those rulings relate to a modern constitutional state is argued among Muslim scholars now, in the open, with serious voices holding that the ruling concerned treason against a polity rather than private belief. Anyone who tells you that argument is settled, in either direction, is reporting a preference.</p>
<h3>For the reader this is about</h3>
<p>Doubting is an offence nowhere. Reading this page is an offence nowhere. No legal provision in any country reaches a person who is unsure, and nothing in the paragraphs above describes you. The legal question and your safety tonight are different questions, and this page has answered only the first.</p>',
		),
		array(
			'slug'        => 'what-the-testimonies-actually-do',
			'title'       => 'What the testimonies do',
			'ce_slug'     => 'how-muslims-leave-the-sociology',
			'claim'       => 'Apostates\' testimonies are emotional venting rather than reasoning: evidence of psychological insecurity and of projecting their own problems onto the religion they left.',
			'source_type' => 'academic',
			'topics'      => array( 'testimony-patterns' ),
			'sources'     => array(
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media: A Speech Acts Analysis\', 3L: The Southeast Asian Journal of English Language Studies 28(4), December 2022, pp. 197-211. DOI: 10.17576/3L-2022-2804-14', 'url' => '' ),
				array( 'citation_text' => 'Radzuwan Ab Rashid and Azweed Mohamad, New Media Narratives and Cultural Influence in Malaysia: The Strategic Construction of Blog Rhetoric by an Apostate (Springer, SpringerBriefs in Religious Studies, 2020), extending the same finding from social-media postings to a sustained blog.', 'url' => '' ),
				array( 'citation_text' => 'John R. Searle, Expressions and Meaning: Studies in the Theory of Speech Acts (Cambridge University Press, 1999).', 'url' => '' ),
				array( 'citation_text' => 'Teun A. van Dijk, Text and Context: Explorations in the Semantics and Pragmatics of Discourse (Longman, 1992).', 'url' => '' ),
			),
			'body'        => '<p>This claim comes from our own side of the argument, and it does not survive contact with the evidence. We retire it here, because an apologetics that keeps a comfortable falsehood about its opponents has no standing to correct anyone else\'s.</p>
<h3>What was claimed</h3>
<p>Older apologetic writing on apostasy from Islam, this author\'s included, characterised those who left as psychologically insecure, described their public statements as projection, and at one point prescribed a psychological examination for them. That is a diagnosis offered by people with no standing to make one, about people they had never met. It also functions as an excuse: if the testimony is a symptom, nobody has to answer it.</p>
<h3>What the measurement shows</h3>
<p>Hashmi and colleagues examined 291 postings by five former Malaysian Muslims across Facebook and Twitter, between October 2019 and March 2020, coding every utterance against Searle\'s taxonomy of speech acts and van Dijk\'s distinction between the act performed in a sentence and the act performed by a whole posting.</p>
<p>At the level of the whole posting, the most common thing these writers did was argue, roughly a quarter of all postings. Rejection followed at about a fifth, then denial, warning, assertion, and persuasion. At sentence level the ranking was the same: argument first, denial second. Whatever else is going on in this discourse, its dominant activity is the construction of arguments intended to be answered.</p>
<p>So the insecurity framing is not merely uncharitable. It is inaccurate, and measurably so. It should be dropped, and this site does not use it.</p>
<h3>What the data does support</h3>
<p>Two of the study\'s categories are worth keeping in view, because they are not the same as argument and the study counts them separately. Denial, the second most frequent act, objects to something previously said; an objection is not yet a case, and a discourse can be dense with denial while establishing very little. Assertion, in that study\'s coding, means a positive statement offered without evidence for it. Both appear alongside genuine argument, and telling the three apart is the whole work of reading such a testimony carefully.</p>
<p>That is the honest position: these are people arguing, sometimes well and sometimes by assertion, and the reply owed to them is a reply to the arguments. Every other entry on this site is an attempt at that reply.</p>
<h3>A note on the source</h3>
<p>The study describes the discourse it examined as derogatory, and closes by suggesting its findings could help authorities in Muslim-majority countries identify such discourse. We cite it for what it measured and not for that application. Identifying people so that they can be pursued is not what this site is for, and a reader who has arrived here uncertain is not a target of anything. The participants in that study had masked their identities so that they could speak at all, which is a fact worth sitting with before treating their words as a specimen.</p>',
		),
		array(
			'slug'        => 'the-fragility-argument',
			'title'       => 'The fragility argument',
			'ce_slug'     => 'doubt-permitted-in-islam',
			'claim'       => 'You accept praise of your religion but not criticism of it. Shutting down questions shows Islam rests on submission rather than peace, and a religion too fragile to survive criticism cannot be the truth.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'reason-and-faith', 'testimony-patterns' ),
			'sources'     => array(
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media: A Speech Acts Analysis\', 3L: The Southeast Asian Journal of English Language Studies 28(4), 2022, pp. 197-211.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 2:256; 3:190-191; 16:125.', 'url' => '' ),
				array( 'citation_text' => 'Ibn Rushd, Tahafut al-Tahafut, replying to al-Ghazali\'s Tahafut al-Falasifah.', 'url' => '' ),
			),
			'body'        => '<p>Two claims are travelling together here, and the join is where the argument fails. The first is that Muslims react badly to criticism. The second is that this tells you something about whether Islam is true. The second does not follow from the first, and it would not follow if the first were established beyond dispute.</p>
<h3>The inference does not work</h3>
<p>Test the form of the argument on anything else. A biologist who loses his temper at a critic of evolution has told you about his manners. He has told you nothing about the descent of species. Adherents\' behaviour is evidence about adherents. Nobody applies this standard elsewhere, and the polemicist does not apply it to himself: the same discourse that calls Islam fragile also calls the Qur\'an "crap" and does not expect the courtesy it demands.</p>
<h3>The premise is weaker than it looks</h3>
<p>The tradition being described as unable to withstand scrutiny is the one that produced <em>kalam</em>, an entire discipline of theological disputation conducted in public. Al-Ghazali attacked the philosophers in writing; Ibn Rushd answered him in writing; the argument ran across generations and both books survive because Muslims copied them. The science of hadith exists so that any student can audit the chain behind any claim and reject what fails. A tradition that builds an apparatus for auditing its own transmission is not a tradition organised around not being looked at.</p>
<p>This site is the same point in miniature. Every entry reproduces the objection first, in the objector\'s words, and then answers it. That is not the behaviour of something fragile.</p>
<h3>On "submission"</h3>
<p>Islam does mean submission, and the word is offered here as though it were a confession extracted under pressure. It is on the tin. What is being submitted to is God, and every system of thought asks submission to something: to evidence, to reason, to the conclusions one\'s method yields. The polemic mistakes a doctrine\'s name for an admission of guilt.</p>
<h3>What is left</h3>
<p>Underneath the argument sits a real observation: some Muslims answer questions with hostility. That is true, and it is their failing, and it damages the faith they think they are protecting. It is also the exact failing on display in a discourse that calls a billion people\'s scripture rubbish and then complains of arrogance.</p>
<p>The argument as constructed requires that believers\' conduct be evidence about the belief. Adopt that standard and nothing anyone has ever held survives contact with its worst adherent, including this objection.</p>',
		),
		array(
			'slug'        => 'the-death-threat-charge',
			'title'       => 'The death threat charge',
			'ce_slug'     => 'apostasy-and-freedom',
			'claim'       => 'Muslims send death threats to people who publicly question Islam. That is what the religion produces.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'war-and-violence', 'testimony-patterns' ),
			'sources'     => array(
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media: A Speech Acts Analysis\', 3L: The Southeast Asian Journal of English Language Studies 28(4), 2022, pp. 197-211.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 5:32; 2:256.', 'url' => '' ),
				array( 'citation_text' => 'On the jurists\' restriction of hudud to constituted authority: Ibn Qudamah, al-Mughni, and the classical consensus that vigilante enforcement is itself an offence.', 'url' => '' ),
			),
			'body'        => '<p>The fact is conceded before the argument begins. People do send these messages, in public, sometimes under their own names, and it is indefensible. Anyone who has received one was frightened for a reason. We are not going to argue about whether it happens.</p>
<h3>The inference is the problem</h3>
<p>What the polemic needs is the next step: that the threats express the religion rather than violate it. That step requires the sources to authorise what the sender did, and they do not.</p>
<p>Qur\'an 5:32 makes the killing of one innocent soul the killing of all humanity. Qur\'an 2:256 forbids compulsion in religion outright. And the classical jurists, who are not usually accused of leniency, closed this door specifically: <em>hudud</em> penalties belong to constituted authority alone, a private individual has no standing to impose them, and the man who takes it upon himself commits an offence in doing so. Declaring another person an unbeliever is likewise reserved to those qualified to judge. The online threatener has no authority under any school to do the thing he is threatening.</p>
<h3>Who agrees with whom</h3>
<p>Set the two men side by side. The one sending the threat believes Islam authorises him to kill a critic. The one citing the threat believes Islam authorises him to kill a critic. They agree completely, and both stand against fourteen centuries of jurisprudence that says otherwise. When a polemicist tells you the threatener is the authentic Muslim, he is accepting the threatener\'s account of Islam and discarding the scholars\'. That is a strange place for a critic to end up, and he never explains why the most lawless reading is the true one.</p>
<h3>What is owed</h3>
<p>Something is owed here, and it is not owed to the argument. A Muslim who reads this and recognises the behaviour in people he knows should say so where it will cost him something. The threats do more damage to the faith than any ex-Muslim posting has ever done, because they hand the polemic its only piece of evidence that behaves like proof.</p>
<p>The charge that Muslims do this is true. The charge that Islam commands it is refuted by the same law the threatener imagines he is enforcing.</p>',
		),
		array(
			'slug'        => 'marital-consent-and-the-rape-charge',
			'title'       => 'Marital consent and the rape charge',
			'ce_slug'     => 'women-in-islam',
			'claim'       => 'Islam has no concept of rape within marriage. A husband may demand sex and his wife is obliged to submit, and the hadith are selected to suit patriarchy.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media: A Speech Acts Analysis\', 3L: The Southeast Asian Journal of English Language Studies 28(4), 2022, pp. 197-211.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 4:19; 30:21; 2:187.', 'url' => '' ),
				array( 'citation_text' => 'Ibn Majah, Sunan, hadith 2341: la darar wa la dirar (no harm shall be inflicted or reciprocated), one of the five universal maxims of Islamic jurisprudence.', 'url' => '' ),
				array( 'citation_text' => 'Malaysian Penal Code, s. 375 Explanation and s. 375A (inserted 2007), on hurt or fear of death caused to compel intercourse.', 'url' => '' ),
				array( 'citation_text' => 'Turkey, Penal Code (Law No. 5237, 2004, in force 2005), art. 102, on sexual assault without marital exception.', 'url' => '' ),
			),
			'body'        => '<p>This is the most serious argument in the set, and it deserves a direct answer.</p>
<h3>What the sources require</h3>
<p>The governing instruction on marriage is that a man live with his wife <em>bil-ma\'ruf</em> (in kindness and decency), in Qur\'an 4:19. The purpose stated for the relationship is tranquillity, affection, and mercy (30:21). The Qur\'an\'s metaphor for spouses is that each is a garment to the other (2:187), an image of covering and protection that cannot be made to accommodate force.</p>
<p>Then there is the maxim. <em>La darar wa la dirar</em> (no harm shall be inflicted or reciprocated) is one of the universal maxims on which the classical jurists built entire chapters of law, and it long predates this objection. Forcing a woman is harm. Harm is prohibited. The prohibition is structural, and it requires no contemporary scholar\'s permission to operate.</p>
<h3>The hadith the argument leans on</h3>
<p>The report usually produced concerns a wife who refuses without cause, and it establishes a duty of responsiveness within marriage. Grant it its full weight and it still does not reach the conclusion. A duty owed by one party has never, in any chapter of <em>fiqh</em> (jurisprudence), licensed the other party to take by force what he is owed. A creditor may not seize; a claimant may not help himself. There is no branch of Islamic law in which private violence is the remedy for an unmet obligation, and the step from "she owes" to "he may compel" is supplied entirely by the polemic.</p>
<h3>Where the criticism lands</h3>
<p>It lands, though not where it was aimed. Classical discussions of <em>tamkin</em> (conjugal availability) read coldly to a modern eye, and the honest course is to say so. National law across the Muslim world is also uneven. Some states criminalise marital rape outright, Turkey among them since 2005; others retain a marital exception while penalising force and threats, Malaysia among them; others have no provision at all. Muslim jurists and legislators are divided on how far the law should go, and that division is real and current.</p>
<p>So the argument has found real failures of legislation and has misidentified them as a teaching. Islam does not license a man to force his wife; the maxim against harm forbids it. Whether a given state\'s statutes say so plainly enough is a fair question, and it is a question for that state\'s legislators rather than for the Qur\'an.</p>',
		),
		array(
			'slug'        => 'modesty-and-the-morality-police',
			'title'       => 'Modesty and the morality police',
			'ce_slug'     => 'hijab-male-control-or-divine-command',
			'claim'       => 'Women are prosecuted for uncovering their hair. Modesty culture is a harmful system that holds women back everywhere.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'head-cover' ),
			'sources'     => array(
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media: A Speech Acts Analysis\', 3L: The Southeast Asian Journal of English Language Studies 28(4), 2022, pp. 197-211.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 24:30-31; 2:256.', 'url' => '' ),
			),
			'body'        => '<p>The cases are real and they should be named. A woman charged for uncovering her head has been wronged, and a Muslim who cannot say that plainly has confused the defence of a command with the defence of whoever claims to enforce it.</p>
<h3>The command and the constable are different things</h3>
<p>What the Qur\'an addresses to the believing woman in 24:31, having first addressed the believing man in 24:30, is an instruction given to her by her Lord. It is not a warrant issued to a state. The apparatus that patrols Tehran\'s streets is a creation of one government after 1979, operating in a Twelver Shi\'i polity whose rulings bind no Sunni anywhere, and it has no counterpart in the classical Sunni tradition, which never conceived of an inspectorate of women\'s clothing.</p>
<p>The verse that settles this is the one the argument never quotes. There is no compulsion in religion. An act of worship performed because a policeman is watching is not the act the verse commands; it is a costume. Coercion does not merely fail to produce obedience here, it destroys the thing it claims to be producing.</p>
<h3>The comparison the argument declines to make</h3>
<p>"Modesty culture holds women back globally" is offered as though the alternative were an absence of any regime governing how women appear. There is no such absence. The woman who uncovers is not thereby released into neutrality; she enters a market that appraises her by her face and her body with a thoroughness no religious code has ever managed, and that grows more exacting each decade. One of these systems is at least honest about being a system. The polemic audits one and treats the other as the natural state of things.</p>
<h3>What follows</h3>
<p>Iran\'s police are an argument against Iran\'s police. They are not an argument against 24:31, any more than a corrupt magistrate is an argument against law. The reader who has been told these are the same thing has been handed a photograph of a state and asked to accept it as an exegesis of a verse.</p>',
		),
		array(
			'slug'        => 'the-movement-and-its-self-description',
			'title'       => 'The movement and its self-description',
			'claim'       => 'Ex-Muslims are individuals who simply want to be left alone to live without religion.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'testimony-patterns' ),
			'sources'     => array(
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media: A Speech Acts Analysis\', 3L: The Southeast Asian Journal of English Language Studies 28(4), 2022, pp. 197-211.', 'url' => '' ),
			),
			'body'        => '<p>The self-description is worth checking against the record, and there is now a record to check it against. A study of 291 postings by former Malaysian Muslims coded what those postings do, and the results do not describe people asking to be left alone.</p>
<h3>What the discourse does</h3>
<p>Argument is the single most common act, at roughly a quarter of postings. That is a movement making a case to an audience. Persuasion appears as a category in its own right. So does warning. So do directives: requests for the reader to act, sign, join, denounce. One posting in the study urges former Muslims to unite so as to display their strength to Muslims and to assist those still inside the community who have not declared themselves, and promises those people that they will not be left alone.</p>
<p>Read that last promise as generously as it will bear, and it is solidarity offered to the frightened. It is still, unmistakably, organised outreach directed at people who are currently inside Islam. Whatever else that is, it is not a request for privacy.</p>
<h3>Name it accurately</h3>
<p>A movement that argues, persuades, warns, recruits, and coordinates is a missionary movement. There is nothing scandalous in the description and no reason for anyone to resent it. It is entitled to exist, to publish, and to make its case, and this site answers that case rather than asking for it to be silenced.</p>
<p>What it is not entitled to is the double posture: presenting as a support group for the wounded while conducting proselytisation, and then treating any reply as persecution. If the case is good, it can be argued in the open under its own name. If it can only be defended once described as merely wanting to be left alone, that tells you the arguer expects the description to do work the arguments cannot.</p>
<h3>The reciprocity question</h3>
<p>There is a version of freedom of conscience worth having, and it runs in both directions. It protects the person who leaves and the person who stays. A movement that will "not leave alone" those who have chosen to remain has adopted, in its own words, precisely the posture it condemns in the Muslims it left: an unwillingness to let the other side simply be. That contradiction is not fatal to their arguments, which we answer one by one elsewhere. It is fatal to the claim that they are only asking for quiet.</p>',
		),
		array(
			'slug'        => 'the-challenge',
			'title'       => 'The challenge',
			'ce_slug'     => 'quran-literary-argument',
			'claim'       => 'No positive evidence has ever been offered for the Qur\'an. Muslims only defend against objections; they never make a case of their own.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 2:23; 10:38; 11:13; 17:88.', 'url' => '' ),
				array( 'citation_text' => 'Abu Bakr al-Baqillani, I\'jaz al-Qur\'an, and Abd al-Qahir al-Jurjani, Dala\'il al-I\'jaz, on nazm (composition) as the ground of the claim.', 'url' => '' ),
			),
			'body'        => '<p>The claim is fair against this site, which has spent most of its pages answering other people\'s arguments. The positive case exists, and the fullest form of it, the literary argument from the Qur\'an\'s own challenge, is set out at length on the companion site. Read it there. This entry does the narrower thing that belongs here: it asks what the objection is claiming, and whether the person making it has done the work he is demanding of others.</p>
<h3>The challenge is testable, which is unusual</h3>
<p>In one paragraph, because it is covered in full elsewhere. The Qur\'an does not ask to be accepted on its author\'s reputation. It stakes itself on a dare that its audience could have met and did not: produce a sura like it (2:23), then one (10:38), then ten with whatever assistance can be found (11:13). The audience had the language at its height, the standing, and every motive, since the message was dismantling their authority and their commerce. They answered with boycott, exile, and war. The cheapest refutation available, and the only one that would have settled the matter, is the one never produced.</p>
<h3>The burden the objection quietly sets down</h3>
<p>Now the local point. "Muslims never make a positive case" is not a finding. It is a report about what the speaker has read, and it is usually accurate as such: the case exists, in Arabic and in English, in the classical <em>i\'jaz</em> literature and in a great deal of contemporary work, and a reader who has met only the objections has met one shelf of a library.</p>
<p>Test it the way any claim about absence should be tested. Which treatment of the challenge has the objector read? Al-Jurjani? Al-Baqillani? Anything at all on <em>nazm</em>? If the answer is none, then the claim is not that no case exists. It is that none was encountered, which is a fact about a search rather than about a religion.</p>
<h3>The verdict</h3>
<p>An argument from silence requires that the silence be established, and that is work. Nobody making this objection has been observed doing it. The case is on the shelf, it has been there for eleven centuries, and the complaint that it does not exist is answered by opening it.</p>',
		),
		array(
			'slug'        => 'what-leaving-actually-costs',
			'title'       => 'What leaving costs',
			'ce_slug'     => 'social-cost-of-leaving',
			'claim'       => 'You lose nothing by walking away except superstition and other people\'s rules. There is only gain on the other side.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'identity' ),
			'sources'     => array(
				array( 'citation_text' => 'Fiyaz Mughal and Aliyah Saleem (eds.), Leaving Faith Behind: The Journeys and Perspectives of People Who Have Left Islam (Darton, Longman and Todd, 2018), for the themes that recur across first-person accounts of leaving.', 'url' => '' ),
				array( 'citation_text' => 'Umair Munir Hashmi et al., \'Former Muslims\' Socio-Religious Discourse on Social Media\', 3L: The Southeast Asian Journal of English Language Studies 28(4), 2022, pp. 197-211, on the promissory register of the discourse.', 'url' => '' ),
			),
			'body'        => '<p>The companion site sets out what the research shows about the personal cost of leaving, and does it with more care than a page this length can. This entry adds the column that treatment leaves aside: in much of the Muslim world the cost is legal as well as social, and the prospectus promising there is nothing to lose was mostly written in countries where it is only social.</p>
<h3>What is gained</h3>
<p>First, because a ledger running one way is not a ledger. Someone released from a community that policed him stops being policed. Someone performing belief he does not hold stops performing. Those gains are real and they are not to be talked away.</p>
<h3>The legal column</h3>
<p>In many Muslim-majority states, family law is administered by religion. Religion is recorded on identity documents in several of them, and changing it is an application to an authority rather than a private decision. What follows from that entry is a body of law: which court hears a divorce, how an estate is distributed, whom one may marry, what one\'s children are registered as. In some jurisdictions the consequences go further. These are not consequences the online prospectus mentions, because the people writing it are mostly in Britain, Canada, or the United States, where leaving costs a family and not a legal status. A reader elsewhere who takes their account as a description of his own position has been handed a map of another country.</p>
<h3>What this does not establish</h3>
<p>Nothing about truth, and this needs saying plainly. If Islam is false, everything above is the price of accuracy, and accuracy is worth paying for. Cost does not make a claim true. A faith retained because the paperwork is difficult is inertia, and it is no part of what this site defends.</p>
<p>What the ledger establishes is that the decision is being taken on a description that does not fit the place the reader is standing. Count the real figures, including the legal ones, and then decide. The person who counts and goes anyway has done something intelligible. The person told there was nothing to count will meet the bill regardless, some years later, at a registry counter.</p>',
		),
		array(
			'slug'        => 'religion-by-registration',
			'title'       => 'Religion by registration',
			'ce_slug'     => 'no-compulsion-in-religion',
			'claim'       => 'The state recorded my religion before I could speak. It sits on my identity documents because of the family I was born into, and nobody ever asked me.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'identity', 'apostasy-law' ),
			'sources'     => array(
				array( 'citation_text' => 'Federal Constitution of Malaysia, Article 160(2), defining \'Malay\' as a person who professes the religion of Islam, habitually speaks Malay, and conforms to Malay custom.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 2:256; 49:13; 10:99.', 'url' => '' ),
				array( 'citation_text' => 'The Farewell Sermon, on the absence of precedence between Arab and non-Arab.', 'url' => '' ),
				array( 'citation_text' => 'On religion recorded in national identity documents: Egypt\'s national ID and the 2009 Supreme Administrative Court ruling on the Baha\'i entry; Indonesia\'s KTP and the 2017 Constitutional Court ruling on indigenous beliefs.', 'url' => '' ),
			),
			'body'        => '<p>The complaint describes something real, and in many countries it is a matter of plain administrative fact. Religion appears on national identity documents in Egypt and Indonesia, among others, and Malaysia goes further still, defining its largest ethnic group in the constitution partly by the profession of Islam. A child is entered on the register before he can spell his own name. None of this is a misunderstanding on the complainant\'s part. It is an accurate description of how a number of modern states administer religion.</p>
<h3>Islam is not an ethnicity</h3>
<p>What it does not describe is Islam, and the objection is available from inside the tradition more sharply than from outside it. The first muezzin was Abyssinian. Salman was Persian and Suhayb was Roman, and the Prophet\'s farewell sermon states that no Arab has precedence over a non-Arab. The Qur\'an grounds distinction in <em>taqwa</em> (God-consciousness) and describes human difference as a means of recognition rather than of rank (49:13). Any arrangement that treats Islam as a property of lineage or nationality contradicts the religion it claims to administer.</p>
<h3>What a registry can and cannot record</h3>
<p>The companion site sets out the full case on compulsion in religion. The narrow point here is administrative. <em>Iman</em> (faith) requires <em>tasdiq</em> (the heart\'s assent), and no ministry anywhere has been able to issue that or revoke it. A state that registers an infant as Muslim has made an entry. The classical law does presume the child of Muslim parents to be Muslim, and that presumption does legal work concerning ritual and inheritance. It was never a claim about what is in a child\'s heart, and it was never a substitute for the assent it presumes.</p>
<h3>The verdict</h3>
<p>The grievance is real and it is aimed at the wrong defendant. Being enrolled without being asked is an injury, and the objection the complainant is making is one Islam makes first and makes harder: God does not want the compelled, and a profession produced by administration is worth nothing before Him. If a state taught anyone that faith is a field on a form, the state misled him.</p>',
		),
		array(
			'slug'        => 'the-age-of-aisha',
			'title'       => 'The age of Aisha',
			'ce_slug'     => 'aisha-age-marriage',
			'claim'       => 'The Prophet married a child. Bukhari records that Aisha was six at the contract and nine when the marriage was consummated.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'hadith-authenticity', 'moral-scriptural' ),
			'sources'     => array(
				array( 'citation_text' => 'al-Bukhari and Muslim, the age reports transmitted through Hisham ibn Urwa; and Ibn Ishaq and al-Tabari for the competing chronology. The full source-critical treatment is on the companion site.', 'url' => '' ),
				array( 'citation_text' => 'Islamic Family Law (Federal Territories) Act 1984, s. 8, on minimum marriage age and the syariah court exception.', 'url' => '' ),
				array( 'citation_text' => 'On capacity and consent as conditions of a valid marriage contract, see the entry on marriage and consent on this site.', 'url' => '' ),
				array( 'citation_text' => 'Morocco, Family Code (Mudawwana) 2004, art. 19; Egypt, Law No. 126 of 2008 amending the Child Law; Indonesia, Law No. 16 of 2019 amending the Marriage Law.', 'url' => '' ),
			),
			'body'        => '<p>The companion site works this objection through at length, source by source, and a reader who wants the full historical treatment should read it there. This page does the other job, because across much of the world this report is used in a live argument about child marriage, and it is used by two opposing parties in exactly the same way.</p>
<h3>What the reports will and will not carry</h3>
<p>Briefly, since it is covered in full elsewhere. The age reports run overwhelmingly through one transmitter and predominantly through his later Iraqi students, and they sit against chronological indications in the same corpus that yield a higher figure. The traditional reading is held by serious scholars and the revised chronology is held by serious scholars; anyone reciting a single number as settled fact has overstated the record in whichever direction he is going. Betrothal at or near puberty was the ordinary practice of every society in that region, Roman law set twelve, and the Prophet\'s enemies, who manufactured a scandal about Aisha and pressed it in public, never once raised her age.</p>
<h3>The premise the two sides share</h3>
<p>The polemicist says: the Prophet married a child, therefore Islam licenses child marriage. And in several countries there are men who say: the Prophet married a child, therefore Islam licenses child marriage. They read the report identically and differ only on whether to be pleased about it.</p>
<p>Both make the same mistake, and it concerns how Islamic law works. A report of what the Prophet did in a particular circumstance is not automatically a general licence, and the jurists never treated this one as such. Marriage requires capacity and requires consent, and it is void where consent is absent, as this site sets out elsewhere. Nothing in these reports suspends those requirements, and no school has held that they do.</p>
<h3>What Muslim states have legislated</h3>
<p>The law in much of the Muslim world has moved accordingly. Morocco\'s 2004 family code set eighteen for both spouses. Egypt set eighteen in 2008. Indonesia raised the minimum for women to nineteen in 2019. Several states, Malaysia among them, still allow exceptions by judicial order, and those exceptions are contested inside those countries, by Muslims, in public. That is a question about judicial discretion under a statute. It is no question about whether the Qur\'an is true.</p>
<h3>The verdict</h3>
<p>The historical charge fails for the reasons the companion site sets out. The present-day use of it fails here: a man who cites this report to justify marrying a child today has adopted the polemicist\'s exegesis wholesale, and the correct answer to both of them is the same answer.</p>',
		),
		array(
			'slug'        => 'polygamy-and-the-condition',
			'title'       => 'Polygamy and the condition',
			'ce_slug'     => 'women-in-islam',
			'claim'       => 'A man may take four wives while a woman may take one husband. The inequality is written into the text.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 4:3 and 4:129.', 'url' => '' ),
				array( 'citation_text' => 'Islamic Family Law (Federal Territories) Act 1984, s. 23, on the court\'s permission requirement.', 'url' => '' ),
				array( 'citation_text' => 'Tunisia, Code of Personal Status 1956, art. 18; Pakistan, Muslim Family Laws Ordinance 1961, s. 6; Morocco, Family Code 2004, arts. 40-46.', 'url' => '' ),
			),
			'body'        => '<p>The permission exists. Nobody is going to argue it away, and the entries on this site that pretend a plain text says something else are the entries a reader is right to distrust. What can be shown is what the permission says, because it is quoted at half length almost every time it appears.</p>
<h3>The verse and its condition</h3>
<p>Qur\'an 4:3 permits two, three, or four, and then attaches a condition in the same breath: if you fear you will not be just, then one. The verse arrives in a passage about orphans, in the aftermath of Uhud, where a battle had left widows and fatherless children in a society with no other mechanism to absorb them. It is legislating for a specific social emergency, and it caps a practice that had previously had no ceiling at all. Against pre-Islamic Arabia the verse is a restriction, and it was received as one.</p>
<p>Then 4:129 says something the polemic never quotes: you will not be able to deal justly between them, however much you desire it. The Qur\'an attaches a condition and then states that the condition is beyond ordinary human capacity. A permission qualified that way by its own scripture is not being recommended.</p>
<h3>What is conceded</h3>
<p>That the permission has been abused, widely and for centuries, by men who read the first half of 4:3 and stopped. That the justice condition is treated in practice as a formality when the text treats it as the whole point. Muslim legislators have recorded the same discomfort in statute. Tunisia prohibited polygamy in 1956. Pakistan has required the permission of an arbitration council since 1961, Morocco the authorisation of a judge since 2004, and Malaysia the permission of the syariah court. Each of these is an admission by Muslims, in law, that the condition needs enforcing because men do not enforce it on themselves.</p>
<h3>The verdict</h3>
<p>The asymmetry is real, and it is defended in the tradition on grounds of lineage and maintenance that a reader may weigh for himself. What cannot survive is the claim that the text hands men a licence. It hands them a narrow permission, hedged by a requirement its own author says they will fail, in a context most of the people quoting it have never mentioned.</p>',
		),
		array(
			'slug'        => 'the-inheritance-shares',
			'title'       => 'The inheritance shares',
			'ce_slug'     => 'women-in-islam',
			'claim'       => 'A woman inherits half of what a man inherits. Islamic law values her at fifty per cent.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'women-in-islam' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 4:11, 4:12, 4:176.', 'url' => '' ),
				array( 'citation_text' => 'On nafaqah as the husband\'s unconditional obligation: the standard treatments in the four Sunni schools.', 'url' => '' ),
			),
			'body'        => '<p>The half-share is real in the case usually cited, and it is not the rule the claim describes. <em>Faraid</em> is a system of more than thirty configurations, and the ratio the polemic generalises applies to one of them.</p>
<h3>What the system does</h3>
<p>Where a deceased leaves sons and daughters, the son takes twice the daughter\'s portion (4:11). In other configurations the shares run differently: a mother and father may take equal sixths where there are children; a uterine brother and sister share equally; a sole daughter takes half the estate, more than several categories of male relative receive. A woman inherits as daughter, as wife, as mother, and as sister, and in a number of these she takes the same as her male counterpart or more. Describing the whole scheme by its most quoted line is like describing a tax code by one bracket.</p>
<h3>The half that is never mentioned</h3>
<p>The share does not arrive alone. Under the same law that gives the brother twice the portion, he carries an unconditional obligation of <em>nafaqah</em>: maintenance of his wife, his children, and where required his mother and unmarried sisters. His portion is encumbered before he receives it. Hers is not. A woman\'s property, whether inherited, earned, or received as <em>mahr</em>, is hers absolutely, and her husband has no claim on it and no power to direct it, at a time when married women in England could not own property at all.</p>
<p>So the comparison the claim makes is between a gross figure and a net one. Set the obligations beside the shares and the arithmetic stops being what the objection needs it to be.</p>
<h3>What is conceded</h3>
<p>That the maintenance assumption is doing heavy lifting, and that where a brother takes the larger share and then supports nobody, the woman has been shortchanged by a man exploiting a system whose premise he has declined to honour. Contemporary Muslim jurists discuss precisely this, and the discussion is live and unfinished.</p>
<h3>The verdict</h3>
<p>The rule is a package: differential shares against differential burdens. The critic who presents one half of that package and suppresses the other has produced a striking statistic and an incomplete account, and the incompleteness is where the entire force of the claim comes from.</p>',
		),
		array(
			'slug'        => 'dhimmi-status-and-jizya',
			'title'       => 'Dhimmi status and jizya',
			'ce_slug'     => 'did-islam-spread-by-the-sword',
			'claim'       => 'Non-Muslims under Islamic rule were second-class subjects, taxed for their religion and legally humiliated.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'moral-scriptural', 'war-and-violence' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 9:29; 2:256; 5:82.', 'url' => '' ),
				array( 'citation_text' => 'The Pact of Umar and its varied transmissions.', 'url' => '' ),
				array( 'citation_text' => 'On Byzantine and Sassanian treatment of religious minorities in the same period, and the Justinianic legislation against Jews, Samaritans, and heterodox Christians.', 'url' => '' ),
			),
			'body'        => '<p>This charge needs disentangling, because it bundles a tax, a legal category, and a set of historical abuses, and each answers differently.</p>
<h3>The tax</h3>
<p><em>Jizya</em> was levied on free adult non-Muslim men of means. It was not levied on women, children, the elderly, the poor, the disabled, monks, or in most rulings on those who served in the army. Against it stood <em>zakat</em>, obligatory on Muslims and not on <em>dhimmis</em>, and military service, from which <em>dhimmis</em> were exempt. Two communities were taxed under two headings, and the exemptions on both sides tell you the categories were doing fiscal and military work rather than punishing belief. Rates were frequently lower than the Byzantine burden the same populations had been paying, which is a substantial part of why Islamic expansion met the reception it did in Syria and Egypt.</p>
<h3>The category</h3>
<p><em>Dhimma</em> means a covenant of protection, and it carried enforceable content: life, property, places of worship, and internal jurisdiction, so that Jewish and Christian communities ran their own courts in their own law on matters of family and religion for centuries. That is a legal pluralism with no counterpart in contemporary Christendom, which expelled Jews from England in 1290, from France repeatedly, and from Spain in 1492.</p>
<h3>The abuses</h3>
<p>Which is where the concession belongs, and it should be made without hedging. The status was unequal, and it was meant to be. Some jurists prescribed humiliating modes of collection, some rulers imposed dress restrictions and building limits, and there were episodes of outright persecution that the theory did not sanction and the practice produced anyway. A reader told that <em>dhimma</em> was a charter of equality is being sold something.</p>
<h3>The verdict</h3>
<p>The comparison the argument requires is with the alternatives available in the seventh to seventeenth centuries, and on that comparison the record favours the <em>dhimma</em> arrangement heavily. The comparison the argument makes is with a liberal constitutional order that did not exist anywhere until the eighteenth century and had to be fought for in Europe against Christian resistance. Judged by that standard, every pre-modern polity stands condemned together, and singling out one of them is a rhetorical decision rather than a historical finding.</p>',
		),
		array(
			'slug'        => 'blasphemy-and-the-rushdie-case',
			'title'       => 'Blasphemy and the Rushdie case',
			'ce_slug'     => 'apostasy-political-history',
			'claim'       => 'A novelist was sentenced to death for a book. Islam cannot tolerate being spoken against.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'apostasy-law', 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Ayatollah Khomeini\'s pronouncement of February 1989, and the responses issued by the Islamic Conference and by scholars at al-Azhar.', 'url' => '' ),
				array( 'citation_text' => 'On sabb al-rasul in classical jurisprudence: Ibn Taymiyyah, al-Sarim al-Maslul, and the differing Hanafi treatment.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 4:140; 6:68.', 'url' => '' ),
			),
			'body'        => '<p>This entry describes law and history. It advocates nothing, and a reader weighing what follows should know that Muslim scholars argue about it openly and that the argument is unfinished.</p>
<h3>The Rushdie pronouncement was not what it is presented as</h3>
<p>The 1989 pronouncement issued from one man holding state office in a Twelver Shi\'i republic, and it was not the finding of Sunni Islam. Scholars at al-Azhar rejected it, and the Islamic Conference declined to endorse it, on grounds internal to the law: no court had sat, no charge had been answered, the accused was outside the jurisdiction, and no individual anywhere has standing to carry out a sentence nobody has pronounced. That last point is the one the polemic never registers. Even where classical law penalises an offence, it reserves execution of penalties to constituted authority, and treats the private avenger as a criminal himself.</p>
<h3>What the classical law did hold</h3>
<p>It held that <em>sabb al-rasul</em>, reviling the Prophet, was an offence, and the schools differed on its treatment: some assimilated it to apostasy, the Hanafis treated it distinctly, and the conditions, evidence, and openings for repentance varied considerably between them. This was confessional-state law, in a world where every polity treated attacks on its constitutive creed as a public offence. Blasphemy was capital in England into the seventeenth century and remained an offence on the statute book until 2008.</p>
<h3>What the Qur\'an says about the encounter</h3>
<p>Its own instruction to the believer who meets mockery of the revelation is to withdraw from that gathering until the subject changes (4:140, 6:68). The response prescribed is to leave the room. It is not to pursue the speaker, and a tradition whose scripture answers ridicule with departure has an internal argument against those who answer it with a hunt.</p>
<h3>The verdict</h3>
<p>The case that made this famous was a political act by a state, rejected by much of the Muslim world at the time and misdescribed ever since as the verdict of a religion. The wider historical question, how confessional-state provisions relate to the modern state, is contested among Muslim scholars now, and anyone who tells you that conversation is settled, in either direction, is selling a conclusion rather than reporting one.</p>',
		),
		array(
			'slug'        => 'where-was-god-when-i-was-hurt',
			'title'       => 'Where was God when I was hurt',
			'ce_slug'     => 'suffering-and-god',
			'claim'       => 'I was abused as a child, more than once, and some of those who did it were my own relatives. A religion that could not protect me when I was helpless is not true.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'theodicy', 'identity' ),
			'sources'     => array(
				array( 'citation_text' => 'Sahih al-Bukhari, Kitab al-Mazalim: the instruction to aid one\'s brother whether he wrongs or is wronged, restraining the one who wrongs.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 4:75; 5:8; 4:135.', 'url' => '' ),
				array( 'citation_text' => 'Sexual Offences Against Children Act 2017 (Act 792); Child Act 2001 (Act 611), on the duty to report and the protection of child victims.', 'url' => '' ),
			),
			'body'        => '<p>Before anything else: what happened to the person making this argument was a crime, the men who did it are guilty of it, and nothing on this page is written to soften that. Anyone who answers this objection by reaching first for theology has stopped listening to the thing being said.</p>
<h3>Where the failure lies</h3>
<p>The complaint is addressed to God, and the evidence it contains is almost entirely about people. Men committed the abuse, some of them family. Other adults either did not see it or saw it and said nothing. The child had nobody to tell, or told and was not believed. Each of these is a human act or a human omission, and Islamic law names every one of them as a wrong.</p>
<p>The Prophet instructed Muslims to aid their brother whether he wrongs or is wronged, and when asked how one aids the wrongdoer, answered: by restraining him. The Qur\'an commands believers to stand firm for justice even against themselves and their own kin (4:135), and rebukes them for failing to fight on behalf of the oppressed, including children, who cry out for a protector (4:75). A household that shelters an abuser because he is a relative has broken these commands. It has not obeyed them.</p>
<h3>The silence that protects abusers</h3>
<p>What often surrounds such cases has a name across the Muslim world: <em>\'ayb</em> (shame), the family\'s reputation, protected by keeping the matter inside the house. The instinct is not peculiar to Muslims, and it has shielded abusers in every culture that practises it. It has no warrant in the religion. Islamic teaching on concealing faults concerns a person\'s private sins against God, and it has never been a licence to conceal a crime against a child. Child-protection law in many countries, Muslim-majority ones included, now says the same thing plainly, precisely because the silence had to be broken by statute.</p>
<h3>What this does not answer</h3>
<p>It leaves a harder question standing: why God permits such wrongs at all. That is the problem of suffering in its general form, and the companion site treats it at the length it deserves. This page makes a narrower claim. The failure described in this testimony was a failure of men and of a culture of silence, both of which the religion condemns in its own words. The charge has the right grief and the wrong defendant.</p>',
		),
		array(
			'slug'        => 'the-slide-from-hadith-to-nothing',
			'title'       => 'The slide from hadith to nothing',
			'ce_slug'     => 'hadith-reliability',
			'claim'       => 'The hadith are unreliable, so a thinking Muslim keeps the Qur\'an alone. Once you do that, the Qur\'an stops holding up as well, and then God goes too.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'hadith-authenticity', 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 59:7; 4:80; 16:44; 33:21.', 'url' => '' ),
				array( 'citation_text' => 'Kassim Ahmad, Hadis: Satu Penilaian Semula (Media Intelek, 1986), banned in Malaysia the same year; the founding text of the local Qur\'an-only position.', 'url' => '' ),
			),
			'body'        => '<p>This is less an argument than a route, and it is a common one: reject the hadith first, keep the Qur\'an for a while, then find the Qur\'an will not stand by itself, and end with nothing. The route deserves attention, because it proves something the people walking it did not intend to prove.</p>
<h3>The first step removes a load-bearing wall</h3>
<p>The Qur\'an does not present itself as a text meant to be read without its messenger. It instructs believers to take what the Messenger gives and refrain from what he forbids (59:7), states that whoever obeys the Messenger has obeyed God (4:80), and describes the Prophet as the one sent to explain to people what was revealed to them (16:44). A reader who removes the Sunnah (the Prophet\'s normative practice) has not purified the Qur\'an. He has set aside the part of the Qur\'an that commands him to keep it.</p>
<p>The consequence arrives quickly. The Qur\'an commands prayer repeatedly and nowhere specifies how many units, at what times, or in what form. Those details come from the Prophet\'s practice, transmitted by the whole community. The Qur\'an-only reader must either invent a prayer or stop praying, and most of those who begin down this road do the second.</p>
<h3>The Malaysian version of the road</h3>
<p>This country has seen the position argued openly, in a book published in 1986 and banned the same year, and in a small movement that grew around it. Its founding claim was that stripping away the hadith would leave a purer, more rational Islam. The testimonies of people who went further along the same road answer that claim better than any refutation: the purer Islam did not hold, because it had been cut away from what explained it.</p>
<h3>The verdict</h3>
<p>The slide is presented as evidence that Islam collapses under scrutiny. It is evidence that the hadith are load-bearing, which is what the tradition always said. Take them out and the structure falls, and the falling is a demonstration of the scholars\' position rather than a refutation of it. The honest question was always whether the hadith were transmitted reliably, and that is a question with a serious scholarly answer, set out in full on the companion site.</p>',
		),
		array(
			'slug'        => 'the-dare',
			'title'       => 'The dare',
			'claim'       => 'I have openly challenged God to punish me for leaving and for what I have said about Him. Nothing has happened. If He existed, He would have acted.',
			'source_type' => 'online-commentary',
			'topics'      => array( 'theodicy', 'reason-and-faith' ),
			'sources'     => array(
				array( 'citation_text' => 'Qur\'an 8:32-33; 22:47; 29:53.', 'url' => '' ),
				array( 'citation_text' => 'Qur\'an 3:178; 16:61; 35:45, on respite (imhal).', 'url' => '' ),
			),
			'body'        => '<p>This challenge is fourteen centuries old, and the Qur\'an quotes it. That fact settles most of what needs saying.</p>
<h3>The Qur\'an recorded the same dare</h3>
<p>The Makkan opposition issued it in almost the same words: O God, if this is indeed the truth from You, then rain stones down upon us from the sky, or bring us a painful punishment (8:32). The next verse answers it. The Qur\'an reports that they urged the punishment to be hastened (22:47, 29:53), and it does not treat the silence that followed as a problem for the revelation. It treats the silence as something the revelation had already explained.</p>
<h3>The claim being tested was never made</h3>
<p>The dare presumes that God, if He exists, punishes defiance on the spot. Islam teaches the opposite, and teaches it plainly. God grants <em>imhal</em>, respite: were He to seize people at once for their wrongdoing, He would leave no creature on the earth, but He defers them to an appointed term (16:61, 35:45). The respite is described as a test and, for those who persist, as a rope that lengthens (3:178). A doctrine of delayed accounting, stated in the text before the challenge was made, cannot be refuted by the observation that the accounting has been delayed.</p>
<p>So the experiment was designed to test a proposition nobody holds. Its result confirms what the Qur\'an predicted about exactly this situation, that the challenge would be made and that nothing visible would follow it.</p>
<h3>What this does not establish</h3>
<p>It does not prove that God exists, and it is not offered as a threat. Its claim is narrow: silence after a dare is not evidence against a God who announced in advance that He would answer defiance with patience. The person who issued the challenge is alive, has time, and remains free to reconsider. On the Qur\'an\'s own account, that is the whole point of the delay.</p>',
		),
	);
}


/**
 * Doubts. 'related' names a rebuttal slug ('' for none).
 *
 * @return array<int,array>
 */
function murtadd_seed_doubts() {
	return array(
		array(
			'slug'      => 'married-off-without-consent',
			'title'     => 'Married off without consent',
			'ce_slug'     => 'women-in-islam',
			'statement' => 'I was taught that a father or grandfather can marry a girl off to whoever he wants, without asking her.',
			'category'  => 'scriptural',
			'topics'    => array( 'women-in-islam' ),
			'related'   => 'marriage-requires-consent',
			'response'  => '<p>The claim is stated with confidence, and it is false. A marriage contracted without the woman\'s consent is invalid, and the earliest sources say so directly.</p>
<p>The Prophet annulled such a marriage. A woman came to him and reported that her father had given her in marriage against her wishes, and he declared the marriage void. That report sits in the most rigorously screened collection Muslims possess, al-Bukhari, at hadith 7:69. Abu Dawud and Ibn Majah record a parallel case in which a girl who had not married before was given the choice to uphold the marriage or dissolve it.</p>
<p>The Prophet also set the standard plainly: a previously married woman is not to be given in marriage until she is consulted, and a woman who has not married before is not to be given in marriage without her permission.</p>
<p>What a person may have witnessed in a family, or in a community, or in a legal system that carries a state\'s name, is a separate question from what the sources require. Coercion happens. It happens against the text rather than because of it.</p>
<p>If the objection is that scholars have argued over the mechanics of guardianship, that is true, and it is worth reading them. The disagreement concerns who signs and who advises. It does not concern whether a woman may be handed over silently, because on that the ruling is settled.</p>
<p>Whoever taught this, taught it wrongly. That is worth being angry about. It is a poor reason to conclude that the religion itself sanctions what it explicitly voids.</p>',
		),
		array(
			'slug'      => 'quran-4-34-wife-beating',
			'title'     => 'Qur\'an 4:34',
			'ce_slug'     => 'women-in-islam',
			'statement' => 'Qur\'an 4:34 says a man may beat his wife if she does not do what he wants. That is horrible.',
			'category'  => 'scriptural',
			'topics'    => array( 'moral-scriptural', 'women-in-islam' ),
			'related'   => 'quran-4-34-in-context',
			'response'  => '<p>Take the reaction seriously first. Anyone who reads a popular English rendering of this verse and feels revulsion is reading it the way it was handed to them.</p>
<p>The verse describes a sequence, and the sequence is aimed at reconciliation. It addresses a specific situation, a wife\'s <em>nushuz</em> (insolence, or a serious breach of the marital bond), and it prescribes stages: counsel, then withdrawal from the shared bed, and only then the third step. The verse closes by forbidding hostility if she seeks reconciliation.</p>
<p>Two things are usually lost. The first is that the passage is a de-escalation ladder with an explicit off-ramp at every rung. The second is that the third step was defined by the Prophet\'s own practice and by the jurists who followed it as something that leaves no mark and causes no pain, a symbolic act. The Prophet never struck a woman in his life. A husband who beats his wife is not executing this verse. He is violating the standard set by the man who delivered it.</p>
<p>English translation carries much of the blame here. A single word is rendered "beat" and the whole architecture collapses into a licence for violence.</p>
<p>A verse can be read to mean something monstrous when it is cut away from the practice that defines it, the jurisprudence that constrains it, and the reconciliation it exists to serve. Anyone can do that to any text. The question worth asking is whether the reading survives contact with the sources, and this one does not.</p>
<p>If a man in your life has used this verse to justify hurting someone, he was lying to you about his religion. Do not let him keep the verse.</p>',
		),
		array(
			'slug'      => 'womens-testimony-half',
			'title'     => 'Women as witnesses',
			'ce_slug'     => 'women-in-islam',
			'statement' => 'I learned that women cannot be witnesses in the Syariah courts, or that a woman\'s word counts for half of a man\'s.',
			'category'  => 'scriptural',
			'topics'    => array( 'women-in-islam' ),
			'related'   => 'women-as-witnesses',
			'response'  => '<p>The claim that women cannot testify is not a strong version of the argument. It is simply untrue, and the verse usually cited to support it says the opposite.</p>
<p>Qur\'an 2:282 concerns the recording of debt. It instructs the parties to call in two male witnesses, and if two men cannot be found, then one man and two women <strong>whom you judge fit to act as witnesses</strong>, so that if one errs the other can remind her. The verse names women as witnesses. It is difficult to build a prohibition out of a text that explicitly appoints them.</p>
<p>What the verse does establish is a specific arrangement for one specific transaction in one specific historical setting, a commercial debt contract in a society where women were, as a rule, kept out of commerce. The stated reason is reminding, not deficiency.</p>
<p>Scholars have noted that the verse\'s clear text applies to economic affairs and to criminal cases where <em>hudud</em> (fixed prescribed penalties) are involved. In matters concerning women exclusively, such as pregnancy, birth, and puberty, the evidence of a single woman is sufficient. That is the exact reverse of a rule that discounts her by half.</p>
<p>There is a real conversation to be had about how contemporary courts apply all of this, and about the gap between a legal principle and its handling by a particular judge in a particular building. That conversation deserves specifics.</p>
<p>What it does not deserve is a summary that misstates the verse it rests on.</p>',
		),
		array(
			'slug'      => 'hate-wearing-the-tudung',
			'title'     => 'The head-cover',
			'ce_slug'     => 'hijab-male-control-or-divine-command',
			'statement' => 'I hate wearing the tudung. It is hot, it is uncomfortable, and I cannot see what it is for.',
			'category'  => 'emotional',
			'topics'    => array( 'head-cover' ),
			'related'   => 'the-head-cover-and-modesty',
			'response'  => '<p>This one is worth separating into two questions, because they get answered as though they were one.</p>
<p>The first is whether the head-cover is difficult. Sometimes it is. Heat is real, discomfort is real, and there is no gain in pretending otherwise or in producing a lecture about women who manage it in harder climates. A hard thing is not made easier by being told it is easy.</p>
<p>The second is whether it is arbitrary, and that is the question being asked. The instruction in Qur\'an 24:31 is not addressed to women alone. One verse earlier, at 24:30, men are told to lower their gaze and guard their chastity. Both sexes carry a duty here. The command is a shared standard of modesty, and the version of the story in which women bear all of it and men bear none of it is a distortion that Muslim men have every reason to stop repeating.</p>
<p>It is also worth naming what the discomfort is often standing in for. When a woman is made to feel that her body is a public hazard, that policing her is other people\'s business, and that her worth is measured in fabric, the cloth becomes the symbol of something she is right to resent. That resentment is not a theological argument. It is a response to how she has been treated.</p>
<p>Those are different injuries, and they call for different answers. One is a question about a command. The other is a question about the people who enforced it. Do not let the second silently settle the first.</p>',
		),
		array(
			'slug'      => 'islam-and-violence',
			'title'     => 'Violence and terrorism',
			'ce_slug'     => 'muhammad-and-warfare',
			'statement' => 'I cannot follow a religion that keeps producing terrorists and calls itself merciful.',
			'category'  => 'intellectual',
			'topics'    => array( 'war-and-violence' ),
			'related'   => 'islam-and-terrorism',
			'response'  => '<p>The objection has weight, and it should not be waved away with a slogan.</p>
<p>Start with what the text says. Qur\'an 5:32 states that whoever kills one innocent soul has killed all humanity, and whoever saves one soul has saved all humanity. The Prophet explicitly forbade the killing of non-combatants, women, and children. Muslim organisations across the world condemned the attacks in New York and in London as violations of Islam\'s own fundamental principles.</p>
<p>Now the harder half. Men have committed atrocities while shouting the name of this religion, and pointing to a verse does not make their victims less dead. Anyone who answers this doubt by quoting 5:32 and then falling silent has answered nothing.</p>
<p>The verses that get cited to justify indiscriminate killing are verses about war, addressed to particular circumstances, hedged by conditions. Islamic law permits the use of force under a legitimate authority, in defence against persecution, oppression, and the seizure of land. It does not authorise a man with a bomb to appoint himself an army. The extremist and the polemicist arrive at the same reading of these verses, and they need each other to sustain it.</p>
<p>Every tradition with a billion adherents contains people who disgrace it. The question is whether the disgrace follows from the teaching or violates it, and that question is answerable by reading the teaching.</p>
<p>The reader who is repelled by violence carried out in Islam\'s name is responding to something real. That instinct is not evidence against the faith. It is the instinct the faith asks for.</p>',
		),
		array(
			'slug'      => 'believe-without-knowing',
			'title'     => 'Belief without knowing',
			'ce_slug'     => 'doubt-permitted-in-islam',
			'statement' => 'I asked my teacher whether we just have to believe without knowing if any of it is true. He said yes.',
			'category'  => 'intellectual',
			'topics'    => array( 'reason-and-faith' ),
			'related'   => 'is-islamic-faith-irrational',
			'response'  => '<p>A bad answer from a teacher is a bad answer from a teacher. It is not the position of the tradition, and it deserves to be corrected rather than defended.</p>
<p>Faith in the Islamic sense has never meant assent without grounds. It has been understood as a conviction with a moral and an intellectual basis, an affirmation of a truth together with surrender to the truth affirmed. Strip away the first and it is blind. Strip away the second and it costs nothing. Both are required, and the tradition has said so consistently.</p>
<p>The underlying assumption in the objection is worth examining as well. It runs: if something cannot be perceived directly, the reasonable conclusion is that it does not exist. That principle would not survive an afternoon in a physics department. A great deal of what is known is inferred rather than seen, and the absence of a particular kind of evidence is not itself evidence of absence.</p>
<p>None of which proves the case. It clears the ground, which is a different and more modest thing.</p>
<p>What is worth saying plainly is that a religious teacher who tells a child to stop asking has failed at the one task he was given. Islam produced centuries of scholars who argued about metaphysics, law, logic, and the natural world, and who were held in high esteem for exactly that. A classroom that treats a question as insubordination is not transmitting that inheritance. It is losing it.</p>
<p>Ask the question again. Ask it of someone who can answer it.</p>',
		),
		array(
			'slug'      => 'good-friends-were-never-muslim',
			'title'     => 'Friends, and hell',
			'ce_slug'     => 'do-good-non-muslims-go-to-hell',
			'statement' => 'My kindest friends were never Muslim. Am I supposed to believe they are all going to hell?',
			'category'  => 'identity',
			'topics'    => array( 'identity' ),
			'related'   => 'are-all-non-muslims-damned',
			'response'  => '<p>Nobody is in a position to tell you where your friends are going, and anyone who claims to be is overreaching.</p>
<p>Judgment belongs to God. No teacher, no relative, and no writer has been given the roll. The Qur\'an is explicit that there is no compulsion in religion, at 2:256, and it is equally explicit that the reckoning is God\'s alone. A person who assigns your friends to the Fire has assumed an authority that was never delegated to them.</p>
<p>The category the polemic turns on is <em>kafir</em> (one who rejects the truth), and it is not a synonym for "non-Muslim". It describes a posture toward truth that is knowingly refused. Whether any given person occupies it is not something a stranger can determine, and it is not something you were ever required to determine about the people you love.</p>
<p>It is also worth noticing what the objection quietly assumes: that the non-Muslim friends were the good people, and the Muslims were the bad ones. That may be an accurate report of a particular childhood. It is not a fact about two billion people, and a conclusion about a faith built on a sample of the individuals who were unkind to you is a conclusion about them.</p>
<p>You can hold your friends in the highest regard, be grateful to them, count them among the closest people in your life, and remain a Muslim. Nothing in the religion requires you to choose. Anyone who told you otherwise handed you a false choice, and you are permitted to hand it back.</p>',
		),
		array(
			'slug'      => 'hadith-contain-inhumane-things',
			'title'     => 'The hadith',
			'ce_slug'     => 'hadith-reliability',
			'statement' => 'The hadith are full of inhumane and absurd material. I do not think they can be taken as true.',
			'category'  => 'scriptural',
			'topics'    => array( 'hadith-authenticity' ),
			'related'   => 'were-monkeys-stoned-for-adultery',
			'response'  => '<p>This objection is almost always made in the abstract, and it collapses the moment specifics are supplied.</p>
<p>The hadith corpus is not a single undifferentiated body of claims. It is a graded literature, and the grading is the entire point. Muslim scholars developed <em>ulum al-hadith</em> (the sciences of hadith) precisely because they took the problem of forged and unreliable reports seriously, centuries before anyone raised it as a polemic. A report is assessed by its <em>matn</em> (text) and its <em>isnad</em> (chain of transmission), and it is classified accordingly.</p>
<p>That machinery does real work. Take the report about monkeys stoning another monkey for adultery, which circulates as a showcase absurdity. Read it in the collection and the speaker is a Companion, describing what he witnessed and believed <strong>before</strong> Islam, during the age of ignorance. The report is <em>mawquf</em> (stopped), meaning it traces to a Companion and not to the Prophet. It is not a saying of the Prophet, it is not ascribed to him, and it cannot ground a ruling. The absurdity is being reported, not endorsed.</p>
<p>Somebody quoting that report as a scandal has either not opened the science, or is relying on you not opening it.</p>
<p>This is not a claim that every report in every collection is beyond discussion, and Muslims have argued about individual traditions for a very long time. It is a claim that "the hadith are full of nonsense" is a conclusion reached by skipping the field that exists to answer it. Bring a specific hadith. It can be examined.</p>',
		),
		array(
			'slug'      => 'afraid-of-what-happens-if-i-leave',
			'title'     => 'The fear itself',
			'ce_slug'     => 'apostasy-and-freedom',
			'statement' => 'I am afraid of what happens to me if I leave. I have read that the punishment is death, and the fear will not let go of me.',
			'category'  => 'identity',
			'topics'    => array( 'identity' ),
			'related'   => '',
			'response'  => '<p>Start with the part of this that concerns you tonight, because it can be answered plainly. Having doubts is not an offence anywhere on earth. Reading this page is not an offence. Asking questions, privately or aloud, is not an offence. No law in any country reaches into a person\'s thoughts, and whatever you have read, nobody is coming for you because you are unsure.</p>
<p>The law varies by country, and it is worth knowing your own rather than trusting what circulates online. In most Muslim-majority states leaving Islam carries no criminal penalty; where legal consequences exist, they usually engage only when a person formally seeks to change a registered religion, and many concern procedure and family law. Contested cases have been fought in the courts over documentation and jurisdiction. None of this touches a person who is doubting, questioning, or reading.</p>
<p>The classical juristic position you encountered is real, and it is not the whole story. It was formed when leaving the religion meant defecting from a political community at war, and jurists across the centuries attached conditions, waiting periods, and distinctions to it. Contemporary scholars, including at major institutions, have argued that the ruling attaches to treason against a polity rather than to private belief, and that the Qur\'anic principle that there is no compulsion in religion (2:256) governs. This is a live scholarly conversation, and anyone who told you it was closed has overstated their case in both directions.</p>
<p>Now the part that matters more. The fear you are describing is evidence about you, and what it evidences is that you have not left. A person who has walked away does not lie awake over the verdict of a tradition they no longer hold. The dread is attachment. Treat it as information, not as a sentence.</p>
<p>Do not carry this alone at 2am. Speak to one person you trust: a friend, a teacher whose knowledge you respect, anyone who has earned it. The fear shrinks when it is said aloud, and it grows in the dark. Your life is not the debate.</p>',
		),
		array(
			'slug'      => 'why-does-the-ummah-feel-broken',
			'title'     => 'The broken ummah',
			'ce_slug'     => 'islam-and-enlightenment',
			'statement' => 'If this religion is true, why is the Muslim world in the state it is in? The decline feels like evidence.',
			'category'  => 'intellectual',
			'topics'    => array( 'reason-and-faith' ),
			'related'   => 'the-state-of-muslims-argument',
			'response'  => '<p>The observation is accurate and the inference is not, and it helps to hold those apart.</p>
<p>The decline is real. Muslim thinkers have said so more bluntly than any polemicist: the intellectual and methodological decline of the ummah is the core of the malaise, the educational systems are caricatures of imported models, and centuries of stagnation have spread illiteracy and superstition where scholarship used to be. Nobody serious disputes the diagnosis.</p>
<p>The question is what the diagnosis is evidence of. A community\'s condition indicts its creed only if the creed produced the condition, and the history runs in the opposite direction: the civilisation declined as it abandoned its own intellectual tradition, its educational depth, and the balance between the knowledge every person owes (<em>fard al-\'ayn</em>) and the knowledge a community holds collectively (<em>fard al-kifayah</em>). The height of Islamic civilisation and the depth of Islamic learning were the same centuries. That is a strange fact for the religion to explain if the religion is the disease.</p>
<p>Test the inference on anything else. Every tradition on earth has presided over a nadir, and if the worst centuries falsify the creed, nothing anyone has ever believed survives. An argument that eliminates every position including its own is not evidence. It is a mood.</p>
<p>The full rebuttal below walks through the diagnosis properly, with the sources. The condition of the ummah is a reason for grief and for work. It is a poor reason for a verdict.</p>',
		),
		array(
			'slug'      => 'i-studied-islam-and-still-left',
			'title'     => 'I studied it, and I still left',
			'ce_slug'     => 'left-because-of-specific-problems',
			'statement' => 'I studied Islam in school for years. I know it well. That is exactly why I cannot accept it.',
			'category'  => 'intellectual',
			'topics'    => array( 'reason-and-faith' ),
			'related'   => 'i-studied-it-in-school',
			'response'  => '<p>Take the claim seriously, because it is usually made sincerely, and then test it the way any claim should be tested.</p>
<p>Years in a classroom is real exposure. Whether it produced knowledge of the religion is a separate question, and it has an answer, because the claim is checkable. A person who knows the religion well will describe its rulings accurately.</p>
<p>So look at what tends to follow. That a woman may be married off without her consent: the Prophet annulled such a marriage, and the report is in al-Bukhari. That women cannot testify: the verse cited to prove it appoints them as witnesses. That the Qur\'an licenses beating a wife into obedience: the verse is a sequence aimed at reconciliation, and the man who delivered it never struck a woman in his life.</p>
<p>Each of these is contradicted by the sources, and none of the contradictions is obscure. Which raises the real question: what exactly was taught in that classroom?</p>
<p>The honest answer is that a great many Muslims are handed a version of their religion that is thin, poorly argued, and delivered by someone who could not answer a hard question and resented being asked one. That is a genuine failure, and it belongs to the teaching. A person who rejected that version rejected something indefensible. They did not reject Islam. They were never shown it.</p>
<p>You are entitled to examine the religion and reject it. What is not available is rejecting a caricature and calling the verdict informed. Bring one specific ruling you were taught, and check it against the source. That is the whole invitation.</p>',
		),
		array(
			'slug'      => 'muslims-cannot-even-agree',
			'title'     => 'Nobody agrees',
			'statement' => 'Muslims cannot even agree with each other. There are dozens of sects, each saying the others are wrong. How can any of it be true?',
			'category'  => 'intellectual',
			'topics'    => array( 'reason-and-faith' ),
			'related'   => 'muslims-cannot-agree',
			'response'  => '<p>The frustration behind this is usually real and rarely abstract. Someone was told their practice was wrong, or watched two people who both claimed the religion insist the other had left it.</p>
<p>But look at what the argument assumes: that a true religion would produce uniform agreement among its adherents. Nobody ever promised that, and the Prophet in fact predicted the opposite: that the community would divide into many groups. The number reported, seventy-three, is an idiom for multiplicity rather than a headcount, in the way "a thousand" works in older speech. A religion whose founder said the community would fracture is not refuted by the fracture.</p>
<p>Most of what gets counted as division is jurisprudence: differences over how rulings are derived. Four Sunni legal schools have coexisted for more than a thousand years, each treating the others as valid, and a Muslim may follow any of them. That is a method for holding disagreement, built deliberately, and it is the opposite of collapse.</p>
<p>Then test the argument itself. Christianity has thousands of denominations. Judaism, Buddhism, and every school of philosophy have split comparably. If internal disagreement falsifies a position, then nothing anyone has ever believed survives, and the objection takes itself down with everything else.</p>
<p>People disagreeing about a thing is evidence about people. It is not evidence about the thing. The men shouting at each other are not the religion, and you were never required to take them as its representatives.</p>',
		),
		array(
			'slug'      => 'websites-confirmed-my-doubts',
			'title'     => 'The websites',
			'ce_slug'     => 'the-algorithm-that-deconverted-you',
			'statement' => 'I read the criticism websites, then checked the Qur\'an for myself, and everything I checked seemed to confirm what they said.',
			'category'  => 'intellectual',
			'topics'    => array( 'testimony-patterns', 'reason-and-faith' ),
			'related'   => 'the-polemical-echo-chamber',
			'response'  => '<p>What you did felt like verification, and the feeling was honest. Look at the sequence, though. The claims went in first, and the reading happened with them in hand. A conclusion carried into a text will be found in the text; that is what carrying it there does.</p>
<p>Here is a fair test of whether it was checking or confirming: for the objections that landed hardest, did you ever read the tradition\'s answer to them, in full, from the people who hold it? Nearly every argument on those sites is old, and the answers have been in print as long as the arguments have. If you never met the answers, the search was half a search, and half a search cannot confirm anything.</p>
<p>This is not a request to stop reading. It is a request to finish. Read the other shelf with the same seriousness you gave the first one, and then decide. The full rebuttal below walks through the method, and the rest of this site is the other shelf.</p>',
		),
		array(
			'slug'      => 'the-miracles-argument-collapsed',
			'title'     => 'The miracles argument',
			'ce_slug'     => 'scientific-miracles-quran',
			'statement' => 'I was raised on the scientific miracles of the Qur’an. Then I watched that argument get taken apart, and it felt like the floor went with it.',
			'category'  => 'intellectual',
			'topics'    => array( 'reason-and-faith' ),
			'related'   => 'the-scientific-miracles-genre',
			'response'  => '<p>The argument you watched fail deserved to fail, and Muslim scholars said so before the critics did. Reading each new discovery back into the verses was always a fragile way to argue: the moment the science moved, the "miracle" was stranded. You are allowed to let that argument go. We let it go too.</p>
<p>What matters is what the argument was standing in for. The Qur\'an never staked its claim on predicting laboratory results. Its actual challenge concerns its own inimitability, and the classical case for it is about language, structure, and history, a case the miracles genre displaced in popular teaching because it was easier to present at speed.</p>
<p>So the floor you lost was scaffolding, put up in living memory, and the building was never resting on it. The full rebuttal below concedes what should be conceded and shows what carries the weight.</p>',
		),
		array(
			'slug'      => 'the-hadith-about-leaving',
			'title'     => 'The hadith about leaving',
			'ce_slug'     => 'kill-him-who-changes-religion',
			'statement' => 'I keep coming back to the hadith that whoever changes his religion should be killed. I cannot read it as anything except a threat aimed at me.',
			'category'  => 'scriptural',
			'topics'    => array( 'apostasy-law' ),
			'related'   => 'apostasy-and-the-classical-law',
			'response'  => '<p>Start with tonight. Doubting is an offence nowhere. Questioning is an offence nowhere. Reading this page is an offence nowhere. Whatever that sentence has come to mean in your mind at 2am, it does not reach a person who is unsure, and you are not what it is about.</p>
<p>The ruling you encountered is real and it has a context: it was formed when leaving the religion meant defecting from a political community at war, and the jurists who transmitted it attached conditions and distinctions to it across the centuries. Every pre-modern confessional state, Christian ones very much included, treated defection from the faith as treason. That does not settle the question, and it is why the question is debated among Muslim scholars today, with serious voices arguing the ruling concerns treason against a polity rather than private belief, under the Qur\'anic principle that there is no compulsion in religion.</p>
<p>The rebuttal below takes the history properly. The fear itself is a separate matter, and it deserves its own page on this site; do not carry it alone.</p>',
		),
		array(
			'slug'      => 'leaving-would-cost-me-my-family',
			'title'     => 'The cost to my family',
			'ce_slug'     => 'social-cost-of-leaving',
			'statement' => 'If I go through with this I lose my mother. That is not a theological problem, and no argument about the Qur’an touches it.',
			'category'  => 'emotional',
			'topics'    => array( 'identity' ),
			'related'   => '',
			'response'  => '<p>You are right that no argument touches it, and anyone who answers this by handing you a verse has not heard what you said.</p>
<p>This is the most common thing in the accounts of people who have left, more common than any argument about scripture or science. What surfaces again and again is not a refuted doctrine; it is a mother\'s face, a house one is no longer invited to, a grandmother writing letters. Older apologetic writing brushed this aside as though being cut off by one\'s family were a minor detail. It is not a minor detail. It is usually the whole weight of the thing.</p>
<p>Two things can be said plainly. The first is that the rupture you are imagining is real for some people and not for others, and you cannot know in advance which you are facing. Families surprise people in both directions. The second is that this cost is separate from the question of whether Islam is true. If you become convinced it is false, the cost does not make it true. If you remain convinced it is true, fear of that cost is a poor reason to stay, and a faith held to keep the peace at home is not the thing this site is defending.</p>
<p>What you should not do is carry this alone, or let the size of it force a decision either way tonight. Talk to someone who knows you and is not invested in the outcome. If there is a person in your family who has ever been gentle with a hard question, start there.</p>',
		),
		array(
			'slug'      => 'losing-the-whole-world-not-just-the-belief',
			'title'     => 'Losing the whole world',
			'ce_slug'     => 'post-muslim-identity',
			'statement' => 'It is not only belief I would be giving up. It is Ramadan, the congregation, the people who came when my father died. I do not know who I would be without it.',
			'category'  => 'identity',
			'topics'    => array( 'identity' ),
			'related'   => '',
			'response'  => '<p>What you are describing is not a weakness in your reasoning. It is an accurate account of what a religion is.</p>
<p>Islam is not only a set of propositions to be assented to. It is a calendar, a body of practice, a language for grief, and the people who arrive at the door when someone dies. Anyone who tells you that leaving is simply a matter of updating a belief has not thought about it seriously, and neither has anyone who tells you that this belonging, by itself, settles whether the belief is true.</p>
<p>Hold the two apart, because they come apart. The question of whether the <em>deen</em> is true is one question. What it would cost you to lose the world built around it is another, and a real one. People do leave and rebuild a life; people also stay for the belonging and find the belief has quietly gone. Neither of those is what this site is asking of you.</p>
<p>What it does ask is that you do not let the fear of that loss stop you from examining the belief itself, carefully and without hurry. If the belief holds, the world around it is not a consolation prize; it is the point. If you are not sure yet, then you are not sure yet, and there is no deadline. Uncertainty is a place you are allowed to stand in for a while.</p>',
		),
		array(
			'slug'      => 'i-was-never-asked',
			'title'     => 'I was never asked',
			'ce_slug'     => 'when-religion-was-imposed-not-discovered',
			'statement' => 'I did not choose any of this. It was on my identity card before I could read, and the first time I said the words I was a child repeating them.',
			'category'  => 'identity',
			'topics'    => array( 'identity' ),
			'related'   => 'religion-by-registration',
			'response'  => '<p>You are describing something real, and it is worth being precise about what it is, because the precision helps.</p>
<p>A form recorded you as Muslim. A school taught you the words. Neither of those things is <em>iman</em>, and the tradition you are describing says so before any critic does. Faith requires <em>tasdiq</em>, the assent of the heart, and no registry has ever been able to issue that or take it away. The entry on your card is an administrative fact about the Malaysian state. Whether you believe is a different question, and it is still open, which is uncomfortable and is also the point: nobody has answered it for you, however much it may have felt that way.</p>
<p>So the thing you are angry about may not be the thing you think you are leaving. Being enrolled without being asked is an injury, and Islam\'s own position is that a compelled profession is worth nothing. If you were handed the religion as a fact about your ethnicity rather than as a claim you could examine, then something was withheld from you, and what was withheld was the choice you are only now being offered.</p>
<p>Take it seriously in both directions. You are free to examine this and conclude it is false. You are also free to examine it and find that you assent, which would be the first time it was ever yours. What you should not do is treat a decision made for you at birth as though it settles anything, in either direction.</p>',
		),
		array(
			'slug'      => 'i-was-hurt-and-nobody-helped',
			'title'     => 'I was hurt and nobody helped',
			'ce_slug'   => 'religious-trauma',
			'statement' => 'Something was done to me as a child by people who prayed and fasted, and nobody around me did anything. I cannot separate that from the religion.',
			'category'  => 'emotional',
			'topics'    => array( 'identity', 'theodicy' ),
			'related'   => '',
			'response'  => '<p>What was done to you was wrong, it was not your fault, and you do not have to settle any question about religion before you are allowed to be angry about it.</p>
<p>If any part of this is still happening, or if you are carrying it alone, please tell someone safe. Most countries have a child-protection or abuse helpline that also takes calls about abuse from long ago; in Malaysia, for example, it is Talian Kasih (15999). A counsellor or doctor you trust can help you decide what to do next. None of that requires you to have made up your mind about anything else.</p>
<p>It makes complete sense that you cannot separate what happened from the religion. The people who hurt you wore it, and the people who stayed silent wore it too, and a child has no way of distinguishing the faith from the adults who claim it. You may find, in time, that the separation becomes possible: that the religion condemned what they did, and that their prayer did not make them right. You may not. Either way, that is a question for later, and for you to answer at your own pace.</p>
<p>For now, the one thing worth holding onto is simple. Their failure is theirs. It was never a verdict on you.</p>',
		),
	);
}

/**
 * Placeholder letters.
 *
 * Lorem ipsum, so the Letters templates render and the layout can be reviewed
 * before any real correspondence is filed. Both paths are exercised: one letter
 * carries an Editor's note, one does not.
 *
 * These publish immediately and say plainly what they are. Replace or delete
 * them before announcing the site.
 *
 * @return array<int,array>
 */
function murtadd_seed_letters() {
	return array(
		array(
			'slug'        => 'placeholder-letter-one',
			'title'       => 'Placeholder letter (with editor\'s note)',
			'outlet'      => 'Placeholder Publication',
			'date'        => '2004-12-08',
			'replying_to' => 'Placeholder: the title of the piece being answered',
			'url'         => '',
			'note'        => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. This field is where the site speaks in its present voice above an archived text: standing context, or a note on where the position has since developed. Replace this placeholder, or clear the field to hide the note entirely.</p>',
			'topics'      => array( 'reason-and-faith' ),
			'body'        => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>',
		),
		array(
			'slug'        => 'placeholder-letter-two',
			'title'       => 'Placeholder letter (no editor\'s note)',
			'outlet'      => 'Placeholder Publication',
			'date'        => '2004-12-17',
			'replying_to' => '',
			'url'         => '',
			'note'        => '',
			'topics'      => array( 'reason-and-faith' ),
			'body'        => '<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
<p>Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio.</p>
<p>Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.</p>',
		),
	);
}
