$postModule = angular.module("myApp", ["angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.PageSub = "ST_COURSE_COVERAGE_RPT";
    $scope.dt = new Date().toLocaleString('sv-SE');
    $scope.temp.txtFromDT=new Date();
    $scope.temp.txtToDT=new Date();

    var url = 'code/Student_Course_Coverage_Report_code.php';


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */
    
    
    
    
    /* =============== CHECK SESSION ============== */
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
                    // $scope.getStudentReport();
                    // $scope.getInventory();
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
    /* =============== CHECK SESSION ============== */





    /* ========== GET STUDENT REPORT =========== */
    $scope.temp.txtASC_DESC = 'ASC';
    $scope.ShowST_RPT = false;
    $scope.getStudentReport = function () {
        $('.spinMainData').show();
        $('.btn-get').attr('disabled','disabled');
        $scope.post.getStudentReport = '';
        $scope.ShowST_RPT = false;
        if($scope.temp.txtFromDT != undefined && $scope.temp.txtToDT != undefined){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 
                                'type': 'getStudentReport',
                                'txtFromDT':$scope.temp.txtFromDT.toLocaleString('sv-SE'),
                                'txtToDT':$scope.temp.txtToDT.toLocaleString('sv-SE'),
                                'ddlPlan':$scope.temp.ddlPlan,
                                'ddlProduct':$scope.temp.ddlProduct,
                                'ddlInventory':$scope.temp.ddlInventory,
                                'ddlStudent':$scope.temp.ddlStudent,
                                'txtOrderby':$scope.temp.txtOrderby,
                                'txtASC_DESC':$scope.temp.txtASC_DESC,
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getStudentReport = data.data.StudentData;
                    $scope.ShowST_RPT = true;
                    $scope.DataNotFound = '';
                }else{
                    $scope.post.getStudentReport = '';
                    $scope.ShowST_RPT = false;
                    $scope.DataNotFound = 'Data Not Found.';
                }

                $('.spinMainData').hide();
                $('.btn-get').removeAttr('disabled');
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getStudentReport(); --INIT
    /* ========== GET STUDENT REPORT =========== */


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.data;
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */




    
    /* ========== GET PRODUCTS BY PLANID =========== */
    $scope.getProductByPlanID = function () {
        $('.spinPlanProduct').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlanProducts','planid':$scope.temp.ddlPlan}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getPlanProduct = data.data.data;
            }else{
                $scope.post.getPlanProduct = [];
            }
            $('.spinPlanProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlanProducts();
    /* ========== GET PRODUCTS BY PLANID =========== */





    /* ========== GET INVENTORY =========== */
    $scope.getInventory = function () {
        $('.SpinINV').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'getInventories','ddlProduct':$scope.temp.ddlProduct}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           if(data.data.success){
               $scope.post.getInventory = data.data.data;
           }else{
               $scope.post.getInventory = [];
           }
           $('.SpinINV').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getInventory();
   /* ========== GET INVENTORY =========== */





    /* ========== GET STUDENTS =========== */
    $scope.getStudents = function () {
        $('.SpinStudent').show();
        if(!$scope.temp.ddlPlan && !$scope.temp.ddlProduct && !$scope.temp.ddlInventory) return
        $http({
            method: 'post',
           url: url,
           data: $.param({ 
                            'type': 'getStudents',
                            'ddlPlan':$scope.temp.ddlPlan,
                            'ddlProduct':$scope.temp.ddlProduct,
                            'ddlInventory':$scope.temp.ddlInventory
                        }),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
           if(data.data.success){
               $scope.post.getStudents = data.data.data;
           }else{
               $scope.post.getStudents = [];
           }
           $('.SpinStudent').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getStudents();
   /* ========== GET STUDENTS =========== */
    



    
    /* ========== LOGOUT =========== */
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
    /* ========== LOGOUT =========== */






    /* =============== ALERT MESSAGE ============== */
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
    /* =============== ALERT MESSAGE ============== */




});