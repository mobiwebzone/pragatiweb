<?php
session_start();
require_once '../../code/connection.php';

// require __DIR__ . '../../../Twilio/autoload.php';
// use Twilio\Rest\Client;

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}



if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "testAuthorizeAPI":testAuthorizeAPI($conn);break;
        case "getOnlineTransactions":getOnlineTransactions($conn);break;
        case "deleteTransaction":deleteTransaction($conn);break;

        case "savePayment":savePayment($conn);break;
		
		// ============ EXTRA ===========
        case "getPlans":getPlans($conn);break;
        case "getProductsByPlan":getProductsByPlan($conn);break;
        case "getStudentByPlanProduct":getStudentByPlanProduct($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


/*============ GET SETTLED/UNSETTLED TRANSACTIONS =============*/ 
function testAuthorizeAPI($mysqli){
	try
	{
		global $userid;
		$data=array();
	
		$FromDT = $_POST['FromDT'] == 'undefined' ? '' : $_POST['FromDT'];
		$ToDT = $_POST['ToDT'] == 'undefined' ? '' : $_POST['ToDT'];

		$firstSettlementDate=new DateTime($FromDT);
		$lastSettlementDate=new DateTime($ToDT);
		
		// use net\authorize\api\contract\v1 as AnetAPI;
		// use net\authorize\api\controller as AnetController;
		require '../../AUTH_2/autoload.php';
		/* Create a merchantAuthenticationType object with authentication details
		   retrieved from the constants file */
		$merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
		$merchantAuthentication->setName('2v79Z5j6gPdJ');
		$merchantAuthentication->setTransactionKey('7rUC84726Zb65ZqE');
		
		// Set the request's refId
		$refId = 'ref' . time();
	
		$request = new net\authorize\api\contract\v1\GetSettledBatchListRequest();
		$request->setMerchantAuthentication($merchantAuthentication);
		$request->setIncludeStatistics(true);
		
		// Both the first and last dates must be in the same time zone
		// The time between first and last dates, inclusively, cannot exceed 31 days.
		$request->setFirstSettlementDate($firstSettlementDate);
		$request->setLastSettlementDate($lastSettlementDate);
	
		$controller = new net\authorize\api\controller\GetSettledBatchListController ($request);
	
		$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
	
		if (($response != null) && ($response->getMessages()->getResultCode() == "Ok"))
		{
			$BATCHID_ARRAY=array();
			foreach($response->getBatchList() as $batch)
			{
			  $BATCHID_ARRAY[] = $batch->getBatchId();
			  $data['batch'][]=$batch;

			}
			$data['BATCHID_ARRAY']=$BATCHID_ARRAY;
			$data['response']=$response;
			

			// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
			// %%%%%%%%%% GET TRANSACTIONS BY BATCHID %%%%%%%%%%
			// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
			if(count($BATCHID_ARRAY)>0){
				// %%%%%%%% SETTLED START %%%%%%%
				//Setting a valid batch Id for the Merchant
				$request_TRANS = new net\authorize\api\contract\v1\GetTransactionListRequest();
				$request_TRANS->setMerchantAuthentication($merchantAuthentication);
				
				for($i=0; $i<count($BATCHID_ARRAY);$i++){

					$BATCHID =$BATCHID_ARRAY[$i];
					// $batchId = "889364998";
					$request_TRANS->setBatchId($BATCHID);
				
					$controller_TRANS = new net\authorize\api\controller\GetTransactionListController ($request_TRANS);
				
					$response_TRANS = $controller_TRANS->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
				
					if (($response_TRANS != null) && ($response_TRANS->getMessages()->getResultCode() == "Ok"))
					{
						// echo "SUCCESS: Get Transaction List for BatchID : " . $batchId  . "\n\n";
						$TRAN_DATA = array();
						if ($response_TRANS->getTransactions() !== null) {
							$TRAN_DATA = $response_TRANS->getTransactions();

							// %%%%%%%%% INSERT DATA START %%%%%%%%%
							if(count($TRAN_DATA)>0){
								$data['AAA'][]= $TRAN_DATA;
								// $data['AAAcount'][]= count($TRAN_DATA);

								foreach($TRAN_DATA as $PaymentData){
									$FINAL_DATA = json_decode(json_encode($PaymentData), true);

									$PAYDATE = new DateTime($FINAL_DATA['submitTimeLocal']);
									$PAYDATE = $PAYDATE->format("Y-m-d H:i:s");
									$TRANSID = $FINAL_DATA['transId'];
									$FIRSTNAME = $FINAL_DATA['firstName'];
									$LASTNAME = $FINAL_DATA['lastName'];
									$ACCNO = $FINAL_DATA['accountNumber'];
									$STATUS = $FINAL_DATA['transactionStatus'];
									$AMOUNT = $STATUS==='refundSettledSuccessfully' ? '-'.$FINAL_DATA['settleAmount'] : $FINAL_DATA['settleAmount'];
									
									// CHECK TRANSID EXIST
									$qryChkTransID = "SELECT * FROM ONLINE_PAYMENT_TRANSACTIONS WHERE TRANSID='$TRANSID'";
									$countChkTransID = unique($qryChkTransID);
									if($countChkTransID<=0){

										$queryInsert = "INSERT INTO ONLINE_PAYMENT_TRANSACTIONS (BID,TRANSDATE,TRANSID,AMOUNT,FIRST_NAME,LAST_NAME,ACCNO,[STATUS],SETTLED_UNSETTLED,INSERTID)
														VALUES ('$BATCHID','$PAYDATE','$TRANSID',$AMOUNT,'$FIRSTNAME','$LASTNAME','$ACCNO','$STATUS','SETTLED',$userid)";
										$stmtInsert=sqlsrv_query($mysqli, $queryInsert);		
										if(!$stmtInsert) throw new Exception($queryInsert);
									}
								}
							}
							// %%%%%%%%% INSERT DATA END %%%%%%%%%

							$data['response_TRANS'][]=$TRAN_DATA;
						}
						$data['success'] = true;
						$data['message'] = 'Get Transactions successfully.';
					}
				}
				// %%%%%%%% SETTLED END %%%%%%%
				
				
				
				
				// %%%%%%%% UNSETTLED START %%%%%%%
				$request_UN_SET = new net\authorize\api\contract\v1\GetUnsettledTransactionListRequest();
    			$request_UN_SET->setMerchantAuthentication($merchantAuthentication);
				$controller_UN_SET = new net\authorize\api\controller\GetUnsettledTransactionListController($request_UN_SET);
    			$response_UN_SET = $controller_UN_SET->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);

				if (($response_UN_SET != null) && ($response_UN_SET->getMessages()->getResultCode() == "Ok"))
				{
					
					$TRAN_DATA_UN_SET = array();
					if ($response_UN_SET->getTransactions() !== null) {
						$TRAN_DATA_UN_SET = $response_UN_SET->getTransactions();

						// %%%%%%%%% INSERT DATA START %%%%%%%%%
						if(count($TRAN_DATA_UN_SET)>0){
							$data['TRANS_AAA'][]= $TRAN_DATA_UN_SET;
							// $data['AAAcount'][]= count($TRAN_DATA_UN_SET);

							foreach($TRAN_DATA_UN_SET as $PaymentData){
								$FINAL_DATA_UN_SET = json_decode(json_encode($PaymentData), true);

								$PAYDATE_1 = new DateTime($FINAL_DATA_UN_SET['submitTimeLocal']);
								$PAYDATE_1 = $PAYDATE_1->format("Y-m-d H:i:s");
								$TRANSID_1 = $FINAL_DATA_UN_SET['transId'];
								$AMOUNT_1 = $FINAL_DATA_UN_SET['settleAmount'];
								$FIRSTNAME_1 = $FINAL_DATA_UN_SET['firstName'];
								$LASTNAME_1 = $FINAL_DATA_UN_SET['lastName'];
								$ACCNO_1 = $FINAL_DATA_UN_SET['accountNumber'];
								$STATUS_1 = $FINAL_DATA_UN_SET['transactionStatus'];
								
								// CHECK TRANSID EXIST
								$qryChkTransID = "SELECT * FROM ONLINE_PAYMENT_TRANSACTIONS WHERE TRANSID='$TRANSID_1'";
								$countChkTransID = unique($qryChkTransID);
								if($countChkTransID<=0){

									$queryInsert = "INSERT INTO ONLINE_PAYMENT_TRANSACTIONS (BID,TRANSDATE,TRANSID,AMOUNT,FIRST_NAME,LAST_NAME,ACCNO,[STATUS],SETTLED_UNSETTLED,INSERTID)
													VALUES ('-','$PAYDATE_1','$TRANSID_1',$AMOUNT_1,'$FIRSTNAME_1','$LASTNAME_1','$ACCNO_1','$STATUS_1','UNSETTLED',$userid)";
									$stmtInsert=sqlsrv_query($mysqli, $queryInsert);		
									if(!$stmtInsert) throw new Exception($queryInsert);
								}
							}

							$data['response_UN_SET'][]=$TRAN_DATA_UN_SET;
	
							$data['success_unsettled'] = true;
						}
						// %%%%%%%%% INSERT DATA END %%%%%%%%%

					}else{

						$data['success_unsettled'] = false;
						$data['message_unsettled'] = 'Unsettled Transaction Not Found';
					}
				}
				else
				{
					$data['success_unsettled'] = false;
					$errorMessages_UN_SET = $response_UN_SET->getMessages()->getMessage();
					$data['message_unsettled'] = $errorMessages_UN_SET[0]->getText();
				}
				// %%%%%%%% UNSETTLED END %%%%%%%
			}else{
				$data['success'] = false;
				$data['message'] = 'Batch ID not Found.';
			}
		}
		else
		{
			$data['success'] = false;
			$errorMessages = $response->getMessages()->getMessage();
			$data['message'] = $errorMessages[0]->getText();
		}
		// return $response;
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
/*============ GET SETTLED/UNSETTLED TRANSACTIONS =============*/ 





// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// =============== SAVE PAYMENT ==================
function savePayment($mysqli){
	try
	{
		global $userid;
		$data = array();

		$REGID = ($_POST['REGID'] == 'undefined' || $_POST['REGID'] == '') ? 0 : $_POST['REGID'];
		$TID = ($_POST['TID'] == 'undefined' || $_POST['TID'] == '') ? 0 : $_POST['TID'];
		$AMOUNT = ($_POST['AMOUNT'] == 'undefined' || $_POST['AMOUNT'] == '') ? 0 : $_POST['AMOUNT'];
		$TRANSID = ($_POST['TRANSID'] == 'undefined' || $_POST['TRANSID'] == '') ? 0 : $_POST['TRANSID'];
		$TRANSDATE = $_POST['TRANSDATE'] == 'undefined' ? '' : $_POST['TRANSDATE'];

		if($REGID == 0)throw new Exception('REGID Not Found.');
		if($TID == 0)throw new Exception('TID Not Found.');
		if($TRANSID == 0)throw new Exception('TRANSID Not Found.');
		if($TRANSDATE == '')throw new Exception('TRANSDATE Not Found.');
		
		// echo json_encode($data);exit;


		// !!!!!!!!!!! CHECK REFNO !!!!!!!!!!!
		$chkRef = "SELECT * FROM STUDENT_RECEIPTS WHERE REFNO='$TRANSID'";
		$refcnt=unique($chkRef);
		if($refcnt > 0)throw new Exception("Ref. no. already exist.");

		$query="EXEC [STUDENT_RECEIPTS_SP] 1,0,'$TRANSDATE',$REGID,0,0,$AMOUNT,13,'$TRANSID','authorize.net',$userid";
		$stmt=sqlsrv_query($mysqli, $query);
		if($stmt === false)
		{
			// die( print_r( sqlsrv_errors(), true));
			// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			$data['success'] = false;
			$data['query'] = $query;
		}
		else
		{	
			// %%%%%% UPDATE ONLINE PAYMENT TRANSACTION UPLOADED %%%%%%%
			$upd = "UPDATE ONLINE_PAYMENT_TRANSACTIONS SET UPLOADED=1 WHERE TID=$TID";
			sqlsrv_query($mysqli, $upd);

			$data['query'] = $query;
			$data['success'] = true;
			$data['message'] = 'Transaction successfully inserted.';
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
// =============== SAVE PAYMENT ==============



/*============ Get Online Transactions =============*/ 
function getOnlineTransactions($mysqli){
	try
	{
		$data = array();

		// $txtFromDT = $_POST['txtFromDT'] == 'undefined' ? '' : $_POST['txtFromDT'];
		// $txtToDT = $_POST['txtToDT'] == 'undefined' ? '' : $_POST['txtToDT'];
		$query = "SELECT TID,BID,CONVERT(VARCHAR,TRANSDATE,21)TRANSDATE,TRANSID,AMOUNT,FIRST_NAME,LAST_NAME,ACCNO,UPLOADED,[STATUS],SETTLED_UNSETTLED,
		ISNULL((SELECT CONVERT(VARCHAR,REGID)+',,,'+ ISNULL(R.FIRSTNAME,'-')+' '+ISNULL(R.LASTNAME,'-')+',,,' +ISNULL(P1_FIRSTNAME,'-')+' '+ISNULL(P1_LASTNAME,'-')+',,,'+ISNULL(P2_FIRSTNAME,'-')+' '+ISNULL(P2_LASTNAME,'-')+' | ' 
			FROM REGISTRATIONS R WHERE R.ISDELETED=0 AND APPROVED=1 AND 
			(R.FIRSTNAME LIKE'%'+OPT.FIRST_NAME+'%' OR R.LASTNAME LIKE'%'+OPT.LAST_NAME+'%' 
			OR P1_FIRSTNAME LIKE'%'+OPT.FIRST_NAME+'%' OR P1_LASTNAME LIKE'%'+OPT.LAST_NAME+'%'
			OR P2_FIRSTNAME LIKE'%'+OPT.FIRST_NAME+'%' OR P2_LASTNAME LIKE'%'+OPT.LAST_NAME+'%') 
			FOR XML PATH('')),'')SUGGEST_STUDENT
		FROM ONLINE_PAYMENT_TRANSACTIONS OPT 
		WHERE ISDELETED=0 AND UPLOADED=0
		ORDER BY CONVERT(DATE,TRANSDATE,105) DESC";

		$data['$query '] = $query;
		// echo json_encode($data);exit;

		$count = unique($query);
		$result = sqlsrv_query($mysqli, $query);
		if($count > 0){
			$SUGGEST = array();
			$P_COUNT = 0;
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$date=date_create($row['TRANSDATE']);
				$row['TRANSDATE_SHOW']= date_format($date,"d-m-Y h:i:s a");

				if(!empty($row['SUGGEST_STUDENT'])){
					$row['SUGGEST_STUDENT'] = rtrim($row['SUGGEST_STUDENT'],' | ');
					$SUGGEST = explode(' | ',$row['SUGGEST_STUDENT']);
					$data['$SUGGEST'][]=$SUGGEST;
	
					$SUGGEST_CHILD = array();
					for($i=0;$i<count($SUGGEST);$i++){
						$SUGGEST_CHILD = explode(',,,',$SUGGEST[$i]);
						$data['aa'][]=$SUGGEST_CHILD;
						$row['STUDENT_TABLE'][$i]['REGID']=$SUGGEST_CHILD[0];
						$row['STUDENT_TABLE'][$i]['STUDENTNAME']=$SUGGEST_CHILD[1];
						$row['STUDENT_TABLE'][$i]['P1NAME']=$SUGGEST_CHILD[2];
						$row['STUDENT_TABLE'][$i]['P2NAME']=$SUGGEST_CHILD[3];
					}
				}
				// $row['STUDENT_TABLE']=$SS;
				if($row['SETTLED_UNSETTLED'] === 'SETTLED'){
					$data['settled'][] = $row;
				}else{
					$data['unsettled'][] = $row;
				}

				$P_COUNT++;
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
/*============ Get Online Transactions =============*/ 



/*============ Delete Transactions =============*/ 
function deleteTransaction($mysqli){
	try{   
			global $userid;
			$data = array();     
            $TID = ($_POST['TID'] == 'undefined' || $_POST['TID'] == '') ? 0 : $_POST['TID'];  
            $txtReasone = ($_POST['txtReasone'] == 'undefined' || $_POST['txtReasone'] == '') ? '' : $_POST['txtReasone'];  
			if($TID == 0)throw new Exception('TID Error.');

			$query = "UPDATE ONLINE_PAYMENT_TRANSACTIONS SET ISDELETED=1,DELETEID=$userid,DELETEDATE=GETDATE(),DELETE_REASONE='$txtReasone' 
					WHERE TID=$TID";
			$stmt=sqlsrv_query($mysqli, $query);
			if( $stmt === false ) 
			{
				die( print_r( sqlsrv_errors(), true));
				throw new Exception( $mysqli->sqlstate );
			}
			else
			{
				$data['success'] = true;
				$data['message'] = 'Transaction successfully deleted';
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
/*============ Delete Transactions =============*/ 




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%% EXTRA %%%%%%%%%%%%%%%%%%%%%%%%%%
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

/*============ Get Plans =============*/ 
function getPlans($mysqli){
	try
	{
		$query = "SELECT PLANID AS id,PLANNAME AS label
		--,convert(varchar, STARTDATE, 106)STARTDATE,
		--convert(varchar, ENDDATE, 106)ENDDATE,PRICE,INST_AMOUNT,INST_FREQ,INST_NO,
		--convert(varchar, DISPLAYFROMDATE, 106)DISPLAYFROMDATE,
		--convert(varchar, DISPLAYTODATE, 106)DISPLAYTODATE,DISPLAYCOLOR,ACTIVE
		FROM PLANS WHERE ISDELETED=0 ORDER BY label";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
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
/*============ Get Plans =============*/ 



/*============ Get Product By Plan =============*/ 
function getProductsByPlan($mysqli){
	try
	{
		
		$planid = (!empty($_POST['PLANID'])) ? $_POST['PLANID'] : '';
		if($planid == '')throw new Exception('PlanID Not Found.');
		$planid = implode(',',$planid);
		$query = "SELECT DISTINCT PRODUCTID AS id,(SELECT PRODUCT_DESC FROM PRODUCTS WHERE PRODUCT_ID=SCC.PRODUCTID)label 
		FROM STUDENT_COURSE_COVERAGE SCC WHERE ISDELETED=0 AND PLANID IN ($planid) ORDER BY label";
		// $data['success'] = false;
		// $data['planid']=$planid;
		// $data['query']=$query;
		// echo json_encode($data);exit;

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
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
/*============ Get Product By Plan =============*/ 



/*============ Get Student By Plan_Product =============*/ 
function getStudentByPlanProduct($mysqli){
	try
	{
		
		$planid = (!empty($_POST['PLANID'])) ? $_POST['PLANID'] : '';
		if($planid == '')throw new Exception('PlanID Not Found.');
		$planid = implode(',',$planid);
		$data['$planid']=$planid;

		// $productid = (!empty($_POST['PRODUCTID'])) ? $_POST['PRODUCTID'] : '';
		// if($productid == '')throw new Exception('ProductID Not Found.');
		// $productid = implode(',',$productid);
		// $data['$productid']=$productid;

		// echo json_encode($data);exit;

		// $query = "SELECT DISTINCT REGID AS id, 
		// (SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID) label,
		// ISNULL((SELECT FIRSTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')FIRSTNAME,
		// ISNULL((SELECT LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')LASTNAME,
		// ISNULL((SELECT PHONE FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')PHONE,
		// ISNULL((SELECT P1_FIRSTNAME+' '+P1_LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')PARENT1,
		// ISNULL((SELECT P1_PHONE FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')P1_PHONE,
		// ISNULL((SELECT P2_FIRSTNAME+' '+P2_LASTNAME FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')PARENT2,
		// ISNULL((SELECT P2_PHONE FROM REGISTRATIONS WHERE REGID=SCCA.REGID),'')P2_PHONE
		// FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY SCCA
		// WHERE ISDELETED=0 AND REGID NOT IN (SELECT REGID FROM REGISTRATIONS WHERE ISDELETED=1 AND APPROVED=0) AND
		// SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE WHERE ISDELETED=0 AND PLANID IN ($planid) AND PRODUCTID IN ($productid))
		// ORDER BY label";

		$query = "SELECT REGID AS id, ISNULL(FIRSTNAME+' '+LASTNAME,'') label,FIRSTNAME,LASTNAME,ISNULL(PHONE,'')PHONE,ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1,
		ISNULL(P1_PHONE,'')P1_PHONE,ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,ISNULL(P2_PHONE,'')P2_PHONE
		FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID IN (SELECT REGID FROM REGISTRATION_DETAILS WHERE CANCELLED=0 AND ACTIVATE=1 AND PLANID IN ($planid))
		ORDER BY label";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		$count = unique($query);
		if($count > 0){
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['id'] = (int) $row['id'];
				$row['FINAL_PHONE'] = '';

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$

				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$

				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				if(strlen($row['P2_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE'].', ';
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

				$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
				$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));

				// $row['FINAL_PHONE'] = ($row['PHONE'] && $row['PHONE'] != '') ? $row['PHONE'] : (($row['P1_PHONE'] && $row['P1_PHONE'] != '') ? $row['P1_PHONE'] : (($row['P2_PHONE'] && $row['P2_PHONE'] != '') ? $row['P2_PHONE'] : ''));
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
/*============ Get Student By Plan_Product =============*/ 



function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







