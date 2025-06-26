<?php

/*============ Get Locations =============*/
function locations($mysqli){
	try
	{
		$data = array();
		$query = "SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 ORDER BY LOCATION";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
                $data['data'][] = $row;
            }
            $data['success'] = true;
		}else{
			$data['success'] = false;
            $data['message'] = 'Failed.';
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

/*============ Get Contries =============*/
function contries($mysqli){
	try
	{
		$data = array();
		$query = "SELECT COUNTRYID,COUNTRY FROM COUNTRIES WHERE ISDELETED=0 ORDER BY COUNTRY";
		$count = unique($query);
		if($count > 0){
			$result = sqlsrv_query($mysqli, $query);
			while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
                $data['data'][] = $row;
            }
            $data['success'] = true;
		}else{
			$data['success'] = false;
            $data['message'] = 'Contries not found.';
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
?>