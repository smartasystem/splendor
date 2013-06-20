<?php

define("ROWS_PER_PAGE", 10);

abstract class AbstractController
{
	protected $_pageID;
		
	function __construct()
	{
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
}

?>