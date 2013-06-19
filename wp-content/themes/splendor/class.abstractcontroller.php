<?php

/** Namnet på databasen för SvePlant */
define('WANDELS_DB_NAME', 'splendor_wp');

/** MySQL-databasens användarnamn */
define('WANDELS_DB_USER', 'splendor_wp');

/** MySQL-databasens lösenord */
define('WANDELS_DB_PASSWORD', 'klachhg854!+');

/** MySQL-server */
define('WANDELS_DB_HOST', 'smartasystem.se');


define("ROWS_PER_PAGE", 10);

require_once('stopwords.php');

abstract class AbstractController
{
	protected $_mysqli;
	protected $_pageID;
		
	function __construct()
	{
		$this->_mysqli = new mysqli(WANDELS_DB_HOST, WANDELS_DB_USER, WANDELS_DB_PASSWORD, WANDELS_DB_NAME);

		/* check connection */
		if ($this->_mysqli->connect_errno) {
		    printf("Connect failed\n<br>");
		    exit();
		}
		
		/* change character set to utf8 */
		if (!$this->_mysqli->set_charset("utf8")) {
		    printf("Error loading character set utf8\n<br>");
		    exit();
		}
		
		$this->_pageID = get_the_ID();
	}

	protected function __default()
	{
		printf("Error occured! Please contact web master.");
	}
	
	public function _dispatch()
	{
		$event = '__default';
		if (isset($_REQUEST['event'])) {
			$event = $_REQUEST['event'];
			if (strpos($event, '_') === 0)
				$event = '__default';	
		}
			
		try {
			if (method_exists($this, $event))
				$this->$event();
			else {
				echo "Ogiltig event ($event)!";
			}
		} catch (Exception $error) {
			echo "Undantag inträffade i event ($event)!";
		}
	}
	
	function __destruct()
	{
		$this->_mysqli->close();
	}

	protected function _displayPagination($event, $numPages, $offset, $searchParams)
	{
		if ($numPages > 1) {
			// only display pagination if we have more than one page
			$curPage = intval($offset/ROWS_PER_PAGE);
			$queryStringPart = "event=$event";
			$queryStringPart .= "&page_id=".get_the_ID();
			foreach ($searchParams as $key => $value) {
				$queryStringPart .= "&$key=".urlencode($value);
			}
			if ($numPages > 12) {
				// if more than 12 pages with search result, then display links to show first and last page
				$startPage = $curPage-6;
				if ($startPage < 1)
					$startPage = 1;
				$endPage = $curPage+6;
				if ($endPage > $numPages)
					$endPage = $numPages;
				$offset2 = ($startPage-1)*ROWS_PER_PAGE;
				echo 'Sida ';
				if ($startPage != 1) {
					$queryString = $queryStringPart."&offset=0";
					echo '<a href="?'.$queryString.'">Första</a> ... ';
				}
				for ($i = $startPage; $i <= $endPage; $i++) {
					if ($offset2 == $offset)
					echo $i.' ';
					else {
						$queryString = $queryStringPart."&offset=$offset2";
						echo '<a href="?'.$queryString.'">'.$i.'</a> ';
					}
					$offset2 += ROWS_PER_PAGE;
				}
				if ($endPage != $numPages) {
					$lastOffset = ($numPages-1)*ROWS_PER_PAGE;
					$queryString = $queryStringPart."&offset=".$lastOffset;
					echo '... <a href="?'.$queryString.'">Sista</a> ';
				}
			} else {
				$offset2 = 0;
				echo 'Sida ';
				for ($i = 1; $i <= $numPages; $i++) {
					if ($offset2 == $offset)
					echo $i.' ';
					else {
						$queryString = $queryStringPart."&offset=$offset2";
						echo '<a href="?'.$queryString.'">'.$i.'</a> ';
					}
					$offset2 += ROWS_PER_PAGE;
				}
			}
		}
	}


	protected function _makeSearchString($str)
	{
		$words = explode(' ' , $str);
		
		$words2 = array();
	
		// include all words except empty and MySQL stopwords
		foreach ($words as $word) {
			if (!empty($word)) {
				if (!isMySqlStopword($word)) {
					$words2[] = '+'.$word.'*';
				}
			}
		}
		
		$newStr = implode(" ", $words2);
				
		return $this->_mysqli->real_escape_string($newStr);
	}

}

?>