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
        case "savePlan":savePlan($conn);break;
        case "saveProducts":saveProducts($conn);break;
        case "saveLocation":saveLocation($conn);break;
        case "saveSchedule":saveSchedule($conn);break;

        case "getProduct":getProduct($conn);break;
        case "getPlans":getPlans($conn);break;
        case "getPlanProducts":getPlanProducts($conn);break;
        case "getLocation":getLocation($conn);break;
        case "getPlanLocation":getPlanLocation($conn);break;
        case "getShedule":getShedule($conn);break;

        case "deletePlans":deletePlans($conn);break;
        case "deletePlanProduct":deletePlanProduct($conn);break;
        case "deletePlanLocation":deletePlanLocation($conn);break;
        case "deletePlanSchedule":deletePlanSchedule($conn);break;

        case "copyPlans":copyPlans($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 



/*============ SAVE PLANS =============*/ 
 function savePlan($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $planid  = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
        $ISCombo  = ($_POST['ISCombo'] == 'undefined' || $_POST['ISCombo'] == '') ? 1 : $_POST['ISCombo'];
        $txtPlan  = $_POST['txtPlan'] == 'undefined' ? '' : $_POST['txtPlan'];
        $txtStartDT  = $_POST['txtStartDT'] == 'undefined' ? '' : $_POST['txtStartDT'];
        $txtEndDT  = $_POST['txtEndDT'] == 'undefined' ? '' : $_POST['txtEndDT'];
        $txtPrice  = ($_POST['txtPrice'] == 'undefined' || $_POST['txtPrice'] == '') ? 0 : $_POST['txtPrice'];
        $txtPriceInstall  = ($_POST['txtPriceInstall'] == 'undefined' || $_POST['txtPriceInstall'] == '') ? 0 : $_POST['txtPriceInstall'];
        $ddlFrequency  = $_POST['ddlFrequency'] == 'undefined' ? '' : $_POST['ddlFrequency'];
        $txtNO_Install  = ($_POST['txtNO_Install'] == 'undefined' || $_POST['txtNO_Install'] == '') ? 0 : $_POST['txtNO_Install'];
        $txtDisplayFrom  = $_POST['txtDisplayFrom'] == 'undefined' ? '' : $_POST['txtDisplayFrom'];
        $txtDisplayTo  = $_POST['txtDisplayTo'] == 'undefined' ? '' : $_POST['txtDisplayTo'];
        $txtDisplayColor  = $_POST['txtDisplayColor'] == 'undefined' ? '' : $_POST['txtDisplayColor'];
		$chkActive = $_POST['chkActive'] === '0' ? 0 : 1;
		
		$actionid = $planid == 0 ? 1 : 2;

		if($txtPlan == '')
		{throw new Exception("Enter Plan Name.");}

		$sql = "SELECT * FROM PLANS WHERE PLANNAME='$txtPlan' AND PLANID!=$planid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [PLANS_SP] $actionid,$planid,1,'$txtPlan','$txtStartDT','$txtEndDT',$txtPrice,$txtPriceInstall,'$ddlFrequency',$txtNO_Install,'$txtDisplayFrom','$txtDisplayTo','$txtDisplayColor',$chkActive,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$row = sqlsrv_fetch_array($stmt);
				$row['PLANID'] = (int) $row['PLANID'];
				$data['PLANID'] = $row['PLANID'];

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($planid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
			echo json_encode($data);exit;
		}

     }
     catch(Exception $e)
     {
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
     }
 }
 





