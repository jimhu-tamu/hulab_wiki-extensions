<?php

class TableEdit_Exporter {

	private $formats = array(
		'csv' 				=> 'Comma Separated Values',
		'xls'				=> 'Microsoft Excel compatible',
		'xml'				=> 'XML',
		'serialized'		=> 'Serialized PHP data object',
		'ifalt'				=> 'IFALT (7 column plain text)',
		'html'  			=> 'HTML',
		'wikitext'			=> 'Mediawiki wiki text'
	);

	function getFiletypes() {
		return $this->formats;
	}

	static function getInstance( wikiBox $box, $filetype ) {
		switch( strtolower($filetype)  ) {
			case 'csv':
			case 'xls':
				return new TableEdit_Exporter_CSV( $box  );	
				break;
			case 'xml':
				return new TableEdit_Exporter_XML( $box );
				break;
			case 'serialized':
				return new TableEdit_Exporter_Serialized( $box );
				break;
			case 'html':
				return new TableEdit_Exporter_HTML( $box );
				break;
			case 'wikitext':
				return new TableEdit_Exporter_Wikitext( $box );
				break;				
			case 'ifalt':
			default:
				return new TableEdit_Exporter_IFALT( $box );
				break;
		}
	}	

	public function export() { }		// child classes must implement this function

}

class TableEdit_Exporter_HTML extends TableEdit_Exporter {
	public function __construct( wikiBox $box ) {
		$this->box = $box;		
	}
	
	function export() {	
		global $wgUser, $wgParser;
	
		$wikibox = TableEdit::make_wikibox( $this->box, true );
		$title = Title::newFromText($this->box->page_name);

		$html = $wgParser->parse(
			$wikibox,
			$title,
			ParserOptions::newFromUser( $wgUser )
		)->getText();
		
		return $html;
	}	
}

class TableEdit_Exporter_Wikitext extends TableEdit_Exporter {
	public function __construct( wikiBox $box ) {
		$this->box = $box;		
	}
	
	function export() {	
		return TableEdit::make_wikibox( $this->box, true );
	}	
}

class TableEdit_Exporter_CSV extends TableEdit_Exporter {
	
	// member variables
	private $box;
	private $output;

	// put things places
	public function __construct( wikiBox $box) {
		$this->box 		= $box;
	}
	
	function export() {		
		$box_as_array = $this->build_2D_array_from_rows( $this->box );
		if ($this->box->type == 1) {
			return $this->make_csv_from_array( $this->rotate_2D_array($box_as_array )  );
		}
		else {
			return $this->make_csv_from_array($box_as_array);
		}
	}
	
	function build_2D_array_from_rows( $box ) {
		if(!is_object($box) || !isset($box->rows) || count($box->rows) == 0) return false;
		$arr = array();
		for ($i=0, $c=count($box->rows); $i<$c; $i++) {
			$arr[$i] = explode("||", $box->rows[$i]->row_data);
		}
		return $arr;
	}
	
	function rotate_2D_array( $arr ) {
		$rotated_arr = array();
		for ($i=0, $c=count($arr); $i<$c; $i++) {
			for ($j=0, $k=count($arr[$i]); $j<$k; $j++) {
				$rotated_arr[$j][$i] = $arr[$i][$j];
			}
		}
		return $rotated_arr;
	}
	
	function make_csv_from_array( $arr ) {
		$csv_str = "";
		for ($i=0, $c=count($arr); $i<$c; $i++) {
			foreach ($arr[$i] as $field) {
				$csv_str .= '"' . str_replace('"', '\"', $field) . '",';		// escape double quotes and wrap in quotes.
			}
			if ($csv_str[strlen($csv_str)-1] == ',') {
				$csv_str = substr_replace($csv_str, "", -1);
			}
			$csv_str .= "\n";
		}
		return $csv_str;
	}
}

class TableEdit_Exporter_IFALT extends TableEdit_Exporter {
	
	function __construct( wikiBox $box ) {
		$this->box 		= $box;
	}

