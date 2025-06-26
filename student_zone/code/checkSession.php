<?php session_start();
if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}


if(!empty($_SESSION['ROLE']))
{$login_role=$_SESSION['ROLE'];}
else
{$login_role='';}

require_once '../code/connection.php';

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
		// throw new Exception($userid);
		if($userid>0)
		{

			$sql="SELECT REGID,FIRSTNAME,LASTNAME,ISNULL(PHONE,'')PHONE,ISNULL(EMAIL,'')EMAIL,LOGINID,LOCATIONID,
			(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOCATION,
			(SELECT LOC_CONTACT FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOC_CONTACT,
			(SELECT LOC_EMAIL FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOC_EMAIL,GRADE
			 FROM REGISTRATIONS R WHERE REGID=$userid AND APPROVED=1 AND ARCHIVED=0";
			
			// $data['$sql']=$sql;

			$stmt = sqlsrv_query( $conn, $sql );
			
			$count = unique($sql);
			
			if($count > 0){

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
						$data['userid']=$row['REGID'];
						$data['userFName']=$row['FIRSTNAME'];
						$data['userLName']=$row['LASTNAME'];
						$data['locid']=$row['LOCATIONID'];
						$data['LOCATION']=$row['LOCATION'];
					}
					// Check active plans
					$chkActivePlan="SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN] FROM REGISTRATION_DETAILS RD 
					WHERE CANCELLED=0 AND REGID=$userid AND ACTIVATE=1";
					$stmtCAP = sqlsrv_query( $conn, $chkActivePlan);
					$COUNT_CAP=unique($chkActivePlan);
					if($COUNT_CAP == 0){
						$data['ActivePlan']='YES';
					}else{
						$data['ActivePlan']='NO';
					}
					
					
					$data['sql'] = $sql;
					$data['success'] = true;
					$data['message'] = 'Login Successfull';
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
			if($login_role=='STUDENT'){
				$query = "SELECT B.LOGO,B.LOGO_DESC FROM BRANDS B,REGISTRATIONS R WHERE B.BRANDID=R.BRANDID AND R.REGID=$userid";
				$count = unique($query);
				if($count>0){
					$stmt = sqlsrv_query( $conn, $query );
					$row = sqlsrv_fetch_array( $stmt,SQLSRV_FETCH_ASSOC);
					$row['LOGO'] = '../backoffice/images/brand/'.$row['LOGO'];
					$data['data'] = $row;
				}
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





