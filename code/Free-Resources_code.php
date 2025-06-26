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
		case "resourceCarousel":resourceCarousel($conn);break;
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
		if($underid == 0) throw new Exception("Somthing Wrong");

		// GET CATEGORY NAME
		$queryCat = "SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=$underid";
		$resultCat = sqlsrv_query($mysqli, $queryCat);
		$rowCat = sqlsrv_fetch_array($resultCat,SQLSRV_FETCH_ASSOC);
		$mainCat = $rowCat['RESOURCE_CATEGORY_TEXT'];

		
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
		$max_height = '100px';
		$data = array();
		$treeString="<ul class='tree'>";
		$underid = ($_POST['underid'] == 'undefined' || $_POST['underid'] == '') ? 0 : $_POST['underid'];

		// GET CATEGORY NAME
		$queryCat = "SELECT RESOURCE_CATEGORY_TEXT FROM FREE_RESOURCES WHERE ID=$underid AND ISDELETED=0";
		$data['$queryCat'] = $queryCat;
		$resultCat = sqlsrv_query($mysqli, $queryCat);
		$rowCat = sqlsrv_fetch_array($resultCat,SQLSRV_FETCH_ASSOC);
		$mainCat = $rowCat['RESOURCE_CATEGORY_TEXT'];

		$qryParent="SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,BLINK,COLOR,
		HTML,RESOURCE_LINK_LABEL,RESOURCE_LINK,RESOURCE_IMAGES
		FROM FREE_RESOURCES WHERE ISDELETED=0 AND UNDER_ID=$underid  ORDER BY SEQNO";
		$count=unique($qryParent);
		if($count>0)
		{
			$resultParent = sqlsrv_query($mysqli, $qryParent);
			$treeString.='<h3>'.$mainCat.'</h3>';
			while($rowParent = sqlsrv_fetch_array($resultParent)){

				//#### IMAGES
				$RESOURCE_IMG_ARR = array();
				if($rowParent['RESOURCE_IMAGES']!==''){
					$RESOURCE_IMG = $rowParent['RESOURCE_IMAGES'];
					$RESOURCE_IMG_ARR = explode(", ",$rowParent['RESOURCE_IMAGES']);
				}


				$id=$rowParent['ID'];
				// $treeString.="<li class='list-group-item'><span ng-class='{".$rowParent['RESOURCE_CATEGORY']." == 'Category' ? 'font-weight-bold' : ''}'><a href='".$rowParent['RESOURCE_LINK']."' target='_Blank'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</a></span>";
				
				if($rowParent['RESOURCE_CATEGORY']=='Category')
				{
					$BLINK = $rowParent['BLINK']=='1'?'blink':'';
					$COLOR = $rowParent['COLOR'];
					$treeString.="<li class='list-group-item'><span class='font-weight-bold $BLINK' style='color:$COLOR'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</span>";
				}
				else
				{
					$treeString.="<li class='list-group-item py-2 px-0 px-lg-3'>
								  <div class='table-responsive'>";
					// <span><a href='".$rowParent['RESOURCE_LINK']."' target='_Blank'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</a></span>";
					$treeString.="<table class='table table-sm mb-0'>
					<tr>
						<th rowspan='0' class='align-middle px-2 font-weight-normal'>".$rowParent['RESOURCE_CATEGORY_TEXT']."</th>";

					// GET FEATURE VALUE
					$F_DATA = array();
					$FVAL_DATA = array();
					$CFID = array();
					$FEATURES = array();
					$qurFeaturesVal = "SELECT RFVID,CFID,(SELECT FEATURES FROM FREE_RESOURCES_FEATURES WHERE CFID=FRFV.CFID AND ISDELETED=0)FEATURES,
					FEATURE_VALUE FROM FREE_RESOURCES_FEATURE_VALUES FRFV WHERE ISDELETED=0 AND ID=$id";
					$data['qurFeaturesVal'][] = $qurFeaturesVal;
					$countFeaturesVal = unique($qurFeaturesVal);
					if($countFeaturesVal > 0){
						$resultFeaturesVal = sqlsrv_query($mysqli, $qurFeaturesVal);
						while ($rowFeaturesVal = sqlsrv_fetch_array($resultFeaturesVal,SQLSRV_FETCH_ASSOC)){
							$Feature = strtolower($rowFeaturesVal['FEATURES']);

							// CHECK ISMEDIA
							$Feature = str_replace(' ', '', $Feature);
							$rowFeaturesVal['ISMEDIA'] = $Feature=='media' ? 1 : 0;
							// CHECK ISESSAY
							$word = "essay";
							$FeatureName = strtolower($Feature);
							if(strpos($FeatureName, $word) !== false){
								$rowFeaturesVal['ISESSAY'] = 1;
							}else{
								$rowFeaturesVal['ISESSAY'] = 0;
							}

							$CFID[] = (int)$rowFeaturesVal['CFID'];
							$F_DATA[] = $rowFeaturesVal['FEATURES'];
							$FVAL_DATA[] = $rowFeaturesVal;
						}
						// print_r($F_DATA);
						// $data['$F_DATA'][]=$F_DATA;
						
						$uniqueFeature = array_unique($F_DATA);
						$FEATURES = array_values($uniqueFeature);
						
						// $uniqueCFID = array_unique($CFID);
						// $CFID = array_values($uniqueCFID);
						
						// $data['$FEATURES'][]=$FEATURES;
						// $data['$CFID'][]=$CFID;
						// echo json_encode($data);exit;

						if(isset($FEATURES) && count($FEATURES)>0){

							for ($i=0; $i <count($FEATURES); $i++) { 
								$FEA = $FEATURES[$i];
								$word = "essay";
								$FeatureName = strtolower($FEA);
								
								// Test if string contains the word 
								if(strpos($FeatureName, $word) !== false){
									$treeString.="<th width='500px'>".$FEA."</th>";
								}else{
									$treeString.="<th>".$FEA."</th>";
								}
							}
						}
						
					}
					
					if($rowParent['HTML'] == 0){
						$treeString.="<th rowspan='0' class='align-middle font-weight-normal' style='width:10%'>
										<u><a href='".$rowParent['RESOURCE_LINK']."' class='py-0 btn-link text-primary text-nowrap' target='_Blank' title='click here for more details'><i class='fa fa-link'></i> More Details</a></u>
									</th>
								</tr>";
					}else{
						$treeString.="<th rowspan='0' class='align-middle' style='width:20%'>
										<div>".$rowParent['RESOURCE_LINK']."</div>";
										if(count($RESOURCE_IMG_ARR)>0){
											$treeString.="<div class='d-flex justify-content-around flex-wrap'>";
											for($i=0; $i<count($RESOURCE_IMG_ARR);$i++){
												$treeString.="<img ng-click='viewRecImages(\"".$RESOURCE_IMG."\")' src='backoffice/images/free_resources/".$RESOURCE_IMG_ARR[$i]."' class='img-thumbnail' alt='".$RESOURCE_IMG_ARR[$i]."' width='50px'/>";
											}
											$treeString.="</div>";
										}
						$treeString.="</th>
								</tr>";
					}
							// <td>".$rowParent['RESOURCE_CATEGORY_TEXT']."</td>";
							$arr = array();
							$NEW_CFID = array();
							$A_DATA = array();
							$FINAL_DATA = array();

							foreach ($FVAL_DATA as $key=>$s) {
								$A_DATA[] = $s;
								$NEW_CFID[] = (int)$s['CFID'];
								if ($CFID[$key] == $s['CFID']) {
									$FINAL_DATA[]=$A_DATA;
									$NEW_CFID = array();
									$A_DATA = array();
								}
							}
							$data['FINAL_DATA'][]=$FVAL_DATA;
							// echo json_encode($data);exit;
							
							if(count($FINAL_DATA) > 0){
								$idx=0;
								foreach($FINAL_DATA as $d){
								$treeString.="<tr>";
									$data['$d'][]=$d;
									for($s=0;$s<count($d);$s++){
										$data['test'][]=$d[$s];
										$url = $d[$s]['FEATURE_VALUE'];
										$ISMEDIA = $d[$s]['ISMEDIA'];
										$url = filter_var($url, FILTER_SANITIZE_URL);
										if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
											if($ISMEDIA==1){
												$treeString.="<td>
																<div class='d-flex flex-column justify-content-center align-items-center' >
																	<iframe scrolling='no' src='".$d[$s]['FEATURE_VALUE']."'  width='100%' height='90px' autoplay='true'></iframe>
																	<a href='$url' target='_blank' ng-click='getDet(\"$url\")' class='btn alert-light btn-block py-1 text-monospace border text-white'
																		style='background: repeating-linear-gradient(178deg, #000000e3, transparent 100px);'>View</a>		
																</div>
															</td>";
															
															// <button data-toggle='modal' data-target='#ViewLargeModal' ng-click='getDet(\"$url\")' class='btn alert-warning btn-block py-1'>View</button>
												// sandbox='allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-presentation allow-same-origin allow-scripts allow-top-navigation allow-top-navigation-by-user-activation'
											}else{
												$treeString.="<td><a class='text-primary text-break' style='text-decoration: underline;' target='_blank' href=".$d[$s]['FEATURE_VALUE'].">".$d[$s]['FEATURE_VALUE']."</a></td>";				
											}
										}else{
											// CHECK IF ESSAY
											$ISESSAY = $d[$s]['ISESSAY'];
											$FEATURE_VALUE = $d[$s]['FEATURE_VALUE'];
											if($ISESSAY==0){
												$treeString.="<td style='white-space: break-spaces;'><div style='max-height:$max_height'>$FEATURE_VALUE</div></td>";
											}else{
												$treeString.="<td>
																<div class='essayCollapse' style='max-height:$max_height' id='col$idx'>
																$FEATURE_VALUE
																</div>
																<div class='text-center' ng-click='toggleCollapse(\"$idx\")'>
																	<i class='fa fa-sort-desc font-20 essayCollapseArrow pointer' id='arrow$idx'></i>
																</div>
															</td>";
											}
										}
									}
									
									// if($idx == 0){
									// 	$treeString.="<td rowspan='0' style='width:10%' class='align-middle'>
									// 		<a href='".$rowParent['RESOURCE_LINK']."' class='py-0 btn-link' target='_Blank' title='click here for more details'>More Details</a>
									// 		</td>";
									// }
									$treeString.="</tr>";
									$idx++;
								}
							}
							else{
								// $treeString.="<tr>
								// <td style='width:10%'>
								// 		<a href='".$rowParent['RESOURCE_LINK']."' class='py-0 btn-link' target='_Blank' title='click here for more details'>More Details</a>
								// 		</td>
								// </tr>";
							}
					$treeString.="</table></div>";
				
				}
				$treeString= getChild($mysqli,$id,$underid);
			}
		
			$data['success'] = true;
		}
		else{
			$data['success'] = false;
		}
		$data['treeString'] = $treeString;
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

