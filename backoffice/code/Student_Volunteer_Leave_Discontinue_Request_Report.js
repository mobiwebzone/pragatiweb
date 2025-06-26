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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$window) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "REPORTS";
    $scope.PageSub = "ST_LVD_REQ";
    $scope.dt = new Date().toLocaleString('sv-SE');
    $scope.temp.txtFromDT=new Date();
    $scope.temp.txtToDT=new Date();
    $scope.PLANS_model = [];
    $scope.STUDENTS_model = [];

    $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px',scrollableWidth:'200px'};
    $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};

    var url = 'code/Student_Volunteer_Leave_Discontinue_Request_Report.php';
    var masterUrl = 'code/MASTER_API.php';


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
    /* =============== CHECK SESSION ============== */





     /* ========== GET REPORT =========== */
     $scope.post.GET_REPORT_VIEW = [];
     $scope.getReport = function () {
        $scope.post.VolunteerData = [];
        $scope.post.LeaveData = [];
        $scope.post.DiscontinueData = [];
        // var REGIDS = $scope.STUDENTS_model.map(s=>s.id);
        // var PLANIDS = $scope.PLANS_model.map(s=>s.id);
        if(!$scope.temp.txtFromDT || $scope.temp.txtFromDT=='' || !$scope.temp.txtToDT || $scope.temp.txtToDT=='' || !$scope.temp.ddlLocation ||$scope.temp.ddlLocation<=0) return;
        $('.btnGet').attr('disabled','disabled').text('Get...');
        $('.spinReport').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport',
            'txtFromDT': (!$scope.temp.txtFromDT || $scope.temp.txtFromDT == '') ? '' : $scope.temp.txtFromDT.toLocaleDateString('sv-SE'),
            'txtToDT': (!$scope.temp.txtToDT || $scope.temp.txtToDT == '') ? '' :$scope.temp.txtToDT.toLocaleDateString('sv-SE'),
            'ddlLocation':$scope.temp.ddlLocation,
            // 'REGIDS': REGIDS,
            // 'PLANIDS': PLANIDS,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.VolunteerData = data.data.successVol?data.data.data_volunteer:[];
        $scope.post.LeaveData = data.data.successLeave?data.data.data_leave:[];
        $scope.post.DiscontinueData = data.data.successDisc?data.data.data_discontinue:[];
        // $timeout(()=>{$scope.filterData()},1000);

        if(!data.data.successVol) $scope.messageFailure(data.data.messageVol);
        if(!data.data.successLeave) $scope.messageFailure(data.data.messageLeave);
        if(!data.data.successDisc) $scope.messageFailure(data.data.messageDisc);

        $('.spinReport').hide();
        $('.btnGet').removeAttr('disabled').text('Get');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
    }
    // $scope.getReport(); --INIT
    /* ========== GET REPORT =========== */


    
    /* ========== Cancel-Approve =========== */
    $scope.CancelModalVolunteer=function(id,For){
        $scope.ModalNM = For;
        $scope.VRID = id.VRID;
    }
    $scope.CancelApprove = function () {
        if($scope.userrole != 'TSEC_USER'){
            var r = confirm("Are you sure want to " + $scope.ModalNM + " this request!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: 'code/dashboard_code.php',
                    data: $.param({ 'VRID': $scope.VRID,'txtRemarkCA':$scope.temp.txtRemarkCA,'FOR':$scope.ModalNM, 'type': 'CancelApprove' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        $('#cancelModalCV').trigger({type:"click"});
                        $scope.getReport();
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== Cancel-Approve =========== */

    
    /* ========== Cancel Leave =========== */
    $scope.CancelModalStudent=function(id){
        $scope.CANCELREQID = id.REQID;
    }
    $scope.CancelStudentLeave = function () {
        if($scope.userrole != 'TSEC_USER'){
            var r = confirm("Are you sure want to cancel this request!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: 'code/dashboard_code.php',
                    data: $.param({ 'reqid': $scope.CANCELREQID,'txtCancelRemark':$scope.temp.txtCancelRemarkST, 'type': 'CancelStudentLeave' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        $('#cancelModalStudent').trigger({type:"click"});
                        $scope.getReport();
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== Cancel Leave =========== */


       /* ========== Discontinue Approve =========== */
   $scope.DiscontinueApprove = function (x) {
    //    alert(x.REGDID);
    if($scope.userrole != 'TSEC_USER'){
        var r = confirm("Are you sure want to approve this request!");
        if (r == true) {
            
            $('#appr_loader').removeClass('d-none');
            
            $http({
                method: 'post',
                url: 'code/dashboard_code.php',
                data: $.param({ 'REGDID': x.REGDID,'REGID':x.REGID, 'type': 'DiscontinueApprove' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    // console.log(data.data.message)
    
                    $timeout(function () {
                        $('#appr_loader').addClass('d-none');
                        $scope.getReport();
                        $scope.messageSuccess(data.data.message);
                    },2000);
                } else {
                    $timeout(function () {
                        $('#appr_loader').addClass('d-none');
                        $scope.messageFailure(data.data.message);
                    },2000);
                }
            })
            
        }
    }
    // $scope.appr_loader=0;
    }
    /* ========== Discontinue Approve =========== */




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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getReport();
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
    
    $scope.addRemoveStudents=function(){
        $scope.STUDENT_LIST = $scope.STUDENTS_model;
    }


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
    
    $scope.onPrintVol = false;
    $scope.onPrintLeave = false;
    $scope.onPrintDisc = false;
    $scope.print = function(FOR){
        $('.printbtn').attr('disabled','disabled');
        if(FOR == 'VOL'){
            $scope.onPrintVol = true;
            $scope.onPrintLeave = false;
            $scope.onPrintDisc = false;
        }
        else if(FOR == 'LEAVE'){
            $scope.onPrintVol = false;
            $scope.onPrintLeave = true;
            $scope.onPrintDisc = false;
        }
        else if(FOR == 'DISC'){
            $scope.onPrintVol = false;
            $scope.onPrintLeave = false;
            $scope.onPrintDisc = true;
        }else{
            $scope.onPrintVol = false;
            $scope.onPrintLeave = false;
            $scope.onPrintDisc = false;
        }
        $timeout(()=>{
            $window.print();
            $('.printbtn').removeAttr('disabled');
        },1000);
    }
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