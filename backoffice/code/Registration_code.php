<?php
session_start();
require_once '../../code/connection.php';

require __DIR__ . '../../../Twilio/autoload.php';
use Twilio\Rest\Client;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}
if(!empty($_SESSION['USER_LOCID']))
{$locid=$_SESSION['USER_LOCID'];}
else
{$locid=0;}
if(!empty($_SESSION['IS_ET']))
{$isET=$_SESSION['IS_ET'];}
else
{$isET=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "login":login($conn);break;
        case "saveRegistrations":saveRegistrations($conn);break;
		case "saveExcelFile":saveExcelFile($conn);break;
        case "getLocation":getLocation($conn);break;
        case "getStudentProposedCourses":getStudentProposedCourses($conn);break;
        case "getCountries":getCountries($conn);break;
        case "getRegistrations":getRegistrations($conn);break;
        case "getPlans":getPlans($conn);break;
        case "getProductPlans":getProductPlans($conn);break;
        case "getAllPlanDetail":getAllPlanDetail($conn);break;
        case "EnrollStudent_PlanByAdmin":EnrollStudent_PlanByAdmin($conn);break;
        case "CancelPlan":CancelPlan($conn);break;
        case "deleteRegistration":deleteRegistration($conn);break;

		// EMAIL / SMS
		case "saveDataSms":saveDataSms($conn);break;
        case "saveDataEmail":saveDataEmail($conn);break;
		case "getMSGHistory":getMSGHistory($conn);break;
        case "getEMAILHistory":getEMAILHistory($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}



/**
 * This function will handle user add, update functionality
 * @throws Exception
 */



/* ============ Save Data ============= */ 
 function saveRegistrations($mysqli){
     try
     {
		$data = array();
        global $userid;
		$insertid = 0;
    
        $regid  = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
        $ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlMode  = ($_POST['ddlMode'] == 'undefined' || $_POST['ddlMode'] == '') ? '' : $_POST['ddlMode'];
        $ddlBrand  = ($_POST['ddlBrand'] == 'undefined' || $_POST['ddlBrand'] == '') ? 0 : $_POST['ddlBrand'];

        $txtFirstName  = $_POST['txtFirstName'] == 'undefined' ? '' : $_POST['txtFirstName'];
        $txtLastName  = $_POST['txtLastName'] == 'undefined' ? '' : $_POST['txtLastName'];
		$txtPhone  = $_POST['txtPhone'] == 'undefined' ? '' : $_POST['txtPhone'];
        $txtEmail  = $_POST['txtEmail'] == 'undefined' ? '' : $_POST['txtEmail'];
        $txtGrade  = $_POST['txtGrade'] == 'undefined' ? '' : $_POST['txtGrade'];
        $txtClassof  = ($_POST['txtClassof'] == 'undefined' || $_POST['txtClassof'] == '' || $_POST['txtClassof'] == 'null') ? 0 : $_POST['txtClassof'];
		$data['classof'] = $_POST['txtClassof'];
        $txtSchool  = $_POST['txtSchool'] == 'undefined' ? '' : $_POST['txtSchool'];
		
        $txtAddressL1  = $_POST['txtAddressL1'] == 'undefined' ? '' : $_POST['txtAddressL1'];
        $txtAddressL2  = $_POST['txtAddressL2'] == 'undefined' ? '' : $_POST['txtAddressL2'];
        $txtCity  = $_POST['txtCity'] == 'undefined' ? '' : $_POST['txtCity'];
        $txtState  = $_POST['txtState'] == 'undefined' ? '' : $_POST['txtState'];
        $txtZipCode  = $_POST['txtZipCode'] == 'undefined' ? '' : $_POST['txtZipCode'];
        $ddlCountry  = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];

        $txtP1_FName  = $_POST['txtP1_FName'] == 'undefined' ? '' : $_POST['txtP1_FName'];
        $txtP1_LName  = $_POST['txtP1_LName'] == 'undefined' ? '' : $_POST['txtP1_LName'];
        $txtP1_Phone  = $_POST['txtP1_Phone'] == 'undefined' ? '' : $_POST['txtP1_Phone'];
        $txtP1_Email  = $_POST['txtP1_Email'] == 'undefined' ? '' : $_POST['txtP1_Email'];
        
		$txtP2_FName  = $_POST['txtP2_FName'] == 'undefined' ? '' : $_POST['txtP2_FName'];
        $txtP2_LName  = $_POST['txtP2_LName'] == 'undefined' ? '' : $_POST['txtP2_LName'];
        $txtP2_Phone  = $_POST['txtP2_Phone'] == 'undefined' ? '' : $_POST['txtP2_Phone'];
        $txtP2_Email  = $_POST['txtP2_Email'] == 'undefined' ? '' : $_POST['txtP2_Email'];
        
        $txtAllergies  = $_POST['txtAllergies'] == 'undefined' ? '' : $_POST['txtAllergies'];
        $txtRefferedBy  = $_POST['txtRefferedBy'] == 'undefined' ? '' : $_POST['txtRefferedBy'];
        $txtHowFind  = $_POST['txtHowFind'] == 'undefined' ? '' : $_POST['txtHowFind'];
        $txtAdditionIntruc  = $_POST['txtAdditionIntruc'] == 'undefined' ? '' : $_POST['txtAdditionIntruc'];
        $Agreed = ($_POST['Agreed'] == 'undefined' || $_POST['Agreed'] == '') ? 0 : $_POST['Agreed'];
        
		
		$ENROLL_PLANID = ($_POST['ENROLL_PLANID'] == 'undefined' || $_POST['ENROLL_PLANID'] == '') ? 0 : $_POST['ENROLL_PLANID'];
		$BOOKEDBY = $_POST['BOOKEDBY'] == 'undefined' ? '' : $_POST['BOOKEDBY'];
		

		$selectedCourse = (isset($_POST['selectedCourse'])) ? json_decode($_POST['selectedCourse'],true) : array();
		$data['$selectedCourse']=$selectedCourse;
		// echo json_encode($data);exit;

		//Get PLAN NAME
		if($ENROLL_PLANID > 0){
			$queryPNAME="SELECT PLANNAME FROM PLANS WHERE PLANID=$ENROLL_PLANID AND ISDELETED=0";
			$resultPNAME=sqlsrv_query($mysqli, $queryPNAME);
			$rowPNAME=sqlsrv_fetch_array($resultPNAME);
			$PNAME = $rowPNAME['PLANNAME'];
		}else{
			$PNAME ='';
		}
		
		//Get LOCATION NAME
		$queryLNAME="SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=$ddlLocation AND ISDELETED=0";
		$resultLNAME=sqlsrv_query($mysqli, $queryLNAME);
		$rowLNAME=sqlsrv_fetch_array($resultLNAME);
		$LNAME = $rowLNAME['LOCATION'];
		
		//Get COUNTRY NAME
		$queryCNAME="SELECT COUNTRY FROM COUNTRIES WHERE ISDELETED=0 AND COUNTRYID=$ddlCountry";
		$resultCNAME=sqlsrv_query($mysqli, $queryCNAME);
		$rowCNAME=sqlsrv_fetch_array($resultCNAME);
		$CNAME = $rowCNAME['COUNTRY'];
		
		//Get LOCATION EMAIL
		$LOCEMAIL='';
		if($ddlLocation > 1){
			$queryLOCEMAIL="SELECT LOC_EMAIL FROM LOCATIONS WHERE ISDELETED=0 AND LOC_ID=$ddlLocation";
			$resultLOCEMAIL=sqlsrv_query($mysqli, $queryLOCEMAIL);
			$rowLOCEMAIL=sqlsrv_fetch_array($resultLOCEMAIL);
			$LOCEMAIL = $rowLOCEMAIL['LOC_EMAIL'];
		}
		
		if($BOOKEDBY == 'STUDENT'){
			$insertid = 0;}
		else{$insertid = $userid;}

		$actionid = $regid == 0 ? 1 : 2;


		if($ddlLocation == 0)
		{throw new Exception("Select Location.");}
        
		


			$query="EXEC [REGISTRATIONS_SP] $actionid,$regid,$ddlLocation,'$ddlMode',$ddlBrand,'$txtFirstName','$txtLastName','$txtPhone','$txtEmail','$txtGrade',$txtClassof,'$txtSchool',
	               '$txtAddressL1','$txtAddressL2','$txtCity','$txtState','$txtZipCode',$ddlCountry,'$txtAllergies',
				   '$txtP1_FName','$txtP1_LName','$txtP1_Phone','$txtP1_Email',
				   '$txtP2_FName','$txtP2_LName','$txtP2_Phone','$txtP2_Email',
				   '$txtRefferedBy','$txtHowFind','$txtAdditionIntruc',$Agreed,$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{

				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// GET REGID
				$row = sqlsrv_fetch_array($stmt);
				$row['REGID'] = (int) $row['REGID'];
				$REGID = $row['REGID'];
				$data['REGID'] = $REGID;

				
				// UPDATE LOGIN ID/PASSWORD
				if($BOOKEDBY == 'ADMIN' && $actionid==2){
					$txtLoginPwd = $_POST['txtLoginPwd'] == 'undefined' ? '' : $_POST['txtLoginPwd'];
					$txtLoginID = $_POST['txtLoginID'] == 'undefined' ? '' : $_POST['txtLoginID'];
					$UPD_PASS ="UPDATE REGISTRATIONS SET SPASSWORD=ENCRYPTBYASYMKEY(ASYMKEY_ID('MYEXAMPREPKEY'),'$txtLoginPwd'),LOGINID='$txtLoginID' WHERE REGID=$regid";
					sqlsrv_query($mysqli, $UPD_PASS);
				}


				// INSERT STUDENT PROPOSED COURSES
				$delCourse = "DELETE FROM STUDENT_PROPOSED_COURSES WHERE REGID=$REGID";
				sqlsrv_query($mysqli, $delCourse);
				if(count($selectedCourse)>0){
					foreach($selectedCourse as $Details){
						$GRADEID = $Details['GRADEID'];
						$CSUBID = $Details['CSUBID'];
						$FINAL_DRAFT = $Details['FINAL_DRAFT'];

						$insertCourse = "EXEC [STUDENT_PROPOSED_COURSES_SP] 1,0,$REGID,$GRADEID,$CSUBID,'$FINAL_DRAFT',$userid";
						sqlsrv_query($mysqli, $insertCourse);
					}
				}

				
				if($BOOKEDBY == 'STUDENT'){
					
						$REG_DETAIL ="EXEC[REGISTRATION_DETAILS_SP] 1,0,$REGID,$ENROLL_PLANID,$BOOKEDBY,'',$insertid";
						$stmtREG_DETAIL=sqlsrv_query($mysqli, $REG_DETAIL);
	
						if($stmtREG_DETAIL === false){
							$data['success'] = false;
							$data['REGID'] = $REGID;
							$data['REG_DETAIL'] = $REG_DETAIL;
							echo json_encode($data);exit;
						}
						
						if($actionid==1){
							sendMail('STUDENT',$PNAME,$LNAME,$CNAME,$LOCEMAIL);
							sendMail('MYEXAMPREP',$PNAME,$LNAME,$CNAME,$LOCEMAIL);
						}

						$data['query'] = $query;
						$data['success'] = true;
						if(!empty($regid))$data['message'] = 'Record successfully updated';
						else $data['message'] = 'Record successfully inserted.';
						echo json_encode($data);exit;
				}
					
					$data['query'] = $query;
					$data['success'] = true;
					if(!empty($regid))$data['message'] = 'Record successfully updated';
						else $data['message'] = 'Record successfully inserted.';
					echo json_encode($data);exit;
				}
			

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
/* ============ Save Data ============= */ 




// =============== SAVE EXCEL DATA ==================
function saveExcelFile($mysqli){
	try
	{
		$data = array();
		global $userid;
	
		$txtUploadExcel  = $_POST['txtUploadExcel'] == 'undefined' ? '' : $_POST['txtUploadExcel'];
		if($txtUploadExcel == ''){throw new Exception("Please Select Excel File.");}

		
		$filename=$_FILES["txtUploadExcelData"]["tmp_name"];
		if($_FILES["txtUploadExcelData"]["size"] > 0)
		{
			$file = fopen($filename, "r");
			$count = 0;
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
			{ 
				$count++;
				if($count>1){
					$data['$emapData'][]=$emapData;

					// SAVE REGISTRATION
					$query="EXEC [REGISTRATIONS_SP] 1,0,$emapData[0],'$emapData[1]',0,'$emapData[2]','$emapData[3]','$emapData[5]','$emapData[6]','$emapData[4]',0,'$emapData[7]',
						'$emapData[8]','$emapData[9]','$emapData[10]','$emapData[11]','$emapData[12]',$emapData[13],'$emapData[22]',
						'$emapData[14]','$emapData[15]','$emapData[16]','$emapData[17]',
						'$emapData[18]','$emapData[19]','$emapData[20]','$emapData[21]',
						'$emapData[23]','$emapData[24]','$emapData[25]',$emapData[26],$userid";
					sqlsrv_query($mysqli, $query);
				}  
			}
			fclose($file);
			$data['message'] = "<i class='fa fa-check'> Data successfully uploaded.";
			$data['success'] = true;
			echo json_encode($data);exit;
		}
		else {
			$data['success'] = false;
			$data['message'] = 'Upload error';
		}
		$data['message'] = 'failed Outside';
		$data['tmp_name']=$_FILES["txtUploadExcelData"]["tmp_name"];
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
// =============== SAVE EXCEL DATA ==============





/* ============ Send Mail ============= */ 
function sendMail($for,$PNAME,$LNAME,$CNAME,$LOCEMAIL){

		$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
        $ddlMode  = ($_POST['ddlMode'] == 'undefined' || $_POST['ddlMode'] == '') ? '' : $_POST['ddlMode'];

        $txtFirstName  = $_POST['txtFirstName'] == 'undefined' ? '' : $_POST['txtFirstName'];
        $txtLastName  = $_POST['txtLastName'] == 'undefined' ? '' : $_POST['txtLastName'];
		$txtPhone  = $_POST['txtPhone'] == 'undefined' ? '' : $_POST['txtPhone'];
        $txtEmail  = $_POST['txtEmail'] == 'undefined' ? '' : $_POST['txtEmail'];
        $txtGrade  = $_POST['txtGrade'] == 'undefined' ? '' : $_POST['txtGrade'];
        $txtClassof  = ($_POST['txtClassof'] == 'undefined' || $_POST['txtClassof'] == '') ? 0 : $_POST['txtClassof'];
        $txtSchool  = $_POST['txtSchool'] == 'undefined' ? '' : $_POST['txtSchool'];
		
        $txtAddressL1  = $_POST['txtAddressL1'] == 'undefined' ? '' : $_POST['txtAddressL1'];
        $txtAddressL2  = $_POST['txtAddressL2'] == 'undefined' ? '' : $_POST['txtAddressL2'];
        $txtCity  = $_POST['txtCity'] == 'undefined' ? '' : $_POST['txtCity'];
        $txtState  = $_POST['txtState'] == 'undefined' ? '' : $_POST['txtState'];
        $txtZipCode  = $_POST['txtZipCode'] == 'undefined' ? '' : $_POST['txtZipCode'];
        $ddlCountry  = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];

        $txtP1_FName  = $_POST['txtP1_FName'] == 'undefined' ? '' : $_POST['txtP1_FName'];
        $txtP1_LName  = $_POST['txtP1_LName'] == 'undefined' ? '' : $_POST['txtP1_LName'];
        $txtP1_Phone  = $_POST['txtP1_Phone'] == 'undefined' ? '' : $_POST['txtP1_Phone'];
        $txtP1_Email  = $_POST['txtP1_Email'] == 'undefined' ? '' : $_POST['txtP1_Email'];
        
		$txtP2_FName  = $_POST['txtP2_FName'] == 'undefined' ? '' : $_POST['txtP2_FName'];
        $txtP2_LName  = $_POST['txtP2_LName'] == 'undefined' ? '' : $_POST['txtP2_LName'];
        $txtP2_Phone  = $_POST['txtP2_Phone'] == 'undefined' ? '' : $_POST['txtP2_Phone'];
        $txtP2_Email  = $_POST['txtP2_Email'] == 'undefined' ? '' : $_POST['txtP2_Email'];
        
        $txtAllergies  = $_POST['txtAllergies'] == 'undefined' ? '' : $_POST['txtAllergies'];
        $txtRefferedBy  = $_POST['txtRefferedBy'] == 'undefined' ? '' : $_POST['txtRefferedBy'];
        $txtHowFind  = $_POST['txtHowFind'] == 'undefined' ? '' : $_POST['txtHowFind'];
        $txtAdditionIntruc  = $_POST['txtAdditionIntruc'] == 'undefined' ? '' : $_POST['txtAdditionIntruc'];
		$ENROLL_PLANID = ($_POST['ENROLL_PLANID'] == 'undefined' || $_POST['ENROLL_PLANID'] == '') ? 0 : $_POST['ENROLL_PLANID'];

	$data = array();
	$STmails = array();

	if($for == 'STUDENT'){
		$STmails = array(
			$txtEmail => $txtFirstName,
			$txtP1_Email => $txtP1_FName,
			$txtP2_Email => $txtP2_FName,
		);
	}else{
		if($ddlLocation > 1){
			$STmails = array(
				'info@myexamsprep.com' => 'HQ',
				$LOCEMAIL => $LNAME,
			);
		}else{
			$STmails = array(
				'info@myexamsprep.com' => 'HQ',
			);
		}
	}
	


	
	
	$msg = "";

			if($for == 'STUDENT'){
				$msg .= "Hello,<br/>
						We’re writing to confirm that we’ve received your registration form. Our team is working on this and will be in with you touch soon! <br/>
								
						Thanks <br/>
						MyExamsPrep";
			}
			else{

				$msg .="
							<div style='border:1px solid #DCEAEB'>
							<h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:5px 25px; margin:0px; background:#D8ECF5; color: #628fa2'>Enroll For $PNAME</h1>
						
							<table style='font-family:Arial; margin: 25px 40px; width: 90%;'>
								<tr>
									<th colspan='3' style='text-align:left; padding:15px 0px;'>Location</th>
								</tr>
								<tr>
									<td style='width:100px;'>Location</td><td style='width:10px'>:</td><td>$LNAME</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Mode</td><td style='width:10px'>:</td><td>$ddlMode</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
					
					
					
								<tr>
									<th colspan='3' style='text-align:left; padding:15px 0px;'>Student Details</th>
								</tr>
								<tr>
									<td style='width:100px;'>Student</td><td style='width:10px'>:</td><td>".$txtFirstName. ' ' .$txtLastName."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Mobile</td><td style='width:10px'>:</td><td>".$txtPhone."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
						
								<tr>
									<td style='width:100px;'>Email</td><td style='width:10px'>:</td><td><a href='mailto:".$txtEmail."' style='color:#118bf2; text-decoration:none'>".$txtEmail."</a></td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Grade</td><td style='width:10px'>:</td><td>".$txtGrade."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Class of</td><td style='width:10px'>:</td><td>".$txtClassof."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>School</td><td style='width:10px'>:</td><td>".$txtSchool."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Address1</td><td style='width:10px'>:</td><td>".$txtAddressL1."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Address2</td><td style='width:10px'>:</td><td>".$txtAddressL2."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
							
								<tr>
									<td style='width:100px;'>City/State</td><td style='width:10px'>:</td><td>".$txtCity." / ".$txtState."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Zip Code</td><td style='width:10px'>:</td><td>".$txtZipCode."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Country</td><td style='width:10px'>:</td><td>".$CNAME."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
					
					
					
					
								<tr>
									<th colspan='3' style='text-align:left; padding:15px 0px;'>Parent 1 / Guardian (tuition responsible party)</th>
								</tr>
					
								<tr>
									<td style='width:100px;'>Name</td><td style='width:10px'>:</td><td>".$txtP1_FName." ".$txtP1_LName."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Mobile</td><td style='width:10px'>:</td><td>".$txtP1_Phone."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Email</td><td style='width:10px'>:</td><td>".$txtP1_Email."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
					
					
					
					
					
								<tr>
									<th colspan='3' style='text-align:left; padding:15px 0px;'>Parent 2 / Guardian</th>
								</tr>
								<tr>
									<td style='width:100px;'>Name</td><td style='width:10px'>:</td><td>".$txtP2_FName." ".$txtP2_LName."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Mobile</td><td style='width:10px'>:</td><td>".$txtP2_Phone."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:100px;'>Email</td><td style='width:10px'>:</td><td>".$txtP2_Email."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								
					
					
								
								<tr>
									<th colspan='3' style='text-align:left; padding:15px 0px;'>Other Details</th>
								</tr>
								<tr>
									<td style='width:150px;'>Allergies if Any</td><td style='width:10px'>:</td><td>".$txtAllergies."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:150px;'>Reffered By</td><td style='width:10px'>:</td><td>".$txtRefferedBy."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:200px;'>How did you find us</td><td style='width:10px'>:</td><td>".$txtHowFind."</td>
								</tr>
								<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td></tr>
								
								<tr>
									<td style='width:200px;'>Additional Instructions</td><td style='width:10px'>:</td><td>".$txtAdditionIntruc."</td>
								</tr>
									<tr><td colspan='3' style='height:10px;'><div style='border-top:1px solid silver'></div></td>
								</tr>
					
							</table>
							
						</div>
				";
			}


				// MAIL
				$mail = new PHPMailer;
				$mail->isSMTP(); 
				$mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
				$mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
				$mail->Port = 587; // TLS only 587
				$mail->SMTPSecure = 'tls'; // ssl is depracated
				$mail->SMTPAuth = true;
				$mail->Username = "no.reply.myexamsprep@gmail.com";
				$mail->Password = "xagdmidhhtzijcgt";
				$mail->setFrom("no.reply.myexamsprep@gmail.com", "MyExamsPrep");
				// $mail->addAddress($Email, $FirstName);
				$mail->Subject = 'myexamsprep:Registration';
				$mail->msgHTML($msg); 
				//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
				$mail->AltBody = 'HTML messaging not supported';
				// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file


				foreach($STmails as $email => $name){
					$mail->AddCC($email, $name);
				}

				if(!$mail->send()){
					// echo "Mailer Error: " . $mail->ErrorInfo;
					$data['Mail_ST'] = "Message sent!";
				}
				else{
					// echo "Message sent!";
					$data['Mail_ST'] = $mail->ErrorInfo;
				}
}
/* ============ Send Mail ============= */ 






/* ========== GET STUDENT PROPOSED COURSES =========== */
function getStudentProposedCourses($mysqli){
	try
	{
		global $locid,$isET;
		$data = array();
		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		if($REGID==0)throw new Exception('REGID Not Found.');
		$query = "SELECT GRADEID,(SELECT GRADE FROM GRADES_MASTER WHERE GRADEID=SPC.GRADEID)GRADE,
					CSUBID,(SELECT SHORT_DESC FROM CLASS_SUBJECT_MASTER WHERE CSUBID=SPC.CSUBID)[SUBJECT],FINAL_DRAFT 
					FROM STUDENT_PROPOSED_COURSES SPC WHERE ISDELETED=0 AND REGID=$REGID";
		$data['$query'] = $query;
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['GRADEID'] = (string) $row['GRADEID'];
				$row['CSUBID'] = (string) $row['CSUBID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
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
/* ========== GET STUDENT PROPOSED COURSES =========== */





/*============ Get Location =============*/ 
function getLocation($mysqli){
	try
	{
		global $locid,$isET;
		$data = array();
		$query = "SELECT LOC_ID,[LOCATION] FROM LOCATIONS WHERE ISDELETED=0";
		if($isET==0 && $locid>0)$query .= " AND LOC_ID=$locid";
		$data['$query'] = $query;
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
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
/*============ Get Location =============*/ 






/*============ Get Countries =============*/ 
function getCountries($mysqli){
	try
	{
		$query = "SELECT COUNTRYID,COUNTRY,COUNTRY_SC FROM COUNTRIES WHERE ISDELETED=0";
		
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['COUNTRYID'] = (int) $row['COUNTRYID'];
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
/*============ Get Countries =============*/ 






/*============ Get Registrations =============*/ 
function getRegistrations($mysqli){
	try
	{
		$data = array();
		$ddlLocationSearch = ($_POST['ddlLocationSearch'] =='undefined' || $_POST['ddlLocationSearch'] =='') ? 0 : $_POST['ddlLocationSearch'];
		$ddlGradeSearch = ($_POST['ddlGradeSearch'] =='undefined' || $_POST['ddlGradeSearch'] =='') ? 0 : $_POST['ddlGradeSearch'];
		$ddlClassSubjectSearch = ($_POST['ddlClassSubjectSearch'] =='undefined' || $_POST['ddlClassSubjectSearch'] =='') ? 0 : $_POST['ddlClassSubjectSearch'];
		$ddlFinalDraftSearch = ($_POST['ddlFinalDraftSearch'] =='undefined' || $_POST['ddlFinalDraftSearch'] =='') ? '' : $_POST['ddlFinalDraftSearch'];
		$txtFromDT_ST = ($_POST['txtFromDT_ST'] =='undefined' || $_POST['txtFromDT_ST'] =='') ? '' : $_POST['txtFromDT_ST'];
		$txtToDT_ST = ($_POST['txtToDT_ST'] =='undefined' || $_POST['txtToDT_ST'] =='') ? '' : $_POST['txtToDT_ST'];

		$query = "SELECT REGID,LOCATIONID,MODE,BRANDID,ISNULL((SELECT BRANDNAME FROM BRANDS WHERE BRANDID=R.BRANDID),'')BRANDNAME,
		(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOCATION,
		FIRSTNAME,LASTNAME,
		PHONE,ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE_F,
		EMAIL,ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL_F,
		GRADE,SCHOOL,
		ADDRESSLINE1,ADDRESSLINE2,CITY,STATE,ZIPCODE,COUNTRYID,
		(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=R.COUNTRYID)COUNTRY,
		ALLERGIES,P1_FIRSTNAME,P1_LASTNAME,
		P1_PHONE,ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE_F,
		P1_EMAIL,ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL_F,
		P2_FIRSTNAME,P2_LASTNAME,
		P2_PHONE,ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE_F,
		P2_EMAIL,ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL_F,
		REFERREDBY,FINDUS,INSTRUCTIONS,AGREED,CONVERT(VARCHAR,INSERTDATE,107)INSERTDATE,
		(select planname+ ' | '  from plans where planid in (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=R.REGID AND CANCELLED=0 ) for xml path('')) ACTIVE_PLANS,
		DBO.GET_CLEAR_STUDENT_PASSWORD(REGID) LOGIN_PWD,LOGINID,
		CLASSOF
		FROM REGISTRATIONS R WHERE ISDELETED=0 AND ARCHIVED=0 AND LOCATIONID=$ddlLocationSearch";

		// if($_SESSION['USER_LOCID'] != '1'){
		// 	$query .=" AND LOCATIONID=".$_SESSION['USER_LOCID']."";
		// }
		if($txtFromDT_ST!=='' && $txtToDT_ST !==''){
			$query .=" AND CONVERT(DATE,INSERTDATE,105) BETWEEN '$txtFromDT_ST' AND '$txtToDT_ST'";
		}
		if($ddlGradeSearch > 0 && $ddlClassSubjectSearch <= 0 && $ddlFinalDraftSearch == ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE GRADEID=$ddlGradeSearch)";
		} 
		else if($ddlGradeSearch > 0 && $ddlClassSubjectSearch > 0 && $ddlFinalDraftSearch == ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE GRADEID=$ddlGradeSearch AND CSUBID=$ddlClassSubjectSearch)";
		}
		else if($ddlGradeSearch > 0 && $ddlClassSubjectSearch > 0 && $ddlFinalDraftSearch != ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE GRADEID=$ddlGradeSearch AND CSUBID=$ddlClassSubjectSearch AND FINAL_DRAFT='$ddlFinalDraftSearch')";
		}

		else if($ddlGradeSearch < 0 && $ddlClassSubjectSearch > 0 && $ddlFinalDraftSearch != ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE CSUBID=$ddlClassSubjectSearch AND FINAL_DRAFT='$ddlFinalDraftSearch')";
		}

		else if($ddlGradeSearch < 0 && $ddlClassSubjectSearch < 0 && $ddlFinalDraftSearch != ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE FINAL_DRAFT='$ddlFinalDraftSearch')";
		}
		
		else if($ddlGradeSearch < 0 && $ddlClassSubjectSearch > 0 && $ddlFinalDraftSearch == ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE CSUBID=$ddlClassSubjectSearch)";
		}
		
		else if($ddlGradeSearch > 0 && $ddlClassSubjectSearch < 0 && $ddlFinalDraftSearch != ''){
			$query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE GRADEID=$ddlGradeSearch AND FINAL_DRAFT='$ddlFinalDraftSearch')";
		}

		// $query.= ($ddlGradeSearch>0 && $ddlClassSubjectSearch > 0) ? " OR" : ($ddlClassSubjectSearch > 0 ? " AND" : "");
		// if($ddlClassSubjectSearch > 0) $query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE CSUBID=$ddlClassSubjectSearch)";
		// $query.= (($ddlClassSubjectSearch > 0 || $ddlGradeSearch > 0) && $ddlFinalDraftSearch!='') ? " OR" : ($ddlFinalDraftSearch!='' ? " AND" : "");
		// if($ddlFinalDraftSearch != '') $query .=" AND REGID IN (SELECT REGID FROM STUDENT_PROPOSED_COURSES WHERE FINAL_DRAFT='$ddlFinalDraftSearch')";

		$query .=" ORDER BY REGID DESC";

		$data['query']=$query;
		// $data['success'] = true;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		
		while ($row = sqlsrv_fetch_array($result)) {
			$row['REGID'] = (int) $row['REGID'];
			$row['ACTIVE_PLANS'] =  rtrim($row['ACTIVE_PLANS'], "| ");

			$row['FINAL_PHONE'] = '';
			$row['FINAL_EMAIL'] = '';

			// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
			$row['PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE_F']);
			$row['PHONE_F'] = preg_match('/^[0-9]+$/', $row['PHONE_F']) ? $row['PHONE_F'] : '';
			$row['PHONE_F'] = is_numeric($row['PHONE_F']) ? $row['PHONE_F'] : '';
			if(strlen($row['PHONE_F']) > 0) $row['FINAL_PHONE'] .= $row['PHONE_F'].', ';

			if (filter_var($row['EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
				if(strlen($row['EMAIL_F']) > 0 && $row['EMAIL_F']!='NaN') $row['FINAL_EMAIL'] .= $row['EMAIL_F'].', ';
			}
			
			// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
			
			// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
			$row['P1_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE_F']);
			$row['P1_PHONE_F'] = preg_match('/^[0-9]+$/', $row['P1_PHONE_F']) ? $row['P1_PHONE_F'] : '';
			$row['P1_PHONE_F'] = is_numeric($row['P1_PHONE_F']) ? $row['P1_PHONE_F'] : '';
			if(strlen($row['P1_PHONE_F']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE_F'].', ';

			if (filter_var($row['P1_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
				if(strlen($row['P1_EMAIL_F']) > 0 && $row['P1_EMAIL_F']!='NaN') $row['FINAL_EMAIL'] .= $row['P1_EMAIL_F'].', ';
			}
			// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
			
			// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
			$row['P2_PHONE_F'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE_F']);
			$row['P2_PHONE_F'] = preg_match('/^[0-9]+$/', $row['P2_PHONE_F']) ? $row['P2_PHONE_F'] : '';
			$row['P2_PHONE_F'] = is_numeric($row['P2_PHONE_F']) ? $row['P2_PHONE_F'] : '';
			if(strlen($row['P2_PHONE_F']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE_F'].', ';

			if (filter_var($row['P2_EMAIL_F'], FILTER_VALIDATE_EMAIL)) {
				if(strlen($row['P2_EMAIL_F']) > 0 && $row['P2_EMAIL_F']!='NaN') $row['FINAL_EMAIL'] .= $row['P2_EMAIL_F'].', ';
			}
			// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

			$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
			$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));
			$row['FINAL_PHONE'] = str_replace(', ', ",\n", $row['FINAL_PHONE']);

			$row['FINAL_EMAIL'] = rtrim($row['FINAL_EMAIL'],', ');
			$row['FINAL_EMAIL'] = implode(", ",array_unique(explode(", ",$row['FINAL_EMAIL'])));
			$row['FINAL_EMAIL'] = str_replace(', ', ",\n", $row['FINAL_EMAIL']);

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
/*============ Get Registrations =============*/ 





/*============ Get Student Plans =============*/ 
function getPlans($mysqli){
	try
	{
		$regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];

		$query = "SELECT REGDID,PLANID,
		(SELECT PLANNAME FROM PLANS WHERE ISDELETED=0 AND PLANID=RD.PLANID)PLANNAME,
		(SELECT CONVERT(VARCHAR,STARTDATE,107) FROM PLANS WHERE ISDELETED=0 AND PLANID=RD.PLANID)STARTDATE,
		(SELECT CONVERT(VARCHAR,ENDDATE,107) FROM PLANS WHERE ISDELETED=0 AND PLANID=RD.PLANID)ENDDATE,
		(SELECT PRICE FROM PLANS WHERE ISDELETED=0 AND PLANID=RD.PLANID)PRICE,
		(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=RD.REGID AND ISDELETED=0))LOCATION,
		(SELECT FLAG_ICON FROM COUNTRIES WHERE COUNTRYID=((SELECT LOC_COUNTRY FROM LOCATIONS WHERE LOC_ID=(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=RD.REGID AND ISDELETED=0) AND ISDELETED=0)) AND ISDELETED=0 ) CFLAG,
		(SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=RD.REGID AND ISDELETED=0) AND ISDELETED=0)) CURRENCY_SYMBOL,
		(SELECT WEEKDAYNAME + ' ' + 
		LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=RD.REGID) AND ISDELETED=0),0),TIMEFROM_ET),8),5) 
		+ '-' + 
		LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=(SELECT LOCATIONID FROM REGISTRATIONS WHERE REGID=RD.REGID) AND ISDELETED=0),0),TIMETO_ET),8),5) 
		+'  |  '
		FROM PLAN_SCHEDULE WHERE PLANID=RD.PLANID AND ISDELETED=0 
		ORDER BY (select dbo.GetWeekDay(WEEKDAYNAME)) FOR XML PATH('')) SCHEDULE
		FROM REGISTRATION_DETAILS RD WHERE CANCELLED=0 AND REGID=$regid";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['REGDID'] = (int) $row['REGDID'];
			$row['PLANID'] = (int) $row['PLANID'];
			$row['SCHEDULE'] = rtrim($row['SCHEDULE'], ' | ');
			$data['data'][] = $row;
		}
		$data['query'] = $query;
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
/*============ Get Student Plans =============*/ 






