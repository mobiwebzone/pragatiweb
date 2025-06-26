
$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$timeout) {

$scope.post = {};
$scope.temp = {};
$scope.index="indexPage";
$scope.PAGE = "HOME";

$scope.DisplayProductPlan=function (id) {
    sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
    sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
    window.location.assign('DisplayProduct.html');
}


    // GET COMMON DATA
    $scope.ANNSHOW = false;
    $scope.init = function () {
        $http({
            method: 'post',
            url: 'code/Common.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getDashBoardAnnouncement');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            
            if (data.data.success) {
                $scope.ANNSHOW = true;
                $scope.post.Announcement = data.data.data;
                $scope.ANN_DATE = data.data.data[0]['ANDATE'];
                $scope.ANN_TILLDATE = data.data.data[0]['DB_ANNOUNCE_TILLDATE'];
                $scope.ANN = data.data.data[0]['ANNOUNCEMENT'];
                $scope.ANN_LOC = data.data.data[0]['LOCATION'];
            }else{
                $scope.ANNSHOW = false;
                $scope.post.Announcement =[];
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }


$timeout(function () {
    
    jQuery('#Coursesdropdown').addClass('show');
},1000);
// document.getElementById('Coursesdropdown').className += ' show';


/* ========== GET Products Display =========== */
$scope.getProductDisplay = function () {
    $http({
        method: 'POST',
        url: 'backoffice/code/ProductDisplayMaster_code.php',
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'getProductDisplay');
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getProductDisplays = data.data.data;
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
$scope.getProductDisplay();



/* ========== GET Location =========== */
$scope.getLocation = function () {
    $http({
        method: 'POST',
        url: 'backoffice/code/Locations_code.php',
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'getLocation');
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getLocations = data.data.data;
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
$scope.getLocation();



    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.checkIcon').show();
        jQuery(".btn-save-text").text('Form Successfully Submitted');
        jQuery('.alert-success').delay(9000).slideUp(function () {
            jQuery('.alert-success > span').html('');
            jQuery(".btn-save-text").text('Submit the Form');
            jQuery('.checkIcon').hide();
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(10000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});