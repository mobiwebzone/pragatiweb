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
		// ============ GEOLOCATION ===========
        case "saveDataGeoLoc":saveDataGeoLoc($conn);break;
        case "getGeoLocations":getGeoLocations($conn);break;
        case "deleteGeoLoc":deleteGeoLoc($conn);break;
		
		// ============ GEOLOCATION_CITY ===========
        case "saveDataGeoCity":saveDataGeoCity($conn);break;
        case "getGeoLocCities":getGeoLocCities($conn);break;
        case "deleteGeoCity":deleteGeoCity($conn);break;
		
		// ============ COUNTRY_STATE_CITY ===========
		// #### COUNTRY

		// #### STATE
        case "saveState":saveState($conn);break;
        case "getStates":getStates($conn);break;
        case "deleteStateModal":deleteStateModal($conn);break;
		// #### CITY
        case "saveCity":saveCity($conn);break;
        case "getCities":getCities($conn);break;
        case "deleteCityModal":deleteCityModal($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% GEOLOCATION SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE DATA ==================
function saveDataGeoLoc($mysqli){
	try
	{
	global $userid;
	$data = array();

	$geolocid  = ($_POST['geolocid'] == 'undefined' || $_POST['geolocid'] == '') ? 0 : $_POST['geolocid'];
	$ddlLocation  = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
	$txtGeolocation  = $_POST['txtGeolocation'] == 'undefined' ? '' : $_POST['txtGeolocation'];
	$txtGeolocation = str_replace("'","''",$txtGeolocation);

	$actionid = $geolocid == 0 ? 1 : 2;

	if($ddlLocation == 0){throw new Exception("Please Select 'Location Name'.");}
	if($txtGeolocation == ''){throw new Exception("Please Enter 'Geographic Location Name'.");}

	$sql = "SELECT * FROM MKT_GEOLOCATIONS WHERE LOCID=$ddlLocation AND GEOLOCATION='$txtGeolocation' AND GEOLOCID!=$geolocid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_GEOLOCATIONS_SP] $actionid,$geolocid,$ddlLocation,'$txtGeolocation',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['GET_GEOLOCID'] = (int)$row['GEOLOCID'];

			$data['success'] = true;
			if(!empty($geolocid))$data['message'] = 'Geographic Location successfully updated.';
			else $data['message'] = 'Geographic Location successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'Chennel already exists';
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
 // =============== SAVE DATA ==============





/*============ GET GEO LOCATION =============*/ 
function getGeoLocations($mysqli){
	try
	{
		$data = array();
		$ddlLocation = ($_POST['ddlLocation'] =='undefined' || $_POST['ddlLocation'] =='') ? 0 : $_POST['ddlLocation'];
		$query = "SELECT LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=MG.LOCID)[LOCATION],
				GEOLOCID,GEOLOCATION FROM MKT_GEOLOCATIONS MG WHERE ISDELETED=0 AND LOCID=$ddlLocation ORDER BY GEOLOCATION";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Geographic location not found.';
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
/*============ GET GEO LOCATION =============*/ 





/* =========== DELETE =========== */ 
function deleteGeoLoc($mysqli){
	try{   
			global $userid;
			$data = array();     
            $GEOLOCID = ($_POST['GEOLOCID'] == 'undefined' || $_POST['GEOLOCID'] == '') ? 0 : $_POST['GEOLOCID'];
			if($GEOLOCID == 0){throw new Exception('GEOLOCID Error.');}
			$delQuery = "EXEC [MKT_GEOLOCATIONS_SP] 3,$GEOLOCID,0,'',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Geographic Location successfully deleted.';
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
/* =========== DELETE =========== */ 





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CITIES SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// =============== SAVE DATA ==================
function saveDataGeoCity($mysqli){
	try
	{
	global $userid;
	$data = array();

	$glcityid  = ($_POST['glcityid'] == 'undefined' || $_POST['glcityid'] == '') ? 0 : $_POST['glcityid'];
	$geolocid  = ($_POST['geolocid'] == 'undefined' || $_POST['geolocid'] == '') ? 0 : $_POST['geolocid'];
	$ddlCountry  = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];
	$ddlState  = ($_POST['ddlState'] == 'undefined' || $_POST['ddlState'] == '') ? 0 : $_POST['ddlState'];
	$ddlCity  = ($_POST['ddlCity'] == 'undefined' || $_POST['ddlCity'] == '') ? 0 : $_POST['ddlCity'];
	
	$actionid = $glcityid == 0 ? 1 : 2;

	if($geolocid == 0){throw new Exception("Please Select 'Geographic Location'.");}
	if($ddlCountry == 0){throw new Exception("Please Select 'Country Name'.");}
	if($ddlState == 0){throw new Exception("Please Select 'State Name'.");}
	if($ddlCity == 0){throw new Exception("Please Select 'City Name'.");}


	$sql = "SELECT * FROM MKT_GEOLOCATION_CITIES WHERE GEOLOCID=$geolocid AND COUNTRYID=$ddlCountry AND STATEID=$ddlState AND 
			CITYID=$ddlCity AND GLCITYID!=$glcityid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [MKT_GEOLOCATION_CITIES_SP] $actionid,$glcityid,$geolocid,$ddlCountry,$ddlState,$ddlCity,$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{
			$data['success'] = true;
			if(!empty($glcityid))$data['message'] = 'City successfully updated.';
			else $data['message'] = 'City successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'City already exists';
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
 // =============== SAVE DATA ==============





/*============ GET GEO LOCATION CITIES =============*/ 
function getGeoLocCities($mysqli){
	try
	{
		$data = array();
		$geolocid = ($_POST['geolocid'] == 'undefined' || $_POST['geolocid'] == '') ? 0 : $_POST['geolocid'];
		if($geolocid == 0) throw new Exception('GEOLOCID Not Found.');

		$query = "SELECT GLCITYID,GEOLOCID,
		(SELECT GEOLOCATION FROM MKT_GEOLOCATIONS WHERE GEOLOCID=MGC.GEOLOCID)GEOLOCATION,COUNTRYID,
		(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=MGC.COUNTRYID)COUNTRY,STATEID,
		(SELECT STATENAME FROM COUNTRY_STATES WHERE STATEID=MGC.STATEID)[STATE],CITYID,
		(SELECT CITYNAME FROM COUNTRY_STATE_CITIES WHERE CITYID=MGC.CITYID)CITY
		FROM MKT_GEOLOCATION_CITIES MGC
		WHERE ISDELETED=0 AND GEOLOCID=$geolocid
		ORDER BY CONVERT(DATE,INSERTDATE,105)DESC";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Geographic location cities not found.';
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
/*============ GET GEO LOCATION CITIES =============*/ 





/* =========== DELETE =========== */ 
function deleteGeoCity($mysqli){
	try{   
			global $userid;
			$data = array();     
            $GLCITYID = ($_POST['GLCITYID'] == 'undefined' || $_POST['GLCITYID'] == '') ? 0 : $_POST['GLCITYID'];
			if($GLCITYID == 0){throw new Exception('GLCITYID Error.');}
			$delQuery = "EXEC [MKT_GEOLOCATION_CITIES_SP] 3,$GLCITYID,0,0,0,0,$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Geographic location city successfully deleted.';
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
/* =========== DELETE =========== */ 












// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% COUNTRY/STATE/CITY %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



// =============== STATE ==================
// ####SAVE
function saveState($mysqli){
	try
	{
	global $userid;
	$data = array();

	$stateid  = ($_POST['stateid'] == 'undefined' || $_POST['stateid'] == '') ? 0 : $_POST['stateid'];
	$ddlCountry  = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];
	$txtAddState  = $_POST['txtAddState'] == 'undefined' ? '' : $_POST['txtAddState'];
	$txtAddStateSN  = $_POST['txtAddStateSN'] == 'undefined' ? '' : $_POST['txtAddStateSN'];
	
	$actionid = $stateid == 0 ? 1 : 2;

	if($ddlCountry == 0){throw new Exception("Please Select 'Country Name'.");}
	if($txtAddState == ''){throw new Exception( "Please Enter 'State Name'.");}
	if($txtAddStateSN == ''){throw new Exception("Please Enter 'Short name'.");}

	$sql = "SELECT * FROM COUNTRY_STATES WHERE COUNTRYID=$ddlCountry AND STATENAME='$txtAddState' AND STATEID!=$stateid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{	
		$query="EXEC [COUNTRY_STATES_SP] $actionid,$stateid,$ddlCountry,'$txtAddState','$txtAddStateSN',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{
			$data['success'] = true;
			if(!empty($stateid))$data['message'] = 'State successfully updated.';
			else $data['message'] = 'State successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'State already exists';
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
// #### GET
function getStates($mysqli){
	try
	{
		$data = array();
		$ddlCountry = ($_POST['ddlCountry'] == 'undefined' || $_POST['ddlCountry'] == '') ? 0 : $_POST['ddlCountry'];
		if($ddlCountry == 0) throw new Exception('Please Select "Country Name".');

		$query = "SELECT STATEID,STATENAME,STATE_SC FROM COUNTRY_STATES WHERE ISDELETED=0 AND COUNTRYID=$ddlCountry";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'States not found.';
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
// #### DELETE
function deleteStateModal($mysqli){
	try{   
			global $userid;
			$data = array();     
            $STATEID = ($_POST['STATEID'] == 'undefined' || $_POST['STATEID'] == '') ? 0 : $_POST['STATEID'];
			if($STATEID == 0){throw new Exception('STATEID Error.');}
			$delQuery = "EXEC [COUNTRY_STATES_SP] 3,$STATEID,0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'State successfully deleted.';
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
// =============== STATE ==============





/*============ CITIES =============*/ 
// ####SAVE
function saveCity($mysqli){
	try
	{
	global $userid;
	$data = array();

	$cityid  = ($_POST['cityid'] == 'undefined' || $_POST['cityid'] == '') ? 0 : $_POST['cityid'];
	$ddlState  = ($_POST['ddlState'] == 'undefined' || $_POST['ddlState'] == '') ? 0 : $_POST['ddlState'];
	$txtAddCity  = $_POST['txtAddCity'] == 'undefined' ? '' : $_POST['txtAddCity'];
	$txtAddCitySN  = $_POST['txtAddCitySN'] == 'undefined' ? '' : $_POST['txtAddCitySN'];
	
	$actionid = $cityid == 0 ? 1 : 2;

	if($ddlState == 0){throw new Exception("Please Select 'State Name'.");}
	if($txtAddCity == ''){throw new Exception( "Please Enter 'City Name'.");}
	if($txtAddCitySN == ''){throw new Exception("Please Enter 'Short name'.");}

	$sql = "SELECT * FROM COUNTRY_STATE_CITIES WHERE STATEID=$ddlState AND CITYNAME='$txtAddCity' AND CITYID!=$cityid AND ISDELETED=0";
	$row_count = unique($sql);
	if($row_count == 0)
	{
		$query="EXEC [COUNTRY_STATE_CITIES_SP] $actionid,$cityid,$ddlState,'$txtAddCity','$txtAddCitySN',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{
			$data['success'] = true;
			if(!empty($cityid))$data['message'] = 'City successfully updated.';
			else $data['message'] = 'City successfully inserted.';
		}
		
		$data['query'] = $query;
		echo json_encode($data);exit;
	}
	else
	{
		$data['success'] = false;
		$data['message'] = 'City already exists';
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
// #### GET
function getCities($mysqli){
	try
	{
		$data = array();
		$ddlState = ($_POST['ddlState'] == 'undefined' || $_POST['ddlState'] == '') ? 0 : $_POST['ddlState'];
		if($ddlState == 0) throw new Exception('Please Select "State Name".');

		$query = "SELECT CITYID,CITYNAME,CITY_SC FROM COUNTRY_STATE_CITIES WHERE ISDELETED=0 AND STATEID=$ddlState";
		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['message'] = 'Cities not found.';
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
// #### DELETE
function deleteCityModal($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CITYID = ($_POST['CITYID'] == 'undefined' || $_POST['CITYID'] == '') ? 0 : $_POST['CITYID'];
			if($CITYID == 0){throw new Exception('CITYID Error.');}
			$delQuery = "EXEC [COUNTRY_STATE_CITIES_SP] 3,$CITYID,0,'','',$userid";
			$stmt=sqlsrv_query($mysqli, $delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'City successfully deleted.';
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
/*============ CITIES =============*/




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







