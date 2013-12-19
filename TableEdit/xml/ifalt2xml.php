<?php

/*

	IFALT to XML translator.

*/
function parseLine($line){
	$tmp = explode("\t",$line);
	if (isset($tmp[6])) {
		// Follows the specification at [http://us2.php.net/parse_str]
		parse_str(trim($tmp[6]), $data); # parse column 7
	}
	if (isset($tmp[0])) {
		$data['page_name'] = trim($tmp[0]);
	}
	if (isset($tmp[1])) {
		$data['page_template'] 	= trim($tmp[1]);
	}
	if (isset($tmp[2])) {
		$data['table_template'] = trim($tmp[2]);
	}
	if (isset($tmp[3])) {
		$data['row_data'] = trim($tmp[3]);
	}
	if (isset($tmp[4])) {
		$data['metadata'] = trim($tmp[4]);
	}
	if (isset($tmp[5])) {
		$data['update_type'] = trim($tmp[5]);
	}
	return $data;
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
$options = getopt('f:');
$params = parseParameters($options);
#print_r($params);

if (isset($params['f'])){
	$file = $params['f'];
} else {
	die("Please use the -f flag to specify a file to read.\n");
}


$doc = new DOMDocument();
$doc->formatOutput = true;
$loader_node = $doc->createElement('loader');
$doc->appendChild($loader_node);

$all_pages = array();
if(is_file($file)){
	$infile = fopen($file, 'r');
	if(!$infile){ 
		die("Cannot open $file for reading.\n");
	}
	while(!feof($infile)){
		$line = fgets($infile, 4096);
		if(trim($line) == "") continue;
		$ifalt = parseLine($line);
		foreach( $ifalt as $key => &$value){
			if($value == "NULL" || is_null($value)){
				$value = "";
			}
		}
		$page_node = $doc->createElement('page');
		// if we've seen this page before...		
		if(in_array($ifalt['page_name'], array_keys($all_pages))){
			// get a list of all the page nodes
			$pages = $doc->getElementsByTagName('page');
			// loop through them...
			foreach($pages as $page_node){
				// get their name...
				$name = $page_node->getElementsByTagName('name');
				if(count($name) > 1) die("More than one name for page " . $ifalt['page_name']);
				// ...and if it's the correct page node...
				if(strcmp($name->item(0)->nodeValue, $ifalt['page_name']) == 0) {
					// check to see if we've seen this table before
					if(in_array($ifalt['table_template'], $all_pages[$ifalt['page_name']])){
						// if we have, then get all the table nodes for this page
						$tables = $page_node->getElementsByTagName('table');
						// loop through them...
						foreach($tables as $table_node){
							// get their template
							$table_template_node = $table_node->getElementsByTagName('template');
							if(count($table_template_node) > 1) die("More than one template for table " . $ifalt['table_template'] . "on page " . $ifalt['page_name']); 
							// ...and if this is the correct table, add the row information to it
							if(strcmp($table_template_node->item(0)->nodeValue, $ifalt['table_template']) == 0){
								// grap the row_data node
								$row_data_node = $table_node->getElementsByTagName('row_data');
								if(count($row_data_node) > 1) die("More than one set of row_data for table " . $ifalt['table_template'] . "on page " . $ifalt['page_name']);
								// create a new row node
								$row_node = $doc->createElement('row');
								// populate it with the actual data
								$fields = explode('||', $ifalt['row_data']);
								foreach($fields as $field){
									$field_node = $doc->createElement('field');
									$field_node->appendChild(
											$doc->createTextNode($field)
										);
									$row_node->appendChild($field_node);
								}
								// and add the update type
								$update_type_node = $doc->createElement('update_type');
								$update_type_node->appendChild(
										$doc->createTextNode($ifalt['update_type'])
									);
								$row_node->appendChild($update_type_node);
								$row_metadata_node = $doc->createElement('metadata');
								$row_metadata_node->appendChild(
										$doc->createTextNode($ifalt['metadata'])
									);
								$row_node->appendChild($row_metadata_node);
								// append the row node to the row_data node
								$row_data_node->item(0)->appendChild($row_node);	
							}
						}
					} else {
						// we haven't seen this particular table for this page, make a new one
						$all_pages[$ifalt['page_name']][] = $ifalt['table_template'];
						$table_node = $doc->createElement('table');
						// add template info
						$table_template_node = $doc->createElement('template');
						$table_template_node->appendChild(
								$doc->createTextNode($ifalt['table_template'])
							);
						$table_node->appendChild($table_template_node);
						// add row data
						$row_data_node = $doc->createElement('row_data');
						// add a new row...
						$row_node = $doc->createElement('row');
						// ..its metadata...
						$row_metadata_node = $doc->createElement('metadata');
						$row_metadata_node->appendChild(
								$doc->createTextNode($ifalt['metadata'])
							);
						$row_node->appendChild($row_metadata_node);
						// ...its data...
						$fields = explode('||', $ifalt['row_data']);
						foreach($fields as $field){
							$field_node = $doc->createElement('field');
							$field_node->appendChild(
									$doc->createTextNode($field)
								);
							$row_node->appendChild($field_node);
						}
						// ...and add the update type
						$update_type_node = $doc->createElement('update_type');
						$update_type_node->appendChild(
								$doc->createTextNode($ifalt['update_type'])
							);
						$row_node->appendChild($update_type_node);
						$row_data_node->appendChild($row_node);
						$table_node->appendChild($row_data_node);
						$table_metadata_node = $doc->createElement('metadata');
						$table_node->appendChild($table_metadata_node);
						$table_misc_node = $doc->createElement('misc');
						$table_node->appendChild($table_misc_node);
						$page_node->appendChild($table_node);	

					}
				}
			}
		} else {
			// we haven't seen this page before. new entry.
			$all_pages[$ifalt['page_name']] = array($ifalt['table_template']);
			$page_name_node = $doc->createElement('name');
			$page_name_node->appendChild(
					$doc->createTextNode($ifalt['page_name'])
				);
			$page_node->appendChild($page_name_node);
			$template_node = $doc->createElement('template');
			$template_node->appendChild(
					$doc->createTextNode($ifalt['page_template'])
				);
			$page_node->appendChild($template_node);
			$table_node = $doc->createElement('table');
			$table_template_node = $doc->createElement('template');
			$table_template_node->appendChild(
					$doc->createTextNode($ifalt['table_template'])
				);
			$table_node->appendChild($table_template_node);
			$row_data_node = $doc->createElement('row_data');
			$update_type_node = $doc->createElement('update_type');
			$update_type_node->appendChild(
					$doc->createTextNode($ifalt['update_type'])
				);
			$row_node = $doc->createElement('row');
			$row_metadata_node = $doc->createElement('metadata');
			$row_metadata_node->appendChild(
					$doc->createTextNode($ifalt['metadata'])
				);
			$fields = explode('||', $ifalt['row_data']);
			foreach($fields as $field){
				$field_node = $doc->createElement('field');
				$field_node->appendChild(
						$doc->createTextNode($field)
					);
				$row_node->appendChild($field_node);
			}
			$row_node->appendChild($row_metadata_node);
			$row_node->appendChild($update_type_node);
			$row_data_node->appendChild($row_node);
			$table_node->appendChild($row_data_node);
			$table_metadata_node = $doc->createElement('metadata');
			$table_node->appendChild($table_metadata_node);
			$table_misc_node = $doc->createElement('misc');
			$table_misc_node->appendChild(
					$doc->createTextNode('')
				);
			$table_node->appendChild($table_misc_node);
			$page_node->appendChild($table_node);	

			$loader_node->appendChild($page_node);
		}
	}
} else {
	die("Can't load non-file.\n");
}
#var_dump($all_pages);
echo $doc->saveXML();
?>
