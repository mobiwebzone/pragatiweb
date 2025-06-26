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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "PRODUCTS";
    $scope.PageSub = "PRO_MATERIAL_MASTER";
    $scope.formTitle = 'Product Material Master';
    $scope.files = [];
    
    var url = 'code/ProductMaterialsMaster.php';

    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.Img_src = event.target.result;
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    $scope.clearImg_src=()=>{
        $scope.Img_src='';
        $scope.files = [];
        angular.element('#pictureUpload').val(null);
    }
    /*========= Image Preview =========*/   

    $scope.objectTypeChange =  () =>{
        $scope.clearImg_src();
        var objtype = !$scope.temp.ddlMaterialType?'':$scope.temp.ddlMaterialType;
        $scope.objectAccept = objtype == 'VIDEO' ? 'video/*' : objtype == 'E-BOOK' ? 'application/pdf' : '.jpg, .jpeg, .png';
        $scope.maxSize = objtype == 'VIDEO' ? '' : objtype == 'PDF' ? '(Max : 2mb)' : '(Max : 1mb)';
    }

    $scope.prints = () =>{
        setTimeout(function() {
            window.print();
        }, 1000);
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
                    $scope.getLocations();
                    $scope.getProduct();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
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

    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $scope.temp.pictureUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("matid", $scope.temp.matid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlMaterialType", $scope.temp.ddlMaterialType);
                formData.append("txtPublishDT", $scope.temp.txtPublishDT.toLocaleString('sv-SE'));
                formData.append("txtTitle", $scope.temp.txtTitle);
                formData.append("pictureUpload",$scope.temp.pictureUpload);
                formData.append("existingPictureUpload", $scope.temp.existingPictureUpload);
                formData.append("txtBuyLink", $scope.temp.txtBuyLink);
                formData.append("txtSEQNO", $scope.temp.txtSEQNO);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getProductMaterials();
                $scope.clearForm();
                
                $("#ddlProduct").focus();
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


     /* ========== GET DATA =========== */
     $scope.getProductMaterials = function () {
        $scope.post.getProductMaterials=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlProduct || $scope.temp.ddlProduct<=0 || !$scope.temp.ddlPlan || $scope.temp.ddlPlan<=0)return;
         $('#SpinMainData').show();
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getProductMaterials',
                            'ddlProduct':$scope.temp.ddlProduct,
                            'ddlLocation':$scope.temp.ddlLocation,
                            'ddlPlan':$scope.temp.ddlPlan
                        }),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                $scope.post.getProductMaterials = data.data.success?data.data.data:[];
                $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    



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
    // $scope.getProduct(); --INIT



     /* ========== GET Products Plans =========== */
     $scope.getProductPlans = function () {
        $scope.post.getProductPlans = [];
        if(!$scope.temp.ddlProduct || $scope.temp.ddlProduct<=0) return;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getProductPlans','ddlProduct':$scope.temp.ddlProduct}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProductPlans = data.data.success?data.data.data:[];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT


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
            // if($scope.temp.ddlLocation > 0) $scope.getGrades();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $("#ddlProduct").focus();
        $scope.temp = {
            matid:id.MATID,
            ddlLocation:(id.LOCID).toString(),
            ddlProduct:(id.PDMID).toString(),
            ddlMaterialType:id.MATTYPE,
            txtPublishDT:new Date(id.PUBDATE),
            txtTitle:id.TITLE,
            pictureUpload:id.MATIMG,
            existingPictureUpload:id.MATIMG,
            txtBuyLink:id.BUYLINK,
            txtSEQNO:Number(id.SEQNO)
        };

        if($scope.temp.ddlProduct>0){
            $scope.getProductPlans();
            $timeout(()=>{$scope.temp.ddlPlan = id.PLANID.toString()},1000);
        }

        $scope.Img_src= id.MATIMG != '' ? 'images/product_materials/'+id.MATIMG : '';
        $scope.editMode = true;
        $scope.index = $scope.post.getProductMaterials.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlProduct").focus();
        // $scope.temp={};
        $scope.temp.matid=0;
        $scope.temp.ddlMaterialType='';
        $scope.temp.txtPublishDT='';
        $scope.temp.txtTitle='';
        $scope.temp.pictureUpload='';
        $scope.temp.existingPictureUpload='';
        $scope.temp.txtBuyLink='';
        $scope.temp.txtSEQNO='';
        $scope.editMode = false;
        $scope.Img_src = '';
        $scope.files = [];
        angular.element('#pictureUpload').val(null);
    }



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'MATID': id.MATID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getProductMaterials.indexOf(id);
		            $scope.post.getProductMaterials.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearForm();
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



    /* ========== MESSAGE =========== */
    $scope.messageSuccess = function (msg) {
        jQuery('#myToast > .toast-body > samp').html(msg);
        jQuery('#myToast').toast('show');
        jQuery('#myToast > .toast-header').removeClass('bg-danger').addClass('bg-success');
        jQuery('#myToastMain').animate({bottom: '50px'});
        setTimeout(function() {
            jQuery('#myToastMain').animate({bottom: "-80px"});
            jQuery('#myToast > .toast-body > samp').html('-');
        }, 5000 );
    }

    $scope.messageFailure = function (msg) {
        jQuery('#myToast > .toast-body > samp').html(msg);
        jQuery('#myToast').toast('show');
        jQuery('#myToast > .toast-header').removeClass('bg-success').addClass('bg-danger');
        jQuery('#myToastMain').animate({bottom: '50px'});
        setTimeout(function() {
            jQuery('#myToastMain').animate({bottom: "-80px"});
            jQuery('#myToast > .toast-body > samp').html('-');
        }, 5000 );
    }
    /* ========== MESSAGE =========== */  




});