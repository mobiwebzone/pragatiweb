<?php
session_start();
require_once 'connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "adminDashboad":adminDashboad($conn);break;
        case "DisplayPlan":DisplayPlan($conn);break;
        case "DisplayPlanProduct":DisplayPlanProduct($conn);break;
        // case "DisplayPlanPrice":DisplayPlanPrice($conn);break;
        // case "DisplayPlanSchedule":DisplayPlanSchedule($conn);break;
        case "DisplayPlanDetails":DisplayPlanDetails($conn);break;
        case "DisplayTopic":DisplayTopic($conn);break;
        case "getLocation":getLocation($conn);break;
        case "deleteContact":deleteContact($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/*============ Get Display Topic =============*/ 
function DisplayTopic($mysqli){
	try
	{
		$PRODUCTID = ($_POST['PRODUCTID'] == 'undefined' || $_POST['PRODUCTID'] == '') ? 0 : $_POST['PRODUCTID'];

		if($PRODUCTID > 0){
			$query = "SELECT TOPIC_ID,TOPIC FROM TOPICS WHERE PRODUCTID=$PRODUCTID AND ISDELETED=0 ORDER BY DISPLAY_ORDER";
	
			$result = sqlsrv_query($mysqli, $query);
			$data = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['TOPIC_ID'] = (int) $row['TOPIC_ID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
			$data ['$query '] = $query ;
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

/*============ Get Display Plan schedule =============*/ 
// function DisplayPlanSchedule($mysqli){
// 	try
// 	{
// 		$PLANID = $_POST['PLANID'] == 'undefined' ? 0 : $_POST['PLANID'];

// 		if($PLANID > 0){
// 			$query = "SELECT LOCATIONID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0) LOC,
// 			(SELECT FLAG_ICON FROM COUNTRIES WHERE COUNTRYID=((SELECT LOC_COUNTRY FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) AND ISDELETED=0 ) CFLAG
// 			,PS.WEEKDAYNAME,TIMEFROM_ET,TIMETO_ET,ZOOMCODE,REMARKS,
// 			LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMEFROM_ET),8),5) ACTUAL_TIME_FROM,
// 			LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMETO_ET),8),5) ACTUAL_TIME_TO
// 			FROM PLAN_LOCATIONS PL,PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0
// 			ORDER BY LOC,(select dbo.GetWeekDay(PS.WEEKDAYNAME) )";
	
// 			$result = sqlsrv_query($mysqli, $query);
// 			$data = array();
// 			while ($row = sqlsrv_fetch_array($result)) {
// 				$row['LOCATIONID'] = (int) $row['LOCATIONID'];
// 				$data['data'][] = $row;
// 			}
// 			$data['success'] = true;
// 			$data ['$query '] = $query ;
// 		}
// 		echo json_encode($data);exit;
	
// 	}catch (Exception $e){
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
// 	}
// }


/*============ Get Display Plan Details =============*/ 
function DisplayPlanDetails($mysqli){
	try
	{
		$PLANID = ($_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];

		if($PLANID > 0){
			$query = "SELECT LOCATIONID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0) LOC,
			(SELECT FLAG_ICON FROM COUNTRIES WHERE COUNTRYID=((SELECT LOC_COUNTRY FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) AND ISDELETED=0 ) CFLAG,
			(SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) CURRENCY_CODE,
			(SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) CURRENCY_SYMBOL,
			CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) =1 THEN
					(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID)*(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
				ELSE 
					(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID)/(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
				END COST,
			(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID) PRICE,
			(SELECT CONVERT(VARCHAR,STARTDATE,105)STARTDATE FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0) START_DATE ,
			(SELECT CONVERT(VARCHAR,ENDDATE,105)ENDDATE FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0) END_DATE,
			(SELECT PS.WEEKDAYNAME + ' ' + 
			LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMEFROM_ET),8),5) 
			+ '-' + 
			LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMETO_ET),8),5) 
			+', '
			FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 
			ORDER BY (select dbo.GetWeekDay(PS.WEEKDAYNAME)) FOR XML PATH('')) SCHEDULE,
			(SELECT DISPLAYCOLOR FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0)DISPLAYCOLOR,
			(SELECT PLANNAME FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0)[PLAN]
			FROM PLAN_LOCATIONS PL WHERE PLANID=$PLANID AND ISDELETED=0";
	
			$result = sqlsrv_query($mysqli, $query);
			$data = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['LOCATIONID'] = (int) $row['LOCATIONID'];
				$row['COST'] = (float) $row['COST'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
			$data ['$query '] = $query ;
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


/*============ Get Display Plan Product =============*/ 
function DisplayPlanProduct($mysqli){
	try
	{
		$PLANID = ($_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];

		if($PLANID > 0){
			$query = "SELECT PRODUCTID,(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)PRODUCT,PRICE,
			(SELECT DISPLAY_ORDER FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0) DISPLAY_ORDER,
			(SELECT DISPLAY_COLOR FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)DISPLAY_COLOR
			 FROM PLAN_PRODUCTS PP WHERE PLANID=$PLANID
			ORDER BY (SELECT DISPLAY_ORDER FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)";
	
			$result = sqlsrv_query($mysqli, $query);
			$data = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PRODUCTID'] = (int) $row['PRODUCTID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
			$data ['$query '] = $query ;
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


/*============ Get Display Plan =============*/ 
function DisplayPlan($mysqli){
	try
	{
		$PDMID = ($_POST['PDMID'] == 'undefined' || $_POST['PDMID'] == '') ? 0 : $_POST['PDMID'];
		$grid='';
		if($PDMID > 0){
			$query = "SELECT PLANID,
			(SELECT DISPLAYCOLOR FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)DISPLAYCOLOR,
			(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)[PLAN] FROM PRODUCT_DISPLAY_DETAIL PD WHERE PLANID>0 AND PDMID=$PDMID AND ISDELETED=0 AND PLANID IN(SELECT PLANID FROM PLANS WHERE ISDELETED=0
			AND CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,DISPLAYFROMDATE,105) AND CONVERT(DATE,DISPLAYTODATE,105)) 
			ORDER BY (SELECT [ORDER] FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PD.PDMID)";
	
			$result = sqlsrv_query($mysqli, $query);
			$data = array();
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PLANID'] = (int) $row['PLANID'];
				$data['PLANID'] = (int) $row['PLANID'];
				$PLANID= (int) $row['PLANID'];
				$data['data'][] = $row;




				$grid .='<div class=" card card-body rounded-my my-2" >
                        <h4 class="font-weight-bold border-bottom pb-2" style="color:'.$row['DISPLAYCOLOR'].'!important;">'.$row['PLAN'].'</h4>

						<div class="table-responsive card-body w-100 pb-0">
						<table class="table  table-borderless border-0 table-hover bg-white table-sm">
																
						<tr>
							<th></th>
							<th>Location</th>
							<th class="text-nowrap">Course Start Date</th>
							<th class="text-nowrap">Course End Date</th>
							<th>Cost</th>
							<th>Schedule</th>
						</tr>';


						$GetDetailsquery = "SELECT LOCATIONID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0) LOC,
						(SELECT FLAG_ICON FROM COUNTRIES WHERE COUNTRYID=((SELECT LOC_COUNTRY FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) AND ISDELETED=0 ) CFLAG,
						(SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) CURRENCY_CODE,
						(SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) CURRENCY_SYMBOL,
						CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) =1 THEN
								(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID)*(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
							ELSE 
								(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID)/(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
							END COST,
					
						CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) =1 THEN
							(SELECT INST_AMOUNT FROM PLANS WHERE PLANID=$PLANID)*(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
						ELSE 
							(SELECT INST_AMOUNT FROM PLANS WHERE PLANID=$PLANID)/(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
						END INSTALLMENT,
					
						(SELECT INST_FREQ FROM PLANS WHERE PLANID=$PLANID) INST_FREQ, 
						(SELECT INST_NO FROM PLANS WHERE PLANID=$PLANID) INST_NO,
					
						(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID) PRICE,
						(SELECT CONVERT(VARCHAR,STARTDATE,107)STARTDATE FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0) START_DATE ,
						(SELECT CONVERT(VARCHAR,ENDDATE,107)ENDDATE FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0) END_DATE,
						(SELECT PS.WEEKDAYNAME + ' ' + 
						LEFT(CONVERT(VARCHAR,(DATEADD(HH,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,0,CHARINDEX(':',LOC_ET_DIFF)) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
						CASE LEFT((SELECT LOC_ET_DIFF FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID),1) WHEN '-' THEN
						DATEADD(MINUTE,-ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2)  AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
						TIMEFROM_ET)
						ELSE 
						DATEADD(MINUTE,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2)  AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
						TIMEFROM_ET) END
						)),100),7) 
						+ '-' + 	
						LEFT(CONVERT(VARCHAR,(DATEADD(HH,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,0,CHARINDEX(':',LOC_ET_DIFF)) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
						CASE LEFT((SELECT LOC_ET_DIFF FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID),1) WHEN '-' THEN
						DATEADD(MINUTE,-ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
						TIMETO_ET)
						ELSE
						DATEADD(MINUTE,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
						TIMETO_ET) END
						
						)),100),7) 
						+'  |  '
						FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 
						-- ORDER BY (select dbo.GetWeekDay(PS.WEEKDAYNAME)) 
						FOR XML PATH('')) SCHEDULE,
					
					
						(SELECT PS.REMARKS + ' | '
						FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 
						FOR XML PATH('')) REMARK
						FROM PLAN_LOCATIONS PL WHERE PLANID=$PLANID AND ISDELETED=0";

						$DETAILS_COUNT = unique($GetDetailsquery);
						$GetDetailsresult = sqlsrv_query($mysqli, $GetDetailsquery);
						$data = array();
						while ($getDetailsrow = sqlsrv_fetch_array($GetDetailsresult)) {
							$getDetailsrow['COST'] = (float) $getDetailsrow['COST'];
							$getDetailsrow['INSTALLMENT'] = (float) $getDetailsrow['INSTALLMENT'];
							$LOCATIONID = (int) $getDetailsrow['LOCATIONID'];

							$getDetailsrow['SCHEDULE'] = rtrim($getDetailsrow['SCHEDULE'], " | ");
							$getDetailsrow['REMARK'] = rtrim($getDetailsrow['REMARK'], " | ");
							// $row['LOCATIONID'] = (int) $row['LOCATIONID'];
							// $data['data'][] = $row;

							
							$grid .="<tr title='$LOCATIONID'>
										<td class='text-nowrap'>
											<button class='btn btn-light' style='color:#000000!important' ng-click='EnrollStudent($LOCATIONID,$PLANID)' >Enroll</button>
										</td>
										<td class='text-nowrap'>
											<i class='flag-icon flag-icon-".$getDetailsrow['CFLAG']."'></i>
											".$getDetailsrow['LOC']."
										</td>
										<td>".$getDetailsrow['START_DATE']."</td>
										<td>".$getDetailsrow['END_DATE']."</td>
										<td class='text-nowrap'>
											<span>Lumpsum &nbsp; : <i class='fa fa-".$getDetailsrow['CURRENCY_SYMBOL']."'></i>
												".$getDetailsrow['COST']."</span> <br>

											<span data-ng-show='".$getDetailsrow['INSTALLMENT'].">0'> Installment : <i class='fa fa-".$getDetailsrow['CURRENCY_SYMBOL']."'></i> ".$getDetailsrow['INSTALLMENT']." 
											 X ".$getDetailsrow['INST_NO']."
											 (".$getDetailsrow['INST_FREQ'].")</span>
										</td>
										<td class='text-nowrap'>
											<span>".$getDetailsrow['SCHEDULE']."</span>
											<p>".$getDetailsrow['REMARK']."</p>
										</td>
										

									</tr>
									<tr ng-hide='$DETAILS_COUNT>0'>
									<td colspan='7' class='text-center text-danger'>No Records</td>
									</tr>";
						}
						// data-ng-show='".$getDetailsrow['INSTALLMENT'].">0'
				
				$grid .='</table>';


				$queryTopicButton = "SELECT PRODUCTID,(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)PRODUCT,PRICE,
					(SELECT DISPLAY_ORDER FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0) DISPLAY_ORDER,
					(SELECT DISPLAY_COLOR FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)DISPLAY_COLOR
					FROM PLAN_PRODUCTS PP WHERE PLANID=$PLANID AND ISDELETED=0
					ORDER BY (SELECT DISPLAY_ORDER FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)";
			
			$resultTopicButton = sqlsrv_query($mysqli, $queryTopicButton);
			$data = array();
			$data['queryTopicButton']=$queryTopicButton;

					$grid .='<div class="row">
								<span class="font-weight-bold py-2 mr-2 ml-4">COURSE CONTENT : </span>';

					while ($rowTopicButton = sqlsrv_fetch_array($resultTopicButton)) {
						$PRODUCTID = (int) $rowTopicButton['PRODUCTID'];
						$PRODUCT = $rowTopicButton['PRODUCT'];
						$DISPLAY_COLOR = $rowTopicButton['DISPLAY_COLOR'];
						$data['data'][] = $rowTopicButton;

						
						if($PRODUCT != ''){
							$grid .="<button class='btn btn-light' style='color:$DISPLAY_COLOR!important' ng-click='PopDisplayTopic($PRODUCTID, \"$PRODUCT\")' data-toggle='modal' data-target='#exampleModalScrollable'>$PRODUCT</button>";
						}
					}


					$grid .='</div>';


				$grid .='</div>';
				$grid .='</div>';
			}
			$data['PlanDetail'] = $grid;
			$data['query'] = $query;
			$data['GetDetailsquery'] = $GetDetailsquery;
			$data['success'] = true;
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






