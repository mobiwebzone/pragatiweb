<?php
session_start();



$logid=0;
if(!empty($_SESSION['LOGINTYPE']))
{
	$LOGINTYPE=$_SESSION['LOGINTYPE'];
	if($LOGINTYPE == 'TEACHER'){
		if(!empty($_SESSION['MEP_USERID']))
		{
			$logid=$_SESSION['MEP_USERID'];
		}
	}
	else if($LOGINTYPE == 'STUDENT'){
		if(!empty($_SESSION['STUDENTID']))
		{
			$logid=$_SESSION['STUDENTID'];
		}
	}
}
else
{$LOGINTYPE='';}



require_once '../../code/connection.php';

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "logout":
			logout($conn);
			break;
		default:
			invalidRequest();
	}
}else{
	invalidRequest();
}


function logout($conn){
	try{
        global $logid,$LOGINTYPE;
	    $data = array();

		// UPDATE LOG
		$logQuery = "UPDATE LOGINS_LOG SET  LOGOUTDT=GETDATE() WHERE ID=(SELECT TOP 1 ID FROM LOGINS_LOG WHERE USERID=$logid AND LOGINTYPE='$LOGINTYPE' ORDER BY ID DESC)";
		$data['$logQuery']=$logQuery;
		sqlsrv_query($conn, $logQuery);
        
        session_unset();
        session_destroy();

		$data['success'] = true;
        $data['message'] = 'Logout successfully';
        //header("Location:../login.html");
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





