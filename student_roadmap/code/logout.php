<?php
session_start();
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
	    $data = array();
        
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





