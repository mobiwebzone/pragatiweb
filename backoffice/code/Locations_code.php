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
        case "CheckISMainET":CheckISMainET($conn);break;
        case "saveLocation":saveLocation($conn);break;
        case "getLocation":getLocation($conn);break;
        case "getCurrency":getCurrency($conn);break;
        case "getCountries":getCountries($conn);break;
        case "deleteLocation":deleteLocation($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

//  function saveLocation($mysqli){
//      try
//      {
// 		$data = array();
//         global $userid;
    
//         $locationid  = ($_POST['locationid'] == 'undefined' || $_POST['locationid'] == '') ? 0 : $_POST['locationid'];
//         $txtLocation  = $_POST['txtLocation'] == 'undefined' ? '' : $_POST['txtLocation'];
//         $txtLocationDesc  = $_POST['txtLocationDesc'] == 'undefined' ? '' : $_POST['txtLocationDesc'];
//         $txtContactNo  = $_POST['txtContactNo'] == 'undefined' ? '' : $_POST['txtContactNo'];
//         $txtContactPerson  = $_POST['txtContactPerson'] == 'undefined' ? '' : $_POST['txtContactPerson'];
//         $txtCompanyName  = $_POST['txtCompanyName'] == 'undefined' ? '' : $_POST['txtCompanyName'];
//         $txtTaxID  = $_POST['txtTaxID'] == 'undefined' ? '' : $_POST['txtTaxID'];
//         $txtEmail  = $_POST['txtEmail'] == 'undefined' ? '' : $_POST['txtEmail'];
//         $txtAddressL1  = $_POST['txtAddressL1'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtAddressL1']);
//         $txtAddressL2  = $_POST['txtAddressL2'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtAddressL2']);
//         $txtCity  = $_POST['txtCity'] == 'undefined' ? '' : $_POST['txtCity'];
//         $txtState  = $_POST['txtState'] == 'undefined' ? '' : $_POST['txtState'];
//         $txtCountry  = $_POST['txtCountry'] == 'undefined' ? '' : $_POST['txtCountry'];
//         $txtZipCode  = $_POST['txtZipCode'] == 'undefined' ? '' : $_POST['txtZipCode'];
//         $IsmainET  = ($_POST['IsmainET'] == 'undefined' || $_POST['IsmainET'] == '') ? 0 : $_POST['IsmainET'];
//         $txtETDiff  = $_POST['txtETDiff'] == 'undefined' ? '' : $_POST['txtETDiff'];
//         $ddlCurrency  = ($_POST['ddlCurrency'] == 'undefined' || $_POST['ddlCurrency'] == '') ? 0 : $_POST['ddlCurrency'];
// 		$ddlLanguage = ($_POST['ddlLanguage'] == 'undefined' || $_POST['ddlLanguage'] == '') ? 0 : $_POST['ddlLanguage'];
// 		$ddlScript = ($_POST['ddlScript'] == 'undefined' || $_POST['ddlScript'] == '') ? 0 : $_POST['ddlScript'];
// 		$txtNextNoofTopics = ($_POST['txtNextNoofTopics'] == 'undefined' || $_POST['txtNextNoofTopics'] == '') ? 0 : $_POST['txtNextNoofTopics'];
//         $txtLoginID  = $_POST['txtLoginID'] == 'undefined' ? '' : $_POST['txtLoginID'];
//         $txtLoginPwd  = $_POST['txtLoginPwd'] == 'undefined' ? '' : $_POST['txtLoginPwd'];
//         $txtFlagIcon  = $_POST['txtFlagIcon'] == 'undefined' ? '' : $_POST['txtFlagIcon'];
        
		
		
// 		$actionid = $locationid == 0 ? 1 : 2;

// 		if($txtLocation == '') throw new Exception("Enter Location Name.");
// 		// if($txtNextNoofTopics == 0) throw new Exception("Enter Location Name.");

// 		$sql = "SELECT * FROM LOCATIONS WHERE LOGIN_ID='$txtLoginID' AND LOCATION='$txtLocation' AND LOC_ID!=$locationid AND ISDELETED=0";
// 		$row_count = unique($sql);
		
		
// 		$data = array();
// 		if($row_count == 0)
// 		{
// 			$query="EXEC [LOCATIONS_SP] $actionid,$locationid,'$txtLocation','$txtLocationDesc','$txtContactNo','$txtContactPerson',
// 					'$txtEmail','$txtCompanyName','$txtTaxID','$txtAddressL1','$txtAddressL2','$txtCity','$txtState',$txtCountry,
// 					'$txtZipCode',$IsmainET,'$txtETDiff',$ddlCurrency,'$txtLoginID','$txtLoginPwd','$txtFlagIcon',$ddlLanguage,
// 					$ddlScript,$txtNextNoofTopics,$userid";

// 			// $data['query'] = $query;
// 			// echo json_encode($data);exit;

// 			$stmt=sqlsrv_query($mysqli, $query);
			
// 			if($stmt === false)
// 			{
// 				// die( print_r( sqlsrv_errors(), true));
// 				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
// 				$data['success'] = true;
// 				$data['query'] = $query;
// 				echo json_encode($data);exit;
// 			}
// 			else
// 			{
// 				$data['query'] = $query;
// 				$data['success'] = true;
// 				if(!empty($locationid))$data['message'] = 'Record successfully updated';
// 				else $data['message'] = 'Record successfully inserted.';
// 				echo json_encode($data);exit;
// 			}
			
// 		}
// 		else
// 		{
// 			$data['success'] = false;
// 			$data['sql'] = $sql;
// 			$data['message'] = 'UserID/LOCATION already exists';
// 			echo json_encode($data);exit;
// 		}

//      }
//      catch(Exception $e)
//      {
// 		$data = array();
// 		$data['success'] = false;
// 		$data['message'] = $e->getMessage();
// 		echo json_encode($data);
// 		exit;
//      }
//  }

function saveLocation($mysqli) {
    try {
        $data = array();
        global $userid;

        // Sanitize inputs and handle undefined values
        $locationid = isset($_POST['locationid']) && $_POST['locationid'] !== 'undefined' ? (int)$_POST['locationid'] : 0;
        $txtLocation = isset($_POST['txtLocation']) && $_POST['txtLocation'] !== 'undefined' ? $_POST['txtLocation'] : '';
        $txtLocationDesc = isset($_POST['txtLocationDesc']) && $_POST['txtLocationDesc'] !== 'undefined' ? $_POST['txtLocationDesc'] : '';
        $txtContactNo = isset($_POST['txtContactNo']) && $_POST['txtContactNo'] !== 'undefined' ? $_POST['txtContactNo'] : '';
        $txtContactPerson = isset($_POST['txtContactPerson']) && $_POST['txtContactPerson'] !== 'undefined' ? $_POST['txtContactPerson'] : '';
        $txtCompanyName = isset($_POST['txtCompanyName']) && $_POST['txtCompanyName'] !== 'undefined' ? $_POST['txtCompanyName'] : '';
        $txtTaxID = isset($_POST['txtTaxID']) && $_POST['txtTaxID'] !== 'undefined' ? $_POST['txtTaxID'] : '';
        $txtEmail = isset($_POST['txtEmail']) && $_POST['txtEmail'] !== 'undefined' ? $_POST['txtEmail'] : '';
        $txtAddressL1 = isset($_POST['txtAddressL1']) && $_POST['txtAddressL1'] !== 'undefined' ? str_replace("'", "''", $_POST['txtAddressL1']) : '';
        $txtAddressL2 = isset($_POST['txtAddressL2']) && $_POST['txtAddressL2'] !== 'undefined' ? str_replace("'", "''", $_POST['txtAddressL2']) : '';
        $txtCity = isset($_POST['txtCity']) && $_POST['txtCity'] !== 'undefined' ? $_POST['txtCity'] : '';
        $txtState = isset($_POST['txtState']) && $_POST['txtState'] !== 'undefined' ? $_POST['txtState'] : '';
        $txtCountry = isset($_POST['txtCountry']) && $_POST['txtCountry'] !== 'undefined' ? $_POST['txtCountry'] : '';
        $txtZipCode = isset($_POST['txtZipCode']) && $_POST['txtZipCode'] !== 'undefined' ? $_POST['txtZipCode'] : '';
        $IsmainET = isset($_POST['IsmainET']) && $_POST['IsmainET'] !== 'undefined' ? (int)$_POST['IsmainET'] : 0;
        $txtETDiff = isset($_POST['txtETDiff']) && $_POST['txtETDiff'] !== 'undefined' ? $_POST['txtETDiff'] : '';
        $ddlCurrency = isset($_POST['ddlCurrency']) && $_POST['ddlCurrency'] !== 'undefined' ? (int)$_POST['ddlCurrency'] : 0;
        $ddlLanguage = isset($_POST['ddlLanguage']) && $_POST['ddlLanguage'] !== 'undefined' ? (int)$_POST['ddlLanguage'] : 0;
        $ddlScript = isset($_POST['ddlScript']) && $_POST['ddlScript'] !== 'undefined' ? (int)$_POST['ddlScript'] : 0;
        $txtNextNoofTopics = isset($_POST['txtNextNoofTopics']) && $_POST['txtNextNoofTopics'] !== 'undefined' ? (int)$_POST['txtNextNoofTopics'] : 0;
        $txtLoginID = isset($_POST['txtLoginID']) && $_POST['txtLoginID'] !== 'undefined' ? $_POST['txtLoginID'] : '';
        $txtLoginPwd = isset($_POST['txtLoginPwd']) && $_POST['txtLoginPwd'] !== 'undefined' ? $_POST['txtLoginPwd'] : '';
        $txtFlagIcon = isset($_POST['txtFlagIcon']) && $_POST['txtFlagIcon'] !== 'undefined' ? $_POST['txtFlagIcon'] : '';

        $actionid = $locationid == 0 ? 1 : 2;

        if (empty($txtLocation)) {
            throw new Exception("Enter Location Name.");
        }

        // Prevent SQL injection in the duplicate check
        $txtLoginID = addslashes($txtLoginID);
        $txtLocation = addslashes($txtLocation);
        $sql = "SELECT * FROM LOCATIONS WHERE LOGIN_ID='$txtLoginID' AND LOCATION='$txtLocation' AND LOC_ID != $locationid AND ISDELETED=0";
        $row_count = unique($sql); // Ensure unique() is defined and works correctly

        if ($row_count == 0) {
            $query = "EXEC [LOCATIONS_SP] $actionid, $locationid, '$txtLocation', '$txtLocationDesc', '$txtContactNo', '$txtContactPerson',
                      '$txtEmail', '$txtCompanyName', '$txtTaxID', '$txtAddressL1', '$txtAddressL2', '$txtCity', '$txtState', '$txtCountry',
                      '$txtZipCode', $IsmainET, '$txtETDiff', $ddlCurrency, '$txtLoginID', '$txtLoginPwd', '$txtFlagIcon', $ddlLanguage,
                      $ddlScript, $txtNextNoofTopics,'', $userid";

	
					  $stmt = sqlsrv_query($mysqli, $query);

            if ($stmt === false) {
                $data['success'] = false;
                $data['message'] = 'Database error: ' . print_r(sqlsrv_errors(), true);
                $data['query'] = $query;
                echo json_encode($data);
                exit;
            }

            $data['success'] = true;
            $data['message'] = $locationid ? 'Record successfully updated' : 'Record successfully inserted.';
            $data['query'] = $query; // For debugging
            echo json_encode($data);
            exit;
        } else {
            $data['success'] = false;
            $data['message'] = 'UserID/LOCATION already exists';
            $data['sql'] = $sql;
            echo json_encode($data);
            exit;
        }
    } catch (Exception $e) {
        $data['success'] = false;
        $data['message'] = $e->getMessage();
        echo json_encode($data);
        exit;
    }
}

/*============ CHECK IS MAIN ET =============*/ 
function CheckISMainET($mysqli){
	try
	{
		$data = array();
		$query = "SELECT * FROM LOCATIONS WHERE IS_ET=1 AND ISDELETED=0";
		$row_count = unique($query);

		if($row_count > 0){
			
			$data['success'] = true;
			echo json_encode($data);exit;
		}
		else{
			$data['success'] = false;
			echo json_encode($data);exit;
		}

		
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

/*============ Get Location =============*/ 
function getLocation($mysqli){
	try
	{
		$query = "SELECT LOC_ID,LOCATION,LOC_DESC,LOC_CONTACT,LOC_PERSON,LOC_EMAIL,COMPANY_NAME,TAXID,LOC_ADDRESS_LINE1,
		LOC_ADDRESS_LINE2,LOC_CITY,LOC_STATE,LOC_COUNTRY,LOC_ZIPCODE,IS_ET,LOC_ET_DIFF,CURRENCY_ID,
		(SELECT CURRENCY_CODE FROM CURRENCY_MASTER WHERE CURRENCY_ID=LOCATIONS.CURRENCY_ID)CURRENCY_CODE,
		LOGIN_ID,DBO.GET_CLEAR_LOCATION_PASSWORD(LOC_ID) LOGIN_PWD,
		(SELECT FLAG_ICON FROM COUNTRIES WHERE ISDELETED=0 AND COUNTRYID=LOCATIONS.LOC_COUNTRY)FLAG_ICON,
		(SELECT LANGUAGE_NAME FROM LANGUAGE_MASTER WHERE LANID=LOCATIONS.LANID)LAN,LANID,
		(SELECT SCRIPT_NAME FROM SCRIPT_MASTER WHERE SCRIPTID=LOCATIONS.SCRIPTID)SCRIPT,SCRIPTID,LA_NOOF_NEXT_TOPICS
		FROM LOCATIONS WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['LOC_ID'] = (int) $row['LOC_ID'];
			$row['CURRENCY_ID'] = (int) $row['CURRENCY_ID'];
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

/*============ Get Currency =============*/ 
function getCurrency($mysqli){
	try
	{
		$query = "SELECT CURRENCY_ID,CURRENCY_CODE FROM CURRENCY_MASTER WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['CURRENCY_ID'] = (int) $row['CURRENCY_ID'];
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



/*============ Get Country =============*/ 
function getCountries($mysqli){
	try
	{
		$query = "SELECT COUNTRYID,COUNTRY FROM COUNTRIES WHERE ISDELETED=0";

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

/* =========== Delete =========== */ 
function deleteLocation($mysqli){
	try{   
			global $userid;
			$data = array();     
            $locid = ($_POST['locid'] == 'undefined' || $_POST['locid'] == '') ? 0 : $_POST['locid'];  
			// $stmt=sqlsrv_query($mysqli, "EXEC [LOCATIONS_SP] 3,$locid,'',0,'','',0,$userid");
			$stmt=sqlsrv_query($mysqli, "UPDATE LOCATIONS SET ISDELETED=1,DELETE_ID=$userid,DELETE_DATE=GETDATE() WHERE LOC_ID=$locid");
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







