
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page="REPORTS";
    $scope.PageSub="CW_HW_REPORT";
    $scope.temp.txtFromDT = new Date();
    $scope.temp.txtToDT = new Date();
    $scope.BACKOFFICE=false;
    $scope.PAGEFOR='TEACHER';
    
    var url = '../backoffice/code/ClassW_HomeW_Report_code.php';

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
                $scope.locid=data.data.locid;
                

                if($scope.userid > 0){
                    $scope.getStudentReport();
                    // $scope.getLocations();
                }

                if(data.data.locid > 0){
                    $scope.getTeacher();
                }
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


     /* ========== GET STUDENT REPORT =========== */
     $scope.getStudentReport = function () {
        jQuery('#mySpinner').removeClass('d-none');

       if($scope.temp.txtFromDT != undefined && $scope.temp.txtToDT != undefined && $scope.userid > 0){
           $http({
               method: 'post',
               url: url,
               data: $.param({ 'type': 'getStudentReport',
                               'ddlPlan' : $scope.temp.ddlPlan,
                               'ddlTeacher' : $scope.userid,
                               'txtFromDT' : $scope.dateFormat($scope.temp.txtFromDT),
                               'txtToDT' : $scope.dateFormat($scope.temp.txtToDT),
                               'ddlLocation' : $scope.USER_LOCATION,
                           }),
               headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
           }).
           then(function (data, status, headers, config) {
            // console.log(data.data);
               $scope.post.getStudentReport = data.data.data;
           },
           function (data, status, headers, config) {
               console.log('Failed');
           })
       }
       jQuery('#mySpinner').addClass('d-none');

   }
//    $scope.getStudentReport();





    
    /* ========== GET Location =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: 'code/ClassW_HomeW_Report_code.php',
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
    $scope.getPlans();



    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $scope.post.getTeacher=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacher','ddlPlan':$scope.temp.ddlPlan,'ddlLocation':$scope.locid}),
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

    $scope.clear=function(){
        document.getElementById("ddlPlan").focus();
        $scope.temp={};
        $scope.temp.txtFromDT = new Date();
        $scope.temp.txtToDT = new Date();
        $scope.post.getTeacher=[];
        $scope.post.getStudentReport =[];
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