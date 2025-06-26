$postModule = angular.module("myApp", ["ngSanitize"]);
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page="PAYMENT";
    $scope.PageSub="SHPR";
    $scope.formTitle = '';
    $scope.ADMIN = false;
    $scope.PAGEFOR = 'ST';
    
    var url = '../backoffice/code/HourlyStudentPaymentReport.php';



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
                $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];
                // window.location.assign("dashboard.html");
                $scope.temp.ddlPaidDue = 'DUE';
                if($scope.LOC_ID>0) $scope.getSummaryReport();
                if($scope.LOC_ID>0) $scope.getDetailReport();
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


    /* ========== Logout =========== */
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
                    window.location.assign('dashboard.html#!/dashboard');
                }
            },
            function (data, status, headers, config) {
                console.log('Not login Failed');
            })
    }



    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});