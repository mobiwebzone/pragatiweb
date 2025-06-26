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
    $scope.PageSub1 = "GEOLOCATION";
    $scope.temp.txtETADate = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Geolocation_code.php';



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

                // $scope.getGeoLocations();
                $scope.getLocations();
                $scope.getCountries();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% GEOLOCATION SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


    // =========== SAVE DATA ==============
    $scope.saveDataGeoLoc = function(){
        $(".btn-save-GeoLoc").attr('disabled', 'disabled');
        $(".btn-save-GeoLoc").text('Adding...');
        $(".btn-update-GeoLoc").attr('disabled', 'disabled');
        $(".btn-update-GeoLoc").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataGeoLoc');
                formData.append("geolocid", $scope.temp.geolocid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtGeolocation", $scope.temp.txtGeolocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.geolocid=data.data.GET_GEOLOCID;
                // $scope.clearForm();
                $scope.getGeoLocations();
                if($scope.temp.geolocid > 0){
                    $scope.getGeoLocCities();
                }
                $timeout(()=>{$("#ddlCountry").focus();},500);
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-GeoLoc').removeAttr('disabled');
            $(".btn-save-GeoLoc").text('ADD');
            $('.btn-update-GeoLoc').removeAttr('disabled');
            $(".btn-update-GeoLoc").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============





    /* ========== GET GEOLOCATIONS =========== */
    $scope.getGeoLocations = function () {
        $scope.post.getTestSection=$scope.post.getGeoLocations=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinnerGeoLoc').show();
        $http({
            method: 'post',
            url: url,
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
            if($scope.temp.ddlLocation > 0) $scope.getGeoLocations();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */






    /* ============ Edit Button ============= */ 
    $scope.editFormGeoLoc = function (id) {
        $("#txtGeolocation").focus();
        
        $scope.temp.geolocid = id.GEOLOCID;
        $scope.temp.txtGeolocation = id.GEOLOCATION;

        if($scope.temp.geolocid > 0){
            $scope.getGeoLocCities();
            // $scope.clearFormGeoCity();
        }
        
        $scope.editModeGeoLoc = true;
        $scope.index = $scope.post.getGeoLocations.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearFormGeoLoc = function(){
        $scope.temp.geolocid = '';
        $scope.temp.txtGeolocation = '';
        $scope.editModeGeoLoc = false;

        
        $scope.clearFormGeoCity();
        $scope.post.getGeoLocCities = [];
        $("#txtGeolocation").focus();

        $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.deleteGeoLoc = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'GEOLOCID': id.GEOLOCID, 'type': 'deleteGeoLoc' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getGeoLocations.indexOf(id);
		            $scope.post.getGeoLocations.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormGeoLoc();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% ADD CITIES SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // =========== SAVE DATA ==============
    $scope.saveDataGeoCity = function(){
        $(".btn-save-GeoCity").attr('disabled', 'disabled');
        $(".btn-save-GeoCity").text('Saving...');
        $(".btn-update-GeoCity").attr('disabled', 'disabled');
        $(".btn-update-GeoCity").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataGeoCity');
                formData.append("glcityid", $scope.temp.glcityid);
                formData.append("geolocid", $scope.temp.geolocid);
                formData.append("ddlCountry", $scope.temp.ddlCountry);
                formData.append("ddlState", $scope.temp.ddlState);
                formData.append("ddlCity", $scope.temp.ddlCity);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormGeoCity();
                $scope.getGeoLocCities();
                $scope.messageSuccess(data.data.message);
                // $scope.getChannels();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-GeoCity').removeAttr('disabled');
            $(".btn-save-GeoCity").text('SAVE');
            $('.btn-update-GeoCity').removeAttr('disabled');
            $(".btn-update-GeoCity").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    
    
    /* ========== GET GEO LOCATION CITIES =========== */
    $scope.getGeoLocCities = function () {
        $('#spinGeoLocCity').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getGeoLocCities', 'geolocid' : $scope.temp.geolocid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getGeoLocCities = data.data.data;
            }else{
                $scope.post.getGeoLocCities = [];
            }
            $('#spinGeoLocCity').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getGeoLocCities();
    /* ========== GET GEO LOCATION CITIES =========== */




    /* ============ Edit Button ============= */ 
    $scope.editFormGeoCity = function (id) {
        $("#ddlCountry").focus();
        
        $scope.temp.glcityid = id.GLCITYID;
        $scope.temp.ddlCountry = (id.COUNTRYID).toString();
        $scope.getStates();
        $timeout(()=>{
            $scope.temp.ddlState = (id.STATEID).toString();
            $scope.getCities();
        },500);
        $timeout(()=>{
            $scope.temp.ddlCity = (id.CITYID).toString();
        },500);
        
        $scope.editModeGeoCity = true;
        $scope.index = $scope.post.getGeoLocCities.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearFormGeoCity = function(){
        $("#ddlCountry").focus();
        $scope.temp.glcityid = '';
        $scope.temp.ddlCountry = '';
        $scope.temp.ddlState = '';
        $scope.temp.ddlCity = '';
        $scope.post.getStates = [];
        $scope.post.getCities = [];
        $scope.editModeGeoCity = false;
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.deleteGeoCity = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'GLCITYID': id.GLCITYID, 'type': 'deleteGeoCity' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getGeoLocCities.indexOf(id);
		            $scope.post.getGeoLocCities.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.getChannels();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */










// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% COUNTRY/STATE/CITY %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    /* ========== Countries =========== */
    // ADD_UPDATE
    $scope.saveCountries = function(){
        $(".btn-save-Country").attr('disabled', 'disabled');
        $(".btn-save-Country").text('Saving...');
        $(".btn-update-Country").attr('disabled', 'disabled');
        $(".btn-update-Country").text('Updating...');
        $http({
            method: 'POST',
            url: 'code/Countries_code.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCountries');
                formData.append("countryid", $scope.temp.countryid);
                formData.append("txtCountry", $scope.temp.txtAddCountry);
                formData.append("txtSortName", $scope.temp.txtAddCountrySN);
                formData.append("txtFlagIcon", $scope.temp.txtFlagIcon);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $('#countryModal').modal('hide');
                $scope.getCountries();
                $("#txtAddCountry").focus();
                $scope.messageSuccess(data.data.message);
                $scope.temp.countryid = '';
                $scope.temp.txtAddCountry = '';
                $scope.temp.txtAddCountrySN = '';
                $scope.temp.txtFlagIcon = '';
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-Country').removeAttr('disabled');
            $(".btn-save-Country").text('SAVE');
            $('.btn-update-Country').removeAttr('disabled');
            $(".btn-update-Country").text('UPDATE');
        });
    }

    // GET
    $scope.getCountries = function () {
        $('.spinCountry').show();
        $http({
            method: 'post',
            url: 'code/Countries_code.php',
            data: $.param({ 'type': 'getCountries'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCountry = data.data.data;
            $('.spinCountry').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCountries(); --INIT
    
    // EDIT
    $scope.editCountryModal = function(id){
        $scope.temp.countryid = id.COUNTRYID;
        $scope.temp.txtAddCountry = id.COUNTRY;
        $scope.temp.txtAddCountrySN = id.COUNTRY_SC;
        $scope.temp.txtFlagIcon = id.FLAG_ICON;
        $scope.index = $scope.post.getCountry.indexOf(id);
    }
    // CLEAR
    $scope.clearCountryModal = function(){
        $scope.temp.countryid = '';
        $scope.temp.txtAddCountry = '';
        $scope.temp.txtAddCountrySN = '';
        $scope.temp.txtFlagIcon = '';
    }
    // DELETE
    $scope.deleteCountryModal = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: 'code/Countries_code.php',
                data: $.param({ 'countryid': id.COUNTRYID, 'type': 'deleteCountries' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCountry.indexOf(id);
		            $scope.post.getCountry.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.getChannels();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== Countries =========== */


    
    
    /* ========== States =========== */
    // ADD_UPDATE
    $scope.saveState = function(){
        $(".btn-save-State").attr('disabled', 'disabled');
        $(".btn-save-State").text('Saving...');
        $(".btn-update-State").attr('disabled', 'disabled');
        $(".btn-update-State").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveState');
                formData.append("stateid", $scope.temp.stateid);
                formData.append("ddlCountry", $scope.temp.ddlCountry);
                formData.append("txtAddState", $scope.temp.txtAddState );
                formData.append("txtAddStateSN", $scope.temp.txtAddStateSN);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $('#stateModal').modal('hide');
                $scope.temp.stateid = '';
                $scope.temp.txtAddState = '';
                $scope.temp.txtAddStateSN = '';
                $scope.getStates();
        
                // $scope.getCountries();
                $("#txtAddState").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-State').removeAttr('disabled');
            $(".btn-save-State").text('SAVE');
            $('.btn-update-State').removeAttr('disabled');
            $(".btn-update-State").text('UPDATE');
        });
    }

    // GET
    $scope.getStates = function () {
    $('.spinState').show();
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getStates','ddlCountry':$scope.temp.ddlCountry}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if(data.data.success){
            $scope.post.getStates = data.data.data;
        }else{
            $scope.post.getStates = [];
        }
        $('.spinState').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getStates();

    // EDIT
    $scope.editStateModal = function(id){
        $scope.temp.stateid = id.STATEID;
        $scope.temp.txtAddState = id.STATENAME;
        $scope.temp.txtAddStateSN = id.STATE_SC;
        $scope.index = $scope.post.getStates.indexOf(id);
    }
    // CLEAR
    $scope.clearStateModal = function(){
        $scope.temp.stateid = '';
        $scope.temp.txtAddState = '';
        $scope.temp.txtAddStateSN = '';
    }
    // DELETE
    $scope.deleteStateModal = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STATEID': id.STATEID, 'type': 'deleteStateModal' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStates.indexOf(id);
		            $scope.post.getStates.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.getChannels();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== States =========== */




    /* ========== Cities =========== */
    // ADD_UPDATE
    $scope.saveCity = function(){
        $(".btn-save-City").attr('disabled', 'disabled');
        $(".btn-save-City").text('Saving...');
        $(".btn-update-City").attr('disabled', 'disabled');
        $(".btn-update-City").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCity');
                formData.append("cityid", $scope.temp.cityid);
                formData.append("ddlState", $scope.temp.ddlState);
                formData.append("txtAddCity", $scope.temp.txtAddCity );
                formData.append("txtAddCitySN", $scope.temp.txtAddCitySN);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $('#stateModal').modal('hide');
                $scope.temp.cityid = '';
                $scope.temp.txtAddCity = '';
                $scope.temp.txtAddCitySN = '';
                $scope.getCities();
        
                // $scope.getCountries();
                $("#txtAddCity").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-City').removeAttr('disabled');
            $(".btn-save-City").text('SAVE');
            $('.btn-update-City').removeAttr('disabled');
            $(".btn-update-City").text('UPDATE');
        });
    }
    // GET
    $scope.getCities = function () {
    $('.spinCity').show();
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getCities','ddlState':$scope.temp.ddlState}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if(data.data.success){
            $scope.post.getCities = data.data.data;
        }else{
            $scope.post.getCities = [];
        }
        $('.spinCity').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getCities();
    // EDIT
    $scope.editCityModal = function(id){
        $scope.temp.cityid = id.CITYID;
        $scope.temp.txtAddCity = id.CITYNAME;
        $scope.temp.txtAddCitySN = id.CITY_SC;
        $scope.index = $scope.post.getCities.indexOf(id);
    }
    // CLEAR
    $scope.clearCityModal = function(){
        $scope.temp.cityid = '';
        $scope.temp.txtAddCity = '';
        $scope.temp.txtAddCitySN = '';
    }
    // DELETE
    $scope.deleteCityModal = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'CITYID': id.CITYID, 'type': 'deleteCityModal' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCities.indexOf(id);
		            $scope.post.getCities.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.getChannels();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== Cities =========== */
    
    
    
    
    

    
    
    
    /* ========== SET MODAL DATA =========== */
    $scope.setModalData=(FOR)=>{
        $scope.modalFor = FOR;
    }
    /* ========== SET MODAL DATA =========== */




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