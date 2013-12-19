<?php

/* TableEditCategorySummary.php

base for parser extensions that mine data from other pages based on table templates found in members of a Category

Usage:
<TableEditCategorySummary>
<pages>
lines of page titles to add in addition to category members (if the tag is on a category page)
</pages>
<fields>
fields to include, in order, one per line. See below
</fields>
</TableEditCategorySummary>

fields:
	Syntax
	Table_template:Field varname:display text(optional)

Using more than one table template will get messy unless the templates have the same fields, this thing is not set up to do joins!


*/
$wgHooks['ParserFirstCallInit'][] = 'efTECategorySummaryExtension';

function efTECategorySummaryExtension(Parser $parser) {
	$parser->setHook( "TableEditCategorySummary", "renderTECategorySummary" );
	return true;
}
/*
$input 
Input between the <sample> and </sample> tags, or null if the tag is "closed", i.e. <sample />
$args 
Tag arguments, which are entered like HTML tag attributes; this is an associative array indexed by attribute name.
$parser 
The parent parser (a Parser object); more advanced extensions use this to obtain the contextual Title, parse wiki text, expand braces, register link relationships and dependencies, etc.
$frame 
The parent frame (a PPFrame object). This is used together with $parser to provide the parser with more complete information on the context in which the extension was called.
*/


function renderTECategorySummary($input, array $args, Parser $parser, PPFrame $frame){
	$o = new TableEditCategorySummary($input, $args, $parser, $frame);
	return $o->execute($parser, $frame);
}


class TableEditCategorySummary{
	var $titles = array(); # array of Title objects NOT a TitleArray object!
	var $fields = array();
	var $tables = array();
	protected $magic_words = array(
			'Page'
	
	);

	function __construct($input, array $args, Parser $parser, PPFrame $frame){
		$this->title = $parser->getTitle();
		try{
			$xml = new SimpleXMLElement("<xml>$input</xml>");
			# set title array
			# if category page, add members
			$title = $parser->getTitle();
			if($title->getNamespace() == NS_CATEGORY){
				$cat = Category::newFromTitle($title);
				$members = $cat->getMembers();
				do{
					$this->titles[] = $members->current();
					$members->next();
				}while($members->valid());
			}
			# add members specified in page
			$page_names = explode("\n", trim($xml->pages));
			foreach ($page_names as $page_name){
				$t = Title::newFromText($page_name);
				if(!is_object($t) || !$t->isKnown()) continue;
				$this->titles[] = $t; 
			}
			# parse the fields and id the relevant tables
			$this->fields = explode("\n", trim($xml->fields));
			foreach ($this->fields as $field){
				list($table,$field,$text) = explode(':',"$field::");
				$t = Title::newFromText($table, NS_TEMPLATE);
				if(is_object($t) && $t->isKnown()){ 
					$this->tables[] = $table;
					#make a column index for the table
				}	
			}
			$this->tables = array_unique($this->tables);
		}catch (Exception $e){
			
		}
	
	}

	function execute( Parser $parser, PPFrame $frame){
		
		$text = "\n{|{{PrettyTable}}\n";
		#iterate through the pages and make rows for each
		foreach($this->titles as $title){
			$text .= $this->make_rows($title);
		}
		$text .= "\n|}\n";
		$output = $parser->recursiveTagParse( $text, $frame );
		$output = self::thead($output, $this->fields);
		$output = self::tfoot($output);
        $output.= "<pre>".print_r($this,true)."</pre>";
        $output.= "<pre>".date("Y-m-d h:i:s")."</pre>";
        return $output;
	}
	
	function thead($output, $headings){
		$html = "<table border='2' cellpadding='4' cellspacing='0' style='margin: 1em 1em 1em 0; border: 1px #aaa solid; border-collapse: collapse;' class='tableEdit GO_summary dataTable'>\n<thead>\n<tr>\n";
		foreach($headings as $heading){
			list($table,$field,$text) = explode(':',"$heading::");
			# look for the field as being specified to a table template
			if(in_array($table, $this->tables)){
				$value = $field;
				if(isset($text) && $text != '') $value = $text;
			# if not, check to see if it's a __MagicWord__	
			}elseif(in_array(trim($table,'_'), $this->magic_words)){
				$value = trim($table,'_');
				if(isset($text) && $text != '') $value = $text;			
			}else{
				continue;
			}	
			$html .= "<th>$value</th>\n";
		}
		$html .= "</tr><thead><tbody>";
		$string = str_replace("<table>", $html, $output);
		return $string;	
	}

	function tfoot($output){
		$html = "</tbody>\n<tfoot></tfoot></table>\n";
		$string = str_replace("</table>",$html, $output);
		return $string;
	}
	
	function make_rows($title){
		if(get_class($title) != 'Title') #die(print_r($title,true));
		$row_arr = array();
		# find all the relevant tables on the page
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select(
			array('ext_TableEdit_box'),
			'*',
			array("page_uid='".$title->getArticleID()."'", "template IN ('".implode("','",$this->tables)."')"),
			__METHOD__
		
		);
		# make boxes and iterate through their rows
		while($x = $dbr->fetchObject($result)){
			$box = new wikiBox($x->box_uid);
			$box->set_from_db();
			foreach($box->rows as $row){
				$data_arr = array();
				$rdata = explode('||', $row->row_data);
				foreach ($rdata as $i => $val){
					$data_arr[$box->column_names[$i]][] = $val;
				}
				$columns = array();
				foreach($this->fields as $field){
					list($table,$field,$text) = explode(':',"$field::"); 
					if(in_array(trim($table,'_'), $this->magic_words) ) $columns[] = $this->do_magic($table, $title);
					if(isset($data_arr[$field])) $columns[] = implode('; ',$data_arr[$field]);
				}
				$row_arr[] = "|-\n|".implode('||',$columns);
			}				
		}
		return implode("\n", $row_arr)."\n";
	}
	
	function do_magic($field, $title){
		switch (trim($field,'_')){
			case 'Page':
				return '[['.$title->getText().']]';
				break;
			default:
				return $field;
		}
	}

}