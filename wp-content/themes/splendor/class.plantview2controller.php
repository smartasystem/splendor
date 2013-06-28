<?php

require_once 'class.abstractcontroller.php';

function sanitizeInt($str) {
	return filter_var($str, FILTER_SANITIZE_NUMBER_INT);
}

class States {

	private $states;

	function __construct($stateNames, $init) {
		$this->states = array();
		if ($init) {
			foreach ($stateNames as $stateName) {
				if (isset($_GET[$stateName]))
					$this->states[$stateName] = $_GET[$stateName];
			}
		}
	}

	function getState($stateName) {
		return $this->states[$stateName];
	}

	function clearState($stateName) {
		unset($this->states[$stateName]);
	}

	function setState($stateName, $stateValue) {
		$this->states[$stateName] = $stateValue;
	}

	function clear() {
		$this->states = array();
	}

	function isEmpty() {
		return count($this->states) == 0;
	}

	function isStateSet($stateName) {
		return isset($this->states[$stateName]);
	}

	function echoLink($linkName, $pageID) {
		$link = "?page_id=$pageID";

		foreach ($this->states as $stateName => $stateValue) {
			$link .= '&' . $stateName . '=' . $stateValue;
		}

		echo "<a href=\"$link\">$linkName</a>";
	}

	function __toString() {

		return print_r($this->states, true);
	}

}

class Category {

	private $name;
	private $showAs;
	private $values;

	function __construct($name, $showAs, $values) {
		$this->name = $name;
		$this->showAs = $showAs;
		$this->values = $values;
	}

	function getShowAs() {
		return $this->showAs;
	}

	function getValue($index) {
		if ($index < 1 or $index > $this->getNumValues())
			return '';

		return $this->values[$index - 1];
	}

	function getNumValues() {
		return count($this->values);
	}

}

class PlantView2Controller extends AbstractController {

	private $stateNames = array('kategori', 'farg', 'blomtid', 'hojd', 'lage', 'jordman', 'sortering', 'vy', 'sok');
	private $states;
	private $categories;

