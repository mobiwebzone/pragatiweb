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
    $scope.PageSub = "ST_CLG_PROC_RPT";
    $scope.temp.txtFromDT = new Date();
    $scope.temp.txtToDT = new Date();
    $scope.chkStudent = [];
    $scope.selectedStudentsArr = [];
    
    
    var url = 'code/Student_College_Process_Report.php';


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
                    $scope.getSTCollegeProcess();
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





    /* ========== GET STUDENT COLLEGE PROCESS =========== */
    $scope.getSTCollegeProcess = function () {
    $('#SpinMainData').show();
    $('.btn-clear').attr('disabled','disabled');
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getSTCollegeProcess',
                        'ddlStudent' : $scope.temp.ddlStudent,
                    }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getSTCollegeProcess = data.data.success ? data.data.data : [];
        $('#SpinMainData').hide();
        $('.btn-clear').removeAttr('disabled');
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
        
    }
    //  $scope.getSTCollegeProcess();
    /* ========== GET STUDENT COLLEGE PROCESS =========== */


    

    
    
   




    /* ========== GET PLANS =========== */
    // $scope.getPlans = function () {
    //     $('.spinPlan').show();
    //     $http({
    //         method: 'post',
    //         url: 'code/SellingPlans_code.php',
    //         data: $.param({ 'type': 'getPlans'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getPlan = data.data.data;
    //         $('.spinPlan').hide();
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */



    /* ========== GET STUDENTS =========== */
    $scope.getStudents = function () {
        $('.SpinStudent').show();
        $http({
            method: 'post',
        //    url: 'code/Student_Course_Pending_Report.php',
           url: url,
           data: $.param({ 
                            'type': 'getStudents'
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
   $scope.getStudents();
   /* ========== GET STUDENTS =========== */




    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp={};
        $scope.selectedStudentsArr=[];
        $scope.chkStudent=[];
        $scope.post.getSTProposedClasses=[];
        $scope.SELECTALL=false;
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