<?php

require_once 'class.abstractcontroller.php';

function sanitizeInt($str)
{
    return filter_var($str, FILTER_SANITIZE_NUMBER_INT);
}


class States
{
	private $states;
	
	function __construct($stateNames, $init)
	{
		$this->states = array();
		if ($init) {
			foreach ($stateNames as $stateName) {
				if (isset($_GET[$stateName]))
					$this->states[$stateName] = $_GET[$stateName];
			}
		}
	}
	
	function getState($stateName)
	{
		return $this->states[$stateName];
	}
	
	function clearState($stateName)
	{
		unset($this->states[$stateName]);
	}
	
	function setState($stateName, $stateValue)
	{
		$this->states[$stateName] = $stateValue;
	}
	
	function clear()
	{
		$this->states = array();
	}
	
	function isEmpty()
	{
		return count($this->states) == 0;
	}
	
	function isStateSet($stateName)
	{
		return isset($this->states[$stateName]);
	}
	
	function echoLink($linkName, $pageID)
	{
		$link = "?page_id=$pageID";
		
		foreach ($this->states as $stateName => $stateValue) {
			$link .= '&'.$stateName.'='.$stateValue;
		}
		
		echo "<a href=\"$link\">$linkName</a>";
	}
	
	function __toString()
	{
		
		return print_r($this->states, true);
	}
}

class Category
{
	private $name;
	private $showAs;
	private $values;
	
	function __construct($name, $showAs, $values)
	{
		$this->name = $name;
		$this->showAs = $showAs;
		$this->values = $values;
	}
	
	function getShowAs()
	{
		return $this->showAs;
	}
	
	function getValue($index)
	{
		if ($index < 1 or $index > $this->getNumValues())
			return '';
		
		return $this->values[$index-1];
	}
	
	function getNumValues()
	{
		return count($this->values);
	}
}

class PlantView2Controller extends AbstractController
{
	private $stateNames = array('kategori', 'farg', 'blomtid', 'hojd', 'lage', 'jordman', 'sortering', 'vy', 'sok');
	private $states;
	private $categories;
	
	function __default()
	{
		$this->states = new States($this->stateNames, true);
		
		$this->_initCategories();
		
		$view = $this->states->getState('vy');
		
		if ($view == 2)
			$this->_showView2();
		else if ($view == 3)
			$this->_showView3();
		else // view 1 is default start view
			$this->_showView1();
	}

	function _initCategories()
	{
		$this->categories = array();
		$this->categories['kategori'] = new Category('kategori', 'Typ',
			array('Bladväxter', 'Bra till snitt', 'Doftande', 'Gräs', 'Kryddväxter', 'Lättskötta', 'Marktäckare', 'Ormbunkar', 'Pioner', 
				'Stenparti', 'Trivs i kruka')
		);
		$this->categories['farg'] = new Category('farg', 'Färg', array('Vit', 'Gul', 'Grön', 'Blå', 'Lila', 'Purpur', 'Rosa', 'Röd', 'Brun', 'Orange', 'Blandade'));
		$this->categories['blomtid'] = new Category('blomtid', 'Blomtid', array('Vår', 'Sommar', 'Höst'));
		$this->categories['hojd'] = new Category('hojd', 'Höjd', array('Låga', 'Mellan', 'Höga'));
		$this->categories['lage'] = new Category('lage', 'Läge', array('Sol', 'Halvskugga', 'Skugga'));
		$this->categories['jordman'] = new Category('jordman', 'Jordmån', array('Torr', 'Fuktig', 'Väldränerad'));
	}

	function _showView1Category($name, $value, $imageFilename)
	{
		$html = <<<EOD
<div style="width:215px;height:165px;float:left;margin-right:10px;margin-bottom:10px;"><img src="/wp-content/categoryimages/$imageFilename" /><div style="color:white;background-color:blue;width:215px;height:25px;">$name</div></div>
EOD;
		$newStates = new States($this->stateNames, false);
		$newStates->setState('vy', 2);
		$newStates->setState('kategori', $value);
		$newStates->echoLink($html, $this->_pageID);
	}
	
