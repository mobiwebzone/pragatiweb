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
    $scope.Page = "STUDENT";
    $scope.PageSub = "REFERRAL_MASTER";
    
    var url = 'code/Referral_Master.php';


    

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
                    $scope.getLocations();
                    // $scope.getReferralMaster();
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
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("refmid", $scope.temp.refmid);
                formData.append("ddlLocation", $scope.ddlLocation);
                formData.append("txtRefDesc", $scope.temp.txtRefDesc);
                formData.append("txtAmount", $scope.temp.txtAmount);
                formData.append("txtValidFrom", $scope.temp.txtValidFrom.toLocaleString('sv-SE'));
                formData.append("txtValidTo", $scope.temp.txtValidTo.toLocaleString('sv-SE'));
                formData.append("txtTermsConditions", $scope.temp.txtTermsConditions);
                formData.append("txtRemarks", $scope.temp.txtRemarks);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getReferralMaster();
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


     /* ========== GET Paymode =========== */
     $scope.getReferralMaster = function () {
        $('#SpinnerData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReferralMaster','LOCID':$scope.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReferralMaster = data.data.success ? data.data.data : [];
            $('#SpinnerData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReferralMaster(); --INIT


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
            $scope.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.ddlLocation > 0) $scope.getReferralMaster();
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
            pmid:id.PMID,
            txtPaymode: id.PAYMENTMODE,
            refmid : id.REFMID,
            txtRefDesc : id.REF_DESC,
            txtAmount : Number(id.REF_AMOUNT),
            txtValidFrom : new Date(id.VALID_FROM),
            txtValidTo : new Date(id.VALID_UPTO),
            txtTermsConditions : id.TERMSCONDITIONS,
            txtRemarks : id.REMARKS
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getReferralMaster.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
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
                data: $.param({ 'REFMID': id.REFMID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getReferralMaster.indexOf(id);
		            $scope.post.getReferralMaster.splice(index, 1);
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