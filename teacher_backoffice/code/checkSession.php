<?php session_start();
if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if(!empty($_SESSION['ROLE']))
{$login_role=$_SESSION['ROLE'];}
else
{$login_role='';}

require_once '../../code/connection.php';

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "checkSession":checkSession($conn);break;
		case "getBrandLogo":getBrandLogo($conn);break;
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

			$sql="SELECT [UID],FIRSTNAME,LASTNAME,MOBILE,EMAIL,LOGINID,PWD,USERROLE,LOCID
			,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=USERS.LOCID)[LOCATION],ALWAYS_ACTIVE 
			FROM USERS WHERE [UID]=$userid
			AND (USERROLE='VOLUNTEER' OR USERROLE='TEACHER') AND ARCHIVED=0";
			$data['$sql'] = $sql;
			$count=unique($sql);
			if($count>0){
				$stmt = sqlsrv_query( $conn, $sql );
				if($stmt == false)
				{
					$data['success'] = false;
					$data['message'] = 'Login Failed' . "/" . $userid;
					echo json_encode($data);exit;
				}
				else
				{
					while ($row = sqlsrv_fetch_array( $stmt,SQLSRV_FETCH_ASSOC) ) {
						$data['data'][] = $row;
						$data['userid']=$row['UID'];
						$data['userFName']=$row['FIRSTNAME'];
						$data['userLName']=$row['LASTNAME'];
						$data['userrole']=$row['USERROLE'];
						$data['locid']=$row['LOCID'];
						$data['LOCATION']=$row['LOCATION'];
							  
					}
					
					$data['success'] = true;
					$data['message'] = 'Login Successfull.';
					$data['session'] = true;
					echo json_encode($data);exit;
				}
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


function getBrandLogo($conn){
	try{
		$data = array();
		global $userid,$login_role;
		$data['$userid']=$userid;
		$data['$login_role']=$login_role;
		
		$data['data'] = array('LOGO'=>'../images/logo.png','LOGO_DESC'=>'');
		if($userid>0){
			if($login_role=='TEACHER' || $login_role=='VOLUNTEER'){
				// $query = "SELECT B.LOGO,B.LOGO_DESC FROM BRANDS B,REGISTRATIONS R WHERE B.BRANDID=R.BRANDID AND R.REGID=$userid";
				// $count = unique($query);
				// if($count>0){
				// 	$stmt = sqlsrv_query( $conn, $query );
				// 	$row = sqlsrv_fetch_array( $stmt,SQLSRV_FETCH_ASSOC);
				// 	$row['LOGO'] = '../backoffice/images/brand/'.$row['LOGO'];
				// 	$data['data'] = $row;
				// }
			}
		}
		echo json_encode($data);exit();
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);exit();
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





