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
$postModule.directive('passwordEye', function () {
	return {
		restrict: 'A',
		link: function (scope, element) {
			var eyeIcon = angular.element('<span class="fa fa-eye"></span>');
			eyeIcon.css({
				position: 'absolute',
				cursor: 'pointer',
				right: '10px',
				top: '50%',
				transform: 'translateY(-50%)',
				// fontSize: '1.5em',
				color: '#aaa'
			});
			element.wrap('<div style="position:relative;"></div>').after(eyeIcon);
			eyeIcon.on('click', function () {
				var inputType = element.attr('type') === 'password' ? 'text' : 'password';
				element.attr('type', inputType);
				eyeIcon.toggleClass('fa-eye fa-eye-slash');
			});
		}
	};
});
$postModule.controller("myCtrl", function ($scope, $http,$window,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.date = new Date().toLocaleString('en-US', {
       
         hour: 'numeric', // numeric, 2-digit
         minute: 'numeric', // numeric, 2-digit
       
     })
    

    var url = 'code/login_code.php';

    //===== Get IP =====
    $.getJSON('https://json.geoiplookup.io/?callback=?', function(data) {
        // console.log(JSON.stringify(data, null, 2));
        $scope.IP = data.ip;
        $scope.TIMEZONE = data.timezone_name;
        $scope.COUNTRYNAME = data.country_name;
    });




    

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
                formData.append("datetime", $scope.date);
                formData.append("IP", $scope.IP);
                formData.append("TIMEZONE", $scope.TIMEZONE);
                formData.append("COUNTRYNAME", $scope.COUNTRYNAME);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                
                $scope.post.user = data.data.data;
               
                $scope.messageSuccess(data.data.message);

                $timeout(()=>{
                    $window.location.assign('dashboard.html');
                },1000);
                
                $('.spinLogin').hide();
                $('.btn-log').text('LOGIN');
                // $('.btn-log').removeAttr('disabled');        
                
            }
            else {
                $scope.messageFailure(data.data.message);
                //  console.log(data.data)
                // jQuery('.btn-save,.btn-update').button('reset');
                $('.spinLogin').hide();
                $('.btn-logText').text('LOGIN');
                $('.btn-log').removeAttr('disabled');
            }
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
                    window.location.assign('index.html#!/login')
                }
                else {
                    //window.location.assign('backoffice/index#!/')
                }
            },
            function (data, status, headers, config) {
                // console.log('Not login Failed');
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
            $scope.post.user = data.data.data;
            $scope.USER_LOCATION=data.data.LOCATION;
            if (data.data.success) {
                // alert(data.data.data[0]['RESET']);
                // $scope.post.USERCATEGORY=data.data.USERCATEGORY;

                // $scope.resetpass=data.data.data[0]['RESET'];
            }
            else {
                window.location.assign('index.html#!/login')
            }
        },
        function (data, status, headers, config) {
            //console.log(data)
            // console.log('Failed');
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