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
    $scope.Page = "L&A";
    $scope.PageSub = "FRANCHISEMANAGEMENT";
    $scope.PageSub1 = "MEPITREPORT";
    $scope.PageSub2 = "MEPFRANCHISESOFTWARE";
    
   
    
    var url = 'code/MEP_Franchise_Software_List_report_code.php';




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
/*
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
                formData.append("SOFTWARE_LIST_ID", $scope.temp.SOFTWARE_LIST_ID);
                formData.append("txtSoftwarename", $scope.temp.txtSoftwarename);
                formData.append("ddlSoftwarePurpose", $scope.temp.ddlSoftwarePurpose);
                formData.append("txtSoftwareversion", $scope.temp.txtSoftwareversion);
                formData.append("txtLicenceinfo", $scope.temp.txtLicenceinfo);
                formData.append("txtLogininfo", $scope.temp.txtLogininfo);
                formData.append("txtPassword", $scope.temp.txtPassword);
                formData.append("ddlMastertask", $scope.temp.ddlMastertask);
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
                $scope.getSoftwarepurposeData();
                $scope.clear();
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
    
    */
    
    
    $scope.getSoftwarepurposeData = function () {
        $(".SpinMain").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getSoftwarepurposeData"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getSoftwarepurposeData = data.data.success ? data.data.data : [];
            $(".SpinMain").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
    };
    $scope.getSoftwarepurposeData();
    
     

    
    /* ========== GET SOFTWARE PURPOSE  =========== */
    $scope.getsoftwarepurpose = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getsoftwarepurpose'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
       console.log(data.data.data);
       $scope.post.getsoftwarepurpose = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getsoftwarepurpose();

     /* ========== GET Master Task =========== */
     $scope.getMastertask = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getMastertask'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getMastertask = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getMastertask();
    
    
    
    /* ============ Edit Button =============  
    $scope.edit = function (id) {
        console.log(id)
        $scope.temp = {
            SOFTWARE_LIST_ID:id.SOFTWARE_LIST_ID,
            txtSoftwarename:id.SOFTWARE_NAME,
            ddlSoftwarePurpose:id.SOFTWARE_PURPOSE_ID.toString(),
            txtSoftwareversion:id.SOFTWARE_VERSION,
            txtLicenceinfo:id.LICENSE_INFO,
            txtLogininfo:id.LOGIN_USER_NAME,
            txtPassword:id.LOGIN_PASSWORD,
            ddlMastertask:!id.MASTER_TASK_CD ? '' : id.MASTER_TASK_CD.toString(),
            txtRemark:id.REMARKS
        };
    
        $scope.editMode = true;
        $scope.index = $scope.post.getSoftwarepurposeData.indexOf(id);
    }
    */ 
    
    /* ============ Clear Form ===========  
    $scope.clear = function(){
        $scope.temp = {};
        $scope.editMode = false;
    }
    */ 
    
    /* ========== DELETE =========== 
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SOFTWARE_LIST_ID': id.SOFTWARE_LIST_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                 console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getSoftwarepurposeData.indexOf(id);
                    $scope.post.getSoftwarepurposeData.splice(index, 1);
                    console.log(data.data.message)
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    */ 

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