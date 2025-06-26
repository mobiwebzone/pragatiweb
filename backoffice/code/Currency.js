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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "SETTING";
    $scope.PageSub = "CURRENCY";
    $scope.formTitle = 'Currency';
    
    var url = 'code/Currency_code.php';


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
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getCurrency();
                }
                // window.location.assign("dashboard.html");
            }
            else {
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.saveCurrency = function(){
        // alert($scope.temp.MultiplyDIV);
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');


        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCurrency');
                formData.append("currencyid", $scope.temp.currencyid);
                formData.append("txtCurrencyCode", $scope.temp.txtCurrencyCode);
                formData.append("txtCurrencySymbolClass", $scope.temp.txtCurrencySymbolClass);
                formData.append("MultiplyDIV", $scope.temp.MultiplyDIV);
                // formData.append("Divide", $scope.Divide);
                formData.append("txtFactor", $scope.temp.txtFactor);
                formData.append("IsmainCurrency", $scope.temp.IsmainCurrency);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
            
                
                
                $scope.getCurrency();
                $scope.clearForm();
                document.getElementById("txtCurrencyCode").focus();

                // alert($scope.temp.Multiply +'-'+ $scope.temp.Divide);
            }
            else {
                $scope.messageFailure(data.data.message);
                // $scope.temp.Multiply=0;
                // $scope.temp.Divide=0;
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }


     /* ========== GET Currency =========== */
     $scope.getCurrency = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCurrency'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCurrencyD = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCurrency(); --INIT





    /* ============ Edit Button ============= */ 
    $scope.editCurrency = function (id) {
        // alert(id.MULTIPLY);
        document.getElementById("txtCurrencyCode").focus();
        $scope.temp = {
            currencyid:id.CURRENCY_ID,
            txtCurrencyCode: id.CURRENCY_CODE,
            txtCurrencySymbolClass: id.CURRENCY_CLASS,
            // MultiplyDIV: id.MULTIPLY,
            // MultiplyDIV: id.DIVIDE,
            
            txtFactor: Number(id.FACTOR),
        };
        if(id.MULTIPLY == 1){
            $('#exampleRadios1').prop('checked',true);
            $scope.temp.MultiplyDIV="Multiply";
        }
        if(id.DIVIDE == 1){
            $('#exampleRadios2').prop('checked',true);
            $scope.temp.MultiplyDIV="Divide";
        }


        if(id.IS_MAIN == 1){
            $scope.temp.IsmainCurrency=true;
        }

        $scope.editMode = true;
        $scope.index = $scope.post.getCurrencyD.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("txtCurrencyCode").focus();
        $scope.temp={};
        $scope.formTitle = 'Currency';
        $scope.editMode = false;
        // $scope.temp.Multiply=0;
        // $scope.temp.Divide=0;
        $scope.temp.IsmainCurrency=false;
    }


    /* ========== DELETE =========== */
    $scope.deleteCurrency = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'currencyid': id.CURRENCY_ID, 'type': 'deleteCurrency' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCurrencyD.indexOf(id);
		            $scope.post.getCurrencyD.splice(index, 1);
		            console.log(data.data.message)
                    
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




});