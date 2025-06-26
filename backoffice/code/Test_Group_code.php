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
        case "saveData":saveData($conn);break;
        case "getAllGroups":getAllGroups($conn);break;
        case "getSelectedTestbyGroupName":getSelectedTestbyGroupName($conn);break;
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



// =============== SAVE DATA ==================
 function saveData($mysqli){
     try
     {
		$data = array();
        global $userid;
    
        $tgid  = ($_POST['tgid'] == 'undefined' || $_POST['tgid'] == '') ? 0 : $_POST['tgid'];
        $groupname  = $_POST['groupname'] == 'undefined' ? '' : $_POST['groupname'];
        $txtGroupName  = $_POST['txtGroupName'] == 'undefined' ? '' : $_POST['txtGroupName'];
        $selectedTest  = (empty($_POST['selectedTest']) || $_POST['selectedTest'] == 'undefined') ? '' : $_POST['selectedTest'];
		if($selectedTest == ''){throw new Exception ('Select Product First.');}
		$selectedTest = explode(',',$selectedTest);
		// $data['selectedTest']=$selectedTest;

		$actionid = $tgid == 0 ? 1 : 2;
		
		
		if($txtGroupName == '')
		{throw new Exception("Please Enter Test Group Name.");}

		// $delOldTest = "DELETE FROM TESTS_TO_PRODUCTS WHERE TESTID=$ddlTest";
		// $stmtDOT=sqlsrv_query($mysqli, $delOldTest);
		// $chkDataExist = "SELECT * FROM TEST_GROUPS WHERE ISDELETED=0 AND GROUPNAME = '$txtGroupName'";


		
		$chkDataExist = "SELECT * FROM TEST_GROUPS WHERE GROUPNAME = '$txtGroupName' AND TGID!=$tgid AND ISDELETED=0";
		sqlsrv_query($mysqli, $chkDataExist);
		$countDataExist = unique($chkDataExist);
		// $data['$chkDataExist'] = $chkDataExist;
		// echo json_encode($data);exit;

		if($countDataExist == 0){

			if($actionid == 1){
				// Check test exist
				for($i=0; $i<COUNT($selectedTest); $i++){
					$Test = $selectedTest[$i];
					// $data['selectedTest']=$selectedTest;
					if($Test > 0){
						$checkTestExist = "SELECT (SELECT TESTDESC +' ('+ CAST(TESTYEAR AS VARCHAR)+')' FROM TEST_MASTER WHERE TESTID=TG.TESTID)TEST,
						(SELECT GROUPNAME FROM TEST_GROUPS WHERE TGID=TG.TGID)GROUPNAME 
						FROM TEST_GROUPS_DETAILS TG WHERE TESTID = $Test AND ISDELETED=0";
						$resultTestExist=sqlsrv_query($mysqli, $checkTestExist);
						$countTestExist = unique($checkTestExist);
						// $data['$checkTestExist'][]=$checkTestExist;
						// echo json_encode($data);exit;
						if($countTestExist > 0){
							$rowTestExist = sqlsrv_fetch_array($resultTestExist);
							$T = $rowTestExist['TEST'];
							$G = $rowTestExist['GROUPNAME'];
							$data['success'] = false;
							$data['checkTestExist'] = $checkTestExist;
							$data['message'] = '"'.$T.'" Test already exist in "'.$G.'"';
							echo json_encode($data);exit;
						}
					}
				}
			}

			$Insertquery="EXEC [TEST_GROUPS_SP] $actionid,$tgid,'$txtGroupName',$userid";
			$Insertstmt=sqlsrv_query($mysqli, $Insertquery);
			if($Insertstmt === false){
				$data['success'] = false;
				$data['Insertquery'] = $Insertquery;
				echo json_encode($data);exit;
			}else{
				$InsertRow = sqlsrv_fetch_array($Insertstmt, SQLSRV_FETCH_ASSOC);
				$GET_TGID = $InsertRow['TGID']; 

				if($GET_TGID > 0 && $actionid == 1){
					// Insert Data
					for($i=0; $i<COUNT($selectedTest); $i++){
						$Test = $selectedTest[$i];
						if($Test > 0){
							$query="INSERT INTO TEST_GROUPS_DETAILS (TGID,TESTID,INSERTID) VALUES($GET_TGID,$Test,$userid)";
							// $data['$query'][] = $query;
							$stmt=sqlsrv_query($mysqli, $query);
							
							if($stmt === false)
							{
								$data['query'] = $query;
								$data['success'] = false;
								$data['message'] = '--- Error ---';
								echo json_encode($data);exit;
							}
						}
					}

					$data['query'] = $query;
					$data['success'] = true;
					$data['message'] = 'Data Inserted.';
					echo json_encode($data);exit;
				}
				else{
					$data['Insertquery'] = $Insertquery;
					$data['success'] = true;
					$data['message'] = 'Group Name Updated.';
					echo json_encode($data);exit;
				}
				
			}


			
		}
		else{
			$data['$chkDataExist'] = $chkDataExist;
			$data['success'] = false;
			$data['message'] = '"'.$txtGroupName.'" Group Already Exist.';
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






/*============ GET ALL GROUPS =============*/ 
 function getAllGroups($mysqli){
	try
	{
		$data = array();

		$query = "SELECT TGID,GROUPNAME,
		(SELECT (SELECT TESTDESC +' ('+ CAST(TESTYEAR AS VARCHAR) +')' FROM TEST_MASTER WHERE TESTID=TGD.TESTID)+', ' FROM TEST_GROUPS_DETAILS TGD WHERE ISDELETED=0 AND TGID=TG.TGID FOR XML PATH(''))TEST
		FROM TEST_GROUPS TG WHERE ISDELETED=0
		ORDER BY GROUPNAME";

		$result = sqlsrv_query($mysqli, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row['TEST'] = rtrim($row['TEST'],', ');
			$row['TEST'] = str_replace(', ', ",\n", $row['TEST']);
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
/*============ GET ALL GROUPS =============*/ 






/* ========== GET ALL SELECTED PRODUCT =========== */
 function getSelectedTestbyGroupName($mysqli){
	try
	{
		$data = array();
		$TESTID = array();
		$tgid = ($_POST['tgid'] == 'undefined' || $_POST['tgid'] == '') ? 0 : $_POST['tgid'];
		if($tgid == 0){throw new Exception('TGID Not Found.');}
		$query = "SELECT TESTID TT,
		CASE WHEN TESTID = (SELECT TESTID FROM TEST_GROUPS_DETAILS WHERE ISDELETED=0 AND TESTID=TM.TESTID AND TGID=$tgid)
				THEN CAST(TESTID AS VARCHAR) ELSE '0'
		END TESTID
		FROM TEST_MASTER TM WHERE ISDELETED=0 ORDER BY TT DESC";

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $row;
				array_push($TESTID, $row['TESTID']);
			}
			$data['success'] = true;
		}
		else{
			$data['success'] = false;
			$data['message'] = 'Data not found.';
		}
		$data['TESTID'] = $TESTID;
		$data['TESTID_COUNT'] = array_sum($TESTID);
		$data['$query']=$query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);exit;
	}
}
/* ========== GET ALL SELECTED PRODUCT =========== */







/* =========== Delete =========== */ 
function delete($mysqli){
	try{   
			global $userid;
			$data = array();     
            $tgid = ($_POST['tgid'] == 'undefined' || $_POST['tgid'] == '') ? 0 : $_POST['tgid'];
			if($tgid == 0){throw new Exception('TGID Not Found.');}

			$del = "EXEC [TEST_GROUPS_SP] 3,$tgid,'',$userid";

			$stmt=sqlsrv_query($mysqli, $del);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Group successfully deleted.';
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





function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







