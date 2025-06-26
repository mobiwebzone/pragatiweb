<?php
session_start();
require_once '../../code/connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "saveFranchise":saveFranchise($conn);break;
        case "getFranchise":getFranchise($conn);break;        
        case "deleteFranchise":deleteFranchise($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function saveFranchise($mysqli){
     try
     {
		$data = array();
        global $userid;
        
        $faid  = ($_POST['faid'] == 'undefined' || $_POST['faid'] == '') ? 0 : $_POST['faid'];        
        $txtfirstname  =  $_POST['txtfirstname'] == 'undefined'  ? '' : str_replace("'","''",$_POST['txtfirstname']);
        $txtmiddlename  = $_POST['txtmiddlename'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtmiddlename']);
        $txtlastname  = $_POST['txtlastname'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtlastname']);
        $txtdob  = $_POST['txtdob'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtdob']);
        $txtcellphone  = $_POST['txtcellphone'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtcellphone']);
        $txtemail  =  $_POST['txtemail'] == 'undefined'  ? '' : str_replace("'","''",$_POST['txtemail']);
        $txtaddress1  = $_POST['txtaddress1'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtaddress1']);
        $txtaddress2  = $_POST['txtaddress2'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtaddress2']);
        $txtcity  = $_POST['txtcity'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtcity']);
        $txtstate  = $_POST['txtstate'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtstate']);
        $txtzip  =  $_POST['txtzip'] == 'undefined'  ? '' : str_replace("'","''",$_POST['txtzip']);
        $txtcitizen  = $_POST['txtcitizen'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtcitizen']);
        $txteducatBack  = $_POST['txteducatBack'] == 'undefined' ? '' : str_replace("'","''",$_POST['txteducatBack']);
        $txtjobexp  = $_POST['txtjobexp'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtjobexp']);
        $txtbusiness  = $_POST['txtbusiness'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtbusiness']);
        $txttutoringexp  =  $_POST['txttutoringexp'] == 'undefined'  ? '' : str_replace("'","''",$_POST['txttutoringexp']);
        $txtliquidfin  = $_POST['txtliquidfin'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtliquidfin']);
        $txtlistallfel  = $_POST['txtlistallfel'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtlistallfel']);
        $txtlistallpast  = $_POST['txtlistallpast'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtlistallpast']);        
		
		$actionid = $faid == 0 ? 1 : 2;

		if($txtfirstname == '')
		{throw new Exception("Enter Full Name.");}

		if($txtcellphone == '')
		{throw new Exception("Enter Phone.");}
        $data = array();

		
			$query="EXEC [FRANCHISE_APPLICATION_SP] $actionid,$faid, '$txtfirstname', '$txtmiddlename','$txtlastname','$txtdob','$txtcellphone','$txtemail','$txtaddress1','$txtaddress2','$txtcity','$txtstate','$txtzip','$txtcitizen','$txteducatBack','$txtjobexp','$txtbusiness','$txttutoringexp','$txtliquidfin','$txtlistallfel','$txtlistallpast'";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($faid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				
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


/*============ Get Countries =============*/ 
 function getFranchise($mysqli){
	try
	{

		$data = array();
		$query = "SELECT FAID, FIRSTNAME,MIDDLENAME,LASTNAME, 
					CASE WHEN BIRTHDATE IS NULL OR CONVERT(DATE,BIRTHDATE,105)='01-01-1900'
						THEN '' ELSE CONVERT(VARCHAR,BIRTHDATE,106)
					END BIRTHDATE,PHONE,EMAILID,
					ADDRESS1,ADDRESS2,CITY,STATE,ZIPCODE,CITIZEN,
					EDUCATION,JOBEXP,BUSIEXP,TUTEXP,LIQFINRESOURCE,FELONY,PASTPERSONAL FROM FRANCHISE_APPLICATION
					ORDER BY INSERTDATE DESC";

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['FAID'] = (int) $row['FAID'];
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



/* =========== Delete =========== */ 
function deleteFranchise($mysqli){
	try{   
			global $userid;
			$data = array();     
            $faid = ($_POST['faid'] == 'undefined' || $_POST['faid'] == '') ? 0 : $_POST['faid'];  
			$stmt=sqlsrv_query($mysqli, "DELETE FROM FRANCHISE_APPLICATION WHERE FAID=$faid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Record successfully deleted';
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







