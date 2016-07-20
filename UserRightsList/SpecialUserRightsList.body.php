<?php
# version 0.54
class UserRightsList extends UserrightsPage {

	function __construct(){
		SpecialPage::__construct('UserRightsList');
		$this->offset=0;
		$this->limit=50;
		list ($this->user_table,$this->user_groups_table) = wfGetDB(DB_SLAVE)->tableNamesN('user','user_groups');
		return true;
	}

	public function userCanExecute( $user ) {
		global $egUserRightsListChGrp, $wgAddGroups, $wgRemoveGroups;
		if (!isset($egUserRightsListChGrp)) return true;
		foreach ($egUserRightsListChGrp as $group=>$chgrps){
			foreach ($chgrps as $grp)	$wgAddGroups[$group][] = $grp;
			foreach ($chgrps as $grp)	$wgRemoveGroups[$group][] = $grp;
		}
		return parent::userCanExecute( $user );
	}

	function execute( $par ) {
		global $wgRequest, $wgOut, $wgUser;
		$this->setHeaders();
		if( !$this->userCanExecute( $wgUser ) ) {
			// fixme... there may be intermediate groups we can mention.
			global $wgOut;
			$wgOut->showPermissionsErrorPage( array(
				$wgUser->isAnon()
					? 'userrights-nologin'
					: 'userrights-notallowed' ) );
			return true;
		}
		# Get request data from, e.g.
		$fields = array('yearfrom','yearto','monthfrom','monthto','username','offset','limit','group');
		foreach($fields as $field){
			if (!is_null($wgRequest->getVal($field))) $this->$field = $wgRequest->getVal($field);
		}
		if ($wgRequest->getText('act') == 'save') $this->save_rights();
		$output = $this->make_form($this->findMyUsers());
		$wgOut->addHTML( $output );
		return true;
	}

	function save_rights(){
		global $wgRequest;
		$users = $this->findMyUsers();
		foreach ($users as $user){
			$u = User::newFromId($user['user_id']);
			if(is_object($u)) {
				$oldGroups = $u->getGroups();
				$newGroups = $wgRequest->getArray('user_'.$user['user_id']);
				if(is_null($wgRequest->getArray('user_'.$user['user_id']))) $newGroups = array();;
				// remove then add groups
				$removegroup = array_diff($oldGroups, $newGroups);
				foreach ($removegroup as $g){
					$u->removeGroup($g);
				}
		#		echo $u->getName();print_r($removegroup);
				$addgroup = array_diff($newGroups, $oldGroups);
		#		echo $u->getName();print_r($addgroup);
				foreach ($addgroup as $g){
					$u->addGroup($g);
				}
				
			}
		}
		return true;
	}

