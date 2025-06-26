$postModule = angular.module("myApp", ["ngSanitize"]);
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
    $scope.PageSub = "ST_HW_STATUS";
    $scope.temp.txtFromDT = new Date();
    $scope.temp.txtToDT = new Date();
    
    
    var url = 'code/Student_Homework_Status_Report.php';


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
                    $scope.getLocations();
                    $scope.getPlans();
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





    /* ========== GET STUDENT PAYMENT =========== */
    $scope.getReport = function () {
    $scope.REPORT_HEAD = `${$("#ddlPlan option:selected").text()} (${$scope.temp.txtFromDT.toLocaleDateString('en-GB')} - ${$scope.temp.txtToDT.toLocaleDateString('en-GB')})`;
    $scope.post.getStudentPayment=[];
    if(!$scope.temp.txtFromDT || $scope.temp.txtFromDT=='' || !$scope.temp.txtToDT || $scope.temp.txtToDT=='' || !$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlPlan || $scope.temp.ddlPlan<=0) return;
    $('#SpinMainData').show();
    $('.btn-clear').attr('disabled','disabled');
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getReport',
                        'txtFromDT' : $scope.temp.txtFromDT.toLocaleDateString('sv-SE'),
                        'txtToDT' : $scope.temp.txtToDT.toLocaleDateString('sv-SE'),
                        'ddlPlan' : $scope.temp.ddlPlan,
                        // 'ddlStudent' : $scope.temp.ddlStudent,
                        'ddlLocation' : $scope.temp.ddlLocation,
                    }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
    //  console.log(data.data);
        $scope.post.getReport = data.data.success ? data.data.data : [];
        $('#SpinMainData').hide();
        $('.btn-clear').removeAttr('disabled');
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
        
    }
    //  $scope.getReport();
    /* ========== GET STUDENT PAYMENT =========== */


    
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
            if($scope.temp.ddlLocation > 0) $scope.getStudentPayment();
            if($scope.temp.ddlLocation > 0) $scope.getStudents();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */





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



    /* ========== GET STUDENTS =========== */
    $scope.getStudents = function () {
        $scope.post.getStudents=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.SpinStudent').show();
        $http({
            method: 'post',
        //    url: 'code/Student_Course_Pending_Report.php',
           url: url,
           data: $.param({ 
                            'type': 'getStudents',
                            'ddlPlan':$scope.temp.ddlPlan,
                            'ddlLocation':$scope.temp.ddlLocation
                        }),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getStudents = data.data.success ? data.data.data : [];

           $('.SpinStudent').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
//    $scope.getStudents();
   /* ========== GET STUDENTS =========== */



    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp={};
        $scope.temp.txtFromDT = new Date();
        $scope.temp.txtToDT = new Date();
        $scope.post.getReport=[];
        // $scope.getStudentPayment();
        $scope.getLocations();
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