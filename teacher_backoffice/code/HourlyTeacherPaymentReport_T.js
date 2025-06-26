$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "HOURLY";
    $scope.PageSub = "HTPR";
    $scope.PAGEFOR = 'TEACHER';

    var url = '../backoffice/code/HourlyTeacherPaymentReport.php';
    // var Masterurl = '../backoffice/code/MASTER_API.php';





    /* ========== CHECK SESSION =========== */
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
                $scope.LOC_ID=data.data.locid;
                $scope.temp.ddlPaidDue = 'DUE';
                if($scope.LOC_ID>0)$scope.getSummaryReport();
                if($scope.LOC_ID>0)$scope.getDetailReport();
            }
            else {
                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ========== CHECK SESSION =========== */


    $scope.printTable = function(FOR){
        if(FOR == 'SUM'){
            $('#SUMMARY_RPT').removeClass('col-lg-6');
            $('#sumHead').removeClass('text-white').addClass('text-dark');
            $('#DETAIL_RPT').hide();
            window.print();
            $timeout(()=>{
                $('#SUMMARY_RPT').addClass('col-lg-6');
                $('#sumHead').removeClass('text-dark').addClass('text-white');
                $('#DETAIL_RPT').show();
            },300);
        }else{
            $('#DETAIL_RPT').removeClass('col-lg-6');
            $('#detHead').removeClass('text-white').addClass('text-dark');
            $('#SUMMARY_RPT').hide();
            window.print();
            $timeout(()=>{
                $('#DETAIL_RPT').addClass('col-lg-6');
                $('#detHead').removeClass('text-dark').addClass('text-white');
                $('#SUMMARY_RPT').show();
            },300);
        }
    }



    
    /* ========== GET SUMMARY REPORT =========== */
    $scope.getSummaryReport = function () {
        // alert($scope.temp.ddlPaidDue);
        $scope.spinSum = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport',
                            'ddlLocation':$scope.LOC_ID,
                            'userid':$scope.userid,
                            'ddlPaidDue':$scope.temp.ddlPaidDue,
                            'sum_det':0
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSummaryReport=data.data.success ? data.data.data : [];
            $scope.TOTALS_SUM=data.data.success ? data.data.TOTALS : [];
            $scope.spinSum = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    /* ========== GET DETAIL REPORT =========== */
    $scope.getDetailReport = function () {
        $scope.spinDet = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport',
                            'ddlLocation':$scope.LOC_ID,
                            'userid':$scope.userid,
                            'ddlPaidDue':$scope.temp.ddlPaidDue,
                            'sum_det':1
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDetailReport=data.data.success ? data.data.data : [];
            $scope.TOTALS_DET=data.data.success ? data.data.TOTALS : [];
            $scope.spinDet = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }


    $scope.logout = function () {
        $http({
            method: 'post',
            url: '../student_zone/code/logout.php',
            data: $.param({ 'type': 'logout' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    window.location.assign('login.html#!/login');
                }
                else {
                    //window.location.assign('backoffice/index#!/')
                }
            },
            function (data, status, headers, config) {
                console.log('logout Failed');
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