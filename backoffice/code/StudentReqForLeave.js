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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$filter) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_ABSENCE_REQ";
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];
    $scope.ADMIN = true;
    
    var url = '../student_zone/code/RequestForLeave_code.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */





    /* ========== CHECK SESSION =========== */
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
                $scope.LOC_ID=data.data.locid;
                // alert(data.data.userrole);
                // window.location.assign("dashboard.html");
                
                if($scope.userrole != "TSEC_USER")
                {
                    // $scope.getRFL();
                    if(data.data.locid > 0){
                        // $scope.getTeacher();
                        $scope.getPlans();
                    }
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
            }
            else {
                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ========== CHECK SESSION =========== */




    /* ========== SAVE =========== */
    $scope.Save = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Save');
                formData.append("reqid", $scope.temp.reqid);
                formData.append("txtFromDate", $scope.dateFormat($scope.temp.txtFromDate));
                formData.append("txtToDate", $scope.dateFormat($scope.temp.txtToDate));
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("REGID", $scope.temp.ddlStudent);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) { 
                if($scope.editMode){
                    $scope.temp.reqid='';
                    $scope.temp.txtRemark='';
                }else{
                    $scope.clear();
                }
                $scope.getRFL();
                $scope.messageSuccess(data.data.message);
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }
    /* ========== SAVE =========== */





    

    /* ========== GET RFL =========== */
    $scope.getRFL = function () {
        $('.spinMainData').show();
        $scope.post.getRFL=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFL',
                            'REGID':$scope.temp.ddlStudent,
                            'GET_FOR':'ADMIN',
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                
                $scope.post.getRFL=data.data.data;
            }
            $('.spinMainData').hide();
            // $scope.post.getAttendance = data.data.data;
            // $scope.temp.txtDate=new date
            // $scope.RegID = data.data.RegID;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    /* ========== GET RFL =========== */



   




    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $http({
            method: 'post',
            url: 'code/Teacher_Product_code.php',
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.LOC_ID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacher = data.data.data;
    
            if($scope.userrole == 'TEACHER'){
                $scope.temp.ddlTeacher = ($scope.userid).toString();
                $scope.getTeacherProduct();
                $scope.getAtt();
            }
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
    }
    // $scope.getTeacher();
    /* ========== GET Teacher =========== */
    
    
    
    /* ========== GET Teacher Plans =========== */
    $scope.getPlans = function () {
        $('.spinPlans').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
            $('.spinPlans').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET Teacher =========== */
    
    
    
    /* ========== GET STUDENT BY PLAN =========== */
    $scope.getStudentByPlan = function () {
        $('.spinStudentName').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByPlan','ddlPlan':$scope.temp.ddlPlan,'ddlLocation':$scope.LOC_ID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByPlan = data.data.success?data.data.data:[];
            $('.spinStudentName').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlan();
    /* ========== GET STUDENT BY PLAN =========== */


    // EDIT
    $scope.edit = function(x){
        if(x.CANCELLED <= 0){
            $scope.temp={
                reqid : x.REQID,
                txtFromDate : new Date(x.FROMDT),
                txtToDate : new Date(x.TODT),
                txtRemark : x.REMARKS,
            }
            $scope.temp.ddlPlan = x.PLANID.toString();
            if($scope.temp.ddlPlan > 0)$scope.getStudentByPlan();
            $timeout(()=>{$scope.temp.ddlStudent = x.REQ_BY_ID.toString();},1000);
            
            $scope.index = $scope.post.getRFL.indexOf(x);
            $scope.editMode = true;
        }
    }

    //Clear
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
        $scope.temp.txtToDate=new Date();
        if($scope.ADMIN) $scope.post.getRFL=[];
        $scope.editMode = false;
    }

    // Cancel Modal
    $scope.CancelModal=function(id){
        $scope.CANCELREQID = id.REQID;
    }

    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'reqid': id.REQID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    if($scope.editMode){
                        $scope.temp.reqid='';
                        $scope.temp.txtRemark='';
                    }else{
                        $scope.clear();
                    }
                    $scope.getRFL();
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
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
                window.location.assign('index.html#!/login');
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