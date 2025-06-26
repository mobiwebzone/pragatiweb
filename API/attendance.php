<?php

/*============ Get All Attendance =============*/
function all_attendance_data($mysqli,$postData){
	try
	{
		$data = array();
		$uid = ($postData['uid'] == 'undefined' || $postData['uid'] == 'undefined') ? 0 : $postData['uid'];

		// ##### ATTENDANCE
		$query = "SELECT SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,
		(SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY,
		(SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,
		REMARK,HOMEWORK,HOMEWORK_DOC,
		(SELECT CASE WHEN REMARK='' THEN '' ELSE REMARK +' || ' END FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$uid FOR XML PATH(''))REMARK_CHILD,
		(SELECT HOMEWORK_DONE FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$uid)HOMEWORK_DONE,
		(SELECT STUDENTWORK FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$uid)STUDENTWORK,
		(SELECT HOMEWORK_IMG FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$uid)HOMEWORK_IMG,
		(SELECT DOC FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE SCCID=SCC.SCCID AND ISDELETED=0 AND REGID=$uid)DOC
		FROM STUDENT_COURSE_COVERAGE SCC
		WHERE ISDELETED=0 AND
		SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND REGID=$uid) AND HOMEWORK<>''
		ORDER BY CONVERT(DATE,CDATE,105) DESC,INVENTORY,CHAPTER";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
				$row['DOC_TYPE'] = pathinfo($row['DOC'], PATHINFO_EXTENSION);
				$row['HOMEWORK_DOC_TYPE'] = pathinfo($row['HOMEWORK_DOC'], PATHINFO_EXTENSION);
				$row['REMARK_CHILD'] = preg_replace('/\|\|(?=[^\|]*$)/', '', $row['REMARK_CHILD']);
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


/*============ SAVE HOMEWORK =============*/
function saveHomework($mysqli,$postData){
	try
	{
		$data = array();
		// $data['img'] =  $_FILES['file']['error'] === UPLOAD_ERR_OK;
        // echo json_encode($data);exit;
		$uid = ($postData['uid'] == 'undefined' || $postData['uid'] == '') ? 0 : $postData['uid'];
		$SCCID = ($postData['SCCID'] == 'undefined' || $postData['SCCID'] == '') ? 0 : $postData['SCCID'];
		$homework = ($postData['homework'] == 'undefined' || $postData['homework'] == '') ? '' : $postData['homework'];
		$homeworkDone = ($postData['homeworkDone'] == 'undefined' || $postData['homeworkDone'] == '' || $postData['homeworkDone'] == 'false') ? 0 : 1;
		$fileName = ($postData['fileName'] == 'undefined' || $postData['fileName'] == '') ? '' : $postData['fileName'];
		$fileOldName = ($postData['fileOldName'] == 'undefined' || $postData['fileOldName'] == '') ? '' : $postData['fileOldName'];
		$FinalFileName = (!$fileName || $fileName=='') ? $fileOldName : $fileName;

		// ##### ATTENDANCE
		// $query = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET HOMEWORK_DONE=$homeworkDone,STUDENTWORK='$homework',HOMEWORK_IMG='$FinalFileName' WHERE SCCID=$SCCID AND REGID=$uid";
		$query = "UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET HOMEWORK_DONE=?,STUDENTWORK=?,HOMEWORK_IMG=? WHERE SCCID=? AND REGID=?";
                $params = array($homeworkDone, $homework,$FinalFileName,$SCCID,$uid);
		$stmt = sqlsrv_query($mysqli, $query, $params);
		if($stmt){
			if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
				$folderPath = '../student_zone/images/homework/';
				// Create directory if it doesn't exist
				if (!file_exists($folderPath)) {
					$data['folter exist'] = false;
					mkdir($folderPath, 0777, true);
				}else{
					$data['folter exist'] = true;
				}
				// Generate unique filename
				// $filePath = $uid.'_'. $_FILES['file']['name'];
				
				// Move uploaded file to destination folder
				if (move_uploaded_file($_FILES['file']['tmp_name'], $folderPath.$fileName)) {
					// Remove old image if exists
                    if ($fileOldName) {
                        $oldImagePath = $folderPath.$fileOldName;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
							$data['success_unlink'] = true;
							$data['message_unlink'] = 'Unlink Success.';
                            // echo "Old image deleted: $oldImage\n";
                        } else {
							$data['success_unlink'] = false;
							$data['message_unlink'] = 'Unlink Failed.';
							// echo "Old image does not exist: $oldImage\n";
                        }
						$data['success_file'] = true;
						$data['message_file'] = 'Upload Success.';
                    }
				}else{
					$data['success_file'] = false;
					$data['message_file'] = 'Upload Failed.';
				}
			}
            $data['success'] = true;
			$data['message']="Homework successfully submitted.";
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