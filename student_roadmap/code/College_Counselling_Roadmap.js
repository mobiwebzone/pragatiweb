
$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$window,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.temp.ddlStudentType = 'Non-Registered';
    
    var url = 'code/College_Counselling_Roadmap.php';


    $scope.StudentForm = function(){
        $('.StartElement').slideUp(function () { 
            // $('.logForm').show();
            $('.logForm').slideDown();
         });
    }
    $scope.StudentFormBack = function(){
        $('.logForm').slideUp(function () { 
            // $('.logForm').show();
            $('.StartElement').slideDown();
         });
    }


    $scope.Login = function () {
        // alert();
        $('.btn-log').attr('disabled','disabled');
        $('.btn-logText').text('LOGIN...');
        $('.spinLogin').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'login');
                formData.append("txtLoginId", $scope.temp.txtLoginId);
                formData.append("txtPWD", $scope.temp.txtPWD);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);

                $timeout(()=>{
                    $window.location.assign('College_Counselling_Roadmap_Form.html');
                },1000);
                
                $('.spinLogin').hide();
                $('.btn-log').text('LOGIN');
                
            }
            else {
                $scope.messageFailure(data.data.message);
                //  console.log(data.data)
                $('.spinLogin').hide();
                $('.btn-logText').text('LOGIN');
                $('.btn-log').removeAttr('disabled');
            }
        });
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
            if (data.data.success){
                $scope.USER_LOCATION=data.data.LOCATION;
            }else{
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            //console.log(data)
            console.log('Failed');
        })

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
                    window.location.assign('College_Counselling_Roadmap.html#!/login');
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