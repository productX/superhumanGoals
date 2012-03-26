<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<?php
include("template/userFacingForceLogin.php");
$ajaxModifyGoal = PAGE_AJAX_MODIFY_GOAL;
$ajaxModifyKPI = PAGE_AJAX_MODIFY_KPI;
$ajaxCreateKPI = PAGE_AJAX_CREATE_KPI;
$ajaxModifyTestStatus = PAGE_AJAX_MODIFY_TEST_STATUS;
$ajaxModifyStrategy = PAGE_AJAX_MODIFY_STRATEGY;
$ajaxCreateStrategy = PAGE_AJAX_CREATE_STRATEGY;
$ajaxSetTracking = PAGE_AJAX_SET_TRACKING;
$ajaxAlterGoalDescription = PAGE_AJAX_ALTER_GOAL_DESCRIPTION;

// DO PROCESSING
if(!isset($_GET["id"])) {
	redirect(PAGE_GOALS);
}
$goalID = GPC::strToInt($_GET["id"]);
$userHasGoal = GoalStatus::doesUserHaveGoal($user->id, $goalID);

// RENDER PAGE
require_once("include/chrome.php");

$goal = Goal::getFullObjFromGoalID($goalID,$user->id);
$goal_name = $goal->goal->name;
$goal_description = $goal->goal->description;


// Show your description if in DB
if ($goal->sub_description == 'none'){ 
}else{ 
?><script>$(document).ready(function(){addDescription('<?php echo $goal->sub_description; ?>');});</script><?php
}

// Show your display style if in DB
if ($goal->display_style == '1'){ 
 	$self_checked = "unchecked";
 	$adherence_checked = "checked";
}else{ 
 	$self_checked = "checked";
	$adherence_checked = "unchecked" ;
}


const PAGEMODE_EDIT='edit';
const PAGEMODE_ACTIVITY='activity';
const PAGEMODE_PEOPLE='people';
const PAGEMODE_FACTS='facts';

$mode = PAGEMODE_EDIT;

# Get all the KPIs and the strategies for the goal being viewed
$kpis = KPI::getListFromGoalID($goalID, $user->id);
$strategies = Dailytest::getListFromGoalID($goalID, $user->id);


if(isset($_GET["t"])) {
	$mode = $_GET["t"];
}
$tabIndex = 0;
switch($mode) {
	case PAGEMODE_EDIT:
		$tabIndex = 0;
		break;
	case PAGEMODE_ACTIVITY:
		$tabIndex = 1;
		break;
	case PAGEMODE_PEOPLE:
		$tabIndex = 2;
		break;
	case PAGEMODE_FACTS:
		$tabIndex = 3;
		break;
	default:
		assert(false);
		break;
}
printHeader(NAVNAME_GOALS, array(
					new ChromeTitleElementHeader("Goal: $goal_name"),
					new ChromeTitleElementTabs(	array(	"Edit"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_EDIT,
														"Activity"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_ACTIVITY,
														"People"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_PEOPLE,
														"Facts"=>PAGE_GOAL."?id=$goalID&t=".PAGEMODE_FACTS
												), $tabIndex)
			));
			
