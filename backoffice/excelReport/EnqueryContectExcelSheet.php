<?php  
session_start();    
require_once '../code/connection.php';
 
$setSql = "SELECT CID,FULLNAME,EMAILID, PHONE, SUBJECT, MESSAGE, LOCATIONID, 
(SELECT LOCATION from LOCATIONS WHERE ISDELETED=0 AND LOC_ID=C.LOCATIONID)LOCATION,
CONVERT(VARCHAR,INSERTDATE,105)INSERTDATE
FROM CONTACTUS C";

if($_SESSION['USER_LOCID'] != '1'){
  $setSql .=" WHERE LOCATIONID=".$_SESSION['USER_LOCID']."";
}


$result = sqlsrv_query($conn, $setSql);
$data = array();

  
$columnHeader = '';  
$columnHeader .= "SNO" ."\t". "DATE". "\t" . "LOCATION" ."\t" ."FULL NAME". "\t" . "EMAIL" . "\t" . "PHONE" . "\t" . "SUBJECT" . "\t" . "MESSAGE" . "\t";  
// $columnHeader .= "ADDRESS1 \t ADDRESS2 \t CITY \t STATE  \t ZIPCODE \t CITIZEN \t EDUCATION \t JOB EXPERIENCE \t BUSINESS EXPERIENCE \t";  
$setData = '';  

  $sn=1;
  while($row=sqlsrv_fetch_array($result))
  {

        $setData .= $sn . "\t";
        $setData .= $row['INSERTDATE'] . "\t";
        $setData .= $row['LOCATION'] . "\t";
        $setData .= $row['FULLNAME'] . "\t";
        $setData .= $row['EMAILID']  . "\t";
        $setData .= $row['PHONE']  . "\t";
        $setData .= $row['SUBJECT'] . "\t";
        $setData .= $row['MESSAGE'] . "\n";
        $sn++;
    }
    
$date = date('d-m-Y');
header("Content-type: application/octet-stream");  
header("Content-Disposition: attachment; filename=".$date."_ENQContect_Sheet.xls");  
header("Pragma: no-cache");  
header("Expires: 0");  
  
echo ucwords($columnHeader) . "\n" . $setData . "\n";  
  
?>  