	function _showView1()
	{
		echo <<<EOD
<div>
    <form method="GET" action="/" >
        <input type="hidden" name="page_id" value="{$this->_pageID}" />
        <input type="hidden" name="vy" value="2" />
        <input type="text" class="field" placeholder="Ange det svenska eller latinska namnet..." id="sok" name="sok" size="20" /><input type="submit" name="submit1" value="Sök" />
    </form>
</div>
<div style="min-width:225px;max-width:675px;">
EOD;

		// show Alla perenner-category		
		$html = '<div style="max-width:215px;max-height:165px;float:left;margin-right:10px;margin-bottom:10px;"><img src="/wp-content/categoryimages/1.jpg" /><div style="color:white;background-color:blue;width:215px;height:25px;">Alla perenner</div></div>';
		$newStates = new States($this->stateNames, false);
		$newStates->setState('vy', 2);
		$newStates->echoLink($html, $this->_pageID);

		$categoryFilenames = array('1.jpg', '1.jpg', '1.jpg',
			'1.jpg', '1.jpg', '1.jpg', '1.jpg',
			'1.jpg', '1.jpg', '1.jpg', '1.jpg'
		);

		$category = $this->categories['kategori'];
		$n = $category->getNumValues();
		for ($i = 1; $i <= $n; $i++)
			$this->_showView1Category($category->getValue($i), $i, $categoryFilenames[$i-1]);

		echo <<<EOD
<div class="clear"></div>
</div>
EOD;
	}

	function _showView2()
	{
		echo '<div>';
		
		$this->_showFilter();
		$this->_showView2SearchResult();
		
		echo '<div class="clear"></div>';
		
		echo '</div>'; // end of v2Main
	}

	function _showView3()
	{
		$artikelID = sanitizeInt($_GET['artikelID']);
		
		$sql = "SELECT latinsktNamn, svensktNamn, hojd, blomtid, vaxtplats, egenskaper, hardighet, plantorPerM2 FROM artikel WHERE id=$artikelID";
		
		$result = $this->_mysqli->query($sql);
		if (!$result) {
			echo "Databasfel! (query fel)";
			return;
		}
		$row = $result->fetch_assoc();
		if (!$result) {
			echo "Databasfel! (ingen post)";
			return;
		}
		
		$latinsktNamn = htmlspecialchars($row['latinsktNamn']); // break line at first single quote
		$pos = strpos($latinsktNamn, "'", 1);
		if ($pos !== false) {
			$latinsktNamn = substr_replace($latinsktNamn, "<br>'", $pos, 1);
		}
		
		$svensktNamn = htmlspecialchars($row['svensktNamn']);
		$hojd = htmlspecialchars($row['hojd']);
		$blomtid = htmlspecialchars($row['blomtid']);
		$vaxtplats = htmlspecialchars($row['vaxtplats']);
		$egenskaper = htmlspecialchars($row['egenskaper']);
		$hardighet = htmlspecialchars($row['hardighet']);
		$plantorPerM2 = htmlspecialchars($row['plantorPerM2']);
		if ($plantorPerM2 != '')
			$plantorPerM2 .= '&nbsp;st';

		$filenames = array("$artikelID.jpg", "$artikelID-1.jpg", "$artikelID-2.jpg", "$artikelID-3.jpg", "$artikelID-4.jpg");
		$filenames2 = array();
		foreach ($filenames as $filename) {
			if ($this->_imageExists($filename))
				$filenames2[] = $filename;
		}
		

		echo '<div id="v3Main">';
		// bread crumbs
		// echo '<div id="v3Breadcrumbs"><a href="/">Hem</a>&nbsp;&gt;&gt;&nbsp;<a href="?page_id='.$this->_pageID.'">Våra perenner</a>&nbsp;&gt;&gt;&nbsp;<a href="?page_id='.$this->_pageID.'&vy=2">Växtsök</a>&nbsp;&gt;&gt;&nbsp;Detaljerad vy</div>';
		echo '<div id="v3Breadcrumbs"><a href="/">Hem</a>&nbsp;&gt;&gt;&nbsp;<a href="?page_id='.$this->_pageID.'">Våra perenner</a>&nbsp;&gt;&gt;&nbsp;<a href="javascript:history.back()">Växtsök</a>&nbsp;&gt;&gt;&nbsp;Detaljerad vy</div>';

		echo <<<EOD
<div id="v3MainBox">
<div id="v3SidebarLeft"></div>
<div id="v3Box">
<div id="v3ImageBox">
<div id="v3LargeImageBox">
	<img id="v3MainImage" src="images/article/stora/{$filenames2[0]}"/><div id="v3LeafBox"><div id="v3LeafTextBox">$artikelID</div></div>
</div>
<div id="v3SmallImagesBox">
EOD;
	
	$firstImage = true;
	$index = 1;
	$numImages = count($filenames2);
	foreach ($filenames2 as $filename) {
		if ($firstImage)
			$class = "v3ImageSelected";
		else
			$class = "v3ImageNotSelected";
		echo "<img id=\"v3SmallImage$index\" class=\"$class\" src=\"images/article/sma/$filename\" style=\"cursor:pointer\" onclick=\"doImageClick('images/article/stora/$filename', $index, $numImages)\" />";
		$firstImage = false;
		$index++;
	}
	echo <<<EOD
</div>
</div>
<div id="v3TextBox">
	<div id="v3TitleBox">
		<span class="v3LatTitle">$latinsktNamn</span><br>
		<span class="v3SweTitle">$svensktNamn</span>
	</div>
	<hr class="v3Rule">
	<p><span class="v3PropTitle">HÖJD&nbsp;</span>$hojd cm</p>
	<p><span class="v3PropTitle">BLOMNING&nbsp;</span>$blomtid</p>
	<p><span class="v3PropTitle">VÄXTPLATS&nbsp;</span>$vaxtplats</p>
	<p><span class="v3PropTitle">EGENSKAPER&nbsp;</span>$egenskaper</p>
	<p><span class="v3PropTitle">HÄRDIGHET&nbsp;</span>$hardighet</p>
	<p><span class="v3PropTitle">PLANTOR&nbsp;PER&nbsp;M2&nbsp;</span>$plantorPerM2</p>
</div>
</div>
</div>
</div>
EOD;

		$result->free();
	}

