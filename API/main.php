<?php
session_start();
require_once '../code/connection.php';
include 'login.php';
require_once 'master.php';
require_once 'dashboard.php';
require_once 'attendance.php';
require_once 'announcement.php';
require_once 'receipts.php';
require_once 'sms.php';

if (isset($_POST['type']) && !empty($_POST['type'])) {
    $type = $_POST['type'];
    switch ($type) {
            // LOGIN & SIGNUP START
            case "login": // include 'login.php'; // Include the login page
            login($conn,$_POST); break;
            case "createStudentAccount":createStudentAccount($conn,$_POST); break;
            // LOGIN & SIGNUP END
            
            
            // DASHBOARD START
            case "dashInfo":dashInfo($conn,$_POST); break;
            // DASHBOARD END

            // ATTENDANCE START
            case "all_attendance_data":all_attendance_data($conn,$_POST); break;
            case "saveHomework":saveHomework($conn,$_POST); break;
            // ATTENDANCE END

            // ANNOUNCEMENT START
            case "all_announcement_data":all_announcement_data($conn,$_POST); break;
            // ANNOUNCEMENT END

            // RECEUPTS START
            case "all_receipt_data":all_receipt_data($conn,$_POST); break;
            // RECEUPTS END

            // SMS START
            case "all_sms_data":all_sms_data($conn,$_POST); break;
            // SMS END


            // MASTER PAGE START
            case "locations":locations($conn,$_POST); break;
            case "contries":contries($conn,$_POST); break;
            // MASTER PAGE STARTEND
            default:invalidRequest();
    }
} else {
    invalidRequest();
}
    
/**
 * This function will handle user add, update functionality
 * @throws Exception
 */
    

    


  function invalidRequest()
{
  $data = array();
  $data['success'] = false;
  $data['message'] = "Invalid request.";
  echo json_encode($data);
  exit;
}
?>