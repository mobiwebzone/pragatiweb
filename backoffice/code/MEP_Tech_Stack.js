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
    $scope.PageSub = "MEPITMANAGEMENT";
    $scope.PageSub1 = "MEPITMASTER";
    $scope.PageSub2 = "MEPTECHSTACK";
   
    
    var url = 'code/MEP_Tech_Stack_code.php';




    
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
                    // $scope.getLocations();
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
                formData.append("TECHSTACKID", $scope.temp.TECHSTACKID);
                formData.append("txtSoftwarename", $scope.temp.txtSoftwarename);
                formData.append("txtPurpose", $scope.temp.txtPurpose);
                formData.append("txtVersion", $scope.temp.txtVersion);
                formData.append("txtWeblink", $scope.temp.txtWeblink);
                formData.append("NumOnetimeCost", $scope.temp.NumOnetimeCost);
                formData.append("NumRecurringcost", $scope.temp.NumRecurringcost);
                formData.append("ddlRecurrCurrency", $scope.temp.ddlRecurrCurrency);
                formData.append("ddlFrequency", $scope.temp.ddlFrequency);
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
                $scope.getTechStackData();
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





 /* ========== GET Recurring Cost Currency =========== */
 $scope.getRecurrCurrency = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getRecurrCurrency'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
       console.log(data.data.data);
       $scope.post.getRecurrCurrency = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getRecurrCurrency();

  
/* ========== GET Frequency =========== */
  $scope.getFrequency = function () {
    $scope.post.getFrequency = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getFrequency"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getFrequency = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getFrequency();

     
 /* ========== GET TECH Stack DATA =========== */
  $scope.getTechStackData = function () {
    $scope.post.getTechStackData = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getTechStackData"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getTechStackData = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getTechStackData();

    

    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            TECHSTACKID:id.TECHSTACKID,
            txtSoftwarename:id.SOFTWARE_NAME,
            txtPurpose:id.PURPOSE,
            txtVersion:id.VERSION,
            txtWeblink:id.WEBSITE_LINK,
            NumOnetimeCost:id.ONE_TIME_COST,
            NumRecurringcost:id.RECURR_COST,
            ddlRecurrCurrency:id.RECURR_COST_CURRENCY_CD.toString(),
            ddlFrequency:id.RECURR_COST_FREQ_CD.toString(),
            txtRemark:id.REMARKS
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getTechStackData.indexOf(id);
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
                data: $.param({ 'TECHSTACKID': id.TECHSTACKID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTechStackData.indexOf(id);
		            $scope.post.getTechStackData.splice(index, 1);
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