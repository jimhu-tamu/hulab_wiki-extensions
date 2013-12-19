<?php
// do the command-line flag options stuff
if (!isset($argv[1])){
	echo "USAGE:
php5 {$_SERVER['SCRIPT_NAME']} [ options ] 
   
 -c <config file>
    A file specifying each of the above as variables:
    \$wikipath
    	The path to the wiki to use.
    \$genome_name 
    	The name of the genome. Gets prepended to the gene name.
    \$nc_number
    	NCBI/RefSeq identifier, with or without the \"NC_\" prefix.
    \$gbrowse_interwiki
    	Name of the gbrowse interwiki link to use.
    \$gbrowse_name
    	Name of the organism in Gbrowse.
    \$taxon_id
    	NCBI taxonomy identifier (number.)
    
";
	exit;
}

// ==================== functions ==============================

/*
	This will make an entry (typically one single line from a *.table file) into it's 
	corresponding data structure for use later.	
*/
function breakIntoDataStructure( $entry ){
	if(!is_string($entry)) return;
	$s = array();
	$key_value_pairs = explode("\t", $entry);
	foreach($key_value_pairs as $pair){
		list($key, $value) = explode("#", $pair);
		$s[trim($key)] = trim($value);
	}
	foreach($s as $k => $v){
		if(preg_match("/!_!/", $v, $m)){
			$values = explode("!_!", $v);
			foreach($values as &$value){
				$value = trim($value);
			}
			$s[trim($k)] = $values;
		}
	}
	/*
	// do lots of unnecessary logic in making $s['desc']
	if(isset($s['note']) || isset($s['function'])){
		if(isset($s['note']) && isset($s['function'])){
			$s['desc'] = $s['note'] . "<br />\n" . $s['function'];
		} else {
			if(isset($s['note'])) $s['desc'] .= $s['note'];
			if(isset($s['function'])) $s['desc'] .= $s['function'];
		}
	} else {
		$s['desc'] = $s['product'];
	}
	*/
	return $s;
}

function parseParameters($noopt = array()) {
	$result = array();
	$params = $GLOBALS['argv'];
	// could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
	reset($params);
	while (list($tmp, $p) = each($params)) {
		if ($p{0} == '-') {
			$pname = substr($p, 1);
			$value = true;
			if ($pname{0} == '-') {
				// long-opt (--<param>)
				$pname = substr($pname, 1);
				if (strpos($p, '=') !== false) {
					// value specified inline (--<param>=<value>)
					list($pname, $value) = explode('=', substr($p, 2), 2);
				}
			}
			// check if next parameter is a descriptor or a value
			$nextparm = current($params);
			if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-') list($tmp, $value) = each($params);
			$result[$pname] = $value;
		} else {
			// param doesn't belong to any option
			$result[] = $p;
		}
	}
	return $result;
}

function make_field( $attrib, $content){
	global $xml;
	$xml->startElement('field');
	foreach($attrib as $k => $v){
		$xml->writeAttribute($k, $v);
	}
	$xml->writeCData($content);
	$xml->endElement();
}

function make_update_type( &$type = "merge" ){
	global $xml;
	$type = strtolower(trim($type));
	$array = array("append", "merge", "clear");
	if(!in_array($type, $array)) die("Incorrect update_type\n.");
	$xml->startElement('update_type');
	$xml->writeCData($type);
	$xml->endElement();
}

function make_metadata( $metadata ){
	global $xml;
	$metadata = trim($metadata);
	$xml->startElement('metadata');
	$xml->writeCData($metadata);
	$xml->endElement();
}



$options = getopt('g:n:b:w:i: c');
$params = parseParameters($options);

if(isset($params['c'])){
	if(!is_file($params['c'])) die("$params[c] is not a valid file.\n");
	require_once($params['c']);
} else {
	die("Please specify a config file with the -c option.\n");
}
if(!isset($wikipath)) die("The variable \$wikipath is not set.\n");
if(!isset($genome_name)) die("The variable \$genome_name is not set.\n");
if(!isset($nc_number)) die("The variable \$nc_number is not set.\n");
if(!isset($gbrowse_interwiki)) die("The variable \$gbrowse_interwiki is not set.\n");
if(!isset($gbrowse_name)) die("The variable \$gbrowse_name is not set.\n");
if(!isset($taxon_id)) die("The variable \$taxon_id is not set.\n");

