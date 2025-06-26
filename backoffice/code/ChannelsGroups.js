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
    $scope.PageSub1 = "CHANNEL&GROUP";
    $scope.temp.txtETADate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/ChannelsGroups_code.php';



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
                formData.append("mchid", $scope.temp.mchid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtChannel", $scope.temp.txtChannel);
                formData.append("txtDesc", $scope.temp.txtDesc);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.mchid=data.data.GET_MCHID;
                // $scope.clearForm();
                $scope.getChannels();
                if($scope.temp.mchid > 0){
                    $scope.getChannelGroups();
                }
                $timeout(()=>{$("#txtGroup").focus();},500);
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






    /* ========== GET CHANNELS =========== */
    $scope.getChannels = function () {
        $scope.post.getTestSection = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinnerChannels').show();
        $http({
            method: 'post',
            url: url,
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
            $('#SpinnerChannels').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getChannels(); --INIT
    /* ========== GET CHANNELS =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#txtChannel").focus();
        
        $scope.temp.mchid = id.MCHID;
        $scope.temp.ddlLocation = id.LOCID.toString();
        $scope.temp.txtChannel = id.CHANNEL;
        $scope.temp.txtDesc = id.DESCR;

        if($scope.temp.mchid > 0){
            $scope.getChannelGroups();
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getChannels.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtChannel").focus();
        $scope.temp={};
        // $scope.temp.mchid = '';
        // // $scope.temp.ddlLocation = '';
        // $scope.temp.txtChannel = '';
        // $scope.temp.txtDesc = '';
        // $scope.temp.txtSerarch = '';

        $scope.post.getChannelGroups = [];
        $scope.editMode = false;

        $scope.temp.mchgid = '';
        $scope.temp.txtGroup = '';
        $scope.temp.txtGroupLink = '';
        $scope.editModeGroup = false;
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
                data: $.param({ 'MCHID': id.MCHID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getChannels.indexOf(id);
		            $scope.post.getChannels.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% GROUP SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // =========== SAVE GROUP DATA ==============
    $scope.saveDataGroup = function(){
        $(".btn-save-group").attr('disabled', 'disabled');
        $(".btn-save-group").text('Saving...');
        $(".btn-update-group").attr('disabled', 'disabled');
        $(".btn-update-group").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataGroup');
                formData.append("mchgid", $scope.temp.mchgid);
                formData.append("mchid", $scope.temp.mchid);
                formData.append("txtGroup", $scope.temp.txtGroup);
                formData.append("txtGroupLink", $scope.temp.txtGroupLink);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormGroup();
                $scope.getChannelGroups();
                $scope.messageSuccess(data.data.message);
                $scope.getChannels();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-group').removeAttr('disabled');
            $(".btn-save-group").text('SAVE');
            $('.btn-update-group').removeAttr('disabled');
            $(".btn-update-group").text('UPDATE');
        });
    }
    // =========== SAVE GROUP DATA ==============




    
    
    /* ========== GET CHANNEL GROUPS =========== */
    $scope.getChannelGroups = function () {
        $('#spinGroups').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getChannelGroups', 'mchid' : $scope.temp.mchid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getChannelGroups = data.data.data;
            }else{
                $scope.post.getChannelGroups = [];
            }
            $('#spinGroups').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getChannelGroups();
    /* ========== GET CHANNEL GROUPS =========== */





    /* ============ Edit Group Button ============= */ 
    $scope.editFormGroup = function (id) {
        $("#txtGroup").focus();
        
        $scope.temp.mchgid = id.MCHGID;
        $scope.temp.txtGroup = id.CHANNELGROUP;
        $scope.temp.txtGroupLink = id.CHANNELLINK;
        
        $scope.editModeGroup = true;
        $scope.index = $scope.post.getChannelGroups.indexOf(id);
    }
    /* ============ Edit Group Button ============= */ 
    
    


    /* ============ Clear Group Form =========== */ 
    $scope.clearFormGroup = function(){
        $("#txtGroup").focus();
        $scope.temp.mchgid = '';
        $scope.temp.txtGroup = '';
        $scope.temp.txtGroupLink = '';
        $scope.editModeGroup = false;
    }
    /* ============ Clear Group Form =========== */ 




    /* ========== DELETE GROUP =========== */
    $scope.deleteGroup = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'MCHGID': id.MCHGID, 'type': 'deleteGroup' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getChannelGroups.indexOf(id);
		            $scope.post.getChannelGroups.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getChannels();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE GROUP =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getChannels();
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