	function _imageExists($filename)
	{
		$pathname = "/home/u/u6443829/www/images/article/stora/$filename";
		
		return file_exists($pathname); 
	}

	function _isMySqlStopword($word)
	{
	$mysqlStopwords = array(
	  "a",
	  "a's",
	  "able",
	  "about",
	  "above",
	  "according",
	  "accordingly",
	  "across",
	  "actually",
	  "after",
	  "afterwards",
	  "again",
	  "against",
	  "ain't",
	  "all",
	  "allow",
	  "allows",
	  "almost",
	  "alone",
	  "along",
	  "already",
	  "also",
	  "although",
	  "always",
	  "am",
	  "among",
	  "amongst",
	  "an",
	  "and",
	  "another",
	  "any",
	  "anybody",
	  "anyhow",
	  "anyone",
	  "anything",
	  "anyway",
	  "anyways",
	  "anywhere",
	  "apart",
	  "appear",
	  "appreciate",
	  "appropriate",
	  "are",
	  "aren't",
	  "around",
	  "as",
	  "aside",
	  "ask",
	  "asking",
	  "associated",
	  "at",
	  "available",
	  "away",
	  "awfully",
	  "b",
	  "be",
	  "became",
	  "because",
	  "become",
	  "becomes",
	  "becoming",
	  "been",
	  "before",
	  "beforehand",
	  "behind",
	  "being",
	  "believe",
	  "below",
	  "beside",
	  "besides",
	  "best",
	  "better",
	  "between",
	  "beyond",
	  "both",
	  "brief",
	  "but",
	  "by",
	  "c",
	  "c'mon",
	  "c's",
	  "came",
	  "can",
	  "can't",
	  "cannot",
	  "cant",
	  "cause",
	  "causes",
	  "certain",
	  "certainly",
	  "changes",
	  "clearly",
	  "co",
	  "com",
	  "come",
	  "comes",
	  "concerning",
	  "consequently",
	  "consider",
	  "considering",
	  "contain",
	  "containing",
	  "contains",
	  "corresponding",
	  "could",
	  "couldn't",
	  "course",
	  "currently",
	  "d",
	  "definitely",
	  "described",
	  "despite",
	  "did",
	  "didn't",
	  "different",
	  "do",
	  "does",
	  "doesn't",
	  "doing",
	  "don't",
	  "done",
	  "down",
	  "downwards",
	  "during",
	  "e",
	  "each",
	  "edu",
	  "eg",
	  "eight",
	  "either",
	  "else",
	  "elsewhere",
	  "enough",
	  "entirely",
	  "especially",
	  "et",
	  "etc",
	  "even",
	  "ever",
	  "every",
	  "everybody",
	  "everyone",
	  "everything",
	  "everywhere",
	  "ex",
	  "exactly",
	  "example",
	  "except",
	  "f",
	  "far",
	  "few",
	  "fifth",
	  "first",
	  "five",
	  "followed",
	  "following",
	  "follows",
	  "for",
	  "former",
	  "formerly",
	  "forth",
	  "four",
	  "from",
	  "further",
	  "furthermore",
	  "g",
	  "get",
	  "gets",
	  "getting",
	  "given",
	  "gives",
	  "go",
	  "goes",
	  "going",
	  "gone",
	  "got",
	  "gotten",
	  "greetings",
	  "h",
	  "had",
	  "hadn't",
	  "happens",
	  "hardly",
	  "has",
	  "hasn't",
	  "have",
	  "haven't",
	  "having",
	  "he",
	  "he's",
	  "hello",
	  "help",
	  "hence",
	  "her",
	  "here",
	  "here's",
	  "hereafter",
	  "hereby",
	  "herein",
	  "hereupon",
	  "hers",
	  "herself",
	  "hi",
	  "him",
	  "himself",
	  "his",
	  "hither",
	  "hopefully",
	  "how",
	  "howbeit",
	  "however",
	  "i",
	  "i'd",
	  "i'll",
	  "i'm",
	  "i've",
	  "ie",
	  "if",
	  "ignored",
	  "immediate",
	  "in",
	  "inasmuch",
	  "inc",
	  "indeed",
	  "indicate",
	  "indicated",
	  "indicates",
	  "inner",
	  "insofar",
	  "instead",
	  "into",
	  "inward",
	  "is",
	  "isn't",
	  "it",
	  "it'd",
	  "it'll",
	  "it's",
	  "its",
	  "itself",
	  "j",
	  "just",
	  "k",
	  "keep",
	  "keeps",
	  "kept",
	  "know",
	  "knows",
	  "known",
	  "l",
	  "last",
	  "lately",
	  "later",
	  "latter",
	  "latterly",
	  "least",
	  "less",
	  "lest",
	  "let",
	  "let's",
	  "like",
	  "liked",
	  "likely",
	  "little",
	  "look",
	  "looking",
	  "looks",
	  "ltd",
	  "m",
	  "mainly",
	  "many",
	  "may",
	  "maybe",
	  "me",
	  "mean",
	  "meanwhile",
	  "merely",
	  "might",
	  "more",
	  "moreover",
	  "most",
	  "mostly",
	  "much",
	  "must",
	  "my",
	  "myself",
	  "n",
	  "name",
	  "namely",
	  "nd",
	  "near",
	  "nearly",
	  "necessary",
	  "need",
	  "needs",
	  "neither",
	  "never",
	  "nevertheless",
	  "new",
	  "next",
	  "nine",
	  "no",
	  "nobody",
	  "non",
	  "none",
	  "noone",
	  "nor",
	  "normally",
	  "not",
	  "nothing",
	  "novel",
	  "now",
	  "nowhere",
	  "o",
	  "obviously",
	  "of",
	  "off",
	  "often",
	  "oh",
	  "ok",
	  "okay",
	  "old",
	  "on",
	  "once",
	  "one",
	  "ones",
	  "only",
	  "onto",
	  "or",
	  "other",
	  "others",
	  "otherwise",
	  "ought",
	  "our",
	  "ours",
	  "ourselves",
	  "out",
	  "outside",
	  "over",
	  "overall",
	  "own",
	  "p",
	  "particular",
	  "particularly",
	  "per",
	  "perhaps",
	  "placed",
	  "please",
	  "plus",
	  "possible",
	  "presumably",
	  "probably",
	  "provides",
	  "q",
	  "que",
	  "quite",
	  "qv",
	  "r",
	  "rather",
	  "rd",
	  "re",
	  "really",
	  "reasonably",
	  "regarding",
	  "regardless",
	  "regards",
	  "relatively",
	  "respectively",
	  "right",
	  "s",
	  "said",
	  "same",
	  "saw",
	  "say",
	  "saying",
	  "says",
	  "second",
	  "secondly",
	  "see",
	  "seeing",
	  "seem",
	  "seemed",
	  "seeming",
	  "seems",
	  "seen",
	  "self",
	  "selves",
	  "sensible",
	  "sent",
	  "serious",
	  "seriously",
	  "seven",
	  "several",
	  "shall",
	  "she",
	  "should",
	  "shouldn't",
	  "since",
	  "six",
	  "so",
	  "some",
	  "somebody",
	  "somehow",
	  "someone",
	  "something",
	  "sometime",
	  "sometimes",
	  "somewhat",
	  "somewhere",
	  "soon",
	  "sorry",
	  "specified",
	  "specify",
	  "specifying",
	  "still",
	  "sub",
	  "such",
	  "sup",
	  "sure",
	  "t",
	  "t's",
	  "take",
	  "taken",
	  "tell",
	  "tends",
	  "th",
	  "than",
	  "thank",
	  "thanks",
	  "thanx",
	  "that",
	  "that's",
	  "thats",
	  "the",
	  "their",
	  "theirs",
	  "them",
	  "themselves",
	  "then",
	  "thence",
	  "there",
	  "there's",
	  "thereafter",
	  "thereby",
	  "therefore",
	  "therein",
	  "theres",
	  "thereupon",
	  "these",
	  "they",
	  "they'd",
	  "they'll",
	  "they're",
	  "they've",
	  "think",
	  "third",
	  "this",
	  "thorough",
	  "thoroughly",
	  "those",
	  "though",
	  "three",
	  "through",
	  "throughout",
	  "thru",
	  "thus",
	  "to",
	  "together",
	  "too",
	  "took",
	  "toward",
	  "towards",
	  "tried",
	  "tries",
	  "truly",
	  "try",
	  "trying",
	  "twice",
	  "two",
	  "u",
	  "un",
	  "under",
	  "unfortunately",
	  "unless",
	  "unlikely",
	  "until",
	  "unto",
	  "up",
	  "upon",
	  "us",
	  "use",
	  "used",
	  "useful",
	  "uses",
	  "using",
	  "usually",
	  "v",
	  "value",
	  "various",
	  "very",
	  "via",
	  "viz",
	  "vs",
	  "w",
	  "want",
	  "wants",
	  "was",
	  "wasn't",
	  "way",
	  "we",
	  "we'd",
	  "we'll",
	  "we're",
	  "we've",
	  "welcome",
	  "well",
	  "went",
	  "were",
	  "weren't",
	  "what",
	  "what's",
	  "whatever",
	  "when",
	  "whence",
	  "whenever",
	  "where",
	  "where's",
	  "whereafter",
	  "whereas",
	  "whereby",
	  "wherein",
	  "whereupon",
	  "wherever",
	  "whether",
	  "which",
	  "while",
	  "whither",
	  "who",
	  "who's",
	  "whoever",
	  "whole",
	  "whom",
	  "whose",
	  "why",
	  "will",
	  "willing",
	  "wish",
	  "with",
	  "within",
	  "without",
	  "won't",
	  "wonder",
	  "would",
	  "would",
	  "wouldn't",
	  "x",
	  "y",
	  "yes",
	  "yet",
	  "you",
	  "you'd",
	  "you'll",
	  "you're",
	  "you've",
	  "your",
	  "yours",
	  "yourself",
	  "yourselves",
	  "z",
	  "zero");
	  
		$word = mb_strtolower($word);
		
		return in_array($word, $mysqlStopwords);
	}
	
