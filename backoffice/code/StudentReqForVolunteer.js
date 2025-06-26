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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$filter) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_VOLUNTEER_REQ";
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.ADMIN = true;
    
    var url = '../student_zone/code/RequestForVolunteer_code.php';

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
                    if(data.data.locid > 0){
                        // $scope.getTeacher();
                        // $scope.getRFV();
                        // $scope.getStudentProduct();
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




    /* =================== SAVE DATA =================== */
    $scope.Save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Save');
                formData.append("vrid", $scope.temp.vrid);
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
                $scope.GET_VRID = data.data.VRID;
                $scope.temp.vrid = data.data.VRID;
                $scope.editMode = true;
                $scope.getRFV();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* =================== SAVE DATA =================== */
    
    
    
    /* =================== ADD PRODUCT =================== */
    $scope.AddProduct = function(){
        $(".btn-add").attr('disabled', 'disabled').text('Add...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'AddProduct');
                formData.append("vrdid", $scope.temp.vrdid);
                formData.append("GET_VRID", $scope.GET_VRID);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) { 
                $scope.getRFVD($scope.GET_VRID);
                $scope.getRFV();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-add').removeAttr('disabled').text('Add');
        });
    }
    /* =================== ADD PRODUCT =================== */



    /* ========== GET RFV =========== */
    $scope.getRFV = function () {
        $('.spinMainData').show();
        $scope.post.getRFV=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFV',
            'REGID':$scope.temp.ddlStudent,
            'GET_FOR':'ADMIN',
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if(data.data.success){
            $scope.post.getRFV=data.data.data;
        }
        $('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    // $scope.getRFV();
    /* ========== GET RFV =========== */



    /* ========== GET RFVD =========== */
    $scope.getRFVD = function (GET_VRID) {
        $('.spinProduct').show();
        $scope.post.getRFVD = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFVD',
                            'GET_VRID':$scope.GET_VRID,
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRFVD=data.data.data;
            }
            $('.spinProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    // $scope.getRFV();
    /* ========== GET RFVD =========== */


    
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
            url: '../student_zone/code/RequestForLeave_code.php',
            data: $.param({ 'type': 'getStudentByPlan','ddlPlan':$scope.temp.ddlPlan}),
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


    


    /* ========== GET STUDENT PRODUCT =========== */
    $scope.getStudentProduct = function () {
        $scope.post.getStudentProduct=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentProduct',
                            'REGID':$scope.temp.ddlStudent,
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentProduct=data.data.data;
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    //    $scope.getStudentProduct();
    /* ========== GET STUDENT PRODUCT =========== */



    /* ========== EDIT =========== */
    $scope.edit = function(x){
        if(x.APPROVED > 0 || x.CANCELLED > 0){
            console.log('Edit not allowed after admin response.');
        }
        else{
            $scope.temp={
                vrid : x.VRID,
                txtFromDate : new Date(x.FROMDT),
                txtToDate : new Date(x.TODT),
                txtRemark : x.REMARKS,
            }
            $scope.GET_VRID=x.VRID;
            $scope.getRFVD(x.VRID);

            $scope.temp.ddlPlan = x.PLANID.toString();
            if($scope.temp.ddlPlan > 0)$scope.getStudentByPlan();
            $timeout(()=>{$scope.temp.ddlStudent = x.REGID.toString();},1000);

            $scope.index = $scope.post.getRFV.indexOf(x);
            $scope.editMode = true;
        }
    }
    /* ========== EDIT =========== */



    /* ========== CLEAR =========== */
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
        $scope.temp.txtToDate=new Date();
        $scope.GET_VRID=0;
        $scope.post.getRFV=[];
        $scope.post.getRFVD=[];
        $scope.temp.ddlProduct='';
        $scope.editMode = false;
    }
    /* ========== CLEAR =========== */
    


    /* ========== CANCEL MODAL =========== */
    $scope.CancelModal=function(id){
        $scope.CANCELVRID = id.VRID;
    }
    /* ========== CANCEL MODAL =========== */



    /* ========== CANCEL PRODUCT =========== */
    $scope.CancelPeoduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'vrdid': id.VRDID, 'type': 'CancelPeoduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    $scope.getRFV();
                    $scope.getRFVD();
                    $scope.post.getRFVD=[];
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== CANCEL PRODUCT =========== */

    
    
    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'vrid': id.VRID,'txtCancelRemark':$scope.temp.txtCancelRemark, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    console.log($scope.editMode);
                    if($scope.editMode){
                        $scope.temp.vrid='';
                        $scope.temp.txtRemark='';
                        $scope.post.getRFVD=[];
                        $scope.GET_VRID=0;
                        $scope.temp.ddlProduct='';
                    }else{
                        $scope.clear();
                    }
                    $scope.getRFV();
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */








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