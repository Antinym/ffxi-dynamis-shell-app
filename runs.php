<?php
//
require_once('class_db.php');
require_once('class_members.php');

$mem = new members();
$mem->is_trial(); // update any trial members that have attended the last 3 runs
$lastrun = $mem->get_last_run();
$mem->run_id = $lastrun;
$run_info = $mem->get_run_info($lastrun);
$active_members = $mem->get_active_members();
$inactive_members = $mem->get_inactive_members();
$trial_members = $mem->get_trial_members();
$droplist = $mem->get_run_drops($lastrun);

$newrun = $_POST;

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TL points</title>
		<style type="text/css" title="currentStyle">
			@import "css/demo_page.css";
			@import "css/demo_table.css";
		</style>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$('#example').dataTable( {
					"bPaginate": false,
					"aaSorting": [[ 1, "desc" ]]
				} );
			} );
		</script>
</head>
<body id="dt_example">

<div id="container">
<div id="demo">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
	<thead>
		<tr>
			<th>Member</th>
			<th>points</th>
		</tr>
	</thead>
	<tbody>
<?php 
foreach($active_members as $memberinfo){
	print "<tr class=\"gradeA\">";
	print "<td>".$memberinfo[0]."</td>";
	print "<td class=\"center\">".$memberinfo[1]."</td>";
	print "</tr>";
}

foreach($inactive_members as $memberinfo){
	print "<tr class=\"gradeX\">";
	print "<td>".$memberinfo[0]."</td>";
	print "<td class=\"center\">".$memberinfo[1]."</td>";
	print "</tr>";
}

foreach($trial_members as $memberinfo){
	print "<tr class=\"gradeC\">";
	print "<td>".$memberinfo[0]."</td>";
	print "<td class=\"center\">".$memberinfo[1]."</td>";
	print "</tr>";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Member</th>
			<th>points</th>
		</tr>
	</tfoot>
</table>
</div>	
<div class="spacer"></div>
			<h1>Initialisation code</h1>
<pre>			$(document).ready(function() {
				$('#example').dataTable( {
				  "bPaginate": false,
					"aaSorting": [[ 1, "desc" ]]
				} );
			} );</pre>
</div>
</body>
</html>