<?php

include_once("../class_runs.php");

$job = $_GET['j'];  // job is the control variable for this program
$month = $_GET['month']; // used in case 4 to determine what data is loaded into the table
$run_id = ( $_GET['runid'] )? $_GET['runid'] : '' ; // the run_id used in determining what data is loaded
$item_id = ( $_GET['itemid'] )? $_GET['itemid'] : '' ; // used in case 3 a compound of the member_id and the item_type
$item_value = ( $_GET['itemvalue'] )? $_GET['itemvalue'] : '' ; // used in case 3 as the item's value(db id)
$setRuns = new runs(); // initialize the class
$setRuns->get_zone($run_id);
$setRuns->run_id = $run_id;

$content = '';


switch ($job)
{
	
	/**
	 * case 1: loads the run select drop-down list
	 * case 2: loads the table for the run selected
	 * case 3: updates the database for changed values
	 * case 4: loads a payouts table
	 * case 5: generates the rank table
	 */
	case 1: //--> case 1: loads the run select drop-down list
		$setRuns->get_runs();
		//var_dump($setRuns->runs_list);die();
		$content = "		<div>
        		<label for=\"run\">Select Run:</label>
        		<select id=\"run\" onchange=\"getMemberList();\">
							<option value=\"default\">select run</option>";
		foreach ( $setRuns->runs_list as $run )
		{
       $content .= "<option value=".$run['id'].">".$run['id'].") ".$run['run_date']." -- ".$run['zone']."</option>";
		}
		$content .= "        		</select>
        <img id=\"runLoadingImg\" src=\"\" />
        </div>
        ";
		echo $content;
		break;
		
	case 2: //--> case 2: loads the table for the run selected
		$setRuns->get_members($run_id);
		
		$content .= 
"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"memberTable\">
	<thead>
		<tr>
			<th>Member</th>
			<th>points</th>
			<th title=\"did the member arrive on time?\">early</th>
			<th title=\"did the member stay til the megaboss was defeated?\">mb</th>
			<th>comment</th>
			<th>freelot 1</th>
			<th>freelot 2</th>
			<th>excused</th>
		</tr>
	</thead>
	<tbody>";

foreach($setRuns->members_list as $key=>$memberinfo){
	$memRunData = $setRuns->get_member_run_info($key,$run_id);
	$grade = $setRuns->get_grade($memberinfo[2]);
	
	$content .= "<tr class=\"grade".$grade."\" id=\"memid_".$key."\" >";
	$content .= "<td>".$memberinfo[0]."</td>";
	$content .= "<td class=\"center\">".$memberinfo[1]."</td>";
	if ( $memRunData )
	{
		$early    = ( $memRunData['early_point'] ) ? "value=\"checked\" checked=\"checked\"" : "value=\"\"";
		$megaboss = ( $memRunData['mb_point'] ) ? "value=\"checked\" checked=\"checked\"" : "value=\"\"";
		$excused  = ( $memRunData['excused'] ) ? "value=\"checked\" checked=\"checked\"" : "value=\"\"";
		$comment = ( $memRunData['comment'] ) ? $memRunData['comment'] : false;
		$fl_one = ( $memRunData['fl_one'] ) ? $memRunData['fl_one'] : false;
		$fl_one_tier = ( $memRunData['fl_one'] ) ? $memRunData['fl_one_tier'] : false;
		$fl_two = ( $memRunData['fl_two'] ) ? $memRunData['fl_two'] : false;
		$fl_two_tier = ( $memRunData['fl_two'] ) ? $memRunData['fl_two_tier'] : false;
		
		$content .= "<td class=\"center\"><input type=\"checkbox\" ".$early." id=\"early_".$key."\"></input></td>";
		$content .= "<td class=\"center\"><input type=\"checkbox\" ".$megaboss." id=\"megaboss_".$key."\"></input></td>";
		$content .= "<td class=\"center\">".$setRuns->item_selector($setRuns->zone_id,"comment_".$key,$comment)."</td>";
		$content .= "<td class=\"nowrap\">".$setRuns->item_selector($setRuns->zone_id,"flone_".$key,$fl_one).$setRuns->tier_selector("flonetier_".$key,$fl_one_tier)."</td>";
		$content .= "<td class=\"nowrap\">".$setRuns->item_selector($setRuns->zone_id,"fltwo_".$key,$fl_two).$setRuns->tier_selector("fltwotier_".$key,$fl_two_tier)."</td>";
		$content .= "<td class=\"center\"><input type=\"checkbox\" ".$excused." id=\"excused_".$key."\"></input></td>";
	} else {
		$content .= "<td class=\"center\"><input type=\"checkbox\" value=\"\" id=\"early_".$key."\"></input></td>";
		$content .= "<td class=\"center\"><input type=\"checkbox\" value=\"\" id=\"megaboss_".$key."\"></input></td>";
		$content .= "<td class=\"center\">".$setRuns->item_selector($setRuns->zone_id,"comment_".$key)."</td>";
		$content .= "<td class=\"nowrap\">".$setRuns->item_selector($setRuns->zone_id,"flone_".$key).$setRuns->tier_selector("flonetier_".$key)."</td>";
		$content .= "<td class=\"nowrap\">".$setRuns->item_selector($setRuns->zone_id,"fltwo_".$key).$setRuns->tier_selector("fltwotier_".$key)."</td>";
		$content .= "<td class=\"center\"><input type=\"checkbox\" value=\"\" id=\"excused_".$key."\"></input></td>";
	}
	$content .= "</tr>";
}
		$content .= 
			"</tbody>
				<tfoot>
					<tr>
						<th>Member</th>
						<th>points</th>
						<th title=\"did the member arrive on time?\">early</th>
						<th title=\"did the member stay til the megaboss was defeated?\">mb</th>
						<th>comment</th>
						<th>freelot 1</th>
						<th>freelot 2</th>
						<th>excused</th>
					</tr>
				</tfoot>
			</table>
			<script>
				$('#memberTable').dataTable( {
						\"bPaginate\": false,
						\"bFilter\": false,
						\"aaSorting\": [[ 0, \"asc\" ]]
					} );
			</script>";

			
		print $content;
		break;
		
	case 3: //--> case 3: updates the database for changed values
		$itemArray = explode("_",$item_id);
		$member_id = $itemArray[1];
		$item_type = $itemArray[0];
		
		//clean up the item_type to match the field names
		switch ( $item_type )
		{
			case "early":
				$field = "early_point";
				break;
			
			case "megaboss":
				$field = "mb_point";
				break;
				
			case "flone":
				$field = "fl_one";
				break;
				
			case "flonetier":
				$field = "fl_one_tier";
				$item_value = ( !$item_value )? '50' : $item_value;
				break;
				
			case "fltwo":
				$field = "fl_two";
				break;
				
			case "fltwotier":
				$field = "fl_two_tier";
				$item_value = ( !$item_value )? '50' : $item_value;
				break;
				
			default:
				$field = $item_type;
				break;
		}
		
		// Testing if the row exists already
		$query = "SELECT count(id) FROM comments WHERE run_id=".$run_id." AND member_id=".$member_id;
		$results = $setRuns->query($query);
		$row = $setRuns->fetchrow($results);
		
		// if the row exists already then update the field with the item_value
		if ( $row[0] )
		{
			$query = "UPDATE comments set ".$field."='".$item_value."' WHERE run_id='".$run_id."' AND member_id='".$member_id."'";
		}
		else
		{
			$insert = "INSERT INTO comments (member_id, run_id, ".$field.") VALUES(";
			$values = "'".$member_id."', '".$run_id."', '".$item_value."')";
			$query  = $insert.$values;
		}
		
		// ALLOWUPDATES has to be set to yes to write to the db
		if( $setRuns->ALLOWUPDATES == 'yes' ) 
		{
			$setRuns->query($query); 
		}
		break;
		
	case 4: //--> case 4: loads a payouts table
		$setRuns->get_payout_months();
		$setRuns->get_payout_members();
		$setRuns->set_payout_monthly_totals();

		// no month selected so only display the month selector 
		if ( $month == 0 )
		{
			$content = "		<div>
	        		<label for=\"pay_month\">Select Month:</label>
	        		<select id=\"pay_month\" onchange=\"getPayoutList();\">
								<option value=\"default\">select month</option>";
			foreach ( $setRuns->payout_months_list as $pmonth=>$value )
			{
	       $content .= "<option value=".$pmonth.">".$pmonth."</option>";
			}
			$content .= "        		</select>
	        <img id=\"runLoadingImg\" src=\"\" />
	        </div>
	        ";
		} 
		// a month has been specified, build the table and display it
		else
		{
			$run_columns = ''; // html output
			$runs_string = ''; // run_in,run_id,run_id,etc
			$runs_array = array(); // [] = run_id

			foreach ( $setRuns->payout_months_list[$month] as $run_id )
			{
				$run_columns .= "<th title='".$run_id."'>".$setRuns->runs_list[$run_id]['run_date']." ".$setRuns->runs_list[$run_id]['zone']."</th>";
				$runs_string .= $run_id.",";
				$runs_array[] = $run_id;
			}
			$runs_string = substr($runs_string, 0, -1);
			$run_columns_count = sizeof($runs_array); // how many run columns are going to be displayed
			
			$setRuns->set_total_month_points($runs_string);
			$content .= "<div id='Totals'><div id='Attendance_Points'>Attendance Points = ".$setRuns->total_month_points."</div><br />\n";
			$content .= "<div id='Payout_Gil'>Payout Gil = ".$setRuns->payout_monthly_totals[$month]['amount']."</div></div><br />\n\n";
			
			$content .= 
				"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"payoutTable\">
					<thead>
						<tr>
						  <th>Member Id</th>
							<th>Member</th>\n"
							.$run_columns.
							"<th>Totalpoints</th>
							<th title=\"base payout for this player\">payout</th>
							<th>ab points</th>
							<th>ab payout</th>
						</tr>
					</thead>
					<tbody>";
			
			// cycle through the members list, generate each row for the table
			foreach ( $setRuns->members_list as $memid=>$value )
			{
				$payout_cells = $setRuns->get_payout_points($memid, $runs_string);
				$total_payout_points = 0;
				foreach ( $payout_cells as $run_points )
				{
					$total_payout_points += $run_points;
				}
				$payout_gil = $setRuns->get_payout_amount($total_payout_points,$month);
				
				// check if this person has points and set the status variable
				$status = ( $total_payout_points > 0 )? "haspoints" : "nopoints";
				// get the correct class to use based on this member's points
				$grade = $setRuns->get_grade($status);
				
				$content .= "<tr class=\"grade".$grade."\" id=\"memid_".$memid."\" >";
				$content .= "<td id='member_id'>".$memid."</td>";
				$content .= "<td id='name'>".$value['name']."</td>";
				foreach ( $runs_array as $value )
				{
					$cell_payout_amt = ( $payout_cells[$value] )? $payout_cells[$value] : "0";
					$content .= "<td class='center'>".$cell_payout_amt."</td>";
				}
				$content .= "<td class='center' id='tppoints' value='".$total_payout_points."'>".$total_payout_points."</td>";
				$content .= "<td class='center' id='tpayout' value='".$payout_gil."'>".$payout_gil."</td>";
				$content .= "<td class='center' id='abpoints'>#</td>";
				$content .= "<td class='center' id='abpayout'>$</td>";
				$content .= "</tr>";
				
				$total_month_points += $total_payout_points; // how many points for this entire month?
				$total_payout += $payout_gil; // how much gil for the entire month?
			}
			$content .= 
				"</tbody>
					<tfoot>
						<tr>
						  <th>Member Id</th>
							<th>Member</th>\n"
							.$run_columns.
							"<th>Totalpoints</th>
							<th title=\"base payout for this player\">payout</th>
							<th>ab points</th>
							<th>ab payout</th>
						</tr>
					</tfoot>
				</table>
				<script>
					$('#payoutTable').dataTable( {
							\"bPaginate\": false,
							\"bFilter\": false,
							\"aoColumns\": [ 
								/* member_id */  { \"bVisible\":    true },
								/* name */       null,";
			$content .= $setRuns->get_dataTable_run_columns($run_columns_count);				
			$content .= "			/* tppoints */	null,
								/* tpayouts */	 null,
								/* abpoints */   { \"bVisible\":    false },
								/* abpayout */   { \"bVisible\":    false }
								],
							\"aaSorting\": [[ 1, \"asc\" ]]
						} );
				</script>";
			$content .= "<div id='Totals'><div id='Attendance_Points'>Attendance Points = ".$total_month_points."</div><br />\n";
			$content .= "<div id='Payout_Gil'>Payout Gil = ".$total_payout."</div></div><br />\n\n";
		}
			
		print $content;		
		break;
		
	case 5: //--> case 5: generates the rank table
		 // generates the rank lists --> might use a function that is called in each cell
		$setRuns->get_items($setRuns->zone_id); // generate the item_list
		$setRuns->get_members($setRuns->run_id); // generates a list of members and their total points
//	var_dump($setRuns->members_list);
		$run_columns = ''; // html output --> column labels for the table
		$run_cells = ''; // html output --> container cells for the rank lists

		// loop to generate the column list for the rankings table
		foreach ( $setRuns->item_list as $items )
		{
			// putting coins at the end of the table for astetics
			if ( $items['job'] != 'coin' )
			{
				$rank_columns .= "<th title=\"".$items['item']."\">".$items['job']."</th>";
				$rank_cells   .= "<td id='".$items['id']."'>".$setRuns->get_ranks($items['id'])."</td>";
			}
		}
		// need to deal with single coin or mulitple coin here... thinking of making it a drop down list but not sure yet... 
		// for now just making it as 224
		$rank_columns .= "<th title='coin'>coin</th>";
		$rank_cells   .= "<td id='224'>".$setRuns->get_ranks('224')."</td>";
		
		$content .= 
				"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"rankings\" id=\"rankingsTable\" >
					<thead>
						<tr>"
							.$rank_columns.
							"
						</tr>
					</thead>
					<tbody>";
		$content .= "<tr class=\"gradeU\" id=\"rankingRow\" style=\"vertical-align:top;\">";
		$content .= $rank_cells;
		$content .=
				"</tbody>
					<tfoot>
						<tr>"
							.$rank_columns.
							"
						</tr>
					</tfoot>
				</table>";
		print $content;
		
		break;
		
	default:
		print "not an option";
		break;
}

?>