	function __default() {
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

	function _initCategories() {
		$this->categories = array();
		$this->categories['kategori'] = new Category('kategori', 'Typ', array('Frukt & Bär', 'Rosor', 'Träd & Buskar', 'Klätterväxter', 'Barrväxter', 'Perenner'));
		/* $this->categories['farg'] = new Category('farg', 'Färg', array('Vit', 'Gul', 'Grön', 'Blå', 'Lila', 'Purpur', 'Rosa', 'Röd', 'Brun', 'Orange', 'Blandade'));
		$this->categories['blomtid'] = new Category('blomtid', 'Blomtid', array('Vår', 'Sommar', 'Höst'));
		$this->categories['hojd'] = new Category('hojd', 'Höjd', array('Låga', 'Mellan', 'Höga'));
		$this->categories['lage'] = new Category('lage', 'Läge', array('Sol', 'Halvskugga', 'Skugga'));
		$this->categories['jordman'] = new Category('jordman', 'Jordmån', array('Torr', 'Fuktig', 'Väldränerad')); */
	}

	function _showView1Category($name, $value, $imageFilename) {
		$html = <<<EOD
<div class="assortmentView1Category"><img src="/wp-content/categoryimages/$imageFilename" /><div class="assortmentView1TitleBox">$name</div></div>
EOD;
		$newStates = new States($this->stateNames, false);
		$newStates->setState('vy', 2);
		$newStates->setState('kategori', $value);
		$newStates->echoLink($html, $this->_pageID);
	}

	function _getCategoryPicFilename($index) {
		$categoryPicFilenames = array('1.jpg', '2.jpg', '3.jpg',
			'4.jpg', '5.jpg', '6.jpg'
		);

		if ($index < 1)
			return '';
		
		if ($index > count($categoryPicFilenames))
			return '';
		
		return $categoryPicFilenames[$index-1];
	}
	
	function _showView1() {
		echo '<div id="main">';
		echo the_content();

		echo '<div>';


		// show categories
		$category = $this->categories['kategori'];
		$n = $category->getNumValues();
		for ($i = 1; $i <= $n; $i++)
			$this->_showView1Category($category->getValue($i), $i, $this->_getCategoryPicFilename($i));

		echo <<<EOD
			<div class="clear"></div>
		</div>
		</div>
EOD;

		get_sidebar();
	}

	function _showView2Category() {
		if (!($this->states->isStateSet('kategori'))) // check if we have a category to display
			return;
		
		$catList = $this->categories['kategori'];

		$catIndex = intval($this->states->getState('kategori'));
		if ($catIndex < 1)
			$catIndex = 1;
		if ($catIndex > $catList->getNumValues())
			$catIndex = $catList->getNumValues();
		
		$picFilename = $this->_getCategoryPicFilename($catIndex); // fixa use this var below
		
		$cat = $catList->getValue($catIndex);
		echo <<<EOD
			<div class="assortmentView2Category">
				<div class="assortmentView2CategoryTitleBox">$cat</div>
				<div>
					<div id="assortmentView2CategoryImage"><img src="/wp-content/categoryimages/1.jpg" /></div>
					<div id="assortmentView2CategoryText">
					Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum 
					</div>
				</div>
				<div class="fix"></div>
			</div>
		
EOD;
	}
	
	function _showView2() {
		echo '<div id="main">';

		echo the_content();

		$this->_showView2Category();
		$this->_showView2SearchResult();

		echo '<div class="clear"></div>';

		echo '</div>';

		get_sidebar();
	}

	function _showView3() {
		global $wpdb;

		$artikelID = sanitizeInt($_GET['artikelID']);

		$sql = "SELECT C001_Vaxtkod, C002_Latinskt_namn_langt, C006_Svenskt_namn_langt, C029_Vaxtbeskrivning_katalog, ".
				"D026_Skylttext_vaxtsatt_anvandning, D027_Skylttext_Utseende, D028_Skylttext_Storlek, ".
				"D029_Skylttext_Vaxtlage, D030_Skylttext_Hardighet FROM sp_vaxt WHERE B001_ID=$artikelID";

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

		$latinsktNamn = htmlspecialchars($row->C002_Latinskt_namn_langt);
		$svensktNamn = htmlspecialchars($row->C006_Svenskt_namn_langt);
		$vaxtbeskrivning = htmlspecialchars($row->C029_Vaxtbeskrivning_katalog);
		$vaxtsatt = htmlspecialchars($row->D026_Skylttext_vaxtsatt_anvandning);
		$utseende = htmlspecialchars($row->D027_Skylttext_Utseende);
		$storlek = htmlspecialchars($row->D028_Skylttext_Storlek);
		$vaxtlage = htmlspecialchars($row->D029_Skylttext_Vaxtlage);
		$hardighet = htmlspecialchars($row->D030_Skylttext_Hardighet);
		$vaxtkod = htmlspecialchars($row->C001_Vaxtkod);
		
		$sql2 = "SELECT C001_Bildnamn FROM sp_bild WHERE B002_VaxtID='$vaxtkod'";
		$rows2 = $wpdb->get_results($sql2);
		if ($rows2 === FALSE) {
			echo "Databasfel! (query2 fel)";
			return;
		}

		$filenames = array();
		foreach ($rows2 as $row2) {
			$filenames[] = $row2->C001_Bildnamn;
		}
		
		$filenames2 = array();
		foreach ($filenames as $filename) {
			if ($this->_imageExists($filename))
				$filenames2[] = $filename;
		}
		$filename1 = '';
		if (count($filenames2) > 0)
			$filename1 = $filenames2[0];
		
		echo <<<EOD
<script>

function doImageClick(imageURL, imageIndex, numImages)
{
	var x = document.getElementById('assortmentView3MainImage');
	x.src = imageURL;
	
	for (i = 1; i <= numImages; i++) {
		var y = document.getElementById('assortmentView3SmallImage'+i);
		if (i == imageIndex) {
			y.className = "assortmentView3SmallImageSelected";
		} else {
			y.className = "assortmentView3SmallImageNotSelected";
		}
	}
}

</script>
EOD;
		echo <<<EOD
<div id="main">
	<div id="assortmentView3TitleBox">
		<span class="assortmentView3SweTitle">$svensktNamn</span><br>
		<span class="assortmentView3LatTitle">($latinsktNamn)</span>
	</div>
	<div id="assortmentView3Main">
		<div id="assortmentView3Col1">
			<img id="assortmentView3MainImage" src="/wp-content/plantimages/$filename1">
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
			echo "<img id=\"assortmentView3SmallImage$index\" class=\"assortmentView3SmallImage $class\" src=\"/wp-content/plantimages/$filename\" style=\"cursor:pointer\" onclick=\"doImageClick('/wp-content/plantimages/$filename', $index, $numImages)\" />";
			$firstImage = false;
			$index++;
		}
		echo <<<EOD
			</div><!-- end of #assortmentView3SmallImages -->
			<div id="assortmentView3Tips">
				<h2 class="extra-h2">Trådgärdsmästarens tips</h2>
<p>-</p>
			</div><!-- end of #assortmentView3Tips -->
		</div><!-- end of #assortmentView3Col1 -->
		<div id="assortmentView3Col2">
			<div id="assortmentView3PlantInfo">
<p>$vaxtbeskrivning</p>
<strong>VÄXTSÄTT:</strong> $vaxtsatt<br>
<strong>UTSEENDE:</strong> $utseende<br>
<strong>STORLEK:</strong> $storlek<br>
<strong>VÄXTLÄGE:</strong> $vaxtlage<br>
<strong>ZON (HÄRDIGHET):</strong> $hardighet<br>
			</div>	
			<div id="assortmentView3StepByStep">
				<strong>STEG FÖR STEG, SÅ HÄR GÖR DU:</strong>
<p>-</p>
			</div>
		</div>
	</div><!-- end of #assortmentView3Main -->
</div><!-- end of #main -->
EOD;
		get_sidebar();
		
		// $this->_randomPopulatePlantCategories();
	}

