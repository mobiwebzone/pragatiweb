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
    $scope.Page = "PRODUCTS";
    $scope.PageSub = "STATIC_PAGE_CAROUSEL_MASTER";
    $scope.files = [];
    $scope.imageDimensionIsCorrect = false;
    $scope.imageHeight = 0;
    $scope.imageWidth = 0;
    
    var url = 'code/Static_Page_Carousel_Master.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.PAGELIST = ['ABOUT US','CAREERS','BLOG','WORKING HOURS','HOLIDAYS','GALLERY','ANNOUNCEMENTS','STUDENT FINAL RESULT','REVIEWS','PRIVACY POLICY','COOKIE POLICY','FRANCHIES','CONTACT US'];

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
                $scope.imageDimensionIsCorrect = (img.width==1920 && img.height==295) ? false : true;
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
                    $scope.imageDimensionIsCorrect = (this.videoWidth && this.videoHeight<=295) ? false : true;
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
    // $scope.UploadImage = function (element) {
    //     $scope.currentFile = element.files[0];
    //     var reader = new FileReader();
    //     let img = new Image()
    //     img.src = window.URL.createObjectURL(element.files[0])
    //     reader.onload = function (event) {
    //         $scope.Img_src = event.target.result;
    //         // $scope.imageDimensionIsCorrect = (img.width==1920 && img.height==589) ? false : true;
    //         $scope.imageDimensionIsCorrect = (img.width==1920 && img.height==295) ? false : true;
    //         $scope.imageWidth = img.width;
    //         $scope.imageHeight = img.height;
    //         $scope.$apply(function ($scope) {
    //             $scope.files = element.files;
    //         });
    //     }
    //     reader.readAsDataURL(element.files[0]);
    // }
    $scope.clearImg_src=()=>{
        $scope.Img_src='';
        $scope.files = [];
        $scope.imageDimensionIsCorrect = false;
        $scope.imageHeight = 0;
        $scope.imageWidth = 0;
    }
    /*========= Image Preview =========*/   

    $scope.objectTypeChange =  () =>{
        $scope.clearImg_src();
        angular.element('#pictureUpload').val(null);

        var objtype = !$scope.temp.ddlDisplayType?'':$scope.temp.ddlDisplayType;
        $scope.objectAccept = objtype == 'VIDEO' ? 'video/*' : '.jpg, .jpeg, .png';
        $scope.maxSize = objtype == 'VIDEO' ? '' : '(Max : 1mb)';
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
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    // alert($scope.userrole);
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    // $scope.getLocations();
                    // $scope.getProduct();
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


    /* ========== Save Paymode =========== */
    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.txtValidFrom.toLocaleString('sv-SE'));
        $scope.temp.pictureUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("spcid", $scope.temp.spcid);
                formData.append("ddlPage", $scope.temp.ddlPage);
                formData.append("ddlDisplayType", $scope.temp.ddlDisplayType);
                formData.append("pictureUpload", $scope.temp.pictureUpload);
                formData.append("existingPictureUpload", $scope.temp.existingPictureUpload);
                formData.append("txtCaption", $scope.temp.txtCaption);
                formData.append("txtValidFrom", $scope.temp.txtValidFrom.toLocaleString('sv-SE'));
                formData.append("txtValidTo", $scope.temp.txtValidTo.toLocaleString('sv-SE'));
                formData.append("txtInterval", $scope.temp.txtInterval);
                formData.append("txtSeqno", $scope.temp.txtSeqno);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getCarouselMaster();
                $scope.clear();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET CAROUSEL =========== */
     $scope.getCarouselMaster = function () {
        $scope.post.getCarouselMaster = [];
        if(!$scope.temp.ddlPage || $scope.temp.ddlPage=='')return;
        $('#SpinnerData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getCarouselMaster');
                formData.append("ddlPage", $scope.temp.ddlPage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCarouselMaster = data.data.success ? data.data.data : [];
            $('#SpinnerData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCarouselMaster(); --INIT
    /* ========== GET CAROUSEL =========== */


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            spcid:id.SPCID,
            ddlPage: id.PAGENAME,
            ddlDisplayType: id.DISPLAY_TYPE,
            existingPictureUpload: id.PIC,
            txtCaption : id.PIC_CAPTION,
            txtValidFrom : new Date(id.VALID_FROM),
            txtValidTo : new Date(id.VALID_UPTO),
            txtInterval : Number(id.PIC_INTERVAL),
            txtSeqno : Number(id.SEQNO)
        };
        $scope.Img_src= id.PIC != '' ? 'images/static_page_carousel/'+id.PIC : '';
        $scope.editMode = true;
        $scope.index = $scope.post.getCarouselMaster.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // $scope.temp={};
        $scope.temp.spcid= '';
        $scope.ddlDisplayType = '';
        $scope.temp.existingPictureUpload= '';
        $scope.temp.txtCaption = '';
        $scope.temp.txtValidFrom = '';
        $scope.temp.txtValidTo = '';
        $scope.temp.txtInterval = '';
        $scope.temp.txtSeqno = '';

        $scope.editMode = false;
        $scope.Img_src = '';
        $scope.files = [];
        angular.element('#pictureUpload').val(null);
        $scope.imageDimensionIsCorrect = false;
        $scope.imageHeight = 0;
        $scope.imageWidth = 0;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SPCID': id.SPCID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCarouselMaster.indexOf(id);
		            $scope.post.getCarouselMaster.splice(index, 1);
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