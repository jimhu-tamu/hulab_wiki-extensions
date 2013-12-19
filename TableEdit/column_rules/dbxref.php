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

	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
	}

	function make_form_row(){
		$wdata = str_replace(":\n",':',trim($this->col_data));
		$wlines = preg_split("/\n|<br(\s\/)?>/", $wdata); #print_r($wlines);
		$options = array_slice($this->rule_fields,1);
		$unique = 0;
		if ($options[0] == 'unique'){
			$unique = 1;
			array_shift($options); #echo "uniq";
		}
		if(!in_array('other', $options)) $options[] = 'other';
		if(!in_array('', $options)) array_unshift($options,'');
#echo "<pre>";print_r($wdata);echo "</pre>".strpos(" ".$wdata,":\n");
		if ($unique == 0){
			#unset anything with a key but no value
			foreach ($wlines as $k => $tmp){
				if(trim($tmp,':') != $tmp) unset ($wlines[$k]);
			}
			$wlines[] = '';
		}
#echo "<pre>";print_r($lines);echo "</pre>";
		#$wlines = array_unique($wlines);
		foreach ($wlines as $wline){
			list($prefix) = explode(':', $wline);
			$acc = str_replace("$prefix:",'',$wline);

			$menu = "<select name = 'field[$this->col_index][]'>";
			if (isset ($acc) && !in_array($prefix, $options)) $options[] = trim($prefix);
			foreach ($options as $option){
				$selected = '';
				if (isset($prefix) && $option == $prefix) $selected = 'selected';
				if ($option != '') $option .= ':';
				$menu .= "<option label='$option' value='$option' $selected>$option</option>";
			}
			$acc = $this->validate_dbxref($prefix, $acc);
			$menu.= "</select>".XML::input("field[$this->col_index][]",10,trim($acc), array('maxlength'=>255, 'onchange'=>'this.form.submit();'));
			$menu_items[] = $menu;
			$tmp = null;
		}
		sort($menu_items);
		return implode('<br />', $menu_items);

	}
	
	function validate_dbxref($option, $acc){
		switch ($option){
			case 'PMID':
				#PMID has no leading zeroes
				$acc = ltrim($acc, '0');
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
		$this->col_data = str_replace(":\n", ":", $this->col_data);
		return $this->col_data;
	}
}
