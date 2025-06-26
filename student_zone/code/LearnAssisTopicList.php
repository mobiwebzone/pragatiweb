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
        case "getTopics":$LIST = getTopics($conn,0);break;
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
function getTopics($mysqli,$TOPICID){
	global $data, $cardList, $index,$SUCCESS, $RETURN_DATA;
	$LOCID = ($_POST['LOCID'] == 'undefined' || $_POST['LOCID'] == '') ? 0 : $_POST['LOCID'];
	$GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];
	$SUBID = ($_POST['SUBID'] == 'undefined' || $_POST['SUBID'] == '') ? 0 : $_POST['SUBID'];

	$query = "SELECT TOPICID,TOPIC,UNDERTOPICID,
				CASE WHEN (SELECT COUNT(*) FROM LA_TOPICS_MASTER WHERE UNDERTOPICID=TM.TOPICID)>0 THEN 1 ELSE 0 END NEXT_TOPIC_EXIST
				FROM LA_TOPICS_MASTER TM WHERE ISDELETED=0 AND LOCID=$LOCID 
				AND GRADEID=$GRADEID AND SUBID=$SUBID AND UNDERTOPICID=$TOPICID
				ORDER BY TOPIC";
	$count = unique($query);
	if($count > 0){
		$result = sqlsrv_query($mysqli, $query);
		$SUCCESS = true;
		// $cardList.="<div class='accordion' id='accordionExample'>";		
		$cardList.="<ol>";
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$TOPIC = $row['TOPIC'];
			$TOPICID = $row['TOPICID'];
			$UNDERTOPICID = $row['UNDERTOPICID'];
			$NEXT_TOPIC_EXIST = $row['NEXT_TOPIC_EXIST'];
			$angleClass =  $NEXT_TOPIC_EXIST>0 ? 'fa-angle-right' : '';
			$CLASS = $UNDERTOPICID == 0 ? 'GRADES px-4 rounded-pill' : 'SUBJECTS px-2';
			$LiCLASS = $UNDERTOPICID > 0 ? 'pl-4 mt-2' : '';
			$event = '$event';

			if($UNDERTOPICID > 0){
				$cardList.="<li class='py-2 $LiCLASS'>
								<span class='$CLASS' ng-click='getSlides($TOPICID,\"$TOPIC\",$event)' data-toggle='modal' data-target='#slideModal'>$TOPIC</span>";
			}else{
				$cardList.="<li class='py-2 $LiCLASS'>
								<span class='$CLASS' ng-click=''>$TOPIC</span>";
			}
							getTopics($mysqli ,$TOPICID);
			$cardList.="</li>";
		}
		$cardList.="</ol>";
	}else{
	}
	$RETURN_DATA = ['data'=>$cardList, 'success'=>$SUCCESS];
	return $RETURN_DATA;
}
/*============ GET UNDER TOPIC =============*/ 


// $LIST['data'].="
// <script>
//   $(document).ready(function() {
//     $('#accordionList .accordion').click(function() {
// 		$('#accordionList').find('.focus').removeClass('focus');
// 		// console.log($(this).find(ul).has(li));
// 		if($(this).next('.panel').find('li').length > 0){
// 			$(this).find('#angle').toggleClass('fa-angle-right');
// 			$(this).find('#angle').toggleClass('fa-angle-down');
// 			$(this).toggleClass('active');
// 			$(this).next('.panel').slideToggle();
// 		}else{
// 			$(this).addClass('focus');
// 		}
//     });
//   });
// </script>";


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







