<?php
session_start();
require_once '../../code/connection.php';

date_default_timezone_set('Asia/Kolkata'); // Ensure IST time zone

if (!empty($_SESSION['MEP_USERID'])) {
    $userid = $_SESSION['MEP_USERID'];
} else {
    $userid = 0;
}

if (isset($_POST['type']) && !empty($_POST['type'])) {
    $type = $_POST['type'];
    switch ($type) {
        case "save": save($conn); break;
        case "getQuery": getQuery($conn); break;
        case "getschoolname": getschoolname($conn); break;
        case "getForm": getForm($conn); break;
        case "getIsvisible": getIsvisible($conn);break;
        case "delete":delete($conn);break;
        default: invalidRequest();
    }
} else {
    invalidRequest();
}

function save($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $pmid  = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
		$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
		$TEXT_FORM_ID  = $_POST['TEXT_FORM_ID'] == 'undefined' ? 0 : $_POST['TEXT_FORM_ID'];
        $TEXT_FIELD_NAME_DESC  = $_POST['TEXT_FIELD_NAME_DESC'] == 'undefined' ? 0 : $_POST['TEXT_FIELD_NAME_DESC'];
		$TEXT_FIELD_NAME  = $_POST['TEXT_FIELD_NAME'] == 'undefined' ? 0 : $_POST['TEXT_FIELD_NAME'];
		$TEXT_IS_VISIBLE  = $_POST['TEXT_IS_VISIBLE'] == 'undefined' ? 0 : $_POST['TEXT_IS_VISIBLE'];
	   
		
		$actionid = $pmid == 0 ? 1 : 2;
		
		$sql = "SELECT * FROM FIELD_VISIBILITY 
		WHERE FIELD_VISIBILITY_ID   != $pmid
		AND   SCHOOL_ID             =  $TEXT_SCHOOL_ID
		AND   FORM_ID               =  $TEXT_FORM_ID
		AND   FIELD_NAME            = '$TEXT_FIELD_NAME'
		AND   IS_VISIBLE            =  '$TEXT_IS_VISIBLE'
		AND   ISDELETED             = 0 ";	
       
		// throw new Exception($sql);
	   $row_count = unique($sql);
	   
		   
	   if($row_count == 0)
	   {
	   		
		$query="EXEC [FIELD_VISIBILITY_SP] 
										 $actionid
										,$pmid
										,$TEXT_SCHOOL_ID
										,$TEXT_FORM_ID
										,'$TEXT_FIELD_NAME_DESC'
										,'$TEXT_FIELD_NAME'
										,'$TEXT_IS_VISIBLE'
										,$userid ";
							
		$data['$sql'] = $query;
				   
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				
				$data['success'] = false;
				$data['query'] = $query;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				
                if(!empty($pmid))
                 {                           
					$data['message'] = 'Record successfully updated';
					echo json_encode($data);exit;
				 }
				 
				else 
				{
					$data['message'] = 'Record successfully inserted.';
					echo json_encode($data);exit;
				}
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Object Type already exists.';
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


function getQuery($mysqli){
		try
	{
	
	$data = array();
	$TEXT_SCHOOL_ID  = $_POST['TEXT_SCHOOL_ID'] == 'undefined' ? 0 : $_POST['TEXT_SCHOOL_ID'];
	$TEXT_FORM_ID    = $_POST['TEXT_FORM_ID'] == 'undefined' ? 0 : $_POST['TEXT_FORM_ID'];
    
	
       $query =     "SELECT 
	                 FIELD_VISIBILITY_ID
                    ,SCHOOL_ID
                    ,FORM_ID
                    ,FORM_NAME
                    ,FIELD_NAME_DESC
                    ,FIELD_NAME
                    ,IS_VISIBLE
					,IS_VISIBLE_CD
					from FIELD_VISIBILITY
					WHERE ISDELETED=0 
					AND SCHOOL_ID = 2  AND FORM_ID = $TEXT_FORM_ID AND ISDELETED = 0 ";
		

        $result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FIELD_VISIBILITY_ID'] = (int) $row['FIELD_VISIBILITY_ID'];
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



function getForm($mysqli){
	try
	{
		
	$query = "SELECT FORM_ID, FORM_NAME FROM FORM_MASTER WHERE isdeleted=0";

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['FORM_ID'] = (int) $row['FORM_ID'];
				
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



function getschoolname($mysqli){
    try {
        global $userid;
        // $query = "SELECT SCHOOL_ID, SCHOOL_NAME 
        //           FROM SCHOOL 
        //           WHERE ISDELETED = 0 
        //           AND SCHOOL_ID IN (SELECT SCHOOL_ID FROM SCHOOL_USER WHERE USER_ID = ? AND ISDELETED = 0)
        //           ORDER BY SCHOOL_ID";

		$query = "SELECT SCHOOL_ID, SCHOOL_NAME 
                  FROM SCHOOL 
                  WHERE ISDELETED = 0 
                  AND   SCHOOL_ID = 2 ";

        $result = sqlsrv_query($mysqli, $query, [$userid]);
        if ($result === false) {
            $data['message'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
            echo json_encode($data);
            exit;
        }

        $data = array('success' => false, 'data' => []);
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $row['SCHOOL_ID'] = (int)$row['SCHOOL_ID'];
            $data['data'][] = $row;
        }

        $data['success'] = !empty($data['data']);
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        $data = array('success' => false, 'data' => [], 'message' => 'Exception: ' . $e->getMessage());
        echo json_encode($data);
        exit;
    }
}


function getIsvisible($mysqli){
	try
	{
		
	$data = array();
	

	$query = "SELECT CODE_DETAIL_ID, CODE_DETAIL_DESC FROM MEP_CODE_DETAILS WHERE CODE_ID=20 AND ISDELETED=0"; 

		$data = array();
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
	
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CODE_DETAIL_ID'] = (int) $row['CODE_DETAIL_ID'];
				
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



function delete($mysqli){
    try {
        global $userid;
        $data = array();

        $pmid = ($_POST['pmid'] == 'undefined' || $_POST['pmid'] == '') ? 0 : $_POST['pmid'];
      
        
								
		$stmt = sqlsrv_query($mysqli, "EXEC [FIELD_VISIBILITY_SP] 3, $pmid, '','', '', '', '', $userid ");


        if ($stmt === false) {
            $errors = sqlsrv_errors();
            throw new Exception($errors[0]['message']);
        } else {
            $data['success'] = true;
            $data['message'] = 'Record successfully deleted';
        }

        echo json_encode($data); 
        exit;

    } catch (Exception $e) {
        $data = array();
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        echo json_encode($data);
        exit;
    }
}


function invalidRequest() {
    $data = array('success' => false, 'data' => [], 'message' => 'Invalid request.');
    echo json_encode($data);
    exit;
}
?>