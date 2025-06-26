

$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = 'REQUEST';
    $scope.PageSub = 'REQ_FOR_DIS';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.ADMIN=false;
    
    var url = 'code/RequestForDiscontinue_code.php';

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
                $scope.USER_LOCATION=data.data.LOCATION;
                $scope.REGID=data.data.data[0]['REGID'];
                $scope.PLAN=data.data.data[0]['PLAN'];
                $scope.GRADE=data.data.data[0]['GRADE'];
                $scope.LOCID=data.data.data[0]['LOCATIONID'];
                $scope.PLANID=data.data.data[0]['PLANID'];
                $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];

                $scope.ActivePlan = data.data.ActivePlan;

                $scope.getRFD();
                $scope.getStudentPlans();
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
    /* =============== CHECK SESSION ============== */



    /* =============== SAVE DATA ============== */
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
                    formData.append("REGID", $scope.REGID);
                    formData.append("txtRemark", $scope.temp.txtRemark);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) { 
                    $scope.clear();
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
    /* =============== SAVE DATA ============== */





    /* ========= GET STUDENT PLANS ========== */
    $scope.getStudentPlans = function () {
        $('.spinPlans').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentPlans','GET_FOR':'STUDENT'}),
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





    
    /* ========== GET RFD =========== */
    $scope.getRFD = function () {
        $('.spinMainData').show();
        $scope.post.getRFD=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFD','REGID':$scope.REGID,'ddlLocation':$scope.LOCID}),
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





    /* ========== CLEAR FORM =========== */
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
        $scope.post.getStudentByPlan=[];
    }
    /* ========== CLEAR FORM =========== */






    /* ========== DELETE =========== */
    // $scope.delete = function (id) {
    //     var r = confirm("Are you sure want to delete this record!");
    //     if (r == true) {
    //         $http({
    //             method: 'post',
    //             url: url,
    //             data: $.param({ 'reqid': id.REQID, 'type': 'delete' }),
    //             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //         }).
	// 	    then(function (data, status, headers, config) {
    //             // console.log(data.data)
	// 	        if (data.data.success) {
	// 	            // console.log(data.data.message)
    //                 $scope.clear();
    //                 $scope.getRFD();
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
                    window.location.assign('login.html#!/login');
                }
                else {
                    window.location.assign('dashboard.html#!/dashboard');
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