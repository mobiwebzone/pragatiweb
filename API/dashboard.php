<?php

/*============ Get Dash Info =============*/
function dashInfo($mysqli,$postData){
	try
	{
		$data = array();
		$uid = ($postData['uid'] == 'undefined' || $postData['uid'] == 'undefined') ? 0 : $postData['uid'];
		$locid = ($postData['locid'] == 'undefined' || $postData['locid'] == 'undefined') ? 0 : $postData['locid'];

		// ##### ATTENDANCE
		$queryAtt = "SELECT TOP 1 SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,
		(SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY,
		(SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,REMARK
		FROM STUDENT_COURSE_COVERAGE SCC
		WHERE ISDELETED=0 AND
		SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND REGID=$uid)
		ORDER BY SCCID DESC";
		$countAtt = unique($queryAtt);
		if($countAtt > 0){
			$resultAtt = sqlsrv_query($mysqli, $queryAtt);
			while($row = sqlsrv_fetch_array($resultAtt,SQLSRV_FETCH_ASSOC)){
                $data['data_att'][] = $row;
            }
            $data['success_att'] = true;
		}else{
			$data['success_att'] = false;
            $data['message_att'] = 'Failed.';
		}


		// ##### ANNOUNCEMENT
		$queryAnn = "SELECT TOP 1 ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS A
		WHERE ISDELETED=0 AND (PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=$uid AND CANCELLED=0 AND ACTIVATE=1) OR PLANID=0)
		AND (LOCID=$locid OR LOCID=0)
		ORDER BY CONVERT(DATE,ANDATE,105) DESC";
		$data['$queryAnn'] = $queryAnn;
		$countAnn = unique($queryAnn);
		if($countAnn > 0){
			$resultAnn = sqlsrv_query($mysqli, $queryAnn);
			while($row = sqlsrv_fetch_array($resultAnn,SQLSRV_FETCH_ASSOC)){
                $data['data_ann'][] = $row;
            }
            $data['success_ann'] = true;
		}else{
			$data['success_ann'] = false;
            $data['message_ann'] = 'Failed.';
		}


		// ##### MEETING LINKS
		$queryML = "SELECT MTID,MEETINGID,MEETINGLINK,MPASSCODE,
		(SELECT PLANNAME FROM PLANS WHERE PLANID=OML.PLANID)PLANNAME,
		(SELECT 
		  CASE WHEN CONVERT(DATE,ENDDATE,105)>CONVERT(DATE,GETDATE(),105)
			THEN 'No'
			ELSE 'YES'
		  END EXPIRE
		FROM PLANS WHERE PLANID=OML.PLANID)EXPIRE
		FROM ONLINE_MEETINGS_LINKS OML WHERE ISDELETED=0 
		AND PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE ISDELETED=0 AND REGID=$uid AND CANCELLED=0 AND ACTIVATE=1)";
		$countML = unique($queryML);
		if($countML > 0){
			$resultML = sqlsrv_query($mysqli, $queryML);
			while($row = sqlsrv_fetch_array($resultML,SQLSRV_FETCH_ASSOC)){
                $data['data_ml'][] = $row;
            }
            $data['success_ml'] = true;
		}else{
			$data['success_ml'] = false;
            $data['message_ml'] = 'Failed.';
		}


		// ##### RECEIPTS
		$queryRec = "SELECT TOP 1 RECID,CONVERT(VARCHAR,RECDATE,106)RECDATE,RECNO,RECNOFULL,AMOUNT,REFNO,REMARK,
		ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID),'No Plan')PLANNAME
		FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID=$uid ORDER BY RECID DESC";
		$countRec = unique($queryRec);
		if($countRec > 0){
			$resultRec = sqlsrv_query($mysqli, $queryRec);
			while($row = sqlsrv_fetch_array($resultRec,SQLSRV_FETCH_ASSOC)){
                $data['data_rec'][] = $row;
            }
            $data['success_rec'] = true;
		}else{
			$data['success_rec'] = false;
            $data['message_rec'] = 'Failed.';
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