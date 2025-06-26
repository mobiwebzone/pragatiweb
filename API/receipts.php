<?php

/*============ Get All Receipts =============*/
function all_receipt_data($mysqli,$postData){
	try
	{
		$data = array();
		$uid = ($postData['uid'] == 'undefined' || $postData['uid'] == 'undefined') ? 0 : $postData['uid'];
		
		$query = "SELECT RECID,CONVERT(VARCHAR,RECDATE,106)RECDATE,RECNO,RECNOFULL,PLANID,
		ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID),'No Plan')PLANNAME,INSTALLMENT,AMOUNT,
		PMID,(SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=SR.PMID)PAYMODE,REFNO,
		(SELECT PAYPLAN FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)PAYPLAN,
		REMARK
		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID=$uid
		ORDER BY CONVERT(DATE,RECDATE,105) DESC";
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