/*============ Get Product Plans =============*/ 
function getProductPlans($mysqli){
	try
	{
		$ddlCourse = ($_POST['ddlCourse'] == 'undefined' || $_POST['ddlCourse'] == '') ? 0 : $_POST['ddlCourse'];

		$query = "SELECT PLANID,(SELECT DISPLAYCOLOR FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)DISPLAYCOLOR,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=PD.PLANID AND ISDELETED=0)[PLAN] FROM PRODUCT_DISPLAY_DETAIL PD WHERE PDMID=$ddlCourse AND ISDELETED=0 AND PLANID NOT IN(SELECT PLANID FROM PLANS WHERE ISDELETED=1)";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['PLANID'] = (int) $row['PLANID'];
			$data['data'][] = $row;
		}
		$data['query'] = $query;
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
/*============ Get Product Plans =============*/ 





/*============ Get Plan all details =============*/ 
function getAllPlanDetail($mysqli){
	try
	{
		$PLANID = ($_POST['ddlplan'] == 'undefined' || $_POST['ddlplan'] == '') ? 0 : $_POST['ddlplan'];
		$locid = ($_POST['locid'] == 'undefined' || $_POST['locid'] == '') ? 0 : $_POST['locid'];

		$query = "SELECT PLANID,LOCATIONID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0) LOC,
		(SELECT FLAG_ICON FROM COUNTRIES WHERE COUNTRYID=((SELECT LOC_COUNTRY FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) AND ISDELETED=0 ) CFLAG,
		(SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) CURRENCY_CODE,
		(SELECT CURRENCY_CLASS FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) CURRENCY_SYMBOL,
		CASE WHEN (SELECT MULTIPLY FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0)) =1 THEN
				(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID)*(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
			ELSE 
				(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID)/(SELECT FACTOR  FROM CURRENCY_MASTER WHERE CURRENCY_ID=(SELECT CURRENCY_ID FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0))	
			END COST,
		(SELECT PRICE FROM PLANS WHERE PLANID=$PLANID) PRICE,
		(SELECT CONVERT(VARCHAR,STARTDATE,107)STARTDATE FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0) START_DATE ,
		(SELECT CONVERT(VARCHAR,ENDDATE,107)ENDDATE FROM PLANS WHERE PLANID=PL.PLANID AND ISDELETED=0) END_DATE,
		(SELECT PS.WEEKDAYNAME + ' ' + 
		LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMEFROM_ET),8),5) 
		+ '-' + 
		LEFT(CONVERT(VARCHAR,DATEADD(HH,ISNULL((SELECT CAST(LOC_ET_DIFF AS INT) FROM LOCATIONS WHERE LOC_ID=PL.LOCATIONID AND ISDELETED=0),0),TIMETO_ET),8),5) 
		+'  |  '
		FROM PLAN_SCHEDULE PS WHERE PS.PLANID=$PLANID AND PS.ISDELETED=0 
		ORDER BY (select dbo.GetWeekDay(PS.WEEKDAYNAME)) FOR XML PATH('')) SCHEDULE
		FROM PLAN_LOCATIONS PL WHERE PLANID=$PLANID AND ISDELETED=0 AND LOCATIONID=$locid";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['LOCATIONID'] = (int) $row['LOCATIONID'];
			$row['SCHEDULE'] = rtrim($row['SCHEDULE'], ' | ');
			$data['data'][] = $row;
		}
		$data['query'] = $query;
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
/*============ Get Plan all details =============*/ 





