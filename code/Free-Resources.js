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


$postModule.controller("myCtrl", function ($scope, $http,$timeout,$sce) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.PAGE = "FREE_RES";
    $scope.FormName = 'Show Entry Form';
    $scope.PAGE_UNDER = 0;
    var url = 'code/Free-Resources_code.php';

    // $scope.GETU_ID= !new URLSearchParams(window.location.search).get('cat') ? 0 : Number(new URLSearchParams(window.location.search).get('cat'));
    // if($scope.GETU_ID > 0){
    //     $scope.PAGE_UNDER = $scope.GETU_ID;
    // }


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

                $scope.GETU_ID= !new URLSearchParams(window.location.search).get('cat') ? 0 : Number(new URLSearchParams(window.location.search).get('cat'));
                if($scope.GETU_ID > 0){
                    $scope.PAGE_UNDER = $scope.GETU_ID;
                    $scope.getUnderCategory($scope.GETU_ID);
                    $scope.resourceCarousel();
                }
                
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
    }

    $scope.getDet = function(id){
        // console.log(id);
        $scope.mediaSrc = '';
        $scope.mediaSrc = !id?'':$sce.trustAsResourceUrl(id);
    }


    /* ========== GET Free Resource Main Category =========== */
    $scope.getMainCat = function () {
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
            $scope.post.getMainCategory = data.data.data;
            $scope.mainID=data.data.data[0][0];
            $scope.catText=data.data.data[0][2];
            $scope.getUnderCategory( $scope.mainID,$scope.catText);
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMainCat();

// $scope.test=function() {
//     alert();
// }
    /* ========== GET Free Resource Under Cat =========== */
    $scope.getUnderCategory = function (id) {
        console.log(id);
        jQuery('.spinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTree' );
                formData.append("underid", id );
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getUnderCat = data.data.data;
            $scope.post.ListField = data.data.treeString;
            jQuery('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
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

    $scope.toggleCollapse = function (id) {
        var essayCollapse = document.getElementById(`col${id}`);
        var essayCollapseArrow = document.getElementById(`arrow${id}`);
        // console.log(essayCollapse);
        essayCollapse.classList.toggle('expanded');
        
        if (essayCollapse.classList.contains('expanded')) {
            essayCollapse.style.maxHeight = essayCollapse.scrollHeight + 'px';
            essayCollapseArrow.classList.toggle('fa-sort-desc');
            essayCollapseArrow.classList.toggle('fa-sort-asc');
        } else {
            essayCollapse.style.maxHeight = '100px';
            essayCollapseArrow.classList.toggle('fa-sort-desc');
            essayCollapseArrow.classList.toggle('fa-sort-asc');
            // essayCollapseArrow.style.transform = 'rotateX(0deg)';
        }
    }



    // =========== VIEW STUDENT HOME WORK IMAGE ==============
    $scope.viewRecImages=function(id){
        console.log(id);
        $scope.HOMEWORK_IMAGE_SET = [];
        if(id=='')return;
            var IMAGES = id.split(", ");
            console.log(IMAGES);
            for($i=0; $i<IMAGES.length; $i++){
                $scope.HOMEWORK_IMAGE_SET.push({src: `backoffice/images/free_resources/${IMAGES[$i]}`,title: IMAGES[$i]})
            }
        
        
        // define options (if needed)
        var options = {
            // optionName: 'option value'
            // for example:
            index: 0, // this option means you will start at first image
            keyboard:true,
            title:true,
            fixedModalSize:true,
            modalWidth: 500,
            modalHeight: 500,
            fixedModalPos:true,
            footerToolbar: ['zoomIn','zoomOut','prev','fullscreen','next','actualSize','rotateRight'],
            // ,'myCustomButton'

            // customButtons: {
            //     myCustomButton: {
            //       text: '',
            //       title: 'Click To Download',
            //       click: function (context, e) {
            //         // alert('clicked the custom button!');
            //         var link = document.createElement('a');
            //         link.href = `../student_zone/images/homework/${id.homework_img}`;
            //         link.download = id.homework_img;
            //         document.body.appendChild(link);
            //         link.click();
            //         document.body.removeChild(link);
            //       }
            //     }
            //   }
        };
        
        // Initialize the plugin
        var photoviewer = new PhotoViewer($scope.HOMEWORK_IMAGE_SET, options);    
        // $('.photoviewer-button-myCustomButton').addClass('bg-success rounded border brder-success mt-2').css({"height": "34px"});            
        // $('.photoviewer-button-myCustomButton').html('<i class="fa fa-download" aria-hidden="true"></i>');            
        
    }
    // =========== VIEW STUDENT HOME WORK IMAGE ==============


    /*============ GET RESOURCE CATEGORY CAROUSEL =============*/ 
    $scope.resourceCarousel = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'resourceCarousel');
                formData.append("ID", $scope.GETU_ID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.resourceCarousel = data.data.success ? data.data.data : [];
            $timeout(()=>{
                jQuery('#carouselExampleInterval').carousel('cycle');
            },1000);
            
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.resourceCarousel(); --INIT
    /*============ GET RESOURCE CATEGORY CAROUSEL =============*/ 



});