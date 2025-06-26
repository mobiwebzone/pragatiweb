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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "REPORTS";
    $scope.PageSub = "ATTENDANCE";
    $scope.PageSub1 = "STUDENT_ATT";
    $scope.temp.txtAttDT = new Date();

    
    var url = 'code/Student_Attendance_Report_code.php';


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */



    /* =========== CHECK SESSION ========== */
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
                $scope.LOCID=data.data.locid;

                
                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getPlans();
                    $scope.getLocations();
                    $scope.getStudentReport();
                    
                    if(data.data.locid > 0){
                        $scope.temp.ddlLocation = (data.data.locid).toString();
                    }
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
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
    /* =========== CHECK SESSION ========== */





     /* ========== GET STUDENT REPORT =========== */
     $scope.getStudentReport = function () {
         if($scope.temp.txtAttDT != undefined){
             $http({
                 method: 'post',
                 url: url,
                 data: $.param({ 'type': 'getStudentReport',
                                 'ddlPlan' : $scope.temp.ddlPlan,
                                 'txtAttDT' : $scope.dateFormat($scope.temp.txtAttDT),
                             }),
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                //  console.log(data.data);
                 $scope.post.getStudentReport = data.data.data;
                 //  $scope.post.getTeacherReportSummry = data.data.Summry;
                },
             function (data, status, headers, config) {
                 console.log('Failed');
                })
         }
     }
    //  $scope.getStudentReport(); --INIT
     /* ========== GET STUDENT REPORT =========== */
        




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
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */
    




    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $http({
            method: 'post',
            url: 'code/Teacher_Product_code.php',
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTeacher = data.data.data;
                
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacher();
    /* ========== GET Teacher =========== */





    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: 'code/LoginApproval_code.php',
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
    /* ========== GET PLANS =========== */



    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        document.getElementById("ddlLocation").focus();
        $scope.temp={};
        $scope.temp.ddlLocation=($scope.LOCID).toString();
        $scope.getTeacher();
        $scope.temp.txtFromDT = new Date();
        $scope.temp.txtToDT = new Date();

        $scope.getTeacherReport();
    }
    /* ============ Clear Form =========== */ 



    


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
    
    
    

    /* ========== Message =========== */
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
    /* ========== Message =========== */
    



});