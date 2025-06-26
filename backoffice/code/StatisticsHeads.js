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
    $scope.PageSub1 = "STA_HEAD";
    $scope.filesExcel = [];
    $scope.temp.txtETADate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/StatisticsHeads.php';


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

                $scope.getStaHeads();
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
        $scope.temp.txtUploadExcelData='';
        angular.element('#txtUploadExcel').val(null);
        $scope.temp.txtUploadExcel='';
        $scope.filesExcel=[];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("shid", $scope.temp.shid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtStaHead", $scope.temp.txtStaHead);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.shid=data.data.GET_SHID;
                // $scope.clearForm();
                $scope.getStaHeads();
                if($scope.temp.shid > 0){
                    $scope.getStaSubHeads();
                }
                $timeout(()=>{$("#txtStaSubHead").focus();},500);
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






    /* ========== GET STATISTICS HEADS =========== */
    $scope.getStaHeads = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStaHeads','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStaHeads = data.data.success?data.data.data:[];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStaHeads(); --INIT
    /* ========== GET STATISTICS HEADS =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        // console.log(id);
        $("#ddlLocation").focus();

        $scope.temp.shid = id.SHID;
        $scope.temp.ddlLocation = id.LOC_ID.toString();
        $scope.temp.txtStaHead = id.STATISTICS_HEAD;

        if($scope.temp.shid > 0){
            $scope.getStaSubHeads();
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getStaHeads.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
        $scope.post.getStaSubHeads = [];
        $scope.editMode = false;

        angular.element('#txtUploadExcel').val(null);
        $scope.temp.txtUploadExcel='';
        $scope.filesExcel=[];

        $scope.temp.sshid = '';
        $scope.temp.txtStaSubHead = '';
        $scope.editModeSubHead = false;
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
                data: $.param({ 'SHID': id.SHID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStaHeads.indexOf(id);
		            $scope.post.getStaHeads.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SUB HEAD SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // =========== SAVE SUB HEAD DATA ==============
    $scope.saveDataSubHead = function(){
        $(".btn-save-SubHead").attr('disabled', 'disabled');
        $(".btn-save-SubHead").text('Saving...');
        $(".btn-update-SubHead").attr('disabled', 'disabled');
        $(".btn-update-SubHead").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataSubHead');
                formData.append("sshid", $scope.temp.sshid);
                formData.append("shid", $scope.temp.shid);
                formData.append("txtStaSubHead", $scope.temp.txtStaSubHead);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormSubHead();
                $scope.getStaSubHeads();
                $scope.messageSuccess(data.data.message);
                $scope.getStaHeads();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-SubHead').removeAttr('disabled');
            $(".btn-save-SubHead").text('SAVE');
            $('.btn-update-SubHead').removeAttr('disabled');
            $(".btn-update-SubHead").text('UPDATE');
        });
    }
    // =========== SAVE SUB HEAD DATA ==============




    
    
    /* ========== GET STATISTICS SUB HEADS =========== */
    $scope.getStaSubHeads = function () {
        $('#spinSubHead').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStaSubHeads', 'shid' : $scope.temp.shid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStaSubHeads = data.data.success?data.data.data:[];
            $('#spinSubHead').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStaSubHeads();
    /* ========== GET STATISTICS SUB HEADS =========== */





    /* ============ Edit Sub Head Button ============= */ 
    $scope.editFormSubHead = function (id) {
        $("#txtStaSubHead").focus();
        
        $scope.temp.sshid = id.SSHID;
        $scope.temp.shid = id.SHID;
        $scope.temp.txtStaSubHead = id.STATISTICS_SUB_HEAD;
        
        $scope.editModeSubHead = true;
        $scope.index = $scope.post.getStaSubHeads.indexOf(id);
    }
    /* ============ Edit Sub Head Button ============= */ 
    
    


    /* ============ Clear Sub HEad Form =========== */ 
    $scope.clearFormSubHead = function(){
        $("#txtStaSubHead").focus();
        $scope.temp.sshid = '';
        $scope.temp.txtStaSubHead = '';
        $scope.editModeSubHead = false;
    }
    /* ============ Clear Sub HEad Form =========== */ 




    /* ========== DELETE SUB HEAD =========== */
    $scope.deleteSubHead = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SSHID': id.SSHID, 'type': 'deleteSubHead' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStaSubHeads.indexOf(id);
		            $scope.post.getStaSubHeads.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getStaHeads();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE SUB HEAD =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getStaHeads();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




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