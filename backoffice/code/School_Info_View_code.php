<?php
require_once '../../code/connection.php';

if (isset($_POST['type']) && $_POST['type'] == 'getAllSchoolInfo') {
    getAllSchoolInfo($conn);
    exit;
}

function getAllSchoolInfo($conn) {
    $sql = "SELECT SCHOOL_NAME, SCHOOL_ADDRESS, SCHOOL_PHONE, SCHOOL_EMAIL, AFFILIATION, LICENSE_START_DATE, LICENSE_END_DATE FROM SCHOOL_MASTER WHERE ISDELETED = 0";
    $result = sqlsrv_query($conn, $sql);

    $schools = array();
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $schools[] = array(
            'SCHOOL_NAME' => $row['SCHOOL_NAME'],
            'SCHOOL_ADDRESS' => $row['SCHOOL_ADDRESS'],
            'SCHOOL_PHONE' => $row['SCHOOL_PHONE'],
            'SCHOOL_EMAIL' => $row['SCHOOL_EMAIL'],
            'AFFILIATION' => $row['AFFILIATION'],
            'LICENSE_START_DATE' => $row['LICENSE_START_DATE'] ? $row['LICENSE_START_DATE']->format('Y-m-d') : '',
            'LICENSE_END_DATE' => $row['LICENSE_END_DATE'] ? $row['LICENSE_END_DATE']->format('Y-m-d') : ''
        );
    }

    echo json_encode($schools);
}
?>
