<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "getBlogs":getBlogs($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/*============ Get Blogs Data =============*/ 
function getBlogs($mysqli){
	try
	{
		$data=array();
		$query = "SELECT BLOGID,BCATID,(SELECT BLOG_CATEGORY FROM BLOG_CATEGORIES WHERE BCATID=BLOGS.BCATID)BLOG_CATEGORY,
		CONVERT(VARCHAR,POSTING_DATE,106)POSTING_DATE_SET, CONVERT(VARCHAR,POSTING_DATE,20)POSTING_DATE,TOPIC,TAGS,BLOG,
		(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE [UID]=BLOGS.INSERTID)USERNAME
		FROM BLOGS WHERE ISDELETED=0 ORDER BY CONVERT(DATE,POSTING_DATE,105) DESC";

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$date=date_create($row['POSTING_DATE']);
			$row['POSTING_DATE_SHOW'] = date_format($date,"d-m-Y h:i A");
			$data['data'][] = $row;
		}


		// GET BLOG CATEGORY
		$queryBC = "SELECT BCATID,BLOG_CATEGORY,
		ISNULL((SELECT TOPIC + ' $$ ' FROM BLOGS WHERE ISDELETED=0 AND BCATID=BC.BCATID FOR XML PATH('')),'')TOPIC,
		ISNULL((SELECT CAST(BLOGID AS VARCHAR) + ' $$ ' FROM BLOGS WHERE ISDELETED=0 AND BCATID=BC.BCATID FOR XML PATH('')),'')BLOGID
		FROM BLOG_CATEGORIES BC WHERE ISDELETED=0 ORDER BY BLOG_CATEGORY";

		$resultBC = sqlsrv_query($mysqli, $queryBC);
		while ($rowBC = sqlsrv_fetch_array($resultBC,SQLSRV_FETCH_ASSOC)) {
			$TOPIC_ARRAY = array();
			$BLOGID_ARRAY = array();
			$rowBC['TOPIC'] = rtrim($rowBC['TOPIC'],' $$ ');
			$rowBC['BLOGID'] = rtrim($rowBC['BLOGID'],' $$ ');
			$TOPIC_ARRAY = explode(' $$ ',$rowBC['TOPIC']);
			$BLOGID_ARRAY = explode(' $$ ',$rowBC['BLOGID']);
			for($i=0;$i<count($TOPIC_ARRAY);$i++){
				$rowBC['TOPIC_ARRAY'][$i][] = $TOPIC_ARRAY[$i];
				$rowBC['TOPIC_ARRAY'][$i][] = (int) $BLOGID_ARRAY[$i];
			}
			$data['BLOG_CATEGORY'][] = $rowBC;
		}
		
		$data['success'] = true;
		echo json_encode($data);exit;
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

/*============ Get Blogs Data =============*/ 




/*============ Get Working Hours Data =============*/ 
// function WorkingHoursData($mysqli){
// 	try
// 	{
// 		$WORKING_HOURS = '';
// 		$data = array();
// 		$days = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',);
// 		// Location
// 		$query = "SELECT LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=LW.LOCID)[LOCATION]
// 		FROM LOCATION_WORK_HOURS LW WHERE ISDELETED=0 
// 		GROUP BY LOCID
// 		ORDER BY LOCID";

// 		$result = sqlsrv_query($mysqli, $query);
// 		while ($row = sqlsrv_fetch_array($result)) {
// 			$data['data'][] = $row;
// 			$LOC = $row['LOCID'];

// 			$WORKING_HOURS .='<div class="card bg-white card card-body rounded-my mb-3 pb-0">
// 								<div class="card-header font-weight-bold border-bottom bg-white py-0 px-0" style="font-size: larger;">
// 									<h4 class="font-weight-bold pb-0">'.$row['LOCATION'].'</h4>
// 								</div>
// 								<div class="card-body p-0">
// 									<table  class="table table-borderless border-0 mb-0 mt-2">
// 										<thead>
// 											<tr class="text-dark">';
											
// 											//  table-borderless border-0
// 								// Week
// 								$queryWeek = "SELECT WDAY,WDAY_NAME
// 								FROM LOCATION_WORK_HOURS LW WHERE ISDELETED=0
// 								GROUP BY WDAY,WDAY_NAME
// 								ORDER BY WDAY";

// 								$resultWeek = sqlsrv_query($mysqli, $queryWeek);

								
// 								for ($d=0; $d<COUNT($days); $d++) {
// 									// $WEEK =$rowWeek['WDAY'];
// 									$WD = $days[$d];

// 									$WORKING_HOURS .='<th class="text-center" style="width: 14.28%;" >'.$WD.'</th>';
// 									// ng-class="'.$d.' == 0 ? \'pl-4\' : \'\'"
// 									// $hw++;
// 								}
								

// 						$WORKING_HOURS .='</tr>
// 										</thead>

// 										<tbody>
// 										<tr>';


// 											// Week
// 											$queryWeek2 = "SELECT WDAY,WDAY_NAME
// 											FROM LOCATION_WORK_HOURS LW WHERE ISDELETED=0 AND LOCID=$LOC
// 											GROUP BY WDAY,WDAY_NAME
// 											ORDER BY WDAY";
											
// 											$resultWeek2 = sqlsrv_query($mysqli, $queryWeek2);
// 											$week2Count = unique($queryWeek2);
// 											$data['week2Count'][]=$week2Count;

// 												for ($d1=0; $d1<COUNT($days); $d1++) { 
// 												$WDAY_NAME1 =$days[$d1];


// 												// $WORKING_HOURS .='<td class="text-center" ng-class="'.$d1.' < 6 ? \'border-right\' : \'\'">';
// 												$WORKING_HOURS .='<td class="text-center pt-0">';

	
// 												// Time
// 												$queryTime = "SELECT CONVERT(VARCHAR,TIME_FROM,100)TIME_FROM,
// 												CONVERT(VARCHAR,TIME_TO,100)TIME_TO,CLOSED
// 												FROM LOCATION_WORK_HOURS WHERE LOCID=$LOC AND WDAY_NAME='$WDAY_NAME1' AND ISDELETED=0";
										
// 												$resultTime = sqlsrv_query($mysqli, $queryTime);
// 												$COUNT_TIMEQ = unique($queryTime);  
												
// 												$s=0;
// 												while ($rowTime = sqlsrv_fetch_array($resultTime)) {
// 													$data['$queryTime'][]=$queryTime;	
													
// 													if($rowTime['CLOSED'] == 0){
// 														$WORKING_HOURS .='<p class="my-0 text-secondary">
// 																			'.$rowTime['TIME_FROM'].' - '.$rowTime['TIME_TO'].'
// 																		</p>';
// 													}
// 													else
// 													{
// 														$WORKING_HOURS .='<p class="my-0 text-danger">
// 																			CLOSED
// 																		</p>';
// 													}
// 																	// <i class="fa fa-circle text-dark pr-2" style="font-size:10px" ng-show="'.$w.' == 0"></i>
// 													$s++;
// 												}
											
// 												$WORKING_HOURS .='</td>';
// 											}


// 										$WORKING_HOURS .='</tr>
// 										</tbody>
// 									</table>
// 								</div>
// 							</div>';

// 		  	$WORKING_HOURS .='';
// 		}
// 		$data['success'] = true;
// 		$data['WORKING_HOURS']=$WORKING_HOURS;
		
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }










function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







