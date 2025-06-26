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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "TEST_TO_PRODUCT";
    $scope.chkProduct = [];
    $scope.lengthOfProducts = 0;
    
    var url = 'code/Test_To_Product_code.php';



    // =============== Open Categories Page =============
    $scope.OpenCategory = function (id) {
        window.open('Question_Categories.html?SEC_ID='+id.SECID,"");
    }
    // =============== Open Categories Page =============






    // =============== Check Session =============
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
                // window.location.assign("dashboard.html");

                $scope.getAllSelectedProduct();
                $scope.getProduct();
                $scope.getTestMaster();
            }
            else {
                // window.location.assign('index.html#!/login')
                // alert
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    // =============== Check Session =============
    
    
    
    
    
    /* ========== GET TEST MASTER =========== */
    $scope.getTestMaster = function () {
        $http({
            method: 'post',
            url: 'code/Test_Master_code.php',
            data: $.param({ 'type': 'getTestMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTestMaster = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestMaster(); --INIT
    /* ========== GET TEST MASTER =========== */




    /* ========== GET PRODUCTS =========== */
    $scope.getProduct = function () {
        $http({
            method: 'post',
            url: 'code/Products_code.php',
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
    // $scope.getProduct(); --INIT
    /* ========== GET PRODUCTS =========== */
    




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
                // formData.append("secid", $scope.temp.secid);
                formData.append("ddlTest", $scope.temp.ddlTest);
                formData.append("chkProduct", $scope.chkProduct);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.messageSuccess(data.data.message);
                
                $scope.clearForm();
                $scope.getAllSelectedProduct();
                document.getElementById("ddlTest").focus();
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



     /* ========== GET PRODUCT BY TEST =========== */
     $scope.getProductByTest = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getProductByTest', 'ddlTest' : $scope.temp.ddlTest}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.chkProduct = data.data.PRODUCTID;

                // alert(data.data.ProductSUM);
                if(data.data.ProductSUM > 0){
                    $scope.temp.testid = $scope.temp.ddlTest;
                }else{
                    $scope.temp.testid = 0;
                }
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProductByTest();
    /* ========== GET PRODUCT BY TEST =========== */
    
    
    
    /* ========== GET ALL SELECTED PRODUCT =========== */
     $scope.getAllSelectedProduct = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAllSelectedProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAllSelectedProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAllSelectedProduct(); --INIT
    /* ========== GET ALL SELECTED PRODUCT =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        document.getElementById("ddlTest").focus();

        $scope.temp.testid=id.TESTID;
        $scope.temp.ddlTest=(id.TESTID).toString();
        
        if($scope.temp.ddlTest > 0){$scope.getProductByTest();}
        $scope.editMode = true;
        $scope.index = $scope.post.getAllSelectedProduct.indexOf(id);

        // var newData = $scope.post.getAllSelectedProduct.filter((x) => {
        //     return x === id;
        // });

        // console.log(newData);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlTest").focus();
        $scope.temp={};
        // $scope.chkProduct = [];
        $scope.lengthOfProducts = $scope.chkProduct.length;
        // alert($scope.lengthOfProducts);
        $scope.chkProduct = Array.apply(null, Array($scope.lengthOfProducts)).map(() => '0');
        $scope.editMode = false;
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'testid': id.TESTID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getAllSelectedProduct.indexOf(id);
		            $scope.post.getAllSelectedProduct.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearForm();
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