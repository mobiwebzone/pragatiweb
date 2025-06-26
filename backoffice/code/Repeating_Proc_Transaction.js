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
    $scope.PageSub = "REPEATING_PROCEDURES";
    $scope.PageSub1 = "REPEATING_TRANSACTION";
    $scope.temp.txtLastUpdateDT = new Date();
    $scope.chkSelectedProc = [];
    
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Repeating_Proc_Transaction_code.php';
    
    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
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
                // window.location.assign("dashboard.html");

                $scope.getLocations();
                // $scope.getTDCategory();
                // $scope.getRepeatingProcMasters();
                $scope.getUsers();
                // $scope.getRepeatingProcTransaction();
                // $scope.getRepeatingProcMastersALL();

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



    

    // =========== Add Transactions ==============
    $scope.addTransactions = function(){
        $(".btn-addTrans").attr('disabled', 'disabled');
        $(".btn-addTrans").text('Adding...');
        // alert($scope.temp.ddlCollege);
        console.log($scope.chkSelectedProc);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'addTransactions');
                formData.append("ddlLocation", $scope.ddlLocation);
                formData.append("chkSelectedProc", $scope.chkSelectedProc);
                return formData;                 
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRepeatingProcTransaction();
                
                $scope.SelectedProc_Exist = false;
                $scope.chkSelectedProc = [];
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-addTrans').removeAttr('disabled');
            $(".btn-addTrans").text('ADD');
        });
    }
    // =========== Add Transactions ==============



    

    // =========== SAVE DATA ==============
    $scope.saveData = function(){
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
                formData.append("type", 'saveData');
                formData.append("rp_transid", $scope.temp.rp_transid);
                formData.append("ddlLocation", $scope.ddlLocation);
                formData.append("ddlRepeatingProc", $scope.temp.ddlRepeatingProc);
                formData.append("ddlStatus", $scope.temp.ddlStatus);
                formData.append("txtCompleteDT", (!$scope.temp.txtCompleteDT || $scope.temp.txtCompleteDT == '') ? '' : $scope.temp.txtCompleteDT.toLocaleString('sv-SE'));
                formData.append("ddlUser", $scope.temp.ddlUser);
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;                 
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearForm();
                $scope.getRepeatingProcTransaction();
                $("#ddlRepeatingProc").focus();
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






    /* ========== GET REPEATING PROC MASTER =========== */
    $scope.getRepeatingProcTransaction = function () {
        $scope.post.getRepeatingProcTransaction=[];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $scope.temp.txtSerarch = undefined;
        $('#ddlSearchCategory').attr('disabled','disabled');
        $('#SpinnerMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getRepeatingProcTransaction',
                            'ddlLocation':$scope.ddlLocation,
                            'ddlSearchRepeatingProc':$scope.temp.ddlSearchRepeatingProc,
                            'ddlSearchStatus':$scope.temp.ddlSearchStatus
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRepeatingProcTransaction = data.data.data;
            }else{
                $scope.post.getRepeatingProcTransaction=[];
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
    // $scope.getRepeatingProcTransaction(); --INIT
    /* ========== GET REPEATING PROC MASTER =========== */






    /* ========== GET REPEATING PROC MASTER ALL =========== */
    $scope.getRepeatingProcMastersALL = function () {
        $scope.post.getRepeatingProcMasters=[];
        $scope.post.getRepeatingProcMastersALL=[];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $('.spinRP').show();
        $http({
            method: 'post',
            url: 'code/Repeating_Proc_Master_code.php',
            data: $.param({ 
                            'type': 'getRepeatingProcMasters',
                            'ddlLocation':$scope.ddlLocation,
                            'ddlSearchCategory': 0
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRepeatingProcMastersALL = data.data.data;
            }else{
                $scope.post.getRepeatingProcMastersALL=[];
                // console.info(data.data.message);
            }
            $('.spinRP').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRepeatingProcMastersALL(); --INIT
    /* ========== GET REPEATING PROC MASTER ALL =========== */






    /* ========== GET REPEATING PROC MASTER =========== */
    $scope.getRepeatingProcMasters = function () {
        $scope.post.getRepeatingProcMasters=[];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        if($scope.temp.ddlSearchCategory>0 || $scope.temp.ddlSearchSubCategory>0 || $scope.temp.ddlSearchSSubCategory>0 || ($scope.temp.ddlSearchFrequency && $scope.temp.ddlSearchFrequency.length>0)){
            $('#SpinSearchRepeatingMaster').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 
                                'type': 'getRepeatingProcMasters',
                                'ddlLocation':$scope.ddlLocation,
                                'ddlSearchCategory': $scope.temp.ddlSearchCategory,
                                'ddlSearchSubCategory': $scope.temp.ddlSearchSubCategory,
                                'ddlSearchSSubCategory': $scope.temp.ddlSearchSSubCategory,
                                'ddlSearchFrequency': $scope.temp.ddlSearchFrequency
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getRepeatingProcMasters = data.data.data;
                }else{
                    $scope.post.getRepeatingProcMasters=[];
                    // console.info(data.data.message);
                }
                $('#SpinSearchRepeatingMaster').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }else{
            $scope.post.getRepeatingProcMasters=[];
        }
    }
    // $scope.getRepeatingProcMasters();
    /* ========== GET REPEATING PROC MASTER =========== */







    

    /* ========== GET USERS =========== */
    $scope.getUsers = function () {
        $scope.post.getUsers = [];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $('.spinUsers').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getUsers','ddlLocation':$scope.ddlLocation}),
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
    // $scope.getUsers(); --INIT
    /* ========== GET USERS =========== */





    /* ========== GET CATEGORY =========== */
    $scope.getTDCategory = function () {
        $scope.post.getTDCategory = [];
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        if(!$scope.ddlLocation || $scope.ddlLocation<=0) return;
        $('.spinCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getCategories','ddlLocation':$scope.ddlLocation}),
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
            data: $.param({ 'type': 'getSubCategories', 'tdcatid' : $scope.temp.ddlSearchCategory}),
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
            data: $.param({ 'type': 'getSSubCategories', 'tdsubcatid' : $scope.temp.ddlSearchSubCategory}),
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
            if($scope.ddlLocation > 0) $scope.getTDCategory();
            if($scope.ddlLocation > 0) $scope.getRepeatingProcMasters();
            if($scope.ddlLocation > 0) $scope.getRepeatingProcTransaction();
            if($scope.ddlLocation > 0) $scope.getRepeatingProcMastersALL();
            if($scope.ddlLocation > 0) $scope.getUsers();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */







    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            $("#ddlRepeatingProc").focus();
            $scope.temp.rp_transid = id.RP_TRANSID;
            
            $scope.temp.ddlRepeatingProc = id.RPID.toString();
            $scope.temp.ddlStatus = id.STATUS == '' ? '' : id.STATUS;
            $scope.temp.txtCompleteDT = id.DATE_COMPLETE == '-' ? '' : new Date(id.DATE_COMPLETE);
            $scope.temp.txtRemark = id.REMARK;
            $scope.temp.ddlUser = id.COMPLETEDBY > 0 ? id.COMPLETEDBY.toString() : '';

            $scope.temp.txtFrequency = '';
            
            $scope.editMode = true;
            $scope.index = $scope.post.getRepeatingProcTransaction.indexOf(id);
        }
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlRepeatingProc").focus();
        $scope.temp={};
        $scope.editMode = false;
        // $scope.getLocations();
        
    }
    /* ============ Clear Form =========== */ 
    
    
    
    /* ============ Clear Top Search =========== */ 
    $scope.resetTopSearch=()=>{
        $scope.temp.ddlSearchCategory = '';
        $scope.temp.ddlSearchSubCategory = '';
        $scope.temp.ddlSearchSSubCategor = '';
        $scope.temp.ddlSearchFrequency = '';
        $scope.post.getTDSSubCategory = [];
        $scope.post.getTDSubCategory = [];      
        $scope.post.getRepeatingProcMasters = [];      
        $scope.SelectedProc_Exist = false;
        $scope.chkSelectedProc = [];
        $scope.getLocations();
    }
    /* ============ Clear Top Search =========== */ 
    
    
    
    
    /* ============ CHECK SELECTED PROCE ARRAY =========== */ 
    $scope.SelectedProc_Exist = false;
    $scope.checkSPArray=()=>{
        var count = 0;
        $scope.chkSelectedProc.map((x)=>count += Number(x));
        $scope.SelectedProc_Exist = count > 0 ? true : false;
    }
    /* ============ CHECK SELECTED PROCE ARRAY =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            var r = confirm("Are you sure want to delete this record!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'RP_TRANSID': id.RP_TRANSID, 'type': 'delete' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getRepeatingProcTransaction.indexOf(id);
                        $scope.post.getRepeatingProcTransaction.splice(index, 1);
                        // console.log(data.data.message)
                        
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== DELETE =========== */
    




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


    /* ========== MESSAGE =========== */
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').show();
    //     jQuery('.alert-success').delay(5000).slideUp(function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').show();
    //     jQuery('.alert-danger').delay(5000).slideUp(function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }
    /* ========== MESSAGE =========== */




});