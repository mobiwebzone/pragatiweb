<?php

/*============ Get All Sms =============*/
function all_sms_data($mysqli,$postData){
	try
	{
		$data = array();
		$uid = ($postData['uid'] == 'undefined' || $postData['uid'] == 'undefined') ? 0 : $postData['uid'];
		
		$query = "SELECT MSGID,CONVERT(VARCHAR,MSGDATE,21)MSGDATE,MOBILENO,TEXTMESSAGE
        FROM TEXT_MESSAGES WHERE MSGTYPE='OUTGOING API' AND STUDENTTYPE='Registered' AND REGID=$uid ORDER BY MSGID DESC";
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