// -------------------------------------


function getChild($mysqli,$id,$underid)
{
	global $treeString;
	$data = array();
	
	$qryChilds="SELECT ID,RESOURCE_CATEGORY,RESOURCE_CATEGORY_TEXT,UNDER_ID,HTML,RESOURCE_LINK_LABEL,RESOURCE_LINK,RESOURCE_IMAGES
	FROM FREE_RESOURCES WHERE ISDELETED=0 AND UNDER_ID=$id  ORDER BY SEQNO";
		
		$count=unique($qryChilds);
		if($count>0)
		{
			$resultChilds = sqlsrv_query($mysqli, $qryChilds);
			$treeString.="<ul class='list-group'>";
			while ($rowChilds = sqlsrv_fetch_array($resultChilds)){
				$id=$rowChilds['ID'];
				$RESOURCE_LINK_LABEL = empty($rowChilds['RESOURCE_LINK_LABEL']) ? $rowChilds['RESOURCE_LINK'] : $rowChilds['RESOURCE_LINK_LABEL'];
				//#### IMAGES
				$RESOURCE_IMG_ARR = array();
				if($rowChilds['RESOURCE_IMAGES']!==''){
					$RESOURCE_IMG = $rowChilds['RESOURCE_IMAGES'];
					$RESOURCE_IMG_ARR = explode(", ",$rowChilds['RESOURCE_IMAGES']);
				}


				if($rowChilds['RESOURCE_CATEGORY']=='Category')
				{

					$treeString.="<li class='list-group-item'><span class=''>".$rowChilds['RESOURCE_CATEGORY_TEXT']."2</span>";
				}
				else
				{
					// $treeString.="<li class='list-group-item'><span><a href='".$rowChilds['RESOURCE_LINK']."' target='_Blank'>".$rowChilds['RESOURCE_CATEGORY_TEXT']."</a></span>";
					$treeString.="<li class='list-group-item py-2'><div class='table-responsive'>";

					$treeString.="<table class='table table-sm mb-0'>
					<tr>
						<th rowspan='0' class='align-middle px-2 font-weight-normal'>".$rowChilds['RESOURCE_CATEGORY_TEXT']."</th>";

					// GET FEATURE VALUE
					$F_DATA = array();
					$FVAL_DATA = array();
					$CFID = array();
					$FEATURES = array();
					$qurFeaturesVal = "SELECT RFVID,CFID,(SELECT FEATURES FROM FREE_RESOURCES_FEATURES WHERE CFID=FRFV.CFID AND ISDELETED=0)FEATURES,
					FEATURE_VALUE FROM FREE_RESOURCES_FEATURE_VALUES FRFV WHERE ISDELETED=0 AND ID=$id";
					$countFeaturesVal = unique($qurFeaturesVal);
					if($countFeaturesVal > 0){
						$resultFeaturesVal = sqlsrv_query($mysqli, $qurFeaturesVal);
						while ($rowFeaturesVal = sqlsrv_fetch_array($resultFeaturesVal,SQLSRV_FETCH_ASSOC)){
							$CFID[] = (int)$rowFeaturesVal['CFID'];
							$F_DATA[] = $rowFeaturesVal['FEATURES'];
							$FVAL_DATA[] = $rowFeaturesVal;
						}
						// print_r($F_DATA);
						// $data['$F_DATA'][]=$F_DATA;


						$FEATURES = array_unique($F_DATA);
						$CFID = array_unique($CFID);

						for ($i=0; $i <count($FEATURES); $i++) { 
							$FEA = $FEATURES[$i];
							$treeString.="<th>".$FEA."</th>";
						}
					}

					if($rowChilds['HTML'] == 0){
						$treeString.="<th rowspan='0' class='align-middle font-weight-normal' style='width:10%'>
											<u><a href='".$rowChilds['RESOURCE_LINK']."' class='py-0 btn-link text-primary text-nowrap' target='_Blank' title='click here for more details'><i class='fa fa-link'></i> More Details</a></u>
									</th>
								</tr>
								<tr>";
					}else{
						$treeString.="<th rowspan='0' class='align-middle' style='width:19%'>
										<div>".$rowChilds['RESOURCE_LINK']."</div>";
							if(count($RESOURCE_IMG_ARR)>0){
								$treeString.="<div class='d-flex justify-content-around flex-wrap'>";
								for($i=0; $i<count($RESOURCE_IMG_ARR);$i++){
									$treeString.="<img ng-click='viewRecImages(\"".$RESOURCE_IMG."\")' src='backoffice/images/free_resources/".$RESOURCE_IMG_ARR[$i]."' class='img-thumbnail' alt='".$RESOURCE_IMG_ARR[$i]."' width='50px'/>";
								}
								$treeString.="</div>";
							}
							$treeString.="</th>
									</tr>
									<tr>";								
					}

					
							// <td>".$rowChilds['RESOURCE_CATEGORY_TEXT']."</td>";
							$arr = array();
							$NEW_CFID = array();
							$A_DATA = array();
							$FINAL_DATA = array();

							foreach ($FVAL_DATA as $s) {
								$A_DATA[] = $s;
								$NEW_CFID[] = (int)$s['CFID'];
								if ($CFID === $NEW_CFID) {
									$FINAL_DATA[]=$A_DATA;
									$NEW_CFID = array();
									$A_DATA = array();
								}
							}
							$data['FINAL_DATA'][]=$FINAL_DATA;
							// ksort($arr, SORT_NUMERIC);

							if(count($FINAL_DATA) > 0){
								$idx=0;
								foreach($FINAL_DATA as $d){
								$treeString.="<tr>";
									// $data['$d'][]=$d[$idx];
									for($s=0;$s<count($d);$s++){
										// $ss=$d[$s]['FEATURE_VALUE'];
										$url = $d[$s]['FEATURE_VALUE'];
										$url = filter_var($url, FILTER_SANITIZE_URL);
										if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
											$treeString.="<td><a href=".$d[$s]['FEATURE_VALUE']." class='text-primary text-break p-0' target='_blank'>".$RESOURCE_LINK_LABEL."</a></td>";				
										}else{
											$treeString.="<td>".$d[$s]['FEATURE_VALUE']."</td>";				
										}
									}
									
									// if($idx == 0){
									// 	$treeString.="<td rowspan='0' style='width:10%'>
									// 		<a href='".$rowChilds['RESOURCE_LINK']."' class='py-0 text-primary' target='_Blank' title='click here for more details'>More Details</a>
									// 		</td>";
									// }
									$treeString.="</tr>";
									$idx++;
								}
							}
							else{
								// $treeString.="<tr>
								// <td style='width:10%'>
								// 		<a href='".$rowChilds['RESOURCE_LINK']."' class='py-0 text-primary' target='_Blank' title='click here for more details'>More Details</a>
								// 		</td>
								// </tr>";
							}
					$treeString.="</table></div>";


					

					// GET FEATURES
					// $qurFeatures = "SELECT CFID,FEATURES FROM FREE_RESOURCES_FEATURES WHERE ISDELETED=0 AND ID=$underid";
					// $countFeatures = unique($qurFeatures);
					// if($countFeatures > 0){
					// 	$resultFeatures = sqlsrv_query($mysqli, $qurFeatures);
					// 	while ($rowFeatures = sqlsrv_fetch_array($resultFeatures,SQLSRV_FETCH_ASSOC)){
					// 		$treeString.="<th>".$rowFeatures['FEATURES']."</th>";
					// 	}
					// }

			
					
				
				}
				getChild($mysqli,$id,$underid);
			}	
			$treeString.="</li></ul>";
		}
	   else
	   {
		$treeString.="</li>";		
	   }
	   return $treeString;
}




