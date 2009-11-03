
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

// when a run is selected this is called to get the table and member data
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
 		$action = 0;
 		$drop_info = $(this).attr("id");
 		$("li").click(function () {
			$(this).attr("disabled","disabled"); //disable button until after the callback executes
 			$drop_info = $(this).attr("id");
 			$(this).toggleClass('wonthisrun');
 			if ( $(this).hasClass('wonthisrun') )
 			{
 				$action = 1;
 			}
/* 			$.get('ajax/ajax_runs.php', {j: 6, runid: run_id, action: $action, dropinfo: $drop_info}, function(data){
 				$(item).removeAttr('disabled'); // re-enables the button
				//getRankingsTable(run_id); // not sure if this is needed
 			}
*/ 			console.log('action = ' + $action);
 			console.log('drop info: ' + $drop_info);
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

// updates drops table when a member's name is clicked on
function dropWon(item_id, member_id){
	
};

// uses jquery to send an xmlhttprequest to update the db on individual cell changes: points/lot items/tiers 
function sendChanges(item_id, item, run_id){
	var item_value = '';
	var $item_id_array = item_id.split("_");
	$(item).attr("disabled","disabled"); //disable button until after the callback executes
	if ( $(item).is( ":checkbox" ) )
	{
		item_value = checkbox(item_id);
		//console.log("item value: " + item_value + "< >" + item_id);
	} 
	else if ( $(item).is("select") )
	{
		item_value = $('select#' + item_id).val();
		
		if ( item_value == "default" )
		{
			item_value = ( $item_id_array[0] == "comment" )? '0' : '50'; 
			console.log(item_value);
		}
	}
	$.get('ajax/ajax_runs.php', {j: 3, runid: run_id, itemid: item_id, itemvalue: item_value}, function(data){
		$("div#ErrorCodes").html(data);
		$(item).removeAttr('disabled'); // re-enables the button
		getRankingsTable(run_id);
	});
};