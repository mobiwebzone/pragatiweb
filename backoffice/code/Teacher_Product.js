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
    $scope.Page = "TEACHER";
    $scope.PageSub = "TEACHER_PRODUCT";
    $scope.formTitle = 'Teacher Products';
    $scope.temp.txtDisplayColor="#000000";
    
    var url = 'code/Teacher_Product_code.php';


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
                // window.location.assign("dashboard.html");

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getPlans();
                    $scope.getProduct();
                    $scope.getLocations();
                    $scope.getTeacherLocation();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
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

    // =========== SAVE TEACHER PRODUCT ===============
    $scope.saveProduct = function(){
        if($scope.temp.ddlLocation>0 && $scope.temp.ddlTeacher>0 && $scope.temp.ddlProduct>0){

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
                    formData.append("type", 'saveProduct');
                    formData.append("tpid", $scope.temp.tpid);
                    formData.append("ddlLocation", $scope.temp.ddlLocation);
                    formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                    formData.append("ddlProduct", $scope.temp.ddlProduct);
                    formData.append("ddlTeacherLocation", $scope.temp.ddlTeacherLocation);
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
                    $scope.getTeacherProduct();
                    $scope.temp.ddlProduct='';
                    $scope.temp.tpid=0;
                    // $scope.clearForm();
                    document.getElementById("ddlLocation").focus();
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
        else{
            if($scope.temp.ddlProduct>0 && ($scope.temp.ddlLocation==undefined || $scope.temp.ddlTeacher==undefined)){
                alert('Check Fields.');
            }
        }
    }
    
    
    // =========== SAVE TEACHER LOCATION ===============
    $scope.saveLocation = function(){
        if($scope.temp.ddlTeacher>0 && $scope.temp.ddlTeacherLocation>0){

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
                    formData.append("type", 'saveLocation');
                    formData.append("tlid", $scope.temp.tlid);
                    formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                    formData.append("ddlTeacherLocation", $scope.temp.ddlTeacherLocation);
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
                    $scope.getTeacherLocation();
                    $scope.temp.ddlTeacherLocation='';
                    // $scope.clearForm();
                    document.getElementById("ddlLocation").focus();
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
        else{
            if($scope.temp.ddlTeacherLocation>0 && ($scope.temp.ddlTeacher==undefined)){
                alert('Check Fields.');
            }
        }
    }


     /* ========== GET Teacher Products =========== */
     $scope.getTeacherProduct = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherProduct','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
     
    /* ========== GET Teacher Location =========== */
     $scope.getTeacherLocation = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherLocation', 'ddlTeacher':$scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherLocation = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherLocation(); --INIT



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
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    

    
    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacher = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacher();
    
    
    /* ========== GET Product =========== */
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
    // $scope.getProduct(); --INIT




    /* ============ Edit Product ============= */ 
    $scope.editProduct = function (id) {
        document.getElementById("ddlLocation").focus();
        $scope.temp = {
            tpid:id.TPID,
            ddlLocation: (id.LOCID).toString(),
            ddlProduct: (id.PRODUCTID).toString(),
        };
        $timeout(function(){

            $scope.temp.ddlTeacher= (id.TEACHERID).toString();
        },500);
        $scope.editMode = true;
        $scope.index = $scope.post.getTeacherProduct.indexOf(id);
    }
    
    
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlLocation").focus();
        $scope.temp={};
        $scope.formTitle = 'Teacher Product';
        $scope.editMode = false;
    }


    /* ========== DELETE PRODUCT =========== */
    $scope.deleteProduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tpid': id.TPID, 'type': 'deleteProduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTeacherProduct.indexOf(id);
		            $scope.post.getTeacherProduct.splice(index, 1);
                    $scope.clearForm();
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    
    
    /* ========== DELETE LOCATION =========== */
    $scope.deleteLocation = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tlid': id.TLID, 'type': 'deleteLocation' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTeacherLocation.indexOf(id);
		            $scope.post.getTeacherLocation.splice(index, 1);
                    // $scope.clearForm();
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    




    // ========================================================== ADD PLAN ==============================================
    /* ========== GET Plan =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: 'code/LoginApproval_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT




    // =========== SAVE PLAN ===============
    $scope.savePlan = function(){
        if($scope.temp.ddlTeacher>0 && $scope.temp.ddlPlan>0){

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
                    formData.append("type", 'savePlan');
                    formData.append("tplid", $scope.temp.tplid);
                    formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                    formData.append("ddlPlan", $scope.temp.ddlPlan);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {

                    
                    $scope.getTeacherPlans();
                    $scope.temp.ddlPlan='';

                    document.getElementById("ddlPlan").focus();
                    
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
        else{
            if($scope.temp.ddlPlan>0 && ($scope.temp.ddlTeacher==undefined)){
                alert('Check Fields.');
            }
        }
    }


    /* ========== GET Teacher Plans =========== */
    $scope.getTeacherPlans = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherPlans','ddlTeacher' : $scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherPlans();


    /* ========== DELETE PLAN =========== */
    $scope.deletePlan = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tplid': id.TPLID, 'type': 'deletePlan' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTeacherPlans.indexOf(id);
		            $scope.post.getTeacherPlans.splice(index, 1);
                    $scope.temp.ddlPlan='';
                    // $scope.clearForm();
		            // console.log(data.data.message)
                    
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