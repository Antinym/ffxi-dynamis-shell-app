<?php
require_once('class_db.php');

class runs extends connection
{
		public $members_list = array();
		public $runs_list = array();
		public $item_list = array();
		public $job_list = array();
		public $payout_months_list = array();
		public $payout_monthly_totals = array();
		public $total_month_points = '';
		public $run_id = '';
		public $zone_id = '';
		public $ALLOWUPDATES = 'yes';
		
		
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
		} # END get_runs function
		
		/**
		 * sets the class variable payout_months_list which holds
		 * an array of the run_ids and a sting of the dates
		 *
		 */
		function get_payout_months()
		{
			
			$this->get_runs();
			$dates = array();
			$runs_list = $this->runs_list;
			ksort($runs_list);
			foreach ( $runs_list as $key=>$run )
			{
				$temp = explode("/", $run['run_date']);
				$comp_date = $temp[0]."/".$temp[2];
				if ( $dates[$comp_date] )
				{
					$dates[$comp_date][] = $key;
				} else 
				{
					$dates[$comp_date] = array();
					$dates[$comp_date][] = $key;
				}
			}
			$this->payout_months_list = $dates;
		} # END get_payout_months function
		
		/**
		 * Sets the class variables run_id and zone_id
		 * 
		 * @param int $run_id $_GET variable passed when a zone is selected to load
		 */
		function get_zone($run_id)
		{
			$this->run_id = $run_id;
			$query = "SELECT zone_id FROM runs WHERE id=\"".$run_id."\" LIMIT 1";
			$results = $this->query($query);
			$row = $this->fetchrow($results);
			$this->zone_id = $row[0];
		} # END get_zone function

		/**
		 * Two functions: 
		 *    1) gets total points spent if $list=false 
		 *    2) return an array of the member's drops if $list=true
		 *
		 * @param int $member_id
		 * @param bool $list
		 * @return int or array
		 */
		function get_member_drops($member_id,$list=false)
		{
			$andrun = ( $this->run_id )? " AND run_id <= ".$this->run_id : "";
			$select = (!$list) ? "SELECT SUM(value) ": "SELECT run_id, item_id, item, job, `piece`, `level`, value, pointed ";
			$from   = "FROM drops, items WHERE drops.item_id = items.id AND drops.pointed = 1 ";
			$andmem    = "AND drops.member_id = ".$member_id;
			$query  = $select.$from.$andmem.$andrun;
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
		} # END get_member_drops function
		
		/**
		 * Queries the comments table returns a total number of points earned by this member based on 
		 * the class variable run_id.
		 *
		 * @param int $member_id
		 * @param bool $list not used yet: planning to use this for returning an array of all this member's points
		 * @return int sum of this members early and megaboss points
		 */
		function get_attendence_points($member_id,$list=false)
		{
			$select = ($list) ? "SELECT * " : "SELECT SUM(early_point + mb_point) ";
			$query = $select."FROM comments WHERE member_id = ".$member_id." AND run_id <= ".$this->run_id;
			$result = $this->query($query);
			if($result) {
				$row = $this->fetchrow($result);
				$points_earned = ($row[0])? $row[0]:0;
			}
			return $points_earned;
		} # END get_attendence_points function
		
		/**
		 * Generates an array of the members and sorts them based on lotting status.
		 * The array is saved to the class variable members_list.
		 *
		 * @param int $runid not used: was to be a limit for the query
		 */
		function get_members($runid='')
		{
			// gets active members
			$query = "SELECT * FROM members WHERE restricted = 0 AND trial = 0 AND shared = 0 ORDER BY name";
			$result = $this->query($query);
			if($result) {
				while($row = $this->fetchassoc($result)){
						$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
						$status = "active";
						$this->members_list[$row['id']]  = array($row['name'],$points_total,$status);
				}
			}
			$query = "SELECT * FROM members WHERE restricted = 0 AND trial = 0 AND shared = 1 ORDER BY name";
			$result = $this->query($query);
			if($result) {
				while($row = $this->fetchassoc($result)){
						$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
						$this->members_list[$row['main_char']][1]  += $points_total;
				}
			}  # END active members loop 
			
			// Gets restricted members
			$query = "SELECT * FROM members WHERE restricted = 1 ORDER BY name";
			$result = $this->query($query);
			if($result) {
				while($row = $this->fetchassoc($result)){
					if(!$row['main_char'])
					{
						$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
						$status = "restricted";
						$this->members_list[$row['id']]  = array($row['name'],$points_total,$status);
					}
				}
			} # END resticted members loop
			
			// gets trial members
			$query = "SELECT * FROM members WHERE trial = 1 ORDER BY name";
			$result = $this->query($query);
			if($result) {
				while($row = $this->fetchassoc($result)){
					if(!$row['main_char'])
					{
						$points_total = $this->get_attendence_points($row['id'])-$this->get_member_drops($row['id']);
						$status = "trial";
						$this->members_list[$row['id']]  = array($row['name'],$points_total,$status);
					}
				}
			} # END trial members loop
		
		} # END get_members function
		
		/**
		 * returns an array of this member's info from a particular run 
		 * if there's nothing in the db then a false returned
		 *
		 * @param int $mem_id
		 * @param int $run_id
		 * @return array OR false depending on results of the sql query
		 */
		function get_member_run_info($mem_id, $run_id)
		{
			$query = "SELECT * FROM comments WHERE member_id = \"".$mem_id."\" AND run_id = \"".$run_id."\" LIMIT 1";
			$result = $this->query($query);
			if ( $result )
			{
				$member_info = $this->fetchassoc($result);
				return $member_info;
			} else {
				return false;
			}
		} # END get_member_run_info function
		
		/**
		 * gets the possible item drops for a zone and sets them in the class array item_list
		 *
		 * @param int $zone_id
		 */
		function get_items($zone_id)
		{
			$query = "SELECT * FROM items WHERE zones LIKE \"%".$zone_id."%\" ORDER BY job";
			$results = $this->query($query);
			while ( $row = $this->fetchassoc($results) )
			{
				$item = $row['id'];
				$this->item_list[$item] = $row;
			}
		} # END get_items function
		
		/**
		 * creates a dropdown list of the jobs. 
		 * it will return a truncated list based on only what drops in that zone
		 * 
		 * @param int $zoneid => which zone this select is being generated for
		 * @param var $type_id => the specific selector being generated (column_memberid)
		 * @param var $item_id => if there is a value in the db this will be the selected, otherwise default is selected
		 */
		function item_selector($zone_id=false, $type_id, $item_id=false)
		{
			$selected = ( $item_id ) ? "" : "" ;
			$onlyonecoin = 0;
			$this->get_items($zone_id);
			$content  = "<select id=\"".$type_id."\">";
			$content .= "<option value=\"default\">NC</option>";
			foreach ($this->item_list as $key=>$item){
				if ( $item_id == $key )
				{
					if ( $zone_id > 3 && $key > 220 )
					{
						$content .= "<option selected=\"selected\" value=\"224\">".$item['job']."</option>";
					} else {
						$content .= "<option selected=\"selected\" value=\"".$key."\">".$item['job']."</option>";	
					}
				} 
				else 
				{
					if ( ( !preg_match("/comment/",$type_id) && $key > 220 ) || ( $key > 220 && $onlyonecoin == 1 ) )
					{
						// do nothing
					}
					elseif ( $zone_id > 3 && $key > 220 && $onlyonecoin != 1 ) 
					{
						$content .= "<option value=\"224\">".$item['job']."</option>";
						$onlyonecoin = 1;
					}
					else
					{
						$content .= "<option value=\"".$key."\">".$item['job']."</option>";		
					}
			  	
				}
			}
			$content .= "</select>";
			return $content;
		} # END item_selector function
		
		/**
		 * creates an html select for level selecting
		 *
		 * @param var $type_id the DOM id to be set for this selector
		 * @param bool $selected determines if a specific value is set as selected
		 * @return var returns the html stored in a variable
		 */
		function tier_selector($type_id,$selected=false)
		{
			$tiers = array("75","70","65","60","55","50");
			$content .= "<select id=\"".$type_id."\">";
			$content .= "<option value=\"default\">lvl</option>";
			foreach ($tiers as $tier)
			{
				if ( $tier == $selected )
				{
					$content .= "<option selected=\"selected\" value=\"".$tier."\">".$tier."</option>";
				} else {
					$content .= "<option value=\"".$tier."\">".$tier."</option>";
				}
			}
			$content .= "</select>";
			return $content;
		} # END tier_selector function
		
		/**
		 * Used to select a specific css class based on a member's lotting or points status
		 *
		 * @param var $status this is the lotting status of a member
		 * @return var a single letter designation used to specify a css class
		 */
		function get_grade($status)
		{
			switch ($status)
			{
				case ( $status == 'active' || $status == 'haspoints' ):
					$grade = "A";
					break;
				
				case ( $status == 'restricted' || $status == 'paid' ):
					$grade = "X";
					break;
					
				case 'trial':
					$grade = "C";
					break;
					
				default:
					$grade = "U";
					break;
			} 
			return $grade;
		} # END get_grade function
		
		/**
		 * simple loop to generate some html needed for dataTables aoColumns
		 *
		 * @param int $run_columns_count the total number of run columns 
		 * @return var the html to be displayed
		 */
		function get_dataTable_run_columns($run_columns_count)
		{
			$content = '';
			for ($i=0; $i < $run_columns_count; $i++)
			{
				$content .= "/* run_column_".$i." */       null,\n";
			}
			return $content;
		}
		
		function get_ranks($item_id)
		{
			$content = '';
			$dropswon = array();
			$comment = array();
			$freelot = array();
			
			// get member_ids who've already won this item
			$d_query = "SELECT member_id, run_id FROM drops WHERE item_id='".$item_id."' and run_id <= ".$this->run_id;
			$d_results = $this->query($d_query);
			while ( $d_row = $this->fetchassoc($d_results) )
			{
				$dropswon[$d_row['member_id']] = (int)$d_row['run_id'];
			}
//print $d_query;
//var_dump($dropswon);
			// get the lotters
			$select = "SELECT member_id, name, comment, fl_one, fl_one_tier, fl_two, fl_two_tier";
			$from = " FROM comments, members m WHERE run_id='".$this->run_id."' AND member_id = m.id";
			$and = " AND (comment='".$item_id."' OR fl_one='".$item_id."' OR fl_two='".$item_id."' )";
			$order = " ORDER BY name";
			$query = $select.$from.$and.$order;
			$results = $this->query($query);
			$content .= "<ul id=\"ul_".$item_id."\">\n";
			
			// at this point we have an unsorted list of members lotting this specific item
			// the next step is to sort the members into lotting tiers, group them by tiers,
			// and create the html dts for each
			if ( $results )
			{
				while ( $row = $this->fetchassoc($results) )
				{
					// if this member is spending points on this item put them in the comments array
					if ( $row['comment'] == $item_id )
					{
						$points = $this->members_list[$row['member_id']][1];
						$comment[] = array( 'member_id'=>$row['member_id'], 'name'=>$row['name'], 'points'=>$points);
					}
					// if this members is freelotting put them in the freelot array.. check both fl_one and fl_two
					else if ( $row['fl_one'] == $item_id )
					{
						$freelot[] = array( 'member_id'=>$row['member_id'], 'name'=>$row['name'], 'tier'=>(int)$row['fl_one_tier'] );
					}
					else if ( $row['fl_two'] == $item_id )
					{
						$freelot[] = array( 'member_id'=>$row['member_id'], 'name'=>$row['name'], 'tier'=>(int)$row['fl_two_tier'] );
					}
				}
				if ( $freelot )
				{
					foreach ( $freelot as $key=>$val )
					{
						$member_id[$key]= $val['member_id'];
						$name[$key] = $val['name'];
						$tier[$key] = $val['tier'];
					}
					array_multisort($tier, SORT_DESC, $freelot);
				}	
				
				// zones above 4 are ranked based on point totals
				if ( $this->zone_id > 4 )
				{
					
				}
				// zones 4 and below are ranked by comment, then by level tier
				else
				{
					foreach ( $comment as $comm )
					{
						$class_title = '';
						if ( $dropswon[$comm['member_id']] )
						{
							$class_title = ( $dropswon[$comm['member_id']] == (int)$this->run_id )?
															 " class=\"pointing wonthisrun\"":
															 " class=\"pointing wonpastrun\" title=\"Won on run ".$dropswon[$comm['member_id']]."\"";
						} else {
							$class_title = " class=\"pointing\"";
						}
						$content .= "<li id=\"li_".$item_id."_".$comm['member_id']."\"".$class_title.">".$comm['name']."</dt>";
					}
					foreach ( $freelot as $comm )
					{
						$class_title = " class=\"tier".$comm['tier']."\"";
						if ( $dropswon[$comm['member_id']] )
						{
							$class_title = ( $dropswon[$comm['member_id']] == $this->run_id )? 
															" class=\"wonthisrun tier".$comm['tier']."\"":
															" class=\"wonpastrun tier".$comm['tier']."\" title=\"Won on run ".$dropswon[$comm['member_id']]."\"";							
						}
						$content .= "<li id=\"li_".$item_id."_".$comm['member_id']."\"".$class_title.">".$comm['name']."</dt>";
					}
				}
			}
			$content .= "</ul>";
			
			return $content;
//var_dump($this->item_list);die();
		} # END get_ranks function
		
		/**
		 * returns an array of this memeber's afterboss points for the
		 * run_ids in the month array.
		 *
		 * @param array $month
		 * @param int $mem_id
		 * @return array 
		 */
		function get_ab_points($month, $mem_id)
		{
			$mem_points = array();
			$ab_data = array();
			$ab_points = array();
			$ab_string = '';
			$query = "SELECT run_id, ab_points FROM comments WHERE member_id = ".$mem_id;
			$results = $this->query($query);
			if ($results)
			{
				while ( $row = $this->fetchassoc($results) )
				{
					$run_id = $row['run_id'];
					$mem_points[$run_id] = $row['ab_points'];
				}
				foreach ( $month as $key=>$value )
				{
					$ab_points[] = $mem_points[$key]['ab_points'];
					$ab_string .= $mem_points[$key]['ab_points'].",";
				}
				$ab_string = substr($ab_string, 0, -1);
				$ab_data = array($ab_string, $ab_points);
			}
			return $ab_data;			
		} # END get_ab_points function
		
	/**
	 * this generates an array of all the shell members and stores it in the class
	 * variable members_list.
	 *
	 */
	function get_payout_members()
	{
		$members_list = array();
		$select = "SELECT id, name, main_char, shared FROM members ";
		$where_a  = "WHERE shared = 0 ORDER BY name";
		$where_b  = "WHERE shared = 1 ORDER BY name";
		$query_a  = $select.$where_a;
		$query_b  = $select.$where_b;
		$not_shared = $this->query($query_a);
		if ( $not_shared )
		{
			while ( $row = $this->fetchassoc($not_shared) )
			{
				$members_list[] = $row;
			}
		}
		$are_shared = $this->query($query_b);
		if ( $are_shared )
		{
			while ( $row = $this->fetchassoc($are_shared) )
			{
				$members_list[] = $row;
			}
		}
		foreach ( $members_list as $value )
		{
			$this->members_list[$value['id']] = $value;
		}
	} # END get_payout_members function
	
	/**
	 * sets the class variable total_monthly_points by getting a SUM from the comments table
	 * using the string of run_ids supplied
	 *
	 * @param string $run_ids a comma seperated string of run_ids
	 */
	function set_total_month_points($run_ids)
	{
		$query = "SELECT SUM(early_point+mb_point) as points FROM comments WHERE run_id IN($run_ids)";
		$results = $this->query($query);
		if ( $results )
		{
			$row = $this->fetchrow($results);
			$this->total_month_points = $row[0];
		} else 
		{
			$this->total_month_points = 0;
		}
	}
	
	/**
	 * gets the montly payout totals from the database ## NOTE: this is gimped atm using a temp table
	 *
	 */
	function set_payout_monthly_totals()
	{
		$query = "SELECT * FROM pay_temp";
		$result = $this->query($query);
		if ( $result )
		{
			while ( $row = $this->fetchassoc($result) )
			{
				$this->payout_monthly_totals[$row['month']] = array( 'amount'=>$row['amount'], 'override'=>$row['override_points'] ); 
			}
		}
	} # END set_payout_monthly_totals function
	
	/**
	 * Generates an array of the sum of the early and megaboss points this member has earned
	 * for each run requested.
	 *
	 * @param int $mem_id
	 * @param string $run_ids list of run_ids for the month requested
	 * @return array [run_id, points_for_that_run]
	 */
	function get_payout_points($mem_id, $run_ids)
	{
		$payout_points = array();
		$query = "SELECT run_id, (early_point+mb_point) as points FROM comments WHERE run_id IN($run_ids) AND member_id = ".$mem_id;
		$results = $this->query($query);
		if ( $results )
		{
			while ( $row = $this->fetchrow($results)) 
			{
				$payout_points[$row[0]] = $row[1];
			}
		} else 
		{
			$payout_points = false;
		}
		return $payout_points;
	} 
	
	/**
	 * simple math function to determine the total member payout earned for this month
	 *
	 * @param int $points number of points 
	 * @param string $month
	 * @return int
	 */
	function get_payout_amount($points, $month)
	{
		// if there's an override amount of points use it instead of the calculated points
		$total_month_points = ( $this->payout_monthly_totals[$month]['override'] )? $this->payout_monthly_totals[$month]['override'] : $this->total_month_points;
		
		// total gil for the month divided by the total points, then rounded down
		$gil_per_point = floor( $this->payout_monthly_totals[$month]['amount'] / $total_month_points );
		
		// the gil value of each point multiplied by the total attendance points the member had for this month
		$member_payout = $gil_per_point * $points;		
		return $member_payout;
	}
		
} # END runs class

?>