	function _makeSearchString($str)
	{
		$words = explode(' ' , $str);
		
		$words2 = array();
	
		// include all words except empty and MySQL stopwords
		foreach ($words as $word) {
			if (!empty($word)) {
				if (!$this->_isMySqlStopword($word)) {
					$words2[] = '+'.$word.'*';
				}
			}
		}
		
		$newStr = implode(" ", $words2);
		
		return $this->_mysqli->real_escape_string($newStr);
	}
	
	function _echoSetLink($stateName, $stateValue, $linkName)
	{
		$newStates = clone $this->states;
		$newStates->setState($stateName, $stateValue);
		$newStates->echoLink($linkName, $this->_pageID);
	}
	
	function _echoSetLink2($stateName, $stateValue, $linkName)
	{
		$newStates = clone $this->states;
		$newStates->setState($stateName, $stateValue);
		$newStates->clearState('sok');

		$whereParts = $this->_buildWhere($newStates);
		// build where
		if (count($whereParts) == 0) {
			$where = '';
		} else {
			$where = 'WHERE '.implode(' AND ', $whereParts);
		}
		$sql1 = "SELECT count(id) as count1 FROM artikel $where";
		$result = $this->_mysqli->query($sql1);
		if (!$result) {
			echo "Databasfel! (query1 fel)";
			return;
		}
		if ($result->num_rows != 1) {
			echo "Databasfel! (query1 fel antal rader)";
			return;
		}
		$row = $result->fetch_assoc();
		$numRecords = $row['count1'];
		$result->free();
		
		$newStates->echoLink('<p>'.$linkName."&nbsp;<span style=\"color:#888\">($numRecords)</span></p>", $this->_pageID);
	}
	
