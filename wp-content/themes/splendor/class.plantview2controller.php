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
<div class="assortmentView1Category"><img src="/wp-content/categoryimages/$imageFilename" /><div class="assortmentView1TitleBox">$name</div></div>
EOD;
		$newStates = new States($this->stateNames, false);
		$newStates->setState('vy', 2);
		$newStates->setState('kategori', $value);
		$newStates->echoLink($html, $this->_pageID);
	}
	
	function _showView1()
	{  
       echo <<<EOD
	<div id="main">
EOD;
        the_content();
        
        echo <<<EOD
    <div>
EOD;

		// show Alla perenner-category		
		$html = '<div class="assortmentView1Category"><img src="/wp-content/categoryimages/1.jpg" /><div class="assortmentView1TitleBox">Alla perenner</div></div>';
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
</div>
EOD;
      get_sidebar();

	}

	function _showView2()
	{
        echo '<div id="main">';

		the_content();

		$this->_showView2SearchResult();
		
		echo '<div class="clear"></div>';
		
		echo '</div>';
 
		echo '<div id="sidebar">';
		$this->_showFilter();
		echo '</div>';
 	}

	function _showView3()
	{
        global $wpdb;
        
        $artikelID = sanitizeInt($_GET['artikelID']);
		
		$sql = "SELECT latinsktNamn, svensktNamn FROM artikel WHERE id=$artikelID";
		
		$rows = $wpdb->get_results($sql);
		if ($rows === FALSE) {
			echo "Databasfel! (query fel)";
			return;
		}
		if (count($rows) != 1) {
			echo "Databasfel! (ingen post)";
			return;
		}
		$row = $rows[0];
		
		$latinsktNamn = htmlspecialchars($row->latinsktNamn);		
		$svensktNamn = htmlspecialchars($row->svensktNamn);

		$filenames = array("$artikelID.jpg", "$artikelID-1.jpg", "$artikelID-2.jpg");
		$filenames2 = array();
		foreach ($filenames as $filename) {
			if ($this->_imageExists($filename))
				$filenames2[] = $filename;
		}
		
        echo <<<EOD
<div id="main">
	<div id="assortmentView3TitleBox">
		<span class="assortmentView3SweTitle">$svensktNamn</span><br>
		<span class="assortmentView3LatTitle">($latinsktNamn)</span>
	</div>
	<div id="assortmentView3Main">
		<div id="assortmentView3Col1">
			<img id="assortmentView3MainImage" src="/wp-content/plantimages/ros.jpg">
			<strong>FLER BILDER:</strong>
			<div id="assortmentView3SmallImages">
EOD;
      
	$firstImage = true;
	$index = 1;
	$numImages = count($filenames2);
	foreach ($filenames2 as $filename) {
		if ($firstImage)
			$class = "assortmentView3SmallImageSelected";
		else
			$class = "assortmentView3SmallImageNotSelected";
		echo "<img id=\"assortmentView3SmallImage$index\" class=\"assortmentView3SmallImage $class\" src=\"/wp-content/plantimages/ros.jpg\" style=\"cursor:pointer\" onclick=\"doImageClick('images/article/stora/$filename', $index, $numImages)\" />";
		$firstImage = false;
		$index++;
	}
	echo <<<EOD
			</div><!-- end of #assortmentView3SmallImages -->
			<div id="assortmentView3Tips">
				<h2 class="extra-h2">Trådgärdsmästarens tips</h2>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum est est, fringilla laoreet pretium et, mollis vel lorem.
	Vivamus vel eros volutpat, fringilla lorem a, convallis mi. Vivamus felis erat, commodo et blandit vel, eleifend et turpis.
	Morbi volutpat vel magna a ornare. In hac habitasse platea dictumst.</p>
			</div><!-- end of #assortmentView3Tips -->
		</div><!-- end of #assortmentView3Col1 -->
		<div id="assortmentView3Col2">
			<div id="assortmentView3PlantInfo">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum est est, fringilla laoreet pretium et, mollis vel lorem.
	Vivamus vel eros volutpat, fringilla lorem a, convallis mi. Vivamus felis erat, commodo et blandit vel, eleifend et turpis.
	Morbi volutpat vel magna a ornare. In hac habitasse platea dictumst.</p>
<p>Praesent felis neque, volutpat in lacus sit amet, tristique venenatis urna.</p>
<p>Donec eleifend nibh ac adipiscing molestie. Fusce dictum nec orci at cursus.</p>
<strong>VÄXTSÄTT:</strong><br>
<strong>UTSEENDE:</strong><br>
<strong>STORLEK:</strong>6-8 meter<br>
<strong>VÄXTLÄGE:</strong><br>
<strong>ZON (HÄRDIGHET):</strong>1-5<br>
			</div>	
			<div id="assortmentView3StepByStep">
				<strong>STEG FÖR STEG, SÅ HÄR GÖR DU:</strong>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum est est, fringilla laoreet pretium et, mollis vel lorem.
	Vivamus vel eros volutpat, fringilla lorem a, convallis mi. Vivamus felis erat, commodo et blandit vel, eleifend et turpis.
	Morbi volutpat vel magna a ornare. In hac habitasse platea dictumst.</p>
			</div>
		</div>
	</div><!-- end of #assortmentView3Main -->
</div><!-- end of #main -->
EOD;
       get_sidebar();
	}

	function _imageExists($filename)
	{
		// $pathname = "/home/u/u6443829/www/images/article/stora/$filename";
		
		// return file_exists($pathname); 
        
        return true;
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
		
		return $newStr;
	}
	
	function _echoSetLink($stateName, $stateValue, $linkName)
	{
		$newStates = clone $this->states;
		$newStates->setState($stateName, $stateValue);
		$newStates->echoLink($linkName, $this->_pageID);
	}
	
	function _echoSetLink2($stateName, $stateValue, $linkName)
	{
        global $wpdb;
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
		$rows = $wpdb->get_results($sql1);
		if ($rows === FALSE) {
			echo "Databasfel! (query1 fel)";
			return;
		}
        
		if (count($rows) != 1) {
			echo "Databasfel! (query1 fel antal rader)";
			return;
		}
		$row = $rows[0];
		$numRecords = $row->count1;
		
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
			echo '<dt class="'.$class.'"><h3 class="v2TypeTitle">'.$cat->getShowAs().'</h3></dt>';
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
        		echo <<<EOD
    <script>
        jQuery(document).ready(function($){   
 	   // When any dt element is clicked 
$('dt').click(function(e){ 
    // All dt elements after this dt element until the next dt element 
    // Will be hidden or shown depending on it's current visibility 
    $(this).nextUntil('dt').toggle(); 
    $(this).toggleClass("expanded"); 
}); 
 
// Hide all dd elements to start with 
jQuery('dd').hide(); 
jQuery('dd.expanded2').show(); 
     });

    </script>
EOD;

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
		echo '<div class="v2FilterHeader"><h3>Filtrera växtlista</h3></div>';
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
			$kategori = esc_sql($cat->getValue($state));
			$whereParts[] = "kategori LIKE('%|$kategori|%')";
		}
		
		if ($states->isStateSet('blomtid')) {
			// filter by blomtid
			$state = $states->getState('blomtid');
			$cat = $this->categories['blomtid'];
			$blomtid = esc_sql($cat->getValue($state));
			$whereParts[] = "blomtid='$blomtid'";
		}
		
		if ($states->isStateSet('farg')) {
			// filter by farg
			$state = $states->getState('farg');
			$cat = $this->categories['farg'];
			$farg = esc_sql($cat->getValue($state));
			$whereParts[] = "farg LIKE('%|$farg|%')";
		}
		
		if ($states->isStateSet('hojd')) {
			// filter by hojd
			$state = $states->getState('hojd');
			$cat = $this->categories['hojd'];
			$hojd2 = esc_sql($cat->getValue($state));
			$whereParts[] = "hojd2='$hojd2'";
		}
		
		if ($states->isStateSet('lage')) {
			// filter by hojd
			$state = $states->getState('lage');
			$cat = $this->categories['lage'];
			$lage = esc_sql($cat->getValue($state));
			$whereParts[] = "lage LIKE('%|$lage|%')";
		}

		if ($states->isStateSet('jordman')) {
			// filter by hojd
			$state = $states->getState('jordman');
			$cat = $this->categories['jordman'];
			$jordman = esc_sql($cat->getValue($state));
			$whereParts[] = "jordman LIKE('%|$jordman|%')";
		}

		return $whereParts;		
	}
	
	function _showView2SearchResult()
	{
        global $wpdb;
        
		$whereParts = array();
		
		if (isset($_GET['sok'])) { // we are in text search mode
			if ($_GET['sok'] != '') {
				$str = $this->_makeSearchString($_GET['sok']);
				$whereParts[] = $wpdb->prepare("MATCH(latinsktNamn, svensktNamn, idText) AGAINST(%s IN BOOLEAN MODE)", $str);
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
		$rows = $wpdb->get_results($sql1);
		if ($rows === FALSE) {
			echo "Databasfel! (query1 fel)";
			return;
		}
		if (count($rows) != 1) {
			echo "Databasfel! (query1 fel antal rader)";
			return;
		}
		$row = $rows[0];
		$numRecords = $row->count1;
		
		$sql2 = "SELECT id, latinsktNamn, svensktNamn, hojd, blomtid FROM artikel $where ".
				"$orderBy ".
				"LIMIT $offset,$pageSize";
		
		$rows = $wpdb->get_results($sql2);
		if ($rows === FALSE) {
			echo "Databasfel! (query2 fel)";
			return;
		}
		
		$numRows = count($rows);
		$numPages = intval( ($numRecords+$pageSize-1)/$pageSize );

		echo <<<EOD
<div>
<div>DET FINNS $numRecords PRODUKTER</div>
EOD;
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
			
		
		foreach ($rows as $row) {
			$id = $row->id;
			$latinsktNamn = htmlspecialchars($row->latinsktNamn);
			$svensktNamn = htmlspecialchars($row->svensktNamn);
			$hojd = htmlspecialchars($row->hojd);
			$blomtid = htmlspecialchars($row->blomtid);
			
			$this->_showPlant($id.'.jpg', $latinsktNamn, $svensktNamn, $id);
		}

		echo <<<EOD
<div class="clear"></div>
</div>
<div class="clear"></div>
EOD;
	
		echo '<div style="float:right">';
		$this->_showView2Pagination($offset, $numPages, $pageSize);
		echo '</div>';
	
		echo <<<EOD
</div>
</div>
</div>
EOD;
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

	function _showPlant($imageFilename, $latTitle, $sweTitle, $id)
	{
		$html = <<<EOD
<div class="assortmentView2Plant"><img class="assortmentView2Image" src="/wp-content/plantimages/ros.jpg" /><div style="height:35px;">
		<span class="assortmentView2SweTitle">$sweTitle</span><br>
		<span class="assortmentView2LatTitle">$latTitle</span>
	</div>
</div>
EOD;
		$newStates = clone $this->states;
		$newStates->setState('vy', 3);
		$newStates->setState('artikelID', $id);
		$newStates->echoLink($html, $this->_pageID);
	}
}
	