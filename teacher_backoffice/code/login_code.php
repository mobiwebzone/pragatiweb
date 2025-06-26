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
        $IP = ($_POST['IP'] == 'undefined' || $_POST['IP'] == '') ? 'unknown' : $_POST['IP'];   
        
        if($txtLoginId =="")
        {
            throw new Exception( "Enter Correct User ID");
        }
        if($txtPWD =="")
        {
            $data['success'] = false;
            $data['message'] = 'Enter Correct Password.';
        }
        
        $queryUser = "SELECT UID FROM USERS WHERE LOGINID='$txtLoginId' AND ISDELETED=0 AND (USERROLE='VOLUNTEER' OR USERROLE='TEACHER') AND ARCHIVED=0";
        $row_count=unique($queryUser);

        if($row_count > 0)
        {

        
        $resultUser = sqlsrv_query($mysqli, $queryUser);
        $rowUser = sqlsrv_fetch_array($resultUser);
        $ret=$rowUser['UID'];
        
        
            if($ret > 0)
            {
                $queryPwd = "SELECT DBO.GET_CLEAR_USER_PASSWORD($ret) PWD";
                $resultPwd = sqlsrv_query($mysqli, $queryPwd);
                $rowPwd = sqlsrv_fetch_array($resultPwd);

                if($rowPwd['PWD'] == $txtPWD)
                {
                    $query="SELECT UID,FIRSTNAME,LASTNAME,MOBILE,EMAIL,USERROLE,LOCID,
                        (SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=USERS.LOCID)[LOCATION] FROM USERS WHERE UID=$ret AND ISDELETED=0 AND (USERROLE='VOLUNTEER' OR USERROLE='TEACHER')";
                    $stmt = sqlsrv_query($mysqli, $query);
                    while($row = sqlsrv_fetch_array($stmt))
                    {
                        $row['UID'] = (int) $row['UID'];
                        $UID = (int) $row['UID'];
                        $_SESSION['MEP_USERID']=$row['UID'];
                        $_SESSION['FNAME']=$row['FIRSTNAME'];
                        $_SESSION['LNAME']=$row['LASTNAME'];
                        $_SESSION['ROLE']=$row['USERROLE'];
                        $_SESSION['USER_LOCID']=$row['LOCID'];
                        $_SESSION['LOCATION']=$row['LOCATION'];



                        $role=$row['USERROLE'];
                        
                        $data['data'][] = $row;
                        
                    }
                    // UPDATE LOG
                    $logQuery = "INSERT INTO LOGINS_LOG (LOGINTYPE,USERID,LOGINDT,IPADDRESS,LOGINSTATUS)
                                VALUES ('TEACHER',$ret,GETDATE(),'$IP','SUCCESS')";
                    sqlsrv_query($mysqli, $logQuery);
                    $_SESSION['LOGINTYPE']='TEACHER';
                    
                    
                    $data['role'] = $role;
                    // $data['query'] = $query;
                    $data['success'] = true;
                    $data['message'] = 'Signing In...';
                }
                else {
                    $data['success'] = false;
                    $data['message'] = 'Wrong password';

                    // UPDATE LOG
                    $logQuery = "INSERT INTO LOGINS_LOG (LOGINTYPE,USERID,LOGINDT,IPADDRESS,LOGINSTATUS)
                                VALUES ('TEACHER',$ret,GETDATE(),'$IP','FAILED')";
                    sqlsrv_query($mysqli, $logQuery);  
                }
                
                
            }
        }
        else
        {
            // $data['userid'] = $queryUser;
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







