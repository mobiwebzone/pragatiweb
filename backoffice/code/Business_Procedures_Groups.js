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
    $scope.editModeDet = false;
    $scope.Page = "MISC";
    $scope.PageSub = "BUSINESS_PROC";
    $scope.temp.txtLastUpdateDT = new Date();
    $scope.files = [];


    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Business_Procedures_Groups.php';
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
                    // $scope.getBusinessProcAdmin();
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
    $scope.saveGroup = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveGroup');
                formData.append("bpgid", $scope.temp.bpgid);
                formData.append("ddlLocation", $scope.ddlLocation);
                formData.append("txtGroupName", $scope.temp.txtGroupName);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.temp.bpgid = data.data.BPGID;
                // $scope.clearForm();
                $scope.getGroups();
                $("#ddlStep").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============




    /* ========== GET GROUPS =========== */
    $scope.getGroups = function () {
        $scope.post.getGroups=[];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $scope.spinGroup = true;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function(data){
                var formData = new FormData();
                formData.append("type", 'getGroups');
                formData.append("ddlLocation",$scope.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGroups = data.data.success ? data.data.data : [];
            $scope.spinGroup = false;
        },
        function (data, status, headers, config) {
            $scope.spinGroup = false;
            console.log('Failed');
        })
    }
    // $scope.getBusinessProc(); --INIT
    /* ========== GET BUSINESS PROCEDURES =========== */



    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            $scope.GROUP_NAME = id.GROUP_NAME;
            $scope.temp.bpgid = id.BPGID;
            $scope.temp.txtGroupName = id.GROUP_NAME;
            
            $scope.editMode = true;
            $scope.index = $scope.post.getGroups.indexOf(id);
            $("#ddlCategory").focus();
            $scope.getGroupDetails();
        }
    }
    /* ============ Edit Button ============= */ 
    
    

    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
    
        $scope.editMode = false;
        $scope.clearDet();
        $scope.post.getDetails = [];
        $scope.GROUP_NAME = '';
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
                    data: $.param({ 'BPGID': id.BPGID, 'type': 'delete' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getGroups.indexOf(id);
                        $scope.post.getGroups.splice(index, 1);
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
    /* ========== GET STEPS =========== */
    $scope.getSteps = function () {
        $scope.post.getSteps=[];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0 || !$scope.temp.ddlZone || $scope.temp.ddlZone=='' || !$scope.temp.ddlSSubCategory || $scope.temp.ddlSSubCategory<=0) return;
        $scope.spinSteps = true;
        // $('#ddlSearchCategory').attr('disabled','disabled');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getSteps',
                            'ddlLocation': $scope.ddlLocation,
                            'ddlZone': $scope.temp.ddlZone,
                            'ddlSSubCategory': $scope.temp.ddlSSubCategory,
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSteps = data.data.success ? data.data.data : [];
            
            $scope.spinSteps = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSteps(); --INIT
    /* ========== GET STEPS =========== */



    // =========== SAVE ==============
    $scope.saveDet = function(){
        $(".btn-saveDet").attr('disabled', 'disabled').text('Saving...');
        $(".btn-updateDet").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDet');
                formData.append("bpgdid", $scope.temp.bpgdid);
                formData.append("bpgid", $scope.temp.bpgid);
                formData.append("ddlStep", $scope.temp.ddlStep);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getGroupDetails();
                $scope.getGroups();
                $("#ddlStep").focus();
                if($scope.editModeDet){
                    $scope.clearDet();
                }else{
                    $scope.temp.ddlStep = '';
                }
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
    $scope.getGroupDetails = function () {
        $scope.spinDetData = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                'type': 'getGroupDetails',
                'bpgid': $scope.temp.bpgid,
            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGroupDetails = data.data.success ? data.data.data : [];
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
            $("#ddlStep").focus();
            
            $scope.temp.bpgdid = id.BPGDID;
            $scope.temp.ddlCategory = (id.TDCATID).toString();
            $scope.getTDSubCategory();
            $timeout( () => {
                $scope.temp.ddlSubCategory = (id.TDSUBCATID).toString();
                $scope.getTDSSubCategory();
                $timeout( () => {
                    $scope.temp.ddlSSubCategory = (id.TDSSUBCATID).toString();
                    $scope.temp.ddlZone = id.ZONE;
                    $scope.getSteps();
                    $timeout( () => {
                        $scope.temp.ddlStep = id.BPDID.toString();
                    },500);
                },500);
            },700);

            $scope.editModeDet = true;
            $scope.index = $scope.post.getGroupDetails.indexOf(id);
        }
    }
    /* ============ Edit Button ============= */ 
    
    

    /* ============ Clear Form =========== */ 
    $scope.clearDet = function(){
        $("#ddlCategory").focus();
        $scope.temp.bpgdid = '';
        $scope.temp.ddlCategory = '';
        $scope.temp.ddlSubCategory = '';
        $scope.temp.ddlSSubCategory = '';
        $scope.temp.ddlZone = '';
        $scope.temp.ddlStep = '';
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $scope.post.getSteps=[];

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
                    data: $.param({ 'BPGDID': id.BPGDID, 'type': 'deleteDet' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getGroupDetails.indexOf(id);
                        $scope.post.getGroupDetails.splice(index, 1);
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
            if($scope.ddlLocation > 0) $scope.getGroups();             
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