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
		case "login":login($conn);break;
        case "saveProduct":saveProduct($conn);break;
        case "saveLocation":saveLocation($conn);break;
        case "getTeacherProduct":getTeacherProduct($conn);break;
        case "getTeacherLocation":getTeacherLocation($conn);break;
        case "getTeacher":getTeacher($conn);break;
		case "getTeacher_Volunteer":getTeacher_Volunteer($conn);break;
        case "getProduct":getProduct($conn);break;
        case "deleteProduct":deleteProduct($conn);break;
        case "deleteLocation":deleteLocation($conn);break;
		
        case "savePlan":savePlan($conn);break;
		case "getTeacherPlans":getTeacherPlans($conn);break;
        case "deletePlan":deletePlan($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

// ========= SAVE TEACHER PRODUCT ==========
 function saveProduct($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $tpid  = ($_POST['tpid'] == 'undefined' || $_POST['tpid'] == '') ? 0 : $_POST['tpid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $ddlProduct  = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		
		$actionid = $tpid == 0 ? 1 : 2;

		if($ddlLocation == 0)
		{throw new Exception("Please Select Location.");}
		if($ddlTeacher == 0)
		{throw new Exception("Please Select Teacher.");}
		if($ddlProduct == 0)
		{throw new Exception("Please Select Product.");}

		$sql = "SELECT * FROM TEACHER_PRODUCT WHERE LOCID=$ddlLocation AND TEACHERID=$ddlTeacher AND PRODUCTID=$ddlProduct AND TPID!=$tpid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TEACHER_PRODUCT_SP] $actionid,$tpid,$ddlLocation,$ddlTeacher,$ddlProduct,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($tpid))$data['message'] = 'PRODUCT successfully updated';
				else $data['message'] = 'PRODUCT successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'PRODUCT already exists';
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


 // ========= SAVE TEACHER LOCATION ==========
 function saveLocation($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $tlid  = ($_POST['tlid'] == 'undefined' || $_POST['tlid'] == '') ? 0 : $_POST['tlid'];
        $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
        $ddlTeacherLocation  = ($_POST['ddlTeacherLocation'] == 'undefined' || $_POST['ddlTeacherLocation'] == '') ? 0 : $_POST['ddlTeacherLocation'];
		
		$actionid = $tlid == 0 ? 1 : 2;

		
		if($ddlTeacher == 0)
		{throw new Exception("Please Select Teacher.");}
		if($ddlTeacherLocation == 0)
		{throw new Exception("Please Select Teacher Location.");}

		$sql = "SELECT * FROM TEACHER_LOCATION WHERE TEACHERID=$ddlTeacher AND LOCID=$ddlTeacherLocation AND TLID!=$tlid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TEACHER_LOCATION_SP] $actionid,$tlid,$ddlTeacher,$ddlTeacherLocation,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($tlid))$data['message'] = 'LOCATION successfully updated';
				else $data['message'] = 'LOCATION successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'LOCATION already exists';
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


/*============ Get TeacherProduct =============*/ 
 function getTeacherProduct($mysqli){
	try
	{
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];

		$query = "SELECT TPID,LOCID,TEACHERID,
			(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TP.TEACHERID)FULLNAME,
			PRODUCTID,
			(SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=TP.PRODUCTID)PRODUCT
			FROM TEACHER_PRODUCT TP WHERE ISDELETED=0 AND LOCID=$ddlLocation";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TPID'] = (int) $row['TPID'];
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


/*============ Get Teacher Location =============*/ 
 function getTeacherLocation($mysqli){
	try
	{
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
		
		$query = "SELECT TLID,TEACHERID,
				(SELECT FIRSTNAME+' '+LASTNAME FROM USERS WHERE UID=TL.TEACHERID)FULLNAME,
				LOCID,
				(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=TL.LOCID)LOCATION
				FROM TEACHER_LOCATION TL WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TLID'] = (int) $row['TLID'];
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


/*============ Get Teacher =============*/ 
 function getTeacher($mysqli){
	try
	{
		$data = array();
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT UID,FIRSTNAME+' '+LASTNAME+' — '+' ('+USERROLE+')' AS FULLNAME,USERROLE FROM USERS WHERE LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0 ORDER BY USERROLE";
		
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
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

/*============ Get Teacher =============*/ 
 function getTeacher_Volunteer($mysqli){
	try
	{
		
		$data = array();
		$ddlLocation=($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT distinct USERROLE  FROM USERS WHERE LOCID=$ddlLocation AND USERROLE IN ('TEACHER','VOLUNTEER') AND ISDELETED=0 ORDER BY USERROLE";
		
		$data['$query']=$query;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
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
function deleteProduct($mysqli){
	try{   
			global $userid;
			$data = array();     
            $tpid = ($_POST['tpid'] == 'undefined' || $_POST['tpid'] == '') ? 0 : $_POST['tpid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [TEACHER_PRODUCT_SP] 3,$tpid,0,0,0,$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Product successfully deleted';
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

/* =========== Delete =========== */ 
function deleteLocation($mysqli){
	try{   
			global $userid;
			$data = array();     
            $tlid = ($_POST['tlid'] == 'undefined' || $_POST['tlid'] == '') ? 0 : $_POST['tlid'];  
			$delquery="EXEC [TEACHER_LOCATION_SP] 3,$tlid,0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delquery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Location successfully deleted';
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





// ============================================================ ADD PLAN =================================================

 // ========= SAVE TEACHER PLAN ==========
 function savePlan($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $tplid  = ($_POST['tplid'] == 'undefined' || $_POST['tplid'] == '') ? 0 : $_POST['tplid'];
	   $ddlTeacher  = ($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];
	   $ddlPlan  = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
	   
	   $actionid = $tplid == 0 ? 1 : 2;

	   
	   if($ddlTeacher == 0)
	   {throw new Exception("Please Select Teacher.");}
	   if($ddlPlan == 0)
	   {throw new Exception("Please Select Plan Name.");}

	   $sql = "SELECT * FROM TEACHER_PLAN WHERE TEACHERID=$ddlTeacher AND PLANID=$ddlPlan AND TPLID!=$tplid AND ISDELETED=0";
	   $row_count = unique($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   $query="EXEC [TEACHER_PLAN_SP] $actionid,$tplid,$ddlPlan,$ddlTeacher,$userid";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = true;
			   $data['query'] = $query;
		   }
		   else
		   {
			   $data['query'] = $query;
			   $data['success'] = true;
			   if(!empty($tplid))$data['message'] = 'PLAN successfully updated';
			   else $data['message'] = 'PLAN successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'PLAN already exists';
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


/*============ Get Teacher Plans =============*/ 
function getTeacherPlans($mysqli){
	try
	{
		$ddlTeacher=($_POST['ddlTeacher'] == 'undefined' || $_POST['ddlTeacher'] == '') ? 0 : $_POST['ddlTeacher'];

		if($ddlTeacher == 0){throw new Exception ('TeacherID Error.');}
		
		$query = "SELECT TPLID,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=TP.PLANID)PLANNAME,TEACHERID
		FROM TEACHER_PLAN TP WHERE ISDELETED=0 AND TEACHERID=$ddlTeacher ORDER BY PLANNAME ASC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TPLID'] = (int) $row['TPLID'];
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

/* =========== Delete Plan =========== */ 
function deletePlan($mysqli){
	try{   
			global $userid;
			$data = array();     
            $tplid = ($_POST['tplid'] == 'undefined' || $_POST['tplid'] == '') ? 0 : $_POST['tplid'];  
			if($tplid == 0){throw new Exception ('TPLID Error.');}

			$delquery="EXEC [TEACHER_PLAN_SP] 3,$tplid,0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delquery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Plan successfully deleted';
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







