$postModule = angular.module("myApp", ["ngSanitize"]);

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
    $scope.PAGE = "GENERAL_INFO";
    $scope.PAGE_UNDER = "ANNOUNCEMENT";
    $scope.editMode = false;
    // $scope.WORKING_HOURS=[];

    var url = 'code/Announcement.php';

    $scope.dateFormat=function(datetime){
        if(datetime!=undefined){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+datetime.getDate();        
        }
    }

    $scope.DisplayProductPlan=function (id) {
        sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
        sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
        window.location.assign(`DisplayProduct.html?pl=${id.PDMID}&pt=${id.PTYPE}&pn=${encodeURIComponent(id.DISPLAY_PRODUCT).replace(/&/g, '\u{1F984}')}`,'_Blank');
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
            $scope.CATEGORIES = data.data.CATEGORIES;
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

        /* ========== GET HOME MENU =========== */
        $scope.getHomeMenu = function () {
            $http({
                method: 'POST',
                url: 'code/index.php',
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'getHomeMenu');
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.navigationItems = data.data.success ? data.data.data : [];
                
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        $scope.getHomeMenu();
        /* ========== GET CAROUSEL =========== */

        /*============ GET STATIC PAGE CAROUSEL =============*/ 
        $scope.staticPageCarousel = function () {
            $http({
                method: 'POST',
                url: 'code/Common.php',
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'staticPageCarousel');
                    formData.append("PAGE", 'ANNOUNCEMENTS');
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.staticPageCarousel = data.data.success ? data.data.data : [];
                $timeout(()=>{
                    jQuery('#carouselExampleInterval').carousel('cycle');
                },1000);
                
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        $scope.staticPageCarousel();
        /*============ GET STATIC PAGE CAROUSEL =============*/ 
    }



   
    /* ========== GET Products =========== */
    $scope.getProductDisplay = function () {
        $http({
            method: 'POST',
            url: 'backoffice/code/MASTER_API.php',
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
    
    
    /* ========== GET Working Hours Data =========== */
    $scope.getAnnouncements = function () {
        jQuery('.loader').removeClass('loaderHide');
        $http({
            method: 'post',
            url: 'backoffice/code/Announcements_code.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getAnnouncements');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.Announcements = data.data.data;

            jQuery('.loader').addClass('loaderHide');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getAnnouncements();

    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        // jQuery('.alert-success').delay(5000).slideUp(function () {
        //     jQuery('.alert-success > span').html('');
        // });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        // jQuery('.alert-danger').delay(5000).slideUp(function () {
        //     jQuery('.alert-danger > span').html('');
        // });
    }




});