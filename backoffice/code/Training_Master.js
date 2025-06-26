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
    $scope.Page = "MISC";
    $scope.PageSub = "TRAINING";
    $scope.PageSub1 = "TRAINING_MASTER";
    $scope.temp.txtLastUpdateDT = new Date();
    $scope.files = [];
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Training_Master_code.php';




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

                // $scope.getTDCategory();
                $scope.getLocations();
                $scope.getProduct();
                // $scope.getTrainingMasters();
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
                formData.append("tmid", $scope.temp.tmid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtDescription", $scope.temp.txtDescription);
                formData.append("ddlSSubCategory", $scope.temp.ddlSSubCategory);
                formData.append("txtTLink", $scope.temp.txtTLink);
                formData.append("txtLogin", $scope.temp.txtLogin);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtCost", $scope.temp.txtCost);
                formData.append("txtMinute", $scope.temp.txtMinute);
                formData.append("ddlZone", $scope.temp.ddlZone);
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearForm();
                $scope.getTrainingMasters();
                $("#ddlLocation").focus();
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






    /* ========== GET TRAINING MASTER =========== */
    $scope.getTrainingMasters = function () {
        $scope.post.getTrainingMasters=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $scope.temp.txtSerarch = undefined;
        $('#ddlSearchCategory').attr('disabled','disabled');
        $('#SpinnerMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getTrainingMasters',
                            'ddlLocation':$scope.temp.ddlLocation,
                            'ddlSearchCategory': $scope.temp.ddlSearchCategory,
                            'FOR':'ADMIN'
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
            $('#SpinnerMainData').hide();
            $('#ddlSearchCategory').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTrainingMasters(); --INIT
    /* ========== GET BUSINESS PROCEDURES =========== */






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
            if($scope.temp.ddlLocation > 0) $scope.getTDCategory();
            if($scope.temp.ddlLocation > 0) $scope.getTrainingMasters();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    

    /* ========== GET CATEGORY =========== */
    $scope.getTDCategory = function () {
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $scope.post.getTDCategory = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getCategories','ddlLocation':$scope.temp.ddlLocation}),
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
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'tdcatid' : $scope.temp.ddlCategory}),
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
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSSubCategories', 'tdsubcatid' : $scope.temp.ddlSubCategory}),
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
    



    /* ========== GET PRODUCT =========== */
    $scope.getProduct = function () {
        $('.spinProduct').show();
        $http({
            method: 'post',
            url: 'code/Products_code.php',
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
            $('.spinProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT
    /* ========== GET PRODUCT =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            $("#ddlLocation").focus();
            $scope.temp.tmid = id.TMID;
            $scope.temp.ddlLocation = (id.LOCID).toString();
            $scope.temp.txtDescription = id.T_DESC;
            $scope.temp.ddlCategory = (id.TDCATID).toString();
            $scope.getTDSubCategory();
            $timeout( () => {
                $scope.temp.ddlSubCategory = (id.TDSUBCATID).toString();
                $scope.getTDSSubCategory();
                $timeout( () => {$scope.temp.ddlSSubCategory = (id.TDSSUBCATID).toString();},500);
            },700);

            $scope.temp.txtTLink = id.T_LINK;
            $scope.temp.txtLogin = id.LOGIN;
            $scope.temp.ddlProduct = id.PRODUCTID > 0 ? (id.PRODUCTID).toString() : '';
            $scope.temp.txtCost = Number(id.T_COST);
            $scope.temp.txtMinute = Number(id.T_MINUTE);
            $scope.temp.ddlZone = id.T_ZONE==='All' ? '' : id.T_ZONE;
            $scope.temp.txtRemark = id.REMARK;
            
            $scope.editMode = true;
            $scope.index = $scope.post.getTrainingMasters.indexOf(id);
        }
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
    
        $scope.editMode = false;
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            var r = confirm("Are you sure want to delete this record!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'TMID': id.TMID, 'type': 'delete' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getTrainingMasters.indexOf(id);
                        $scope.post.getTrainingMasters.splice(index, 1);
                        // console.log(data.data.message)
                        
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
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


    /* ========== MESSAGE =========== */
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').show();
    //     jQuery('.alert-success').delay(5000).slideUp(function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').show();
    //     jQuery('.alert-danger').delay(5000).slideUp(function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }
    /* ========== MESSAGE =========== */




});