	function _randomPlantCategory($B001_ID)
	{
		$catList = $this->categories['kategori'];
		
		$numCats = rand(0, 6);
		
		$cats = array();
		for ($i = 0; $i < $numCats; $i++) {
			$cats[] = $catList->getValue(rand(1, $catList->getNumValues()));
		}
		
		$catsText = implode('|', $cats);
		if (strlen($catsText) != 0) {
			$catsText = "|$catsText|";
		}
		
		return $catsText;
	}
	
	function _randomPopulatePlantCategory($B001_ID)
	{
		global $wpdb;

		$cat = $this->_randomPlantCategory();
		
		$sql = "UPDATE sp_vaxt SET kategori='$cat' WHERE B001_ID=$B001_ID";	

		$ok = $wpdb->query($sql);
		
		echo "ok=$ok<br>";
	}
	
	function _randomPopulatePlantCategories()
	{
		global $wpdb;
		
		$sql = "SELECT B001_ID FROM sp_vaxt";

		$rows = $wpdb->get_results($sql);
		if ($rows === FALSE) {
			echo "Databasfel! (query fel)";
			return;
		}
		
		foreach ($rows as $row) {
			$this->_randomPopulatePlantCategory($row->B001_ID);
		}
	}
	
	function _imageExists($filename) {
		$pathname = "/Project/splendor/wp-content/plantimages/$filename"; // fixa
		return file_exists($pathname); 
	}

