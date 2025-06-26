$postModule = angular.module("myApp", [ "ngSanitize"]);
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
    $scope.PAGE = "DISPLAY PRODUCT";
    $scope.PDMID=0;
    
    var url = 'code/DisplayProduct_code.php';

    $scope.DisplayProductPlan=function (id) {
        sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
        sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
        window.location.assign(`DisplayProduct.html?pl=${id.PDMID}&pt=${id.PTYPE}&pn=${encodeURIComponent(id.DISPLAY_PRODUCT).replace(/&/g, '\u{1F984}')}`,'_Blank');
    }

    // if(sessionStorage.getItem("PRODUCT_PLANID") === null){
    //     window.location.assign('index.html#!/Home');
    // }else
    // {
    //     $scope.PDMID = sessionStorage.getItem("PRODUCT_PLANID");
    //     $scope.PRODUCTNAME = sessionStorage.getItem("PRODUCT_NAME");
    // }

    
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

                // Get PDMID
            $scope.PDMID=new URLSearchParams(window.location.search).get('pl');
            $scope.PRODUCTNAME = new URLSearchParams(window.location.search).get('pn');
            $scope.PTYPE = new URLSearchParams(window.location.search).get('pt');
            if(!$scope.PDMID || $scope.PDMID==0){
                window.location.assign('index.html#!/Home');
            }else{
                $scope.productCarousel();
                $scope.getFooterMenu();
                $scope.DisplayPlan();
            }
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

    }



    /* ========== GET CAROUSEL =========== */
    $scope.productCarousel = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'productCarousel');
                formData.append("PDMID", $scope.PDMID);
                formData.append("PTYPE", $scope.PTYPE);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.productCarousel = data.data.success ? data.data.data : [];
            $timeout(()=>{
                jQuery('#carouselExampleInterval').carousel('cycle');
            },1000);
            
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.productCarousel(); --INIT
    /* ========== GET CAROUSEL =========== */



    /* ========== GET FOOTER MENU =========== */
    $scope.getFooterMenu = function () {
        $scope.post.getFooterMenu = [];
        if($scope.PTYPE != 'P') return;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getFooterMenu');
                formData.append("PDMID", $scope.PDMID);
                formData.append("PTYPE", $scope.PTYPE);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getFooterMenu = data.data.success ? data.data.data : [];
            
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getFooterMenu(); --INIT
    /* ========== GET FOOTER MENU =========== */



    /*============ Get Display Plan =============*/ 
    $scope.showTabs = false;
    $scope.DisplayPlan=function () {
        // alert($scope.PDMID);
        $scope.showTabs = false;
        jQuery('.loader').show();
        
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", $scope.PTYPE=='P'?'DisplayPlan':'DisplayPlanAll');
                formData.append("PDMID", $scope.PDMID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDisplayPlan = data.data.data;
            $scope.totalScrollBar = data.data.totalScrollBar;
            $scope.post.GetPlanDetails = data.data.PlanDetail;
            $scope.showTabs = true;

            $scope.post.productReviews = data.data.successReview ? data.data.productReviews : [];
            $scope.post.productMaterials = data.data.successMaterial ? data.data.productMaterials : [];

            // SCROLLBAR
            if($scope.totalScrollBar>0){
                for(i=0;i<$scope.totalScrollBar;i++){
                    var idx = i+1;
                    if($scope.totalScrollBar>4){
                        jQuery(document).ready(function () {
                            jQuery("#demo"+idx).als({
                                visible_items: "auto",
                                // visible_items: 4,
                                // scrolling_items: 3,
                                orientation: "horizontal",
                                circular: "no",
                                autoscroll: "no"
                            });
                        });
                    }else{
                        jQuery(document).ready(function () {
                            jQuery("#demo"+idx).als({
                                // visible_items: "auto",
                                visible_items: 4,
                                scrolling_items: 2,
                                orientation: "horizontal",
                                circular: "no",
                                autoscroll: "no"
                            });
                        });
                    }
                }
            }


            jQuery('.loader').hide();

            jQuery(document).ready(function() {
                // Get the height of the left div
                var leftDivHeight = jQuery('.centerDiv').height();
                // alert(leftDivHeight);
                // Set the height of the right div to match the left div
                jQuery('.leftDiv, .rightDiv').height(leftDivHeight-45);
            });



        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.DisplayPlan();


    /*============ Get Display Topic =============*/ 
    $scope.PopDisplayTopic=function (pid,pname) {
        // $scope.post.getDisplayTopic=[];
        // alert(pid+'/'+pname);
        $scope.PopTitle=pname;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'DisplayTopic');
                formData.append("PRODUCTID", pid);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDisplayTopic = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }



    /*============ Get Display Plan Product =============*/ 
    $scope.DisplayPlanProduct=function (id) {
        // alert(id.PLANID);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'DisplayPlanProduct');
                formData.append("PLANID", id.PLANID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDisplayPlanProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.DisplayPlanProduct();

   


    /* ========== GET Header Product Name =========== */
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






    // ENROLL STUDENT
    $scope.EnrollStudent = function (locationid,planid) {
        console.log(locationid+'///'+planid),
        sessionStorage.setItem("ENROLL_LOCATIONID", locationid);
        sessionStorage.setItem("ENROLL_PLANID", planid);
        window.open('Student_Registration.html',"_self");
    }
   
    
    


   


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



    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});