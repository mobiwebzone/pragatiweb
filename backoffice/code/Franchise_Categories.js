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
    $scope.Page = "LA";
    $scope.PageSub = "FRANCHISEMANAGEMENT";
    $scope.PageSub2 = "FRANCHISECATEGORIES";
    $scope.temp.txtETADate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Franchise_Categories.php';




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

                $scope.getCategories();
                // $scope.getLocations();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    

    // =========== SAVE DATA ==============
    $scope.saveData = function(){
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
                formData.append("type", 'saveData');
                formData.append("lmcid", $scope.temp.lmcid);
                formData.append("txtCategory", $scope.temp.txtCategory);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.lmcid=data.data.GET_LMCID;
                // $scope.clearForm();
                $scope.getCategories();
                if($scope.temp.lmcid > 0){
                    $scope.getSubCategories();
                }
                $timeout(()=>{$("#txtSubCategory").focus();},500);
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






    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCategories'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.success?data.data.data:[];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories(); --INIT
    /* ========== GET CATEGORIES =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        // console.log(id);
        $("#txtCategory").focus();

        $scope.temp.lmcid = id.LMCID;
        $scope.temp.txtCategory = id.CATEGORY;

        if($scope.temp.lmcid > 0){
            $scope.getSubCategories();
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getCategories.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtCategory").focus();
        $scope.temp={};
        $scope.post.getSubCategories = [];
        $scope.editMode = false;

        $scope.temp.lscid = '';
        $scope.temp.txtSubCategory = '';
        $scope.editModeSubHead = false;
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'LMCID': id.LMCID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCategories.indexOf(id);
		            $scope.post.getCategories.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SUB CATEGORIES SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // =========== SAVE SUB HEAD DATA ==============
    $scope.saveDataSubHead = function(){
        $(".btn-save-SubHead").attr('disabled', 'disabled');
        $(".btn-save-SubHead").text('Saving...');
        $(".btn-update-SubHead").attr('disabled', 'disabled');
        $(".btn-update-SubHead").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataSubHead');
                formData.append("lscid", $scope.temp.lscid);
                formData.append("lmcid", $scope.temp.lmcid);
                formData.append("txtSubCategory", $scope.temp.txtSubCategory);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormSubHead();
                $scope.getSubCategories();
                $scope.messageSuccess(data.data.message);
                $scope.getCategories();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-SubHead').removeAttr('disabled');
            $(".btn-save-SubHead").text('SAVE');
            $('.btn-update-SubHead').removeAttr('disabled');
            $(".btn-update-SubHead").text('UPDATE');
        });
    }
    // =========== SAVE SUB HEAD DATA ==============




    
    
    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function () {
        $('#spinSubHead').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSubCategories', 'lmcid' : $scope.temp.lmcid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubCategories = data.data.success?data.data.data:[];
            $('#spinSubHead').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */





    /* ============ Edit Sub Head Button ============= */ 
    $scope.editFormSubHead = function (id) {
        $("#txtSubCategory").focus();
        
        $scope.temp.lscid = id.LSCID;
        // $scope.temp.lmcid = id.LMCID;
        $scope.temp.txtSubCategory = id.SUBCATEGORY;
        
        $scope.editModeSubHead = true;
        $scope.index = $scope.post.getSubCategories.indexOf(id);
    }
    /* ============ Edit Sub Head Button ============= */ 
    
    


    /* ============ Clear Sub HEad Form =========== */ 
    $scope.clearFormSubHead = function(){
        $("#txtSubCategory").focus();
        $scope.temp.lscid = '';
        $scope.temp.txtSubCategory = '';
        $scope.editModeSubHead = false;
    }
    /* ============ Clear Sub HEad Form =========== */ 




    /* ========== DELETE SUB HEAD =========== */
    $scope.deleteSubHead = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'LSCID': id.LSCID, 'type': 'deleteSubHead' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getSubCategories.indexOf(id);
		            $scope.post.getSubCategories.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getCategories();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE SUB HEAD =========== */



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
            // $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            // if($scope.temp.ddlLocation > 0) $scope.getExpHeads();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




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