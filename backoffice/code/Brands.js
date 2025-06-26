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
    
    var url = 'code/Brands.php';

    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {

        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        let img = new Image()
        img.src = window.URL.createObjectURL(element.files[0])
        reader.onload = function (event) {

            $scope.Img_src = event.target.result;
            if (element.files[0].type.startsWith('image')) {
                // $scope.imageDimensionIsCorrect = (img.width==1920 && img.height==589) ? false : true;
                $scope.imageDimensionIsCorrect = ((img.width==400 || img.width==0) && (img.height==77 || img.height==0)) ? false : true;
                $scope.imageWidth = img.width;
                $scope.imageHeight = img.height;
                $scope.$apply(function ($scope) {
                    $scope.files = element.files;
                });
            } else if (element.files[0].type.startsWith('video')) {
                // Handle video
                var video = document.createElement('video');
                video.src = event.target.result;
                video.addEventListener('loadedmetadata', function() {
                    // $scope.imageDimensionIsCorrect = (this.videoWidth && this.videoHeight<=589) ? false : true;
                    $scope.imageDimensionIsCorrect = (this.videoWidth) ? false : true;
                    $scope.imageWidth = this.videoWidth;
                    $scope.imageHeight = this.videoHeight;
                    $scope.$apply(function ($scope) {
                        $scope.files = element.files;
                    });
                });
            }
            
        }
        reader.readAsDataURL(element.files[0]);
    }

    $scope.clearImg_src=()=>{
        $scope.Img_src='';
        $scope.files = [];
        $scope.imageDimensionIsCorrect = false;
        $scope.imageHeight = 0;
        $scope.imageWidth = 0;
    }

    $scope.setMyOrderBY = function (COL) {
    $scope.myOrderBY = COL == $scope.myOrderBY ? `-${COL}` : $scope.myOrderBY == `-${COL}` ? (myOrderBY = COL) : (myOrderBY = `-${COL}`);
    // console.log($scope.myOrderBY);
    };

    

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
                $scope.locid=data.data.locid;
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    $scope.getBrands();
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $scope.temp.logoUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("brandid", $scope.temp.BRANDID);
                formData.append("txtBrandName", $scope.temp.txtBrandName);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("logoUpload", $scope.temp.logoUpload);
                formData.append("existingLogoUpload", $scope.temp.existingLogoUpload);
                formData.append("txtLogoDesc", $scope.temp.txtLogoDesc);
                formData.append("txtContactPerson", $scope.temp.txtContactPerson);
                formData.append("txtContactNumber", $scope.temp.txtContactNumber);
                formData.append("txtContactAddress", $scope.temp.txtContactAddress);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getBrands();
                $scope.clearForm();
                
                document.getElementById("txtBrandName").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET Countries =========== */
     $scope.getBrands = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getBrands'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBrands = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBrands(); --INIT
    


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("txtBrandName").focus();
        $scope.temp = {
            BRANDID : id.BRANDID,
            txtBrandName : id.BRANDNAME,
            ddlLocation : id.LOCID.toString(),
            // logoUpload : id.LOGO,
            existingLogoUpload : id.LOGO,
            txtLogoDesc : id.LOGO_DESC,
            txtContactPerson : id.CONTACT_PERSON,
            txtContactNumber : id.CONTACT_NUMBER,
            txtContactAddress : id.CONTACT_ADDRESS,
        };
        $scope.Img_src= id.LOGO != '' ? 'images/brand/'+id.LOGO : '';

        $scope.editMode = true;
        $scope.index = $scope.post.getBrands.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("txtBrandName").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.clearImg_src();
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
                data: $.param({ 'BRANDID': id.BRANDID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getBrands.indexOf(id);
		            $scope.post.getBrands.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearForm();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            // if($scope.temp.ddlLocation > 0) $scope.getAssignedData();
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