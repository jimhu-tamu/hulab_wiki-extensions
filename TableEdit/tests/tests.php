<?php

require_once('class.wikiBox.php');

function array_union($a, $b) {
	$union = array_merge($a, $b); // duplicates may still exist
	$union = array_unique($union);
	return $union;
}

// checks if array1 is a subset of array2
function array_subset( $a, $b ) {
    return ( count( array_diff( array_merge($a,$b), $b)) == 0 ) ? true : false;
}

function is_not_empty_string($str){
	return ($str === "") ? false : true;
}

function compareRows( $old_row_obj, $new_row_obj ){
	// explode out row data
	$old_row_data_fields = explode ('||', $old_row_obj->row_data);
	$new_row_data_fields = explode ('||', $new_row_obj->row_data);	
	// trim off whitespace and delete empty fields
	for($i=0, $c=count($old_row_data_fields); $i<$c; $i++){
		$old_row_data_fields[$i] = trim($old_row_data_fields[$i]);	
	}
	$old_row_data_fields = array_filter($old_row_data_fields, 'is_not_empty_string');
	for($i=0, $c=count($new_row_data_fields); $i<$c; $i++){
		$new_row_data_fields[$i] = trim($new_row_data_fields[$i]);	
	}
	$new_row_data_fields = array_filter($new_row_data_fields, 'is_not_empty_string');	
	// do some set comparison 
	if(count(array_intersect($old_row_data_fields, $new_row_data_fields)) == 0){
		// they have no common elements, they're totally different
		return false;
	} else {
		// they have at least one common element
		if(count(array_union($old_row_data_fields, $new_row_data_fields)) == count($old_row_data_fields)){
			// they are exactly the same 
			return true; 
		} else {
			// check if old is a subset of new
			if(array_subset($old_row_data_fields, $new_row_data_fields)){
				return $new_row_obj;
			} else {
				return false;
			}
		}
	}		
}
	
	
$old_row = new wikiBoxRow;
$old_row->row_data = "abc  ||   ||hij   ||klm";


$new_row = new wikiBoxRow;
$new_row->row_data = "abc||def ||  hij||klm";

$ret = compareRows($old_row, $new_row);

var_dump($ret);
?>