/*============ Enroll Student Plan By Admin =============*/ 
function EnrollStudent_PlanByAdmin($mysqli){
	try
	{
	   $data = array();
	   global $userid;

   
	   $ddlplan  = ($_POST['ddlplan'] == 'undefined' || $_POST['ddlplan'] == '') ? 0 : $_POST['ddlplan'];
	   $regid  = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];
	   $txtEnrollRemark  = ($_POST['txtEnrollRemark'] == 'undefined' || $_POST['txtEnrollRemark'] == '') ? '' : $_POST['txtEnrollRemark'];


	   if($regid == 0)
	   {throw new Exception("Something Went Wrong.");}

	   $sql = "SELECT * FROM REGISTRATION_DETAILS WHERE PLANID=$ddlplan AND REGID=$regid AND CANCELLED=0";
	   $row_count = unique($sql);

	//    $data['sql'] = $sql;
	//    echo json_encode($data);exit;
	   $data = array();
	   if($row_count <= 0)
	   {

			$EnrollPlan ="EXEC[REGISTRATION_DETAILS_SP] 1,0,$regid,$ddlplan,'ADMIN','$txtEnrollRemark',$userid";
			$stmtEnrollPlan=sqlsrv_query($mysqli, $EnrollPlan);

			if($stmtEnrollPlan === false){
				$data['success'] = false;
				$data['EnrollPlan'] = $EnrollPlan;
				echo json_encode($data);exit;
			}
			else{

				$data['EnrollPlan'] = $EnrollPlan;
				$data['success'] = true;
				// if(!empty($regid))$data['message'] = 'Record successfully updated';
				// else $data['message'] = 'Record successfully inserted.';
				$data['message'] = 'Plan successfully enrolled.';
				echo json_encode($data);exit;
			}
		   
		   
	   }
	   else
	   {
	   	$data['success'] = false;
	   	$data['sql'] = $sql;
	   	$data['message'] = 'Plan already exists';
	   	echo json_encode($data);exit;
	   }

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
/*============ Enroll Student Plan By Admin =============*/ 






/* =========== Cancel Plan =========== */ 
function CancelPlan($mysqli){
	try{   
			global $userid;
			$data = array();     
            $regdid = ($_POST['regdid'] == 'undefined' || $_POST['regdid'] == '') ? 0 : $_POST['regdid'];  
            $remark = $_POST['remark'] == 'undefined' ? '' : $_POST['remark']; 
			$cancelQuery ="EXEC [REGISTRATION_DETAILS_CANCEL] $regdid,'$remark',$userid";
			$stmt=sqlsrv_query($mysqli, $cancelQuery);
			// $stmt=sqlsrv_query($mysqli, "UPDATE REGISTRATIONS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE REGID=$regid");
			if( $stmt === false ) 
			{
				// die( print_r( sqlsrv_errors(), true));
				// throw new Exception( $mysqli->sqlstate );
				$data['cancelQuery'] = $cancelQuery;
				$data['success'] = false;
				echo json_encode($data);exit;
			}
			else
			{
				$data['cancelQuery'] = $cancelQuery;
				$data['success'] = true;
				$data['message'] = 'Plan successfully cancelled.';
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
/* =========== Cancel Plan =========== */






/* =========== Delete =========== */ 
function deleteRegistration($mysqli){
	try{   
			global $userid;
			$data = array();     
            $regid = ($_POST['regid'] == 'undefined' || $_POST['regid'] == '') ? 0 : $_POST['regid'];  
			// $stmt=sqlsrv_query($mysqli, "EXEC [LOCATIONS_SP] 3,$locid,'',0,'','',0,$userid");
			$stmt=sqlsrv_query($mysqli, "UPDATE REGISTRATIONS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE REGID=$regid");
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$stmt2=sqlsrv_query($mysqli, "UPDATE REGISTRATION_DETAILS SET CANCELLED=1,CANCELID=$userid,CANCELDATE=GETDATE() WHERE REGID=$regid");

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
/* =========== Delete =========== */ 




















// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SMS / EMAIL %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// =============== SAVE SMS DATA ==================
function saveDataSms($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$ContactData = (!empty($_POST['StudentData'])) ? $_POST['StudentData'] : '';
		if($ContactData == '')throw new Exception('Student Data Not Found.');
		$ContactData =json_decode($ContactData,true);
		$data['$ContactData']=$ContactData;
		if(!$ContactData || count($ContactData)==0)throw new Exception('Select Student First.');
		for($i=0; $i<count($ContactData); $i++){
			$data['FINAL'][]= str_replace("\n","",$ContactData[$i]['FINAL_PHONE']);
			// $ContactData = str_replace("'","''",$_POST['txtMessage']);
		}
		// echo json_encode($data);exit;	
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);
		
		
		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($ContactData) <= 0)throw new Exception('Select Student First.');
		if(count($ContactData) == 1){
			$p = $ContactData[0]['FINAL_PHONE'];
			if(!$p || $p=='') throw new Exception('Selected Student Number Not Found.');
		}


		for($i=0; $i<count($ContactData); $i++){
			$REGID = $ContactData[$i]['REGID'];
			$FIRSTNAME = $ContactData[$i]['FIRSTNAME'];
			$LASTNAME = $ContactData[$i]['LASTNAME'];
			$FINAL_PHONE = $ContactData[$i]['FINAL_PHONE'];

			if($FINAL_PHONE && $FINAL_PHONE!='' && strlen($FINAL_PHONE) > 0){
				// $query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] '',$CID,'$FIRSTNAME','','$FINAL_PHONE','$txtMessage','REGISTRATION_SMS',$userid";
				$query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] $locid,'Registered',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_PHONE','$txtMessage','REGISTRATION_SMS',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
				}
				else
				{
					// GET MSGID
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$GET_MSGID = (int) $row['MSGID'];
	
	
					// MESSAGE
					$account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
					$auth_token = 'ad245122998781f49da657a66141cad6';
					$twilio_number = "+17039910242";
					

					// EXPLODE PHONE NUMBER
					$FINAL_PHONE = str_replace("\n","",$FINAL_PHONE);
					$data['$FINAL_PHONE'][] = $FINAL_PHONE;
					$NUM = explode(",",$FINAL_PHONE);

					forEach($NUM as $value){
						$data['num'][] = $value;
						
						if($value!='' && $value){
							$client = new Client($account_sid, $auth_token);
							try{
								$client->messages->create(
									// Where to send a text message (your cell phone?)
									// '+17035653342'
									$value,
									array(
										'from' => $twilio_number,
										'body' => $txtMessage
									)
								);

								// INSERT DETAILS
								$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
								VALUES($GET_MSGID,'$value','SUCCESS')";
								$stmt2=sqlsrv_query($mysqli, $query2);
								$data['query2'][] = $query2;
								$data['success'] = true;

							}catch (\Twilio\Exceptions\RestException $e) {
								$error_msg=$e->getMessage();
								$error_msg = str_replace("'","''",$error_msg);
								// INSERT DETAILS
								$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
								VALUES($GET_MSGID,'$value','ERROR','$error_msg')";
								$stmt2=sqlsrv_query($mysqli, $query2);
								$data['query2'][] = $query2;

								// echo "Error sending SMS: ".$e->getCode() . ' : ' . $e->getMessage()."\n";
								$data['success'] = false;
								// $data['error'] = var_dump($e);
								$data['message'] = $e->getMessage();
							}
						}
					}
					// try{
					// 	$client->messages->create(
					// 		// Where to send a text message (your cell phone?)
					// 		// '+17035653342'
					// 		$FINAL_PHONE,
					// 		array(
					// 			'from' => $twilio_number,
					// 			'body' => $txtMessage
					// 		)
					// 	);

					// 	// INSERT DETAILS
					// 	$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
					// 	VALUES($GET_MSGID,'$FINAL_PHONE','SUCCESS')";
					// 	$stmt2=sqlsrv_query($mysqli, $query2);
					// 	$data['query2'][] = $query2;
					// 	$data['success'] = true;

					// }catch (\Twilio\Exceptions\RestException $e) {
					// 	$data['$e'] = $e;
					// 	$error_msg=$e->getMessage();
					// 	$error_msg = str_replace("'","''",$error_msg);
					// 	// INSERT DETAILS
					// 	$query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS,REMARK)
					// 	VALUES($GET_MSGID,'$FINAL_PHONE','ERROR','$error_msg')";
					// 	$stmt2=sqlsrv_query($mysqli, $query2);
					// 	$data['query2'][] = $query2;

					// 	// echo "Error sending SMS: ".$e->getCode() . ' : ' . $e->getMessage()."\n";
					// 	$data['success'] = false;
					// 	// $data['error'] = var_dump($e);
					// 	$data['message'] = $e->getMessage();
					// }

					$data['querySuccess'][] = $query;
	
				}
			}
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
 // =============== SAVE SMS DATA ==============



// =============== SAVE EMAIL DATA ==================
function saveDataEmail($mysqli){
	try
	{
		global $userid,$locid;
		$data = array();

		$ContactData = (!empty($_POST['StudentData'])) ? $_POST['StudentData'] : '';
		if($ContactData == '')throw new Exception('Contact Data Not Found.');
		$ContactData =json_decode($ContactData,true);
		$data['$ContactData']=$ContactData;
		if(!$ContactData || count($ContactData)==0)throw new Exception('Select Student First.');
		for($i=0; $i<count($ContactData); $i++){
			$data['FINAL'][]= str_replace("\n","",$ContactData[$i]['FINAL_EMAIL']);
			// $ContactData = str_replace("'","''",$_POST['txtMessage']);
		}
		// echo json_encode($data);exit;
		
		$txtMessage = ($_POST['txtMessage'] == 'undefined' || $_POST['txtMessage'] == '') ? '' : str_replace("'","''",$_POST['txtMessage']);

		if($txtMessage == ''){throw new Exception("Please Enter 'Message'.");}
		if(count($ContactData) <= 0)throw new Exception('Select Student First.');
		if(count($ContactData) == 1){
			$p = $ContactData[0]['FINAL_EMAIL'];
			if(!$p || $p=='') throw new Exception('Selected Student Email Id Not Found.');
			// if(!filter_var($p, FILTER_VALIDATE_EMAIL)) throw new Exception("Selected Student Email Id Not Valid.");
		}

		$txtAttachment="";
		$data['$_FILES']=$_FILES;
		// $data['$_FILES_25']=formatSizeUnits(26214400); // 25 MB
		
		if(isset($_FILES['txtAttachment']['name']) && $_FILES['txtAttachment']['size'] > 0){

			$data['$_FILES_size']=formatSizeUnits($_FILES['txtAttachment']['size']);
			if($_FILES['txtAttachment']['size'] > 26214400) throw new Exception('File size limit of 25MB.');

			$ext = pathinfo($_FILES['txtAttachment']['name'],PATHINFO_EXTENSION);
			$txtAttachment .= strtolower(time().'.'.$ext);
		}
		else
		{
			$txtAttachment="";
		}
		$data['$txtAttachment']=$txtAttachment;
		// echo json_encode($data);exit;



		$msg = "";
		$msg .="
				<div style='border: 1px solid #DCEAEB; background: #ffd82b24;'>
					<h1 style='font-family:Arial; font-size:17px; font-weight:normal; padding:5px 25px; margin:0px; background:#ffd82b; color: #615136; font-weight: 800;'>MyExamsPrep</h1>
						
					<p style='padding: 0px 20px; font-family: system-ui;'>$txtMessage</p>			
				</div>
		";
		
		// echo json_encode($data);exit;
		
		for($i=0; $i<count($ContactData); $i++){
			$REGID = $ContactData[$i]['REGID'];
			$FIRSTNAME = $ContactData[$i]['FIRSTNAME'];
			$LASTNAME = $ContactData[$i]['LASTNAME'];
			$FINAL_EMAIL = $ContactData[$i]['FINAL_EMAIL'];
			if($FINAL_EMAIL!=''){
				// $query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] '',$CID,'$FIRSTNAME','','$FINAL_EMAIL','$txtMessage','CONTACTUS_EMAIL',$userid";
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $locid,'Registered',$REGID,'$FIRSTNAME','$LASTNAME','$FINAL_EMAIL','$txtMessage','REGISTRATION_EMAIL',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
				}
				else
				{
					// ########### SAVE IMAGE IN FOLDER ###########
						$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
						$GET_EMID = (int)$row['EMID'];
		
						if($txtAttachment != ''){
		
							if($GET_EMID > 0)
							{
								$insertimage = "UPDATE TEXT_EMAIL SET ATTACHMENT='$txtAttachment' WHERE EMID=$GET_EMID";
								sqlsrv_query($mysqli,$insertimage);
							}
			
			
							if(isset($_FILES['txtAttachment']['name']) && $_FILES['txtAttachment']['size'] > 0)
							{
								move_uploaded_file($_FILES["txtAttachment"]["tmp_name"], '../mail_attachment_images/'.$txtAttachment);
							}
		
							// UPDATE SECTION
		
							// if(isset($_FILES['txtAttachment']['name']) && $existingCatImage != '')
							// {
							// 	if (file_exists('../gallery_images/'.$existingCatImage))
							// 	{
							// 		unlink('../gallery_images/'.$existingCatImage);
							// 	}
							// }
						
		
						}
					// ########### SAVE IMAGE IN FOLDER ###########
		
					// %%%%%%%%%%% EXPLODE EMAILS %%%%%%%%%%%%%
					$data['$FINAL_EMAIL'][] = $FINAL_EMAIL;
					$FINAL_EMAIL = str_replace("\n","",$FINAL_EMAIL);
					$MAIL = explode(",",$FINAL_EMAIL);
					// %%%%%%%%%%% EXPLODE EMAILS %%%%%%%%%%%%%

						$STmails = array();
						// $STmails = array(
						// 	$FINAL_EMAIL => $FIRSTNAME,
						// );
						foreach($MAIL as $value){
							// EMAIL
							$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);
							$data['mail'][] = $value;
						}
						

						foreach($STmails as $email => $name){
							// MAIL
							$mail = new PHPMailer;
							$mail->isSMTP(); 
							$mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
							$mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
							$mail->Port = 587; // TLS only 587
							$mail->SMTPSecure = 'tls'; // ssl is depracated
							$mail->SMTPAuth = true;
							$mail->Username = "no.reply.myexamsprep@gmail.com";
							$mail->Password = "xagdmidhhtzijcgt";
							$mail->setFrom("no.reply.myexamsprep@gmail.com", "MyExamsPrep");
							$mail->addAddress($email, $name);
							$mail->Subject = 'myexamsprep:Alert';
							$mail->msgHTML($msg); 
							//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
							$mail->AltBody = 'HTML messaging not supported';
							if($txtAttachment && $txtAttachment!='')$mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
							// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
							
							//USE AddCC When use foreach loop
							// foreach($STmails as $email => $name){
							// 	$EM = $email;
							// 	$NM = $name;
							// 	$mail->AddCC($email, $name); 
							// }
							
							if(!$mail->send()){
								// INSERT DETAILS
								$error_msg=$mail->ErrorInfo;
								$error_msg = str_replace("'","''",$error_msg);
								$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
								VALUES($GET_EMID,'$email','ERROR','$error_msg')";
								sqlsrv_query($mysqli, $query2);
								$data['query2'] = $query2;
								// echo "Mailer Error: " . $mail->ErrorInfo;
								$data['Mail_ST'] = $mail->ErrorInfo;
								$data['sss'][] = $mail;
								$data['success'] = false;
								$data['message'] = 'Sms Send Failed.';
							}
							else{
								// INSERT DETAILS
								$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
								VALUES($GET_EMID,'$email','SUCCESS','')";
								sqlsrv_query($mysqli, $query2);
								$data['query2'] = $query2;
								// echo "Message sent!";
								$data['sss'][] = $mail;
								$data['Mail_ST'] = "Message sent!";
								$data['message'] = 'Sms Send successfully.';
								$data['success'] = true;
							}
						}
		
							
		
					
					$data['querySuccess'][] = $query;
		
				}
			}
			
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
// =============== SAVE EMAIL DATA ==================
function array_push_assoc($array, $key, $value){
	$array[$key] = $value;
	return $array;
 }


/*============ Get MSG History =============*/ 
function getMSGHistory($mysqli){
	try
	{
		global $locid;
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT MSGID,CONVERT(VARCHAR,MSGDATE,20)MSGDATE,MSGTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,MOBILENO,TEXTMESSAGE,
		-- ISNULL((SELECT MOBILENO+' ('+MSG_STATUS+'), ' FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID FOR XML PATH('')),'')MSG_STATUS
		CASE WHEN (SELECT COUNT(*) FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID)>0
			THEN ISNULL((SELECT MOBILENO+'_('+MSG_STATUS+'), ' FROM TEXT_MESSAGES_DETAILS WHERE MSGID=TM.MSGID FOR XML PATH('')),'')
			ELSE MOBILENO
		END MSG_STATUS
		FROM TEXT_MESSAGES TM
		WHERE CONVERT(DATE,MSGDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND MSGTYPE='OUTGOING API' AND MSG_FROM='REGISTRATION_SMS' AND LOCID=$locid
		ORDER BY MSGID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['MSG_STATUS'] = $row['MSG_STATUS'] && $row['MSG_STATUS']!='' ? rtrim($row['MSG_STATUS'],', ') : '';
				$row['MSG_STATUS'] = str_replace(', ', "\n", $row['MSG_STATUS']);
				$MSGDATE = $row['MSGDATE'];
				$MSGDATE=date_create($MSGDATE);
				$row['MSGDATE']= date_format($MSGDATE,"d-m-Y || h:i:s a");

				$row['MSGID'] = (int) $row['MSGID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
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
/*============ Get MSG History =============*/ 


/*============ Get EMAIL History =============*/ 
function getEMAILHistory($mysqli){
	try
	{
		global $locid;
		$txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		$txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT EMID,CONVERT(VARCHAR,EMAILDATE,20)EMAILDATE,EMAILTYPE,STUDENTTYPE,FIRSTNAME,LASTNAME,EMAILID,TEXTEMAIL,ATTACHMENT
		FROM TEXT_EMAIL 
		WHERE CONVERT(DATE,EMAILDATE,105) BETWEEN '$txtFromDT' AND '$txtToDT'
		AND EMAILTYPE='OUTGOING API' AND EMAIL_FROM='REGISTRATION_EMAIL' AND LOCID=$locid
		ORDER BY EMID DESC";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				
				$EMAILDATE = $row['EMAILDATE'];
				$EMAILDATE=date_create($EMAILDATE);
				$row['EMAILDATE']= date_format($EMAILDATE,"d-m-Y || h:i:s a");

				$row['EMID'] = (int) $row['EMID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
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
/*============ Get EMAIL History =============*/ 



/*============ CHECK/CONVERT FILE SIZE =============*/ 
function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
}
/*============ CHECK/CONVERT FILE SIZE =============*/
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SMS / EMAIL %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