	// pull together the row data and print it
	function export() {
	
header('Content-Type: text/plain; charset=utf-8');	
var_dump($this->box);
die();

		$row_data_string = "";
		foreach ($this->box->rows as $row) {
			$this->output .= sprintf(
				"%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
				$this->box->page_name,							// page name
				"",												// page template
				$this->box->template,							// table template
				str_replace("\n", "<br />", $row->row_data),	// row data
				"",												// metadata (box)
				"",												// update type
				""												// other/misc
			);
		}
	
		return $this->output;
	}

}


class TableEdit_Exporter_Serialized extends TableEdit_Exporter {

	function __construct( wikiBox $box ) {
		$this->box 		= $box;
		
		$this->data = $box->get_serialized();
	}

	function export() {
		return $this->data . "\n";
	}	
}


class TableEdit_Exporter_XML extends TableEdit_Exporter {
	
	function __construct( wikiBox $box ) {
		$this->box 		= $box;
		
		$this->doc = new DOMDocument();
		$this->doc->formatOutput = true;		
	}

	function print_xml( $doc ) {
		$root_node = $this->doc->createElement('TableEdit');
		$this->doc->appendChild( $root_node );
		
		
	}

	function export() {
		$page_node = $this->doc->createElement('page');
		
// can't we just iterate through the object and turn it into XML?
header('Content-Type: text/plain; charset=utf-8');	
		foreach (get_object_vars($this->box) as $key => $value) {
		var_dump("$key => $value");
		}
die();

		$page_node->appendChild( 
			$this->make_xml_node('template', $this->box->template)
		);		
		
		// create the table tag
		$table_node = $this->doc->createElement('table');
		
		// create the table template tag
		$template_node = $this->doc->createElement('template');
		$template_node->appendChild(
			$this->doc->createTextNode($this->box->template)
		);
		$table_node->appendChild($template_node);
		
		$row_data_node = $this->doc->createElement('row_data');
		foreach ($this->box->rows as $row) {
			$row_data_node->appendChild(
				$this->make_xml_row_node($row)
			);
		}
		
		
		
		$table_node->appendChild($row_data_node);
		
		
		// append the table to the page
		$page_node->appendChild($table_node);
		
		$this->doc->appendChild($page_node);
		return $this->doc->saveXML();
	}
	
	private function make_xml_node( $node_name, $value, $attributes = array() ) {
		$node = $this->doc->createElement($node_name);
		if (sizeof($attributes) != 0) {
			foreach ($attributes as $k => $v) {
				$a = $this->doc->createAttribute($k);
				$a->appendChild(
					$this->doc->createTextNode($v)
				);
				$node->appendChild($a);
			}
		}
		$node->appendChild(
			$this->doc->createTextNode($value)
		);
		return $node;
	}
	
	private function make_xml_row_node( $r ) {
		$row_node = $this->doc->createElement('row');
		$fields = explode('||', $r->row_data);
		foreach ($fields as $i => $field) {
			$field_node = $this->doc->createElement('field');
			$field_node->appendChild(
				$this->doc->createTextNode($field)
			);
			$attribute = $this->doc->createAttribute('index');
			$attribute->appendChild(
				$this->doc->createTextNode($i)
			);
			$field_node->appendChild($attribute);
			$row_node->appendChild($field_node);
		}
		if (isset($r->row_style) && trim($r->row_style) != "") {
			$style_node = $this->doc->createElement('style');
			$style_node->appendChild(
				$this->doc->createTextNode($r->row_style)
			);
			$row_node->appendChild($style_node);
		}
		$timestamp_node = $this->doc->createElement('timestamp');
		$timestamp_node->appendChild(
			$this->doc->createTextNode($r->timestamp)
		);
		$row_node->appendChild($timestamp_node);		
		foreach ($r->row_metadata as $m) {
			$row_node->appendChild(
				$this->make_xml_metadata_node($m)
			);
		}
		
		return $row_node;
	}
	
	
	private function make_xml_metadata_node( $m ) {
		$node = $this->doc->createElement('metadata');
		
		$metadata_node = $this->doc->createElement('metadata');
		$metadata_node->appendChild(
			$this->doc->createTextNode($m->metadata)
		);
		$node->appendChild($metadata_node);
	
		$timestamp_node = $this->doc->createElement('timestamp');
		$timestamp_node->appendChild(
			$this->doc->createTextNode($m->timestamp)
		);
		$node->appendChild($timestamp_node);		
		
		return $node;
	}
}

?>
