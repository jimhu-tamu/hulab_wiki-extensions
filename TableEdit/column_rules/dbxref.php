<?php
/*
This column rule helps constrain user input when entering database cross-references.
For example, we want to let the user pick the cross-reference
	DBID:Accession
	PMID:1223456
Allowed DBIDs will come from the column rule. Multiple entries allowed or restricted based on parameters
*/

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:dbxref',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Special column type for dbxrefs, including publications',
    'version'=>'0.1'
);


class ecTableEdit_dbxref extends TableEdit_Column_rule{

	# constructor inherited
	
	function make_form_row(){
		/*
		$wdata is holds the column data in a string, which will be a set of lines for multiple
		first we replace the : in any line that ends in a :, which would be a dbxref key with no value, e.g. PMID:
		then we split the lines into an array, $wlines
		*/
		$wdata = str_replace(":\n",':',trim($this->col_data,"\n*"));
		$wdata = str_replace("\n*", "\n", $wdata);
		$wlines = preg_split("/\n|<br(\s\/)?>/", $wdata); #print_r($wlines);
		
		/*
		$options is an array of the pulldown menu items we need.
		we use array_slice to remove the first item, which is the name of the rule itself
		then we test whether the first option is 'unique", which is the setting to allow only one value.
		*/
		$options = array_slice($this->rule_fields,1);
		$unique = 0;
		if ($options[0] == 'unique'){
			$unique = 1;
			array_shift($options); 
		}
		# if 'other' isn't already an option, add it to the end of the list of options.
		if(!in_array('other', $options)) $options[] = 'other';
		# if an empty value, '' isn't already an option, add it to the list of options as the first option.
		if(!in_array('', $options)) array_unshift($options,'');
		if ($unique == 0){
			#unset anything with a key but no value
			foreach ($wlines as $k => $tmp){
				if(trim($tmp,':') != $tmp) unset ($wlines[$k]);
			}
			# add a blank line to the end if we allow multiple lines
			$wlines[] = '';
		}
		
		#iterate through the lines and make the pulldown menu for each item.
		foreach ($wlines as $wline){
			# identify the selected prefix
			list($prefix) = explode(':', $wline);
			# extract the value by replacing the prefix:
			$acc = str_replace("$prefix:",'',$wline);
			
			# initialize the menu 
			$menu = "<select name = 'field[$this->col_index][]'>";
			# if the prefix for an existing item isn't already in the list of options, add it.
			if (isset ($acc) && !in_array($prefix, $options)) $options[] = trim($prefix);
			# make the menu options
			foreach ($options as $option){
				# set the selected option to the preexisting prefix, if applicable
				$selected = '';
				if (isset($prefix) && $option == $prefix) $selected = 'selected';
				# make sure we have a : separator
				if ($option != '') $option .= ':';
				# add the item to the list of options
				$menu .= "<option label='$option' value='$option' $selected>$option</option>";
			}
			# validate the field content
			$acc = $this->validate_dbxref($prefix, $acc);
			# finish off the menu and add the text input field
			$menu.= "</select>".XML::input("field[$this->col_index][]",50,trim($acc), array('maxlength'=>255, 'onchange'=>'this.form.submit();'));
			# add the item for this line to the collection of pulldowns attached to input boxes.
			$menu_items[] = $menu;
			# I forget what this is for!
			$tmp = null;
		}
		# sort it and return the whole form.
		sort($menu_items);
		return implode('<br />', $menu_items);

	}
	
	function validate_dbxref($option, $acc){
		switch ($option){
			case 'PMID':
				#PMID has no leading zeroes
				$acc = ltrim($acc, ' 0');
				if(preg_match('/[^\d]/',$acc)){ 
					$this->error .= "<div style='color:red'>PMID:$acc doesn't look like a PMID</div>";
					$this->row_save_ok = false;
				}	
				if(preg_match('/PMC|pmc/',$acc)){ 
					$this->error .= "<div style='color:red'>On Pubmed Central pages, see PubMed under Links to go to pubmed find the PMID</div>";
					$this->row_save_ok = false;
				}
				break;
		}
		return $acc;
	}

	function show_data(){
		$this->col_data = str_replace(":\n", ":", trim($this->col_data, "*\n"));
		if(strpos($this->col_data, "\n*") > 0 ){
			$this->col_data = "*$this->col_data";
		}else{
			$this->col_data = str_replace("\n", "\n*", $this->col_data);
		}
		return $this->col_data;
	}
}
