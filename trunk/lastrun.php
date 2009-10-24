<?php
//
require_once('class_db.php');
require_once('class_members.php');

$thisdomain = "http://".$_SERVER['SERVER_NAME'];
$thispage = $_SERVER['SCRIPT_NAME'];
$do_restricted = ( $_GET['r'] )? false : true ;
$mem = new members();
$run_id = ( $_GET['r'] )? $_GET['r'] : $mem->get_last_run() ;
$mem->run_id = $run_id;
if ( $do_restricted == true ){
	$mem->is_trial(); // update any trial members that have attended the last 3 runs
	$mem->is_restricted(); // update any members that haven't attended the last 2 runs
} 
$run_info = $mem->get_run_info($run_id);
$droplist = $mem->get_run_drops($run_id);
$active_members = $mem->get_active_members();
$inactive_members = $mem->get_inactive_members();
$trial_members = $mem->get_trial_members();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TL lastrun</title>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.js"></script>
<script type="text/javascript">
// function reloads the page with the selected run number in the url string
function onChange()
{
	var run_selected_id = document.getElementById("run").value;
	window.location= "<?=$thisdomain;?><?=$thispage;?>?r=" + run_selected_id;
}
</script>
</head>
<body>
<? 
		$mem->get_runs();
		$content = "		<div>
        		<label for=\"run\">Select Run:</label>
        		<select id=\"run\" onchange=\"onChange();\">";
		foreach ( $mem->runs_list as $run )
		{
			$selected = ( $run['id'] == $run_id )? "selected=\"selected\"": "";
       $content .= "<option ".$selected." value=".$run['id'].">".$run['run_date']." -- ".$run['zone']."</option>";
		}
		$content .= "        		</select>
        </div>
        ";
		echo $content;
?>
<br /><br />
<?=$run_info['zone_name_long']?> <?=$run_info['run_date']?>
<br /><? //var_dump($_SERVER); ?>
<br /><br />
[b]AF2 Drops[/b]<br />
<?php 

foreach($droplist['pointed'] as $drop){
	echo $drop[0]." ".$drop[1]." - ".$drop[2]."<br />";
}
foreach($droplist['freelot'] as $drop){
	echo $drop[0]." ".$drop[1]."(FL) - ".$drop[2]."<br />";
}
echo "<br />";
foreach($droplist['coins'] as $drop){
	echo $drop[0]." - ".$drop[1]."<br />";
}
?>
<br />
[b]Active Members[/b]<br />
<?php 
foreach($active_members as $memberinfo){
	echo $memberinfo[0]." - ".$memberinfo[1]."<br />";
}
?>
<br />
[b]Resticted Members[/b]<br />
<?php 
foreach($inactive_members as $memberinfo){
	echo $memberinfo[0]." - ".$memberinfo[1]."<br />";
}
?>
<br />
[b]Trial Members[/b]<br />
<?php 
foreach($trial_members as $memberinfo){
	echo $memberinfo[0]." - ".$memberinfo[1]."<br />";
}
?>

</body>
</html>