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
    $scope.PageSub = "MARKETING";
    $scope.PageSub1 = "CAMPAIGN_TRANSACTION";
    $scope.temp.txtActivityDate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/CampaignTransaction_code.php';



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
                // $scope.getCampaign();
                // $scope.getChannels();
                // $scope.getTransactions();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% TANSACTION SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


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
                formData.append("mktransid", $scope.temp.mktransid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlCampaign", $scope.temp.ddlCampaign);
                formData.append("ddlChannel", $scope.temp.ddlChannel);
                formData.append("ddlChannelGroup", $scope.temp.ddlChannelGroup);
                formData.append("txtTarget", $scope.temp.txtTarget);
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.mktransid=data.data.GET_MKTRANSID;
                // $scope.clearForm();
                $scope.getTransactions();
                if($scope.temp.mktransid > 0){
                    $scope.getTransactionLogs();
                }
                $timeout(()=>{$("#ddlActivity").focus();},500);
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






    /* ========== GET TRANSACTION =========== */
    $scope.getTransactions = function () {
        $scope.post.getTransactions=[];
        // $scope.clearFormTransLog();
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinTransaction').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTransactions','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTransactions = data.data.data;
            }else{
                $scope.post.getTransactions=[];
                // console.info(data.data.message);
            }
            $('#SpinTransaction').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTransactions(); --INIT
    /* ========== GET TRANSACTION =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getCampaign();
            if($scope.temp.ddlLocation > 0) $scope.getChannels();
            if($scope.temp.ddlLocation > 0) $scope.getTransactions();           
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ========== GET CAMPAIGN =========== */
    $scope.getCampaign = function () {
        $scope.post.getCampaign=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.SpinnerCamp').show();
        $http({
            method: 'post',
            url: 'code/Campaign_code.php',
            data: $.param({ 'type': 'getCampaign','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getCampaign = data.data.data;
            }else{
                $scope.post.getCampaign=[];
                // console.info(data.data.message);
            }
            $('.SpinnerCamp').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCampaign(); --INIT
    /* ========== GET CAMPAIGN =========== */



    /* ========== GET CHANNELS =========== */
    $scope.getChannels = function () {
        $scope.post.getChannelGroups=$scope.post.getChannels=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinChannel').show();
        $http({
            method: 'post',
            url: 'code/ChannelsGroups_code.php',
            data: $.param({ 'type': 'getChannels','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getChannels = data.data.data;
            }else{
                $scope.post.getChannels=[];
                // console.info(data.data.message);
            }
            $('.spinChannel').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getChannels(); --INIT
    /* ========== GET CHANNELS =========== */



    /* ========== GET CHANNEL GROUPS =========== */
    $scope.getChannelGroups = function () {
        $('.spinGroups').show();
        $http({
            method: 'post',
            url: 'code/ChannelsGroups_code.php',
            data: $.param({ 'type': 'getChannelGroups', 'mchid' : $scope.temp.ddlChannel}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getChannelGroups = data.data.data;
            }else{
                $scope.post.getChannelGroups = [];
            }
            $('.spinGroups').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getChannelGroups();
    /* ========== GET CHANNEL GROUPS =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#ddlCampaign").focus();
        
        $scope.temp.mktransid = id.MKTRANSID;
        $scope.temp.ddlCampaign = (id.CAMPID).toString();
        $scope.temp.ddlChannel = (id.MCHID).toString();
        $scope.getChannelGroups();
        $timeout(()=>{
            $scope.temp.ddlChannelGroup = (id.MCHGID).toString();
        },500);
        $scope.temp.txtTarget = Number(id.TARGET);
        $scope.temp.txtRemark = id.REMARKS;

        if($scope.temp.mktransid > 0){
            $scope.getTransactionLogs();
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getTransactions.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;
        
        $scope.clearFormTransLog();
        $scope.post.getTransactionLogs = [];
        $("#ddlCampaign").focus();
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
                data: $.param({ 'MKTRANSID': id.MKTRANSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTransactions.indexOf(id);
		            $scope.post.getTransactions.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% TRANSACTION LOG SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // =========== SAVE DATA ==============
    $scope.saveDataTransLog = function(){
        $(".btn-save-TransLog").attr('disabled', 'disabled');
        $(".btn-save-TransLog").text('Saving...');
        $(".btn-update-TransLog").attr('disabled', 'disabled');
        $(".btn-update-TransLog").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataTransLog');
                formData.append("mklogid", $scope.temp.mklogid);
                formData.append("mktransid", $scope.temp.mktransid);
                formData.append("ddlActivity", $scope.temp.ddlActivity);
                formData.append("txtActivityDate", $scope.temp.txtActivityDate.toLocaleString('sv-SE'));
                formData.append("txtActivityCount", $scope.temp.txtActivityCount);
                formData.append("txtActivityRemark", $scope.temp.txtActivityRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormTransLog();
                $scope.getTransactionLogs();
                $scope.messageSuccess(data.data.message);
                $scope.getTransactions();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-TransLog').removeAttr('disabled');
            $(".btn-save-TransLog").text('SAVE');
            $('.btn-update-TransLog').removeAttr('disabled');
            $(".btn-update-TransLog").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============




    
    
    /* ========== GET DATA =========== */
    $scope.getTransactionLogs = function () {
        $('#spinTransLog').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTransactionLogs', 'mktransid' : $scope.temp.mktransid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTransactionLogs = data.data.data;
            }else{
                $scope.post.getTransactionLogs = [];
            }
            $('#spinTransLog').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTransactionLogs();
    /* ========== GET DATA =========== */





    /* ============ Edit Button ============= */ 
    


    $scope.editFormTransLog = function (id) {
        $("#ddlActivity").focus();
        $scope.temp.mklogid = id.MKLOGID;
        $scope.temp.ddlActivity = id.ACTIVITY;
        $scope.temp.txtActivityDate = new Date(id.ACTIVITY_DATE);
        $scope.temp.txtActivityCount = Number(id.ACTIVITY_COUNT);
        $scope.temp.txtActivityRemark = id.ACTIVITY_REMARK;
        $scope.index = $scope.post.getTransactionLogs.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearFormTransLog = function(){
        $("#ddlActivity").focus();
        $scope.temp.mklogid = '';
        $scope.temp.ddlActivity = '';
        $scope.temp.txtActivityCount = '';
        $scope.temp.txtActivityRemark = '';
        $scope.temp.txtActivityDate = new Date();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.deleteTransLog = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'MKLOGID': id.MKLOGID, 'type': 'deleteTransLog' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTransactionLogs.indexOf(id);
		            $scope.post.getTransactionLogs.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getTransactions();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
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




});