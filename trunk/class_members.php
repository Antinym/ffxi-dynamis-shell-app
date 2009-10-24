<?php
class members extends connection
{
	public $members_list = array();
	public $run_id = '';
	public $run_list = array();
  
	
	/**
	 * sets the public runs_list array of all the runs
	 */
	function get_runs()
	{
		$query = "SELECT * FROM runs ORDER BY id DESC";
		$results = $this->query($query);
		while ( $row = $this->fetchassoc($results) )
		{
			$run = $row['id'];
			$this->runs_list[$run] = $row;				
		}
	}
	
	// returns the id of the last
	function get_last_run($limit=1)
	{
		$query = "SELECT id FROM runs ORDER BY id DESC LIMIT $limit";
		$result = $this->query($query);
		if($result) {
			if ($limit == 1) {
				$row = $this->fetchrow($result);
				$run_number = $row[0];
			} else {
				while($row = $this->fetchrow($result)){
					$run_number[] = $row[0];
				}
			}
		}
		return $run_number;
	} # END get_last_run function
	
	// returns an array of information about a run based on the run_id
	function get_run_info($run_id)
	{
		$query = "SELECT * FROM runs WHERE id = $run_id";
		$result = $this->query($query);
		if($result) {
			$run_info = $this->fetchassoc($result);
			$zone_id = $run_info['zone_id'];
			$query = "SELECT * FROM dynamis_zones WHERE id = $zone_id";
			$result = $this->query($query);
			$row = $this->fetchassoc($result);
			$run_info['zone_name_long'] = $row['zone_name_long'];
		}
		return $run_info;
	} # END get_run_info function

