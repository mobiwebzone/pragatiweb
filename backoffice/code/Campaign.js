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
    $scope.PageSub1 = "CAMPAIGN";
    $scope.temp.txtStartDate = new Date();
    $scope.temp.txtEndDate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Campaign_code.php';



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
                // $scope.getGeoLocations();
                $scope.getProduct();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CAMPAIGN SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


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
                formData.append("campid", $scope.temp.campid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtCampaign", $scope.temp.txtCampaign);
                formData.append("txtCampDesc", $scope.temp.txtCampDesc);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtStartDate", $scope.temp.txtStartDate.toLocaleString('sv-SE'));
                formData.append("txtEndDate", $scope.temp.txtEndDate.toLocaleString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.campid=data.data.GET_CAMPID;
                // $scope.clearForm();
                $scope.getCampaign();
                if($scope.temp.campid > 0){
                    $scope.getCampGeoLocation();
                }
                $timeout(()=>{$("#ddlGeoLocation").focus();},500);
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






    /* ========== GET CAMPAIGN =========== */
    $scope.getCampaign = function () {
        $scope.post.getTestSection = [];
        $scope.post.getCampaign=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('#SpinnerCamp').show();
        $http({
            method: 'post',
            url: url,
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
            $('#SpinnerCamp').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCampaign(); --INIT
    /* ========== GET CAMPAIGN =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getGeoLocations();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ========== GET PRODUCT =========== */
    $scope.getProduct = function () {
        $http({
            method: 'post',
            url: 'code/Products_code.php',
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT
    /* ========== GET PRODUCT =========== */




    /* ========== GET GEOLOCATIONS =========== */
    $scope.getGeoLocations = function () {
        $scope.post.getGeoLocations=[];
        $scope.post.getTestSection = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinnerGeoLoc').show();
        $http({
            method: 'post',
            url: 'code/Geolocation_code.php',
            data: $.param({ 'type': 'getGeoLocations','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getGeoLocations = data.data.data;
            }else{
                $scope.post.getGeoLocations=[];
                // console.info(data.data.message);
            }
            $('#SpinnerGeoLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getGeoLocations(); --INIT
    /* ========== GET GEOLOCATIONS =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#txtCampaign").focus();
        
        $scope.temp.campid = id.CAMPID;
        $scope.temp.ddlLocation = id.LOCID.toString();
        $scope.temp.txtCampaign = id.CAMPAIGN;
        $scope.temp.txtCampDesc = id.CAMPAIGN_DESC;
        $scope.temp.ddlProduct = (id.PRODUCTID).toString();
        $scope.temp.txtStartDate = new Date(id.STARTDATE);
        $scope.temp.txtEndDate = new Date(id.ENDDATE);

        if($scope.temp.campid > 0){
            $scope.getCampGeoLocation();
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getGeoLocations.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.post.getCampGeoLocation = [];
        $scope.editMode = false;
        $scope.temp.txtStartDate = new Date();
        $scope.temp.txtEndDate = new Date();
        
        $scope.clearFormGeoLoc();
        $scope.post.getCampGeoLocation = [];
        $("#txtCampaign").focus();
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
                data: $.param({ 'CAMPID': id.CAMPID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getGeoLocations.indexOf(id);
		            $scope.post.getGeoLocations.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CAMP LOCATION SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // =========== SAVE DATA ==============
    $scope.saveDataGeoLoc = function(){
        $(".btn-save-GeoLoc").attr('disabled', 'disabled');
        $(".btn-save-GeoLoc").text('Saving...');
        $(".btn-update-GeoLoc").attr('disabled', 'disabled');
        $(".btn-update-GeoLoc").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataGeoLoc');
                formData.append("camplocid", $scope.temp.camplocid);
                formData.append("campid", $scope.temp.campid);
                formData.append("ddlGeoLocation", $scope.temp.ddlGeoLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormGeoLoc();
                $scope.getCampGeoLocation();
                $scope.messageSuccess(data.data.message);
                $scope.getCampaign();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-GeoLoc').removeAttr('disabled');
            $(".btn-save-GeoLoc").text('SAVE');
            $('.btn-update-GeoLoc').removeAttr('disabled');
            $(".btn-update-GeoLoc").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============




    
    
    /* ========== GET DATA =========== */
    $scope.getCampGeoLocation = function () {
        $('#spinGeoLoc').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCampGeoLocation', 'campid' : $scope.temp.campid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getCampGeoLocation = data.data.data;
            }else{
                $scope.post.getCampGeoLocation = [];
            }
            $('#spinGeoLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCampGeoLocation();
    /* ========== GET DATA =========== */





    /* ============ Edit Button ============= */ 
    $scope.editFormGeoLoc = function (id) {
        $("#ddlGeoLocation").focus();
        $scope.temp.camplocid = id.CAMPLOCID;
        $scope.temp.ddlGeoLocation = (id.GEOLOCID).toString();
        $scope.index = $scope.post.getCampGeoLocation.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearFormGeoLoc = function(){
        $("#ddlGeoLocation").focus();
        $scope.temp.camplocid = '';
        $scope.temp.ddlGeoLocation = '';
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.deleteGeoLoc = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'CAMPLOCID': id.CAMPLOCID, 'type': 'deleteGeoLoc' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCampGeoLocation.indexOf(id);
		            $scope.post.getCampGeoLocation.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getCampaign();
                    
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