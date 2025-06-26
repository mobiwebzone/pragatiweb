$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$filter) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = "L&A";
    $scope.PageSub = "HourlyTutoring";
    $scope.PageSub1 = "ST_HOURLY_PAY_RPT";
    $scope.date = new Date();
    $scope.temp.txtPaymentDate=new Date();
    $scope.studentPay=[];
    $scope.PAGEFOR = 'ADMIN';
    
    var url = 'code/HourlyStudentPaymentReport.php';
    var Masterurl = 'code/MASTER_API.php';


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
                $scope.LOC_ID=data.data.locid;
                // alert(data.data.userrole);
                // window.location.assign("dashboard.html");
                
                if($scope.userrole != "TSEC_USER")
                {
                    // if(data.data.locid > 0){
                    // }
                    $scope.getLocations();
                    $scope.temp.ddlPaidDue = 'DUE';
                    // $scope.getClassSubjectMaster();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
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
                            'ddlLocation':$scope.temp.ddlLocation,
                            'userid':0,
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
                            'ddlLocation':$scope.temp.ddlLocation,
                            'userid':0,
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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.LOC_ID.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getSummaryReport();
            if($scope.temp.ddlLocation > 0) $scope.getDetailReport();
            // if($scope.temp.ddlLocation > 0) $scope.getAtt();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    


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
                window.location.assign('index.html#!/login');
            }
            else {
                window.location.assign('dashboard.html#!/dashboard');
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



});