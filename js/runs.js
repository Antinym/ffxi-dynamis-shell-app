
// When the document loads do everything inside here ...
$(document).ready(function(){
  $.get('ajax/ajax_runs.php', {j: 1}, function(data){
    $('#runSelect').html(data);
  }); //select form for loading the run data
});

function eleId(id){
	var id = document.getElementById(id);
	return id;
}

function getMemberList(){
	$("#runLoadingImg").attr("src", "images/loading.gif");
	var run_id = document.getElementById("run").value;
	if (run_id != "default"){
 	$.get('ajax/ajax_runs.php', {j: 2, runid: run_id}, function(data){
 		$('#MemberList').html(data);
 		$("#runLoadingImg").attr("src", "");
 		$("input").change(function () {
			sendChanges($(this).attr("id"), this, run_id);
 		});
 		$("select:not(#run)").change(function () {
			sendChanges($(this).attr("id"), this, run_id);
 		});
 	});
 	getRankingsTable(run_id);
	} else {
		$('#MemberList').html('');
	 	$("#runLoadingImg").attr("src", "");
	}
}; // retrieving a table of members for this run

function getRankingsTable(run_id)
{
	$.get('ajax/ajax_runs.php', {j: 5, runid: run_id}, function(data){
 		$('#lotRankings').html(data);
 		//$("dt:first-child:not[text-decoration='line-through']").css("font-weight","bold");
 		$("dt").click(function () {
 			if ( $(this).css("text-decoration") == "line-through" )
 			{
 				$(this).css({"text-decoration":"","font-weight":"","font-size":""})
 				console.log(this);
 			}
 			else {
 				$(this).css({"text-decoration":"line-through","font-weight":"bold","font-size":"10px"})
 				console.log(this);
 			}
 		});
 	});
}; 

// takes the checkbox id and returns a value to send to the update script
function checkbox(input_id)
{
	//var cb_id = "#" + input_id;
	var cb_id = "#" + input_id; //"input#" + ":checkbox:checked"      
	var cb_value = '0';
	  //if the checkbox is checked
	//if ( $(cb_id).attr('checked').val() == '' ){
	if ( $(cb_id).val() == '' ){
		//$(cb_id).attr('checked','checked');
		$(cb_id).val('checked');
		cb_value = '1';
		//alert('checked ' + $(cb_id).is(':checked'));
	} 
	// if the checkbox is unchecked 
	else if ( $(cb_id).val() == 'checked' ){ //$(cb_id).attr('checked')
		//$(cb_id).attr('checked','');
		$(cb_id).val('');
		cb_value = '0';
		//alert('return checked ' + $(cb_id).is(':checked') );
	}
	var input_id = '';
	return cb_value;
}

function dropWon(item_id, member_id){
	
};

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
			item_value = '50'; 
		}
	}
	$.get('ajax/ajax_runs.php', {j: 3, runid: run_id, itemid: item_id, itemvalue: item_value}, function(data){
		$("div#ErrorCodes").html(data);
	});
	getRankingsTable(run_id);
};