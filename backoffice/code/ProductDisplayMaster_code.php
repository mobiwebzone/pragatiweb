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
        case "saveProductDisplay":saveProductDisplay($conn);break;
        case "updateSeqno":updateSeqno($conn);break;

        case "getPlans":getPlans($conn);break;
        case "getProductDisplay":getProductDisplay($conn);break;
        case "getPDMID_Plans":getPDMID_Plans($conn);break;
        case "getDetails":getDetails($conn);break;

        case "deleteProductDisplay":deleteProductDisplay($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 




 function saveProductDisplay($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $pdmid  = ($_POST['pdmid'] == 'undefined' || $_POST['pdmid'] == '') ? 0 : $_POST['pdmid'];
        $txtProductDisplay  = $_POST['txtProductDisplay'] == 'undefined' ? '' : $_POST['txtProductDisplay'];
        $ddlPlans = ($_POST['ddlPlans'] == 'undefined' || $_POST['ddlPlans'] == '') ? 0 : $_POST['ddlPlans'];
		$ddlPlans = explode(",",$ddlPlans);
		
		// $data['success'] = true;
		// $data['ddlPlans'] = $ddlPlans;
		// echo json_encode($data);exit;

        $txtProductOrder  = ($_POST['txtProductOrder'] == 'undefined' || $_POST['txtProductOrder'] == '') ? '' : $_POST['txtProductOrder'];
        $txtDisplayColor  = $_POST['txtDisplayColor'] == 'undefined' ? '' : $_POST['txtDisplayColor'];
        $isHeader  = ($_POST['isHeader'] == 'undefined' || $_POST['isHeader'] == '' || $_POST['isHeader'] == '0') ? 0 : 1;
		$txtHeader  = ($_POST['txtHeader'] == 'undefined' || $_POST['txtHeader'] == 'null') ? '' : str_replace("'","''",$_POST['txtHeader']);
		
		$actionid = $pdmid == 0 ? 1 : 2;

		if($ddlPlans == 0)
		{throw new Exception("Select Plan.");}

		$sql = "SELECT * FROM PRODUCT_DISPLAY_MASTER WHERE DISPLAY_PRODUCT='$txtProductDisplay' AND PDMID!=$pdmid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{

			$query="EXEC [PRODUCT_DISPLAY_MASTER_SP] $actionid,$pdmid,'$txtProductDisplay',$txtProductOrder,'$txtDisplayColor',$isHeader,'$txtHeader',$userid";
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
				$PDMID = (int) $row['PDMID'];
				$PDMID = $row['PDMID'];

				$delQuery ="DELETE FROM PRODUCT_DISPLAY_DETAIL WHERE PDMID=$pdmid";
				$stmtDel=sqlsrv_query($mysqli, $delQuery);

				for($P=0;$P<count($ddlPlans);$P++){
					$PLANS=$ddlPlans[$P];
					if($PLANS>0){
						$SEQNO = ($P+1);
						$query1="EXEC [PRODUCT_DISPLAY_DETAIL_SP] 1,0,$PDMID,$PLANS,$SEQNO,$userid";
						$stmt1=sqlsrv_query($mysqli, $query1);
					}
					
				}

					if($stmt1 === false)
					{
						// die( print_r( sqlsrv_errors(), true));
						// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
						$data['success'] = true;
						$data['query1'] = $query1;
						echo json_encode($data);exit;
					}
					else
					{
		
						$data['query1'] = $query1;
						$data['delete Query'] = $delQuery;
						$data['success'] = true;
						if(!empty($pdmid))$data['message'] = 'Record successfully updated';
						else $data['message'] = 'Record successfully inserted.';
						echo json_encode($data);exit;
					}
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




/*============ UPDATE DETAIL SEQUNCE =============*/ 
function updateSeqno($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $PDDID  = ($_POST['PDDID'] == 'undefined' || $_POST['PDDID'] == '') ? 0 : $_POST['PDDID'];
	   $SEQNO = ($_POST['SEQNO'] == 'undefined' || $_POST['SEQNO'] == '') ? 0 : $_POST['SEQNO'];
	   
	   if($PDDID==0) throw new Exception('Invalid PDDID.');
	   if($SEQNO==0) throw new Exception('Invalid SEQ No.');

		$query="UPDATE PRODUCT_DISPLAY_DETAIL SET SEQNO=$SEQNO WHERE PDDID=$PDDID";
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{

			$data['success'] = true;
			$data['message'] = 'Sequnce successfully updated.';
		}
		echo json_encode($data);exit;
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
		$data = array();
		$query = "SELECT PLANID AS id,PLANNAME AS label FROM PLANS WHERE ISDELETED=0 order by PLANNAME";
		$cnt = unique($query);
		if($cnt>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				// $row['PLANID'] = (int) $row['PLANID'];
				$data['data'][] = ['id'=>$row['id'],'label'=>$row['label']];
				// $data['data'][] = $row;
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

/*============ Get getProductDisplay =============*/ 
function getProductDisplay($mysqli){
	try
	{
		$query = "SELECT PM.PDMID,PM.DISPLAY_PRODUCT, (SELECT (SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0) +'#_#' FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=PM.PDMID AND ISDELETED=0 ORDER BY SEQNO FOR XML PATH('')) PRODUCTS
		,PM.PDMID,PM.[ORDER],PM.COLORCODE,PM.ISHEADER,PM.HEADER 
		FROM PRODUCT_DISPLAY_MASTER PM WHERE ISDELETED=0 ORDER BY [ORDER]";
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['PDMID'] = (int) $row['PDMID'];
			$row['ISHEADER'] = (string) $row['ISHEADER'];
			$row['PRODUCTS'] = rtrim($row['PRODUCTS'],'#_#');
			$row['PRODUCTS'] = str_replace("#_#",",\n",$row['PRODUCTS']);
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

/*============ Get getPDMID_Plans =============*/ 
function getPDMID_Plans($mysqli){
	try
	{
		$data = array();
		$PDMID = ($_POST['PDMID'] == "undefined" || $_POST['PDMID'] == "") ? 0 : $_POST['PDMID'];

		$query = "SELECT PLANID id,(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID) label
		FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=$PDMID AND PLANID>0 AND ISDELETED=0";
		$PDM_COUNT = unique($query);
		$data['PDM_COUNT'] = $PDM_COUNT;
		$data['query'] = $query;
		if($PDM_COUNT>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				// $data['data'][] = ['id'=>$row['id'],'label'=>$row['label']];
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


/*============ GET Products Display Details  =============*/ 
function getDetails($mysqli){
	try
	{
		$data = array();
		$PDMID = ($_POST['PDMID'] == "undefined" || $_POST['PDMID'] == "") ? 0 : $_POST['PDMID'];
		if($PDMID==0) throw new Exception('Invalid PDMID.');

		$query = "SELECT PDDID,PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID)[PLAN],SEQNO
		FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=$PDMID AND PLANID>0 AND ISDELETED=0 ORDER BY SEQNO";
		$data['query'] = $query;
		// echo json_encode($data);exit;
		$PDM_COUNT = unique($query);
		if($PDM_COUNT>0){
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

/* =========== Delete =========== */ 
function deleteProductDisplay($mysqli){
	try{   
			global $userid;
			$data = array();     
			$pdmid = ($_POST['pdmid'] == 'undefined' || $_POST['pdmid'] == '') ? 0 : $_POST['pdmid'];
			$stmt=sqlsrv_query($mysqli, "EXEC [PRODUCT_DISPLAY_MASTER_SP] 3,$pdmid,'',0,'',0,'',$userid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$stmt1=sqlsrv_query($mysqli, "UPDATE PRODUCT_DISPLAY_DETAIL SET ISDELETED=1,UPDATEID=$userid,
				UPDATEDATE=GETDATE() WHERE PDMID=$pdmid");
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







