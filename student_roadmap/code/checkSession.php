<?php session_start();
if(!empty($_SESSION['PID']))
{$userid=$_SESSION['PID'];}
else
{$userid=0;}
if(!empty($_SESSION['RMAPID']))
{$rmapid=$_SESSION['RMAPID'];}
else
{$rmapid=0;}





require_once '../../code/connection.php';

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
			global $userid,$rmapid;
		// throw new Exception($userid);
		if($userid>0)
		{

			$sql="SELECT REGID,FIRSTNAME,LASTNAME,LOGINID,LOCATIONID,
			(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOCATION,
			(SELECT LOC_CONTACT FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOC_CONTACT,
			(SELECT LOC_EMAIL FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOC_EMAIL,GRADE,
			(SELECT ROADMAPID FROM STUDENT_COLLEGE_ROADMAP WHERE REGID=$userid AND ROADMAPID=$rmapid AND ISDELETED=0)ROADMAPID 
			 FROM REGISTRATIONS R WHERE REGID=$userid AND PROVISIONAL=1";
			
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
					while ($row = sqlsrv_fetch_array( $stmt) ) {
						$data['data'][] = $row;
						$data['RID']=$row['REGID'];
						$data['RMAPID']=$row['ROADMAPID'];
						$data['userFName']=$row['FIRSTNAME'];
						$data['userLName']=$row['LASTNAME'];
						$data['locid']=$row['LOCATIONID'];
						$data['LOCATION']=$row['LOCATION'];


						
					}
					// Check active plans
					// $chkActivePlan="SELECT PLANID,(SELECT PLANNAME FROM PLANS WHERE PLANID=RD.PLANID)[PLAN] FROM REGISTRATION_DETAILS RD 
					// WHERE CANCELLED=0 AND REGID=$userid AND ACTIVATE=1";
					// $stmtCAP = sqlsrv_query( $conn, $chkActivePlan);
					// $COUNT_CAP=unique($chkActivePlan);
					// if($COUNT_CAP == 0){
					// 	$data['ActivePlan']='YES';
					// }else{
					// 	$data['ActivePlan']='NO';
					// }
					
					
					// $data['sql'] = $sql;
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





