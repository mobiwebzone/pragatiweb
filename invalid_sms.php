<?php
require_once 'code/connection.php';
require __DIR__ . '../Twilio/autoload.php';
use Twilio\Rest\Client;

// $account_sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
// $auth_token = 'ad245122998781f49da657a66141cad6';

$responseBody = file_get_contents('php://input');
$json = $responseBody;
//If you need return data
//echo json_encode($json);
//Save in json file

//#######################################################
//########## PRINT DATA IN TEXT FILE ON SERVER ##########
//#######################################################

// ________STEP 1_________
$fp = fopen('invalid_msg.txt', 'w');
fwrite($fp, $json);
fclose($fp);

// ________STEP 2_________
$msgData = '';
$fpr = fopen('invalid_msg.txt', 'r');
$msgData= fread($fpr,2000);
fclose($fpr);
// $jsonSave = json_decode($msgData);
parse_str($msgData, $get_array);
// print_r($get_array);

$AccountSid = $get_array['AccountSid'];
$Sid = $get_array['Sid'];
$Payload = $get_array['Payload'];
$payload = json_decode($Payload);
$resource_sid = $payload->resource_sid;
// json_decode('{"resource_sid":"SM08389beeb8e9b836b1dc203145307300","service_sid":"SM08389beeb8e9b836b1dc203145307300","error_code":"30003"}')

// $fp = fopen('invalid_SID.txt', 'w');
// fwrite($fp, $resource_sid);
// fclose($fp);




// ________STEP 3_________
// GET INVALID MSG INFO
$sid = 'ACf51c7c1e782e77f2634da47b1d850f70';
$token = 'ad245122998781f49da657a66141cad6';
$twilio = new Client($sid, $token);

$message = $twilio->messages($resource_sid)->fetch();

$AccountSid = isset($message->account_sid) ? $message->account_sid : '';
$ApiVersion = isset($message->api_version) ? $message->api_version : '';
$Body = isset($message->body) ? $message->body : '';                          // SMS
$DateCreated = isset($message->date_created) ? $message->date_created : '';                        
$DateSent = isset($message->date_sent) ? $message->date_sent : '';
$DateUpdated = isset($message->date_updated) ? $message->date_updated : '';
$Direction = isset($message->direction) ? $message->direction : '';          // LIKE : outbound-api
$ErrorCode = isset($message->error_code) ? $message->error_code : '';
$ErrorMessage = isset($message->error_message) ? $message->error_message : '';
$From = isset($message->from) ? $message->from : '';
$MessaginfServiceSid = isset($message->messaging_service_sid) ? $message->messaging_service_sid : '';
$NumMedia = isset($message->num_media) ? $message->num_media : '';
$NumSegments= isset($message->num_segments) ? $message->num_segments : '';
$Price = isset($message->price) ? $message->price : '';
$PriceUnit = isset($message->price_unit) ? $message->price_unit : '';               
$Sid = isset($message->sid) ? $message->sid : '';                             
$Status = isset($message->status) ? $message->status : '';
$To = isset($message->to) ? $message->to : '';
$Uri = isset($message->uri) ? $message->uri : '';

$FINAL_DATA = "account_sid : ".$AccountSid."\n";
$FINAL_DATA .= "api_version : ".$ApiVersion."\n";
$FINAL_DATA .= "body : ".$Body."\n";
$FINAL_DATA .= "date_created : ".$DateCreated."\n";
$FINAL_DATA .= "date_sent : ".$DateSent."\n";
$FINAL_DATA .= "date_updated : ".$DateUpdated."\n";
$FINAL_DATA .= "direction : ".$Direction."\n";
$FINAL_DATA .= "error_code : ".$ErrorCode."\n";
$FINAL_DATA .= "error_message : ".$ErrorMessage."\n";
$FINAL_DATA .= "from : ".$From."\n";
$FINAL_DATA .= "messaging_service_sid : ".$MessaginfServiceSid."\n";
$FINAL_DATA .= "num_media : ".$NumMedia."\n";
$FINAL_DATA .= "num_segments : ".$NumSegments."\n";
$FINAL_DATA .= "price : ".$Price."\n";
$FINAL_DATA .= "price_unit : ".$PriceUnit."\n";
$FINAL_DATA .= "sid : ".$Sid."\n";
$FINAL_DATA .= "status : ".$Status."\n";
$FINAL_DATA .= "to : ".$To."\n";
$FINAL_DATA .= "uri : ".$Uri."\n";

$fp = fopen('fetch_error_sms_details.txt', 'w');
// fwrite($fp, print_r($message,true));
fwrite($fp, $FINAL_DATA);
fclose($fp);                        
//#######################################################
//########## PRINT DATA IN TEXT FILE ON SERVER ##########
//#######################################################




// ============== SAVE ==============
$data = array();



        // $FINAL_DATA = json_encode($get_array);
        $query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] 0,'',0,'','','$To','$Body','',0";
        $stmt=sqlsrv_query($conn, $query);
        
        if($stmt === false)
        {
            // die( print_r( sqlsrv_errors(), true));
            // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
            $data['success'] = false;
            $data['queryFail'][] = $query;
        }
        else
        {
            // GET MSGID
            $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
            $GET_MSGID = (int) $row['MSGID'];
            
            // UPDATE DATA
            $update = "UPDATE TEXT_MESSAGES SET MSGTYPE='INCOMING',[STATUS]='UNDELIVERED',INCO_MESSAGE_SID='$Sid' WHERE MSGID=$GET_MSGID";
            sqlsrv_query($conn, $update);
            
            // INSERT DETAILS
            $query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
            VALUES($GET_MSGID,'$To','UNDELIVERED')";
            sqlsrv_query($conn, $query2);
            // $query="INSERT INTO TEXT_MESSAGES(MSGDATE,MSGTYPE,[STATUS],REGID,MOBILENO,TEXTMESSAGE,INCO_MESSAGE_SID,INSERTID)
            // VALUES(GETDATE(),'INCOMING','UNDELIVERED',0,'$To','$Body','$Sid',0)";
            // $stmt=sqlsrv_query($conn, $query);
            
        }

        


// ============== SAVE ==============
?>