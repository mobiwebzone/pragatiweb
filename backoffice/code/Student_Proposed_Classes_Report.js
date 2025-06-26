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
    $scope.PageSub = "ST_PROCLASS_RPT";
    $scope.temp.txtFromDT = new Date();
    $scope.temp.txtToDT = new Date();
    $scope.chkStudent = [];
    $scope.selectedStudentsArr = [];
    
    
    var url = 'code/Student_Proposed_Classes_Report.php';


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
                    $scope.getClassSubject();
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





    /* ========== GET STUDENT PROPOSED CLASSES =========== */
    $scope.getSTProposedClasses = function () {
    $scope.selectedStudentsArr=[];
    if((!$scope.temp.txtYear || $scope.temp.txtYear < 4) || $scope.temp.ddlClassSubject <=0 || !$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
    $scope.post.getSTProposedClasses =[];
    $scope.chkStudent=[];
    $scope.SELECTALL=false;
    $('#SpinMainData').show();
    $('.btn-clear').attr('disabled','disabled');
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getSTProposedClasses',
                        'txtYear' : $scope.temp.txtYear,
                        'ddlClassSubject' : $scope.temp.ddlClassSubject,
                        'ddlLocation' : $scope.temp.ddlLocation,
                    }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getSTProposedClasses = data.data.success ? data.data.data : [];
        if(data.data.success && ($scope.post.getSTProposedClasses && $scope.post.getSTProposedClasses.length > 0)){
            $timeout(()=>{$scope.chkStudent= new Array($scope.post.getSTProposedClasses.length).fill('0');},200);
        }
        $('#SpinMainData').hide();
        $('.btn-clear').removeAttr('disabled');
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
        
    }
    //  $scope.getSTProposedClasses();
    /* ========== GET STUDENT PROPOSED CLASSES =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getSTProposedClasses();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    


    /* ========== GET CLASS/SUBJECT =========== */
    $scope.getClassSubject = function () {
        $('.spinCS').show();
        $http({
            method: 'post',
            url: 'code/StudentProposedSubject.php',
            data: $.param({ 'type': 'getClassSubject'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getClassSubject = data.data.success?data.data.data:[];
            $('.spinCS').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubject(); --INIT
    /* ========== GET CLASS/SUBJECT =========== */
    
    
    
    
    // =========== SEND SMS ==============
    $scope.SendSMS = function(){
        $(".btn-sms").attr('disabled', 'disabled');
        $(".btn-sms").text('Sending...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'SendSMS');
                formData.append("txtSMS", $scope.temp.txtSMS);
                formData.append("STUDENT_DATA", JSON.stringify($scope.selectedStudentsArr));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.txtSMS='';
                $scope.selectedStudentsArr=[];
                // $scope.chkStudent=[];
                $scope.chkStudent = new Array($scope.chkStudent.length).fill('0');
                $scope.SELECTALL=false;
                $('#SmsModal').modal('hide');
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-sms').removeAttr('disabled');
            $(".btn-sms").html('<i class="fa fa-send"></i> SEND');
        });
    }
    // =========== SEND SMS ==============




    /* ========== SELECT STUDENTS =========== */
    $scope.selectedStudents = function(val,index,dt){
        // console.log(`${val} || ${index}`);
        if(val == '1'){
            $scope.selectedStudentsArr.push(dt);
        }else{
            var indx = $scope.selectedStudentsArr.findIndex(p => p.GSID == dt.GSID);
            $scope.selectedStudentsArr.splice(indx,1);
        }
        $scope.SELECTALL=$scope.selectedStudentsArr.length == $scope.post.getSTProposedClasses.length ? true : false;
        // console.log(`SELECTED : ${$scope.selectedStudentsArr.length} || MAIN_DATA ${$scope.post.getSTProposedClasses.length}`);
        // console.log($scope.SELECTALL);
        // console.log($scope.selectedStudentsArr);
    }
    /* ========== SELECT STUDENTS =========== */
    
    
    
    /* ========== SELECT ALL STUDENTS =========== */
    $scope.SELECTALL=false;
    $scope.SelectAllStudent = function(){
        if(!$scope.post.getSTProposedClasses || $scope.post.getSTProposedClasses.length<=0) return;
        // console.log($scope.chkStudent);
        // $scope.selectedStudentsArr = [];
        // console.log(`SELECTED : ${$scope.selectedStudentsArr.length} || MAIN_DATA ${$scope.post.getSTProposedClasses.length}`);
        if($scope.selectedStudentsArr.length == $scope.post.getSTProposedClasses.length){
            $scope.chkStudent = new Array($scope.chkStudent.length).fill('0');
            $scope.selectedStudentsArr = [];
            $scope.SELECTALL=false;
        }else{
            $scope.chkStudent = new Array($scope.chkStudent.length).fill('1');
            $scope.selectedStudentsArr = JSON.parse(JSON.stringify($scope.post.getSTProposedClasses));
            $scope.SELECTALL=true;
        }
        // console.log($scope.selectedStudentsArr);
    }
    /* ========== SELECT ALL STUDENTS =========== */


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
//     $scope.getStudents = function () {
//         $('.SpinStudent').show();
//         $http({
//             method: 'post',
//         //    url: 'code/Student_Course_Pending_Report.php',
//            url: url,
//            data: $.param({ 
//                             'type': 'getStudents',
//                             'ddlPlan':$scope.temp.ddlPlan
//                         }),
//            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
//        }).
//        then(function (data, status, headers, config) {
//         //    console.log(data.data);
//             $scope.post.getStudents = data.data.success ? data.data.data : [];

//            $('.SpinStudent').hide();
//        },
//        function (data, status, headers, config) {
//            console.log('Failed');
//        })
//    }
//    $scope.getStudents();
   /* ========== GET STUDENTS =========== */




    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp={};
        $scope.selectedStudentsArr=[];
        $scope.chkStudent=[];
        $scope.post.getSTProposedClasses=[];
        $scope.SELECTALL=false;
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