/*============ GET RESOURCE CATEGORY CAROUSEL =============*/ 
function resourceCarousel($mysqli){
	try
	{
		$data = array();
		$ID = ($_POST['ID'] == 'undefined' || $_POST['ID'] == '') ? 0 : $_POST['ID'];
		if($ID==0) throw new Exception("Invalid ID.");

		$query = "SELECT RCCID,DISPLAY_TYPE,PIC,PIC_CAPTION,
				CONVERT(VARCHAR,PIC_FROMDT,106)VALID_FROM,CONVERT(VARCHAR,PIC_TODT,106)VALID_UPTO,PIC_INTERVAL,SEQNO 
				FROM RESOURCE_CATEGORY_CAROUSEL_DISPLAY WHERE ISDELETED=0 AND ID=$ID AND 
				CONVERT(DATE,GETDATE(),105) BETWEEN CONVERT(DATE,PIC_FROMDT,105) AND CONVERT(DATE,PIC_TODT,105)
				ORDER BY SEQNO ASC";

		$count = unique($query);
		if($count>0){
			$result = sqlsrv_query($mysqli, $query);
			while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$row['PIC'] = 'backoffice/images/resource_category_carousel/'.$row['PIC'];
				$data['data'][] = $row;
			}
			$data['success'] = true;
		}else{
			$data['success'] = false;
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
/*============ GET RESOURCE CATEGORY CAROUSEL =============*/ 




function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}







