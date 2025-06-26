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
        case "save":save($conn);break;
        case "getQuery":getQuery($conn);break;
		
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
		   
        $pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
        $TEXT_ORG_NAME  = $_POST['TEXT_ORG_NAME'] == 'undefined' ? '' : $_POST['TEXT_ORG_NAME'];
	    $TEXT_ADDRESS  = $_POST['TEXT_ADDRESS'] == 'undefined' ? '' : $_POST['TEXT_ADDRESS'];
	    $TEXT_MOBILE_NO  = $_POST['TEXT_MOBILE_NO'] == 'undefined' ? '' : $_POST['TEXT_MOBILE_NO'];
	    $TEXT_E_MAIL_ID  = $_POST['TEXT_E_MAIL_ID'] == 'undefined' ? '' : $_POST['TEXT_E_MAIL_ID'];
	
		$txtremarks  = $_POST['txtremarks'] == 'undefined' ? '' : $_POST['txtremarks'];
	
		
		$actionid = $pmid == 0 ? 1 : 2;

		

		$sql = "SELECT * FROM ORGANIZATION 
		WHERE ORG_NAME='$TEXT_ORG_NAME' 
		AND   ORG_ID!=$pmid  
		AND   ISDELETED=0 ";

	// throw new Exception($sql);

		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [ORGANIZATION_SP] $actionid,$pmid,'$TEXT_ORG_NAME','$TEXT_ADDRESS','$TEXT_MOBILE_NO','$TEXT_E_MAIL_ID','$txtremarks',$userid ";
			
			// echo json_encode($query);exit;

			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				
				$data['success'] = true;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($pmid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
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


/*============ Get PAYMENT MODES =============*/ 
 function getQuery($mysqli){
	try
	{
		
        $query = "SELECT * FROM ORGANIZATION WHERE ISDELETED=0 ORDER BY ORG_ID DESC";	

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ORG_ID'] = (int) $row['ORG_ID'];
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
            $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];  
			if($pmid == 0){
				throw new Exception('ORG_ID Error.');
			}

			$stmt=sqlsrv_query($mysqli, "EXEC [ORGANIZATION_SP] 3,$pmid,'','','','','',$userid ");
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







