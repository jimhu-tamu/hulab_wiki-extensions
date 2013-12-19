<?php
# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:protein_properties',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Special column type for genome coordinates',
    'version'=>'0.1'
);


# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditProteinPropertiesColumn';

function efTableEditProteinPropertiesColumn( $te, $rule_fields, $box, $row_data, $i, $type ){
	if (!in_array($rule_fields[0], array( 'prot_length','prot_mw','prot_pi','prot_extcoeff')) ) return true;
	$type = 'protprop'; #echo "</pre>";

	$sequence = strtolower(strip_tags($row_data[1])); 
	$sequence = preg_replace("/\[.*?\]/",'',$sequence);
	$sequence = preg_replace("/[^acdefghiklmnpqrstvwy]/",'',$sequence);
	if ($sequence == ''){ 
		$row_data[$i] = '';
		return true;
	}	
	$aa_mass = array(
		'a' => 71.08,
		'c' => 103.1,
		'd' => 115.1,
		'e' => 129.1,
		'f' => 147.2,
		'g' => 57.05,
		'h' => 137.1,
		'i' => 113.2,
		'k' => 128.2,
		'l' => 113.2,
		'm' => 131.2,
		'n' => 114.1,
		'p' => 97.12,
		'q' => 128.1,
		'r' => 156.2,
		's' => 87.08,
		't' => 101.1,
		'v' => 99.07,
		'w' => 186.2,
		'y' => 163.2
	
	);
	#echo $sequence; echo "<pre>";
	$aa = str_split($sequence);
	$aa_count = array_count_values($aa);
	# initialize, using $aa_mass keys for aa names
	foreach ($aa_mass as $aa_name => $mass){
		$aa_count[$aa_name] = 0;
	}
	switch ($rule_fields[0]){
		case 'prot_length':
			$row_data[$i] = number_format(strlen($sequence));
			break;
		case 'prot_mw':
			# avg masses from http://www.ionsource.com/Card/aatable/aatable.htm
			$mw = 17; # H20 for N and C term
			#print_r($aa_mass);
			foreach ($aa_count as $res => $num){	
				#echo "$res $num ".$aa_mass["$res"]."\n";
				$mw = $mw + $num * $aa_mass[$res];
			}
			if ($mw > 1000) $row_data[$i] = round($mw/1000, 3)." kDa";
			else $row_data[$i] = $mw. " Da";
			break;
		case 'prot_pi':
			# based on http://kobesearch.cpan.org/htdocs/BioPerl/Bio/Tools/pICalculator.pm.html
			# http://fields.scripps.edu/DTASelect/20010710-pI-Algorithm.pdf
			#echo "<pre>";
			# pK values from the alanine peptide data from table 1 of Grimsley et al 2009 Prot Sci. 18:247
			$pK  = array( 
				'N_term'	=>  8.0,
				'k'     	=> 10.4, # Lys
                'r'     	=> 12.5, # Arg
                'h'     	=>  6.5, # His
                'd'     	=>  3.9, # Asp
                'e'     	=>  4.3, # Glu
                'c'     	=>  8.6, # Cys
                'y'     	=> 10.4, # Tyr
                'C_term'    =>  3.7
                 );
		
 		   	$pH = 7.0;
    		$step = 3.5;
   	 		$last_charge = 0.0;
			# start at ph 7 and home in on pH minimum by decreasing size of pH step
			do{
    			$charge =    
    						       _partial_charge( $pK['N_term'], $pH )
   				+ $aa_count['k'] * _partial_charge( $pK['k'], $pH )
   				+ $aa_count['r'] * _partial_charge( $pK['r'], $pH )
   				+ $aa_count['h'] * _partial_charge( $pK['h'], $pH )
   				- $aa_count['d'] * _partial_charge( $pH, $pK['d'] )
   				- $aa_count['e'] * _partial_charge( $pH, $pK['e'] )
   				- $aa_count['c'] * _partial_charge( $pH, $pK['c'] )
   				- $aa_count['y'] * _partial_charge( $pH, $pK['y'])
   				-                  _partial_charge( $pH, $pK['C_term']);
				#echo "$pH\t$charge\n";				
				if ($charge > 0) $pH = $pH + $step; else $pH = $pH - $step;
				$step = $step/2;
			}while(abs($charge) > 0.05);
			$row_data[$i] = number_format($pH,1)." (calculated)";			
			break;
		case 'prot_extcoeff':
			$Ys = $aa_count['y'];
			$Ws = $aa_count['w'];
			$Cs = $aa_count['c'];
			$elow = ((1490 * $Ys) + (5500 * $Ws));
			$ehigh = ((1490 * $Ys) + (5500 * $Ws) + (125 * $Cs));
			$ext = number_format($elow);
			if ($ehigh > $elow) $ext .= " - ".number_format($ehigh);
			$ext .= " (calc based on $Ys Y, $Ws W, and $Cs C residues)"; 
			$row_data[$i] = $ext;
			break;
	}
	return true;
}

# Concentration Ratio is 10**(pK - pH) for positive groups
# and 10**(pH - pK) for negative groups
function _partial_charge ($p1, $p2) {
   $cr = pow(10, ( $p1 - $p2 ));
   return $cr / ( $cr + 1 );
}