	function _echoClearLink($stateName, $linkName)
	{
		$newStates = clone $this->states;
		$newStates->clearState($stateName);
		$newStates->echoLink($linkName, $this->_pageID);
	}
	
	function _echoClearLink2($stateName, $linkName)
	{
		$newStates = clone $this->states;
		$newStates->clearState($stateName);
		$newStates->clearState('sok');
		$newStates->echoLink($linkName, $this->_pageID);
	}
	
	function _echoResetLink($linkName)
	{
		$newStates = clone $this->states;
		$newStates->clear();
		$newStates->echoLink($linkName, $this->_pageID);
	}
	
	function _showFilterCategory($categoryName, $expand)
	{
		if (!$this->states->isStateSet($categoryName)) {
			$cat = $this->categories[$categoryName];
			
			$class = $expand ? "expanded" : "";
			echo '<dt class="'.$class.'"><p class="v2TypeTitle">'.$cat->getShowAs().'</p></dt>';
			$n = $cat->getNumValues();
			for ($i = 1; $i <= $n; $i++) {
				if ($expand)
					echo "<dd class=\"expanded2\">";
				else
					echo "<dd>";
				$this->_echoSetLink2($categoryName, $i, $cat->getValue($i));
				echo "</dd>";
			}
		}		
	}
	function _showFilter()
	{
		echo '<div id="v2Filter">';
			
		if ($this->states->isStateSet('kategori') or
			$this->states->isStateSet('farg') or 
			$this->states->isStateSet('blomtid') or 
			$this->states->isStateSet('hojd') or 
			$this->states->isStateSet('lage') or 
			$this->states->isStateSet('jordman') ) {
			echo '<div id="v2CurrentFilter">';
			
			echo '<div class="v2FilterHeader">Nuvarande filtrering</div>';
			foreach ($this->categories as $categoryName => $cat) {
				if ($this->states->isStateSet($categoryName)) {
					$this->_echoClearLink2($categoryName, "<p class=\"v2TypeTitle4\"><span class=\"v2TypeTitle2\">{$cat->getShowAs()}:</span>&nbsp;".$cat->getValue($this->states->getState($categoryName)).'</p>');
				}
			}

			$newStates = clone $this->states;
			$newStates->clear();
			$newStates->setState('vy', 2);
			$newStates->echoLink('<p class="v2TypeTitle5"><span class="v2TypeTitle3">Rensa allt</span></p>', $this->_pageID);
			echo '<br>';
			echo '</div>';
		}
		echo '<div id="v2TypeFilter">';
		echo '<div class="v2FilterHeader">Filtrera växtlista</div>';
		echo '<dl>';

		foreach ($this->categories as $categoryName => $cat) {
			$expand = (strcmp($categoryName, 'kategori') == 0) and !$this->states->isStateSet('kategori');
			$this->_showFilterCategory($categoryName, $expand);
		}

		echo '</dl>';
		echo '</div>';
		echo '</div>';
	}
	
