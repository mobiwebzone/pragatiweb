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
    $scope.PageSub = "ST_DISCONTINUE_REQ";
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.ADMIN = true;
    
    var url = '../student_zone/code/RequestForDiscontinue_code.php';

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
                
                if($scope.userrole != "TSEC_USER")
                {
                    if(data.data.locid > 0){
                        // $scope.getTeacher();
                        // $scope.getRFV();
                        // $scope.getStudentProduct();
                        $scope.getLocations();
                        $scope.getStudentPlans();
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
        var r = confirm("Are you sure want to discontinue this plan!");
        if (r == true) {
            $(".btn-save").attr('disabled', 'disabled').text('Saving...');
            $(".btn-update").attr('disabled', 'disabled').text('Updating...');
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'Save');
                    formData.append("txtDate", $scope.temp.txtFromDate.toLocaleString('sv-SE'));
                    formData.append("ddlPlan", $scope.temp.ddlPlan);
                    formData.append("REGID", $scope.temp.ddlStudent);
                    formData.append("txtRemark", $scope.temp.txtRemark);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                if (data.data.success) { 
                    // $scope.clear();
                    $scope.temp.txtRemark = '';
                    $scope.messageSuccess(data.data.message);
                    $scope.getRFD();
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
                $('.btn-save').removeAttr('disabled').text('SAVE');
                $('.btn-update').removeAttr('disabled').text('UPDATE');
            });
        }
    }
    /* =================== SAVE DATA =================== */
    
    

    /* ========= GET STUDENT PLANS ========== */
    $scope.getStudentPlans = function () {
        $('.spinPlans').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentPlans','GET_FOR':'ADMIN'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                
                $scope.post.getStudentPlans=data.data.data;
                if(data.data.data.length == 1){
                    $scope.temp.ddlPlan = (data.data.data[0]['PLANID']).toString();
                }
            }
            $('.spinPlans').hide();

        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    // $scope.getStudentPlans();
    /* ========= GET STUDENT PLANS ========== */
    
    
    
    /* ========== GET STUDENT BY PLAN =========== */
    $scope.getStudentByPlan = function () {
        $scope.post.getStudentByPlan=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.spinStudentName').show();
        $http({
            method: 'post',
            url: '../student_zone/code/RequestForLeave_code.php',
            data: $.param({ 'type': 'getStudentByPlan','ddlPlan':$scope.temp.ddlPlan,'ddlLocation':$scope.temp.ddlLocation}),
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
            if($scope.temp.ddlLocation > 0) $scope.getRFD();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    


    /* ========== GET RFD =========== */
    $scope.getRFD = function () {
        $scope.post.getRFD=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.spinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFD','REGID':$scope.temp.ddlStudent,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRFD=data.data.data;
            }
            $('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    // $scope.getRFD();
    /* ========== GET RFD =========== */




    /* ========== CLEAR =========== */
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
        $scope.post.getStudentByPlan = [];
    }
    /* ========== CLEAR =========== */
    


    
    
    /* ========== DELETE =========== */
    // $scope.delete = function (id) {
    //     var r = confirm("Are you sure want to delete this record!");
    //     if (r == true) {
    //         $http({
    //             method: 'post',
    //             url: url,
    //             data: $.param({ 'vrid': id.VRID,'txtCancelRemark':$scope.temp.txtCancelRemark, 'type': 'delete' }),
    //             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //         }).
	// 	    then(function (data, status, headers, config) {
    //             // console.log(data.data)
	// 	        if (data.data.success) {
	// 	            // console.log(data.data.message)
    //                 console.log($scope.editMode);
    //                 if($scope.editMode){
    //                     $scope.temp.vrid='';
    //                     $scope.temp.txtRemark='';
    //                     $scope.post.getRFVD=[];
    //                     $scope.GET_VRID=0;
    //                     $scope.temp.ddlProduct='';
    //                 }else{
    //                     $scope.clear();
    //                 }
    //                 $scope.getRFV();
	// 	            $scope.messageSuccess(data.data.message);
	// 	        } else {
	// 	            $scope.messageFailure(data.data.message);
	// 	        }
	// 	    })
    //     }
    // }
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