/*============ Get getPlans =============*/ 
function getPlans($mysqli){
	try
	{
		$query = "SELECT PLANID,PLANNAME,convert(varchar, STARTDATE, 106)STARTDATE,
		convert(varchar, ENDDATE, 106)ENDDATE,PRICE,INST_AMOUNT,INST_FREQ,INST_NO,
		convert(varchar, DISPLAYFROMDATE, 106)DISPLAYFROMDATE,
		convert(varchar, DISPLAYTODATE, 106)DISPLAYTODATE,DISPLAYCOLOR,ACTIVE
		FROM PLANS WHERE ISDELETED=0 ORDER BY PLANID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PLANID'] = (int) $row['PLANID'];
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

/*============ Get getProduct =============*/ 
function getProduct($mysqli){
	try
	{
		$query = "SELECT PRODUCT_ID,PRODUCT FROM PRODUCTS WHERE ISDELETED=0";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PRODUCT_ID'] = (int) $row['PRODUCT_ID'];
			$data['data'][] = $row;
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

/* =========== Delete =========== */ 
function deletePlans($mysqli){
	try{   
			global $userid;
			$data = array();     
			$planid = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [PLANS_SP] 3,$planid,0,'','','',0,0,'',0,'','','',0,$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted';
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













 /*================================================== Products Start ==============================================*/ 
 function saveProducts($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $planpid  = ($_POST['planpid'] == 'undefined' || $_POST['planpid'] == '') ? 0 : $_POST['planpid'];
	   $planid  = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
	   $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
	   $txtProductPrice  = $_POST['txtProductPrice'] == 'undefined' ? '' : $_POST['txtProductPrice'];
	   
	   $actionid = $planpid == 0 ? 1 : 2;


	   if($planid == 0)
	   {throw new Exception("Somthing went wroung.");}

	   $sql = "SELECT * FROM PLAN_PRODUCTS WHERE PLANID=$planid AND PRODUCTID=$ddlProduct AND PLANPID!=$planpid AND ISDELETED=0";
	   $row_count = unique($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   $query="EXEC [PLAN_PRODUCTS_SP] $actionid,$planpid,$planid,$ddlProduct,$txtProductPrice,$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = true;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($planpid))$data['message'] = 'Record successfully updated';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'Record already exists';
		   echo json_encode($data);exit;
	   }

	}
	catch(Exception $e)
	{
	   $data = array();
	   $data['success'] = false;
	   $data['message'] = $e->getMessage();
	   echo json_encode($data);
	   exit;
	}
}



/*============ Get getPlanProducts =============*/ 
function getPlanProducts($mysqli){
	try
	{
		$planid = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];

		$query = "SELECT PLANPID,PLANID,PRODUCTID,
		(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID)PRODUCT,
		(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=PP.PRODUCTID)PRODUCT_DESC,PRICE
		FROM PLAN_PRODUCTS PP WHERE ISDELETED=0 AND PLANID=$planid";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['PLANPID'] = (int) $row['PLANPID'];
				$row['PLANID'] = (int) $row['PLANID'];
				$row['PRODUCTID'] = (int) $row['PRODUCTID'];
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


/* =========== Delete Product =========== */ 
function deletePlanProduct($mysqli){
	try{   
			global $userid;
			$data = array();     
			$planpid = ($_POST['planpid'] == 'undefined' || $_POST['planpid'] == '') ? 0 : $_POST['planpid'];  

			$stmt=sqlsrv_query($mysqli, "EXEC [PLAN_PRODUCTS_SP] 3,$planpid,0,0,0,$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted';
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



 /*================================================== Products End ==============================================*/ 











/*================================================== Location Start ==============================================*/ 
function saveLocation($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $planlid  = ($_POST['planlid'] == 'undefined' || $_POST['planlid'] == '') ? 0 : $_POST['planlid'];
	   $planid  = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
	   $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	   
	   $actionid = $planlid == 0 ? 1 : 2;

	   if($planid == 0)
	   {throw new Exception("Somthing went wroung.");}

	   $sql = "SELECT * FROM PLAN_LOCATIONS WHERE PLANID=$planid AND LOCATIONID=$ddlLocation AND PLANLID!=$planlid AND ISDELETED=0";
	   $row_count = unique($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   $query="EXEC [PLAN_LOCATIONS_SP] $actionid,$planlid,$planid,$ddlLocation,$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = true;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
				// GET PLANLID
				$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				$data['GET_PLANLID'] = (int) $row['PLANLID'];

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($planlid))$data['message'] = 'Record successfully updated';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'Record already exists';
		   echo json_encode($data);exit;
	   }

	}
	catch(Exception $e)
	{
	   $data = array();
	   $data['success'] = false;
	   $data['message'] = $e->getMessage();
	   echo json_encode($data);
	   exit;
	}
}



 /*============ Get Location =============*/ 
function getLocation($mysqli){
	try
	{
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
			$data['data'][] = $row;
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



/*============ Get Plan Location =============*/ 
function getPlanLocation($mysqli){
	try
	{
		$planid = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];

		$query = "SELECT PLANLID,PLANID,LOCATIONID,
		(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID)LOCATION
		 FROM PLAN_LOCATIONS PL WHERE ISDELETED=0 AND PLANID=$planid";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PLANLID'] = (int) $row['PLANLID'];
			$data['data'][] = $row;
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

/* =========== Delete Location =========== */ 
function deletePlanLocation($mysqli){
	try{   
			global $userid;
			$data = array();     
			$planlid = ($_POST['planlid'] == 'undefined' || $_POST['planlid'] == '') ? 0 : $_POST['planlid'];  

			$stmt=sqlsrv_query($mysqli, "EXEC [PLAN_LOCATIONS_SP] 3,$planlid,0,0,$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted';
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

/*================================================== Location End ==============================================*/ 










/*================================================== Schedule Start ==============================================*/ 
function saveSchedule($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $plansid  = ($_POST['plansid'] == 'undefined' || $_POST['plansid'] == '') ? 0 : $_POST['plansid'];
	   $planid  = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
	   $locid  = ($_POST['locid'] == 'undefined' || $_POST['locid'] == '') ? 0 : $_POST['locid'];
	   $ddlDay  = $_POST['ddlDay'] == 'undefined' ? '' : $_POST['ddlDay'];
	   $txtFromTime  = $_POST['txtFromTime'] == 'undefined' ? '' : $_POST['txtFromTime'];
	   $txtToTime  = $_POST['txtToTime'] == 'undefined' ? '' : $_POST['txtToTime'];
	   $txtFromDate  = $_POST['txtFromDate'] == 'undefined' ? '' : $_POST['txtFromDate'];
	   $txtToDate  = $_POST['txtToDate'] == 'undefined' ? '' : $_POST['txtToDate'];
	   $txtRemark  = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
	   
	   $actionid = $plansid == 0 ? 1 : 2;

	   if($planid == 0) throw new Exception("Error : Planid Not Found.");
	   if($locid == 0) throw new Exception("Error : Locid Not Found.");

	//    $sql = "SELECT * FROM PLAN_SCHEDULE WHERE PLANID=$planid AND WEEKDAYNAME='$ddlDay' AND PLANSID!=$plansid AND ISDELETED=0";
	//    $row_count = unique($sql);

	//    $data = array();
	//    if($row_count == 0)
	//    {
		   $query="EXEC [PLAN_SCHEDULE_SP] $actionid,$plansid,$planid,$locid,'$ddlDay','$txtFromDate','$txtToDate','$txtFromTime','$txtToTime','','$txtRemark',$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = true;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {

			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($plansid))$data['message'] = 'Record successfully updated';
			   else $data['message'] = 'Record successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	//    }
	//    else
	//    {
	// 	   $data['success'] = false;
	// 	   $data['message'] = 'Record already exists';
	// 	   echo json_encode($data);exit;
	//    }

	}
	catch(Exception $e)
	{
	   $data = array();
	   $data['success'] = false;
	   $data['message'] = $e->getMessage();
	   echo json_encode($data);
	   exit;
	}
}

 /*============ Get Schedule =============*/ 
 function getShedule($mysqli){
	try
	{
		$data = array();
		$planid = ($_POST['planid'] == 'undefined' || $_POST['planid'] == '') ? 0 : $_POST['planid'];
		$locid = ($_POST['locid'] == 'undefined' || $_POST['locid'] == '') ? 0 : $_POST['locid'];

		$query = "SELECT PLANSID,PLANID,LOCID,WEEKDAYNAME,
		CONVERT(VARCHAR,FROMDATE,107)FROMDATE,CONVERT(VARCHAR,FROMDATE,105)FROMDATE_S,
		CONVERT(VARCHAR,TODATE,107)TODATE,CONVERT(VARCHAR,TODATE,105)TODATE_S,
		CONVERT(VARCHAR,TIMEFROM_ET,8)TIMEFROM_ET,
		CONVERT(VARCHAR,TIMETO_ET,8)TIMETO_ET,REMARKS
		FROM PLAN_SCHEDULE WHERE ISDELETED=0 AND PLANID=$planid AND LOCID=$locid";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PLANSID'] = (int) $row['PLANSID'];
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

/* =========== Delete schedule =========== */ 
function deletePlanSchedule($mysqli){
	try{   
			global $userid;
			$data = array();     
			$plansid = ($_POST['plansid'] == 'undefined' || $_POST['plansid'] == '') ? 0 : $_POST['plansid'];  

			$stmt=sqlsrv_query($mysqli, "EXEC [PLAN_SCHEDULE_SP] 3,$plansid,0,0,'','','','','','','',$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted';
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

/*================================================== Schedule End ==============================================*/ 







/*============ COPY PLANS =============*/ 
function copyPlans($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $PLANID  = ($_POST['PLANID'] == 'undefined' || $_POST['PLANID'] == '') ? 0 : $_POST['PLANID'];
	   if($PLANID == 0)throw new Exception("Error : PLANID Not Found.");

		$query="EXEC [PLANS_COPY_SP] $PLANID,$userid";
		$data['query'] = $query;
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['message'] = 'Copy Plan Failed.';
			echo json_encode($data);exit;
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Plan successfully copied.';
			echo json_encode($data);exit;
		}

	}
	catch(Exception $e)
	{
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







