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
    $scope.editModeCat = false;
    $scope.editModeSubCat = false;
    $scope.editModeSSubCat = false;
    $scope.Page = "MISC";
    $scope.PageSub = "TODO";
    $scope.PageSub1 = "TODO_CAT";
    
    
    var url = 'code/ToDoCategories_code.php';




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

                $scope.getLocations();
                // $scope.getCategories();
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





    // ##################################################### CATEGORY SECTION START #####################################################

    // =========== SAVE DATA ==============
    $scope.GetTDCatid = 0;
    $scope.saveCategory = function(){
        $scope.post.getSubCategories = [];
        $scope.post.getSSubCategories = [];
        $(".btnSaveCat").attr('disabled', 'disabled');
        $(".btnSaveCat").text('Saving...');
        $(".btnUpdCat").attr('disabled', 'disabled');
        $(".btnUpdCat").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCategory');
                formData.append("tdcatid", $scope.temp.tdcatid);
                formData.append("txtCategory", $scope.temp.txtCategory);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.GetTDCatid = data.data.TDCATID;
                $scope.temp.tdcatid = data.data.TDCATID;
                // alert(data.data.CATID);
                $scope.messageSuccess(data.data.message);
                
                $scope.getCategories();
                // $scope.getSubCategories();
                // $scope.clearFormCat();
                $scope.temp.txtCategory='';
                $("#txtSubCategory").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnSaveCat').removeAttr('disabled');
            $(".btnSaveCat").text('SAVE');
            $('.btnUpdCat').removeAttr('disabled');
            $(".btnUpdCat").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function () {
        $scope.post.getCategories=$scope.post.getSubCategories=$scope.post.getSSubCategories=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCategories','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getCategories = data.data.data;
            }else{
                $scope.post.getCategories = [];
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories(); --INIT
    /* ========== GET CATEGORIES =========== */


    /* ============ Edit Button ============= */ 
    $scope.editFormCat = function (id) {
        $("#txtCategory").focus();

        $scope.GetTDCatid = id.TDCATID;
        $scope.temp.tdcatid=id.TDCATID;
        $scope.temp.txtCategory= id.CATEGORY;
       
        $scope.editModeCat = true;
        $scope.index = $scope.post.getCategories.indexOf(id);

        $scope.getSubCategories();

        $scope.clearFormSubCat();
        $scope.clearFormSSubCat();
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormCat = function(){
        // document.getElementById("txtCategory").focus();
        $("#txtCategory").focus();
        $scope.temp.tdcatid = 0;
        $scope.GetTDCatid = 0;
        $scope.temp.txtCategory = '';
        $scope.editModeCat = false;
        // $scope.post.getCategories=[];

        $scope.clearFormSubCat();
        $scope.clearFormSSubCat();
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteCat = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tdcatid': id.TDCATID, 'type': 'deleteCat' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCategories.indexOf(id);
		            $scope.post.getCategories.splice(index, 1);
                    // $scope.getCategories();
                    $scope.getSubCategories();
                    $scope.getSSubCategories();

                    $scope.clearFormCat();
                    $scope.clearFormSubCat();
                    $scope.clearFormSSubCat();
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

    // ##################################################### CATEGORY SECTION END #####################################################








    // ##################################################### SUB CATEGORY SECTION START #####################################################

    // =========== SAVE DATA ==============
    $scope.GetTDSubCatid = 0;
    $scope.saveSubCategory = function(){
        $scope.post.getSSubCategories = [];
        $(".btn-save-subcat").attr('disabled', 'disabled');
        $(".btn-save-subcat").text('Saving...');
        $(".btn-update-subcat").attr('disabled', 'disabled');
        $(".btn-update-subcat").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveSubCategory');
                formData.append("tdsubcatid", $scope.temp.tdsubcatid);
                formData.append("GetTDCatid", $scope.GetTDCatid);
                formData.append("txtSubCategory", $scope.temp.txtSubCategory);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.GetTDSubCatid = data.data.TDSUBCATID;
                $scope.temp.tdsubcatid = data.data.TDSUBCATID;
                // alert(data.data.CATID);
                $scope.messageSuccess(data.data.message);
                
                $scope.getSubCategories();
                // $scope.clearFormSubCat();
                $scope.temp.txtSubCategory = '';
                $("#txtSSubCategory").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-subcat').removeAttr('disabled');
            $(".btn-save-subcat").text('SAVE');
            $('.btn-update-subcat').removeAttr('disabled');
            $(".btn-update-subcat").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function () {
        if($scope.GetTDCatid > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getSubCategories', 'tdcatid' : $scope.GetTDCatid}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getSubCategories = data.data.data;
                    $timeout(function () { 
                        window.location.hash = '#SUBCAT';
                    },500);
                }else{
                    $scope.post.getSubCategories = [];
                }

            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */


    /* ============ Edit Button ============= */ 
    $scope.editFormSubCat = function (id) {
        // document.getElementById("txtSubCategory").focus();
        $("#txtSubCategory").focus();

        $scope.GetTDSubCatid = id.TDSUBCATID;
        $scope.temp.tdsubcatid=id.TDSUBCATID;
        $scope.temp.txtSubCategory= id.SUBCATEGORY;
       
        $scope.editModeSubCat = true;
        $scope.index = $scope.post.getSubCategories.indexOf(id);

        $scope.getSSubCategories();
        $scope.clearFormSSubCat();
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormSubCat = function(){
        // document.getElementById("txtSubCategory").focus();
        $("#txtSubCategory").focus();
        $scope.GetTDSubCatid = 0;
        $scope.temp.tdsubcatid = 0;
        $scope.temp.txtSubCategory = '';
        $scope.editModeSubCat = false;
        // $scope.post.getSubCategories=[];

        $scope.clearFormSSubCat();
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteSubCat = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tdsubcatid': id.TDSUBCATID, 'type': 'deleteSubCat' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getSubCategories.indexOf(id);
		            $scope.post.getSubCategories.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getSSubCategories();
                    $scope.clearFormSubCat();
                    $scope.clearFormSSubCat();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

    // ##################################################### SUB CATEGORY SECTION END #####################################################









    // ##################################################### SUB SUBCATEGORY SECTION START #####################################################

    // =========== SAVE DATA ==============
    $scope.saveSSubCategory = function(){
        $(".btn-save-ssubcat").attr('disabled', 'disabled');
        $(".btn-save-ssubcat").text('Saving...');
        $(".btn-update-ssubcat").attr('disabled', 'disabled');
        $(".btn-update-ssubcat").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveSSubCategory');
                formData.append("tdssubcatid", $scope.temp.tdssubcatid);
                formData.append("GetTDSubCatid", $scope.GetTDSubCatid);
                formData.append("txtSSubCategory", $scope.temp.txtSSubCategory);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                
                $scope.getSSubCategories();
                // $scope.clearFormSSubCat();
                $scope.temp.txtSSubCategory = '';
                $scope.temp.tdssubcatid = 0;
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-ssubcat').removeAttr('disabled');
            $(".btn-save-ssubcat").text('SAVE');
            $('.btn-update-ssubcat').removeAttr('disabled');
            $(".btn-update-ssubcat").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    /* ========== GET SUB-SUBCATEGORIES =========== */
    $scope.getSSubCategories = function () {
        if($scope.GetTDSubCatid > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getSSubCategories', 'tdsubcatid' : $scope.GetTDSubCatid}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getSSubCategories = data.data.data;
                    
                    $timeout(function () { 
                        window.location.hash = '#SSUBCAT';
                    },500);
                }else{
                    $scope.post.getSSubCategories = [];
                }
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getSSubCategories();
    /* ========== GET SUB-SUBCATEGORIES =========== */


    /* ============ Edit Button ============= */ 
    $scope.editFormSSubCat = function (id) {
        $("#txtSSubCategory").focus();
        $scope.temp.tdssubcatid=id.TDSSUBCATID;
        $scope.temp.txtSSubCategory= id.SSUBCATEGORY;
       
        $scope.editModeSSubCat = true;
        $scope.index = $scope.post.getSSubCategories.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormSSubCat = function(){
        $("#txtSSubCategory").focus();
        $scope.temp.tdssubcatid = 0;
        $scope.temp.txtSSubCategory = '';
        $scope.editModeSSubCat = false;

        // $scope.post.getSSubCategories=[];
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteSSubCat = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tdssubcatid': id.TDSSUBCATID, 'type': 'deleteSSubCat' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getSSubCategories.indexOf(id);
		            $scope.post.getSSubCategories.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormSSubCat();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

    // ##################################################### SUB SUBCATEGORY SECTION END #####################################################



    
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
            if($scope.temp.ddlLocation > 0) $scope.getCategories();
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