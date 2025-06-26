<?php session_start();
if(!empty($_SESSION['MEP_USERID']))
{$userid=$_SESSION['MEP_USERID'];}
else
{$userid=0;}

if(!empty($_SESSION['ROLE']))
{$login_role=$_SESSION['ROLE'];}
else
{$login_role='';}

require_once '../code/connection.php';

if( isset($_POST['type']) && !empty($_POST['type'] ) ){
	$type = $_POST['type'];
	
	switch ($type) {
		case "checkSession":checkSession($conn);break;
		case "getBrandLogo":getBrandLogo($conn);break;
		case "logout":logout($conn);break;
		default:invalidRequest();
	}
}else{
	invalidRequest();
}


/**
 * This function gets list of users from database
 */
$all_data=array();
$selected_all_data=array();

function checkSession($conn){
	try{
		global $userid,$all_data,$selected_all_data;
		$data = array();
		if($userid>0)
		{

			$sql="SELECT [UID],FIRSTNAME,LASTNAME,MOBILE,EMAIL,LOGINID,USERROLE,LOCID,
			(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=USERS.LOCID)[LOCATION],
			(SELECT IS_ET FROM LOCATIONS WHERE LOC_ID=USERS.LOCID)IS_ET
			FROM USERS WHERE [UID]=$userid";
						
			$stmt = sqlsrv_query($conn, $sql);
			$count = unique($sql);
			$data['$count'] = $count;
			if($stmt == false)
			{
				$data['success'] = false;
				$data['message'] = 'Login Failed' . "/" . $userid;
				echo json_encode($data);exit;
			}
			else
			{
				while ($row = sqlsrv_fetch_array($stmt) ) {
					$data['data'][] = $row;
					$data['userid']=$row['UID'];
					$data['userFName']=$row['FIRSTNAME'];
					$data['userLName']=$row['LASTNAME'];
					$data['userrole']=$row['USERROLE'];
					$data['locid']=$row['LOCID'];
					$data['LOCATION']=$row['LOCATION'];
					$data['IS_ET']=$row['IS_ET'];
				}
				
				$data['sql'] = $sql;
				$data['success'] = true;
				$data['message'] = 'Login Successfull';
				$data['session'] = true;

				//################################
				//############## GET_MENU START
				//################################
				$MENUID_USER = array();
				$queryUM = "SELECT MENUID FROM BO_LOCATION_USER_MENU_PERMISSIONS WHERE ACTIVE=1 AND ISDELETED=0 AND USERID=$userid";
				$data['$queryUM']=$queryUM;
				$resultUM = sqlsrv_query($conn, $queryUM);
				if(sqlsrv_has_rows($resultUM) !== false){
					while ($rowUM = sqlsrv_fetch_array($resultUM,SQLSRV_FETCH_ASSOC)) {
						$data['$rowUM'][]  = $rowUM;
						$MENUID_USER[]=$rowUM['MENUID'];
					}
				}

				$queryM = "SELECT MENUID,MENU,MENU_SHORTNAME,SEQNO,UNDER_MENUID,PAGE_LINK,HAS_LINK,ISHEADER,HEADER,
				ISNULL((SELECT MENU FROM BO_MENU WHERE MENUID=M.UNDER_MENUID),'')UNDER,
				ISNULL((SELECT SEQNO FROM BO_MENU WHERE MENUID=M.UNDER_MENUID),0)UNDER_SEQNO,SETHEIGHT,
				CASE WHEN (SELECT COUNT(*) FROM BO_MENU WHERE UNDER_MENUID=M.MENUID)>0 THEN 1 ELSE 0 END NEXT_MENU_EXIST
				FROM BO_MENU M 
				WHERE ISDELETED=0 
				ORDER BY UNDER_SEQNO,SEQNO,UNDER,MENU";

				$data['$queryM']=$queryM;
				$resultM = sqlsrv_query($conn, $queryM);
				if(sqlsrv_has_rows($resultM) !== false){
					
					while ($rowM = sqlsrv_fetch_array($resultM,SQLSRV_FETCH_ASSOC)) {
						$all_data[] = $rowM;
						if($rowM['UNDER_MENUID'] > 0){
							$data['dataUnder'][] = $rowM;
						}
						$data['dataM'][] = $rowM;
					}

					//######### CREATE MENU DATA START #########
					$get_menu = array();
					foreach($MENUID_USER as $MID){
						$get_menu[] = getUnderMenuId($MID);
					}
					$selected_all_data = reindexingObject($get_menu); 
					$data['get_menu'] = $selected_all_data;
					//######### CREATE MENU DATA END #########
					
					$UnderMenuData = getUnderMenu($conn,0);
					if(isset($UnderMenuData['RETURN_DATA']) && $UnderMenuData['RETURN_DATA']!=''){
						$data['=========1'] = $UnderMenuData['RETURN_DATA'];
						$UnderMenuData['RETURN_DATA'] = preg_replace('/<\/ul><\/li>$/', '', $UnderMenuData['RETURN_DATA']);
						$data['=========2'] = $UnderMenuData['RETURN_DATA'];
						$UnderMenuData['RETURN_DATA'] .= '
													<li class="nav-item dropdown text-center ml-lg-auto">
														<a class="nav-link font-weight-bold bg-warning rounded dropdown-toggle px-2" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														<i class="fa fa-user mr-2"></i>{{userFName}}
														</a>
														<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
														<a class="dropdown-item" href="#" data-ng-click="logout()">Sign Out</a>
														</div>
													</li>
													</ul>
													</div>
													</nav>
														<script>
															$(".dropdown-menu a.dropdown-toggle").on("click", function(e) {
															if (!$(this).next().hasClass("show")) {
															$(this).parents(".dropdown-menu").first().find(".show").removeClass("show");
															}
															var $subMenu = $(this).next(".dropdown-menu");
															$subMenu.toggleClass("show");
														
														
															$(this).parents("li.nav-item.dropdown.show").on("hidden.bs.dropdown", function(e) {
															$(".dropdown-submenu .show").removeClass("show");
															});
														
														
															return false;
														});
														</script>';
					}
					$data['finalData'] = $UnderMenuData;
					$data['data']['MenuData'] = $UnderMenuData['RETURN_DATA'];
					

					$data['success'] = true;
				}

				//################################
				//############## GET_MENU END
				//################################

				echo json_encode($data);exit;
			}
		}
		$data['session'] = false;
		$data['success'] = false;
		$data['message'] = 'Login Failed';
		echo json_encode($data);exit;
	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

function getBrandLogo($conn){
	try{
		$data = array();
		global $userid,$login_role;
		$data['$userid']=$userid;
		$data['$login_role']=$login_role;
		
		$data['data'] = array('LOGO'=>'../images/logo.png','LOGO_DESC'=>'');
		if($userid>0){
			if($login_role!='SUPERADMIN'){
				// $query = "SELECT B.LOGO,B.LOGO_DESC FROM BRANDS B,REGISTRATIONS R WHERE B.BRANDID=R.BRANDID AND R.REGID=$userid";
				// $count = unique($query);
				// if($count>0){
				// 	$stmt = sqlsrv_query( $conn, $query );
				// 	$row = sqlsrv_fetch_array( $stmt,SQLSRV_FETCH_ASSOC);
				// 	$row['LOGO'] = '../backoffice/images/brand/'.$row['LOGO'];
				// 	$data['data'] = $row;
				// }
			}
		}
		echo json_encode($data);exit();
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);exit();
	}
}

function getUnderMenuId($MID){
	global $all_data,$dataMenuID;
	$dataMenuID=array();

	$filteredObjects = array_filter($all_data, function($object) use($MID) {
		return $object['MENUID'] == $MID;
	});
	// if($filteredObjects)
	$val = array_values($filteredObjects);
	$UNDER_MENUID = $val[0]['UNDER_MENUID'];
	if($UNDER_MENUID>0){
		getUnderMenuId($UNDER_MENUID);
	}
	$dataMenuID[] = $val[0];

	return $dataMenuID;
}


// function flattenArray($array) {
//     $result = [];
//     foreach ($array as $value) {
//         if (is_array($value)) {
//             $result = array_merge($result, $value);
//         } else {
//             $result[] = $value;
//         }
//     }
//     return $result;
// }
function reindexingObject($myObject){
	$flattenedData = [];
	// Loop through each array
	foreach ($myObject as $array) {
		// Flatten each array recursively
		$flattenedData = array_merge($flattenedData, $array);
	}
	// return $flattenedData;
	$flattenedData = array_map("unserialize", array_unique(array_map("serialize", $flattenedData)));

	usort($flattenedData, function($a, $b) {
        if ($a['UNDER_SEQNO'] != $b['UNDER_SEQNO']) {
            return $a['UNDER_SEQNO'] <=> $b['UNDER_SEQNO'];
        }
        if ($a['SEQNO'] != $b['SEQNO']) {
            return $a['SEQNO'] <=> $b['SEQNO'];
        }
        if ($a['UNDER'] != $b['UNDER']) {
            return strcmp($a['UNDER'], $b['UNDER']);
        }
        return strcmp($a['MENU'], $b['MENU']);
    });

	return $flattenedData;
}

// ========== UNDER MENU ==========
$index=0;
$index_L1=0;
$index_L2=0;
$index_L3=0;
$RETURN_DATA='';
$data=array();
function getUnderMenu($mysqli,$MENUID){
	global $RETURN_DATA,$selected_all_data,$index,$index_L1,$index_L2,$index_L3,$data;

	$navUpper = '<nav class="navbar navbar-expand-lg navbar-light bg-light py-0">
					<a class="navbar-brand py-0" href="dashboard.html">
					
					</a>
					<button class="navbar-toggler my-2" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse px-2" style="font-size: 14px;" id="navbarTogglerDemo01">
						<ul class="navbar-nav me-auto mb-2 mb-lg-0 text-left d-flex flex-wrap w-100">
							<li class="nav-item" ng-hide="USER_LOCATION.indexOf(\'HQ\')<0">
							<a class="nav-link active border border-secondary rounded text-center" aria-current="page" href="dashboard.html">ADMIN ZONE</a>
							</li>';
	
	$filteredObjects = array_filter($selected_all_data, function($object) use($MENUID) {
							return $object['UNDER_MENUID'] == $MENUID;
						});
	
	$count = count($filteredObjects);
	if($count > 0){
		$data['$filteredObjects'][] = $filteredObjects;

		$RETURN_DATA .=(!isset($index) || $index == 0 ? $navUpper : "");
		foreach($filteredObjects as $rows){
			$MENU = $rows['MENU'];
			$ISHEADER = $rows['ISHEADER'];
			$HEADER = $rows['HEADER'];
			$UNDER_MENUID = $rows['UNDER_MENUID'];
			$PAGE_LINK = $rows['PAGE_LINK'];
			$UNDER = $rows['UNDER'];
			$NEXT_MENU_EXIST = $rows['NEXT_MENU_EXIST'];
			$SETHEIGHT = $rows['SETHEIGHT'];

			if($UNDER_MENUID==0){
				// if($index_L1>0) $RETURN_DATA.="</ul></li>";
				$index_L1++;
				$data['$index_L1'][]=$index_L1;
				$menuHeight = $SETHEIGHT==1?'dropdownHeight':'';
				$RETURN_DATA.="<li class='nav-item dropdown'>
								<a class='nav-link px-4 px-md-2 dropdown-toggle' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
								$MENU
								</a>
								<ul class='dropdown-menu dropdown-menu-right $menuHeight' id='multi-menu' aria-labelledby='navbarDropdownMenuLink'>";
			}else{
				if($NEXT_MENU_EXIST==1){
					if($index_L2>0 && $NEXT_MENU_EXIST==0) $RETURN_DATA.="</ul></li>";
					$index_L2++;
					$data['$index_L2'][]=$index_L2;
					if($ISHEADER==1){
						$DIVIDER_CLASS = (!empty($HEADER) && $HEADER!='' && $HEADER!='-') ? 'mb-0' : '';
						$HEADER_SHOW = ($ISHEADER==1 && !empty($HEADER) && $HEADER!='' && $HEADER!='-') ? true : false;
						$RETURN_DATA.="<div class='mx-2'>
											<div class='dropdown-divider $DIVIDER_CLASS' style='border-color: #85858563 !important;'></div>
										</div>
										<h6  ng-if='$HEADER_SHOW' class='dropdown-header px-4 p-2 font-16' style='font-weight: 700;color:#070707'>$HEADER</h6>
									";
					}

					$RETURN_DATA.="<li class='dropdown-submenu'>
									<a class='dropdown-item dropdown-toggle' href='#'>$MENU</a>
									<ul class='dropdown-menu'>";
				}else{
					// if($index_L3>0) $RETURN_DATA.="</div>";
					$index_L3++;
					$data['$index_L3'][]=$index_L3;
					if($ISHEADER==1){
						$DIVIDER_CLASS = (!empty($HEADER) && $HEADER!='' && $HEADER!='-') ? 'mb-0' : '';
						$HEADER_SHOW = ($ISHEADER==1 && !empty($HEADER) && $HEADER!='' && $HEADER!='-') ? true : false;
						$RETURN_DATA.="<div class='mx-2'>
											<div class='dropdown-divider $DIVIDER_CLASS' style='border-color: #85858563 !important;'></div>
										</div>
										<h6  ng-if='$HEADER_SHOW' class='dropdown-header p-2 px-4 font-16' style='font-weight: 700;color:#070707'>$HEADER</h6>
									";
					}
					$RETURN_DATA.="<li><a class='dropdown-item text-wrap' href='$PAGE_LINK'>$MENU</a></li>";
				}
			}
			$index++;
			if($NEXT_MENU_EXIST==1){
				getUnderMenu($mysqli,$rows['MENUID']);
			}
		}
		$RETURN_DATA.="</ul></li>";
	}else{
		$RETURN_DATA.="";
	}
	
	return ['RETURN_DATA'=>$RETURN_DATA,'data'=>$data];
}





function logout($conn){
	try{
	    $data = array();
        
        session_unset();
        

		$data['success'] = true;
        $data['message'] = 'Logout successfully';
		echo json_encode($data);exit;

	
	}catch (Exception $e){
		$data = array();
		$data['success'] = false;
		$data['message'] = $e->getMessage();
		echo json_encode($data);
		exit;
	}
}

	
	


function invalidRequest()
{
	$data = array();
	$data['success'] = false;
	$data['message'] = "Invalid request.";
	echo json_encode($data);
	exit;
}





