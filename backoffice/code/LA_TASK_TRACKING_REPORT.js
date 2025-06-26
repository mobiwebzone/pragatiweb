$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","angularjs-dropdown-multiselect"]);
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
    $scope.CATEGORY_model = [];
    $scope.ASSIGNEDTO_model = [];
    $scope.editMode = false;
    $scope.Page = "L&A";
    $scope.PageSub = "TSK_MANG";
    $scope.PageSub1 = "TASK_REPORT";
    
    var url = 'code/LA_TASK_TRACKING_REPORT.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
    }


    $scope.Task_Category_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.AssignedTo_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN" && $scope.userrole != "LA_MASTER")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    $scope.getPlans();
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


     /* ========== GET Rpt Task Tracking Report =========== */
     $scope.Get_RPT_TASK_TRACKING = function () {
        // if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        var Category = $scope.CATEGORY_model.length>0 ? $scope.CATEGORY_model.map(x=>x.id).toString() : '';
        var ASSIGNEDTO = $scope.ASSIGNEDTO_model.length>0 ? $scope.ASSIGNEDTO_model.map(x=>x.id).toString() : '';
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'Get_RPT_TASK_TRACKING','ddlLocation':$scope.temp.ddlLocation,'ddlTask_Category':Category
            ,'txtFromDT':$scope.temp.txtFromDT.toLocaleDateString('sv-SE')
            ,'txtToDT':$scope.temp.txtToDT.toLocaleDateString('sv-SE')
            ,'ddlAssignedToID':ASSIGNEDTO,'txtStatus':$scope.temp.txtStatus}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.Get_RPT_TASK_TRACKING = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.Get_RPT_TASK_TRACKING();



    /* ========== GET CATEGORY =========== */
    $scope.getTaskMainCategory = function () {
        $scope.SpinTMC=true;
        $scope.post.getTaskCategory = [];
        $http({
           method: 'post',
           url: masterUrl,
           data: $.param({ 'type': 'getTaskMainCategory','LOCID':$scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getTaskMainCategory = data.data.success ? data.data.data : [];
           $scope.SpinTMC=false;
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   //  $scope.getTaskMainCategory();



    /* ========== GET SUBCATEGORY =========== */
    $scope.getTaskCategory = function () {
        $scope.CATEGORY_model = [];
        $scope.SpinTSC=true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskCategory','TASKMAINCATID':$scope.temp.ddlTask_MainCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskCategory = data.data.success ? data.data.data : [];
            $scope.SpinTSC=false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTaskCategory();

    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getTaskMainCategory();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


        /* ========== GET Plan =========== */
        $scope.getPlans = function () {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getPlans'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getPlans = data.data.data;
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        // $scope.getPlans(); --INIT


   

    /* ========== GET AssignedToUser =========== */
    $scope.getAssignedToUser = function () {
        $scope.ASSIGNEDTO_model=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAssignedToUser','ddlPlan':$scope.temp.ddlPlan,'txtAssignedTo':$scope.temp.txtAssignedTo,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAssignedToUser = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAssignedToUser(); --INIT
    /* ========== GET AssignedToUser =========== */





    $scope.clear = function(){
        $scope.temp={};
        $scope.post.getTaskCategory = [];
        $scope.CATEGORY_model = [];
        $scope.ASSIGNEDTO_model = [];
        $scope.post.getAssignedToUser = [];
        $scope.post.Get_RPT_TASK_TRACKING  = [];
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