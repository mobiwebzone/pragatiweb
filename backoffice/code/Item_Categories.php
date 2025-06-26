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
        case "getItemCategories":getItemCategories($conn);break;
        case "delete":delete($conn);break;
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
		
		$data = array();
        $icatid  = ($_POST['icatid'] == 'undefined' || $_POST['icatid'] == '') ? 0 : $_POST['icatid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtItemCat  = ($_POST['txtItemCat'] == 'undefined' || $_POST['txtItemCat'] == '') ? '' : $_POST['txtItemCat'];
        $txtDesc  = ($_POST['txtDesc'] == 'undefined' || $_POST['txtDesc'] == '') ? '' : $_POST['txtDesc'];
		$Itype  = ($_POST['Itype'] == 'undefined' || $_POST['Itype'] == '') ? '' : $_POST['Itype'];
		
		$actionid = $icatid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtItemCat == '')throw new Exception("Please Enter Item Category Name.");
		if($Itype == '')throw new Exception("Please select Item TYPE.");
		
		$sql = "SELECT * FROM ITEM_CATEGORIES WHERE LOCID=$ddlLocation AND ITEMCATEGORY='$txtItemCat' AND ITYPE='$Itype' AND ICATID!=$icatid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [ITEM_CATEGORIES_SP] $actionid,$icatid,$ddlLocation,'$txtItemCat','$txtDesc','$Itype',$userid";
			// $data['$query'] = $query;
			// echo json_encode($data);exit;
			$stmt=sqlsrv_query($mysqli, $query);
			// throw new Exception($query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($icatid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			
			$data['success'] = false;
			$data['message'] = 'Item Category already exists.';
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


/*============ Get Item Categories =============*/ 
 function getItemCategories($mysqli){
	try
	{
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT ICATID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=IC.LOCID)[LOCATION],
				ITEMCATEGORY,DESCR,ITYPE FROM ITEM_CATEGORIES IC WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY LOCATION,ITEMCATEGORY";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['ICATID'] = (int) $row['ICATID'];
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
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $icatid = ($_POST['icatid'] == 'undefined' || $_POST['icatid'] == '') ? 0 : $_POST['icatid'];  
			$query = "EXEC [ITEM_CATEGORIES_SP] 3,$icatid,0,'','','',$userid";
			$data['$query'] = $query;
			$stmt=sqlsrv_query($mysqli,$query);
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
		$data['success'] = false . $query;
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