	# takes an array of users where each user is a hash
	#	user_id, user_name, log_timestamp
	function make_form($users){
		global $wgUser;
		$form = $this->pageTop();
		if (count($users) == 0) return $form.wfMessage('nousersfound')->text();
		$form .= $this->navLinks();
		$form .= "<br/><form method='post' name='editGroup'><table>\n";
		$row = 1; $style = array('',"bgcolor = '#dddddd'");
		$changeable = UserrightsPage::changeableGroups();
		$changeable_groups = array_unique($changeable['add']+$changeable['remove']+$changeable['add-self']+$changeable['remove-self']);
		foreach ($users as $user){
			$mwUser = User::newFromId($user['user_id']);
			$mwUser->loadFromId();

			$form .= "<tr valign='bottom' ".$style[$row]."><td>".$user['user_name'].":</td>";
			foreach ($changeable_groups as $group){
				if (in_array($group, User::getAllGroups())){
					$checked = '';
					if (in_array($group, $mwUser->getGroups())) $checked = 'checked';
					$form .= "<td><input name='user.".$user['user_id']."[]' id='".$user['user_id']."' type='checkbox' value = '$group' $checked>$group</input></td>";
				}
			}
			$form .= "</tr>\n";
			$row++;
			$row = $row%2;
		}
		$form .= "</table>";
		# Preserve params
		if( isset($this->offset) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'offset', 'value' => $this->offset ) );
		if( isset($this->limit) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'limit', 'value' => $this->limit ) );
		if( isset($this->yearfrom) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'yearfrom', 'value' => $this->yearfrom ) );
		if( isset($this->monthfrom) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'monthfrom', 'value' => $this->monthfrom ) );
		if( isset($this->yearto) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'yearto', 'value' => $this->yearto ) );
		if( isset($this->monthto) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'monthto', 'value' => $this->monthto ) );
		if( isset($this->username) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'username', 'value' => $this->username ) );
		if( isset($this->group) )
			$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'group', 'value' => $this->group ) );

		$form .="
		<input name='act' type='submit' value='save'>
		</form>\n";
		$form .= $this->navLinks();

		return $form;

	}

	function pageTop(){
		$self = $this->getTitle();
		$out = '<p>';
		# Form tag
		$out .= Xml::openElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );

		# Group drop-down list
		$out .= Xml::element( 'label', array( 'for' => 'group' ), wfMessage( 'group' )->text() ) . ' ';
		$out .= Xml::openElement( 'select', array( 'name' => 'group' ) );
		$out .= Xml::element( 'option', array( 'value' => '' ), wfMessage( 'group-all' )->text() ); # Item for "all groups"
		$groups = User::getAllGroups();
		array_unshift($groups, 'group-none');
		foreach( $groups as $group ) {
			$attribs = array( 'value' => $group );
			if( isset($this->group) && $group == $this->group ) $attribs['selected'] = 'selected';
			switch ($group){
				case 'group-none':
					$group_name = wfMessage($group)->text();
					break;
				default:
					$group_name = User::getGroupName( $group );
			}
		$out .= Xml::element( 'option', $attribs, $group_name );
		}
		$out .= Xml::closeElement( 'select' ) . ' ';# . Xml::element( 'br' );

		# Username field
		$out .= Xml::element( 'label', array( 'for' => 'username' ), wfMessage( 'usernamelike' )->text() ) . '</td><td>';
		$out .= Xml::element( 'input', array( 'type' => 'text', 'id' => 'username', 'name' => 'username',
							'value' => @$this->username ) ) . ' ';

		$out .= Xml::element( 'label', array( 'for' => 'year' ), wfMessage( 'regafter' )->text() ) . ' ';
		$years = $this->getYears();
		$out .= $this->yearMenu($years, 'yearfrom');
		$out .= $this->monthMenu('monthfrom').' ';
		$out .= Xml::element( 'label', array( 'for' => 'year' ), wfMessage( 'regbefore' )->text() ) . ' ';
		$out .= $this->yearMenu($years, 'yearto');
		$out .= $this->monthMenu('monthto');


		# Submit button and form bottom
		$out .= Xml::element( 'input', array( 'type' => 'submit', 'value' => wfMessage( 'allpagessubmit' )->text() ) );
		$out .= Xml::closeElement( 'form' );
		$out .= '</p>';
		$out .= "<hr>";
		return $out;
	}

	function getYears(){
		$dbr = wfGetDB( DB_SLAVE );
		$years = array();
		$result = $dbr->selectRow(
			$this->user_table,
			'user_registration',
			'user_registration IS NOT NULL',
			__METHOD__,
			array('ORDER BY' => 'user_registration')
			);
		$y = 2000;
		$thisyear = date("Y");
		if (is_object($result))	$y = substr(wfTimeStamp(TS_MW, $result->user_registration),0,4);
		for ($year = $y; $year <= $thisyear; $year++) $years[] = $year;
		return $years;
	}

	# Year drop-down list
	function yearMenu($years, $item = 'yearfrom'){
		$out = Xml::openElement( 'select', array( 'name' => $item ) );
		$out .= Xml::element( 'option', array( 'value' => '' ), wfMessage( 'group-all' )->text() ); # Item for "all years"
		foreach( $years as $year ) {
			$attribs = array( 'value' => $year );
			if( isset ($this->$item) && $year == $this->$item )
				$attribs['selected'] = 'selected';
			$out .= Xml::element( 'option', $attribs, $year );
		}
		$out .= Xml::closeElement( 'select' ) . ' ';# . Xml::element( 'br' );
		return $out;
	}

	function monthMenu($item){
		global $wgContLang;
		$out = Xml::openElement( 'select', array( 'name' => $item ) );
		$out .= Xml::element( 'option', array( 'value' => '' ), wfMessage( 'group-all' )->text() ); # Item for "all months"
		for( $i = 1; $i <= 12; $i++ ) {
			$month = str_pad($i,2,'0',STR_PAD_LEFT);
			$monthName = $wgContLang->getMonthAbbreviation( $i );
			$attribs = array( 'value' => $month );
			if( isset ($this->$item) && $month == $this->$item )
				$attribs['selected'] = 'selected';
			$out .= Xml::element( 'option', $attribs, $monthName );
		}
		$out .= Xml::closeElement( 'select' ) . ' ';# . Xml::element( 'br' );
		return $out;
	}

	function navLinks(){
		global $wgContLang;
		$atend = $this->num < $this->limit;
		$params = array();
		if( isset($this->yearfrom) ) 	$params['yearfrom']	= $this->yearfrom;
		if( isset($this->monthfrom) ) 	$params['monthfrom']	= $this->monthfrom;
		if( isset($this->yearto) ) 		$params['yearto'] 	= $this->yearto;
		if( isset($this->monthto) ) 	$params['monthto'] 	= $this->monthto;
		if( isset($this->username) )	$params['username'] 	= $this->username;
		if( isset($this->group) )		$params['group'] 	= $this->group;

		return $wgContLang->viewPrevNext(
				$this->getContext()->getTitle(),
				$this->offset,
				$this->limit ,
			#	$wgContLang->specialPage( $this->getName() ),
			#	wfArrayToCGI( $params ),
				$params,
				$atend  );
	}

	function findMyUsers(){
		global $wgUser, $wgDBprefix;
		$dbr = wfGetDB( DB_SLAVE );
		$vars = array('user_id', 'user_name', 'user_registration');
		if($wgUser->isAllowed('userrights')){
			$table = array($this->user_table);
			$conds = array();
		}else{
			$table = array($this->user_table,'logging');
			$conds = array('log_title = user_name',
				"log_type = 'newusers'",
				"log_user = '".$wgUser->getID()."'");
		}
		if (isset($this->group) && $this->group !=''){
			switch($this->group){
				case 'group-none':
					$conds = array_merge($conds, array(" user_id NOT IN (SELECT ug_user FROM ".$this->user_groups_table.")"));
					break;
				default:
					$table[] = $this->user_groups_table;
					$conds = array_merge($conds, array(" ug_user = user_id", "ug_group = '".$this->group."'"));
			}
		}
		if (isset($this->username) && !is_null($this->username) && $this->username != ''){
			$conds = array_merge($conds, array("user_name LIKE'".mysql_real_escape_string($this->username)."' "));
		}
		if (isset($this->yearfrom) && !is_null($this->yearfrom) && $this->yearfrom != ''){
			$month = '00';
			if (!is_null($this->monthfrom )) $month = $this->monthfrom;
			$fromdate =  $dbr->timestamp(str_pad($this->yearfrom.$month, 14, '0', STR_PAD_RIGHT));
			$conds = array_merge($conds, array("user_registration >='$fromdate' "));
		}
		if (isset($this->yearto) && !is_null($this->yearto) && $this->yearto != ''){
			$year = $this->yearto;
			$month = '99';
			if (!is_null($this->monthto ) ) $month = $this->monthto;
			$todate = $dbr->timestamp(str_pad($year.$month, 14, '9', STR_PAD_RIGHT));
			$conds = array_merge($conds, array("user_registration <= '$todate'"));
		}
		$options["ORDER BY"] = "user_name";
		$options["LIMIT"] = $this->limit;
		$options["OFFSET"] = $this->offset;
		$results = $dbr->select($table, $vars, $conds, __METHOD__, $options);
		$this->num = $dbr->numRows($results);

		if (!$results) return array();
		while( $x = $dbr->fetchObject ( $results ) ) {
			$arr[] = get_object_vars($x);
		}
		#echo "<pre>";print_r($conds);print_r($dbr->lastQuery());echo "</pre>";
		return $arr;
	}
}