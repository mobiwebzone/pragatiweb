
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$window) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.EmailID = '';
    
    var url = 'code/forgot_password_code.php';


    /* Go to Admin Dashboard */
    $scope.adminDashboad = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'adminDashboad' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                
            }
            else {
                $scope.loginFailure(data.data.message);
            }
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }



    

    $scope.CheckUserID = function () {
        // alert();
        $scope.EmailID='';
        $(".btn-proceed").attr('disabled', 'disabled');
        $(".btn-proceed").text('PROCEED...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'CheckUserID');
                formData.append("txtLoginId", $scope.temp.txtLoginId);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.EmailID = data.data.EMAIL;

                // $scope.messageSuccess(data.data.message); 
            }
            else {
                $scope.messageFailure(data.data.message);
                //  console.log(data.data)
            }
            $('.btn-proceed').removeAttr('disabled');
            $(".btn-proceed").text('PROCEED');
        });
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
                    window.location.assign('login.html#!/login')
                }
                else {
                    //window.location.assign('backoffice/index#!/')
                }
            },
            function (data, status, headers, config) {
                console.log('Not login Failed');
            })
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
                $scope.USER_LOCATION=data.data.LOCATION;
                // alert(data.data.data[0]['RESET']);
                // $scope.post.USERCATEGORY=data.data.USERCATEGORY;

                // $scope.resetpass=data.data.data[0]['RESET'];
            }
            else {
                // window.location.assign('login.html#!/login');
            }
        },
        function (data, status, headers, config) {
            //console.log(data)
            console.log('Failed');
        })

    }




    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-success').show();
        });
        jQuery('.alert-success').delay(5000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-danger').show();
        });
        jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});