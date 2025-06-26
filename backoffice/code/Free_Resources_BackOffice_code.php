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
        case "saveFreeResource":saveFreeResource($conn);break;
        case "getCategory":getCategory($conn);break;
        case "getFreeResource":getFreeResource($conn);break;
        case "deleteFreeResource":deleteFreeResource($conn);break;
		//%%%%%% CATEGORY FEATURES
        case "saveCatFeatures":saveCatFeatures($conn);break;
        case "getCategoryFeatures":getCategoryFeatures($conn);break;
        case "deleteCatFeature":deleteCatFeature($conn);break;
		//%%%%%% CATEGORY FEATURES VALUES
        case "saveCatFeaturesVal":saveCatFeaturesVal($conn);break;
        case "getCategoryFeaturesVal":getCategoryFeaturesVal($conn);break;
        case "deleteCatFeatureVal":deleteCatFeatureVal($conn);break;

		
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */

function saveFreeResource($mysqli){
	try
	{
	$data = array();
	global $userid;

	$id  = ($_POST['id'] == 'undefined' || $_POST['id'] == '') ? 0 : $_POST['id'];
	$ddlResCat  = $_POST['ddlResCat'] == 'undefined' ? '' : $_POST['ddlResCat'];
	$txtRecCatName  = $_POST['txtRecCatName'] == 'undefined' ? '' : $_POST['txtRecCatName'];
	$ddlUnderResource  = ($_POST['ddlUnderResource'] == 'undefined' || $_POST['ddlUnderResource'] == '') ? 0 : $_POST['ddlUnderResource'];
	$txtChkBlink = ($_POST['txtChkBlink'] == 'undefined' || $_POST['txtChkBlink'] == ''  || $_POST['txtChkBlink'] == 'false') ? 0 : 1;
	$txtCatColor = ($_POST['txtCatColor'] == 'undefined' || $_POST['txtCatColor'] == '') ? '#000000' : $_POST['txtCatColor'];
	$txtChkHTML  = ($_POST['txtChkHTML'] == 'undefined' || $_POST['txtChkHTML'] == ''  || $_POST['txtChkHTML'] == 'false') ? 0 : 1;
	$txtResourceLinkLabel  = $_POST['txtResourceLinkLabel'] == 'undefined' ? '' : $_POST['txtResourceLinkLabel'];
	$txtResourceLink  = $_POST['txtResourceLink'] == 'undefined' ? '' : $_POST['txtResourceLink'];
	$chkRemoveImgOnUpdate  = $_POST['chkRemoveImgOnUpdate'] == 'undefined' ? 0 : $_POST['chkRemoveImgOnUpdate'];

	$countfiles = isset($_FILES['file']['name']) ? count($_FILES['file']['name']) : 0;
	$data['img'] = $countfiles;
	$txtResImage_arr = array(); 
	
	if($countfiles>0){
		for ( $i = 0;$i < $countfiles;$i++ ){
			$filename = $_FILES['file']['name'][$i];  
			$data['filename'][] = $filename;

			if(isset($_FILES['file']['name'][$i]) && $_FILES['file']['size'][$i] > 0){
				$ext = pathinfo($_FILES['file']['name'][$i],PATHINFO_EXTENSION);
				$txtResImage_arr[] = strtolower(time().'_'.$i.'.'.$ext);
			}
			// Upload file    
			// if(move_uploaded_file($_FILES['file']['tmp_name'][$i],$location.$filename)){      
			//    $filename_arr[] = $filename;
			// }
		}
	}
	$txtResImage_arr_string = $countfiles>0 ? implode(", ", $txtResImage_arr):'';
	$data['txtResImage_arr'] = $txtResImage_arr;

	$existingResImage  = (empty($_POST['existingResImage']) || $_POST['existingResImage'] == 'undefined') ? array() : explode(", ",$_POST['existingResImage']);
	$existingResImage_count = isset($existingResImage) ? count($existingResImage) : 0;
	$data['existingResImage'] = $existingResImage;
	$data['existingResImage_count'] = $existingResImage_count;
	// $data['success'] = false;
	// echo json_encode($data);exit;
	//==== IMAGE
	// $txtResImage = '';
	// if(isset($_FILES['txtResImage']['name']) && $_FILES['txtResImage']['size'] > 0){
	// 	$ext = pathinfo($_FILES['txtCatImage']['name'],PATHINFO_EXTENSION);
	// 	$txtResImage .= strtolower(time().'.'.$ext);
	// }
	// else
	// {
	// 	$txtResImage="";
	// }
	//==== IMAGE

	// $data['txtChkHTML'] = $txtChkHTML;
	
	
	$actionid = $id == 0 ? 1 : 2;

	if($ddlResCat == '')
	{throw new Exception("Select Resource/Category.");}

	$sql = "SELECT * FROM FREE_RESOURCES WHERE RESOURCE_CATEGORY='$ddlResCat' AND RESOURCE_CATEGORY_TEXT='$txtRecCatName'
			AND UNDER_ID=$ddlUnderResource AND ID!=$id AND ISDELETED=0";
	$row_count = unique($sql);

	if($row_count == 0)
	{
		$query="EXEC [FREE_RESOURCES_SP] $actionid,$id,'$ddlResCat','$txtRecCatName',$ddlUnderResource,$txtChkBlink,'$txtCatColor',$txtChkHTML,'$txtResourceLinkLabel','$txtResourceLink',$userid";
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
			$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
			$data['GET_ID']=(int)$row['ID'];
			$GET_ID=(int)$row['ID'];

			// UPLOAD IMAGE==========
			if($txtResImage_arr_string != ''){

				if($GET_ID > 0)
				{
					$insertimage = "UPDATE FREE_RESOURCES SET RESOURCE_IMAGES='$txtResImage_arr_string' WHERE ID=$GET_ID";
					sqlsrv_query($mysqli,$insertimage);
				}


				if($countfiles>0){
					for ( $i = 0;$i < $countfiles;$i++ ){
						$filename = $txtResImage_arr[$i];  
						if(isset($_FILES['file']['name'][$i]) && $_FILES['file']['size'][$i] > 0)
						{
							move_uploaded_file($_FILES["file"]["tmp_name"][$i], '../images/free_resources/'.$filename);
						}
					}
				}


				if($countfiles>0 && $existingResImage_count>0){
					for ( $i = 0;$i < $existingResImage_count;$i++ ){
						// $filename = $_FILES['file']['name'][$i];
						$existFile = $existingResImage[$i];
						if (file_exists('../images/free_resources/'.$existFile))
						{
							unlink('../images/free_resources/'.$existFile);
						}
					}
				}

			
			}
			if($chkRemoveImgOnUpdate>0 && $existingResImage_count>0 && $countfiles<=0){
				if($GET_ID>0){
					$insertimage = "UPDATE FREE_RESOURCES SET RESOURCE_IMAGES='' WHERE ID=$GET_ID";
						sqlsrv_query($mysqli,$insertimage);
				}

				for ( $i = 0;$i < $existingResImage_count;$i++ ){
					// $filename = $_FILES['file']['name'][$i];
					$existFile = $existingResImage[$i];
					if (file_exists('../images/free_resources/'.$existFile))
					{
						unlink('../images/free_resources/'.$existFile);
					}
				}
			}
			// UPLOAD IMAGE==========

			$data['query'] = $query;
			$data['success'] = true;
			if(!empty($id))$data['message'] = 'Record successfully updated';
			else $data['message'] = 'Record successfully inserted.';
			echo json_encode($data);exit;
		}
		
	}
	else
	{
		$data['success'] = false;
		$data['sql'] = $sql;
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


 /*============ Get Free Resource =============*/ 
function getFreeResource($mysqli){
	try
	{
		$query = "SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,
		ISNULL((SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=FR.UNDER_ID),'')UNDER_CATEGORY,RESOURCE_LINK_LABEL,RESOURCE_LINK,
		CASE WHEN RESOURCE_CATEGORY='Category'
			THEN ISNULL((SELECT FEATURES+' | ' FROM FREE_RESOURCES_FEATURES WHERE ID=FR.ID AND ISDELETED=0 FOR XML PATH('')),'')
			ELSE ''
		END FEATURES,
		CASE WHEN RESOURCE_CATEGORY='Resource'
			THEN ISNULL((SELECT FEATURE_VALUE+' | ' FROM FREE_RESOURCES_FEATURE_VALUES WHERE ID=FR.ID AND ISDELETED=0 FOR XML PATH('')),'')
			ELSE ''
		END FEATURES_VALUES,HTML,RESOURCE_IMAGES,BLINK,COLOR
		FROM FREE_RESOURCES FR WHERE ISDELETED=0";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ID'] = (int) $row['ID'];
			$row['FEATURES'] = rtrim($row['FEATURES'],' | ');
			$row['FEATURES_VALUES'] = rtrim($row['FEATURES_VALUES'],' | ');
			$row['RESOURCE_IMAGES_ARRAY'] = explode(', ',$row['RESOURCE_IMAGES']);
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



/*============ Get Category =============*/ 
function getCategory($mysqli){
	try
	{
		$query = "SELECT ID,RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ISDELETED=0 AND RESOURCE_CATEGORY='Category'";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ID'] = (int) $row['ID'];
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
function deleteFreeResource($mysqli){
	try{   
			global $userid;
			$data = array();     
            $ID = ($_POST['ID'] == 'undefined' || $_POST['ID'] == '') ? 0 : $_POST['ID'];  
            $RESOURCE_CATEGORY = ($_POST['RESOURCE_CATEGORY'] == 'undefined' || $_POST['RESOURCE_CATEGORY'] == '') ? '' : $_POST['RESOURCE_CATEGORY'];  

			if($RESOURCE_CATEGORY == 'Category'){
				$query="EXEC [FREE_RESOURCES_SP] 3,$ID,'','',0,0,'',0,'','',$userid;
				UPDATE FREE_RESOURCES_FEATURES SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE ID=$ID";
			}else if($RESOURCE_CATEGORY == 'Resource'){
				$query="EXEC [FREE_RESOURCES_SP] 3,$ID,'','',0,0,'',0,'','',$userid;
				UPDATE FREE_RESOURCES_FEATURE_VALUES SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE ID=$ID";
			}
			else{ throw new Exception('Error.');}
			$stmt=sqlsrv_query($mysqli,$query);
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








//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
function saveCatFeatures($mysqli){
	try
	{
	   $data = array();
	   global $userid;
   
	   $cfid  = ($_POST['cfid'] == 'undefined' || $_POST['cfid'] == '') ? 0 : $_POST['cfid'];
	   $id  = ($_POST['id'] == 'undefined' || $_POST['id'] == '') ? 0 : $_POST['id'];
	   $txtFeatureName  = $_POST['txtFeatureName'] == 'undefined' ? '' : $_POST['txtFeatureName'];
	   
	   
	   $actionid = $cfid == 0 ? 1 : 2;

	   if($txtFeatureName == '')
	   {throw new Exception("Please Enter Feature Name.");}

	   $sql = "SELECT * FROM FREE_RESOURCES_FEATURES WHERE ID=$id AND FEATURES='$txtFeatureName'
			   AND CFID!=$cfid AND ISDELETED=0";
	   $row_count = unique($sql);

	   $data = array();
	   if($row_count == 0)
	   {
		   if($actionid == 1){
			   $query="INSERT INTO FREE_RESOURCES_FEATURES (ID,FEATURES,INSERTID) VALUES($id,'$txtFeatureName',$userid)";
			}else{
			   $query="UPDATE FREE_RESOURCES_FEATURES SET FEATURES='$txtFeatureName', UPDATEDATE=GETDATE(), UPDATEID=$userid WHERE CFID=$cfid";
		   }
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
			   if(!empty($cfid))$data['message'] = 'Feature successfully updated.';
			   else $data['message'] = 'Feature successfully inserted.';
			   echo json_encode($data);exit;
		   }
		   
	   }
	   else
	   {
		   $data['success'] = false;
		   $data['sql'] = $sql;
		   $data['message'] = 'Feature already exists.';
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


/*============ Get Category Features =============*/ 
function getCategoryFeatures($mysqli){
	try
	{
		$data = array();
		$ID = ($_POST['ID'] == 'undefined' || $_POST['ID'] == '') ? 0 : $_POST['ID'];
		if($ID == 0) throw new Exception('ID Missing.');
		$query = "SELECT CFID,FEATURES FROM FREE_RESOURCES_FEATURES WHERE ISDELETED=0 AND ID=$ID";
		$count = unique($query);
		if($count > 0){
			$CFID_ARRAY = array();
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result)) {
				$Feature = strtolower($row['FEATURES']);
				$Feature = str_replace(' ', '', $Feature);
				$row['ISMEDIA'] = $Feature=='media' ? 1 : 0;
				// IS ESSAY
				$word = "essay";
				$FeatureName = strtolower($row['FEATURES']);
				if(strpos($FeatureName, $word) !== false){
					$row['ISESSAY'] =1;
				}else{
					$row['ISESSAY'] =0;
				}
				// IS ESSAY
				$row['CFID'] = (int) $row['CFID'];
				$CFID_ARRAY[] = $row['CFID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
			$data['CFID_ARRAY'] = $CFID_ARRAY;
		}
		else{
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
function deleteCatFeature($mysqli){
	try{   
			global $userid;
			$data = array();     
            $CFID = ($_POST['CFID'] == 'undefined' || $_POST['CFID'] == '') ? 0 : $_POST['CFID'];  
			if($CFID == 0) throw new Exception('CFID Missing.');
			$delQuery = "UPDATE FREE_RESOURCES_FEATURES SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE CFID=$CFID;
						UPDATE FREE_RESOURCES_FEATURE_VALUES SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE CFID=$CFID";
			$data['$delQuery'] =$delQuery;
			$stmt=sqlsrv_query($mysqli,$delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Feature successfully deleted';
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

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%










//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES VALUES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
function saveCatFeaturesVal($mysqli){
	try
	{
		$data = array();
		global $userid;
	
		$rfvid = ($_POST['rfvid'] == 'undefined' || $_POST['rfvid'] == '') ? 0 : $_POST['rfvid'];
		$ID = ($_POST['ID'] == 'undefined' || $_POST['ID'] == '') ? 0 : $_POST['ID'];
		$Feature = (isset($_POST['Feature']) && !empty($_POST['Feature'])) ? json_decode($_POST['Feature'],true) : '';
		$RFVID_UPD = (isset($_POST['RFVID_UPD']) && !empty($_POST['RFVID_UPD'])) ? explode(",",$_POST['RFVID_UPD']) : '';
		$data['Feature'] = $Feature;
		$data['RFVID_UPD'] = $RFVID_UPD;
		
		// $data['success'] = false;
		// echo json_encode($data);exit;
	   
	   	$actionid = $rfvid == 0 ? 1 : 2;

		if($ID == 0){throw new Exception("Main ID Not Found.");}
		if($Feature == '' || count($Feature)<=0){throw new Exception("Please Enter Feature Values.");}

		// $sql = "SELECT * FROM FREE_RESOURCES_FEATURES WHERE ID=$id AND FEATURES='$txtFeatureName'
		// 		AND CFID!=$cfid AND ISDELETED=0";
		// $row_count = unique($sql);

		// if($row_count == 0)
		// {
			$inx = 0;
			foreach($Feature as $Fval){
				$CFID = $Fval['CFID'];
				$VAL = str_replace("'","''",$Fval['VAL']);
				
				if($actionid == 1){
					$data['VALL'][] = $CFID;
					$query="INSERT INTO FREE_RESOURCES_FEATURE_VALUES (ID,CFID,FEATURE_VALUE,INSERTID) 
							VALUES($ID,$CFID,'$VAL',$userid)";
				}else{
					$RFVID = (int)$RFVID_UPD[$inx];
					$query="UPDATE FREE_RESOURCES_FEATURE_VALUES SET FEATURE_VALUE='$VAL', 
							UPDATEDATE=GETDATE(), UPDATEID=$userid WHERE RFVID=$RFVID";
				}
				$data['$query'][]=$query;
				$stmt=sqlsrv_query($mysqli, $query);

				$inx++;
			}
		
			
			if(!$stmt)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				// $data['query'] = $query;
				$data['success'] = true;
				if(!empty($rfvid))$data['message'] = 'Feature Values successfully updated.';
				else $data['message'] = 'Feature Values successfully inserted.';
				echo json_encode($data);exit;
			}
		   
		// }
		// else
		// {
		// 	$data['success'] = false;
		// 	$data['sql'] = $sql;
		// 	$data['message'] = 'Feature already exists.';
		// 	echo json_encode($data);exit;
		// }

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



/*============ Get Category Features Val =============*/ 
function getCategoryFeaturesVal($mysqli){
	try
	{
		$data = array();
		$ID = ($_POST['ID'] == 'undefined' || $_POST['ID'] == '') ? 0 : $_POST['ID'];
		if($ID == 0) throw new Exception('ID Missing.');
		$CFID_ARRAY = (isset($_POST['CFID_ARRAY']) || !empty($_POST['CFID_ARRAY'])) ? $_POST['CFID_ARRAY'] : '';
		$data['$CFID_ARRAY']=$CFID_ARRAY;

		
		$query = "SELECT RFVID,ID,(SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=FRFV.ID AND ISDELETED=0)RESOURCE_NAME,
		CFID,(SELECT FEATURES FROM FREE_RESOURCES_FEATURES WHERE CFID=FRFV.CFID AND ISDELETED=0)FEATURES,FEATURE_VALUE 
		FROM FREE_RESOURCES_FEATURE_VALUES FRFV
		WHERE ID=$ID AND ISDELETED=0";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			$FEATURE_VAL = array();
			$NEW_CFID = array();
			$NEW_FV = array();
			$indx = 0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['RFVID'] = (int) $row['RFVID'];
				$row['CFID'] = (int) $row['CFID'];
				$row['ID'] = (int) $row['ID'];

				$Feature = strtolower($row['FEATURES']);
				$Feature = str_replace(' ', '', $Feature);
				$row['ISMEDIA'] = $Feature=='media' ? 1 : 0;

				// $NEW_FV['RFVID'][] = $row['RFVID'];
				$NEW_FV[$indx]['RFVID'] = $row['RFVID'];
				$NEW_FV[$indx]['VALUE'] = $row['FEATURE_VALUE'];
				$NEW_FV[$indx]['ISMEDIA'] = $row['ISMEDIA'];
				$NEW_CFID[] = (string)$row['CFID'];

				// if(in_array($row['CFID'], $CFID_ARRAY) $FEATURE_VAL[][]=$row['FEATURE_VALUE']

				// $intersection = array_intersect($CFID_ARRAY, $NEW_CFID);
				$data['$CFID_ARRAY']=$CFID_ARRAY;
				$data['$NEW_CFID'][]=$NEW_CFID;
				if ($CFID_ARRAY === $NEW_CFID) {
					// both arrays contain $value
					$FEATURE_VAL[]=$NEW_FV;
					$NEW_CFID = array();
					$NEW_FV = array();
					$indx=-1;
				}

				// for($i=0;$i<COUNT($CFID_ARRAY);$i++){
				// 	if($CFID_ARRAY[$i] == $row['CFID']) $FEATURE_VAL[$CFID_ARRAY[$i]][]=$row['FEATURE_VALUE'];
				// }

				$data['data'][] = $row;
				$indx++;
			}
			$data['FEATURE_VAL'] = $FEATURE_VAL;

			$data['success'] = true;
		}
		else{
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
function deleteCatFeatureVal($mysqli){
	try{   
			global $userid;
			$data = array();     
            $RFVID = ($_POST['RFVID'] == 'undefined' || $_POST['RFVID'] == '') ? 0 : $_POST['RFVID']; 
			// $data['$RFVID']=$RFVID;
			// echo json_encode($data);exit;	
			if($RFVID == '') throw new Exception('RFVID Missing.');
			$delQuery = "UPDATE FREE_RESOURCES_FEATURE_VALUES SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE() WHERE RFVID IN ($RFVID)";
			$data['$delQuery'] =$delQuery;
			$stmt=sqlsrv_query($mysqli,$delQuery);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Feature Values successfully deleted.';
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


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES VALUES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