	function _isMySqlStopword($word) {
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

	function _makeSearchString($str) {
		$words = explode(' ', $str);

		$words2 = array();

		// include all words except empty and MySQL stopwords
		foreach ($words as $word) {
			if (!empty($word)) {
				if (!$this->_isMySqlStopword($word)) {
					$words2[] = '+' . $word . '*';
				}
			}
		}

		$newStr = implode(" ", $words2);

		return $newStr;
	}

	function _echoSetLink($stateName, $stateValue, $linkName) {
		$newStates = clone $this->states;
		$newStates->setState($stateName, $stateValue);
		$newStates->echoLink($linkName, $this->_pageID);
	}

	function _echoSetLink2($stateName, $stateValue, $linkName) {
		global $wpdb;
		$newStates = clone $this->states;
		$newStates->setState($stateName, $stateValue);
		$newStates->clearState('sok');

		$whereParts = $this->_buildWhere($newStates);
		// build where
		if (count($whereParts) == 0) {
			$where = '';
		} else {
			$where = 'WHERE ' . implode(' AND ', $whereParts);
		}

		$sql1 = "SELECT count(B001_ID) as count1 FROM sp_vaxt $where";
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

		$newStates->echoLink('<p style="color:white">' . $linkName . "&nbsp;<span>($numRecords)</span></p>", $this->_pageID);
	}

	function _echoClearLink($stateName, $linkName) {
		$newStates = clone $this->states;
		$newStates->clearState($stateName);
		$newStates->echoLink($linkName, $this->_pageID);
	}

	function _echoClearLink2($stateName, $linkName) {
		$newStates = clone $this->states;
		$newStates->clearState($stateName);
		$newStates->clearState('sok');
		$newStates->echoLink($linkName, $this->_pageID);
	}

	function _echoResetLink($linkName) {
		$newStates = clone $this->states;
		$newStates->clear();
		$newStates->echoLink($linkName, $this->_pageID);
	}

	function _showFilterCategory($categoryName, $expand) {
		if (!$this->states->isStateSet($categoryName)) {
			$cat = $this->categories[$categoryName];

			$class = $expand ? "expanded" : "";
			echo '<dt class="' . $class . '"><h3 class="v2TypeTitle">' . $cat->getShowAs() . '</h3></dt>';
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

	function _showFilter() {
		$this->states = new States($this->stateNames, true);

		$this->_initCategories();

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
		if ($this->states->isStateSet('kategori') /* or
				$this->states->isStateSet('farg') or
				$this->states->isStateSet('blomtid') or
				$this->states->isStateSet('hojd') or
				$this->states->isStateSet('lage') or
				$this->states->isStateSet('jordman') */) {
			echo '<div id="v2CurrentFilter">';

			echo '<div class="v2FilterHeader"><h3>Nuvarande filtrering</h3></div>';
			foreach ($this->categories as $categoryName => $cat) {
				if ($this->states->isStateSet($categoryName)) {
					$this->_echoClearLink2($categoryName, "<p class=\"v2TypeTitle4\"><span class=\"v2TypeTitle2\">{$cat->getShowAs()}:</span>&nbsp;" . $cat->getValue($this->states->getState($categoryName)) . '</p>');
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
		echo '<div class="v2FilterHeader"><h3>Filtrera sök</h3></div>';
		echo '<dl>';

		foreach ($this->categories as $categoryName => $cat) {
			$expand = (strcmp($categoryName, 'kategori') == 0) and !$this->states->isStateSet('kategori');
			$this->_showFilterCategory($categoryName, $expand);
		}

		echo '</dl>';
		echo '</div>'; // end of v2TypeFilter

		echo '</div>'; // end of v2Filter
		echo '<div class="fix"></div>';
	}

	function _buildWhere($states) {
		$whereParts = array();

		if ($states->isStateSet('kategori')) {
			// filter by kategori
			$state = $states->getState('kategori');
			$cat = $this->categories['kategori'];
			$kategori = esc_sql($cat->getValue($state));
			$whereParts[] = "kategori LIKE('%|$kategori|%')";
		}
/*
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
*/
		return $whereParts;
	}

	function _showView2SearchResult() {
		global $wpdb;

		$whereParts = array();

		if (isset($_GET['sok'])) { // we are in text search mode
			if ($_GET['sok'] != '') {
				$str = $this->_makeSearchString($_GET['sok']);
				$whereParts[] = $wpdb->prepare("MATCH(C002_Latinskt_namn_langt, C006_Svenskt_namn_langt) AGAINST(%s IN BOOLEAN MODE)", $str);
			}
		} else { // we are in filter search mode
			$whereParts = $this->_buildWhere($this->states);
		}

		$orderBy = 'ORDER BY C006_Svenskt_namn_langt';
		if ($this->states->isStateSet('sortering') and $this->states->getState('sortering') == '2')
			$orderBy = 'ORDER BY C002_Latinskt_namn_langt';


		// build where
		if (count($whereParts) == 0) {
			$where = '';
		} else {
			$where = 'WHERE ' . implode(' AND ', $whereParts);
		}

		$pageSize = 20;
		if (isset($_GET['offset']))
			$offset = sanitizeInt($_GET['offset']);
		if (empty($offset))
			$offset = 0;

		$sql1 = "SELECT count(B001_ID) as count1 FROM sp_vaxt $where";
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

		$sql2 = "SELECT B001_ID, C002_Latinskt_namn_langt, C006_Svenskt_namn_langt FROM sp_vaxt $where " .
				"$orderBy " .
				"LIMIT $offset,$pageSize";

		$rows = $wpdb->get_results($sql2);
		if ($rows === FALSE) {
			echo "Databasfel! (query2 fel)";
			return;
		}

		$numRows = count($rows);
		$numPages = intval(($numRecords + $pageSize - 1) / $pageSize);

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
			$id = $row->B001_ID;
			$latinsktNamn = htmlspecialchars($row->C002_Latinskt_namn_langt);
			$svensktNamn = htmlspecialchars($row->C006_Svenskt_namn_langt);

			$this->_showPlant($id . '.jpg', $latinsktNamn, $svensktNamn, $id);
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

	function _showView2Pagination($offset, $numPages, $pageSize) {
		// show pagination
		$curPage = intval($offset / $pageSize) + 1;
		// show previous page link if we are not on page 1
		if ($curPage > 1)
			$this->_echoSetLink('offset', ($curPage - 2) * $pageSize, "Föreg. sida&nbsp;");

		// show 4 page links before current page
		$page = $curPage - 4;
		if ($page < 1)
			$page = 1;
		if ($page > 1)
			echo "...&nbsp";
		while ($page < $curPage) {
			$pageOffset = ($page - 1) * $pageSize;
			$this->_echoSetLink('offset', $pageOffset, "$page");
			echo "&nbsp";
			$page++;
		}

		// show current page
		echo "$curPage&nbsp";

		// show 4 page links after current page
		for ($page = $curPage + 1; $page < $curPage + 5 and $page <= $numPages; $page++) {
			$pageOffset = ($page - 1) * $pageSize;
			$this->_echoSetLink('offset', $pageOffset, "$page");
			echo "&nbsp";
		}
		if ($curPage + 4 < $numPages)
			echo "...&nbsp";

		// show next page link if we are not on last page
		if ($curPage < $numPages)
			$this->_echoSetLink('offset', $curPage * $pageSize, "Nästa sida");
	}

	function _showPlant($imageFilename, $latTitle, $sweTitle, $id) {
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

