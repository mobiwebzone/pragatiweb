$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.temp.chkPlans = [];
    $scope.PAGE = "CAREERS";
    $scope.editMode = false;

    var url = 'code/indexSearch.php';

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
        // $scope.SEARCH_TEXT=new URLSearchParams(window.location.search).get('search');
        $scope.SEARCH_TEXT=sessionStorage.getItem("SERACH_TEXT");
        $scope.searchIndex = !$scope.SEARCH_TEXT ? '' : $scope.SEARCH_TEXT;
        sessionStorage.setItem("SERACH_TEXT",'');
        $scope.searchCourses();
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
                    formData.append("PAGE", 'CAREERS');
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

    
    $scope.searchCourses = function(){
        if(!$scope.searchIndex) return;
        jQuery(".searchButton").attr('disabled', true);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'searchCourses');
                formData.append("searchIndex", $scope.searchIndex);             
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.productList = data.data.successP ? data.data.dataP : [];
            $scope.resourceList = data.data.successR ? data.data.dataR : [];

            if ($scope.productList.length>0 || $scope.resourceList.length>0) $scope.messageSuccess(`${($scope.productList.length+$scope.resourceList.length)} Records Found.`);
            if (!data.data.successP && !data.data.successR) $scope.messageFailure('Search Not found.');

            // history.replaceState(null, '', window.location.pathname);
            if(window.location.search)
            if($scope.SEARCH_TEXT && $scope.SEARCH_TEXT.length>0){
                // var baseUrl = window.location.origin + window.location.pathname;
                // window.location.replace(baseUrl);

                // document.getElementById("content").innerHTML = response.html;
                // document.title = response.pageTitle;
                // window.history.pushState({"html":response.html,"pageTitle":response.pageTitle},"", urlPath);

                window.onpopstate = function(event) {    
                    if(event && event.state) {
                        location.reload(); 
                    }
                }
            }
        
            jQuery(".searchButton").attr('disabled',false);
        });
    }




    /* ========== GET Locations =========== */
    $scope.getLocation = function () {
        $http({
            method: 'POST',
            url: url,
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
            $scope.post.getLocation = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
              
    }
    // $scope.getLocation();


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