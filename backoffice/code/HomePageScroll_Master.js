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
    $scope.Page = "SETTING";
    $scope.PageSub = "HOME_SCROLL_MASTER";
    $scope.files = [];
    
    var url = 'code/HomePageScroll_Master.php';

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
        var objtype = !$scope.temp.ddlObjType?'':$scope.temp.ddlObjType;
        $scope.objectAccept = objtype == 'IMAGE' ? '.jpg, .jpeg, .png' : objtype == 'VIDEO' ? 'video/*' : objtype == 'PDF' ? 'application/pdf' : '';
        $scope.maxSize = objtype == 'IMAGE' ? '(Max : 1mb)' : objtype == 'VIDEO' ? '' : objtype == 'PDF' ? '(Max : 2mb)' : '';
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
                    $scope.getScrollData();
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
                formData.append("psid", $scope.temp.psid);
                formData.append("ddlObjType",$scope.temp.ddlObjType);
                formData.append("pictureUpload",$scope.temp.pictureUpload);
                formData.append("existingPictureUpload", $scope.temp.existingPictureUpload);
                formData.append("txtTitle",$scope.temp.txtTitle);
                formData.append("txtDesc",$scope.temp.txtDesc);
                formData.append("txtLink",$scope.temp.txtLink);
                formData.append("txtSeqno",$scope.temp.txtSeqno);
                formData.append("isActive",$scope.temp.isActive);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getScrollData();
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


     /* ========== GET DATA =========== */
     $scope.getScrollData = function () {
        $('#SpinnerData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getScrollData'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getScrollData = data.data.success ? data.data.data : [];
            $('#SpinnerData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getScrollData(); --INIT
    /* ========== GET DATA =========== */


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            psid:id.PSID,
            ddlObjType:id.OBJECTTYPE,
            pictureUpload:id.OBJECTNAME,
            existingPictureUpload:id.OBJECTNAME,
            txtTitle:id.TITLE,
            txtDesc:id.TITLE_DESC,
            txtLink:id.LINK,
            txtSeqno:Number(id.SEQNO),
            isActive:id.INACTIVE.toString()
        };
        $scope.objectTypeChange();
        $scope.Img_src= id.OBJECTNAME != '' ? 'images/home_page_scroll/'+id.OBJECTNAME : '';
        $scope.editMode = true;
        $scope.index = $scope.post.getScrollData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp={};
        $scope.editMode = false;
        $scope.Img_src = '';
        $scope.files = [];
        $scope.temp.isActive='0';
        angular.element('#pictureUpload').val(null);
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'PSID': id.PSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getScrollData.indexOf(id);
		            $scope.post.getScrollData.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clear();
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