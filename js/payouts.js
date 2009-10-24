
// When the document loads do everything inside here ...
$(document).ready(function(){
  $.get('ajax/ajax_runs.php', {j: 4, month: 0}, function(data){
    $('#monthSelect').html(data);
  }); //select form for loading the run data
});

function eleId(id){
	var id = document.getElementById(id);
	return id;
}

 // retrieving a table of members for this run
 function getPayoutList(){
 	$("#runLoadingImg").attr("src", "images/loading.gif");
 	var pay_month = document.getElementById("pay_month").value;
 	if (pay_month != "default"){
	 	$.get('ajax/ajax_runs.php', {j: 4, month: pay_month}, function(data){
	 		$('#PayoutList').html(data);
	 		$("#runLoadingImg").attr("src", "");
	 	});
	} else {
		$('#PayoutList').html('');
	 	$("#runLoadingImg").attr("src", "");
	}}; 
 
 function sendChanges(item_id, item, run_id){
 	var item_value = '';
 	if ( $(item).is( ":checkbox" ) )
 	{
 		item_value = checkbox(item_id);
 		//alert("item value: " + item_value + "< >" + item_id);
 	} 
 	else if ( $(item).is("select") )
 	{
 		item_value = $('select#' + item_id).val();
 		
 		if ( item_value == "default" )
 		{
 			item_value = '0'; 
 		}
 	}
 	$.get('ajax/ajax_runs.php', {j: 3, runid: run_id, itemid: item_id, itemvalue: item_value}, function(data){
 		$("div#lotRankings").html(data);
 	});
 };
 
 function hideEmptyRows()
 {
 	 $("tr.gradeU").toggle();
 }