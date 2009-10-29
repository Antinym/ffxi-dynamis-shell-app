
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
 
 function hideEmptyRows()
 {
 	 $("tr.gradeU").toggle();
 }