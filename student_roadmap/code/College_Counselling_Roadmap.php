<?php
session_start();
require_once '../../code/connection.php';

// if(!empty($_SESSION['MEP_USERID']))
// {$userid=$_SESSION['MEP_USERID'];}
// else
// {$userid=0;}

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function login($mysqli){
	try{
	    $data = array();
        $txtLoginId = $_POST['txtLoginId'] == 'undefined' ? '' : $_POST['txtLoginId'];
        $txtPWD = $_POST['txtPWD'] == 'undefined' ? '' : $_POST['txtPWD'];   
        
        if($txtLoginId == "")throw new Exception( "Enter User ID.");
        if($txtPWD == "")throw new Exception( "Enter Password.");
        
        $queryUser = "SELECT REGID FROM REGISTRATIONS WHERE LOGINID='$txtLoginId' AND ISDELETED=0";
        $row_count=unique($queryUser);

        if($row_count > 0)
        {

        
        $resultUser = sqlsrv_query($mysqli, $queryUser);
        $rowUser = sqlsrv_fetch_array($resultUser);
        $ret=(int) $rowUser['REGID'];
        

        
            if($ret > 0)
            {
                $queryPwd = "SELECT DBO.GET_CLEAR_STUDENT_PASSWORD($ret) PWD";
                $resultPwd = sqlsrv_query($mysqli, $queryPwd);
                $rowPwd = sqlsrv_fetch_array($resultPwd);

                if($rowPwd['PWD'] == $txtPWD)
                {

                    $ChkApprove="SELECT PROVISIONAL FROM REGISTRATIONS WHERE REGID=$ret AND ISDELETED=0";
                    $stmtApprove = sqlsrv_query($mysqli, $ChkApprove);
                    $rowApprove = sqlsrv_fetch_array($stmtApprove);

                    if($rowApprove['PROVISIONAL'] == 1){

                        // $query="SELECT REGID,FIRSTNAME,LASTNAME,LOCATIONID FROM REGISTRATIONS WHERE REGID=$ret AND ISDELETED=0 AND APPROVED=1";
                        $query="SELECT REGID,FIRSTNAME,LASTNAME,LOCATIONID,
                        (SELECT ROADMAPID FROM STUDENT_COLLEGE_ROADMAP WHERE REGID=$ret AND ISDELETED=0)ROADMAPID 
                        FROM REGISTRATIONS R WHERE REGID=$ret AND ISDELETED=0 AND PROVISIONAL=1";
                        $stmt = sqlsrv_query($mysqli, $query);
                        while($row = sqlsrv_fetch_array($stmt))
                        {
                            
                            $row['REGID'] = (int) $row['REGID'];
                            // $REGID = (int) $row['REGID'];
                            $_SESSION['PID']=$row['REGID'];
                            $_SESSION['RMAPID']=$row['ROADMAPID'];
                            $_SESSION['ST_FNAME']=$row['FIRSTNAME'];
                            $_SESSION['ST_LNAME']=$row['LASTNAME'];
                            $_SESSION['ST_LOCID']=$row['LOCATIONID'];
                            // $_SESSION['ROLE']='STUDENT';
                            
                            // $data['data'][] = $row;
                            
                        }
                        
                        
                        $data['role'] = 'STUDENT';
                        $data['query'] = $query;
                        $data['success'] = true;
                        $data['message'] = 'Signing In...';
                    }
                    else{
                        $data['success'] = false;
                        // $data['message'] = 'You are not Approved for login.';
                        $data['message'] = 'Login Failed.';
                    }
                    
                }
                else {
                    $data['success'] = false;
                    $data['message'] = 'Wrong password';
                }
                
                
            }
        }
        else
        {
            $data['userid'] = $queryUser;
            $data['success'] = false;
            $data['message'] = 'Wrong Login ID.';
            // $data['message'] = 'We could not verify your credentials. Please double-check and try again.';
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



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







