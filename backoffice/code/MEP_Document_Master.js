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
    $scope.PageSub = "SYSTEMDESIGN";
    $scope.PageSub2 = "MEPSYSTEMDESIGNMASTER";
   
    
    var url = 'code/MEP_Document_Master_code.php';




    
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
                    //$scope.getfLocations();
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
                formData.append("DESIGN_DOC_ID", $scope.temp.DESIGN_DOC_ID);
                formData.append("ddlbusinessProc", $scope.temp.ddlbusinessProc);
                formData.append("txtBPgroupname", $scope.temp.txtBPgroupname);
                formData.append("txtfilename", $scope.temp.txtfilename);
                formData.append("txtMenuNav", $scope.temp.txtMenuNav);
                formData.append("txtHTMLcontent", $scope.temp.txtHTMLcontent);
                 formData.append("ddlRoles", $scope.temp.ddlRoles);
                formData.append("ddlInOut", $scope.temp.ddlInOut);
                formData.append("txtGoogledrive1", $scope.temp.txtGoogledrive1);
                formData.append("txtGoogledrive2", $scope.temp.txtGoogledrive2);
                formData.append("ddlLocationEnable", $scope.temp.ddlLocationEnable);
                formData.append("ddlDisplaywebsite", $scope.temp.ddlDisplaywebsite);
                
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
                $scope.getSystemdocu();
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
    
    
     /* ========== GET System documentation Data  =========== */
     $scope.getSystemdocu = function () {
        $(".SpinMain").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getSystemdocu"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getSystemdocu = data.data.success ? data.data.data : [];
            $(".SpinMain").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
    };
    $scope.getSystemdocu();
    
       /* ========== GET BUISNESS PROCEDURE  =========== */
    $scope.getBusinessProc = function () {
        $(".SpinMain").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getBusinessProc"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getBusinessProc = data.data.success ? data.data.data : [];
            $(".SpinMain").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
    };
    $scope.getBusinessProc();
    
     
    
    /* ========== GET ROLES  =========== */
    $scope.getRolesdata = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getRolesdata'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
       console.log(data.data.data);
       $scope.post.getRolesdata = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getRolesdata();
    
    
    /* ========== GET IN/OUTSIDE SYSTEM =========== */
    $scope.getINOUT = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getINOUT'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getINOUT = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getINOUT();
    
        
   
     /* ========== GET Location Enabled =========== */
     $scope.getfLocationenabled = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getfLocationenabled'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getfLocationenabled = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getfLocationenabled();


        /* ========== GET DISPLAY ON WEBSITE =========== */
     $scope.getfDisplaywebsite = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getfDisplaywebsite'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getfDisplaywebsite = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getfDisplaywebsite();
    
       
    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        console.log(id)
        $scope.temp = {
            DESIGN_DOC_ID:id.DESIGN_DOC_ID,
            ddlbusinessProc:id.BUSINESS_PROCEDURE_ID.toString(),
            txtBPgroupname:id.BUSINESS_PROCEDURE_GROUP_NAME,
            txtfilename:id.HTML_FILE_NAME,
            txtMenuNav:id.BUSINESS_PROCESS_NAME,
            txtHTMLcontent:id.HTML_CONTENT,
            ddlRoles:id.ROLE_ID.toString(),
            ddlInOut:id.SCOPE_ID.toString(),
            ddlLocationEnable:id.LOCATION_ENABLED_CODE.toString(),
            ddlDisplaywebsite:id.DISPLAY_ON_WEBSITE_CD.toString(),
            txtGoogledrive2:id.GOOGLE_DRIVE_LINK_VIDEO,
            txtGoogledrive1:id.GOOGLE_DRIVE_LINK_PDF,
            txtRemark:id.REMARKS
        };
    
        $scope.editMode = true;
        $scope.index = $scope.post.getSystemdocu.indexOf(id);
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
                data: $.param({ 'DESIGN_DOC_ID': id.DESIGN_DOC_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                 console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getSystemdocu.indexOf(id);
                    $scope.post.getSystemdocu.splice(index, 1);
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