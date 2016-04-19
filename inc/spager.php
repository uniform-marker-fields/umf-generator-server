<?php
require_once('database.php');

class Pager {

	private $this_script;
	private $page_count;
	private $cur_page;
	private $rows_on_page;
	private $request_path;

    function __construct($this_script, $page_count, $current_page, $rows_on_page = ROWS_ON_PAGE, $request_path='') {
		$this->this_script = $this_script;
		$this->page_count = $page_count;
		$this->cur_page = $current_page;
		$this->rows_on_page = $rows_on_page;
		$this->request_path = $request_path;
	}


	function doPagingFirstEnd($range='2') {
		$request_script = $this->this_script.'(';
		$current_page= $this->cur_page;
		if($this->request_path != '') {
			$request_script .= $this->request_path.','; 
		}
		echo "\n<span class=\"pager\">";
		$from_page = max($current_page-$range, 1);
		$to_page = min($current_page+$range, $this->page_count);
		
		if(($current_page != 1) && ($from_page != 1)) {
			echo '<input type="button" onClick="'.$request_script.'1)" value="first" />';
			echo " ... ";
		}
		for($i=$from_page; $i<=$to_page; $i++) {
			if($i != $current_page) {
				echo '<input type="button" onClick="'.$request_script.$i.')" value="'.$i.'" />';
			}
			else {
				echo ' <input type="button" disabled="disabled" onClick="'.$request_script.$i.')" value="'.$i.'" />';
			}
		}	
		if(($current_page != $this->page_count) && ($to_page != $this->page_count)) {
			echo " ... ";
			echo '<input type="button" onclick="'.$request_script.$this->page_count.')" value="last" />';
		}
		echo '</span>';
	}
	
	function doPaging($range=2){
		$request_script = $this->this_script.'(';
		$current_page= $this->cur_page;
		if($this->request_path != '') {
			$request_script .= $this->request_path.','; 
		}
		echo "\n<span class=\"pager\">";
		$from_page = max($current_page-$range, 1);
		$to_page = min($current_page+$range, $this->page_count);
		
		if(($current_page != 1) && ($from_page != 1)) {
			echo '<input type="button" onclick="'.$request_script.'1)" value="1" />';
			echo " ... ";
		}
		for($i=$from_page; $i<=$to_page; $i++) {
			if($i != $current_page) {
				echo '<input type="button" onclick="'.$request_script.$i.')" value="'.$i.'" /> ';
			}
			else {
				echo '<input type="button" disabled="disabled" onClick="'.$request_script.$i.')" value="'.$i.'" />';
			}
		}	
		if(($current_page != $this->page_count) && ($to_page != $this->page_count)) {
			echo " ... ";
			echo '<input type="button" onclick="'.$request_script.$this->page_count.')" value="'.$this->page_count.'" />';
		}
		echo '</span>';
	}

}
?>