	function _buildWhere($states)
	{
		$whereParts = array();

		if ($states->isStateSet('kategori')) {
			// filter by blomtid
			$state = $states->getState('kategori');
			$cat = $this->categories['kategori'];
			$kategori = $this->_mysqli->real_escape_string($cat->getValue($state));
			$whereParts[] = "kategori LIKE('%|$kategori|%')";
		}
		
		if ($states->isStateSet('blomtid')) {
			// filter by blomtid
			$state = $states->getState('blomtid');
			$cat = $this->categories['blomtid'];
			$blomtid = $this->_mysqli->real_escape_string($cat->getValue($state));
			$whereParts[] = "blomtid='$blomtid'";
		}
		
		if ($states->isStateSet('farg')) {
			// filter by farg
			$state = $states->getState('farg');
			$cat = $this->categories['farg'];
			$farg = $this->_mysqli->real_escape_string($cat->getValue($state));
			$whereParts[] = "farg LIKE('%|$farg|%')";
		}
		
		if ($states->isStateSet('hojd')) {
			// filter by hojd
			$state = $states->getState('hojd');
			$cat = $this->categories['hojd'];
			$hojd2 = $this->_mysqli->real_escape_string($cat->getValue($state));
			$whereParts[] = "hojd2='$hojd2'";
		}
		
		if ($states->isStateSet('lage')) {
			// filter by hojd
			$state = $states->getState('lage');
			$cat = $this->categories['lage'];
			$lage = $this->_mysqli->real_escape_string($cat->getValue($state));
			$whereParts[] = "lage LIKE('%|$lage|%')";
		}

		if ($states->isStateSet('jordman')) {
			// filter by hojd
			$state = $states->getState('jordman');
			$cat = $this->categories['jordman'];
			$jordman = $this->_mysqli->real_escape_string($cat->getValue($state));
			$whereParts[] = "jordman LIKE('%|$jordman|%')";
		}

		return $whereParts;		
	}
	
