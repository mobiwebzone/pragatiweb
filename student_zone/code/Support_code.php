<?php
session_start();
require_once '../code/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if(!empty($_SESSION['STUDENTID']))
{$userid=$_SESSION['STUDENTID'];}
else
{$userid=0;}
if(!empty($_SESSION['USER_LOCID']))
{$locid=$_SESSION['USER_LOCID'];}
else
{$locid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getAllTicket":getAllTicket($conn);break;
        case "OpenTicket":OpenTicket($conn);break;
        case "getSupportTicket":getSupportTicket($conn);break;
        case "saveComment":saveComment($conn);break;
        case "SaveReply":SaveReply($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */


 function getAllTicket($mysqli){
     try
     {
		$data = array();
        global $userid;
		
			$FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
			$txtFromDt = $_POST['txtFromDt'] == 'undefined' ? '' : $_POST['txtFromDt'];
			$txtToDt = $_POST['txtToDt'] == 'undefined' ? '' : $_POST['txtToDt'];

			// CHECK ADMIN REPLY DAYS & UPDATE STATUS START
			$ChkReplyday = "SELECT TICKETID,CLOSEDBY,
			DATEDIFF(DAY, CONVERT(DATE,(SELECT MAX(COMMENTDATE) FROM SUPPORT_TICKETS_DETAILS WHERE COMMENTSBY='ADMIN' AND CANCELLED=0),105), CONVERT(DATE,GETDATE(),105)) TOTAL_DAYS
			FROM SUPPORT_TICKETS ST WHERE CANCELLED=0";

			$stmtReplyday=sqlsrv_query($mysqli, $ChkReplyday);
			$Count_RD = unique($ChkReplyday);
			if($Count_RD > 0){
				while($rowRD = sqlsrv_fetch_array($stmtReplyday)){
					$DAY = $rowRD['TOTAL_DAYS'];
					$CLOSEDBY = (int)$rowRD['CLOSEDBY'];
					$TICKETID = (int) $rowRD['TICKETID'];

					if($DAY > 2 && $CLOSEDBY == 0){
						$Upd_Status="UPDATE SUPPORT_TICKETS SET [STATUS]='HOLD' WHERE TICKETID=$TICKETID";
						$stmtUpd_Status=sqlsrv_query($mysqli, $Upd_Status);
					}
				}
			}
			// CHECK ADMIN REPLY DAYS & UPDATE STATUS END

			if($FOR == 'STUDENT'){
				$query="SELECT TICKETID,CONVERT(VARCHAR,TICKETDATE,106)TICKETDATE,
				[SUBJECT],[PRIORITY],[STATUS],CLOSEDBY CLOSEDBYID,
				ISNULL((SELECT FIRSTNAME FROM USERS WHERE UID=ST.CLOSEDBY),'')CLOSEDBY,
				TICKET_BY,TICKET_BYID,
				ISNULL((SELECT FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' FROM USERS WHERE [UID]=ST.TICKET_BYID),'')TICKET_BY_NAME
				FROM SUPPORT_TICKETS ST WHERE REGID=$userid AND CANCELLED=0";
				if($txtFromDt !='' && $txtToDt !='') $query.=" AND CONVERT(DATE,TICKETDATE,105) BETWEEN '$txtFromDt' AND '$txtToDt'";
				// if($_SESSION['USER_LOCID'] != '1'){
				// 	$query .=" AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=".$_SESSION['USER_LOCID'].")";
				// }
				$query.=" ORDER BY TICKETID DESC";
			}
			else {
				$query="SELECT TICKETID,CONVERT(VARCHAR,TICKETDATE,105)TICKETDATE,REGID,
				ISNULL((SELECT FIRSTNAME+' '+LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID),'')STUDENT_NAME,
				[SUBJECT],[PRIORITY],[STATUS],TICKET_BY,TICKET_BYID,
				CLOSEDBY CLOSEDBYID,
				ISNULL((SELECT FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' FROM USERS WHERE UID=ST.CLOSEDBY),'')CLOSEDBY,
				ISNULL((SELECT FIRSTNAME+' '+LASTNAME+' ('+USERROLE+')' FROM USERS WHERE [UID]=ST.TICKET_BYID),'')TICKET_BY_NAME
				FROM SUPPORT_TICKETS ST WHERE CANCELLED=0";
				if($txtFromDt !='' && $txtToDt !='') $query.=" WHERE CONVERT(DATE,TICKETDATE,105) BETWEEN '$txtFromDt' AND '$txtToDt'";
				if($_SESSION['USER_LOCID'] != '1'){
					$query .=" AND REGID IN (SELECT REGID FROM REGISTRATIONS WHERE LOCATIONID=".$_SESSION['USER_LOCID'].")";
				}
				$query.=" ORDER BY TICKETID DESC";
			}



			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				while($row = sqlsrv_fetch_array($stmt)){
					$data['TICKETID'] = (int) $row['TICKETID'];
					$data['data'][] = $row;
				}

				$data['query'] = $query;
				$data['success'] = true;
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


 function OpenTicket($mysqli){
     try
     {
		$data = array();
        global $userid;
		
		$textSubject=$_POST['textSubject'] == 'undefined' ? '' : $_POST['textSubject'];
		$ddlPriority=$_POST['ddlPriority'] == 'undefined' ? '' : $_POST['ddlPriority'];
		$txtComment=$_POST['txtComment'] == 'undefined' ? '' : $_POST['txtComment'];

		if($textSubject == '')
		{throw new Exception("Please Enter Subject.");}
		if($ddlPriority == '')
		{throw new Exception("Please Select Priority.");}
		if($txtComment == '')
		{throw new Exception("Please Enter Comment.");}
	
			$query="EXEC [SUPPORT_TICKETS_SP] 0,$userid,'$textSubject','$ddlPriority','OPEN','STUDENT',0";
			$stmt=sqlsrv_query($mysqli, $query);
			
			if($stmt === false)
			{
				// die( print_r( sqlsrv_errors(), true));
				// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
				$data['success'] = false;
				$data['query'] = $query;
				echo json_encode($data);exit;
			}
			else
			{
				$row = sqlsrv_fetch_array($stmt);
				$data['TICKETID'] = (int) $row['TICKETID'];
				$TICKETID = (int) $row['TICKETID'];


				//Save Comment
				$queryComment="EXEC [SUPPORT_TICKETS_DETAILS_SP] 0,$TICKETID,1,'$txtComment','STUDENT',$userid,'COMMENT'";
				$resultComment=sqlsrv_query($mysqli, $queryComment);

				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryComment'] = $queryComment;
					echo json_encode($data);exit;
				}
				else
				{
					$data['query'] = $query;
					$data['success'] = true;
					$data['message'] = 'Record successfully inserted.';
				}



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



/*============ Get Support Ticket =============*/ 
 function getSupportTicket($mysqli){
	try
	{
		global $userid;
		$ST_HTML = '';
		$STATUS_BG='';
		$TICKET_TIMING='';
		$data=array();

		$TICKETID = ($_POST['TICKETID'] == 'undefined' || $_POST['TICKETID'] == '') ? 0 : $_POST['TICKETID'];
		$FOR = $_POST['FOR'] == 'undefined' ? '' : $_POST['FOR'];
		// ====================================== MAIN ================================================
		$query = "SELECT TICKETID,(SELECT DATEDIFF(hh, TICKETDATE, GETDATE()) FROM SUPPORT_TICKETS WHERE TICKETID=ST.TICKETID)TOTAL_HOURS ,
		(SELECT DATEDIFF(MINUTE, TICKETDATE, GETDATE()) FROM SUPPORT_TICKETS WHERE TICKETID=ST.TICKETID)TOTAL_MINUTES,
		(SELECT DATEDIFF(DAY, TICKETDATE, GETDATE()) FROM SUPPORT_TICKETS WHERE TICKETID=ST.TICKETID)TOTAL_DAY,
		(SELECT DATEDIFF(MONTH, TICKETDATE, GETDATE()) FROM SUPPORT_TICKETS WHERE TICKETID=ST.TICKETID)TOTAL_MONTH,
		(SELECT DATEDIFF(YEAR, TICKETDATE, GETDATE()) FROM SUPPORT_TICKETS WHERE TICKETID=ST.TICKETID)TOTAL_YEAR,
		CONVERT(VARCHAR,TICKETDATE,106)TICKETDATE,
		(SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=ST.REGID)STUDENT_NAME,
		[SUBJECT],[PRIORITY],[STATUS],
		(SELECT COUNT(COMMENTS) FROM SUPPORT_TICKETS_DETAILS WHERE TICKETID=ST.TICKETID)TOTAL_COMMENTS
		FROM SUPPORT_TICKETS ST WHERE CANCELLED=0";

		if($TICKETID > 0){
			$query .=" AND TICKETID=$TICKETID";
		}
		 
		$RCOUNT = unique($query);
		$data['RCOUNT'] = $RCOUNT;
		$result = sqlsrv_query($mysqli, $query);
		
		if($RCOUNT > 0){
			$M_INDEX = 0;
			while ($row = sqlsrv_fetch_array($result)) {
				$data['TOTAL_HOURS'][] = $row['TOTAL_HOURS'];
				$TICKETID = (int) $row['TICKETID'];
				
				// MAKE STATUS COLOR 
				if($row['STATUS'] == 'OPEN'){$STATUS_BG='bg-success';}
				else if($row['STATUS'] == 'WIP'){$STATUS_BG='bg-primary';}
				else if($row['STATUS'] == 'HOLD'){$STATUS_BG='bg-danger';}
				else if($row['STATUS'] == 'CLOSED'){$STATUS_BG='bg-secondary';}
				else{$STATUS_BG='bg-dark';}

				// MAKE TICKET TIMING
				if($row['TOTAL_MINUTES'] >= 10){$TICKET_TIMING = $row['TOTAL_MINUTES'] .' '. 'minutes ago';}
				else{$TICKET_TIMING = 'few minutes ago';}
				if($row['TOTAL_MINUTES'] >= 60){$TICKET_TIMING = $row['TOTAL_HOURS'] .' '. 'hours ago';}
				if($row['TOTAL_HOURS'] >= 24){$TICKET_TIMING = $row['TOTAL_DAY'] .' '. 'days ago';}
				if($row['TOTAL_DAY'] >= 31){$TICKET_TIMING = $row['TOTAL_MONTH'] .' '. 'months ago';}
				if($row['TOTAL_MONTH'] > 12){$TICKET_TIMING = $row['TOTAL_YEAR'] .' '. 'years ago';}


				$ST_HTML .='<div class="row d-flex justify-content-center flex-column align-items-center px-3">

								<div class="col-sm-12 col-md-12 col-lg-12 mb-2 bg-white card card-body rounded-my">
								<div class="col-12 text-center">
									<i class="fa fa-times text-light pointer p-0 p-2 rounded-circle m-0 cancel" title="close" ng-click="closeTicket()"></i>
								</div>
								<div class="container my-2">
									<div class="d-flex justify-content-center row">
                    					<div class="d-flex flex-column col-md-12 px-0 px-lg-2">

										<div class="d-flex flex-row justify-content-start align-items-center text-left comment-top p-2 bg-white border-bottom px-0 px-lg-4">
											<div class="d-flex">
												<div class="profile-image">
													<img class="rounded-circle" src="../images/USER.png" width="70">
												</div>
												<div class="d-flex flex-column-reverse flex-grow-0 align-items-center votings ml-2">
													<i class="fa fa-sort-up fa-2x hit-voting"></i>
													<span title="Ticket id" ng-bind="'.$row['TICKETID'].'"></span>
													<i class="fa fa-sort-down fa-2x hit-voting"></i>
												</div>
											</div>

											<div class="d-flex flex-column ml-3">
												<div class="d-flex flex-row post-title">
													<h3 class="font-weight-bold">'.$row['SUBJECT'].'</h3>
												</div>
												<div class="post-title">
													<div class="row d-flex justify-content-between">
														<div class="col-sm-12 col-md-6 d-flex justify-content-start pr-md-0 pr-3">
															<span class="bdge mr-4 STATUS_BADGE pointer '.$STATUS_BG.'"  data-toggle="modal" data-target="#Status">'.$row['STATUS'].'</span>
															<span class="text-nowrap">
																<span class="mr-2 dot"></span>
																<span ng-bind="'.$row['TOTAL_COMMENTS'].'"></span> comments&nbsp;
															</span>
														</div>
														
														<div class="col-sm-12 col-md-6 d-flex justify-content-start pl-md-0 pl-3 mt-md-0 mt-2">
															<span class="text-nowrap">
																<span class="mr-2 dot"></span>
																<span class="mr-2">'.$TICKET_TIMING.'</span>
															</span>
															<span class="text-nowrap">
																<span class="mr-2 dot"></span>
																<span>'.$row['TICKETDATE'].'</span>
															</span>
														</div>
													</div>

												</div>
											</div>
										</div>';
							
							$ST_HTML .='<div class="coment-bottom bg-white p-2 px-0 px-lg-4">
											<div class="d-flex add-comment-section mt-4 mb-4 flex-column flex-lg-row">
												<input type="text" class="form-control mr-3 mb-0" id="txtNewComment" data-ng-model="temp.txtNewComment'.$M_INDEX.'" placeholder="{{'.$FOR.' == \'STUDENT\' ? \'Add comment...\' : \'Add reply...\'}}">
												<button class="btn btn-secondary btn-save-comment py-1 mt-2 mt-lg-0 text-nowrap" ng-disabled="!temp.txtNewComment'.$M_INDEX.'" data-ng-click="saveComment(temp.txtNewComment'.$M_INDEX.','.$TICKETID.')" type="button">
													<i class="fa fa-reply"></i> <span ng-bind="'.$FOR.' == \'STUDENT\' ? \'COMMENT\' : \'REPLY\'"></span>
												</button>
												</div>
											<div class="text-right add-comment-section">
												<span ng-click="getSupportTicket('.$TICKETID.')" title="Refresh" class="fa fa-refresh p-2 rounded-circle bg-warning pointer refresh-comment"></span>
											</div>';


											// GET COMMENTS START
											$COMMENT_TIMING = '';

											$queryComment = "SELECT TID,PARENTTID,COMMENTS,COMMENTSBY,
											CASE WHEN COMMENTSBY='STUDENT' 
												THEN (SELECT FIRSTNAME +' '+ LASTNAME FROM REGISTRATIONS WHERE REGID=STD.BYID)
												ELSE (SELECT FIRSTNAME +' '+ LASTNAME FROM USERS WHERE UID=STD.BYID)
											END AS COMMENTBY_NAME,
											CASE WHEN COMMENTSBY='ADMIN' 
												THEN (SELECT USERROLE FROM USERS WHERE [UID]=STD.BYID)
												ELSE ''
											END AS ADMIN_USERROLE,
											(SELECT DATEDIFF(hh, COMMENTDATE, GETDATE()) FROM SUPPORT_TICKETS_DETAILS WHERE TID=STD.TID)TOTAL_HOURS,
											(SELECT DATEDIFF(MINUTE, COMMENTDATE, GETDATE()) FROM SUPPORT_TICKETS_DETAILS WHERE TID=STD.TID)TOTAL_MINUTES,
											(SELECT DATEDIFF(DAY, COMMENTDATE, GETDATE()) FROM SUPPORT_TICKETS_DETAILS WHERE TID=STD.TID)TOTAL_DAY,
											(SELECT DATEDIFF(MONTH, COMMENTDATE, GETDATE()) FROM SUPPORT_TICKETS_DETAILS WHERE TID=STD.TID)TOTAL_MONTH,
											(SELECT DATEDIFF(YEAR, COMMENTDATE, GETDATE()) FROM SUPPORT_TICKETS_DETAILS WHERE TID=STD.TID)TOTAL_YEAR,
											CONVERT(VARCHAR,COMMENTDATE,106)COMMENTDATE,COMMENT_REPLY
											FROM SUPPORT_TICKETS_DETAILS STD WHERE CANCELLED=0 AND TICKETID=$TICKETID ORDER BY TID DESC";
									
											$ComCOUNT = unique($queryComment);
											$resultComment = sqlsrv_query($mysqli, $queryComment);
											
											if($ComCOUNT > 0){
												$C_INDEX=0;
												while ($rowComment = sqlsrv_fetch_array($resultComment)) {
													// $data['data'][] = $row;
													$TID=$rowComment['TID'];
													$PARENTTID=$rowComment['PARENTTID'];
													$COMMENTSBY=$rowComment['COMMENTSBY'];
													$COMMENTS=$rowComment['COMMENTS'];
													$ADMIN_USERROLE=$rowComment['ADMIN_USERROLE'];
													$COMMENTBY_NAME=strlen($ADMIN_USERROLE)>0 ? $rowComment['COMMENTBY_NAME'].' <small class="text-secondary"><b>('.$ADMIN_USERROLE.')</b></small>':$rowComment['COMMENTBY_NAME'];
													// MAKE COMMENT TIMING
													if($rowComment['TOTAL_MINUTES'] >= 1){$COMMENT_TIMING = $rowComment['TOTAL_MINUTES'] .' '. 'minutes ago';}
													else{$COMMENT_TIMING = 'few seconds ago';}
													if($rowComment['TOTAL_MINUTES'] >= 60){$COMMENT_TIMING = $rowComment['TOTAL_HOURS'] .' '. 'hours ago';}
													if($rowComment['TOTAL_HOURS'] >= 24){$COMMENT_TIMING = $rowComment['TOTAL_DAY'] .' '. 'days ago';}
													if($rowComment['TOTAL_DAY'] >= 31){$COMMENT_TIMING = $rowComment['TOTAL_MONTH'] .' '. 'months ago';}
													if($rowComment['TOTAL_MONTH'] > 12){$COMMENT_TIMING = $rowComment['TOTAL_YEAR'] .' '. 'years ago';}
													
													$ST_HTML .='<div class="commented-section mt-3 border border-light shadow-sm bg-white">
																	<div class="d-flex flex-row align-items-center commented-user p-2" ng-class="\''.$COMMENTSBY.'\' == \'STUDENT\' ? \'student-head\' : \'admin-head\'">
																		<h4 class="font-weight-bold mb-0 mr-4" ng-class="\''.$COMMENTSBY.'\' == \'STUDENT\' ? \'text-primary\' : \'text-success\'">'.$COMMENTBY_NAME.'</h4>
																		<span class="dot mx-2"></span>
																		<span class="">'.$COMMENT_TIMING.'</span>
																		<span class="dot mx-2"></span>
																		<span class="">'.$rowComment['COMMENTDATE'].'</span>
																	</div>
																	<div class="comment-text-sm">
																		<p class="mb-0 text-dark px-2 viewcomment'.$M_INDEX.$C_INDEX.'">'.$COMMENTS.'</p>
																	</div>';


																				

														// REPLY BOX INSIDE
														$ST_HTML .='<div>
																		<div class="d-flex flex-row justify-content-end align-items-center voting-icons mb-2">
																			<button class="mr-2 py-1 btn-secondary-my btn px-2 rounded-0 font-10 btn-REP'.$M_INDEX.$C_INDEX.'" ng-click="openReplyBox('.$M_INDEX.','.$C_INDEX.',\'I\','.$TID.')">Reply</button>';
															if($FOR == 'ADMIN' && $COMMENTSBY == 'ADMIN'){
																$ST_HTML .='<button class="mr-2 py-1 btn-warning btn px-2 rounded-0 font-10 btn-UPD'.$M_INDEX.$C_INDEX.'" ng-click="openReplyBox('.$M_INDEX.','.$C_INDEX.',\'U\','.$TID.')">Update</button>';
															}
																			
															$ST_HTML .='</div>';

															$ST_HTML .='<div class="replyBox'.$M_INDEX.$C_INDEX.' text-right mt-2 px-2" style="display:none;">
																			<form>
																				<div class="row">
																					<div class="col-sm-12">
																						<div class="form-inline d-flex justify-content-end">
																							<input type="text" class="form-control form-control-sm my-1 mr-sm-2 w-75" placeholder="Add reply" ng-model="temp.txtReply'.$M_INDEX.$C_INDEX.'" id="'.$M_INDEX.$C_INDEX.'">
																							<button type="submit" ng-disabled="!temp.txtReply'.$M_INDEX.$C_INDEX.'" ng-click="SaveReply(temp.txtReply'.$M_INDEX.$C_INDEX.','.$M_INDEX.','.$C_INDEX.','.$PARENTTID.','.$TICKETID.')" class="btn btn-dark btn-sm py-1 my-1 btn-save-reply'.$C_INDEX.'">
																								<i class="fa fa-reply"></i>
																							</button>
																						</div>
																					</div>
																				</div>
																			</form>			
																		</div>
																	</div>
																</div>';
														// REPLY BOX INSIDE
													$C_INDEX++;
												}
											}else{
												
											}

											// GET COMMENTS END


							
				$ST_HTML .='				</div>
										</div>
									</div>
								</div>';
				$ST_HTML .='<div class="row">
								<div class="col-12 text-center">
									<i class="fa fa-times text-light pointer p-0 p-2 rounded-circle m-0 cancel" title="close" ng-click="closeTicket()"></i>
								</div>
							</div>
							</div>
							</div>';
				$M_INDEX++;
			}
			$data['success'] = true;
			$data['ST_HTML'] = $ST_HTML;
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


// ========== SAVE COMMENTS ===========
function saveComment($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   $LPID = 1;
	   
	   
	   $userid=($_POST['userid'] == 'undefined' || $_POST['userid'] == '') ? 0 : $_POST['userid'];
	   $TICKETID=($_POST['TICKETID'] == 'undefined' || $_POST['TICKETID'] == '') ? 0 : $_POST['TICKETID'];
	   $txtNewComment=$_POST['txtNewComment'] == 'undefined' ? '' : $_POST['txtNewComment'];
	   $COMMENTBY=$_POST['COMMENTBY'] == 'undefined' ? '' : $_POST['COMMENTBY'];

	   if($TICKETID == 0)throw new Exception("TICKETID Error.");
	   if($userid==0)throw new Exception("USERID Invalid.");
	
		
	//    GET PARANTTID 
		$GET_LAST_PARANTTID = "SELECT MAX(PARENTTID) PARENTTID FROM SUPPORT_TICKETS_DETAILS WHERE TICKETID=$TICKETID";
		$stmtLP=sqlsrv_query($mysqli, $GET_LAST_PARANTTID);
		$LP_COUNT = unique($GET_LAST_PARANTTID);
		if($LP_COUNT > 0){
			$rowLP = sqlsrv_fetch_array($stmtLP);
			$LPID = $rowLP['PARENTTID'];
			$LPID = $LPID+1;
		}else{
			$LPID = 1;
		}
	

		if($COMMENTBY == 'ADMIN'){
			// Check Admin 1st reply  
			$ADMIN_FIRST_REPLY = "SELECT * FROM SUPPORT_TICKETS_DETAILS WHERE COMMENTSBY='ADMIN' AND TICKETID=$TICKETID";
			$stmtAFR=sqlsrv_query($mysqli, $ADMIN_FIRST_REPLY);
			$AFR_COUNT = unique($ADMIN_FIRST_REPLY);
			if($AFR_COUNT <= 0){
				$UPDSTATUS = "UPDATE SUPPORT_TICKETS SET [STATUS]='WIP' WHERE TICKETID=$TICKETID";
				$stmtUST=sqlsrv_query($mysqli, $UPDSTATUS);
				if($stmtUST === false)
				{
					$data['success'] = false;
					$data['UPDSTATUS'] = $UPDSTATUS;
					echo json_encode($data);exit;
				}else{
					$data['UPDSTATUS'] = $UPDSTATUS;
					$data['success'] = true;
				}
			}
		}

   
		   $query="EXEC [SUPPORT_TICKETS_DETAILS_SP] 0,$TICKETID,$LPID,'$txtNewComment','$COMMENTBY',$userid,'COMMENT'";
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {


				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'Record successfully inserted.';
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

// ========= SAVE REPLY ==========
function SaveReply($mysqli){
	try
	{
	   $data = array();
	   global $userid;
	   
	   $TICKETID=($_POST['TICKETID'] == 'undefined' || $_POST['TICKETID'] == '') ? 0 : $_POST['TICKETID'];
	   $PARENTTID=($_POST['PARENTTID'] == 'undefined' || $_POST['PARENTTID'] == '') ? 0 : $_POST['PARENTTID'];
	   $txtReply=$_POST['txtReply'] == 'undefined' ? '' : $_POST['txtReply'];
	   $REPLYBYID=($_POST['REPLYBYID'] == 'undefined' || $_POST['REPLYBYID'] == '') ? 0 : $_POST['REPLYBYID'];
	   $REPLYBY=$_POST['REPLYBY'] == 'undefined' ? '' : $_POST['REPLYBY'];
	   $INSERT=$_POST['FOR_INSERT'] == 'false' ? false :true;
	   $TID=($_POST['TID'] == 'undefined' || $_POST['TID'] == '') ? 0 : $_POST['TID'];

		//    $data['$INSERT']=$INSERT;
		//    echo json_encode($data);exit;
		if(!$INSERT && $TID==0)throw new Exception('TID Missing');

		if($TICKETID == 0)
		{throw new Exception("TICKETID Error.");}
		
		if($PARENTTID == 0)
		{throw new Exception("PARENTTID Error.");}

		//    GET PARANTTID 
		// $GETPARANTTID = "SELECT "

		if($INSERT){
			$query="EXEC [SUPPORT_TICKETS_DETAILS_SP] 0,$TICKETID,$PARENTTID,'$txtReply','$REPLYBY',$REPLYBYID,'REPLY'";
		}else{
			$query="UPDATE SUPPORT_TICKETS_DETAILS SET COMMENTS='$txtReply',UPDATEID=$userid,UPDATEDATE=GETDATE() WHERE TID=$TID";
		}
		   $stmt=sqlsrv_query($mysqli, $query);
		   
		   if($stmt === false)
		   {
			   // die( print_r( sqlsrv_errors(), true));
			   // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
			   $data['success'] = false;
			   $data['query'] = $query;
			   echo json_encode($data);exit;
		   }
		   else
		   {
				// SEND MAIL BY ADMIN
				if($REPLYBY == 'ADMIN'){
					// GET REGID
					$queryRegid = "SELECT TOP 1 REGID FROM SUPPORT_TICKETS WHERE TICKETID=$TICKETID AND CANCELLED=0";
					$resultRegid = sqlsrv_query($mysqli,$queryRegid);
					$rowRegid = sqlsrv_fetch_array($resultRegid,SQLSRV_FETCH_ASSOC);
					$REGID = $rowRegid['REGID'];

					if($REGID>0){
						// SEND MAIL
						$sendSMS = sendText_Email($mysqli,$REGID,'updated');
						$data['sendSMS']=$sendSMS;
					}
				}

				$data['query'] = $query;
				$data['success'] = true;
				$data['message'] = 'Record successfully inserted.';
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




/*============ SEND EMAIL =============*/ 
function sendText_Email($mysqli,$regid,$msgFor){
	global $userid;
	$data = array();

	$msg = "MyExamPrep's Support ticket has been ".$msgFor.". <br/><br/>
						
				Thanks <br/>
				MyExamsPrep";
	$msgdb = str_replace("'","''",$msg);

	$query = "SELECT REGID, ISNULL(FIRSTNAME+' '+LASTNAME,'') FULLNAME,FIRSTNAME,LASTNAME,LOCATIONID,
		ISNULL(CASE WHEN PHONE='' OR PHONE='null' OR PHONE LIKE'%TBD%' OR PHONE IS NULL THEN '' ELSE PHONE END,'') PHONE,
		ISNULL(CASE WHEN EMAIL='' OR EMAIL='null' OR EMAIL LIKE'%TBD%' OR EMAIL IS NULL THEN '' ELSE EMAIL END,'') EMAIL,
		ISNULL(P1_FIRSTNAME+' '+P1_LASTNAME,'') PARENT1, 
		ISNULL(CASE WHEN P1_PHONE='' OR P1_PHONE='null' OR P1_PHONE LIKE'%TBD%' OR P1_PHONE IS NULL THEN '' ELSE P1_PHONE END,'') P1_PHONE,
		ISNULL(CASE WHEN P1_EMAIL='' OR P1_EMAIL='null' OR P1_EMAIL LIKE'%TBD%' OR P1_EMAIL IS NULL THEN '' ELSE P1_EMAIL END,'') P1_EMAIL,
		ISNULL(P2_FIRSTNAME+' '+P2_LASTNAME,'') PARENT2,
		ISNULL(CASE WHEN P2_PHONE='' OR P2_PHONE='null' OR P2_PHONE LIKE'%TBD%' OR P2_PHONE IS NULL THEN '' ELSE P2_PHONE END,'') P2_PHONE,
		ISNULL(CASE WHEN P2_EMAIL='' OR P2_EMAIL='null' OR P2_EMAIL LIKE'%TBD%' OR P2_EMAIL IS NULL THEN '' ELSE P2_EMAIL END,'') P2_EMAIL
		FROM REGISTRATIONS WHERE ISDELETED=0 AND APPROVED=1 AND REGID=$regid ORDER BY FULLNAME";

		$data['$query']=$query;

		$result = sqlsrv_query($mysqli, $query);
		$count = unique($query);
		$data['COUNT'] = $count;
		if($count > 0){
			$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
				$row['REGID'] = (int) $row['REGID'];
				$row['FINAL_PHONE'] = '';
				$row['FINAL_EMAIL'] = '';

				

				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				$row['PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['PHONE']);
				$row['PHONE'] = preg_match('/^[0-9]+$/', $row['PHONE']) ? $row['PHONE'] : '';
				$row['PHONE'] = is_numeric($row['PHONE']) ? $row['PHONE'] : '';
				if(strlen($row['PHONE']) > 0) $row['FINAL_PHONE'] .= $row['PHONE'].', ';

				if (filter_var($row['EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['EMAIL']) > 0 && $row['EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ STUDENT $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				$row['P1_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P1_PHONE']);
				$row['P1_PHONE'] = preg_match('/^[0-9]+$/', $row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				$row['P1_PHONE'] = is_numeric($row['P1_PHONE']) ? $row['P1_PHONE'] : '';
				if(strlen($row['P1_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P1_PHONE'].', ';

				if (filter_var($row['P1_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P1_EMAIL']) > 0 && $row['P1_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P1_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P1 $$$$$$$$$$$$$
				
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$
				$row['P2_PHONE'] = preg_replace('~[\\\\/:*?"<>|()-/\s+/]~', '', $row['P2_PHONE']);
				$row['P2_PHONE'] = preg_match('/^[0-9]+$/', $row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				$row['P2_PHONE'] = is_numeric($row['P2_PHONE']) ? $row['P2_PHONE'] : '';
				if(strlen($row['P2_PHONE']) > 0) $row['FINAL_PHONE'] .= $row['P2_PHONE'].', ';

				if (filter_var($row['P2_EMAIL'], FILTER_VALIDATE_EMAIL)) {
					if(strlen($row['P2_EMAIL']) > 0 && $row['P2_EMAIL']!='NaN') $row['FINAL_EMAIL'] .= $row['P2_EMAIL'].', ';
				}
				// $$$$$$$$$$$$$ P2 $$$$$$$$$$$$$

				$row['FINAL_PHONE'] = rtrim($row['FINAL_PHONE'],', ');
				$row['FINAL_PHONE'] = implode(", ",array_unique(explode(", ",$row['FINAL_PHONE'])));

				$row['FINAL_EMAIL'] = rtrim($row['FINAL_EMAIL'],', ');
				$row['FINAL_EMAIL'] = implode(", ",array_unique(explode(", ",$row['FINAL_EMAIL'])));

				// $row['FINAL_PHONE'] = ($row['PHONE'] && $row['PHONE'] != '') ? $row['PHONE'] : (($row['P1_PHONE'] && $row['P1_PHONE'] != '') ? $row['P1_PHONE'] : (($row['P2_PHONE'] && $row['P2_PHONE'] != '') ? $row['P2_PHONE'] : ''));
				$data['data_STUDENT'][] = $row;

				$REGID = $row['REGID'];
				$LOCATIONID = $row['LOCATIONID'];
				$FIRSTNAME = $row['FIRSTNAME'];
				$LASTNAME = $row['LASTNAME'];
				$FINAL_EMAIL = $row['FINAL_EMAIL'];


				// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& SEND EMAIL DATA &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				$query="EXEC [TEXT_EMAIL_SEND_AND_SAVE] $LOCATIONID,'Registered',$regid,'$FIRSTNAME','$LASTNAME','$FINAL_EMAIL','$msgdb','TICKET_EMAIL',$userid";
				$stmt=sqlsrv_query($mysqli, $query);
				// $stmt=true;
				
				if($stmt === false)
				{
					// die( print_r( sqlsrv_errors(), true));
					// 		throw new Exception( $mysqli->sqlstate.' - '. $query );
					$data['success'] = false;
					$data['queryFail'][] = $query;
				}
				else
				{
					$row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					$GET_EMID = (int)$row['EMID'];
					

					$data['$FINAL_EMAIL'][] = $FINAL_EMAIL;
					$MAIL = explode(", ",$FINAL_EMAIL);
					
					// echo json_encode($data);exit;
					
					$STmails = array();
					foreach($MAIL as $value){
						// EMAIL
						$STmails = array_push_assoc($STmails, $value, $FIRSTNAME);
						$data['mail'][] = $value;
					}
					$STmails = array_push_assoc($STmails, 'info@myexamsprep.com', 'HQ');
					$data['$STmails'][] = $STmails;



					foreach($STmails as $email => $name){
						// MAIL
						$mail = new PHPMailer;
						$mail->isSMTP(); 
						$mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
						$mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
						$mail->Port = 587; // TLS only 587
						$mail->SMTPSecure = 'tls'; // ssl is depracated
						$mail->SMTPAuth = true;
						$mail->Username = "no.reply.myexamsprep@gmail.com";
						$mail->Password = "xagdmidhhtzijcgt";
						$mail->setFrom("no.reply.myexamsprep@gmail.com", "MyExamsPrep");
						// $mail->addAddress("shubham07v@gmail.com", "Shubham");
						$mail->addAddress($email, $name);
						$mail->Subject = 'myexamsprep:Alert';
						$mail->msgHTML($msg); 
						//$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
						$mail->AltBody = 'HTML messaging not supported';
						// if($txtAttachment && $txtAttachment!='')$mail->addAttachment('../mail_attachment_images/'.$txtAttachment); //Attach an image file
						// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file


						//USE AddCC When use foreach loop
						// foreach($STmails as $email => $name){
						// 	$mail->AddCC($email, $name); 
						// }

						if(!$mail->send()){
							// INSERT DETAILS
							$error_msg=$mail->ErrorInfo;
							$error_msg = str_replace("'","''",$error_msg);
							$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
							VALUES($GET_EMID,'$email','ERROR','$error_msg')";
							sqlsrv_query($mysqli, $query2);
							$data['query2'][] = $query2;
							// echo "Mailer Error: " . $mail->ErrorInfo;
							$data['Mail_ST'][] = $mail->ErrorInfo;
							$data['sss'] = $mail;
							$data['success'] = false;
							$data['message'] = 'Sms Send Failed.';
						}
						else{
							// INSERT DETAILS
							$query2="INSERT INTO TEXT_EMAIL_DETAILS(EMID,EMAIL,EMAIL_STATUS,REMARK)
							VALUES($GET_EMID,'$email','SUCCESS','')";
							sqlsrv_query($mysqli, $query2);
							$data['query2'] = $query2;
							// echo "Message sent!";
							$data['sss'] = $mail;
							$data['Mail_ST'] = "Message sent!";
							$data['success'] = true;
							$data['message'] = 'Sms Send successfully.';
						}

					}

					$data['querySuccess'][] = $query;
					$data['email_message'] = 'Email Send Successfully.';
	
				}
				
			}
			else{
				$data['email_message'] = 'Student Not Found For Email.';
			}
			return $data;
}
/*============ SEND EMAIL =============*/ 
function array_push_assoc($array, $key, $value){
	$array[$key] = $value;
	return $array;
}


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







