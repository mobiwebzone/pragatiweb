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
    $scope.PageSub = "GALLERY";
    $scope.files = [];
    $scope.temp.txtETADate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Gallery_Backoffice_code.php';


    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.logo_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview =========*/ 


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
                $scope.LOCID=data.data.locid;
                // window.location.assign("dashboard.html");
                $scope.getCategory();
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





// ========================================================== CATEGORY SECTION ==================================================
    
    // =========== SAVE CATEGORY DATA ==============
    $scope.GET_GCATID = 0;
    $scope.saveCategoryData = function(){
        if($scope.LOCID!==1) return
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Adding...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCategoryData');
                formData.append("gcatid", $scope.temp.gcatid);
                formData.append("txtCategory", $scope.temp.txtCategory);
                formData.append("txtSEQNo", $scope.temp.txtSEQNo);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.GET_GCATID = data.data.GET_GCATID;
                $scope.temp.gcatid = data.data.GET_GCATID;
                // $scope.clearForm();
                $scope.getCategory();
                if($scope.GET_GCATID > 0){
                    $(".ImgCateForm").slideDown("fast");
                    $(".txtQueImage").focus();
                    $scope.getImages();
                }
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
    // =========== SAVE CATEGORY DATA ==============






    /* ========== GET CATEGORY =========== */
    $scope.getCategory = function () {
        $('#SpinnerCategory').show();
        $scope.post.getImages=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCategory'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getCategory = data.data.data;
            }else{
                $scope.post.getCategory=[];
                // console.info(data.data.message);
            }
            $('#SpinnerCategory').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategory(); --INIT
    /* ========== GET CATEGORY =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $(".ddlLocation").focus();
        $scope.GET_GCATID = id.GCATID;
        $scope.temp.gcatid = id.GCATID;
        $scope.temp.txtCategory = id.CATEGORY;
        $scope.temp.txtSEQNo = Number(id.SEQNO);

        if($scope.GET_GCATID > 0){
            $(".ImgCateForm").slideDown("fast");
            $(".txtQueImage").focus();
            $scope.getImages();
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getCategory.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtCategory").focus();
        $scope.temp={};
        $scope.temp.existingCatImage='';
        $scope.logo_src = '';
        $scope.post.getImages=[];
        $scope.clearImagesForm();
        $scope.editMode = false;
        $(".ImgCateForm").slideUp("fast");
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        if($scope.LOCID!==1) return
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'gcatid': id.GCATID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCategory.indexOf(id);
		            $scope.post.getCategory.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    

    // ========================================================== CATEGORY SECTION ==================================================









    // ========================================================== IMAGE SECTION ==================================================
    
    /* ========== ADD IMAGE =========== */
    $scope.AddImages = function(){
        if($scope.LOCID!==1) return
        $(".btnAdd").attr('disabled', 'disabled');
        $(".btnAdd").html('<i class="fa fa-plus mr-1"></i> Adding...');
        $(".btnUpd").attr('disabled', 'disabled');
        $(".btnUpd").text('Updating...');

        $scope.temp.txtCatImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'AddImages');
                formData.append("gimgid", $scope.temp.gimgid);
                formData.append("GCATID", $scope.GET_GCATID);
                formData.append("txtIMG_Caption", $scope.temp.txtIMG_Caption);
                formData.append("txtIMG_SEQNo", $scope.temp.txtIMG_SEQNo);
                formData.append("txtCatImage", $scope.temp.txtCatImage);
                formData.append("existingCatImage", $scope.temp.existingCatImage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getImages();
                $scope.getCategory();
                $scope.clearImagesForm();
                
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btnAdd").removeAttr('disabled');
            $(".btnAdd").html('<i class="fa fa-plus mr-1"></i> ADD');
            $(".btnUpd").removeAttr('disabled');
            $(".btnUpd").text('UPDATE');
        });
    }
    /* ========== ADD IMAGE =========== */





    /* ========== GET IMAGES =========== */
    $scope.getImages = function () {
        $('#SpinnerImages').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getImages', 'GCATID' : $scope.GET_GCATID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getImages = data.data.data;
            }else{
                $scope.post.getImages=[];
                // console.info(data.data.message);
            }
            $('#SpinnerImages').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getImages();
    /* ========== GET IMAGES =========== */




     /* ============ Edit Button ============= */ 
    $scope.editImagesForm = function (id) {
        if($scope.LOCID!==1) return
         $("#txtIMG_Caption").focus();

         $scope.temp.gimgid = id.GIMGID;
         $scope.temp.existingCatImage = id.IMAGE;
         $scope.temp.txtIMG_Caption = id.IMAGE_CAPTION;
         $scope.temp.txtIMG_SEQNo = Number(id.SEQNO);

        /*########### IMG #############*/
        if(id.IMAGE != ''){
            $scope.logo_src='gallery_images/'+id.IMAGE;
        }else{
            $scope.logo_src='gallery_images/default.png';
        }

        $scope.editModeImages = true;
        $scope.index = $scope.post.getImages.indexOf(id);

    }
    /* ============ Edit Button ============= */ 


    /* ============ CLEAR Button ============= */ 
    $scope.clearImagesForm = ()=>{
        $scope.temp.gimgid = '';
        $scope.temp.existingCatImage = '';
        $scope.temp.txtIMG_Caption = '';
        $scope.temp.txtIMG_SEQNo = '';
        $scope.logo_src = '';
        $scope.editModeImages = false;
        $scope.files = [];
        angular.element('#txtCatImage').val(null);
    }
    /* ============ CLEAR Button ============= */ 


    /* ========== DELETE =========== */
    $scope.deleteImages = function (id) {
        if($scope.LOCID!==1) return
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'gimgid': id.GIMGID, 'type': 'deleteImages' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getImages.indexOf(id);
		            $scope.post.getImages.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getCategory();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    // ========================================================== IMAGE SECTION ==================================================





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