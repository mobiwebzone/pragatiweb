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
        case "saveData":saveData($conn);break;
        case "getColleges":getColleges($conn);break;
        case "deleteData":deleteData($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

/* ========== SAVE DATA =========== */
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;

		$clid = ($_POST['clid'] == 'undefined' || $_POST['clid'] == '') ? 0 : $_POST['clid'];
		$ddlUniversity = ($_POST['ddlUniversity'] == 'undefined' || $_POST['ddlUniversity'] == '') ? 0 : $_POST['ddlUniversity'];
		$txtCollegeName = $_POST['txtCollegeName'] == 'undefined' ? '' : $_POST['txtCollegeName'];
		$txtAddLine1 = $_POST['txtAddLine1'] == 'undefined' ? '' : $_POST['txtAddLine1'];
		$txtAddLine2 = $_POST['txtAddLine2'] == 'undefined' ? '' : $_POST['txtAddLine2'];
		$txtCity = $_POST['txtCity'] == 'undefined' ? '' : $_POST['txtCity'];
		$txtState = $_POST['txtState'] == 'undefined' ? '' : $_POST['txtState'];
		$txtZipcode = $_POST['txtZipcode'] == 'undefined' ? '' : $_POST['txtZipcode'];
		$ddlCountry = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];
		$txtPhone = $_POST['txtPhone'] == 'undefined' ? '' : $_POST['txtPhone'];
		$txtEmail = $_POST['txtEmail'] == 'undefined' ? '' : $_POST['txtEmail'];
		$txtWebsite = $_POST['txtWebsite'] == 'undefined' ? '' : $_POST['txtWebsite'];
		$txtCountyRank = ($_POST['txtCountyRank'] == 'undefined' || $_POST['txtCountyRank'] == '') ? 0 : $_POST['txtCountyRank'];
		$txtInternationalRank = ($_POST['txtInternationalRank'] == 'undefined' || $_POST['txtInternationalRank'] == '') ? 0 : $_POST['txtInternationalRank'];
		$ddlCollegeType = $_POST['ddlCollegeType'] == 'undefined' ? '' : $_POST['ddlCollegeType'];
		$txtCollegeStrength = ($_POST['txtCollegeStrength'] == 'undefined' || $_POST['txtCollegeStrength'] == '') ? 0 : $_POST['txtCollegeStrength'];
		$txtAnuuTuiInState = ($_POST['txtAnuuTuiInState'] == 'undefined' || $_POST['txtAnuuTuiInState'] == '') ? 0 : $_POST['txtAnuuTuiInState'];
		$txtAnuuTuiOutOfState = ($_POST['txtAnuuTuiOutOfState'] == 'undefined' || $_POST['txtAnuuTuiOutOfState'] == '') ? 0 : $_POST['txtAnuuTuiOutOfState'];
		$txtAnuuTuiInternational = ($_POST['txtAnuuTuiInternational'] == 'undefined' || $_POST['txtAnuuTuiInternational'] == '') ? 0 : $_POST['txtAnuuTuiInternational'];
		$txtLodging = ($_POST['txtLodging'] == 'undefined' || $_POST['txtLodging'] == '') ? 0 : $_POST['txtLodging'];
		$txtFood = ($_POST['txtFood'] == 'undefined' || $_POST['txtFood'] == '') ? 0 : $_POST['txtFood'];
		$txtPerOfStInState = ($_POST['txtPerOfStInState'] == 'undefined' || $_POST['txtPerOfStInState'] == '') ? 0 : $_POST['txtPerOfStInState'];
		$txtPerOfStOutOfState = ($_POST['txtPerOfStOutOfState'] == 'undefined' || $_POST['txtPerOfStOutOfState'] == '') ? 0 : $_POST['txtPerOfStOutOfState'];
		$txtPerOfStInternational = ($_POST['txtPerOfStInternational'] == 'undefined' || $_POST['txtPerOfStInternational'] == '') ? 0 : $_POST['txtPerOfStInternational'];
		$txtRemark = $_POST['txtRemark'] == 'undefined' ? '' : $_POST['txtRemark'];
    

		$actionid = $clid == 0 ? 1 : 2;
		
		if($ddlUniversity == 0){throw new Exception("Please Select University Name.");}
		if($txtCollegeName == ''){throw new Exception("Please Enter College Name.");}
		// if($txtAddLine1 == ''){throw new Exception("Please Enter Address Line1.");}
		// if($ddlCountry == 0){throw new Exception("Please Select Country Name.");}
		// if($txtPhone == ''){throw new Exception("Please Enter Phone Number.");}
		// if($ddlCollegeType == ''){throw new Exception("Please Enter College Type.");}
		// if($txtCollegeStrength == ''){throw new Exception("Please Enter College Strength.");}

		$sql = "SELECT * FROM COLLEGES_MASTER WHERE UNIVERSITYID=$ddlUniversity AND COLLEGE='$txtCollegeName' AND CLID!=$clid AND ISDELETED=0";
		$row_count = unique($sql);

		if($row_count == 0)
		{

			$query="EXEC [COLLEGES_MASTER_SP]$actionid,$clid,$ddlUniversity,'$txtCollegeName','$txtAddLine1','$txtAddLine2',
				'$txtCity','$txtState','$txtZipcode',$ddlCountry,'$txtPhone','$txtEmail','$txtWebsite',$txtCountyRank,$txtInternationalRank,
				'$ddlCollegeType',$txtCollegeStrength,$txtAnuuTuiInState,$txtAnuuTuiOutOfState,$txtAnuuTuiInternational,$txtLodging,
				$txtFood,$txtPerOfStInState,$txtPerOfStOutOfState,$txtPerOfStInternational,'$txtRemark',$userid";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = true;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$data['query'] = $query;
				$data['success'] = true;
				if(!empty($clid))$data['message'] = 'Record successfully updated';
				else $data['message'] = 'Record successfully inserted.';
				echo json_encode($data);exit;
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists';
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
/* ========== SAVE DATA =========== */




/* ========== GET COLLEGES =========== */
 function getColleges($mysqli){
	try
	{
		$query = "SELECT CLID,UNIVERSITYID,(SELECT UNIVERSITY FROM UNIVERSITY_MASTER WHERE UNIVERSITYID=CM.UNIVERSITYID)UNIVERSITY,
		COLLEGE,ADDRESSLINE1,ADDRESSLINE2,CITY,[STATE],ZIPCODE,COUNTRYID,
		(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=CM.COUNTRYID)COUNTRY,PHONE,EMAILID,WEBSITE,COUNTRY_RANK,
		INTERNATIONAL_RANK,COLLEGE_TYPE,STRENGTH,ANNUAL_TUITION_INSTATE,ANNUAL_TUITION_OUTSTATE,ANNUAL_TUITION_INTERNATIONAL,
		LODGING,FOOD,PEROFSTUDENT_INSTATE,PEROFSTUDENT_OUTSTATE,PEROFSTUDENT_INTERNATIONAL,REMARKS 
		FROM COLLEGES_MASTER CM
		WHERE ISDELETED=0 ORDER BY COLLEGE";
		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['CLID'] = (int) $row['CLID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['$query'] = $query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* ========== GET COLLEGES =========== */




/* =========== DELETE DATA =========== */ 
function deleteData($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CLID = ($_POST['CLID'] == 'undefined' || $_POST['CLID'] == '') ? 0 : $_POST['CLID'];  
			if($CLID == 0){throw new Exception('CLID NOT FOUND.');}
			$delQuery = "UPDATE COLLEGES_MASTER SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE CLID=$CLID";
			$stmt=sqlsrv_query($mysqli, $delQuery);
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
		$data['success'] = false . $query;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* =========== DDELETE DATA =========== */ 






function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







