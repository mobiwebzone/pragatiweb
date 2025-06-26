<?php



require_once 'connection.php';

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "getDashBoardAnnouncement":getDashBoardAnnouncement($conn);break;
		case "getFreeResourcesCategory":getFreeResourcesCategory($conn);break;
		case "staticPageCarousel":staticPageCarousel($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function gets list of users from database
 */
/*============ Get Dashboard Announcements =============*/ 
function getDashBoardAnnouncement($mysqli){
	try
	{
		$query = "SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,
		ISNULL((SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=A.LOCID),'ALL')[LOCATION],
		ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=A.PLANID),'ALL')PLANNAME,
		ANNOUNCEMENT,CONVERT(VARCHAR,DB_ANNOUNCE_TILLDATE,106)DB_ANNOUNCE_TILLDATE
		FROM ANNOUNCEMENTS A WHERE ISDELETED=0 AND DB_ANNOUNCE=1";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['ANID'] = (int) $row['ANID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;

		}else{
			$data['success'] = false;
		}
		// Get Under Category
		$data['CATEGORIES'] = getFreeResourcesCategory($mysqli);
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ Get Dashboard Announcements =============*/ 


/*============ Get Free Resources Categories =============*/ 
function getFreeResourcesCategory($mysqli){
	$Categories = array();
	$query = "SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,BLINK,COLOR
	FROM FREE_RESOURCES WHERE ISDELETED=0 AND RESOURCE_CATEGORY='Category' AND UNDER_ID=0 order by SEQNO";
	$count = unique($query);
	if($count > 0){
		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$row['ID'] = (int) $row['ID'];
			$Categories[] = $row;
		}
	}else{
		$Categories = array();
	}
	return $Categories;
}
/*============ Get Free Resources Categories =============*/ 

	
/*============ GET STATIC PAGE CAROUSEL =============*/ 
function staticPageCarousel($mysqli){
	try
	{
		$data = array();
		$PAGE = ($_POST['PAGE'] == 'undefined' || $_POST['PAGE'] == '') ? '' : $_POST['PAGE'];
		if($PAGE=='') throw new Exception("Invalid Page.");

		$query = "SELECT SPCID,DISPLAY_TYPE,PIC,PIC_CAPTION,
				CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,PIC_INTERVAL,SEQNO 
				FROM STATIC_PAGE_CAROUSEL_DISPLAY WHERE ISDELETED=0 AND PAGENAME='$PAGE' AND 
				CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,PIC_FROMDT,105) AND CONVERT(DATE,PIC_TODT,105)
				ORDER BY SEQNO ASC";

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PIC'] = 'backoffice/images/static_page_carousel/'.$row['PIC'];
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
/*============ GET STATIC PAGE CAROUSEL =============*/ 


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}