switch($mode) {

	case PAGEMODE_EDIT:
		$numAdopters = $goal->goal->getNumAdopters();
		$average = GoalStatus::getAverageGoalScore($goalID);
		if(is_null($average)) {
			$average=0;
		}
?>

<script>


//////////////////////////////////////////////////////////////////////////
// AJAX for modifying (adding/removing/readopting, not creating) a KPI //
////////////////////////////////////////////////////////////////////////

function modifyKPI(kpi_id, type){
    $.ajax({  
        type: "POST", 
        url: '<?php echo $ajaxModifyKPI; ?>', 
        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&kpiID="+ kpi_id+"&type="+ type,
        dataType: "html",
        complete: function(data){
            $("#ratingBox").html(data.responseText);  
        }  
    });  
}

//////////////////////////////
// AJAX for creating a KPI //
////////////////////////////

function createKPI(userID, goalID, kpiName, kpiDescription, kpiTestDescription, kpiTestName, kpiTestFrequency, adopt, newKPINum){
    $.ajax({  
        type: "POST", 
        url: '<?php echo $ajaxCreateKPI; ?>', 
        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&kpiName="+ kpiName+"&kpiDescription="+ kpiDescription+"&kpiTestDescription="+ kpiTestDescription+"&kpiTestName="+ kpiTestName+"&kpiTestFrequency="+ kpiTestFrequency+"&adopt="+ adopt,
        dataType: "html",
        complete: function(data){
        
			var val = jQuery.parseJSON(data.responseText);       	
        	
        	// Get the new KPI id and set the value into the checkbox
        	$("#adopted_kpi_checkbox_" + newKPINum).attr("value",val[0]);
        	
        	// Get the new Test id and set the value into the checkbox
        	$("#adopted_test_checkbox_id_" + newKPINum + "_0").attr("value",val[1]);

			// Modify the onclick event for the checkbox to include the kpiID, testID, as well as the newKPINum (included on top) and testNUM (0)
        	$("#adopted_test_checkbox_id_" + newKPINum + "_0").attr("onclick","modifyTestStatus("+ val[0] + ","+val[1]+"," +newKPINum+ ",0);");
        	
            $("#ratingBox").html(val[0]);  

        }  
    });  
}

/////////////////////////////////////////////////////////////////////////////////////////////////
// AJAX for modifying a KPI Test (including creating it in certain cases if it doesn't exist) //
///////////////////////////////////////////////////////////////////////////////////////////////

function modifyTestStatus(kpiID, testID, kpiNum, testNum){

	if ($("#adopted_test_checkbox_id_" + kpiNum + "_" + testNum).attr("checked") == 'checked' ){	
		var newActiveStatus = '1';
	}else{
		var newActiveStatus = '0';	
	}

    $.ajax({  
        type: "POST", 
        url: '<?php echo $ajaxModifyTestStatus; ?>', 
        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&kpiID="+ kpiID+"&testID="+ testID+"&newActiveStatus="+ newActiveStatus,
        dataType: "html",
        complete: function(data){
        	//$("#adopted_kpi_checkbox_" + newKPINum).attr("value",data.responseText);
            $("#ratingBox").html(data.responseText);  
        }  
    });	

}


////////////////////////////////////
// AJAX for modifying a Strategy //
//////////////////////////////////

function modifyStrategy(strategy_id, type){
    $.ajax({  
        type: "POST", 
        url: '<?php echo $ajaxModifyStrategy; ?>', 
        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&strategyID="+ strategy_id+"&type="+ type,
        dataType: "html",
        complete: function(data){
            $("#ratingBox").html(data.responseText);  
        }  
    });  
}


//////////////////////////////////
// AJAX for creating a Strategy //
/////////////////////////////////

function createStrategy(strategyName, strategyDescription, strategyType, newStrategyNum){
    $.ajax({  
        type: "POST", 
        url: '<?php echo $ajaxCreateStrategy; ?>', 
        data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&strategyName="+ strategyName+"&strategyDescription="+ strategyDescription+"&strategyType="+ strategyType,
        dataType: "html",
        complete: function(data){

			var val = data.responseText;       	
        	
        	// Get the new Strategy id and set the value into the checkbox
        	$("#adopted_strategy_checkbox_" + newStrategyNum).attr("value",val);
        	
			// Modify the onclick event for the checkbox to include the kpiID, testID, as well as the newKPINum (included on top) and testNUM (0)
        	//$("#adopted_test_checkbox_id_" + newKPINum + "_0").attr("onclick","modifyTestStatus("+ val[0] + ","+val[1]+"," +newKPINum+ ",0);");
        	
            $("#ratingBox").html(val[0]);  

        }  
    });  
}



/////////////////////////////////////////
// AJAX for adopting/removing a Goal //
///////////////////////////////////////

	function modifyGoal(type){
        $.ajax({  
            type: "POST", 
            url: '<?php echo $ajaxModifyGoal; ?>', 
            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&type="+type,
            dataType: "html",
            complete: function(data){
                $("#ratingBox").html(data.responseText);  
            }  
        });  
    }
		

////////////////////////////////////////
// AJAX for inserting Tracking Style //
//////////////////////////////////////

	function setTracking(displayStyle){
		
		if(displayStyle == '0'){
			$("#self_reported").prop('checked', true);
			$("#adherence_based").prop('checked', false);		
		}else{
			$("#self_reported").prop('checked', false);
			$("#adherence_based").prop('checked', true);		
		}
		
        $.ajax({  
            type: "POST", 
            url: '<?php echo $ajaxSetTracking; ?>', 
            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&displayStyle="+ displayStyle,
            dataType: "html",
            complete: function(data){
                $("#ratingBox").html(data.responseText);  
            }  
        });  
    }


////////////////////////////////////////////
// AJAX for inserting a Goal description //
//////////////////////////////////////////

	function alterGoalDescription(description){
        $.ajax({  
            type: "POST", 
            url: '<?php echo $ajaxAlterGoalDescription; ?>', 
            data: "userID="+<?php echo $user->id; ?>+"&goalID="+<?php echo $goalID; ?>+"&description="+ description,
            dataType: "html",
            complete: function(data){
                $("#ratingBox").html(data.responseText);  
            }  
        });  
    }
		

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   //
// %%%%%%%%%%%%%%%%%%%   END OF AJAX  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  //
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% //


////////////////////////////////////////////
//   %%% Adding and removing KPIs %%%%   //
//////////////////////////////////////////


////////////////////////////////////////////////////////////////	
////// Adding a KPI to the Adopted column the first time //////
//////////////////////////////////////////////////////////////

	function change_kpi_tests(kpi_box_id){

		// DB Insert Variables
		var kpi_db_id = $("#kpi_checkbox_" + kpi_box_id).attr("value");
		
		// *** DB ENTRY *** //
		modifyKPI(kpi_db_id,'adopt');
		
		// DOM variables needed to move the data to the adopted list
		var kpi_box = "#kpi_box_" + kpi_box_id;		
		var kpi_checkbox_id = "#kpi_checkbox_" + kpi_box_id;
		var kpi_tests_box = "#kpi_test_box_" + kpi_box_id;
		var test_display_status = $(kpi_tests_box).css("display");
		var adopted_kpi = ".kpi_results #adopted_kpi"; 

		// Displays the tests after the KPI has been chosen
		if(test_display_status == 'none'){
			$(kpi_tests_box).css("display", "");
		} /*else{
			$(kpi_tests_box).css("display", "none");
		}*/

		// Hide the KPI in Suggested and make it appear in Adopted
		$(kpi_box).fadeOut();
		$(adopted_kpi).prepend($(kpi_box).html());


		// Change the new div names under Adopted to have the "adopted" prefix
		var temp_kpi_internals_all = ".kpi_results #kpi_internals_" + kpi_box_id;		
		var adopted_internals = "adopted_internals_" + kpi_box_id;
		var adopted_internals_id = "#adopted_internals_" + kpi_box_id;

		$(temp_kpi_internals_all).attr("name",adopted_internals);
		$(temp_kpi_internals_all).attr("id",adopted_internals);
	
		// Rename checkbox names under Adopted to have the "adopted" prefix
		var temp_kpi_checkbox = ".kpi_results #kpi_checkbox_" + kpi_box_id;
		var adopted_kpi_checkbox = "adopted_kpi_checkbox_" + kpi_box_id;
		
		// Remove the KPI checkbox
		$(temp_kpi_checkbox).attr("id",adopted_kpi_checkbox);
		$(temp_kpi_checkbox).attr("name",adopted_kpi_checkbox);		
		$("#" + adopted_kpi_checkbox).css("display","none");	
			
		// Rename Main Test Div and then Each Test Checkbox 
		var test_count = $("#num_tests_" + kpi_box_id).attr("value");
		var kpi_test_box = ".kpi_results #kpi_test_box_" + kpi_box_id;
		var adopted_test_box = "adopted_test_box_" + kpi_box_id;
		$(kpi_test_box).attr("name",adopted_test_box)
		$(kpi_test_box).attr("id",adopted_test_box)

		for(i=0; i<test_count; i++) { 
			var adopted_test_box_id = "adopted_test_checkbox_id_" + kpi_box_id + "_" + i;
			var kpi_test_checkbox_id = ".kpi_results #kpi_test_checkbox_id_" + kpi_box_id + "_" + i;			
			
			$(kpi_test_checkbox_id).attr("id",adopted_test_box_id);	
			$("#" + adopted_test_box_id).attr("name",adopted_test_box_id);	
		}

		// Add new "remove" button
		var new_remove = "<input id='removeKPIButton' type='button' value='X' onclick='removeKPI(" + kpi_box_id + ");' class='small-add-btn up-down'/>";
		$(adopted_internals_id).append(new_remove);

		$(".kpi_start_prompt").hide();
		
	}
	

/////////////////////////////////////////////////////
////// Removing a KPI from the Adopted column //////
///////////////////////////////////////////////////

	function removeKPI(adopted_box_id){
		var kpi_db_id = $("#adopted_kpi_checkbox_" + adopted_box_id).attr("value");
		var new_test_db_id = $("#adopted_test_checkbox_id_"+adopted_box_id+"_0").attr("value");
	
		var kpi_internals_id = "#kpi_box_" + adopted_box_id;
		var adopted_internals_id = "#adopted_internals_" + adopted_box_id;
		var kpi_internals_input = "#kpi_checkbox_" + adopted_box_id;
		var new_checkbox = "<input type='checkbox' unchecked name='kpi_" + adopted_box_id + "' onclick='reAdoptKPI(" + adopted_box_id + ");' id='kpi_checkbox_" + adopted_box_id + "'  value='"+ kpi_db_id +"' />";
		var newKPINum = adopted_box_id;
		//var newTestID = $("#adopted_internals_" + adopted_box_id + " .kpi_tests").attr("id");
		var kpiName = $("#adopted_internals_" + adopted_box_id + " .newKPIName").html();
		var kpiDescription = $("#adopted_internals_" + adopted_box_id + " .newKPIDescription").html();
		var kpiTestNameFrequency = $("#adopted_internals_" + adopted_box_id + " .newTestNameFrequency").html();
		var kpiTestDescription = $("#adopted_internals_" + adopted_box_id + " .newTestDescription").html();
			
						
		var newKPI = "<div id='kpi_box_" +newKPINum+ "' class='kpi' style='display: block;'><div id='kpi_internals_" +newKPINum+ "' name='kpi_internals_" +newKPINum+ "' class='kpi_internals'><input type='checkbox' id='kpi_checkbox_" +newKPINum+ "' onclick='reAdoptKPI(" +newKPINum+ ");' name='kpi_" +newKPINum+ "' unchecked='' value='"+ kpi_db_id +"'>" + kpiName + "<br><subtitle style='font-size:11px'>"+ kpiDescription + "</subtitle><br><input type='hidden' value='1' name='num_tests_" +newKPINum+ "' id='num_tests_" +newKPINum+ "'><div id='" +newKPINum+ "' name='" +newKPINum+ "' style='' class='kpi_tests'>Test 1 <br><input type='checkbox' value='"+ new_test_db_id + "' name='kpi_test_checkbox_id_"+ newKPINum + "_0' onclick='modifyTestStatus(" +kpi_db_id+ ","+new_test_db_id+"," +newKPINum+ ",0);' id='kpi_test_checkbox_id_"+ newKPINum + "_0'> "+ kpiTestNameFrequency + " <br><subtitle style='font-size:11px'>"+ kpiTestDescription +"</subtitle><br></div></div></div>";
		
		
		// Deactivate the KPI and its Tests
		modifyKPI(kpi_db_id,'remove');
		
				
		// This is only for KPIs that were not newly created
		$(kpi_internals_input).replaceWith(new_checkbox);

		// Checks if the KPI is new or old, if new it inserts the new value into the PRE block
		if (typeof $(kpi_internals_id).attr("class") === 'undefined'){					
			$(".kpi_box").append(newKPI);
			
			// Hide the test checkboxes in the Suggested column -> They should not be used and can be removed down the line
			   if(typeof $("#adopted_test_checkbox_id_" + adopted_box_id + "_0").attr("checked") === 'undefined'){
			   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_0").css("display","none");
				}else{
			   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_0").css("display","none");
				}
					
		}else{
			$(kpi_internals_id).fadeIn();
		
			// Hide the test checkboxes in the Suggested column -> They should not be used and can be removed down the line
			var test_count = $("#num_tests_" + adopted_box_id).attr("value");
				// Create JS array including all of the checked test_ids and update Checked/Unchecked status of Suggested KPI tests
				var test_id_array = [];
				for(i=0; i<test_count; i++) { 
				   test_id_array.push(i);
				   if(typeof $("#adopted_test_checkbox_id_" + adopted_box_id + "_" + i).attr("checked") === 'undefined'){
				   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_" + i).css("display","none");
					}else{
				   		$("#kpi_test_checkbox_id_" + adopted_box_id + "_" + i).css("display","none");
					}
				}					
		}
		
		$(adopted_internals_id).fadeOut();
			
	}

	
////////////////////////////////////////////////////////////////////////////////	
////// Adopting a KPI after it has already been adopted and removed once //////
//////////////////////////////////////////////////////////////////////////////

	function reAdoptKPI(kpi_id){
		var kpi_db_id = $("#kpi_checkbox_" + kpi_id).attr("value");
		var test_count = $("#num_tests_" + kpi_id).attr("value");

	    modifyKPI(kpi_db_id,'readopt');


		var kpi_internals_id = "#kpi_box_" + kpi_id;
		var adopted_internals_id = "#adopted_internals_" + kpi_id;
	
		$(kpi_internals_id).fadeOut();
		$(adopted_internals_id).fadeIn();
	}


///////////////////////////////////////////
////// Adopting a newly created KPI //////
/////////////////////////////////////////

	function addAndAdoptKPI(){

		var kpiName = $("#kpiName").attr("value");
		var kpiDescription = $("#kpiDescription").attr("value");
		var kpiTestName = $("#kpiTestName").attr("value");
		var kpiTestDescription = $("#kpiTestDescription").attr("value");
		var kpiTestFrequency = $("#kpiTestFrequency").attr("value");
		var currentKPINum = $("#numkpis").attr("value");
		var newKPINum = Number(currentKPINum) + 1;
		var currentTestID = $("#numtests").attr("value");
		//var newTestID = Number(currentTestID) +1;
		var adopted_kpi = ".kpi_results #adopted_kpi";	
		
		createKPI(<?php echo $user->id; ?>, <?php echo $goalID;?>, kpiName, kpiDescription, kpiTestDescription, kpiTestName, kpiTestFrequency, 'true', newKPINum);
		
		var newKPI = "<div id='adopted_internals_"+ newKPINum + "' name='adopted_internals_0' class='kpi_internals'><input id='adopted_kpi_checkbox_"+ newKPINum +"' type='checkbox' value='a' name='kpi_"+ newKPINum +"' style='display: none;'><span class='newKPIName'>" + kpiName + "</span><br><subtitle style='font-size:11px'><span class='newKPIDescription'>"+ kpiDescription + " </span></subtitle><br><input type='hidden' value='1' name='num_tests_" +newKPINum+ "' id='num_tests_" +newKPINum+ "'><div id='" +newKPINum+ " ' name='" +newKPINum+ " ' style='' class='kpi_tests'> Test 1 <br><input type='checkbox' checked='true' value='na' name='adopted_test_checkbox_id_"+ newKPINum + "_0'  onclick='modifyTestStatus(" +newKPINum+ ",'0');'  id='adopted_test_checkbox_id_"+ newKPINum + "_0''> <span class='newTestNameFrequency'>"+ kpiTestName +" (every " + kpiTestFrequency + " days)</span> <br><subtitle style='font-size:11px'><span class='newTestDescription'>"+ kpiTestDescription +"</span></subtitle><br></div><input type='button' class='small-add-btn up-down' onclick='removeKPI("+ newKPINum +");' value='X' id='removeKPIButton'></div>";

		$(adopted_kpi).prepend(newKPI);
		$("#numkpis").attr("value", newKPINum);

		$("#if_kpi_input").fadeOut();
		$("#remove_kpi_input").hide();
		$("#show_kpi_input").fadeIn();
		$(".kpi_start_prompt").hide();
		

	}


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   //
// %%%%%%%%%%%%%%%%%%%   END OF KPIs  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  //
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% //


//////////////////////////////////////////////////
//   %%% Adding and removing Strategies %%%%   //
////////////////////////////////////////////////


	  //////////////////////////////////////////////////////////////////////////
	 // Move contents of strategy_internals divs to Adopted/"results" column, /
	//////////////////////////////////////////////////////////////////////////
	
	function adopt_strategy(strategy_num){
		var strategy_id = $("#strategy_checkbox_" + strategy_num).attr("value");

		var strategy_internals = "#strategy_internals_" + strategy_num;		
		var strategy_box = "#strategy_box_" + strategy_num;
		var adopted_strategy = ".strategy_results #adopted_strategy"; 
		var strategy_checkbox_id = "#adopted_strategy_checkbox_" + strategy_num;
		var new_remove_strategy = "<input id='removeStrategyButton' type='button' value='X' onclick='removeStrategy(" + strategy_num + ");' class='small-add-btn up-down'/>";

		modifyStrategy(strategy_id, 'adopt');


		$(strategy_box).fadeOut();
		$(adopted_strategy).prepend($(strategy_box).html());
		
		var strategy_internals_new = ".strategy_results " + strategy_internals;
		var adopted_internal_strategies = "adopted_strategy_internals_" + strategy_num;		
		var adopted_internal_strategies_id = "#" + adopted_internal_strategies;
		var adopted_strategy_checkbox = "adopted_strategy_checkbox_" + strategy_num;
		var temp_strategy_checkbox = ".strategy_results #strategy_checkbox_" + strategy_num;
						
		$(strategy_internals_new).attr("name",adopted_internal_strategies);
		$(strategy_internals_new).attr("id",adopted_internal_strategies);

		$(temp_strategy_checkbox).attr("id", adopted_strategy_checkbox);
		$(adopted_internal_strategies_id).append(new_remove_strategy);
		$(adopted_internal_strategies_id + " "+strategy_checkbox_id ).hide();
		$(".strategy_start_prompt").hide();
			
	}

	  ///////////////////////////////////////////////////
	 // This removes a Strategy from the Adopted column/
	///////////////////////////////////////////////////
	
	function removeStrategy(strategy_num){
		var strategy_id = $("#adopted_strategy_checkbox_" + strategy_num).attr("value");
		var strategyType = $("#newStrategyType_" + strategy_num).attr("value");;


		var adopted_internal_strategies_id = "#adopted_strategy_internals_" + strategy_num;		
		var strategy_box = "#strategy_box_" + strategy_num;
		var strategy_internals_input = "#strategy_checkbox_" + strategy_num;
		var new_checkbox = "<input type='checkbox' name='strategy_" + strategy_num + "' onclick='reAdoptStrategy(" + strategy_num + ");' id='strategy_checkbox_" + strategy_num + "' value='"+ strategy_id +"' />";
		var strategyName = $("#adopted_strategy_internals_" + strategy_num + " .newStrategyName").html();
		var strategyDescription = $("#adopted_strategy_internals_" + strategy_num + " .newStrategyDescription").html();
		
		var newStrategy = "<div id='strategy_box_"+ strategy_num +"' name='strategy_box_"+ strategy_num +"' class='strategy_boxes'><div id='strategy_internals_"+ strategy_num +"' name='strategy_internals_"+ strategy_num +"'><input type='checkbox' id='strategy_checkbox_"+ strategy_num +"' onclick='reAdoptStrategy("+ strategy_num +");' value='"+strategy_id+"' name='strategy_"+ strategy_num +"'> "+ strategyName +"<br><subtitle style='font-size:11px'> "+ strategyDescription +"</subtitle><br><span class='newStrategyType' value='"+strategyType+"' style='display:none;'></span></div></div>";


		modifyStrategy(strategy_id, 'remove');

		// This is only for Strategies that were not newly created
		$(strategy_internals_input).replaceWith(new_checkbox);
				
		// Checks if the KPI is new or old, if new it inserts the new value into the PRE block
		if (typeof $(strategy_box).attr("class") === 'undefined'){
			$(".strategies").append(newStrategy);		
		}else{
			$(strategy_box).fadeIn();
		}		
		
		
		$(adopted_internal_strategies_id).fadeOut();
	}
	
	
     //////////////////////////////////////////////////////////////////////////
	// Adopting a Strategy after it has already been adopted and removed once/
   //////////////////////////////////////////////////////////////////////////
   
	function reAdoptStrategy(strategy_num){
		var strategy_id = $("#strategy_checkbox_" + strategy_num).attr("value");
	
		var strategy_internals_id = "#strategy_box_" + strategy_num;
		var adopted_internals_id = "#adopted_strategy_internals_" + strategy_num;
	
		modifyStrategy(strategy_id, 'readopt');
	
		$(strategy_internals_id + " input").attr("checked",false);
		$(strategy_internals_id).fadeOut();
		$(adopted_internals_id).fadeIn();
	}


	 //////////////////////////////////////////////////////////////
	// Creating and Adopting a new Strategy that you just created/
   //////////////////////////////////////////////////////////////
   
	function addAndAdoptStrategy(){

		var strategyName = $("#strategyName").attr("value");
		var strategyDescription = $("#strategyDescription").attr("value");
		var strategyType = $("#strategyType").attr("value");
		var currentStrategyNum = $("#numstrategies").attr("value");
		var newStrategyNum = Number(currentStrategyNum) + 1;
		var adopted_strategy = ".strategy_results #adopted_strategy";
		
		createStrategy(strategyName, strategyDescription, strategyType, newStrategyNum);
		
		var newStrategy = "<div id='adopted_strategy_internals_"+newStrategyNum+"' name='adopted_strategy_internals_"+newStrategyNum+"'><input id='adopted_strategy_checkbox_"+newStrategyNum+"' type='checkbox' onclick='adopt_strategy("+newStrategyNum + ");' value='na' name='strategy_"+newStrategyNum+"' style='display: none;'><span class='newStrategyName'>"+ strategyName +"</span><br><subtitle style='font-size:11px'><span class='newStrategyDescription'>"+ strategyDescription +"</span></subtitle><br><span class='newStrategyType' id='newStrategyType_"+ newStrategyNum +"'  value='"+strategyType+"' style='display:none;'></span><input type='button' class='small-add-btn up-down' onclick='removeStrategy("+newStrategyNum+");' value='X' id='removeStrategyButton'></div>";


		$(adopted_strategy).prepend(newStrategy);
		$("#numstrategies").attr("value", newStrategyNum);

		$("#if_strategy_input").fadeOut();
		$("#remove_strategy_input").hide();
		$("#show_strategy_input").fadeIn();
		$(".strategy_start_prompt").hide();
		

	}


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%   //
// %%%%%%%%%%%%%%%%%%%   END OF Strategies  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  //
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% //


//////////////////////////////////////////////////////////
//   %%% Adding and removing custom Description %%%%   //
////////////////////////////////////////////////////////

	function addDescription(existing_desc){

		if(existing_desc == "none"){
			var new_desc = $("#goal_desc").attr("value");		
		}else{
			var new_desc = existing_desc;
		}

		$(".enter_desc_prompt").hide();
		$(".adopted_desc p").html(new_desc);
		$("#removeDescButton").fadeIn();
		alterGoalDescription(new_desc);		
	}
	function removeDescription(){
		var new_desc = "";
		$(".enter_desc_prompt").fadeIn();
		$(".adopted_desc p").html("").fadeIn();
		$("#removeDescButton").hide();
		alterGoalDescription(new_desc);		
	}

//////////////////////////////////////////////////////////////////
// Transition to edit mode when somebody elects to Adopt a goal	/
////////////////////////////////////////////////////////////////
	
	function removeShowAdopt(){				
			$("#if_adopt").show();
			$("#suggested_goal_params").show();
			$(".pre_adopt").hide();
			$("#who_else_adopted").hide();
			$(".reporting_select").show();
			modifyGoal('insert');	
	}

	function removeGoal(){
			$(".pre_adopt").show();
			$(".pre_adopt").css("margin-top","305px");
			modifyGoal('remove');	
			$("#if_adopt").css("display","none");
	}


	function onEdit(){
			$("#if_adopt").show();
			$(".pre_adopt").hide();
			$("#suggested_goal_params").show();
			$("#who_else_adopted").hide();
			$(".reporting_select").show();
	}


	$(document).ready(function(){ 

		 /////////////////////////////////////////////////////////////////////
		// Grey text that disappears onclick in the goal description field //
	   /////////////////////////////////////////////////////////////////////
		$("#goal_desc").one("click", function(){
			$("#goal_desc").css("color","black");
			$("#goal_desc").attr("value","");
		});
		
		 ////////////////////////////////////////////////////////
		// Exposing the input fields for creating your own KPI//
	   ////////////////////////////////////////////////////////
		$("#show_kpi_input").click(function(){
			$("#if_kpi_input #kpiName").attr("value", '');
			$("#if_kpi_input #kpiDescription").attr("value", '');
			$("#if_kpi_input #kpiTestName").attr("value", '');
			$("#if_kpi_input #kpiTestDescription").attr("value", '');
			$("#if_kpi_input #kpiTestFrequency").attr("value", '');
			$("#if_kpi_input").show();
			$("#show_kpi_input").hide();
			$("#remove_kpi_input").fadeIn();
		});	

	     /////////////////////////////////////////////////////////////
		// Exposing the input fields for creating your own Strategy//
	   /////////////////////////////////////////////////////////////	
		$("#show_strategy_input").click(function(){
			$("#if_strategy_input #strategyName").attr("value", '');
			$("#if_strategy_input #strategyDescription").attr("value", '');
			$("#if_strategy_input").show();
			$("#show_strategy_input").hide();
			$("#remove_strategy_input").fadeIn();
		});	

		 ////////////////////////////////////////////////////////
		// Removing the input fields for creating your own KPI//
	   ////////////////////////////////////////////////////////
		$("#remove_kpi_input").click(function(){
			$("#if_kpi_input").fadeOut();
			$("#show_kpi_input").fadeIn();
			$("#remove_kpi_input").hide();
		});	

	     /////////////////////////////////////////////////////////////
		// Removing the input fields for creating your own Strategy//
	   /////////////////////////////////////////////////////////////	
		$("#remove_strategy_input").click(function(){
			$("#if_strategy_input").fadeOut();
			$("#show_strategy_input").fadeIn();
			$("#remove_strategy_input").hide();			
		});	


	});  											

</script>



<!-- AJAX TESTING GROUNDS -->		
<div name="ratingBox" id="ratingBox" style="display:none;"></div>

<!-- Case -->
<div class="case">

	<!-- Score -->
	
	
	<div class="score">
		<div class="text">
<?php if(!$userHasGoal){?>
		<div class="pre_adopt">
			<p id="suggested_description"><strong>Suggested Description:</strong> <?php echo $goal_description; ?></p>
			<button class="adopt-goal-btn" id="show_adopt_options" onclick="removeShowAdopt();">Adopt this goal</button>
		</div>
<?php } ?>
			<div id="if_adopt" name="if_adopt" style="display:none;">
			<div class="edit_goal_params" id="suggested_goal_params"> Edit Goal Parameters </div>

				<!-- START REPORTING STYLE -->
				<div class="reporting_select">
					<label class='small-label' style="font-weight:bold"> Your Progress Indicator: </label>
					<input type="radio" id="self_reported" name="self_reported" <?php echo $self_checked; ?> value="0" onclick="setTracking('0')" /> Self Reported
					<input type="radio" id="adherence_based" name="adherence_based" <?php echo $adherence_checked; ?> value="1" onclick="setTracking('1')" /> Adherence Based
				</div>
				
				<!-- START YOUR DESCRIPTION --> 
				<div class="goal_desc_box" id="goal_desc_box" name="goal_desc_box">
					<label class='small-label' style="font-weight:bold;">Your own description (optional):</label><br/>	
					<div class="enter_desc_prompt" id="enter_desc_prompt" name="enter_desc_prompt">
						<input type="text" name="goal_desc" id="goal_desc" value="feel free to enter your own description" style="color:#999; width:280px; margin: 10px 10px 0px 0px; float:left;" />	
						<input type="button" value="Add" onclick="addDescription('none');" class="small-add-btn up-down"/>
					</div>									
	
					<div class="adopted_desc">
						<p></p>
						<input id="removeDescButton" type="button" value="X" onclick="removeDescription();" class="small-add-btn up-down" style="display:none;"/>
					</div>
				</div>
				
				<!-- START KPIS -->
				<div class="kpi_headings">
					<div class="chosen_kpis" id="show_adopted_kpis"> My KPIs </div>
					<div class="choose_kpi_heading"> 
						Choose KPIs:
						<a class="show_kpi_input" id="show_kpi_input" name="show_kpi_input" style="color:white; float:right;">
							Add Your Own
						</a>
						<a class="remove_kpi_input" id="remove_kpi_input" name="remove_kpi_input" style="color:white; float:right; display:none;">
							Close KPI Creator
						</a>

					</div>
				</div>
				<div style="clear:both;"/>


				<div class="all_kpis">
					<div class="kpi_results">
						<div class="adopted_kpi" id="adopted_kpi" name="adopted_kpi">
						
							<!-- ADOPTED KPIs GO HERE -->
							<?php 
							$active_kpis = 0;
							for($i=0; $i<count($kpis);$i++){
								$test_count = count($kpis[$i]->kpi_tests);
								if($kpis[$i]->kpi_active == 1){
									$active_kpis++;
							?>
									<div class="kpi_internals" name="<?php echo "adopted_internals" . "_" . $i ;?>" id="<?php echo "adopted_internals" . "_" . $i ;?>">									
									<input type="checkbox" value="<?php echo $kpis[$i]->id;?>" onclick="change_kpi_tests('<?php echo $i;?>');" id="<?php echo "adopted_kpi_checkbox_" . $i ;?>" style="display:none;" />

											<?php echo $kpis[$i]->kpi_name;?><br/>
											<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_desc;?></subtitle>
											<br/>
									
										<input type="hidden" name="<?php echo "num_tests_" . $i?>" id="<?php echo "num_tests_" . $i?>" value="<?php echo $test_count; ?>" />
										<div class="kpi_tests" name="<?php echo "adopted_test_box_" . $i;?>" id="<?php echo "adopted_test_box_" .  $i;?>">
							<?php
															for($k=0; $k<$test_count;$k++){
																if ($kpis[$i]->kpi_tests[$k]->active == '1'){ 
																 	$test_checked = "checked";
																}else{ 
																 	$test_checked = "unchecked";
																}
														?>
																KPI Test <?php echo $k+1; ?> <br/>
			
																<input type="checkbox" name="<?php echo "adopted_test_checkbox_id_". $i ."_". $k;?>" id="<?php echo "adopted_test_checkbox_id_". $i ."_". $k;?>" value="<?php echo $kpis[$i]->kpi_tests[$k]->id;?>" <?php echo $test_checked;?> onclick="modifyTestStatus(<?php echo $kpis[$i]->id;?>, <?php echo $kpis[$i]->kpi_tests[$k]->id;?>, <?php echo $i;?>, <?php echo $k;?> );"  /> <?php echo $kpis[$i]->kpi_tests[$k]->test_name;?> (every <?php echo $kpis[$i]->kpi_tests[$k]->test_frequency;?> days) <br />
																<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_tests[$k]->test_description;?></subtitle>
																<br/>
												<?php  }?>
										</div>
										<input id='removeKPIButton' type='button' value='X' onclick='removeKPI(<?php echo $i; ?>);' class='small-add-btn up-down'/>
									</div>
						<?php	}
						}  
						if($active_kpis == 0){
							echo "<div class='kpi_start_prompt'>Choose some KPIs to get started ----></div>";
						}

						
						?>

						</div>
					</div>

					<div class="kpi_box">
						<div id="if_kpi_input" style="margin-top:10px; display:none; color:white;"><br/>
							<label class='small-label'>  KPI: </label>
							<input type='text' class='small-field' name='kpiName' id='kpiName' /><br/><br/>
							<label class='small-label'> Description: </label>
							<input type='text' class='small-field' name='kpiDescription' id='kpiDescription' /><br/><br/>
							<label class='small-label'>  Test Name: </label>
							<input type='text' class='small-field' name='kpiTestName' id='kpiTestName' /><br/><br/>
							<label class='small-label'>  Test Description: </label>
							<input type='text' class='small-field' name='kpiTestDescription' id='kpiTestDescription' /><br/><br/>
							<label class='small-label'>  Test Freq. (days): </label>
							<input type='text' class='small-field' name='kpiTestFrequency' id='kpiTestFrequency' />
							<div class='cl'></div>
							<br/>					
							<center><input type="button" value="Add KPI" onclick="addAndAdoptKPI();" class="small-add-btn up-down"/></center>
						</div>
						<?php 
						for($i=0; $i<count($kpis);$i++){
							$test_count = count($kpis[$i]->kpi_tests);
							if($kpis[$i]->kpi_active == 0){
							$show_kpi = "display:'';";
							}else{
							$show_kpi = "display:none;";
							}
						?>
						<div class="kpi" id="<?php echo "kpi_box_" . $i;?>" style="<?php echo $show_kpi; ?>">
							<div class="kpi_internals" name="<?php echo "kpi_internals" . "_" . $i ;?>" id="<?php echo "kpi_internals" . "_" . $i ;?>">
									<input type="checkbox" value="<?php echo $kpis[$i]->id;?>" onclick="change_kpi_tests('<?php echo $i;?>');" id="<?php echo "kpi_checkbox_" . $i ;?>" /> <?php echo $kpis[$i]->kpi_name;?><br/>
									<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_desc;?></subtitle>
									<br/>
							
								<input type="hidden" name="<?php echo "num_tests_" . $i?>" id="<?php echo "num_tests_" . $i?>" value="<?php echo $test_count; ?>" />
								<div class="kpi_tests" style="display:none;" name="<?php echo "kpi_test_box_" . $i;?>" id="<?php echo "kpi_test_box_" .  $i;?>">
					<?php
													for($k=0; $k<$test_count;$k++){
												?>
														KPI Test <?php echo $k+1; ?> <br/>
	
														<input type="checkbox" name="<?php echo "kpi_test_checkbox_id_". $i ."_". $k;?>" id="<?php echo "kpi_test_checkbox_id_". $i ."_". $k;?>" value="<?php echo $kpis[$i]->kpi_tests[$k]->id;?>"  onclick="modifyTestStatus(<?php echo $kpis[$i]->id;?>, <?php echo $kpis[$i]->kpi_tests[$k]->id;?>, <?php echo $i;?>, <?php echo $k;?> );"  /> <?php echo $kpis[$i]->kpi_tests[$k]->test_name;?> (every <?php echo $kpis[$i]->kpi_tests[$k]->test_frequency;?> days) <br />
														<subtitle style="font-size:11px"><?php echo $kpis[$i]->kpi_tests[$k]->test_description;?></subtitle>
														<br/>
										<?php  }?>
								</div>
							</div>
						</div>			
						<input type="hidden" name="numtests" id="numtests" value="<?php echo count($kpis[$i]->kpi_tests); ?> " />
						<input type="hidden" name="numkpis" id="numkpis" value="<?php echo count($kpis); ?> " />
			
					<?php
						}  
						?>
					</div>
				</div>
				<div style="clear:both;"/>


				<!-- START STRATEGIES -->
				<div class="strategy_headings">
					<div class="chosen_strategies" id="show_adopted_strategies"> My Strategies </div>
					<div class="choose_strategy_heading"> 
						Choose Strategies:
						<a class="show_strategy_input" id="show_strategy_input" name="show_strategy_input" style="color:white; float:right;">
							Add Your Own
						</a>
						<a class="remove_strategy_input" id="remove_strategy_input" name="remove_strategy_input" style="color:white; float:right; display:none;">
							Close Strategy Creator
						</a>
					</div>
				</div>
				
				<div style="clear:both;"/>
				<div class="all_strategies">
					<div class="strategy_results">
						<div class="adopted_strategy" id="adopted_strategy" name="adopted_strategy">
						
							<!-- ADOPTED Strategies GO HERE -->
						
							<?php 
							$active_strategies = 0;
							for($j=0; $j<count($strategies);$j++){
								if($strategies[$j]->strategy_active == 1){
									$active_strategies++;
							?>
								<div class="strategy_boxes" name="<?php echo "adopted_strategy_box_" . $j ; ?>" id="<?php echo "adopted_strategy_box_" . $j ; ?>">				
									<div name="<?php echo "adopted_strategy_internals_" . $j ;?>" id="<?php echo "adopted_strategy_internals" . "_" . $j ;?>">
									<!-- Strategy <?php echo $j; ?> <br/> -->
										<input type="checkbox" name="<?php echo "adopted_strategy" . "_" . $j ;?>" value="<?php echo $strategies[$j]->id;?>" onclick="adopt_strategy('<?php echo $j;?>');" id="<?php echo "adopted_strategy_checkbox_" . $j ;?>" style="display:none;"/> <?php echo $strategies[$j]->name;?><br/>
										<subtitle style="font-size:11px"><?php echo $strategies[$j]->description;?></subtitle><span><?php echo $strategies[$j]->strategy_type;?></span>
										<br/>
									<input id='removeStrategyButton' type='button' value='X' onclick='removeStrategy(<?php echo $j; ?>);' class='small-add-btn up-down'/>
									</div>
								</div>
						<?php }
							}
							if($active_strategies == 0){
								echo "<div class='strategy_start_prompt'>Choose some Strategies to get started ----></div>";
							}
						?>
						
						</div>
					</div>
										
					<div class="strategies" >						
						<div id="if_strategy_input" style="margin-top:10px; display:none; color:white;"><br/>
							<label class='small-label'> Strategy: </label>
							<input type='text' class='small-field' name='strategyName' id='strategyName'/>
							<br/><br/>
							<label class='small-label'> Description: </label>
							<input type='text' class='small-field' name='strategyDescription' id='strategyDescription'/>
							<br/><br/>
							<label class='small-label'> Type: </label>
							<select name='strategyType' id='strategyType'>
								<option value='adherence'>Adherence</option>
								<option value='todo'>ToDo</option>
								<option value='tactic'>Tactic</option>
							</select>
							<div class='cl'> </div>					
							<center><input type="button" value="Add Strategy" onclick="addAndAdoptStrategy();" class="small-add-btn up-down" style="margin-bottom:14px;"/></center>
						</div>
					<?php 
					for($j=0; $j<count($strategies);$j++){
						if($strategies[$j]->strategy_active == 0){
							$show_strategy = "display:'';";
							}else{
							$show_strategy = "display:none;";
						}
						
					?>
						<div class="strategy_boxes" name="<?php echo "strategy_box_" . $j ; ?>" id="<?php echo "strategy_box_" . $j;?>" style="<?php echo $show_strategy; ?>" >				
							<div name="<?php echo "strategy_internals_" . $j ;?>" id="<?php echo "strategy_internals" . "_" . $j ;?>">
							<!-- Strategy <?php echo $j; ?> <br/> -->
								<input type="checkbox" name="<?php echo "strategy" . "_" . $j ;?>" value="<?php echo $strategies[$j]->id;?>" onclick="adopt_strategy('<?php echo $j;?>');" id="<?php echo "strategy_checkbox_" . $j ;?>" /> <?php echo $strategies[$j]->name;?><br/>
								<subtitle style="font-size:11px"><?php echo $strategies[$j]->description;?></subtitle><span><?php echo $strategies[$j]->strategy_type;?></span>
								<br/>
							</div>
						</div>
				<?php
					}
				?>
	
						<div id="dailytests" style="margin-top:10px;"></div>
						<input type="hidden" name="numstrategies" id="numstrategies" value="<?php echo count($strategies); ?>" />
					</div>
				</div>		
				<input type="hidden" name="num_strategies" value="<?php echo count($strategies);?>"/>
				<input type="hidden" name="num_kpis" value="<?php echo count($kpis);?>"/>
				<input type="hidden" name="goal_id" value="<?php echo $goalID;?>"/>
			</div>
							
			<!-- END EDIT GOAL PARAMETERS -->
			<div class="cl">&nbsp;</div>
		</div>

		<!-- START DISPLAY TO USERS WHO HAVE NOT ADDED GOAL YET -->		
		<div class="results">
			<div id="who_else_adopted">		
				<ul>
				    <li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label">People have this goal</p></li>
				    <li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
				</ul>
				
				<div class="five_friends"> 
					<!-- Insert 5 images of people who are have this goal here prioritizing friends -->
				</div>
			</div>
		</div>
		<div class="cl">&nbsp;</div> 
		<!-- END DISPLAY TO USERS WHO HAVE NOT ADDED GOAL YET -->		

	</div>
	
	<div class="remove_goal">
			<button class="remove-goal-btn" id="remove_goal" onclick="removeGoal();">Deactivate goal</button>
	</div>
	
	
	<div style="min-height:100px; min-width:700px;"></div>
	<!-- End Score -->	
</div>
<?php if($userHasGoal){?>
<script> onEdit(); </script>			
<?php } ?>

<!-- End Case -->

<?php
		break;
	case PAGEMODE_ACTIVITY:
		// only returns event type stories for this goal
		$rs = $db->doQuery("SELECT * FROM stories WHERE is_public=TRUE AND type='".EventStory::STORY_TYPENAME."' AND event_goal_id=%s ORDER BY entered_at DESC LIMIT 100", $goalID);
		
		Story::printListForRS($rs);
		
		break;
		
	case PAGEMODE_PEOPLE:
		User::printListByGoal($goalID);
		break;
	case PAGEMODE_FACTS:

?>
<div class="facts">
	<div class="adopted_desc">
		<p>
		</p>
		<input id="removeDescButton" type="button" value="X" onclick="removeDescription();" class="small-add-btn up-down" style="display:none;"/>
	</div>


	<div id="who_else_adopted">		
		<ul>
		    <li><p class="res-box"><span><?php echo $numAdopters; ?></span></p><p class="label">People have this goal</p></li>
		    <li class="last"><p class="res-box"><span><?php echo sprintf("%1.1f",$average); ?></span></p><p class="label">Average level</p></li>
		</ul>
		
		<div class="five_friends"> 
			<!-- Insert 5 images of people who are have this goal here prioritizing friends -->
		</div>
	</div>
	
</div>
<?php
		break;
		
	default:
		break;
}


printFooter();
?>