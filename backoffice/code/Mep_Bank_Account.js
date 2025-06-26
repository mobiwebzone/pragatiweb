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
    $scope.PageSub = "MEP_BANK";
   
    
    var url = 'code/Mep_Bank_Account.php';




    
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
                   
                    // $scope.getBankID();
                    $scope.getLocations();
                     $scope.getBankAccountsDetails();
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
                formData.append("bankaccid", $scope.temp.bankaccid);
                formData.append("ddlBankID", $scope.temp.ddlBankID);
                formData.append("ddlAcNO", $scope.temp.ddlAcNO);
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("TEXT_IFSC_CODE", $scope.temp.TEXT_IFSC_CODE);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.getBankAccountsDetails();
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





     
     /* ========== GET Bank Account Details =========== */
     $scope.getBankAccountsDetails = function () {
        $scope.post.getBankAccountsDetails = [];
        
         console.log($scope.temp.ddlLocation);

         if (!$scope.temp.ddlLocation || $scope.temp.ddlLocation <= 0) return;
        $('#SpinMainData').show();
        $http({
            method: 'post',
           url: url,
            data: $.param({
                'type': 'getBankAccountsDetails'
                ,ddlLocation: $scope.temp.ddlLocation
            }),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           console.log(data.data);
           $scope.post.getBankAccountsDetails = data.data.success ? data.data.data : [];
           $('#SpinMainData').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
    // $scope.getBankAccountsDetails();


    
    /* ========== GET Bank Name By ID Details =========== */
    $scope.getBankID = function () {
        $scope.post.getBankID = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.SpinBank').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getBankID','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            //    console.log(data.data);
            $scope.post.getBankID = data.data.success ? data.data.data : [];
            $('.SpinBank').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBankID();


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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getBankID();
            if($scope.temp.ddlLocation > 0) $scope.getBankAccountsDetails();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            bankaccid:id.BANKACCID,
            ddlLocation: id.LOCID.toString(),
            ddlBankID: id.BANKID.toString(),
            ddlAcNO:id.ACCOUNTNO,
            txtRemark: id.REMARKS,
            TEXT_IFSC_CODE: id.IFSC_CODE 
        };
        $scope.getBankID();
        $timeout(()=>{
            $scope.temp.ddlBankID=(id.BANKID).toString();
        },700);
        $scope.editMode = true;
        $scope.index = $scope.post.getBankAccountsDetails.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // document.getElementById("ddlPlan").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.getLocations();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'bankaccid': id.BANKACCID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getBankAccountsDetails.indexOf(id);
		            $scope.post.getBankAccountsDetails.splice(index, 1);
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