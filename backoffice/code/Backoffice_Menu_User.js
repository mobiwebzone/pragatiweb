$postModule = angular.module("myApp", ["angularjs-dropdown-multiselect","angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.PageSub1 = "BO_MENU_USER";
    $scope.SELECTED_LOC = [];
    $scope.USERS_model = [];

    $scope.USERS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    
    var url = 'code/Backoffice_Menu_User.php';
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
                $scope.IS_ET = data.data.IS_ET;
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
                formData.append("UID", $scope.temp.ddlUser);
                formData.append("MENUID", id.MENUID);
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
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $("#loc"+idx).attr('disabled', false);
        });
    }


    /* ========== COPY MENU DATA =========== */
    $scope.copyMenu = function(){
        var COPY_USERS = $scope.USERS_model.map(x=>x.id).toString();
        if(!COPY_USERS || COPY_USERS.length==0) return;

        $(".btn-copy").text('Wait...').attr('disabled', true);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'copyMenu');
                formData.append("LOCID", $scope.temp.ddlLocation);
                formData.append("USERID", $scope.temp.ddlUser);
                formData.append("USERID_COPY", COPY_USERS);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.USERS_model = [];
                // $scope.post.getUsersByLocationCopy = [];
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
             data: $.param({ 'type': 'getMenusData','LOCID':$scope.temp.ddlLocation,'USERID':$scope.temp.ddlUser}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMenusData = data.data.success ? data.data.data : [];
            $scope.spinMenu = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMenusData(); 


    /* ========== GET MENU DATA =========== */
    $scope.getMenuForLocation = function () {
         $scope.spinMenu = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getMenuForLocation'}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMenuForLocation = data.data.success ? data.data.data : [];
            $scope.spinMenu = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMenuForLocation(); 

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
            if(!$scope.IS_ET || $scope.IS_ET==0){
                $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
                if($scope.temp.ddlLocation > 0) $scope.getUsersByLocation();
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    /* ========== GET USER BY LOCATIONS =========== */
    $scope.getUsersByLocation = function () {
        $scope.spinUser = true;
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getUsersByLocation','LOCID':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.getUserData = data.data.success ? data.data.data : [];
            if($scope.getUserData.length>0){
                // $scope.post.getUsersByLocation = $scope.getUserData.filter(x=>x.USERROLE!=='ADMINISTRATOR' && x.USERROLE !== 'SUPERADMIN');
                $scope.post.getUsersByLocation = $scope.getUserData;
            }
           $scope.spinUser = false;
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getUsersByLocation();

    $scope.getCopyUsersData = function(){
        $scope.USERS_model = [];
        $scope.post.getUsersByLocationCopy = [];
        if(!$scope.temp.ddlUser || $scope.temp.ddlUser=='') return;
        $scope.spinUserC =true;
        $scope.post.getUsersByLocationCopy = $scope.post.getUsersByLocation.filter(x=>x.UID!=$scope.temp.ddlUser).map(x=>({ 'id': x.UID, 'label': `${x.FIRSTNAME} ${x.LASTNAME} ---- (${x.USERROLE})`}));
        // console.log($scope.post.getUsersByLocationCopy);
        $scope.spinUserC =false;
    }


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