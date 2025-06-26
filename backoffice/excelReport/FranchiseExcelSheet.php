<?php  
session_start();    
require_once '../code/connection.php';
 
$setSql = "SELECT FAID, FIRSTNAME,MIDDLENAME,LASTNAME, CONVERT(VARCHAR,BIRTHDATE,105)BIRTHDATE,PHONE,EMAILID,ADDRESS1,ADDRESS2,CITY,STATE,ZIPCODE,CITIZEN,
EDUCATION,JOBEXP,BUSIEXP,TUTEXP,LIQFINRESOURCE,FELONY,PASTPERSONAL,CONVERT(VARCHAR,INSERTDATE,105)INSERTDATE FROM FRANCHISE_APPLICATION";

$result = sqlsrv_query($conn, $setSql);
$data = array();

  
$columnHeader = '';  
$columnHeader .= "SNO" ."\t". "DATE". "\t" . "FIRST NAME" ."\t" ."MIDDLE NAME". "\t" . "LAST NAME" . "\t" . "BIRTH DATE" . "\t" . "PHONE" . "\t" . "EMAILID" . "\t";  
$columnHeader .= "ADDRESS1 \t ADDRESS2 \t CITY \t STATE  \t ZIPCODE \t CITIZEN \t EDUCATION \t JOB EXPERIENCE \t BUSINESS EXPERIENCE \t";  
$columnHeader .= "TUTORING EXPERIENCE \t LIQUID FINANCIAL RESOURCES \t FELONY \t";
$columnHeader .= "PAST FILINGS \t";  
$setData = '';  

  $sn=1;
  while($row=sqlsrv_fetch_array($result))
  {

        $setData .= $sn . "\t";
        $setData .= $row['INSERTDATE'] . "\t";
        $setData .= $row['FIRSTNAME'] . "\t";
        $setData .= $row['MIDDLENAME'] . "\t";
        $setData .= $row['LASTNAME'] . "\t";
        $setData .= $row['BIRTHDATE']  . "\t";
        $setData .= $row['PHONE']  . "\t";
        $setData .= $row['EMAILID'] . "\t";
        $setData .= $row['ADDRESS1'] . "\t";
        $setData .= $row['ADDRESS2'] . "\t";
        $setData .= $row['CITY'] . "\t";
        $setData .= $row['STATE'] . "\t";
        $setData .= $row['ZIPCODE'] . "\t";
        $setData .= $row['CITIZEN'] . "\t";
        $setData .= $row['EDUCATION'] . "\t";
        $setData .= $row['JOBEXP'] . "\t";
        $setData .= $row['BUSIEXP'] . "\t";
        $setData .= $row['TUTEXP'] . "\t";
        $setData .= $row['LIQFINRESOURCE'] . "\t";
        $setData .= $row['FELONY'] . "\t";
        $setData .= $row['PASTPERSONAL'] . "\n";
        $sn++;
    }
    
$date = date('d-m-Y');
header("Content-type: application/octet-stream");  
header("Content-Disposition: attachment; filename=".$date."_Franchise_Sheet.xls");  
header("Pragma: no-cache");  
header("Expires: 0");  
  
echo ucwords($columnHeader) . "\n" . $setData . "\n";  
  
?>  