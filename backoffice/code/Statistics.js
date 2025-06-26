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
    $scope.Page = "MISC";
    $scope.PageSub = "STATISTICS";
    $scope.PageSub1 = "STATISTICS";
    $scope.temp.txtDate = new Date();
    $scope.filesExcel = [];
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Statistics.php';


    /*========= For Excel File Name =========*/ 
    $scope.temp.txtUploadExcel ='';
    $scope.ExcelFileName = function (element) {
        $scope.temp.txtUploadExcel ='';

        if(element.files[0] != undefined){
            var reader = new FileReader();
            reader.onload = function (event) {
                $scope.$apply(function ($scope) {
                    $scope.filesExcel = element.files;
                });
            }
            reader.readAsDataURL(element.files[0]);

            $scope.temp.txtUploadExcel = element.files[0]['name'];
            $('.uploadBtn').removeAttr('disabled');
        }
        else{
            $scope.temp.txtUploadExcel = '';
            $('.uploadBtn').attr('disabled','disabled');
        }
        // console.info($scope.temp.txtUploadExcel);
    }
    /*========= For Excel File Name =========*/ 




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


    // =========== SAVE EXCEL DATA ==============
    $scope.saveExcelFile = function(){
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0){
            $scope.messageFailure("Please Select Location First.");return;
        }
        $(".uploadBtn").attr('disabled', 'disabled');
        $(".uploadBtn").text('Uploading...');
        $scope.temp.txtUploadExcelData = $scope.filesExcel[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveExcelFile');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtUploadExcel", $scope.temp.txtUploadExcel);
                formData.append("txtUploadExcelData", $scope.temp.txtUploadExcelData);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                angular.element('#txtUploadExcel').val(null);
                $scope.temp.txtUploadExcel='';
                $scope.filesExcel=[];

                $scope.getStatistics();
                $scope.messageSuccess(data.data.message);                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            // $('.uploadBtn').removeAttr('disabled');
            $(".uploadBtn").text('Upload');
        });
    }
    // =========== SAVE EXCEL DATA ==============



    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("staid", $scope.temp.staid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtDate", $scope.temp.txtDate.toLocaleDateString('sv-SE'));
                formData.append("ddlStaHead", $scope.temp.ddlStaHead);
                formData.append("ddlStaSubHead", $scope.temp.ddlStaSubHead);
                formData.append("txtAmount", $scope.temp.txtAmount);
                formData.append("ddlPaymentSource", $scope.temp.ddlPaymentSource);
                formData.append("txtNarration", $scope.temp.txtNarration);
                formData.append("chkCapitalize", $scope.temp.chkCapitalize);
                formData.append("chkAccrued", $scope.temp.chkAccrued);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearForm();
                $scope.getStatistics();
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
    // =========== SAVE DATA ==============






    /* ========== GET REVENUE =========== */
    $scope.getStatistics = function () {
        $scope.post.getStatistics = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStatistics','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStatistics = data.data.success?data.data.data:[];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStatistics(); --INIT
    /* ========== GET REVENUE =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#ddlLocation").focus();
        $scope.temp = {
            staid : id.STAID,
            ddlLocation : id.LOC_ID.toString(),
            txtDate : new Date(id.STADATE),
            ddlStaHead : id.SHID.toString(),
            ddlStaSubHead : id.SSHID.toString(),
            txtAmount : Number(id.AMOUNT),
            ddlPaymentSource : id.STAFROM_BANKACCID.toString(),
            txtNarration : id.NARRATION,
            chkCapitalize : id.CAPITALIZE>0?'1':'0',
            chkAccrued : id.ACCRUED>0?'1':'0',
        }        
        $scope.editMode = true;
        $scope.index = $scope.post.getStatistics.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.txtDate = new Date();

        angular.element('#txtUploadExcel').val(null);
        $scope.temp.txtUploadExcel='';
        $scope.filesExcel=[];

        $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STAID': id.STAID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStatistics.indexOf(id);
		            $scope.post.getStatistics.splice(index, 1);
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
            if($scope.temp.ddlLocation > 0) $scope.getStatistics();
            if($scope.temp.ddlLocation > 0) $scope.getStaHeads();
            if($scope.temp.ddlLocation > 0) $scope.getBankAccountsDetails();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */



    /* ========== GET STATISTICS HEADS =========== */
    $scope.getStaHeads = function () {
        $scope.post.getStaHeads = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinStaHead').show();
        $http({
            method: 'post',
            url: 'code/StatisticsHeads.php',
            data: $.param({ 'type': 'getStaHeads','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStaHeads = data.data.success?data.data.data:[];
            $('#SpinStaHead').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStaHeads(); --INIT
    /* ========== GET STATISTICS HEADS =========== */


    /* ========== GET STATISTICS SUB HEADS =========== */
    $scope.getStaSubHeads = function () {
        $('#spinStaSubHead').show();
        $http({
            method: 'post',
            url: 'code/StatisticsHeads.php',
            data: $.param({ 'type': 'getStaSubHeads', 'shid' : $scope.temp.ddlStaHead}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStaSubHeads = data.data.success?data.data.data:[];
            $('#spinStaSubHead').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStaSubHeads();
    /* ========== GET STATISTICS SUB HEADS =========== */


    /* ========== GET MEP BANK ACCOUNTS =========== */
    $scope.getBankAccountsDetails = function () {
        $('#spinBA').show();
        $http({
            method: 'post',
            url: 'code/Mep_Bank_Account.php',
            data: $.param({ 'type': 'getBankAccountsDetails', 'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBankAccountsDetails = data.data.success?data.data.data:[];
            $('#spinBA').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBankAccountsDetails();
    /* ========== GET MEP BANK ACCOUNTS =========== */



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
    /* ========== Logout =========== */


     // =============== Check Session =============
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

                // $scope.getChannels();
                $scope.getLocations();
                
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
    // =============== Check Session =============




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