	function _showView2SearchResult()
	{
		$whereParts = array();
		
		if (isset($_GET['sok'])) { // we are in text search mode
			if ($_GET['sok'] != '') {
				$str = $this->_makeSearchString($_GET['sok']);
				$whereParts[] = "MATCH(latinsktNamn, svensktNamn, idText) AGAINST('$str' IN BOOLEAN MODE)";
			}
		} else { // we are in filter search mode
			$whereParts = $this->_buildWhere($this->states);
		}
		
		$orderBy = 'ORDER BY svensktNamn';
		if ($this->states->isStateSet('sortering') and $this->states->getState('sortering') == '2')
			$orderBy = 'ORDER BY latinsktNamn';
				
		
		// build where
		if (count($whereParts) == 0) {
			$where = '';
		} else {
			$where = 'WHERE '.implode(' AND ', $whereParts);
		}

		$pageSize = 20;
		if (isset($_GET['offset']))
			$offset = sanitizeInt($_GET['offset']);
		if (empty($offset))
			$offset = 0;
			
		$sql1 = "SELECT count(id) as count1 FROM artikel $where";
		$result = $this->_mysqli->query($sql1);
		if (!$result) {
			echo "Databasfel! (query1 fel)";
			return;
		}
		if ($result->num_rows != 1) {
			echo "Databasfel! (query1 fel antal rader)";
			return;
		}
		$row = $result->fetch_assoc();
		$numRecords = $row['count1'];
		$result->free();
		
		$sql2 = "SELECT id, latinsktNamn, svensktNamn, hojd, blomtid FROM artikel $where ".
				"$orderBy ".
				"LIMIT $offset,$pageSize";
		
		$result = $this->_mysqli->query($sql2);
		if (!$result) {
			echo "Databasfel! (query2 fel)";
			return;
		}
		
		$numRows = $result->num_rows;
		$numPages = intval( ($numRecords+$pageSize-1)/$pageSize );

		echo <<<EOD
<div id="v2">
<div id="v2Label" style="float:left;"><h2>Vi har hittat $numRecords växter som passar din sökning</h2></div>
<div style="float:right; text-align:right"><form method="GET" action="/" ><input type="hidden" name="page_id" value="{$this->_pageID}" /><input type="hidden" name="vy" value="2" /><input type="text" class="field" placeholder="Sök" id="sok" name="sok" size="20" /><label for="sok" class="assistive-text">Sök</label><input type="submit" name="submit1" value="Sök" /></form></div>
<p style="clear:both"></p>
EOD;
		echo '<span id="v2Label"><h2></h2></span>';
		echo '<div><div id="v2Sort"><ul><span>Sortera på: </span>';
		if ($this->states->getState('sortering') != 2) // state is already set, no link
			echo '<li class="selected">Svenskt namn';
		else {
			echo '<li>';
			$this->_echoSetLink('sortering', 1, 'Svenskt namn');
		}
		echo '</li>';
		if ($this->states->getState('sortering') == 2) // state is already set, no link
			echo '<li class="selected">Latinskt namn';
		else {
			echo '<li>';
			$this->_echoSetLink('sortering', 2, 'Latinskt namn');
		}
		echo '</li>';
		echo '</ul></div>';

		echo '<div style="float:right">';
		$this->_showView2Pagination($offset, $numPages, $pageSize);
		echo '</div>';
		echo '</div>';
		
		echo '<br><br>';

		echo <<<EOD
<div id="v2Box">
<div id="v2SubBox">
<div id="v2CatBox">
EOD;

		if ($numRows == 0)
			echo "Inga resultat.";
			
		
		$left = true;
		while ($row = $result->fetch_assoc()) {
			$id = $row['id'];
			// $latinsktNamn = str_replace("'", "<br>", htmlspecialchars($row['latinsktNamn']), 1); // break line at first single quote
			$latinsktNamn = htmlspecialchars($row['latinsktNamn']); // break line at first single quote
			$pos = strpos($latinsktNamn, "'", 1);
			if ($pos !== false) {
				$latinsktNamn = substr_replace($latinsktNamn, "<br>'", $pos, 1);
			}
			$svensktNamn = htmlspecialchars($row['svensktNamn']);
			$hojd = htmlspecialchars($row['hojd']);
			$blomtid = htmlspecialchars($row['blomtid']);
			
			$this->_showPlant($left, $id.'.jpg', $latinsktNamn, $svensktNamn, $hojd.' cm', $blomtid, $id);
			
			$left = !$left;
		}

		echo <<<EOD
<p style="clear:both"></p>
</div>
<p style="clear:both"></p>
EOD;
	
		echo '<div style="float:right">';
		$this->_showView2Pagination($offset, $numPages, $pageSize);
		echo '</div>';
	
		echo <<<EOD
</div>
</div>
</div>
EOD;

		$result->free();
	}

