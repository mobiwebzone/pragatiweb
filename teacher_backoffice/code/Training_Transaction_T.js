
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page="TRAINING";
    $scope.PageSub="TRAINING_TRANS";
    $scope.formTitle = '';
    $scope.PAGEFOR = 'TEACHER';
    $scope.temp.txtCompletedDT = new Date();
    
    var url = '../backoffice/code/Training_Transaction_code.php';



    

    /* =============== CHECK SESSION ============== */
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: '../backoffice/code/checkSession.php',
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
                
                $scope.getTDCategory();
                $scope.getTrainingMasters();
                $scope.getTrainingTransactions();

                $scope.temp.ddlTeacher_Student = 'Teacher';
                $scope.getTeacher_Students();

                // window.location.assign("dashboard.html");
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





    // =========== SAVE DATA ==============
    $scope.saveData = function(){
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
                formData.append("type", 'saveData');
                formData.append("ttid", $scope.temp.ttid);
                formData.append("ddlLocation", $scope.LOC_ID);
                formData.append("ddlTransaction", $scope.temp.ddlTransaction);
                formData.append("ddlTraining", $scope.temp.ddlTraining);
                formData.append("txtCompletedDT", (!$scope.temp.txtCompletedDT || $scope.temp.txtCompletedDT=='') ? '' : $scope.temp.txtCompletedDT.toLocaleString('sv-SE'));
                formData.append("ddlTeacher_Student", $scope.temp.ddlTeacher_Student);
                formData.append("ddlUser", $scope.temp.ddlUser);
                formData.append("txtTRemark", $scope.temp.txtTRemark);
                formData.append("txtSRemark", $scope.temp.txtSRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearForm();
                $scope.getTrainingTransactions();
                $("#ddlTransaction").focus();
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
    // =========== SAVE DATA ==============






    /* ========== GET TRAINING TRANSACTIONS =========== */
    $scope.getTrainingTransactions = function () {
        $scope.temp.txtSerarch = undefined;
        $('#ddlSearchCategory').attr('disabled','disabled');
        $('#SpinnerMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getTrainingTransactions',
                            'ddlLocation':$scope.LOC_ID,
                            'ddlSearchCategory': $scope.temp.ddlSearchCategory,
                            'ddlSearchSubCategory': $scope.temp.ddlSearchSubCategory,
                            'ddlSearchSSubCategory': $scope.temp.ddlSearchSSubCategory,
                            'USERID': $scope.userid,
                            'FOR': 'TEACHER',
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTrainingTransactions = data.data.data;
            }else{
                $scope.post.getTrainingTransactions=[];
                // console.info(data.data.message);
            }
            $scope.refreshData = !$scope.refreshData;
            $('#SpinnerMainData').hide();
            $('#ddlSearchCategory').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTrainingMasters(); --INIT
    /* ========== GET TRAINING TRANSACTIONS =========== */






    /* ========== GET TRAINING MASTER =========== */
    $scope.getTrainingMasters = function () {
        $('.spinTM').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Training_Master_code.php',
            data: $.param({ 
                            'type': 'getTrainingMasters',
                            'ddlLocation':$scope.LOC_ID,
                            'ddlSearchCategory': $scope.temp.ddlSearchCategory,
                            'FOR':'TEACHER'
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTrainingMasters = data.data.data;
            }else{
                $scope.post.getTrainingMasters=[];
                // console.info(data.data.message);
            }
            $scope.refreshData = !$scope.refreshData;
            $('.spinTM').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTrainingMasters(); --INIT
    /* ========== GET TRAINING MASTER =========== */



    /* ========== GET TEACHER/STUDENTS =========== */
    $scope.getTeacher_Students = function () {
        $scope.post.getTeacher_Students = [];
        $('.spinUsers').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacher_Students',
                            'ddlTeacher_Student':$scope.temp.ddlTeacher_Student,
                            'ddlLocation':$scope.LOC_ID
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTeacher_Students = data.data.data;
                if($scope.post.getTeacher_Students.length>0 && $scope.userid>0) $scope.temp.ddlUser = ($scope.userid).toString();
                
            }else{
                $scope.post.getTeacher_Students = [];
            }
            $('.spinUsers').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacher_Students();
    /* ========== GET USERS/STUDENTS =========== */
        




    /* ========== GET CATEGORY =========== */
    $scope.getTDCategory = function () {
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $('.spinCat').show();
        $http({
            method: 'post',
            url: '../backoffice/code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getCategories','ddlLocation':$scope.LOC_ID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDCategory = data.data.data;
            }else{
                $scope.post.getTDCategory = [];
            }
            $('.spinCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDCategory(); --INIT
    /* ========== GET CATEGORY =========== */




    

    /* ========== GET SUB CATEGORY =========== */
    $scope.getTDSubCategory = function () {
        $scope.post.getTDSSubCategory = [];
        $('.spinSubCat').show();
        $http({
            method: 'post',
            url: '../backoffice/code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'tdcatid' : $scope.temp.ddlSearchCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDSubCategory = data.data.data;
            }else{
                $scope.post.getTDSubCategory = [];
            }
            $('.spinSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDSubCategory();
    /* ========== GET SUB CATEGORY =========== */




    

    /* ========== GET SUB SUBCATEGORY =========== */
    $scope.getTDSSubCategory = function () {
        $('.spinSSubCat').show();
        $http({
            method: 'post',
            url: '../backoffice/code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSSubCategories', 'tdsubcatid' : $scope.temp.ddlSearchSubCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDSSubCategory = data.data.data;
            }else{
                $scope.post.getTDSSubCategory = [];
            }
            $('.spinSSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDSSubCategory();
    /* ========== GET SUB SUBCATEGORY =========== */




    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
 
        $("#ddlTransaction").focus();
        $scope.temp.ttid = id.TTID;
        $scope.temp.ddlLocation = id.LOCID.toString();
        $scope.temp.ddlTransaction = id.TRANSACTION;
        $scope.temp.ddlTraining = id.TMID > 0 ? (id.TMID).toString() : '';
        $scope.temp.txtCompletedDT = id.COMPLETED_DATE == '-' ? '' : new Date(id.COMPLETED_DATE);
        // $scope.temp.ddlTeacher_Student = id.USER_TYPE;
        // if($scope.temp.ddlTeacher_Student.length>0) $scope.getTeacher_Students();
        // $timeout(()=>{$scope.temp.ddlUser = id.USERID > 0 ? (id.USERID).toString() : '';},1000);
        $scope.temp.txtTRemark = id.TEACHER_REMARK;
        $scope.temp.txtSRemark = id.SUPERVISIOR_REMARK;
        
        $scope.editMode = true;
        $scope.index = $scope.post.getTrainingTransactions.indexOf(id);
        
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlTransaction").focus();
        $scope.temp={};
        // $scope.post.getTeacher_Students=[];
        $scope.editMode = false;

        $scope.temp.ddlTeacher_Student = 'Teacher';
        $scope.getTeacher_Students();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TTID': id.TTID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTrainingTransactions.indexOf(id);
                    $scope.post.getTrainingTransactions.splice(index, 1);
                    // console.log(data.data.message)
                    
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
                    window.location.assign('dashboard.html#!/dashboard');
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