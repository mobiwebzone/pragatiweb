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
    $scope.PageSub1 = "TEACHER_ATT";
    $scope.temp.txtFromDT = new Date();
    $scope.temp.txtToDT = new Date();

   

    // $scope.temp.txtForYear = Number(new Date().getFullYear());
    
    var url = 'code/Teacher_Attendance_Report_code.php';


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
                $scope.LOCID=data.data.locid;

                if($scope.userrole != "TSEC_USER")
                {
                    
                    if(data.data.locid > 0){
                        $scope.temp.ddlLocation = (data.data.locid).toString();
                        $scope.getTeacher();
                        $scope.getTeacher_Volunteer();    
                        
                    }
                    $scope.getLocations();
                    $scope.getTeacherReport();
                   
                    
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


     /* ========== GET TEACHER REPORT =========== */
     $scope.getTeacherReport = function () {
         $('#SpinMainData').show();
         $scope.post.getTeacherReport=[];
         $scope.post.getTeacherReportSummry=[];
         if($scope.temp.txtFromDT != undefined && $scope.temp.txtToDT != undefined){
             $http({
                 method: 'post',
                 url: url,
                 data: $.param({ 'type': 'getTeacherReport',
                                 'ddlLocation' : $scope.temp.ddlLocation,
                                 'ddlTeacher' : $scope.temp.ddlTeacher,
                                 'txtFromDT' : $scope.dateFormat($scope.temp.txtFromDT),
                                 'txtToDT' : $scope.dateFormat($scope.temp.txtToDT)
                             }),
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
             }).
             then(function (data, status, headers, config) {
                 console.log(data.data);
                 $scope.post.getTeacherReport = data.data.data;
                 $scope.post.getTeacherReportSummry = data.data.FINAL_DATA;

                 //  Total
                 $scope.TOTAL_NOH = data.data.TOTAL_NOH;
                 $scope.TOTAL_NOH_DECIMAL = data.data.TOTAL_NOH_DECIMAL;
                 $scope.TOTAL_NOH_SUM = data.data.TOTAL_NOH_SUM;
                 $scope.TOTAL_NOH_DECIMAL_SUM = data.data.TOTAL_NOH_DECIMAL_SUM;
                 $('#SpinMainData').hide();
                },
                function (data, status, headers, config) {
                 console.log('Failed');
             })
         }
    }
    // $scope.getTeacherReport(); --INIT


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
                $scope.post.getTeacherVolunteerList = angular.copy($scope.post.getTeacher);
                
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    
  /* ========== GET Teacher-Volunteer =========== */
    $scope.getTeacher_Volunteer = function () {
        $http({
            method: 'post',
            url: 'code/Teacher_Product_code.php',
            data: $.param({ 'type': 'getTeacher_Volunteer','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTeacher_Volunteer = data.data.data;
                
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    
    $scope.filterTeacherList = function () { 
        if (!$scope.temp.ddlTeacherVolunteer || $scope.temp.ddlTeacherVolunteer == '') {
            $scope.post.getTeacherVolunteerList = angular.copy($scope.post.getTeacher);
        }
        else { 
            $scope.post.getTeacherVolunteerList = $scope.post.getTeacher.filter(x=>x.USERROLE==$scope.temp.ddlTeacherVolunteer);
        }
    }

    

    
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