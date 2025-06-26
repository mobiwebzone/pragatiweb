$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","textAngular"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.editModeDet = false;
    $scope.Page = "MISC";
    $scope.PageSub = "BUSINESS_PROC";
    $scope.temp.txtLastUpdateDT = new Date();
    $scope.files = [];
    $scope.txtStepDesc='';

    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
        ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========

    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Business_Procedures_code.php';
    var masterUrl = 'code/MASTER_API.php';


    $scope.printTable = function(FOR){
        if(FOR=='MAIN'){
            $('#detTable').addClass('d-print-none')
            $('#mainTable').removeClass('d-print-none');
        }
        else if(FOR=='DET'){
            $('#mainTable').addClass('d-print-none');
            $('#detTable').removeClass('d-print-none')
            $('#detTableHead').show();
            $timeout(()=>{
                $('#detTableHead').hide();
            },1000);
        }
        window.print();
    }


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
                $scope.LOCID=data.data.locid;
                // window.location.assign("dashboard.html");
                if($scope.userrole!=='SUPERADMIN'){
                    $scope.getBusinessProcAdmin();
                }else{
                    $scope.getLocations();
                    // $scope.getTDCategory();
                    $scope.getUsers();
                    $scope.getHasLinkMenu();
                }
                // $scope.getBusinessProc();
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



    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.txtCatImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("bpid", $scope.temp.bpid);
                formData.append("ddlLocation", $scope.ddlLocation);
                formData.append("ddlSSubCategory", $scope.temp.ddlSSubCategory);
                formData.append("txtProcedureShortDesc", $scope.temp.txtProcedureShortDesc);
                formData.append("txtProcedureLongDesc", $scope.txtProcedureLongDesc);
                formData.append("ddlZone", $scope.temp.ddlZone);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.bpid = data.data.BPID;
                // $scope.clearForm();
                $scope.getBusinessProc();
                $("#ddlCategory").focus();
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



    // =========== COPY DATA ==============
    $scope.openCopyModal = function(id){        
        $scope.GET_BPID = id.BPID;
        $scope.GET_LOCID = id.LOCID;
        $scope.GET_TDSSUBCATID = id.TDSSUBCATID;
        $scope.GET_ZONE = id.ZONE;
    }
    $scope.copy = function(){
        $(".btn-saveC").attr('disabled', false).text('Save...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.txtCatImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'copy');
                formData.append("BPID", $scope.GET_BPID);
                formData.append("ddlLocation", $scope.GET_LOCID);
                formData.append("ddlSSubCategory", $scope.GET_TDSSUBCATID);
                formData.append("ddlZone_old", $scope.GET_ZONE);
                formData.append("ddlZone", $scope.temp.ddlZoneC);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getBusinessProc();
                $scope.temp.ddlZoneC = '';
                $scope.GET_BPID = '';
                $scope.GET_LOCID = '';
                $scope.GET_TDSSUBCATID = '';
                $scope.GET_ZONE = '';
                $("#ddlZoneC").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-saveC").attr('disabled', false).text('SAVE');
        });
    }
    // =========== COPY DATA ==============



    /* ========== GET BUSINESS PROCEDURES =========== */
    $scope.getBusinessProc = function () {
        $scope.post.getBusinessProc=[];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $scope.temp.txtSerarch = undefined;
        $('#ddlSearchCategory').attr('disabled','disabled');
        $('#SpinnerMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getBusinessProc',
                            'ddlLocation': $scope.ddlLocation,
                            'ddlSearchCategory': $scope.temp.ddlSearchCategory
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getBusinessProc = data.data.data;
            }else{
                $scope.post.getBusinessProc=[];
                // console.info(data.data.message);
            }
            $scope.refreshData = !$scope.refreshData;
            $('#SpinnerMainData').hide();
            $('#ddlSearchCategory').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBusinessProc(); --INIT
    /* ========== GET BUSINESS PROCEDURES =========== */


    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            $("#ddlCategory").focus();

            $scope.PRINT_CATEGORY = id.CATEGORY;
            $scope.PRINT_SUBCATEGORY = id.SUBCATEGORY;
            $scope.PRINT_SSUBCATEGORY = id.SSUBCATEGORY;
            $scope.PRINT_LOCATION = id.LOCATION;
            $scope.PRINT_ZONE = id.ZONE;
            
            $scope.temp.bpid = id.BPID;
            $scope.temp.ddlCategory = (id.TDCATID).toString();
            $scope.getTDSubCategory();
            $timeout( () => {
                $scope.temp.ddlSubCategory = (id.TDSUBCATID).toString();
                $scope.getTDSSubCategory();
                $timeout( () => {$scope.temp.ddlSSubCategory = (id.TDSSUBCATID).toString();},500);
            },700);
            
            $scope.ddlLocation = (id.LOCID).toString();
            $scope.temp.txtProcedureShortDesc = id.PROCEDURE_SHORTDESC;
            $scope.txtProcedureLongDesc = id.PROCEDURE_LONGDESC;
            $scope.temp.ddlZone = id.ZONE==='All' ? '' : id.ZONE;
            
            $scope.editMode = true;
            $scope.index = $scope.post.getBusinessProc.indexOf(id);

            $scope.getDetails();
        }
    }
    /* ============ Edit Button ============= */ 
    
    

    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlCategory").focus();
        $scope.temp={};
        $scope.txtProcedureLongDesc='';
    
        $scope.editMode = false;
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $scope.clearDet();
        $scope.post.getDetails = [];
        // $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            var r = confirm("Are you sure want to delete this record!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'BPID': id.BPID, 'type': 'delete' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getBusinessProc.indexOf(id);
                        $scope.post.getBusinessProc.splice(index, 1);
                        // console.log(data.data.message)
                        $scope.clearForm();
                        
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== DELETE =========== */
    

    // ==========================================================
    // DETAILS START
    // ==========================================================
    // =========== SAVE ==============
    $scope.saveDet = function(){
        $(".btn-saveDet").attr('disabled', 'disabled').text('Saving...');
        $(".btn-updateDet").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.txtCatImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDet');
                formData.append("bpdid", $scope.temp.bpdid);
                formData.append("bpid", $scope.temp.bpid);
                formData.append("txtStep", $scope.temp.txtStep);
                formData.append("txtStepDesc", $scope.txtStepDesc);
                formData.append("ddlInOut", $scope.temp.ddlInOut);
                formData.append("ddlMenuName", $scope.temp.ddlMenuName);
                formData.append("LocEnabled", $scope.temp.LocEnabled);
                formData.append("displayOnWeb", $scope.temp.displayOnWeb);
                formData.append("txtPdfLink", $scope.temp.txtPdfLink);
                formData.append("txtVideoLink", $scope.temp.txtVideoLink);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getDetails();
                $("#txtStep").focus();
                $scope.clearDet();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveDet').removeAttr('disabled').text('SAVE');
            $('.btn-updateDet').removeAttr('disabled').text('UPDATE');
        });
    }
    // =========== SAVE ==============


    /* ========== DETAILS =========== */
    $scope.getDetails = function () {
        $scope.spinDetData = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                'type': 'getDetails',
                'bpid': $scope.temp.bpid,
            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getDetails = data.data.success ? data.data.data : [];
            $scope.spinDetData = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ========== GET DETAILS =========== */


    /* ============ Edit Button ============= */ 
    $scope.editDet = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            $("#txtStep").focus();
            
            $scope.temp.bpdid = id.BPDID;
            $scope.temp.txtStep = id.STEP;
            $scope.txtStepDesc = id.STEP_DESC;
            $scope.temp.ddlInOut = id.INOUT;
            $scope.temp.ddlMenuName = id.MENUID > 0 ? id.MENUID.toString() : '';
            $scope.temp.LocEnabled = id.LOCATION_ENABLED;
            $scope.temp.displayOnWeb = id.DISPLAY_WEBSITE;
            $scope.temp.txtPdfLink = !id.PDF_LINK ? '' : id.PDF_LINK;
            $scope.temp.txtVideoLink = !id.VIDEO_LINK ? '' : id.VIDEO_LINK;
            
            $scope.editModeDet = true;
            $scope.index = $scope.post.getDetails.indexOf(id);
        }
    }
    /* ============ Edit Button ============= */ 
    
    

    /* ============ Clear Form =========== */ 
    $scope.clearDet = function(){
        $("#ddlCategory").focus();
        $scope.temp.bpdid = '';
        $scope.temp.txtStep = '';
        $scope.temp.ddlInOut = '';
        $scope.temp.ddlMenuName = '';
        $scope.temp.LocEnabled = '0';
        $scope.temp.displayOnWeb = '0';
        $scope.temp.txtPdfLink = '';
        $scope.temp.txtVideoLink = '';
        $scope.txtStepDesc='';

        $scope.editModeDet = false;
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteDet = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            var r = confirm("Are you sure want to delete this record!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'BPDID': id.BPDID, 'type': 'deleteDet' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getDetails.indexOf(id);
                        $scope.post.getDetails.splice(index, 1);
                        // console.log(data.data.message)
                        $scope.clearDet();
                        
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== DELETE =========== */


    // ==========================================================
    // DETAILS START
    // ==========================================================


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











    // ========================================================
    // OTHER DATA START
    // ========================================================
    /* ========== GET FOR ADMIN =========== */
    $scope.getBusinessProcAdmin = function () {
        $('.spinMaindata').show();
        $http({
            method: 'post',
            url: '../teacher_backoffice/code/TeacherBusinessProc_code.php',
            data: $.param({ 'type': 'getBusinessProc',
                            'LOCID':$scope.LOCID,
                            'FOR':'TEACHER'
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getBusinessProc=data.data.success ? data.data.data : [];
            }
            $('.spinMaindata').hide();
        },
        function (data, status, headers, config) {
            // console.log('Failed');
        })

    }
    // $scope.getBusinessProcAdmin(); --INIT
    /* ========== GET FOR ADMIN =========== */

    

    /* ========== GET CATEGORY =========== */
    $scope.getTDCategory = function () {
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $('.spinCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getCategories','ddlLocation': $scope.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDCategory = data.data.data;
            }else{
                $scope.post.getTDCategory = [];
            }
            $('.spinCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDCategory(); --INIT
    /* ========== GET CATEGORY =========== */

    

    /* ========== GET SUB CATEGORY =========== */
    $scope.getTDSubCategory = function () {
        $scope.post.getTDSSubCategory = [];
        $('.spinSubCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'tdcatid' : $scope.temp.ddlCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDSubCategory = data.data.data;
            }else{
                $scope.post.getTDSubCategory = [];
            }
            $('.spinSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDSubCategory();
    /* ========== GET SUB CATEGORY =========== */



    /* ========== GET SUB SUBCATEGORY =========== */
    $scope.getTDSSubCategory = function () {
        $('.spinSSubCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSSubCategories', 'tdsubcatid' : $scope.temp.ddlSubCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDSSubCategory = data.data.data;
            }else{
                $scope.post.getTDSSubCategory = [];
            }
            $('.spinSSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDSSubCategory();
    /* ========== GET SUB SUBCATEGORY =========== */



    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getUserByLoc = [];
        $('.spinLoc').show();
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.ddlLocation = ($scope.post.getLocations) ? $scope.LOCID.toString():'';
            if($scope.ddlLocation > 0) $scope.getTDCategory();
            if($scope.ddlLocation > 0) $scope.getBusinessProc();             
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET USERS =========== */
    $scope.getUsers = function () {
        $('.spinUsers').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getUsers'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getUsers = data.data.data;
            }else{
                $scope.post.getUsers = [];
            }
            $('.spinUsers').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getUsers();--INIT
    /* ========== GET USERS =========== */


    /* ========== GET OBJECT MASTER =========== */
    $scope.getHasLinkMenu = function () {
        $scope.spinObj = true;
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getHasLinkMenu'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getHasLinkMenu = data.data.success ? data.data.data : [];
            $scope.spinObj = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getHasLinkMenu();--INIT
    /* ========== GET OBJECT MASTER =========== */
    // ========================================================
    // OTHER DATA END
    // ========================================================

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