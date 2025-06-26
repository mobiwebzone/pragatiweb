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
        case "save":save($conn);break;
        case "getBrands":getBrands($conn);break;
        case "delete":delete($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function save($mysqli){
     try
     {
		$data = array();
        global $userid;

		$brandid = ($_POST['brandid'] == 'undefined' || $_POST['brandid'] == '') ? 0 : $_POST['brandid'];
		$txtBrandName = $_POST['txtBrandName'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtBrandName']);
		$ddlLocation = ($_POST['ddlLocation'] == 'undefined' || $_POST['ddlLocation'] == '') ? 0 : $_POST['ddlLocation'];
		$existingLogoUpload = $_POST['existingLogoUpload'] == 'undefined' ? '' : str_replace("'","''",$_POST['existingLogoUpload']);
		$txtLogoDesc = $_POST['txtLogoDesc'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtLogoDesc']);
		$txtContactPerson = $_POST['txtContactPerson'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtContactPerson']);
		$txtContactNumber = $_POST['txtContactNumber'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtContactNumber']);
		$txtContactAddress = $_POST['txtContactAddress'] == 'undefined' ? '' : str_replace("'","''",$_POST['txtContactAddress']);
		
		// === IMAGE
		$existingLogoUpload  = $_POST['existingLogoUpload'] == 'undefined' ? '' : $_POST['existingLogoUpload'];
		$fileSize = 102400; // 100 kb
		if(isset($_FILES['logoUpload']['name']) && $_FILES['logoUpload']['size'] > 0 && $_FILES['logoUpload']['size'] > $fileSize) throw new Exception('File size too large.');
		
		$logoUpload = '';
		if(isset($_FILES['logoUpload']['name']) && $_FILES['logoUpload']['size'] > 0){
			$ext = pathinfo($_FILES['logoUpload']['name'],PATHINFO_EXTENSION);
			$logoUpload .= strtolower('brand_logo_'.rand().'_'.time().'.'.$ext);
		}
		else
		{
			$logoUpload=$existingLogoUpload;
		}
		// === IMAGE
		
		$actionid = $brandid == 0 ? 1 : 2;

		if($txtBrandName == '') throw new Exception("Enter Brand Name.");
		if($ddlLocation == 0) throw new Exception("Select Location Name.");
		if($txtContactPerson == '') throw new Exception("Enter Contact Person Name.");
		if($txtContactNumber == '') throw new Exception("Enter Contact Person Number.");
		if(!is_numeric($txtContactNumber)) throw new Exception("Invalid Contact Person Number.");

		$sql = "SELECT * FROM BRANDS WHERE BRANDNAME='$txtBrandName' AND LOCID=$ddlLocation AND BRANDID!=$brandid AND ISDELETED=0";
		$row_count = unique($sql);

		$data = array();
		if($row_count == 0)
		{
			$query="EXEC [BRANDS_SP] $actionid,$brandid,'$txtBrandName',$ddlLocation,'$logoUpload','$txtLogoDesc',
					'$txtContactPerson','$txtContactNumber','$txtContactAddress',$userid";
			$data['query'] = $query;
			$stmt=sqlsrv_query($mysqli, $query);
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
			}
			else
			{	

				// === IMAGE
				if(isset($_FILES['logoUpload']['name']) && $_FILES['logoUpload']['size'] > 0)
				{
					move_uploaded_file($_FILES["logoUpload"]["tmp_name"], '../images/brand/'.$logoUpload);
				}

				
				if(isset($_FILES['logoUpload']['name']) && $existingLogoUpload != '')
				{
					if (file_exists('../images/brand/'.$existingLogoUpload))
					{
						unlink('../images/brand/'.$existingLogoUpload);
					}
				}
				// === IMAGE

				$data['success'] = true;
				if(!empty($brandid))$data['message'] = 'Record successfully updated.';
				else $data['message'] = 'Record successfully inserted.';
			}
			
		}
		else
		{
			$data['success'] = false;
			$data['message'] = 'Record already exists.';
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


/*============ Get Brands =============*/ 
 function getBrands($mysqli){
	try
	{
		$data = array();
		$query = "SELECT BRANDID,BRANDNAME,LOCID,LOGO,LOGO_DESC,CONTACT_PERSON,CONTACT_NUMBER,CONTACT_ADDRESS,
					(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=BRANDS.LOCID)[LOCATION]
					FROM BRANDS WHERE ISDELETED=0 ORDER BY BRANDNAME";
		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
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




/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
		global $userid;
		$data = array();     
		$BRANDID = ($_POST['BRANDID'] == 'undefined' || $_POST['BRANDID'] == '') ? 0 : $_POST['BRANDID'];
		if($BRANDID==0) throw new Exception('Invalid Brandid.');
		$query = "EXEC [BRANDS_SP] 3,$BRANDID,'',0,'','','','','',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		if( $stmt === false ) 
		{
			die( print_r( sqlsrv_errors(), true));
			throw new Exception( $mysqli->sqlstate );
		}
		else
		{
			$data['success'] = true;
			$data['message'] = 'Record successfully deleted.';
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







