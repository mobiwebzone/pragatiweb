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
    $scope.PageSub = "MEPITMANAGEMENT";
    $scope.PageSub1 = "MEPITMASTER";
    $scope.PageSub2 = "MEPMASTTABLES";
   
    
    var url = 'code/MEP_Master_Tables_code.php';




    
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
            console.log(data.data);   
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
                    // $scope.getBankAccountsDetails();
                    // $scope.getBankID();
                   // $scope.getfLocations();
                }
                // window.location.assign("dashboard.html");
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
                formData.append("MASTER_TABLES_ID", $scope.temp.MASTER_TABLES_ID);
                formData.append("ddlTBname", $scope.temp.ddlTBname);
                formData.append("txtRemark", $scope.temp.txtRemark);
             
                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.clear();
                $scope.getTableData();
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
    
    /* ========== GET MASTER TABLE  =========== */
    $scope.getmastertable = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getmastertable'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getmastertable = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getmastertable();


    
    /* ========== GET TABLE DATA  =========== */
    $scope.getTableData = function () {
        $(".SpinMain").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getTableData"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getTableData = data.data.success ? data.data.data : [];
            $(".SpinMain").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
    };
    $scope.getTableData();

    
    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            
            MASTER_TABLES_ID:id.MASTER_TABLES_ID,
            ddlTBname:id.TABLE_ID.toString(),
            txtRemark:id.REMARKS
        };
    
        $scope.editMode = true;
        $scope.index = $scope.post.getTableData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp = {};
        $scope.editMode = false;
    }
    
    
    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'MASTER_TABLES_ID': id.MASTER_TABLES_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                 console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTableData.indexOf(id);
                    $scope.post.getTableData.splice(index, 1);
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