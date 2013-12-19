<?php
/*
This is preprocessor code for modifying go_annotation rules for Cacao

For testing, used a cu from a known user

	$cu = CacaoUser::newFromName('Singhpr4');

*/
if ($box->column_names[$i] == 'evidence' && class_exists('CacaoUser')){ 
	global $wgUser, $wgCacaoEvidenceCodes;
	$cu = CacaoUser::newFromID($wgUser->getId());
	if($cu->isParticipant() && !$cu->isInstructor()){
		$rule_fields = array_merge(array('select'), $wgCacaoEvidenceCodes);
	}
}
