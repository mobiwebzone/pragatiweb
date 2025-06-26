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
        case "saveData":saveData($conn);break;
        case "getLegends":getLegends($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ============ SAVE DATA ============= */ 
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$legendid = ($_POST['legendid'] == 'undefined' || $_POST['legendid'] == '') ? 0 : $_POST['legendid'];
		$txtLegend = $_POST['txtLegend'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLegend']);
		$txtLegendShortDesc = $_POST['txtLegendShortDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLegendShortDesc']);
		$txtLegendLongDesc = $_POST['txtLegendLongDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLegendLongDesc']);

		$actionid = $legendid == 0 ? 1 : 2;

		if($txtLegend == '') throw new Exception("Enter Legend.");


		$sql = "SELECT * FROM ACTIVITY_LEGEND_MASTER WHERE LEGEND='$txtLegend' AND LEGENDID!=$legendid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ACTIVITY_LEGEND_MASTER_SP] $actionid,$legendid,'$txtLegend','$txtLegendShortDesc','$txtLegendLongDesc',$userid";
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
				if(!empty($legendid))$data['message'] = 'Record successfully updated.';
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
/* ============ SAVE DATA ============= */ 




/* ========== GET LEGENDS =========== */
 function getLegends($mysqli){
	try
	{
		$data = array();
		$query = "SELECT LEGENDID,LEGEND,LEGEND_SHORT_DESC,LEGEND_LONG_DESC FROM ACTIVITY_LEGEND_MASTER WHERE ISDELETED=0 ORDER BY LEGEND";
		$count = unique($query);

		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row['LEGENDID'] = (int) $row['LEGENDID'];
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
/* ========== GET LEGENDS =========== */



/* =========== Delete =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $LEGENDID = ($_POST['LEGENDID'] == 'undefined' || $_POST['LEGENDID'] == '') ? 0 : $_POST['LEGENDID'];  
			if($LEGENDID == 0)throw new Exception('LEGENDID Not Found.');
			$delQuery = "EXEC [ACTIVITY_LEGEND_MASTER_SP] 3,$LEGENDID,'','','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
/* =========== Delete =========== */ 





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







