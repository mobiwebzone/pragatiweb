<?php  
session_start();    
require_once '../code/connection.php';
 
$setSql = "SELECT CID,(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=C.LOCID AND ISDELETED=0)LOCATION
        ,NAME,EMAIL,PHONE,MADDRESS,PRODUCT
        ,EDUBACKGROUND,WORKEXPERIENCE,ADDINFO,CONVERT(VARCHAR,INSERTDATE,105)INSERTDATE FROM CAREERS C WHERE ISDELETED=0";

$result = sqlsrv_query($conn, $setSql);
$data = array();

  
$columnHeader = '';  
$columnHeader .= "SNO" ."\t". "DATE". "\t" . "LOCATION" ."\t" ."NAME". "\t" . "EMAIL" . "\t" . "PHONE" . "\t" . "ADDRESS" . "\t";  
$columnHeader .= "PRODUCT \t EDUCATION BACKGROUND \t WORK EXPERIENCE \t ADDITIONAL INFORMATION \t";  
$setData = '';  

  $sn=1;
  while($row=sqlsrv_fetch_array($result))
  {

        $setData .= $sn . "\t";
        $setData .= $row['INSERTDATE'] . "\t";
        $setData .= $row['LOCATION'] . "\t";
        $setData .= $row['NAME'] . "\t";
        $setData .= $row['EMAIL'] . "\t";
        $setData .= $row['PHONE']  . "\t";
        $setData .= $row['MADDRESS'] . "\t";
        $setData .= $row['PRODUCT'] . "\t";
        $setData .= $row['EDUBACKGROUND'] . "\t";
        $setData .= $row['WORKEXPERIENCE'] . "\t";
        $setData .= $row['ADDINFO'] . "\n";
        $sn++;
    }
    
$date = date('d-m-Y');
header("Content-type: application/octet-stream");  
header("Content-Disposition: attachment; filename=".$date."_Careers_Sheet.xls");  
header("Pragma: no-cache");  
header("Expires: 0");  
  
echo ucwords($columnHeader) . "\n" . $setData . "\n";  
  
?>  