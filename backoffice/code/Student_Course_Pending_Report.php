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
        case "getInventories":getInventories($conn);break;
        case "getStudents":getStudents($conn);break;
        case "getStudentReport":getStudentReport($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/* ========== GET INVENTORY =========== */
function getInventories($mysqli){
	try
	{
		$ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		if($ddlProduct == 0) throw new Exception('ProductID Not Found.');

		$query = "SELECT INVID,ITID,ISNULL((SELECT INVTYPE FROM INVENTORY_TYPES WHERE ITID=I.ITID),'')INVTYPE,SECID,
		(SELECT SECTION FROM SECTION_MASTER WHERE SECID=I.SECID)SECTION,CATID,
		(SELECT CATEGORY FROM SECTION_CATEGORIES WHERE CATID=I.CATID)CATEGORY,SUBCATID,
		(SELECT SUBCATEGORY FROM SECTION_SUB_CATEGORIES WHERE SUBCATID=I.SUBCATID)SUBCATEGORY,TOPICID,
		(SELECT TOPIC FROM SECTION_TOPICS WHERE TOPICID=I.TOPICID)TOPIC,PRODUCTID,
		ISNULL((SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=I.PRODUCTID),'')PRODUCT,TITLE,DESCR,COST,PUBID,
		ISNULL((SELECT MAKE_PUB FROM MAKE_PUBLISHERS WHERE PUBID=I.PUBID),'')PUBLISHER,ITYPE 
		FROM INVENTORY I WHERE ISDELETED=0 AND PRODUCTID=$ddlProduct ORDER BY TITLE";

		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['INVID'] = (int) $row['INVID'];
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
/* ========== GET INVENTORY =========== */





/* ========== GET STUDENTS =========== */
function getStudents($mysqli){
	try
	{
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		// if($ddlPlan == 0) throw new Exception('PlanID Not Found.');
		// if($ddlProduct == 0) throw new Exception('ProductID Not Found.');
		// if($ddlInventory == 0) throw new Exception('InventoryID Not Found.');

		$query = "SELECT DISTINCT REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID)STUDENT
		FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA
		WHERE ISDELETED=0";

		if($ddlPlan > 0) $query .=" AND (SELECT PLANID FROm STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)=$ddlPlan";

		$query .= " ORDER BY STUDENT";

		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result)) {
				$row['REGID'] = (int) $row['REGID'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
		$data['query']=$query;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/* ========== GET STUDENTS =========== */








/*============ Get Student Test Report =============*/ 
 function getStudentReport($mysqli){
	try
	{
		// $txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		// $txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$ddlPlan = ($_POST['ddlPlan'] == 'undefined' || $_POST['ddlPlan'] == '') ? 0 : $_POST['ddlPlan'];
		// $ddlProduct = ($_POST['ddlProduct'] == 'undefined' || $_POST['ddlProduct'] == '') ? 0 : $_POST['ddlProduct'];
		// $ddlInventory = ($_POST['ddlInventory'] == 'undefined' || $_POST['ddlInventory'] == '') ? 0 : $_POST['ddlInventory'];
		$ddlStudent = ($_POST['ddlStudent'] == 'undefined' || $_POST['ddlStudent'] == '') ? 0 : $_POST['ddlStudent'];
		$txtOrderby = $_POST['txtOrderby'] == 'undefined' ? '' : $_POST['txtOrderby'];
		$txtASC_DESC = $_POST['txtASC_DESC'] == 'undefined' ? 'ASC' : $_POST['txtASC_DESC'];

		// if($txtFromDT == '' || $txtToDT == '')throw new Exception('Select date first.');

		$data = array();
		$grid = '';

		
		// =========== ALL DATA ==============

		$queryForALL = "SELECT DISTINCT REGID,
		(SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID)STUDENT
		FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0";
		// AND (SELECT CONVERT(DATE,CDATE,105) FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0) BETWEEN '$txtFromDT' AND '$txtToDT'";

		if($ddlPlan > 0){
			$queryForALL .=" AND (SELECT PLANID FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)=$ddlPlan";
		}
		// if($ddlProduct > 0){
		// 	$queryForALL .=" AND (SELECT PRODUCTID FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)=$ddlProduct";
		// }
		// if($ddlInventory > 0){
		// 	$queryForALL .=" AND (SELECT INVID FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)=$ddlInventory";
		// }
		if($ddlStudent > 0){
			$queryForALL .=" AND REGID=$ddlStudent";
		}
		$queryForALL .=" ORDER BY STUDENT ASC";

		$countForALL = unique($queryForALL);
		
		
		
		

		// ----------------------------------------------------
		
		$grid .= '<div class="table-responsive mt-3 table2excel">';
		if($countForALL > 0){
			$resultForALL = sqlsrv_query($mysqli, $queryForALL);

			$sNO=0;
			while ($rowForALL = sqlsrv_fetch_array($resultForALL,SQLSRV_FETCH_ASSOC)) {
				$data['data'][] = $rowForALL;
				$REGID = (int)$rowForALL['REGID'];
				$STUDENT = $rowForALL['STUDENT'];
				
				$S_NO = $sNO+1;
				// STUDENT NAME
				$grid .= '<table class="table table-sm table-bordered">';
				$grid .= '<thead>';
				$grid .= '<tr class="" style="background:#4a4a4a">';
				$grid .= '<th colspan="7"><h3 class="font-weight-bold mb-0 font-18 text-center text-light">'.$S_NO.'.) &nbsp;&nbsp;'.$STUDENT.'</h3></th>';
				$grid .= '</tr>';
				$grid .= '</thead>';
				
				


				// GET DATA BY STUDENTID
				// $queryDetails="SELECT SCCID,(SELECT CONVERT(VARCHAR,CDATE,106) FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)CDATE,
				// (SELECT (SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID) FROM STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0)CHAPTER,
				// (SELECT PAGEFROM FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)PAGEFROM,
				// (SELECT PAGETO FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)PAGETO,
				// (SELECT REMARK FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)MAIN_REMARK,
				// (SELECT INVID FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0)INVID,
				// (SELECT (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID) FROm STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0)INVENTORY
				// FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA WHERE ISDELETED=0 AND REGID=$REGID 
				// ORDER BY (SELECT (SELECT PLANNAME FROM PLANS WHERE PLANID=SCC.PLANID) FROm STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0) ASC";
				// if($txtOrderby === 'INVENTORY'){
				// 	$queryDetails .=",(SELECT (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID) FROm STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0) $txtASC_DESC";
				// }
				// else if($txtOrderby === 'DATE'){
				// 	$queryDetails .=",(SELECT CONVERT(VARCHAR,CDATE,105) FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0) $txtASC_DESC";
				// }
				// else if($txtOrderby === 'CHAPTER'){
				// 	$queryDetails .=",(SELECT (SELECT CHAPNO FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID) FROM STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0) $txtASC_DESC";
				// }
				// else{
				// 	$queryDetails .=",(SELECT (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID) FROm STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0) ASC,
				// 	(SELECT (SELECT CHAPNO FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID) FROM STUDENT_COURSE_COVERAGE SCC WHERE SCCID=SCCA.SCCID AND ISDELETED=0) ASC,
				// 	(SELECT CONVERT(VARCHAR,CDATE,105) FROM STUDENT_COURSE_COVERAGE WHERE SCCID=SCCA.SCCID AND ISDELETED=0) DESC";
				// }


				$queryDetails="EXEC [RPT_COURSE_COVERAGE_PENDING] $REGID";
				

				$data['$queryDetails'] = $queryDetails;
				// $data['queryForALL'] = $queryForALL;
				// echo json_encode($data);exit;	
				$countDetails = unique($queryDetails);
				if($countDetails > 0){
					$resultDetails = sqlsrv_query($mysqli, $queryDetails);
					$grid .= '<tbody>';
					$grid .='<tr class="" style="background-color: #dbdbdb!important;">
								<th>Sno.</th>
								<th>Inventory</th>
								<th>Chapter</th>
								<th>Chapter No.</th>
							</tr>';

					$INVID = 0;	
					$D_NO=0;						
					while ($rowDetails = sqlsrv_fetch_array($resultDetails,SQLSRV_FETCH_ASSOC)) {
						$DSNO = $D_NO+1;
						$grid .= '<tr>';
						$grid .= '<td>'.$DSNO.'</td>';

						$NEW_INVID = $rowDetails['INVID'];
						if($INVID == 0 || $NEW_INVID!=$INVID){
							$INVID = $NEW_INVID;
							$data['chk'][] = 'if';
							$grid .= '<td>'.$rowDetails['INVENTORY'].'</td>';
						}else{
							if($NEW_INVID!=$INVID){
								$INVID = 0;
							}
							$data['chk'][] = 'else';
							$grid .= '<td></td>';
						}
						$grid .= '<td>'.$rowDetails['DESCR'].'</td>';
						$grid .= '<td>'.$rowDetails['CHAPNO'].'</td>';
						$grid .= '</tr>';
						// $grid .= '';

						$D_NO++;
					}
					$grid .= '</tbody>';
				}else{

				}
				
				
				$sNO++;
				$grid .= '</table>';
			}
				
			$data['success'] = true;
		}
		else{
			$grid .= '<div class="row">
			<div class="col-12">
			<h3 class="text-danger text-center font-weight-bold">Data Not Found.</h3>
			</div>
			</div>';
			$data['success'] = false;
		}

		$grid .= '</div>';
		
		$data['$queryForALL'] = $queryForALL;
		$data['StudentData'] = $grid;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
/*============ Get Student Test Report =============*/ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







