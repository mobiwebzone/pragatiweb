<?php
require_once 'code/connection.php';
// require __DIR__ . '/Twilio/autoload.php';
// use Twilio\Rest\Client;

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
$fp = fopen('incoming_msg.txt', 'w');
fwrite($fp, $json);
fclose($fp);
//#######################################################
//########## PRINT DATA IN TEXT FILE ON SERVER ##########
//#######################################################




// ============== SAVE ==============
$data = array();
        $msgData = '';
        $fpr = fopen('incoming_msg.txt', 'r');
        $msgData= fread($fpr,2000);
        fclose($fpr);
        // $jsonSave = json_decode($msgData);
        parse_str($msgData, $get_array);

        // $FINAL_DATA = json_encode($get_array);
      
            $AccountSid = $get_array['AccountSid'];
            $ApiVersion = $get_array['ApiVersion'];
            $Body = $get_array['Body'];                         //Main Msg
            $From = $get_array['From'];                         //Number
            $FromCity = $get_array['FromCity'];
            $FromCountry = $get_array['FromCountry'];
            $FromState = $get_array['FromState'];
            $FromZip = $get_array['FromZip'];
            $MessageSid = $get_array['MessageSid'];
            $NumMedia = $get_array['NumMedia'];
            $NumSegments = $get_array['NumSegments'];
            $ReferralNumMedia = $get_array['ReferralNumMedia'];
            $SmsMessageSid= $get_array['SmsMessageSid'];
            $SmsSid = $get_array['SmsSid'];
            $SmsStatus = $get_array['SmsStatus'];               //LIKE:revceived
            $To = $get_array['To'];                             //Twilio Number
            $ToCity = $get_array['ToCity'];
            $ToCountry = $get_array['ToCountry'];
            $ToState = $get_array['ToState'];
            $ToZip = $get_array['ToZip'];
    

            $query="EXEC [TEXT_MESSAGES_SEND_AND_SAVE] 0,'',0,'','','$From','$Body','',0";
    
            // $query="INSERT INTO TEXT_MESSAGES(MSGDATE,MSGTYPE,REGID,MOBILENO,TEXTMESSAGE,INCO_MESSAGE_SID,INSERTID)
            // VALUES(GETDATE(),'INCOMING',0,'$From','$Body','$MessageSid',0)";
            $stmt=sqlsrv_query($conn, $query);
            
            if($stmt === false)
            {
                // die( print_r( sqlsrv_errors(), true));
                // 		throw new Exception( $mysqli->sqlstate.' - '. $query );
                $data['success'] = false;
                $data['queryFail'] = $query;
                // echo json_encode($data);exit;
            }
            else
            {
                // GET MSGID
                $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
                $GET_MSGID = (int) $row['MSGID'];
                
                // UPDATE DATA
                $update = "UPDATE TEXT_MESSAGES SET MSGTYPE='INCOMING',INCO_MESSAGE_SID='$MessageSid' WHERE MSGID=$GET_MSGID";
                sqlsrv_query($conn, $update);
                
                // INSERT DETAILS
                $query2="INSERT INTO TEXT_MESSAGES_DETAILS(MSGID,MOBILENO,MSG_STATUS)
                VALUES($GET_MSGID,'$From','SUCCESS')";
                sqlsrv_query($conn, $query2);
                
                $data['success'] = true;
                $data['querySuccess'] = $query;
    
            }
        


// ============== SAVE ==============
?>