	// returns an array of active members
	function get_active_members()
	{
		$member_list = '';
		$query = "SELECT * FROM members WHERE restricted = 0 AND trial = 0 AND shared = 0 ORDER BY name";
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
					$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
					$member_list[$row['id']]  = array($row['name'],$points_total);
			}
		}
		$query = "SELECT * FROM members WHERE restricted = 0 AND trial = 0 AND shared = 1 ORDER BY name";
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
					$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
					$member_list[$row['main_char']][1]  += $points_total;
			}
		}
		return $member_list;
	} # END get_active_members function
	
	// returns an array of inactive members
	function get_inactive_members()
	{
		$member_list = '';
		$query = "SELECT * FROM members WHERE restricted = 1 ORDER BY name";
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
				if(!$row['main_char'])
				{
					$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
					$member_list[$row['id']]  = array($row['name'],$points_total);
				}
			}
		}
		return $member_list;
	} # END get_inactive_members function
	

	// returns an array of active members
	function get_trial_members()
	{
		$member_list = '';
		$query = "SELECT * FROM members WHERE trial = 1 ORDER BY name";
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
				if(!$row['main_char'])
				{
					$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
					$member_list[$row['id']]  = array($row['name'],$points_total);
				}
			}
		}
		return $member_list;
	} # END get_trial_members function
	
	
	// two functions: 1) gets total points spent if $list=false, 2) return an array of the member's drops
	function get_member_drops($member_id,$list=false)
	{
		$select = (!$list) ? "SELECT SUM(value) ": "SELECT run_id, item_id, item, job, `piece`, `level`, value, pointed ";
		$from   = "FROM drops, items WHERE drops.item_id = items.id AND drops.pointed = 1 ";
		$and    = "AND drops.member_id =".$member_id." AND run_id <=".$this->run_id;
		$query = $select.$from.$and;
		$result = $this->query($query);
		// this gets the points total if no list is requested
		if( $result && !$list ) {
			$row = $this->fetchrow($result);
			$points_spent = ($row[0])? $row[0]:0;
		} else {
			// make the return variable an array and fill it with all the list data
			$points_spent = array();
			while ( $row = $this->fetchassoc($result) )
			{
				$points_spent[] = $row;
			}
		}
		return $points_spent;
	} # END get_member_drops
	
	function get_attendence_points($member_id,$list=false)
	{
		// return all fields if a list is requested otherwise return a sum of the values requested
		$select = ($list) ? "SELECT * " : "SELECT SUM(early_point + mb_point) ";
		$query = $select."FROM comments WHERE member_id = ".$member_id." AND run_id <=".$this->run_id;
		$result = $this->query($query);
		if($result) {
			$row = $this->fetchrow($result);
			$points_earned = ($row[0])? $row[0]:0;
		}
		return $points_earned;
	} # END get_attendence_points
	
	function get_run_drops($run_id)
	{
		$droplist = array('pointed'=>array(),'freelot'=>array(),'coins'=>array());
		$select = "SELECT job, `piece`, name, item FROM drops, items, members WHERE drops.item_id = items.id AND member_id = members.id AND ";
		$query = $select."drops.pointed = 1 AND job != 'coin' AND drops.run_id =".$run_id;
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
				$droplist['pointed'][] = array($row['job'],$row['piece'],$row['name']);
			}
		}
		$query = $select."drops.pointed = 0 AND drops.run_id =".$run_id;
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
				$droplist['freelot'][] = array($row['job'],$row['piece'],$row['name']);
			}
		}
		
		$query = $select."job = 'coin' AND drops.run_id =".$run_id;
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchassoc($result)){
				$droplist['coins'][] = array($row['item'],$row['name']);
			}
		}
		return $droplist;
		
	} # END get_run_drops
	
	// this function checks if a member has attended that last 2 runs
	function check_trial($mem_id)
	{
		$lastrun = $this->get_last_run();
		$trial = true; 
		$member = array();
		
		//make an array of the member and any shared accounts
		$accounts = $this->get_shared_accounts($mem_id);
		
		// cycle through the accounts and make an array with all the member info
		for ( $i = 0; $i < sizeof($accounts); ++$i )
		{
			// get all comments for this account
			$select = "SELECT run_id, (early_point + mb_point) as points, excused  FROM comments WHERE member_id = $accounts[$i] ORDER BY run_id DESC";
			$result = $this->query($select);
			if($result) 
			{
				while ( $row = $this->fetchassoc($result) )
				{ 
					$thisrun = $row['run_id'];
					$thisaccount = $accounts[$i];

					$member[$thisaccount][$thisrun] = array('excused'=>$row['excused'], 'points'=>$row['points']);
				}
			}
		}
		
		//loop through the run numbers
		for ( $k = $lastrun, $j=0, $m=0; $k > 1; $k-- )
		{ 
			// loop through the accounts
			foreach ( $member as $key => $thismember )
			{ 
				$m++; // account counter for each rundate cycle
				// check if this account has been on this run
				if ( $thismember[$k] )
				{
					// is the run marked as excused
					if ( $thismember[$k]['excused'] == true )
					{
						break; //check the next date
					} else {
						// did this account attend long enough to get credit
						if ( $thismember[$k]['points'] > 0 )
						{
							$j++;
							  // has the member completed the three consecutive runs
								if ( $j == 3 )
								{
									$trial = false;
									break 2; // end function, member is no longer on trial
								}
							break; //check the next run date
						} # END points check
					} # END excused check
				} 
				// test if this account failed to attend this run
				elseif ( ($m) == sizeof($member) )
				{
					$trial = true;
					break 2; // end function, member is still on trial;
				} # END account to run check
				
			} # END account loop	
		} # END run number loop
		return $trial;
	} # END check_trial function
	
	// this function cycles through all the trial members and updates any that have attended that last 3 runs
	function is_trial()
	{
		$query = "SELECT id FROM members WHERE trial = 1 AND shared = 0";
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchrow($result)){
				if (!$this->check_trial($row[0])) 
				{
					$query = "UPDATE members SET trial = 0 WHERE id = $row[0] OR main_char = $row[0]";
					$this->query($query);
				} 
			}
		}
	} # END is_trial function
	
	// This returns an array of member_ids that share points with the mem_id passed
	function get_shared_accounts($mem_id)
	{
		$accounts = array($mem_id);
		$query = "SELECT id FROM members WHERE main_char = $mem_id AND shared = 1";
		$result = $this->query($query);
		if($result) {
			while ( $row = $this->fetchrow($result) ) {
				$accounts[] = $row[0];
			}
		}
		return $accounts;
	}
	
	// this function checks if a member has attended that last 2 runs
	function check_restricted($mem_id)
	{
		$lastrun = $this->get_last_run();
		$status = false; 
		
		//make an array of the member and any shared accounts
		$accounts = $this->get_shared_accounts($mem_id);
		
		// cycle through the accounts
		for ( $i = 0; $i < sizeof($accounts); ++$i )
		{
			// get all comments for this account
			$select = "SELECT run_id, (early_point + mb_point) as points, excused  FROM comments WHERE member_id = $accounts[$i] ORDER BY run_id DESC";
			$result = $this->query($select);
			if($result) 
			{
				while ( $row = $this->fetchassoc($result) )
				{ 
					//loop through the run numbers
					for ($k = $lastrun, $j=0; $k > 1; $k--)
					{ 
						// is this run number a match for the one in the current row
						if ( $k == $row['run_id'] )
						{ 
							// is the excused absence field not set
							if ( !$row['excused'] )
							{
								// did the member get any points for this run 
								if ( $row['points'] > 0 )
								{
									$restricted = false;
									break 3; // this member isn't restricted, goto end of function
								} else {
									$j++;
									$restricted = true;
									// if this account has missed the last two runs then check the next account
									if ( $j == $runs ) 
									{ 
										break 2; // this acccunt is restricted, goto end of function
									} 
								} # END of resticted points check
							} # End of excused absence check
						} else 
						{
							$j++;
							$restricted = true;
							// if this account has missed the last two runs then check the next account
							if ( $j == 2 ) 
							{ 
								break 2;
							} 
						}# END of run number matching check
					} # End of run numbers loop
				} # END of while $row loop
			} # End of results check
		} # END of cycle through accounts loop
		return $restricted;
	} # END check_trial function
	
	// this function cycles through all the non-trial members and updates restricted based on the last 2 runs
	function is_restricted()
	{
		$query = "SELECT id, restricted FROM members WHERE trial = 0 AND shared = 0";
		$result = $this->query($query);
		if($result) {
			while($row = $this->fetchrow($result)){
				if ($this->check_restricted($row[0]))
				{
					$query = "UPDATE members SET restricted = 1 WHERE id = $row[0] OR main_char = $row[0]";
				} else 
				{
					$query = "UPDATE members SET restricted = 0 WHERE id = $row[0] OR main_char = $row[0]";
				} 
				$this->query($query);
			}
		}
	} # END is_trial function
}
?>