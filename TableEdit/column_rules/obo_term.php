<?php
/*
Generalized column rule for entering ontology ids and 
looking up ontology term names from them.

Eventually, we would also like to do the reverse as well

Usage:
rule fields examples
	obo_term|omp_id
	obo_term|omp_term
	obo_term|eco_id
	obo_term|eco_term

Jim Hu 6/10/2014 - started. Built around go_annotation2 approach.

*/

# the class name must be ecTableEdit_ followed by the name of the column rule
class ecTableEdit_OBO_term extends TableEdit_Column_rule{

	private $ontology;
	private $dbi;
	public $term;

	# the constructor instantiates the object. 
	# wgCodePath is the path to the hulab code library, which has the OntologyProject classes.
	function __construct($te, $box, $rule_fields, $row_id, $row_data, $col_index){
		global $wgCodePath;
		parent::__construct($te, $box, $rule_fields, $row_id, $row_data, $col_index);
		# instantiate classes to register the other classes and provide bases for extension.
		require_once ($wgCodePath.'library/OntologyProject.php');
		$project = new OntologyProject;
		$project->register_class('OntologyArchive2', $wgCodePath.'library/OntologyProject/OntologyDBI/OntologyArchive2.php');
		
		# create db interface. 
		$this->dbi = new OntologyArchive2('obo_archive');
		# set the ontology to use and find the term
		list($this->ontologyName, $this->fieldType) = explode('_', strtolower($rule_fields[1]));
		$this->ontology = $this->dbi->getOntologyByName($this->ontologyName);	
		$this->get_term(self::correct_obo_id($this->row_hash[$this->ontologyName."_id"], $this->ontologyName));
	#	echo "<pre>";print_r($this->term);echo "</pre>";
		$this->box->row_tooltips = array(
		#	"Evidence Code" => "Evidence Codes are used to describe the kind of experiment used to infer the function described by the GO term"
		
		);
	}

	/*
	This method overloads make_form_row in the abstract class.  In this case it takes column data
	that is in wiki markup for an unordered list with three items, splits it up, and makes three
	input text boxes, one for each row in the list.
	*/
	function make_form_row(){
		switch($this->fieldType){
			case 'id':
				return $this->obo_id();
				break;
			case 'term':
				return $this->obo_term();
				break;
			default:
				$form = parent::make_form_row();
		
		}
		return $form;
	
	}

	# overload to make the data a list
	function show_data(){
		$tmp = str_replace("\n*","\n",trim($this->col_data));
		$tmp = preg_replace("/^\*/","",$tmp);
		$value = explode("\n",$tmp);
		if(count($value) > 1) $this->col_data = "*".implode("\n*",$value);
		#echo $this->col_data."\n";
		return $this->col_data;
	}
	
	function correct_obo_id($term_id, $ontologyName){
		$ontologyName = strtoupper($ontologyName);
		switch($ontologyName){
			case 'OMP':
				if(preg_match('/^('.$ontologyName.':)*\s*(\d{1,7})/', $term_id, $m)){
					$term_id = "$ontologyName:".str_pad($m[2],7,'0',STR_PAD_LEFT);
				}
				break;
		}
		return $term_id;
	}
	
	/*
	Form input for the OMP term id includes some validation and testing
	*/
	function obo_id(){
		$obo_id = str_replace(' ','', strtoupper(trim($this->col_data)));
		#fall through if blank
		if($obo_id != ''){
			# fall through to check if it looks right already
			if(!preg_match('/^'.$this->ontologyName.':\d+$/',$obo_id)){
				$new_obo_id = self::correct_obo_id($obo_id, $this->ontologyName);
				if ($new_obo_id != $obo_id){
					$obo_id = $new_obo_id;
					$this->error =
						"<div style='color:green'>Guessing that when you wrote $this->col_data you meant...</div>";
				}
			}
			if(trim($obo_id) != '' && $this->term->name == ''){
				$obo_id = $this->col_data;
				$this->error =
					"<div style='color:red'>$this->col_data doesn't look like a $this->ontologyName id</div>";
			}
		}
		return TableEditView::form_field($this->col_index, $obo_id, 40,'text');
		
	}
	
	function obo_term(){
		return $this->term->name.Html::hidden('field[]',$this->term->name);
	}
	
	function get_term($id){
		$this->term = new OntologyTerm; #$id = 'GO:0000003';
		$this->term->setTag('id', $id);
		$x = $this->dbi->getLastTermRevision($this->term);
		if (is_object($x)) $this->term->setStanza($x->term_text);
	}



}