	function _showView2Pagination($offset, $numPages, $pageSize)
	{
		// show pagination
		$curPage = intval($offset/$pageSize)+1;
		// show previous page link if we are not on page 1
		if ($curPage > 1)
			$this->_echoSetLink('offset', ($curPage-2)*$pageSize, "Föreg. sida&nbsp;");
			
		// show 4 page links before current page
		$page = $curPage-4;
		if ($page < 1)
			$page = 1;
		if ($page > 1)
			echo "...&nbsp";
		while ($page < $curPage) {
			$pageOffset = ($page-1)*$pageSize;
			$this->_echoSetLink('offset', $pageOffset, "$page");
			echo "&nbsp";
			$page++;
		}
		
		// show current page
		echo "$curPage&nbsp";

		// show 4 page links after current page
		for ($page = $curPage+1; $page < $curPage+5 and $page <= $numPages; $page++) {
			$pageOffset = ($page-1)*$pageSize;
			$this->_echoSetLink('offset', $pageOffset, "$page");
			echo "&nbsp";
		}
		if ($curPage+4 < $numPages)
			echo "...&nbsp";
		
		// show next page link if we are not on last page
		if ($curPage < $numPages)
			$this->_echoSetLink('offset', $curPage*$pageSize, "Nästa sida");
	}

	function _showPlant($left, $imageFilename, $latTitle, $sweTitle, $height, $bloom, $id)
	{
		$theClass = $left ? "v2CatLeft" : "v2CatRight";
/*
		<div class="v2LeafBox">
			<div class="v2LeafTextBox">$id</div>
		</div>
*/		
		$html = <<<EOD
<div class="v2Cat $theClass">
	<div class="v2ImgBox"><img style="width:200px; height:200px" src="/wp-content/plantimages/ros.jpg" /><div class="v2LeafBox">
			<div class="v2LeafTextBox">$id</div>
		</div></div>
	<div class="v2TextBox">
		<div class="v2TitleBox">
			<span class="v2LatTitle">$latTitle</span><br>
			<span class="v2SweTitle">$sweTitle</span>
		</div>
		<hr class="v2Rule">
		<span class="v2PropTitle">HÖJD&nbsp;</span>$height<br>
		<span class="v2PropTitle">BLOMNING&nbsp;</span>$bloom
	</div>
</div>
EOD;
		$newStates = clone $this->states;
		$newStates->setState('vy', 3);
		$newStates->setState('artikelID', $id);
		$newStates->echoLink($html, $this->_pageID);
	}
}
	