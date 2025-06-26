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

$postModule.directive('gallery', ['$timeout', function ($timeout) {
    return {
        restrict: 'AC',
        link: function ($scope, $elm) {
            $timeout(function () {
                baguetteBox.run('.gallery',{
                    animation : "fadeIn",
                    titleTag : true,
                    captions: function(element) {
                        return element.getElementsByTagName('img')[0].alt;
                    }
                });
            });
        }
    };
}]);


$postModule.controller("myCtrl", function ($scope, $http,$timeout) {
    
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.PAGE = "GENERAL_INFO";
    $scope.PAGE_UNDER = "GALLERY";
    
    var url = 'code/Gallery_code.php';


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
                    formData.append("PAGE", 'GALLERY');
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


    /* ========== GET CATEGORY =========== */
    $scope.getMainCat = function () {
        jQuery('.spinnerData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getMainCat');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                // $scope.post.GalleryDiv = data.data.GalleryDiv;
                $scope.post.getMainCategory = data.data.data;
                // $scope.mainID=data.data.data[0]['GCATID'];
                // $scope.catText=data.data.data[0]['CATEGORY'];
                // $scope.MYGCATID=data.data.data[0]['GCATID'];
                // $scope.getImages( $scope.mainID,$scope.catText);

                // for(i=0; i<$scope.post.getMainCategory.length; i++){
                //     // baguetteBox.run(".gallery"+i+"", {
                //     //     animation: "fadeIn",
                //     //     buttons: true,
                //     // });
                  
                // }
            }else{
                $scope.post.getMainCategory = [];
            }
            jQuery('.spinnerData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getMainCat();
    /* ========== GET CATEGORY =========== */




    /* ========== GET IMAGES =========== */
    $scope.getImages = function (id,CatName) {
        jQuery('.spinnerData').show();
        $scope.catText = CatName;
        $scope.MYGCATID=id;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getImages' );
                formData.append("GCATID", id );
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getImages = data.data.data;
            }else{
                $scope.post.getImages = [];
            }
            jQuery('.spinnerData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getImages();
    /* ========== GET IMAGES =========== */





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
});