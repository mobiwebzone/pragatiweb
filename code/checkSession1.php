<?php session_start();
if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}



require_once '../code/connection.php';

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "checkSession":checkSession($conn);break;
		case "logout":logout($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function gets list of users from database
 */
function checkSession($conn){
	try{
	    	$data = array();
			global $userid;
		if($userid>0)
		{

			$sql="SELECT UID,FIRSTNAME,LASTNAME,MOBILE,EMAIL,LOGINID,PWD FROM USERS WHERE UID=$userid";
						
			$stmt = sqlsrv_query( $conn, $sql );

			if($stmt == false)
			{
				$data['success'] = false;
				$data['message'] = 'Login Failed' . "/" . $userid;
				echo json_encode($data);exit;
			}
			else
			{
				while ($row = sqlsrv_fetch_array( $stmt) ) {
					$data['data'][] = $row;
					$data['userid']=$row['UID'];
					$data['userFName']=$row['FIRSTNAME'];
					$data['userLName']=$row['LASTNAME'];
					// $data['userrole']=$row['USERROLE'];
						  
				}
				
				$data['success'] = true;
				$data['message'] = 'Login Successfull';
				$data['session'] = true;
				echo json_encode($data);exit;
			}
		}
		$data['session'] = false;
		$data['success'] = false;
		$data['message'] = 'Login Failed';
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}


function logout($conn){
	try{
	    $data = array();
        
        session_unset();
        

		$data['success'] = true;
        $data['message'] = 'Logout successfully';
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





