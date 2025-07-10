$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect","angularUtils.directives.dirPagination", "ngSanitize"]);
$postModule.directive('bindHtmlCompile', ['$compile', function ($compile) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            scope.$watch(function () {
                return scope.$eval(attrs.bindHtmlCompile);
            }, function (value) {
                element.html(value);
                $compile(element.contents())(scope);
            });
        }
    };
}]);
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.editModeLoc = false;
    $scope.Page = "SETTING";
    $scope.PageSub = "BO_MENU";
    $scope.PageSub1 = "BO_MENU_LOC";
    $scope.SELECTED_LOC = [];
    $scope.LOCS_model = [];
    
    $scope.LOCS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};

    var url = 'code/Backoffice_Menu_Assign_User_code.php';
    var masterUrl = 'code/MASTER_API.php';
    
    // GET DATA
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: 'code/checkSession.php',
            data: $.param({ 'type': 'checkSession' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);   
            if (data.data.success) {
                $scope.post.user = data.data.data;
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    // $scope.getMenuForLocation();
                    $scope.getLocations();
                    $scope.getOrgUser();
                }
                // window.location.assign("dashboard.html");
            }
            else {
                // window.location.assign('index.html#!/login')
                // alert
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }


   
    $scope.createCopyToLocations= function(){
        $scope.LOCS_model = [];
        $scope.copytoLocationsData = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation=='') return;
        $scope.spinCLOC = true;
        $scope.copytoLocationsData = $scope.post.getLocations.filter(x=>x.LOC_ID != $scope.temp.ddlLocation).map(x=>({'id':x.LOC_ID,'label':x.LOCATION}));
        // console.log($scope.copytoLocationsData);
        $scope.spinCLOC = false;
    }


    // ##############################################
    //                      MENU
    // ##############################################
    $scope.updatePermission = function(id,val,idx){
        $("#loc"+idx).attr('disabled', 'disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updatePermission');
                formData.append("LOCID", $scope.temp.ddlLocation);
                formData.append("MENUID", id.MENUID);
                formData.append("UID", id.UID);
                formData.append("val", val);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // $scope.getMenusData();
                $scope.selectAllMenu = $scope.SELECTED_LOC.every(x=>x==1);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $("#loc"+idx).attr('disabled', false);
        });
    }
    
    $scope.updatePermissionAll = function(){
        $("#loc").attr('disabled', 'disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updatePermissionAll');
                formData.append("LOCID", $scope.temp.ddlLocation);
                formData.append("val", !$scope.selectAllMenu ? 0 : 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getMenusData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $("#loc").attr('disabled', false);
        });
    }


    /* ========== COPY MENU DATA =========== */
    $scope.copyMenu = function(){
        var COPY_LOCS = $scope.LOCS_model.map(x=>x.id).toString();
        if(!COPY_LOCS || COPY_LOCS.length==0) return;
        $(".btn-copy").text('Wait...').attr('disabled', true);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'copyMenu');
                formData.append("LOCID", $scope.temp.ddlLocation);
                formData.append("LOCID_COPY", COPY_LOCS);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.LOCS_model = [];
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-copy").text('Copy').attr('disabled', false);
        });
    }


    /* ========== GET MENU DATA =========== */
    $scope.getMenusData = function () {
         $scope.spinMenu = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getMenusData',
                'LOCID':$scope.temp.ddlLocation,
                'UID':$scope.temp.TEXT_USER_ID,
                 }),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMenusData = data.data.success ? data.data.data : [];
            $scope.spinMenu = false;
            if($scope.post.getMenusData.length>0){
                // console.log($scope.post.getMenusData.every(x=>x.ACTIVE==1));
                $scope.selectAllMenu = $scope.post.getMenusData.every(x=>x.ACTIVE==1);
            }else{
                $scope.selectAllMenu = false;
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMenusData(); 


    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.spinLoc = true;
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.spinLoc = false;
            // $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            // if($scope.temp.ddlLocation > 0) $scope.getMeetingLinks();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


$scope.getOrgUser = function () {
    $scope.post.getOrgUser = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        
          
        type: "getOrgUser",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getOrgUser = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
};
$scope.getOrgUser();


    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: 'code/logout.php',
            data: $.param({ 'type': 'logout' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                window.location.assign('index.html#!/login')
            }
            else {
                //window.location.assign('backoffice/index#!/')
            }
        },
        function (data, status, headers, config) {
            console.log('Not login Failed');
        })
    }



    /* ========== MESSAGE =========== */
    $scope.messageSuccess = function (msg) {
        jQuery('#myToast > .toast-body > samp').html(msg);
        jQuery('#myToast').toast('show');
        jQuery('#myToast > .toast-header').removeClass('bg-danger').addClass('bg-success');
        jQuery('#myToastMain').animate({bottom: '50px'});
        setTimeout(function() {
            jQuery('#myToastMain').animate({bottom: "-80px"});
        }, 5000 );
    }

    $scope.messageFailure = function (msg) {
        jQuery('#myToast > .toast-body > samp').html(msg);
        jQuery('#myToast').toast('show');
        jQuery('#myToast > .toast-header').removeClass('bg-success').addClass('bg-danger');
        jQuery('#myToastMain').animate({bottom: '50px'});
        setTimeout(function() {
            jQuery('#myToastMain').animate({bottom: "-80px"});
        }, 5000 );
    }
    /* ========== MESSAGE =========== */


    $scope.eyepass = function(For,index) {
        if(For == 'MPASS'){
            var input = $("#txtMeetingPasscode");
        }else if(For == 'EPASS'){
            var input = $("#txtEmailPassword");
        }
        
        // var input = $("#txtMeetingPasscode");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon'+index).removeClass('fa-eye');
            $('.Eyeicon'+index).addClass('fa-eye-slash');
        }else{
            $('.Eyeicon'+index).removeClass('fa-eye-slash');
            $('.Eyeicon'+index).addClass('fa-eye');
        }
    };

});