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
    $scope.Page = "FREERES";
    $scope.PageSub = "STUDY_RES";
    $scope.RESID=0;
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Study_Resources_code.php';




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
                    $scope.getProduct();
                    $scope.getStudyResource();
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

    $scope.save = function(){
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
                formData.append("type", 'save');
                formData.append("resid", $scope.temp.resid);
                formData.append("txtResourceID", $scope.temp.txtResourceID);
                formData.append("txtResourceDesc", $scope.temp.txtResourceDesc);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtResSeqNo", $scope.temp.txtResSeqNo);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.RESID=data.data.GETRESID;
                $scope.getStudyResource();
                // $scope.clear();
                document.getElementById("ddlProduct").focus();
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


     /* ========== GET Study Resource =========== */
     $scope.getStudyResource = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getStudyResource'}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getStudyResource = data.data.data;
                $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudyResource(); --INIT


    /* ========== GET Products =========== */
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


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("ddlProduct").focus();

        $scope.temp = {
            resid:id.RESID,
            ddlProduct:(id.PRODUCT_ID).toString(),
            txtResourceID: id.RESOURCEID,
            txtResourceDesc: id.RESOURCE_DESC,
            txtResSeqNo: Number(id.RES_SEQ)
        };
        $scope.RESID=id.RESID;

        $scope.editMode = true;
        $scope.index = $scope.post.getStudyResource.indexOf(id);

        $scope.getResourceLink();
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        document.getElementById("ddlProduct").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.RESID=0;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'resid': id.RESID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudyResource.indexOf(id);
		            $scope.post.getStudyResource.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    

    // ============================================== RESOURCE LINK ===========================================
    $scope.saveRL = function(){
        $(".btn-save-link").attr('disabled', 'disabled');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveRL');
                formData.append("resid", $scope.RESID);
                formData.append("txtResourceLink", $scope.temp.txtResourceLink);
                formData.append("txtSeqNo", $scope.temp.txtSeqNo);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.getResourceLink();
                $scope.temp.txtResourceLink='';
                $scope.temp.txtSeqNo='';
                document.getElementById("txtResourceLink").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-link').removeAttr('disabled');
        });
    }


    /* ========== GET Resource Link =========== */
    $scope.getResourceLink = function () {
        $scope.post.getResourceLink =[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getResourceLink','RESID':$scope.RESID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getResourceLink = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getResourceLink();

    /* ========== DELETE =========== */
    $scope.deleteRL = function (id) {
        var r = confirm("Are you sure want to delete this Link!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'RESLID': id.RESLID, 'type': 'deleteRL' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getResourceLink.indexOf(id);
		            $scope.post.getResourceLink.splice(index, 1);
		            console.log(data.data.message)
                    
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
                    window.location.assign('index.html#!/login');
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