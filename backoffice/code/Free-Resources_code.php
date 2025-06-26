<?php
session_start();
require_once 'connection.php';

if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}


if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
        case "getMainCat":getMainCat($conn);break;
        case "getUnderCategory":getUnderCategory($conn);break;
		case "getTree":getTree($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function will handle user add, update functionality
 * @throws Exception
 */




 /*============ Get Free Resource Main category =============*/ 
function getMainCat($mysqli){
	try
	{
		$query = "SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID
		FROM FREE_RESOURCES WHERE ISDELETED=0 AND RESOURCE_CATEGORY='Category' AND UNDER_ID=0 order by SEQNO";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ID'] = (int) $row['ID'];
			$data['data'][] = $row;
		}
		$data['success'] = true;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}
$PRINT_RESOURCE="";
/*============ Get Free Resource Under category =============*/ 
function getUnderCategory($mysqli){
	try
	{
		global $PRINT_RESOURCE;
		
		$underid = ($_POST['underid'] == 'undefined' || $_POST['underid'] == '') ? 0 : $_POST['underid'];
		$mainCat = $_POST['mainCat'] == 'undefined' ? '' : $_POST['mainCat'];
		if($underid == 0){
			throw new Exception("Somthing Wrong");
		}
		$query = "SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,RESOURCE_LINK
		FROM FREE_RESOURCES WHERE ISDELETED=0 AND UNDER_ID=$underid ORDER BY SEQNO";

		$result = sqlsrv_query($mysqli, $query);
		$data = array();
		while ($row = sqlsrv_fetch_array($result)) {
			$row['ID'] = (int) $row['ID'];
			$data['data'][] = $row;
			$varid=(int) $row['ID'];
			$PRINT_RESOURCE.='<h3>'.$mainCat.'</h3>';
			$PRINT_RESOURCE.='<ul class="list-group"><li class="list-group-item" ng-init="test()" >'.$row['RESOURCE_CATEGORY_TEXT'].'</li></ul>';
			
		
		}
		$data['ListField'] = $PRINT_RESOURCE;
		$data['success'] = true;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}



$treeString="";
function getTree($mysqli){
	try
	{
		global $treeString;
		$treeString="<ul class='tree'>";
		$underid = ($_POST['underid'] == 'undefined' || $_POST['underid'] == '') ? 0 : $_POST['underid'];
		$mainCat = $_POST['mainCat'] == 'undefined' ? '' : $_POST['mainCat'];
		$data = array();
		$qryParent="SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,RESOURCE_LINK
		FROM FREE_RESOURCES WHERE ISDELETED=0 AND UNDER_ID=$underid  ORDER BY SEQNO";
		$count=unique($qryParent);
		if($count>0)
		{
			$resultParent = sqlsrv_query($mysqli, $qryParent);
			$treeString.='<h3>'.$mainCat.'</h3>';
			while($rowParent = sqlsrv_fetch_array($resultParent)){

				$id=$rowParent['ID'];
				// $treeString.="<li class='list-group-item'><span ng-class='{".$rowParent['RESOURCE_CATEGORY']." == 'Category' ? 'font-weight-bold' : ''}'><a href='".$rowParent['RESOURCE_LINK']."' target='_Blank'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</a></span>";
				
				if($rowParent['RESOURCE_CATEGORY']=='Category')
				{

					$treeString.="<li class='list-group-item'><span class='font-weight-bold'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</span>";
				}
				else
				{
					$treeString.="<li class='list-group-item'><span><a href='".$rowParent['RESOURCE_LINK']."' target='_Blank'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</a></span>";
				
				}
				$treeString= getChild($mysqli,$id);
			}
		
			$data['success'] = true;
		}
		else{
			$data['success'] = false;
		}
		$data['treeString'] = $treeString;
		echo json_encode($data);
		exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}


function getChild($mysqli,$id)
{
	global $treeString;
	
	$qryChilds="SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,RESOURCE_LINK
	FROM FREE_RESOURCES WHERE ISDELETED=0 AND UNDER_ID=$id  ORDER BY SEQNO";
		
		$count=unique($qryChilds);
		if($count>0)
		{
			$resultChilds = sqlsrv_query($mysqli, $qryChilds);
			$treeString.="<ul class='list-group'>";
			while ($rowChilds = sqlsrv_fetch_array($resultChilds)){
				$id=$rowChilds['ID'];
				if($rowChilds['RESOURCE_CATEGORY']=='Category')
				{

					$treeString.="<li class='list-group-item'><span class='font-weight-bold'>".$rowChilds['RESOURCE_CATEGORY_TEXT']."</span>";
				}
				else
				{
					$treeString.="<li class='list-group-item'><span><a href='".$rowChilds['RESOURCE_LINK']."' target='_Blank'>".$rowChilds['RESOURCE_CATEGORY_TEXT']."</a></span>";
				
				}
				// $treeString.="<li class='list-group-item' ng-class='{".$rowChilds['RESOURCE_CATEGORY']." == 'Category' ? 'font-weight-bold' : ''}'><a href='".$rowChilds['RESOURCE_LINK']." '  target='_Blank'>".$rowChilds['RESOURCE_CATEGORY_TEXT']."</a></span>";
				getChild($mysqli,$id);
			}	
			$treeString.="</li></ul>";
		}
	   else
	   {
		$treeString.="</li>";		
	   }
	   return $treeString;
}









function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







