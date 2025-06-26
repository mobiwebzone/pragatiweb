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
    $scope.Page = "COLLEGE_APP";
    $scope.PageSub = "CA_MASTER";
    $scope.PageSub1 = "RECOMMENDATION_MASTER";
    $scope.editMode = false;
    
    var url = 'code/Recommendations_Master.php';

    /* ============ CHECK SESSION ============= */ 
    $scope.init = function () {
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
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getRecommendations();
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
    /* ============ CHECK SESSION ============= */ 






    /* ============ SAVE DATA ============= */ 
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("recommendid", $scope.temp.recommendid);
                formData.append("txtRecommendType", $scope.temp.txtRecommendType);
                formData.append("txtDesc", $scope.temp.txtDesc);
                formData.append("ddlRecommendFrom", $scope.temp.ddlRecommendFrom);
                formData.append("txtComments", $scope.temp.txtComments);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRecommendations();
                $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#txtRecommendType").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */ 





     /* ========== GET RECOMMENDATIONS =========== */
     $scope.getRecommendations = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getRecommendations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRecommendations = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRecommendations(); --INIT
    /* ========== GET RECOMMENDATIONS =========== */
    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#txtRecommendType").focus();
    
        $scope.temp = {
            recommendid:id.RECOMMENDID,
            txtRecommendType:id.RECOMMENDTYPE,
            txtDesc:id.RECOMMENDDESC,
            ddlRecommendFrom:(id.RECOMMENDFROM && id.RECOMMENDFROM!='') ? id.RECOMMENDFROM : '',
            txtComments:id.COMMENTS
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getRecommendations.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtRecommendType").focus();
        $scope.temp={};
        $scope.editMode = false;
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'RECOMMENDID': id.RECOMMENDID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRecommendations.indexOf(id);
		            $scope.post.getRecommendations.splice(index, 1);
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
        jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-success').show();
        });
        jQuery('.alert-success').delay(1000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-danger').show();
        });
        jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});