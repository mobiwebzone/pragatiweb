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
    $scope.formTitle = 'Products';
    
    var url = 'code/Sub_Products_code.php';


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
            $scope.userid=data.data.userid;
            $scope.userFName=data.data.userFName;
            $scope.userLName=data.data.userLName;
            $scope.userrole=data.data.userrole;
            $scope.USER_LOCATION=data.data.LOCATION;

            
            if (data.data.success) {
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


    $scope.saveSubProduct = function(){
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
                formData.append("type", 'saveSubProduct');
                formData.append("sub_productid", $scope.temp.sub_productid);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtSubProduct", $scope.temp.txtSubProduct);
                formData.append("txtSubProductDesc", $scope.temp.txtSubProductDesc);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.getSubProduct();
                $scope.clearForm();
                document.getElementById("ddlProduct").focus();
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


     /* ========== GET Sub Products =========== */
     $scope.getSubProduct = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSubProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getSubProduct();


     /* ========== GET Products =========== */
     $scope.getProduct = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getProduct();




    /* ============ Edit Button ============= */ 
    $scope.editSubProduct = function (id) {
        document.getElementById("ddlProduct").focus();
        $scope.formTitle = 'Update Sub Product';
        $scope.temp = {
            sub_productid:id.SUB_PRODUCT_ID,
            txtSubProduct: id.SUB_PRODUCT,
            txtSubProductDesc: id.SUB_PRODUCT_DESC,
        };
        $scope.temp.ddlProduct=(id.PRODUCT_ID).toString();

        $scope.editMode = true;
        $scope.index = $scope.post.getSubProduct.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlProduct").focus();
        $scope.temp={};
        $scope.formTitle = 'Sub Product';
        $scope.editMode = false;
    }


    /* ========== DELETE =========== */
    $scope.deleteSubProduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'sub_productid': id.SUB_PRODUCT_ID, 'type': 'deleteSubProduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getSubProduct.indexOf(id);
		            $scope.post.getSubProduct.splice(index, 1);
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
                    window.location.assign('../index.html#!/login')
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