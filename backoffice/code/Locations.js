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
$postModule.controller("myCtrl", function ($scope, $http,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "LOCATIONS";
    $scope.PageSub = "LOC_FRANCHISE";
    $scope.FormName = 'Show Entry Form';
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Locations_code.php';

    



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

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getCountries();
                    $scope.getCurrency();
                    $scope.getLocation();
                    $scope.CheckISMainET();
                    $scope.getScript();
                    $scope.getLanguage();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
                // window.location.assign("dashboard.html");
            }
            else {
                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.FormShowHide=function (){
        $scope.FormName ='';
        var isMobileVersion = document.getElementsByClassName('collapsed');
        if (isMobileVersion.length > 0) {
            $scope.FormName = 'Hide Entry Form';
        }else{
            $scope.FormName = 'Show Entry Form';
        }

        $('.ShowHideIcon').toggleClass("fa-plus-circle fa-minus-circle");
    }

    /* ========== Check IS MAIN ET =========== */
    $scope.CheckISMainET = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'CheckISMainET'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.IsMainET = 1;
            }
            else{
                $scope.IsMainET = 0;
            }

        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.CheckISMainET(); --INIT

    // $scope.saveLocation = function(){
    //     $(".btn-save").attr('disabled', 'disabled');
    //     $(".btn-save").text('Saving...');
    //     $(".btn-update").attr('disabled', 'disabled');
    //     $(".btn-update").text('Updating...');

    //     $http({
    //         method: 'POST',
    //         url: url,
    //         processData: false,
    //         transformRequest: function (data) {
    //             var formData = new FormData();
    //             formData.append("type", 'saveLocation');
    //             formData.append("locationid", $scope.temp.locid);
    //             formData.append("txtLocation", $scope.temp.txtLocation);
    //             formData.append("txtLocationDesc", $scope.temp.txtLocationDesc);
    //             formData.append("txtContactNo", $scope.temp.txtContactNo);
    //             formData.append("txtContactPerson", $scope.temp.txtContactPerson);
    //             formData.append("txtCompanyName", $scope.temp.txtCompanyName);
    //             formData.append("txtTaxID", $scope.temp.txtTaxID);
    //             formData.append("txtEmail", $scope.temp.txtEmail);
    //             formData.append("txtAddressL1", $scope.temp.txtAddressL1);
    //             formData.append("txtAddressL2", $scope.temp.txtAddressL2);
    //             formData.append("txtCity", $scope.temp.txtCity);
    //             formData.append("txtState", $scope.temp.txtState);
    //             formData.append("txtCountry", $scope.temp.txtCountry);
    //             formData.append("txtZipCode", $scope.temp.txtZipCode);
    //             formData.append("IsmainET", $scope.temp.IsmainET);
    //             formData.append("txtETDiff", $scope.temp.txtETDiff);
    //             formData.append("ddlCurrency", $scope.temp.ddlCurrency);
    //             formData.append("ddlLanguage", $scope.temp.ddlLanguage);
    //             formData.append("ddlScript", $scope.temp.ddlScript);
    //             formData.append("txtNextNoofTopics", $scope.temp.txtNextNoofTopics);
    //             formData.append("txtLoginID", $scope.temp.txtLoginID);
    //             formData.append("txtLoginPwd", $scope.temp.txtLoginPwd);
    //             formData.append("txtFlagIcon", $scope.temp.txtFlagIcon);
    //             return formData;
    //         },
    //         data: $scope.temp,
    //         headers: { 'Content-Type': undefined }
    //     }).
    //     then(function (data, status, headers, config) {
    //         console.log(data.data);
    //         if (data.data.success) {
    //             $scope.messageSuccess(data.data.message);
    //             $scope.getLocation();
    //             $scope.clearForm();
    //             document.getElementById("txtLocation").focus();
                
    //         }
    //         else {
    //             $scope.messageFailure(data.data.message);
    //              console.log(data.data)
    //         }
    //         $('.btn-save').removeAttr('disabled');
    //         $(".btn-save").text('SAVE');
    //         $('.btn-update').removeAttr('disabled');
    //         $(".btn-update").text('UPDATE');
    //     });
    // }

    $scope.saveLocation = function() {
        console.log('Form Data:', $scope.temp); // Log all form data
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
    
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function(data) {
                var formData = new FormData();
                formData.append("type", 'saveLocation');
                formData.append("locationid", $scope.temp.locid || 0); // Ensure locationid is not undefined
                formData.append("txtLocation", $scope.temp.txtLocation || '');
                formData.append("txtLocationDesc", $scope.temp.txtLocationDesc || '');
                formData.append("txtContactNo", $scope.temp.txtContactNo || '');
                formData.append("txtContactPerson", $scope.temp.txtContactPerson || '');
                formData.append("txtCompanyName", $scope.temp.txtCompanyName || '');
                formData.append("txtTaxID", $scope.temp.txtTaxID || '');
                formData.append("txtEmail", $scope.temp.txtEmail || '');
                formData.append("txtAddressL1", $scope.temp.txtAddressL1 || '');
                formData.append("txtAddressL2", $scope.temp.txtAddressL2 || '');
                formData.append("txtCity", $scope.temp.txtCity || '');
                formData.append("txtState", $scope.temp.txtState || '');
                formData.append("txtCountry", $scope.temp.txtCountry || '');
                formData.append("txtZipCode", $scope.temp.txtZipCode || '');
                formData.append("IsmainET", $scope.temp.IsmainET || 0);
                formData.append("txtETDiff", $scope.temp.txtETDiff || '');
                formData.append("ddlCurrency", $scope.temp.ddlCurrency || 0);
                formData.append("ddlLanguage", $scope.temp.ddlLanguage || 0);
                formData.append("ddlScript", $scope.temp.ddlScript || 0);
                formData.append("txtNextNoofTopics", $scope.temp.txtNextNoofTopics || 0);
                formData.append("txtLoginID", $scope.temp.txtLoginID || '');
                formData.append("txtLoginPwd", $scope.temp.txtLoginPwd || '');
                formData.append("txtFlagIcon", $scope.temp.txtFlagIcon || '');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).then(function(response) {
            console.log('Server Response:', response.data); // Log full response
            if (response.data.success) {
                $scope.messageSuccess(response.data.message);
                $scope.getLocation();
                $scope.clearForm();
                document.getElementById("txtLocation").focus();
            } else {
                $scope.messageFailure(response.data.message);
                console.log('Error Response:', response.data);
            }
            $('.btn-save').removeAttr('disabled');
            $('.btn-save').text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $('.btn-update').text('UPDATE');
        }, function(error) {
            console.error('HTTP Error:', error); // Log HTTP errors
            $scope.messageFailure('An error occurred while saving the location.');
            $('.btn-save').removeAttr('disabled');
            $('.btn-save').text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $('.btn-update').text('UPDATE');
        });
    };
     


     /* ========== GET Location =========== */
     $scope.getLocation = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLocation'}),
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
    // $scope.getLocation(); --INIT



    /* ========== GET Currency =========== */
    $scope.getCurrency = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCurrency'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCurrencyD = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCurrency(); --INIT
    
    
    /* ========== GET COUNTRY =========== */
    $scope.getCountries = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCountries'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCountry = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCountries(); --INIT


    /* ========== GET Script =========== */
    $scope.getScript = function () {
        $http({
            method: 'post',
            url: 'code/SCRIPT-MASTER.php',
            data: $.param({ 'type': 'getScript'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getScript = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //   $scope.getScript();
    /* ========== GET Script =========== */


    /* ========== GET LANGUAGE =========== */
    $scope.getLanguage = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: 'code/LANGUAGE-MASTER.php',
            data: $.param({ 'type': 'getLanguage'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLanguage = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getLanguage();



    /* ============ Edit Button ============= */ 
    $scope.editLocation = function (id) {
        $('.collapse').collapse('show');
        $timeout(function() {
            $scope.FormShowHide();
            $scope.FormName = 'Hide Entry Form';
        },500);
        document.getElementById("txtLocation").focus();
        $scope.temp = {
            locid:id.LOC_ID,
            txtLocation: id.LOCATION,
            txtLocationDesc: id.LOC_DESC,
            txtContactNo: id.LOC_CONTACT,
            txtContactPerson: id.LOC_PERSON,
            txtCompanyName: id.COMPANY_NAME,
            txtTaxID: id.TAXID,
            txtEmail: id.LOC_EMAIL,
            txtEmail: id.LOC_EMAIL,
            txtAddressL1: id.LOC_ADDRESS_LINE1,
            txtAddressL2: id.LOC_ADDRESS_LINE2,
            txtCity: id.LOC_CITY,
            txtState: id.LOC_STATE,
            txtCountry: (id.LOC_COUNTRY).toString(),
            txtZipCode: id.LOC_ZIPCODE,
            txtETDiff: id.LOC_ET_DIFF,
            ddlCurrency: (id.CURRENCY_ID).toString(),
            ddlLanguage: id.LANID>0?id.LANID.toString():'',
            ddlScript: id.SCRIPTID>0?id.SCRIPTID.toString():'',
            txtNextNoofTopics: id.LA_NOOF_NEXT_TOPICS>0?id.LA_NOOF_NEXT_TOPICS:'',
            txtLoginID: id.LOGIN_ID,
            txtLoginPwd: id.LOGIN_PWD,
            // txtFlagIcon: id.FLAG_ICON,
        };
        
        if(id.IS_ET == 1){
            $scope.temp.IsmainET = true;
        }


        $scope.editMode = true;
        $scope.index = $scope.post.getLocations.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("txtLocation").focus();
        // $scope.FormName = 'Show Entry Form';
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.IsmainET = false;
    }



    /* ========== DELETE =========== */
    $scope.deleteLocation = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'locid': id.LOC_ID, 'type': 'deleteLocation' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getLocations.indexOf(id);
		            $scope.post.getLocations.splice(index, 1);
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

    // EYE
    // $scope.eye=function(){
    //     alert();
    //     $('#eyes').attr('type','password');
    // }
    $scope.eyepass= function() {

        // $(this).toggleClass("fa-eye fa-eye-slash");
        
        var input = $("#txtLoginPwd");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon').removeClass('fa-eye');
            $('.Eyeicon').addClass('fa-eye-slash');
        }else{
            $('.Eyeicon').removeClass('fa-eye-slash');
            $('.Eyeicon').addClass('fa-eye');
        }
    };


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