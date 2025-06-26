
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
    $scope.Page = "REPORTS";
    $scope.PageSub = "ST_ATT_PAY_RPT2";
    $scope.dt = new Date().toLocaleDateString('es-US');
    $scope.temp.txtFromDT='';
    $scope.temp.txtToDT='';
    $scope.ToDT =$scope.dt;
    $scope.FromDT = new Date();
    $scope.FromDT.setDate($scope.FromDT.getDate() - 60);
    $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
    $scope.PLANS_model = [];
    $scope.STUDENTS_model = [];

    $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    // $scope.FromDT =$scope.dt;
    // $scope.ToDT = new Date();
    // $scope.ToDT.setDate($scope.ToDT.getDate() + 60);
    // $scope.ToDT=new Date($scope.ToDT).toLocaleDateString('es-US');

    $scope.daysInWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    var url = 'code/Student_Attendance_Payment_Report_P2.php';


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

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
                    $scope.getPlans();
                    $scope.getReport();
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



     /* ========== GET REPORT =========== */
     $scope.post.GET_REPORT_VIEW = [];
     $scope.getReport = function () {
        var REGIDS = $scope.STUDENTS_model.map(s=>s.id);
        if(!$scope.temp.txtFromDT || $scope.temp.txtFromDT=='' || !$scope.temp.txtToDT || $scope.temp.txtToDT=='') return;
        $('.btnGet').attr('disabled','disabled').text('Get...');
        $('.spinReport').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport',
            'txtFromDT': (!$scope.temp.txtFromDT || $scope.temp.txtFromDT == '') ? '' : $scope.temp.txtFromDT.toLocaleDateString('sv-SE'),
            'txtToDT': (!$scope.temp.txtToDT || $scope.temp.txtToDT == '') ? '' :$scope.temp.txtToDT.toLocaleDateString('sv-SE'),
            'REGIDS': REGIDS,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.weeks = data.data.success?data.data.WEEKS:[];

        $scope.post.GET_REPORT_VIEW = data.data.success?data.data.data:[];
        // $timeout(()=>{$scope.filterData()},1000);

        if(!data.data.success) $scope.messageFailure(data.data.message);

        $('.spinReport').hide();
        $('.btnGet').removeAttr('disabled').text('Get');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
    }
    // $scope.getReport(); --INIT
    /* ========== GET REPORT =========== */


    $scope.addRemoveStudents=function(){
        $scope.STUDENT_LIST = $scope.STUDENTS_model;
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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            // if($scope.temp.ddlLocation > 0) $scope.getInventories();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: 'code/Student_SMS.php',
            data: $.param({ 'type': 'getPlans'}),
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


    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
    $scope.getStudentByPlanProduct = function () {
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct=[];
        $scope.post.weeks = [];
        $scope.post.GET_REPORT_VIEW = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinStudent').show();
        // $FINAL_PRODUCTID = [];
        // $FINAL_PRODUCTID = $scope.PRODUCTS_model.map(x=>x.id);
        $FINAL_PLANID = [];
        $FINAL_PLANID = $scope.PLANS_model.map(x=>x.id);
        $http({
            method: 'post',
            url: url,
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




    
    
    /*============ CHANGE PRINT DATE =============*/ 
    $scope.changePrintDate=()=>{
        if($scope.temp.txtFromDT && $scope.temp.txtFromDT!=''){
            $scope.FromDT = new Date($scope.temp.txtFromDT).toLocaleDateString('es-US');
        }else{
            $scope.FromDT = new Date();
            $scope.FromDT.setDate($scope.FromDT.getDate() - 60);
            $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
        }
        
        if($scope.temp.txtToDT && $scope.temp.txtToDT!=''){
            $scope.ToDT = new Date($scope.temp.txtToDT).toLocaleDateString('es-US');
        }
        else{
            $scope.ToDT = new Date().toLocaleDateString('es-US');
            // $scope.ToDT = new Date();
            // $scope.ToDT.setDate($scope.ToDT.getDate() + 60);
            // $scope.ToDT=new Date($scope.ToDT).toLocaleDateString('es-US');
        }
    }
    $scope.clearDate=function(){
        $scope.temp.txtFromDT='';$scope.temp.txtToDT='';
        $scope.ToDT = new Date().toLocaleDateString('es-US');
        $scope.FromDT = new Date();
        $scope.FromDT.setDate($scope.FromDT.getDate() - 60);
        $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
        $scope.getReport();
    }
    /*============ CHANGE PRINT DATE =============*/ 
    


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




});