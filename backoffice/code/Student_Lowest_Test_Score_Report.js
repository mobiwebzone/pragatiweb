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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "ST_LOWEST_TEST_SC_RPT";
    $scope.dt = new Date().toLocaleString('sv-SE')
    $scope.temp.txtFromDT=new Date();
    // $scope.temp.txtFromDT=new Date('01-12-2021');
    $scope.temp.txtToDT=new Date();
    $scope.PLANS_model = [];
    $scope.STUDENTS_model = [];

    var url = 'code/Student_Lowest_Test_Score_Report_code.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px',scrollableWidth:'200px'};
    $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 

   

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
                $scope.locid = data.data.locid;

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
                    $scope.getPlans();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
                
            }else{

                // window.location.assign('index.html#!/login');
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }



     /* ========== GET Student Test =========== */
     $scope.getStudentTest = function () {
        $('.spinMainData').show();
        $('.btn-get').attr('disabled','disabled');
        $scope.post.getStudentTestScore = '';
        if($scope.temp.txtFromDT != undefined && $scope.temp.txtToDT != undefined && $scope.temp.ddlLocation>0){
            var REGIDS = $scope.STUDENTS_model.map(s=>s.id).toString();
            console.log(REGIDS);
            // return;
            // var PLANIDS = $scope.PLANS_model.map(s=>s.id);
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentTest',
                                'ddlLocation':$scope.temp.ddlLocation,
                                'txtFromDT':$scope.temp.txtFromDT.toLocaleString('sv-SE'),
                                'txtToDT':$scope.temp.txtToDT.toLocaleString('sv-SE'),
                                'REGIDS': REGIDS,
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getStudentTestScore = data.data.StudentTest;
                if(!data.data.success) $scope.messageFailure(data.data.message);
                $('.spinMainData').hide();
                $('.btn-get').removeAttr('disabled');
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getStudentTest(); --INIT
    /* ========== GET Student Test =========== */

    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getPlans_MultiSelect'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.success ? data.data.data : [];
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */

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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            // if($scope.temp.ddlLocation > 0) $scope.getStudentTest();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

     /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
     $scope.getStudentByPlanProduct = function () {
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinStudent').show();
        $FINAL_PLANID = [];
        $FINAL_PLANID = $scope.PLANS_model.map(x=>x.id);
        $http({
            method: 'post',
            url: 'code/Student_Attendance_Payment_Report_P2.php',
            data: $.param({ 'type': 'getStudentByPlanProduct', 
                            'PLANID' : $FINAL_PLANID,
                            'ddlLocation' : $scope.temp.ddlLocation
                            // 'PRODUCTID' : $FINAL_PRODUCTID
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByPlanProduct = data.data.success ? data.data.data : [];
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlanProduct();
    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
    

    
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
    /* ========== Logout =========== */



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