if(is_file($wikipath . "/maintenance/commandLine.inc") ){
	require_once($wikipath . "/maintenance/commandLine.inc");
} else {
	die("Cannot find \"{$wikipath}/maintenance/commandLine.inc\", please check the path.\n");
}
if(preg_match("/[Nn][Cc]_(\d+)/", $nc_number, $m)){
	$nc_number = $m[1];
}
require_once("/usr/local/phpwikibots/trunk/epoch.php");
			
$phage= fopen("php://STDIN", "r");

// send a header just in case
	//header('Content-type: application/xml');
	header('Content-Type: text/plain');

$xml = new XMLWriter();
$xml->openURI('php://output');
$xml->startDocument('1.0', 'UTF-8');
$xml->setIndent(true);
$xml->setIndentString("  ");

$xml->startElement('loader');

// root node
while (!feof($phage)) {
	$line=fgets($phage);
	if(trim($line) == "") continue;
	$data = breakIntoDataStructure($line);
	//var_dump($data);
	
	// set the gene name
	if($data['gene'] == "NO_INFO"){
		//$data['gene'] = $data['product'];
		$data['gene'] = $data['locus_tag'];
	}
	
	// add units to molecular weight.
	if(isset($data['MW'])){
		$data['MW'] .= "kDa (calc)";
	}
	
	$generalized_page_name = $genome_name . " " . $data['gene'];
	$page_set = new EW_Gene_Set($generalized_page_name);
	
	foreach($page_set->pages as $template => $page_obj){
		$xml->startElement('page');
		
		$xml->startElement('name');
		$xml->writeCData($page_obj->article->mTitle->getDBkey());
		$xml->endElement(); // loader->page->name
		
		$xml->startElement('template');
		$xml->writeCData($template);
		$xml->endElement(); // loader->page->template		

		foreach ($page_set->table_types[$template] as $table_type) {
			$xml->startElement('table');

			$xml->startElement('template');
			$xml->writeCData($table_type);
			$xml->endElement(); // loader->page->table->template

			switch($table_type){
				case 'Quickview table':
					$xml->startElement('row');
					make_field(array("heading"=>"Gene Name"), "{$data['gene']} (synonyms: {$data['locus_tag']})");
					make_field(array("heading"=>"Product(s)"), $data['product']);
					$xml->endElement(); 
					break;					
				case 'Gene nomenclature table':
					$xml->startElement('row');
					make_field(array('heading'=>'Standard name'), $data['gene']);
					make_field(array('heading'=>'Mnemonic'), $data['locus_tag']);		
					$xml->endElement(); // loader->page->table->row
					break;				
				case 'Gene location table':	
					preg_match("/
								 (complement)?
								   \(?
								     (join)?
								       \(?
								         (\d+)\.\.(\d+)
								         (?:,(\d+)\.\.(\d+))?
								       \)?
								     \)?  
								/xms", 
							   $data['Co-ordinates'], $m);
							   
					// NOTE: m[0] is the string, m[1] is complement or emtpy, m[2] is join or empty, and m[3],m[4] are the coords
					//var_dump($m);
					
					$xml->startElement('row');
					// make_field(array('heading'=>'Strain'), "");
					// make_field(array('heading'=>'Map location'), "");
					
					// NC_%s: 1235..1827<br />{{GBrowseFigureByCoord|1235|1827|bacteriophage_MS2|NC_001417}}
					$a = ($m[3] >= 100) ? $m[3] - 100 : "0" ;
					$b = $m[4] + 100;	
					if ($m[1] != "") { 
						$display_coords = $m[4] . ".." . $m[3];
					} else {
						$display_coords = $m[3] . ".." . $m[4];
					}
					make_field(
					 	array('heading'=>'Genome coordinates'), 
						sprintf("NC_%s: %s<br />{{GBrowseFigureByCoord|%s|%s|%s|NC_%s}}", $nc_number, $display_coords, $a, $b, $gbrowse_name, $nc_number)
					);
					make_field(
						array('heading'=>'Genome browsers'), 
						sprintf("*[[%s:NC_%s:%s..%s|Gbrowse(NC_%s)]]", $gbrowse_interwiki, $nc_number, $a, $b, $nc_number)
					);
					make_field(
						array('heading'=>'Sequence links'),
						sprintf("*[[gbrowse:%s/?plugin=FastaDumper&name=NC_%s:%s..%s&plugin_action=Go|FASTA Dump]]", $gbrowse_name, $nc_number, $a, $b)
					);
					$xml->endElement(); // loader->page->table->row
					break;					
				case 'Gene sequence features table':
					break;
				case 'Gene allele table':
					break;
				case 'Gene resource table':
					break;
				case 'Gene accessions table':
					foreach($data['db_xref'] as $dbxref){
						$xml->startElement('row');
						list($db, $id) = explode(":", $dbxref);
						switch ($db){
							case 'GI':
								$acc_link = sprintf("[[ncbi:%s|GI:%s]]", $id, $id);
								$database_link= "[http://www.ncbi.nlm.nih.gov/ NCBI] ([[NCBI|EcoliWiki Page]])";
								break;
							case 'GOA':
							case 'UniProtKB/Swiss-Prot':
								$acc_link = sprintf("[[uniprot:%s|UniProt:%s]]", $id, $id);
								$database_link= "[http://www.uniprot.org/ UniProt] ([[UniProt|EcoliWiki Page]])";
								break;
							case 'GeneID':	
								$acc_link= "[http://www.ncbi.nlm.nih.gov/sites/entrez?db=gene&cmd=Retrieve&dopt=full_report&list_uids=$id $db:$id]";
								$database_link= "[http://www.ncbi.nlm.nih.gov/ NCBI] ([[NCBI|EcoliWiki Page]])";
								break;
						}
						make_field(array('heading'=>'Database'), $database_link);
						make_field(array('heading'=>'Accession'), $acc_link);
						$xml->endElement();
					}
					break;
				case 'Links table':
					break;					
				case 'Product nomenclature table':
					$xml->startElement('row');
					make_field(array('heading'=>'Standard name'), $data['gene']);
					make_field(array('heading'=>'Synonyms'), $data['locus_tag']);
					make_field(array('heading'=>'Product description'), $data['product']);
					$xml->endElement();
					break;
				case 'Go table product':
					break;
				case 'Product interactions table':
					break;
				case 'Product localization table':
					break;
				case 'Product phys prop table':
					$xml->startElement('row');
					$seq = "<pre>".wordwrap(wordwrap($data['translation'], 10, ' ',true), 70, "\n")."</pre>";
					make_field(array('heading'=>'Sequence'), $seq);
					make_field(array('heading'=>'Mol. Wt'), $data['MW'] . " (calc)");
					make_field(array('heading'=>'pI'), $data['pi']);
					$count_chars = count_chars($data['translation'], 1);
					$num_Y = $count_chars[89];
					$num_W = $count_chars[87];
					$num_C = $count_chars[67];
					make_field(
						array('heading'=>'Extinction coefficient'), 
						sprintf("%s (calc based on %d Y, %d W, and %d C residues)", $data['ExtinctionCoefficient'], $num_Y, $num_W, $num_C)
					);
					$xml->endElement();
					break;
				case 'Product motif table':
					break;
				case 'Product structure table':
					$xml->startElement('row');
					make_field(
						array('heading'=>'Structures'),
						"<beststructure>{$data['gene']}\n{$taxon_id}</beststructure>"
					);
					make_field(
						array('heading'=>'Models'),
						"View models at:\n*"
					);
					$xml->endElement();
					break;
				case 'Product resource table':
					break;
				/*	
				case 'Gene accessions table':
					$gact_row_data = create_row_data ($single, 'db_xref', 'Product');
					foreach($gact_row_data as $row_data){
						if (!check_only_pipes ($row_data)) {
							print $finished_row . $prod_tbl . "\t" . $row_data . "\n";	
						}
					}
					break; 
				*/
				case 'Links table':
					break;	
				case 'Expressions operons table':
					$eot_row_data="||";						// Anands file has no data and there is only 1 line, but I put in a double pipe to be true in our check_only_pipes funtn
					if (!check_only_pipes ($eot_row_data)) {
					print $finished_row . $expr_tbl . "\t" . $eot_row_data. "\n";
					}
					break;	
				case 'Expression allele table':
					break;
				case 'Expresssion studies table':
					break;
				case 'Expression resources table':	
					break;	
				case 'Gene accessions table':
					break;
				case 'Links table':	
					break;
				case 'Evolution homologs table':
					break;
				case 'Evolution families table':	
					break;
				case 'Links table':									
					break;	
			}	// end switch for tables
			
			$xml->endElement(); // loader->page->table
		}
		$xml->endElement(); // loader->page
	}
}
$xml->endElement(); // loader
?>