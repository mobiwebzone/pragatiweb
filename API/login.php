<?php


/*============ Get login =============*/
function login($mysqli,$postData){
  try {
    $data = array();
    $username = ($postData['username'] == 'undefined' || $postData['username'] == '') ? '' : $postData['username'];
    $password = ($postData['password'] == 'undefined' || $postData['password'] == '') ? '' : $postData['password'];
    $logFor = ($postData['logFor'] == 'undefined' || $postData['logFor'] == '') ? '' : $postData['logFor'];

    if($username =="")throw new Exception( "Enter Correct Phone Number");
    if($password =="")throw new Exception( "Enter Correct Password");
    if($logFor =="")throw new Exception( "Invalid LogFor");
        
    if($logFor==='ADMIN'){
        $query = "SELECT UID, FIRSTNAME, LASTNAME, USERROLE, (SELECT DBO.GET_CLEAR_USER_PASSWORD(UID))PWD,LOCID FROM USERS WHERE USERROLE IN ('ADMINISTRATOR','SUPERADMIN','USER') AND LOGINID='$username'";
    }else if($logFor==='TEACHER'){
        $query = "SELECT UID, FIRSTNAME, LASTNAME, USERROLE, (SELECT DBO.GET_CLEAR_USER_PASSWORD(UID))PWD,LOCID FROM USERS WHERE USERROLE IN ('TEACHER','VOLUNTEER') AND LOGINID='$username'";
    }else if($logFor==='STUDENT'){
        $query = "SELECT REGID UID, FIRSTNAME, LASTNAME, '' USERROLE, (SELECT DBO.GET_CLEAR_STUDENT_PASSWORD(REGID))PWD,LOCATIONID LOCID FROM REGISTRATIONS WHERE APPROVED=1 AND ISDELETED=0 AND LOGINID='$username'";
    }
    // $data['$query'] =$query;
    $count = unique($query);
    if($count>0){
        $result = sqlsrv_query($mysqli, $query);
        $row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
        $row['PWD'] = $row['PWD'];
        if($row['PWD'] === $password){
            $data['message'] = 'You are successfully logged In.';
            $data['success'] = true;
            $data['data'] = $row;
        }else{
            
            $data['success'] = false;
            $data['message'] = 'Invalid Userid or Password.';
        }
    }else{
        $data['success'] = false;
        $data['message'] = 'Invalid Userid or Password.';
    }
    
    echo json_encode($data);exit;
  } catch (Exception $e) {
    $data = array();
    $data['success'] = false;
    $data['message'] = $e->getMessage();
    echo json_encode($data);
    exit;
  }
}


/*============ Create Student Account =============*/
function createStudentAccount($mysqli,$postData){
    try{
        $data = array();
        $postData = isset($postData['formData']) ? json_decode($postData['formData'],true) : array();
        if(count($postData)==0) throw new Exception('Invalid Data.');
        $data['$location'] = $postData['location'];
        $location = ($postData['location']=='undefined' || $postData['location'] == '') ? 0 : $postData['location'];
        $mode = ($postData['mode']=='undefined' || $postData['mode'] == '') ? '' : str_replace("'","''",$postData['mode']);
        $firstName = ($postData['firstName']=='undefined' || $postData['firstName'] == '') ? '' : str_replace("'","''",$postData['firstName']);
        $lastName = ($postData['firstName']=='undefined' || $postData['firstName'] == '') ? '' : str_replace("'","''",$postData['firstName']);
        $phone = ($postData['phone']=='undefined' || $postData['phone'] == '') ? '' : str_replace("'","''",$postData['phone']);
        $email = ($postData['email']=='undefined' || $postData['email'] == '') ? '' : str_replace("'","''",$postData['email']);
        $grade = ($postData['grade']=='undefined' || $postData['grade'] == '') ? '' : str_replace("'","''",$postData['grade']);
        $classof = ($postData['classof']=='undefined' || $postData['classof'] == '') ? 0 : $postData['classof'];
        $school = ($postData['school']=='undefined' || $postData['school'] == '') ? '' : str_replace("'","''",$postData['school']);
        $address = ($postData['address']=='undefined' || $postData['address'] == '') ? '' : str_replace("'","''",$postData['address']);
        $city = ($postData['city']=='undefined' || $postData['city'] == '') ? '' :  str_replace("'","''",$postData['city']);
        $state = ($postData['state']=='undefined' || $postData['state'] == '') ? '' :  str_replace("'","''",$postData['state']);
        $zipcode = ($postData['zipcode']=='undefined' || $postData['zipcode'] == '') ? '' :  str_replace("'","''",$postData['zipcode']);
        $country = ($postData['country']=='undefined' || $postData['country'] == '') ? 0 :  $postData['country'];
        $firstNameP1 = ($postData['firstNameP1']=='undefined' || $postData['firstNameP1'] == '') ? '' :  str_replace("'","''",$postData['firstNameP1']);
        $lastNameP1 = ($postData['lastNameP1']=='undefined' || $postData['lastNameP1'] == '') ? '' :  str_replace("'","''",$postData['lastNameP1']);
        $phoneP1 = ($postData['phoneP1']=='undefined' || $postData['phoneP1'] == '') ? '' :  str_replace("'","''",$postData['phoneP1']);
        $emailP1 = ($postData['emailP1']=='undefined' || $postData['emailP1'] == '') ? '' :  str_replace("'","''",$postData['emailP1']);
        $firstNameP2 = ($postData['firstNameP2']=='undefined' || $postData['firstNameP2'] == '') ? '' :  str_replace("'","''",$postData['firstNameP2']);
        $lastNameP2 = ($postData['lastNameP2']=='undefined' || $postData['lastNameP2'] == '') ? '' :  str_replace("'","''",$postData['lastNameP2']);
        $phoneP2 = ($postData['phoneP2']=='undefined' || $postData['phoneP2'] == '') ? '' :  str_replace("'","''",$postData['phoneP2']);
        $emailP2 = ($postData['emailP2']=='undefined' || $postData['emailP2'] == '') ? '' :  str_replace("'","''",$postData['emailP2']);
        $Addi_Instructions = ($postData['Addi_Instructions']=='undefined' || $postData['Addi_Instructions'] == '') ? '' :  str_replace("'","''",$postData['Addi_Instructions']);


        $query = "EXEC [REGISTRATIONS_SP] 1,0,$location,'$mode',0,'$firstName','$lastName',
                    '$phone','$email','$grade',$classof,'$school','$address','',
                    '$city','$state','$zipcode',$country,'','$firstNameP1','$lastNameP1',
                    '$phoneP1','$emailP1','$firstNameP2','$lastNameP2','$phoneP2','$emailP2',
                    '','','$Addi_Instructions',1,0";
        // $data['$query'] = $query;
        // echo json_encode($data);exit;
        $stmt = sqlsrv_query($mysqli,$query);

        if($stmt === false)
        {
            $data['success'] = false;
            $data['message'] = 'Registration Failed.';
            $data['query'] = $query;
        }
        else
        {
            $data['success'] = true;
            $data['message']='Account Successfully Created.';
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


?>