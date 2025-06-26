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
		case "productCarousel":productCarousel($conn);break;
		case "getFooterMenu":getFooterMenu($conn);break;
        case "DisplayPlan":DisplayPlan($conn);break;
        case "DisplayPlanAll":DisplayPlanAll($conn);break;
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
		$data = array();
		$PRODUCTID = ($_POST['PRODUCTID'] == 'undefined' || $_POST['PRODUCTID'] == '') ? 0 : $_POST['PRODUCTID'];
		if($PRODUCTID > 0){
			$query = "SELECT TOPIC_ID,TOPIC,TOPIC_DESC,OBJECTTYPE,OBJECTNAME FROM TOPICS WHERE PRODUCTID=$PRODUCTID AND ISDELETED=0 ORDER BY DISPLAY_ORDER";
			$result = sqlsrv_query($mysqli, $query);
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
			FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 AND PS.LOCID=PL.LOCATIONID
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


/*============ GET PRODUCT CAROUSEL =============*/ 
function productCarousel($mysqli){
	try
	{
		$data = array();
		$PDMID = ($_POST['PDMID'] == 'undefined' || $_POST['PDMID'] == '') ? 0 : $_POST['PDMID'];
		$PTYPE = ($_POST['PTYPE'] == 'undefined' || $_POST['PTYPE'] == '') ? '' : $_POST['PTYPE'];
		if($PDMID==0) throw new Exception("Invalid PDMID.");
		if($PTYPE=='') throw new Exception("Invalid PTYPE.");

		if($PTYPE == 'P'){
			$query = "SELECT PCID,DISPLAY_TYPE,PIC,PIC_CAPTION,
					CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,
					PIC_INTERVAL,SEQNO FROM PRODUCT_CAROUSEL_DISPLAY WHERE ISDELETED=0 AND PDMID=$PDMID AND 
					CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,PIC_FROMDT,105) AND CONVERT(DATE,PIC_TODT,105)
					ORDER BY SEQNO ASC";
		}else if($PTYPE == 'L'){
			$query = "SELECT PCID,DISPLAY_TYPE,PIC,PIC_CAPTION,
					CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,
					PIC_INTERVAL,SEQNO FROM PRODUCT_CAROUSEL_DISPLAY WHERE ISDELETED=0 AND LOCID=$PDMID AND 
					CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,PIC_FROMDT,105) AND CONVERT(DATE,PIC_TODT,105)
					ORDER BY SEQNO ASC";
		}else{
			throw new Exception("Invalid PTYPE.");
		}
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PIC'] = 'backoffice/images/product_carousel/'.$row['PIC'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
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
/*============ GET PRODUCT CAROUSEL =============*/ 


/*============ GET FOOTER MENU =============*/ 
function getFooterMenu($mysqli){
	try
	{
		$data = array();
		$PDMID = ($_POST['PDMID'] == 'undefined' || $_POST['PDMID'] == '') ? 0 : $_POST['PDMID'];
		$PTYPE = ($_POST['PTYPE'] == 'undefined' || $_POST['PTYPE'] == '') ? '' : $_POST['PTYPE'];
		if($PDMID==0) throw new Exception("Invalid PDMID.");
		if($PTYPE=='') throw new Exception("Invalid PTYPE.");

		if($PTYPE == 'P'){
			$query = "SELECT RESOURCEID,(SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=MI.RESOURCEID)RESOURCE 
					FROM MENU_ITEM_RESOURCES MI
					WHERE ISDELETED=0 AND MITEMID IN (SELECT MITEMID FROM MENU_ITEMS WHERE ISDELETED=0 AND PDMID=$PDMID)
					ORDER BY SEQNO";
		}else if($PTYPE == 'L'){
			$query = "";
		}else{
			throw new Exception("Invalid PTYPE.");
		}
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
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
/*============ GET FOOTER MENU =============*/ 


/*============ Get Display Plan =============*/ 
function DisplayPlan($mysqli){
	try
	{
		$data = array();
		$PDMID = ($_POST['PDMID'] == 'undefined' || $_POST['PDMID'] == '') ? 0 : $_POST['PDMID'];
		$grid='';
		$gridError ='<div class=" card card-body rounded-my my-2" ><h4 class="text-center text-danger">Data Not Found.</h4></div>';
		if($PDMID > 0){
			$query = "SELECT PLANID,
			(SELECT DISPLAYCOLOR FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)DISPLAYCOLOR,
			(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)[PLAN] 
			FROM PRODUCT_DISPLAY_DETAIL PD WHERE PLANID>0 AND PDMID=$PDMID AND ISDELETED=0 
			AND PLANID IN(SELECT PLANID FROM PLANS WHERE ISDELETED=0 AND CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,DISPLAYFROMDATE,105) AND CONVERT(DATE,DISPLAYTODATE,105)) 
			ORDER BY SEQNO";
			// ORDER BY (SELECT [ORDER] FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PD.PDMID)";

			$indexMain = 0;
			$result = sqlsrv_query($mysqli, $query);
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
							<th class="text-nowrap">Course Start-End</th>
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
								DATEADD(MINUTE,-ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2)  AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMEFROM_ET)
							ELSE 
								DATEADD(MINUTE,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2)  AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMEFROM_ET) 
							END )),100),7) 
							+ ' - ' + 	
							LEFT(CONVERT(VARCHAR,(DATEADD(HH,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,0,CHARINDEX(':',LOC_ET_DIFF)) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),
							CASE LEFT((SELECT LOC_ET_DIFF FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID),1) WHEN '-' THEN
								DATEADD(MINUTE,-ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMETO_ET)
							ELSE
								DATEADD(MINUTE,ISNULL((SELECT CAST(SUBSTRING(LOC_ET_DIFF,CHARINDEX(':',LOC_ET_DIFF)+1,2) AS FLOAT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMETO_ET) 
							END
						
							)),100),7) 
						+'  |  '
						FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 AND PS.LOCID=PL.LOCATIONID
						-- ORDER BY (select dbo.GetWeekDay(PS.WEEKDAYNAME)) 
						FOR XML PATH('')) SCHEDULE,
					
					
						(SELECT CASE WHEN PS.REMARKS='' OR PS.REMARKS IS NULL THEN '' ELSE PS.REMARKS + ' | ' END 
						--PS.REMARKS + ' | '
						FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 AND PS.LOCID=PL.LOCATIONID 
						FOR XML PATH('')) REMARK
						FROM PLAN_LOCATIONS PL 
						WHERE PLANID=$PLANID AND ISDELETED=0  AND LOCATIONID NOT IN (SELECT LOC_ID FROM LOCATIONS WHERE ISDELETED=1)";
						// $data['$GetDetailsquery1'][] = $GetDetailsquery;

						$DETAILS_COUNT = unique($GetDetailsquery);
						$GetDetailsresult = sqlsrv_query($mysqli, $GetDetailsquery);

						while ($getDetailsrow = sqlsrv_fetch_array($GetDetailsresult)) {
							$COST = (int) $getDetailsrow['COST'];
							$getDetailsrow['COST'] = (float) $getDetailsrow['COST'];
							$getDetailsrow['COST'] = number_format($getDetailsrow['COST']);
							$getDetailsrow['INSTALLMENT'] = (float) $getDetailsrow['INSTALLMENT'];
							$getDetailsrow['INSTALLMENT'] = number_format($getDetailsrow['INSTALLMENT']);
							$LOCATIONID = (int) $getDetailsrow['LOCATIONID'];

							$getDetailsrow['SCHEDULE'] = rtrim($getDetailsrow['SCHEDULE'], " | ");
							$getDetailsrow['SCHEDULE'] = preg_replace('/(\d{1,2}:\d{2})(AM|PM)/', '$1 $2', $getDetailsrow['SCHEDULE']);
				
							$getDetailsrow['SCHEDULE'] = str_replace(":00","",$getDetailsrow['SCHEDULE']) ;
							$getDetailsrow['REMARK'] = rtrim($getDetailsrow['REMARK'], " | ");
							$CURR_SYMBOL = isset($getDetailsrow['CURRENCY_SYMBOL']) ? $getDetailsrow['CURRENCY_SYMBOL'] : '';
							$CURR_CODE = isset($getDetailsrow['CURRENCY_CODE']) ? $getDetailsrow['CURRENCY_CODE'] : '';
							// $row['LOCATIONID'] = (int) $row['LOCATIONID'];
							// $data['data'][] = $row;

							
							$grid .="<tr title='$LOCATIONID'>
										<td class='text-nowrap' style='width:8%;'>
											<button class='btn btn-light' style='color:#000000!important' ng-click='EnrollStudent($LOCATIONID,$PLANID)' >Enroll</button>
										</td>
										<td class='text-nowrap'>
											<i class='flag-icon flag-icon-".$getDetailsrow['CFLAG']."'></i>
											".$getDetailsrow['LOC']."
										</td>
										<td class-text-nowrap>".$getDetailsrow['START_DATE']."</br>".$getDetailsrow['END_DATE']."</td>
										<td class='text-nowrap'>
											<span ng-hide='$COST<=0'>Lumpsum &nbsp; : <b>".($CURR_SYMBOL==''? $CURR_CODE : '')."</b><i ng-if='\"$CURR_SYMBOL\"!=\"\"' class='fa fa-$CURR_SYMBOL'></i>
												".$getDetailsrow['COST']."</span> <br>

											<span data-ng-show='".$getDetailsrow['INSTALLMENT'].">0'> Installment : <b>".($CURR_SYMBOL==''? $CURR_CODE : '')."</b><i ng-if='\"$CURR_SYMBOL\"!=\"\"' class='fa fa-".$CURR_SYMBOL."'></i> ".$getDetailsrow['INSTALLMENT']." 
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
					ORDER BY PRODUCT";
					// ORDER BY (SELECT DISPLAY_ORDER FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)";
			
				$resultTopicButton = sqlsrv_query($mysqli, $queryTopicButton);
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


				// ############################
				// MEDIA CONTENT
				// ############################
				$queryPlanMaterial = "SELECT MATID,PDMID,(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PM.PDMID)PRODUCT,MATTYPE,
				CONVERT(VARCHAR,PUBDATE,106)PUBDATE,TITLE,MATIMG,BUYLINK,SEQNO
				FROM PRODUCT_MATERIAL_DISPLAY PM WHERE ISDELETED=0 AND PDMID=$PDMID AND PLANID=$PLANID ORDER BY SEQNO";
				$cntMaterial = unique($queryPlanMaterial);
				$data['queryPlanMaterial'] = $cntMaterial;
				$data['totalScrollBar'] = $indexMain+1;
				$demoid = $indexMain+1;
				if($cntMaterial>0){
					$grid .="<div class='als-container pb-3 mt-3' id='demo$demoid'>
								<span class='als-prev'><img src='js/scrollbar/thin_left_arrow_333.png' alt='prev' title='previous' /></span>
								<div class='als-viewport'>
								<div class='als-wrapper '>";


					$resultPMaterial = sqlsrv_query($mysqli, $queryPlanMaterial);
					$index = 0;
					while ($rowPMaterial = sqlsrv_fetch_array($resultPMaterial,SQLSRV_FETCH_ASSOC)) {
						$MATTYPE = $rowPMaterial['MATTYPE'];
						$MATIMG = $rowPMaterial['MATIMG'];
						// $OBJECTNAME = $rowPMaterial['OBJECTNAME'];
						$TITLE = $rowPMaterial['TITLE'];
						$PUBDATE = $rowPMaterial['PUBDATE'];
						$BUYLINK = !$rowPMaterial['BUYLINK'] ? '#' : $rowPMaterial['BUYLINK'];

						$grid .="<div class='als-item'>
									<div class='card border-0 shadow-sm  position-relative' style='width: 18rem;'>";
						if($MATIMG && $MATTYPE!=='VIDEO' && $MATTYPE!=='E-BOOK'){
							$grid.="<img src='backoffice/images/product_materials/$MATIMG' class='card-img-top' alt='Image$index'>";
						}
						if($MATTYPE == 'VIDEO'){
							$grid.="<video src='backoffice/images/product_materials/$MATIMG' class='card-img-top mb-0' alt='Image$index' controls></video>";
						}
						if($MATTYPE == 'E-BOOK'){
							$grid.="<embed ng-src='backoffice/images/product_materials/$MATIMG' type='application/pdf' class='card-img-top custom-scrollbar mb-0'/> ";
							$grid.="<a href='backoffice/images/product_materials/$MATIMG' target='_blank'
										class='btn btn-warning position-absolute' title='Open Full Screen' style='background: #ffc107ad;top: 34%;left: 78%;'>
										<i class='fa fa-arrows-alt text-dark' aria-hidden='true'></i>
									</a>
									";
						}
						$grid.="<div class='card-body p-2 border-top'>
									<h5 class='card-title mb-2'>$TITLE</h5>
									<h4 class='border-bottom mb-1 pb-1'>
                                        <i class='fa fa-calendar' style='color: #ba77e9;' aria-hidden='true'></i>
                                        <span>$PUBDATE</span>
                                    </h4>
								</div>
								<div class='card-footer bg-white py-2 border-top border-light'>
									<a href='$BUYLINK' target='_blank' class='btn btn-light border btn-block font-17 font-weight-bold rounded'><i class='fa fa-external-link-square' aria-hidden='true'></i> More Info/Buy</a>
								</div>";
						$grid .="</div></div>";
						
						$index++;
					}
					$grid.="<div class='als-item'></div>";

					$grid .="</div></div>
					<span class='als-next'><img src='js/scrollbar/thin_right_arrow_333.png' alt='next' title='next' /></span>
					</div>";
				}



				$grid .='</div>';

				$indexMain++;
			}
			$data['PlanDetail'] = $grid;
			// $data['query'] = $query;
			// $data['GetDetailsquery'] = $GetDetailsquery;
			$data['success'] = true;

		}else{
			$data['PlanDetail'] = $gridError;
			$data['success'] = true;
		}

		// GET PRODUCT REVIEWS
		$queryReview = "SELECT REVID,CONVERT(VARCHAR,REVDATE,106)REVDATE,PDMID,
		(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PR.PDMID)PRODUCT,REVIEWER,REVIEW 
		FROM PRODUCT_REVIEWS PR WHERE ISDELETED=0 AND PDMID=$PDMID ORDER BY CONVERT(DATE,REVDATE,105) DESC";
		$cntReview = unique($queryReview);
		if($cntReview>0){
			$resultReview = sqlsrv_query($mysqli, $queryReview);
			while ($rowReview = sqlsrv_fetch_array($resultReview,SQLSRV_FETCH_ASSOC)) {
				$data['productReviews'][] = $rowReview;
			}
			$data['successReview'] = true;
		}else{
			$data['successReview'] = false;
		}

		// GET PRODUCT MATERIAL
		$queryMaterial = "SELECT MATID,PDMID,(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PM.PDMID)PRODUCT,MATTYPE,
		CONVERT(VARCHAR,PUBDATE,106)PUBDATE,TITLE,MATIMG,BUYLINK
		FROM PRODUCT_MATERIAL_DISPLAY PM WHERE ISDELETED=0 AND PDMID=$PDMID ORDER BY CONVERT(DATE,PUBDATE,105) DESC";
		$cntMaterial = unique($queryMaterial);
		if($cntMaterial>0){
			$resultMaterial = sqlsrv_query($mysqli, $queryMaterial);
			while ($rowMaterial = sqlsrv_fetch_array($resultMaterial,SQLSRV_FETCH_ASSOC)) {
				$data['productMaterials'][] = $rowMaterial;
			}
			$data['successMaterial'] = true;
		}else{
			$data['successMaterial'] = false;
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


/*============ Get Display Plan All By Location =============*/ 
function DisplayPlanAll($mysqli){
	try
	{
		$data = array();
		$LOCID = ($_POST['PDMID'] == 'undefined' || $_POST['PDMID'] == '') ? 0 : $_POST['PDMID'];
		$grid='';
		$gridError ='<div class=" card card-body rounded-my my-2" ><h4 class="text-center text-danger">Data Not Found.</h4></div>';
		if($LOCID > 0){
			$queryPro = "SELECT PDMID,DISPLAY_PRODUCT, 
			(SELECT (SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0) +', ' FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=PM.PDMID AND ISDELETED=0 ORDER BY SEQNO FOR XML PATH('')) PRODUCTS,[ORDER],COLORCODE,'P'PTYPE
			FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0 ORDER BY [ORDER]";
			$cntPro = unique($queryPro);
			$PRODUCTS_ARR = array();
			if($cntPro>0){
				$resultPro = sqlsrv_query($mysqli, $queryPro);
				while ($rowPro = sqlsrv_fetch_array($resultPro,SQLSRV_FETCH_ASSOC)) {
					$PRODUCTS_ARR[] = $rowPro;
				}
			}else{
				throw new Exception('Error : Products Not Found.');
			}
			if(count($PRODUCTS_ARR)<=0) throw new Exception('Error : Products Not Found.');

			foreach($PRODUCTS_ARR as $records){
				$PDMID = $records['PDMID'];
				$DISPLAY_PRODUCT = $records['DISPLAY_PRODUCT'];
				$COLORCODE = $records['COLORCODE'];

				

				$query = "SELECT PLANID,
				(SELECT DISPLAYCOLOR FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)DISPLAYCOLOR,
				(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)[PLAN],
				(SELECT COUNT(*) FROM PLAN_LOCATIONS WHERE ISDELETED=0 AND PLANID=PD.PLANID AND LOCATIONID=$LOCID)DATA_EXIST
				FROM PRODUCT_DISPLAY_DETAIL PD 
				WHERE PLANID>0 AND PDMID=$PDMID AND ISDELETED=0 AND PLANID IN(SELECT PLANID FROM PLANS WHERE ISDELETED=0
				AND CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,DISPLAYFROMDATE,105) AND CONVERT(DATE,DISPLAYTODATE,105)) 
				ORDER BY SEQNO";
				// ORDER BY (SELECT [ORDER] FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PD.PDMID)";
	
				$result = sqlsrv_query($mysqli, $query);
				$INDEX = 0;
				while ($row = sqlsrv_fetch_array($result)) {
					$row['PLANID'] = (int) $row['PLANID'];
					$data['PLANID'] = (int) $row['PLANID'];
					$PLANID= (int) $row['PLANID'];
					$DATA_EXIST= (int) $row['DATA_EXIST'];
					$data['data'][] = $row;
					
					if($DATA_EXIST>0){
						if($INDEX==0) $grid .="<h2 style='color:$COLORCODE!important;'>$DISPLAY_PRODUCT</h2>";

						$grid .='<div class=" card card-body rounded-my my-2" >
								<h4 class="font-weight-bold border-bottom pb-2" style="color:'.$row['DISPLAYCOLOR'].'!important;">'.$row['PLAN'].'</h4>
		
								<div class="table-responsive card-body w-100 pb-0">
								<table class="table table-borderless border-0 table-hover bg-white table-sm">
																		
								<tr>
									<th></th>
									<th>Location</th>
									<th class="text-nowrap">Course Start-End</th>
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
								FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 AND PS.LOCID=PL.LOCATIONID
								-- ORDER BY (select dbo.GetWeekDay(PS.WEEKDAYNAME)) 
								FOR XML PATH('')) SCHEDULE,
							
							
								(SELECT CASE WHEN PS.REMARKS='' OR PS.REMARKS IS NULL THEN '' ELSE PS.REMARKS + ' | ' END 
								--PS.REMARKS + ' | '
								FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 AND PS.LOCID=PL.LOCATIONID 
								FOR XML PATH('')) REMARK
								FROM PLAN_LOCATIONS PL WHERE PLANID=$PLANID AND ISDELETED=0 and LOCATIONID=$LOCID";
								// $data['$GetDetailsquery1'][] = $GetDetailsquery;
		
								$DETAILS_COUNT = unique($GetDetailsquery);
								$GetDetailsresult = sqlsrv_query($mysqli, $GetDetailsquery);
		
								while ($getDetailsrow = sqlsrv_fetch_array($GetDetailsresult)) {
									$COST = (int) $getDetailsrow['COST'];
									$getDetailsrow['COST'] = (float) $getDetailsrow['COST'];
									$getDetailsrow['COST'] = number_format($getDetailsrow['COST']);
									$getDetailsrow['INSTALLMENT'] = (float) $getDetailsrow['INSTALLMENT'];
									$getDetailsrow['INSTALLMENT'] = number_format($getDetailsrow['INSTALLMENT']);
									$LOCATIONID = (int) $getDetailsrow['LOCATIONID'];

									$getDetailsrow['SCHEDULE'] = rtrim($getDetailsrow['SCHEDULE'], " | ");
									$getDetailsrow['SCHEDULE'] = preg_replace('/(\d{1,2}:\d{2})(AM|PM)/', '$1 $2', $getDetailsrow['SCHEDULE']);
						
									$getDetailsrow['SCHEDULE'] = str_replace(":00","",$getDetailsrow['SCHEDULE']) ;
		
									$getDetailsrow['SCHEDULE'] = rtrim($getDetailsrow['SCHEDULE'], " | ");
									$getDetailsrow['REMARK'] = rtrim($getDetailsrow['REMARK'], " | ");

									$CURR_SYMBOL = isset($getDetailsrow['CURRENCY_SYMBOL']) ? $getDetailsrow['CURRENCY_SYMBOL'] : '';
									$CURR_CODE = isset($getDetailsrow['CURRENCY_CODE']) ? $getDetailsrow['CURRENCY_CODE'] : '';
									// $row['LOCATIONID'] = (int) $row['LOCATIONID'];
									// $data['data'][] = $row;
		
									
									$grid .="<tr title='$LOCATIONID'>
												<td class='text-nowrap' style='width:8%;'>
													<button class='btn btn-light' style='color:#000000!important' ng-click='EnrollStudent($LOCATIONID,$PLANID)' >Enroll</button>
												</td>
												<td class='text-nowrap'>
													<i class='flag-icon flag-icon-".$getDetailsrow['CFLAG']."'></i>
													".$getDetailsrow['LOC']."
												</td>
												<td class-text-nowrap>".$getDetailsrow['START_DATE']."</br>".$getDetailsrow['END_DATE']."</td>
												
												<td class='text-nowrap'>
													<span ng-hide='$COST<=0'>Lumpsum &nbsp; : <b>".($CURR_SYMBOL==''? $CURR_CODE : '')."</b><i ng-if='\"$CURR_SYMBOL\"!=\"\"' class='fa fa-$CURR_SYMBOL'></i>
														".$getDetailsrow['COST']."</span> <br>

													<span data-ng-show='".$getDetailsrow['INSTALLMENT'].">0'> Installment : <b>".($CURR_SYMBOL==''? $CURR_CODE : '')."</b><i ng-if='\"$CURR_SYMBOL\"!=\"\"' class='fa fa-".$CURR_SYMBOL."'></i> ".$getDetailsrow['INSTALLMENT']." 
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
							ORDER BY PRODUCT";
							// ORDER BY (SELECT DISPLAY_ORDER FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID AND ISDELETED=0)";
					
						$resultTopicButton = sqlsrv_query($mysqli, $queryTopicButton);
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
						$INDEX++;
					}
	
					
				}
				$data['PlanDetail'] = $grid;
				// $data['query'] = $query;
				// $data['GetDetailsquery'] = $GetDetailsquery;
				$data['success'] = true;
			}

		}else{
			$data['PlanDetail'] = $gridError;
			$data['success'] = true;
		}

		// GET PRODUCT REVIEWS
		$queryReview = "SELECT REVID,CONVERT(VARCHAR,REVDATE,106)REVDATE,PDMID,
		(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PR.PDMID)PRODUCT,REVIEWER,REVIEW 
		FROM PRODUCT_REVIEWS PR WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY CONVERT(DATE,REVDATE,105) DESC";
		$cntReview = unique($queryReview);
		if($cntReview>0){
			$resultReview = sqlsrv_query($mysqli, $queryReview);
			while ($rowReview = sqlsrv_fetch_array($resultReview,SQLSRV_FETCH_ASSOC)) {
				$data['productReviews'][] = $rowReview;
			}
			$data['successReview'] = true;
		}else{
			$data['successReview'] = false;
		}

		// GET PRODUCT MATERIAL
		$queryMaterial = "SELECT MATID,PDMID,(SELECT DISPLAY_PRODUCT FROM PRODUCT_DISPLAY_MASTER WHERE PDMID=PM.PDMID)PRODUCT,MATTYPE,
		CONVERT(VARCHAR,PUBDATE,106)PUBDATE,TITLE,MATIMG,BUYLINK
		FROM PRODUCT_MATERIAL_DISPLAY PM WHERE ISDELETED=0 AND LOCID=$LOCID ORDER BY CONVERT(DATE,PUBDATE,105) DESC";
		$cntMaterial = unique($queryMaterial);
		if($cntMaterial>0){
			$resultMaterial = sqlsrv_query($mysqli, $queryMaterial);
			while ($rowMaterial = sqlsrv_fetch_array($resultMaterial,SQLSRV_FETCH_ASSOC)) {
				$data['productMaterials'][] = $rowMaterial;
			}
			$data['successMaterial'] = true;
		}else{
			$data['successMaterial'] = false;
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






