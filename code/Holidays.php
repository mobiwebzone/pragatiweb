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

// if($_SESSION['ROLE'] != 'SUPERADMIN')
// {
// 	if(!empty($_SESSION['CLID']))
// 	{$userclid=$_SESSION['CLID'];}
// 	else
// 	{$userclid=0;}
// }
// else
// {
// 	$userclid=0;
// }

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "HolidayData":HolidayData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */





/*============ Get Holiday Data =============*/ 
function HolidayData($mysqli){
	try
	{
		$ALL_HOLIDAYS = '';
		$data = array();
		$days = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',);
		// Location
		$query = "SELECT LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=HM.LOCID)[LOCATION]
		FROM HOLIDAYS_MASTER HM WHERE ISDELETED=0 
		GROUP BY LOCID
		ORDER BY LOCID";

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$data['data'][] = $row;
			$LOC = $row['LOCID'];

			$ALL_HOLIDAYS .='<div class="card card-body rounded-my mb-4">
								<div class="card-header font-weight-bold bg-white py-0 px-0" style="font-size: larger;">
									<h4 class="font-weight-bold pb-0">'.$row['LOCATION'].'</h4>
								</div>
								<div class="card-body pt-3 py-0">
								<div class="row">';


								// year
								$queryYear= "SELECT FORYEAR FROM HOLIDAYS_MASTER WHERE ISDELETED=0 AND LOCID=$LOC AND
											CAST(FORYEAR AS int) >= CAST(YEAR(GETDATE()) AS int)
											GROUP BY FORYEAR
											ORDER BY FORYEAR";
											
											$resultYear = sqlsrv_query($mysqli, $queryYear);
											$YearCount = unique($queryYear);
											$data['YearCount'][]=$YearCount;

											$w=0;
											while ($rowYear = sqlsrv_fetch_array($resultYear)) {
												$YEAR =$rowYear['FORYEAR'];
												$data['$queryYear'][]=$rowYear;


								$ALL_HOLIDAYS .='<div class="col-lg-4 pt-3">
												  <div class="card mb-3">
													<div class="card-header py-1 font-weight-bold text-center bg-white" style="font-size: large;">'.$YEAR.'</div>
													<div class="card-body bg-white yearBox custom-scrollbar">';

													// $queryDate= "SELECT CONVERT(VARCHAR,HDATE,106)HDATE,HOCCASSION,REMARKS
													// FROM HOLIDAYS_MASTER WHERE ISDELETED=0 AND LOCID=$LOC AND FORYEAR='$YEAR'
													// ORDER BY CONVERT(VARCHAR,HDATE,106)";
													$queryDate= "SELECT CONVERT(VARCHAR,HDATE,106)HDATE,CONVERT(VARCHAR,HDATE,105)S_HDATE
													FROM HOLIDAYS_MASTER WHERE ISDELETED=0 AND LOCID=$LOC AND FORYEAR=$YEAR AND
													CONVERT(DATE,HDATE,105) > CONVERT(DATE,GETDATE(),105)
													GROUP BY CONVERT(VARCHAR,HDATE,106),CONVERT(VARCHAR,HDATE,105)
													ORDER BY CAST(CONVERT(VARCHAR,HDATE,106) AS DATETIME) ASC";
													
													$resultDate = sqlsrv_query($mysqli, $queryDate);

													// throw new Exception($queryDate);
													$w=0;
													while ($rowDate = sqlsrv_fetch_array($resultDate)) {
														$data['$queryDate'][]=$queryDate;
														
														$HDATE=$rowDate['S_HDATE'];
														$HDATE=date('Y-m-d',strtotime($HDATE));
														$data['HDATE']=$HDATE;

														$ALL_HOLIDAYS .='<ul class="card-text" id="Datelist">
																			<li>
																				<span>'.$rowDate['HDATE'].'</span>
																				<ul class="mt-2">';

																				$queryHoliday= "SELECT CONVERT(VARCHAR,HDATE,106)HDATE,HOCCASSION,REMARKS
																				FROM HOLIDAYS_MASTER WHERE ISDELETED=0 AND LOCID=$LOC AND FORYEAR=$YEAR AND CONVERT(DATE,HDATE,105)='$HDATE'
																				ORDER BY CONVERT(VARCHAR,HDATE,106)";


																					$data['$queryHoliday'][]=$queryHoliday;
																					// throw new Exception($data['$queryHoliday'][]=$queryHoliday);
																					$resultHoliday = sqlsrv_query($mysqli, $queryHoliday);
																					while ($rowHoliday = sqlsrv_fetch_array($resultHoliday)) {
																						$Remark=$rowHoliday['REMARKS'];
																						$ALL_HOLIDAYS .='<li class="pb-0 text-dark">'.$rowHoliday['HOCCASSION'].'</li>
																										<li class="pb-2">';
																										if($Remark != ''){
																											$ALL_HOLIDAYS .='<small class=" text-secondary">( '.$Remark.' )</small>';
																										}
																										else{
																											$ALL_HOLIDAYS .='';
																										}
																					}

																					
																	$ALL_HOLIDAYS .='</li>
																				</ul>
																			</li>
																		</ul>';
													}
												$ALL_HOLIDAYS .='</div>
															</div>
															</div>';
												$w++;
											}
						$ALL_HOLIDAYS .='</div></div>
							         </div>';
		}
		$data['success'] = true;
		$data['ALL_HOLIDAYS']=$ALL_HOLIDAYS;
		
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}






function adminDashboad($conn){
	try{
	    $data = array();
        if(!empty($_SESSION['MEP_USERID']))
        {
		    $data['success'] = true;
            $data['message'] = 'Login details true';
        }
        else
        {
            $data['success'] = false;
            $data['message'] = 'Login details false';
        }
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







