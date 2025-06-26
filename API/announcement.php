<?php

/*============ Get All Announcement =============*/
function all_announcement_data($mysqli,$postData){
	try
	{
		$data = array();
		$uid = ($postData['uid'] == 'undefined' || $postData['uid'] == 'undefined') ? 0 : $postData['uid'];
		$locid = ($postData['locid'] == 'undefined' || $postData['locid'] == 'undefined') ? 0 : $postData['locid'];
		
		$query = "SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS A
		WHERE ISDELETED=0 AND (PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$uid AND CANCELLED=0 AND ACTIVATE=1) OR PLANID=0)
		AND (LOCID=$locid OR LOCID=0)
		ORDER BY CONVERT(DATE,ANDATE,105) DESC";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
                $data['data'][] = $row;
            }
            $data['success'] = true;
		}else{
			$data['success'] = false;
            $data['message'] = 'Failed.';
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

?>