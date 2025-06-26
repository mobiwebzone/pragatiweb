<?php  
session_start();    
require_once '../code/connection.php';

 
$setSql = "SELECT REGID,LOCATIONID,MODE,
(SELECT LOCATION FROM LOCATIONS WHERE LOC_ID=R.LOCATIONID)LOCATION,
FIRSTNAME,LASTNAME,PHONE,EMAIL,GRADE,SCHOOL,
ADDRESSLINE1,ADDRESSLINE2,CITY,STATE,ZIPCODE,COUNTRYID,
(SELECT COUNTRY FROM COUNTRIES WHERE COUNTRYID=R.COUNTRYID)COUNTRY,
ALLERGIES,P1_FIRSTNAME,P1_LASTNAME,P1_PHONE,P1_EMAIL,
P2_FIRSTNAME,P2_LASTNAME,P2_PHONE,P2_EMAIL,
REFERREDBY,FINDUS,INSTRUCTIONS,AGREED,CONVERT(VARCHAR,INSERTDATE,107)INSERTDATE,
(select planname+ ' | '  from plans where planid in (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=R.REGID AND CANCELLED=0 ) for xml path('')) ACTIVE_PLANS
FROM REGISTRATIONS R WHERE ISDELETED=0";

if($_SESSION['USER_LOCID'] != '1'){
  $setSql .=" AND LOCATIONID=".$_SESSION['USER_LOCID']."";
}

$setSql .=" ORDER BY REGID DESC";

$result = sqlsrv_query($conn, $setSql);
$data = array();

  
$columnHeader = '';  
$columnHeader .= "SNO". "\t" . "LOCATION" . "\t" . "MODE" . "\t" . "FIRSTNAME". "\t" . "LASTNAME" . "\t" . "PHONE" . "\t" . "EMAIL" . "\t" . "GRADE" . "\t";  
$columnHeader .= "SCHOOL \t ADDRESSLINE1 \t ADDRESSLINE2 \t CITY \t STATE  \t ZIPCODE \t COUNTRY \t ALLERGIES \t P1_FIRSTNAME \t P1_LASTNAME \t";  
$columnHeader .= "P1_PHONE \t P1_EMAIL \t P2_FIRSTNAME \t P2_LASTNAME \t P2_PHONE  \t P2_EMAIL \t REFERREDBY \t FINDUS \t INSTRUCTIONS \t DATE \t ACTIVE_PLANS \t";  
$setData = '';  

  $sn=1;
  while($row=sqlsrv_fetch_array($result))
  {

    $row['ACTIVE_PLANS'] =  rtrim($row['ACTIVE_PLANS'], "| ");


        $setData .= $sn . "\t";
        $setData .= $row['LOCATION'] . "\t";
        $setData .= $row['MODE'] . "\t";
        $setData .= $row['FIRSTNAME'] . "\t";
        $setData .= $row['LASTNAME'] . "\t";
        $setData .= $row['PHONE'] . "\t";
        $setData .= $row['EMAIL']  . "\t";
        $setData .= $row['GRADE'] . "\t";
        $setData .= $row['SCHOOL'] . "\t";
        $setData .= $row['ADDRESSLINE1'] . "\t";
        $setData .= $row['ADDRESSLINE2'] . "\t";
        $setData .= $row['CITY'] . "\t";
        $setData .= $row['STATE'] . "\t";
        $setData .= $row['ZIPCODE'] . "\t";
        $setData .= $row['COUNTRY'] . "\t";
        $setData .= $row['ALLERGIES'] . "\t";
        $setData .= $row['P1_FIRSTNAME'] . "\t";
        $setData .= $row['P1_LASTNAME'] . "\t";
        $setData .= $row['P1_PHONE'] . "\t";
        $setData .= $row['P1_EMAIL'] . "\t";
        $setData .= $row['P2_FIRSTNAME'] . "\t";
        $setData .= $row['P2_LASTNAME'] . "\t";
        $setData .= $row['P2_PHONE'] . "\t";
        $setData .= $row['P2_EMAIL'] . "\t";
        $setData .= $row['REFERREDBY'] . "\t";
        $setData .= $row['FINDUS'] . "\t";
        $setData .= $row['INSTRUCTIONS'] . "\t";
        $setData .= $row['INSERTDATE'] . "\t";
        $setData .= $row['ACTIVE_PLANS'] . "\n";
        $sn++;
    }
    
$date = date('d-m-Y');
header("Content-type: application/octet-stream");  
header("Content-Disposition: attachment; filename=".$date."_Registration_Sheet.xls");  
header("Pragma: no-cache");  
header("Expires: 0");  
  
echo ucwords($columnHeader) . "\n" . $setData . "\n";  
  
?>  