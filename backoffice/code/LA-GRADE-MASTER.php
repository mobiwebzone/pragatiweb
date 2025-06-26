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
        case "getGrades":getGrades($conn);break;
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
        $gradeid  = ($_POST['gradeid'] == 'undefined' || $_POST['gradeid'] == '') ? 0 : $_POST['gradeid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $txtGrade  = ($_POST['txtGrade'] == 'undefined' || $_POST['txtGrade'] == '') ? '' : $_POST['txtGrade'];
		
		$actionid = $gradeid == 0 ? 1 : 2;
		
		
		if($ddlLocation == 0)throw new Exception("Please Select Location Name.");
		if($txtGrade == '')throw new Exception("Please Enter Grade.");
		
		$sql = "SELECT * FROM LA_GRADE_MASTER WHERE LOCID=$ddlLocation AND GRADE='$txtGrade' AND GRADEID!=$gradeid AND ISDELETED=0";
		$row_count = unique($sql);
		
		
		if($row_count == 0)
		{
			$query="EXEC [LA_GRADE_MASTER_SP] $actionid,$gradeid,$ddlLocation,'$txtGrade',$userid";
			$data['$query'] = $query;
			// echo json_encode($data);exit;
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
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($gradeid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}	
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Grade already exists.';
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


/*============ GET GRADE =============*/ 
 function getGrades($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT GRADEID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=GM.LOCID)[LOCATION],GRADE 
				FROM LA_GRADE_MASTER GM 
				WHERE ISDELETED=0";
		if($ddlLocation>0)$query .= " AND LOCID=$ddlLocation";
		$query .= " ORDER BY [LOCATION],LEN(GRADE),GRADE";
		$data['$query'] = $query;
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			
			$idx=0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['GRADEID'] = (int) $row['GRADEID'];
				$row['GRADE_OR'] = is_numeric($row['GRADE']) ? (int)$row['GRADE'] : $row['GRADE'];
				$data['data'][] = $row;

				$idx++;
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
/*============ GET GRADE =============*/ 


/* =========== DELETE =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $GRADEID = ($_POST['GRADEID'] == 'undefined' || $_POST['GRADEID'] == '') ? 0 : $_POST['GRADEID'];  
			$query = "EXEC [LA_GRADE_MASTER_SP] 3,$GRADEID,0,'',$userid";
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
/* =========== DELETE =========== */ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







