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
    $scope.Page = "LOCATIONS";
    $scope.PageSub = "LOC_REVIEW";
    $scope.formTitle = 'Location_Reviews';
    $scope.temp.txtDisplayColor="#000000";
    
    var url = 'code/Location_Reviews_code.php';



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
                    $scope.getReviewLink();
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
                formData.append("revid", $scope.temp.revid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtReviewMedia", $scope.temp.txtReviewMedia);
                formData.append("txtLink", $scope.temp.txtLink);
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
                $scope.getReviewLink();
                $scope.clearForm();
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


     /* ========== GET Review Link =========== */
     $scope.getReviewLink = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getReviewLink'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReviewLink = data.data.data;
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReviewLink(); --INIT


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


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("ddlLocation").focus();
        $scope.temp = {
            revid:id.REVID,
            ddlLocation: (id.LOC_ID).toString(),
            txtReviewMedia: id.REVIEWMEDIA,
            txtLink: id.REVIEWLINK,
        };
        $scope.editMode = true;
        $scope.index = $scope.post.getReviewLink.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlLocation").focus();
        $scope.temp={};
        $scope.editMode = false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'revid': id.REVID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getReviewLink.indexOf(id);
		            $scope.post.getReviewLink.splice(index, 1);
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