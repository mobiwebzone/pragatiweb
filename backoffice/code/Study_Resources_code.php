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
        case "save":save($conn);break;
        case "getStudyResource":getStudyResource($conn);break;
        case "delete":delete($conn);break;
        
		case "saveRL":saveRL($conn);break;
        case "getResourceLink":getResourceLink($conn);break;
        case "deleteRL":deleteRL($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $resid  = ($_POST['resid'] == 'undefined' || $_POST['resid'] == '') ? 0 : $_POST['resid'];
		$txtResourceID = $_POST['txtResourceID'] == 'undefined' ? '' : $_POST['txtResourceID'];
		$txtResourceDesc = $_POST['txtResourceDesc'] == 'undefined' ? '' : $_POST['txtResourceDesc'];
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		$txtResSeqNo = ($_POST['txtResSeqNo'] == 'undefined' || $_POST['txtResSeqNo'] == '') ? 0 : $_POST['txtResSeqNo'];
		
		$actionid = $resid == 0 ? 1 : 2;

		if($txtResourceID == '')
		{throw new Exception("Please Enter ResourceID.");}
		if($txtResourceDesc == '')
		{throw new Exception("Please Enter Resource Desc.");}
		if($ddlProduct == 0)
		{throw new Exception("Please Select Product.");}

		$sql = "SELECT * FROM TOPIC_RESOURCES WHERE RESOURCEID='$txtResourceID' AND RES_SEQ=$txtResSeqNo AND RESID!=$resid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [TOPIC_RESOURCES_SP] $actionid,$resid,$ddlProduct,'$txtResourceID','$txtResourceDesc',$txtResSeqNo,$userid";
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

				$row = sqlsrv_fetch_array($stmt);
				$row['RESID'] = (int) $row['RESID'];
				$data['GETRESID'] = $row['RESID'];
				

				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($resid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Resource ID / SEQ No. already exists.';
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


/*============ Get Study Resources =============*/ 
 function getStudyResource($mysqli){
	try
	{
		$query = "SELECT RESID,PRODUCT_ID,
		ISNULL((SELECT PRODUCT FROM PRODUCTS WHERE PRODUCT_ID=TR.PRODUCT_ID),'')PRODUCT,
		RESOURCEID,RESOURCE_DESC,RES_SEQ FROM TOPIC_RESOURCES TR WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['RESID'] = (int) $row['RESID'];
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
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $resid = ($_POST['resid'] == 'undefined' || $_POST['resid'] == '') ? 0 : $_POST['resid'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [TOPIC_RESOURCES_SP] 3,$resid,0,'','',0,$userid");
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



// ============================================== RESOURCE LINK ===========================================
function saveRL($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $resid  = ($_POST['resid'] == 'undefined' || $_POST['resid'] == '') ? 0 : $_POST['resid'];
	   $txtResourceLink = $_POST['txtResourceLink'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtResourceLink']);
	   $txtSeqNo = ($_POST['txtSeqNo'] == 'undefined' || $_POST['txtSeqNo'] == '') ? 0 : $_POST['txtSeqNo'];


	   if($resid == 0)
	   {throw new Exception("RESID ERROR.");}
	   if($txtResourceLink == '')
	   {throw new Exception("Please Enter Resource Link.");}
	   if($txtSeqNo == 0)
	   {throw new Exception("Please Enter SEQ No.");}

	   $sql = "SELECT * FROM TOPIC_RESOURCES_LINKS WHERE RESID=$resid AND SEQNO=$txtSeqNo AND ISDELETED=0";
	   $data['sql']=$sql;
	   $row_count = unique($sql);

	   if($row_count <= 0)
	   {
		   $query="EXEC [TOPIC_RESOURCES_LINKS_SP] 1,0,$resid,'$txtResourceLink',$txtSeqNo,$userid";
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
			   $data['message'] = 'Resource Link successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['message'] = 'SEQ NO. already exists.';
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



/*============ Get Resources Link =============*/ 
function getResourceLink($mysqli){
	try
	{
		$RESID = ($_POST['RESID'] == 'undefined' || $_POST['RESID'] == '') ? 0 : $_POST['RESID'];
		$query = "SELECT RESLID,RESOURCE_LINK,SEQNO FROM TOPIC_RESOURCES_LINKS WHERE ISDELETED=0 AND RESID=$RESID";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['RESLID'] = (int) $row['RESLID'];
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
function deleteRL($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RESLID = ($_POST['RESLID'] == 'undefined' || $_POST['RESLID'] == '') ? 0 : $_POST['RESLID'];  
			$stmt=sqlsrv_query($mysqli, "EXEC [TOPIC_RESOURCES_LINKS_SP] 3,$RESLID,0,'',0,$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Resource Link successfully deleted.';
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







