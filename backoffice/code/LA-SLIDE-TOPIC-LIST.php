<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	switch ($type) {
        case "getUnderTopics":$LIST = getUnderTopics($conn,0);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ GET UNDER TOPIC =============*/ 
$data = array();
$cardList = "";
$index = 0;
$RETURN_DATA =array();
$SUCCESS = false;
// $data['success'] = false;
function getUnderTopics($mysqli,$TOPICID){
	global $data, $cardList, $index,$SUCCESS, $RETURN_DATA;
	$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
	$ddlGrade = ($_POST['ddlGrade'] =='undefined' || $_POST['ddlGrade'] =='') ? 0 : $_POST['ddlGrade'];
	$ddlSubject = ($_POST['ddlSubject'] =='undefined' || $_POST['ddlSubject'] =='') ? 0 : $_POST['ddlSubject'];

	$query = "SELECT TOPICID,TOPIC,SEQNO,UNDERTOPICID,
				CASE WHEN (SELECT COUNT(*) FROM LA_TOPICS_MASTER WHERE UNDERTOPICID=TM.TOPICID AND ISDELETED=0)>0 THEN 1 ELSE 0 END NEXT_TOPIC_EXIST
				FROM LA_TOPICS_MASTER TM WHERE ISDELETED=0 AND LOCID=$ddlLocation 
				AND GRADEID=$ddlGrade AND SUBID=$ddlSubject AND UNDERTOPICID=$TOPICID
				ORDER BY SEQNO,TOPIC";
	$count = unique($query);
	if($count > 0){
		$result = sqlsrv_query($mysqli, $query);
		$SUCCESS = true;
		// $cardList.="<div class='accordion' id='accordionExample'>";		
		$cardList.=$index == 0 ? "<ul id='accordionList'>" : "< class='panel' lis>";
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$TOPIC = $row['TOPIC'];
			$TOPICID = $row['TOPICID'];
			$UNDERTOPICID = $row['UNDERTOPICID'];
			$NEXT_TOPIC_EXIST = $row['NEXT_TOPIC_EXIST'];
			$angleClass =  $NEXT_TOPIC_EXIST>0 ? 'fa-angle-right' : '';
			if($UNDERTOPICID > 0){
				$cardList.="<li>
								<button ng-click='getSlideHeads($TOPICID,\"$TOPIC\")' class='accordion d-flex'><i id='angle' class='fa $angleClass pr-3'></i> $TOPIC</button>
								<ul class='panel nested'>";
			}else{
				$cardList.="<li>
								<button ng-click='getSlideHeads($TOPICID,\"$TOPIC\")' class='accordion d-flex'><i id='angle' class='fa $angleClass pr-3'></i> $TOPIC</button>
								<ul class='panel nested'>";
			}
			getUnderTopics($mysqli ,$row['TOPICID']);
			$cardList.="	</ul>
						</li>";
		}
		$cardList.="</ul>";
	}else{
	}
	$RETURN_DATA = ['data'=>$cardList, 'success'=>$SUCCESS];
	return $RETURN_DATA;
}
/*============ GET UNDER TOPIC =============*/ 


$LIST['data'].="
<script>
  $(document).ready(function() {
    $('#accordionList .accordion').click(function() {
		$('#accordionList').find('.focus').removeClass('focus');
		// console.log($(this).find(ul).has(li));
		if($(this).next('.panel').find('li').length > 0){
			$(this).find('#angle').toggleClass('fa-angle-right');
			$(this).find('#angle').toggleClass('fa-angle-down');
			$(this).toggleClass('active');
			$(this).next('.panel').slideToggle();
		}else{
			$(this).addClass('focus');
		}
    });
  });
</script>";


$data['data'] = $LIST;
// $data['success'] = $SUCCESS;
echo json_encode($data);exit;

function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







