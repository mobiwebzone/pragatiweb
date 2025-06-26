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
$postModule.controller("myCtrl", function ($scope, $http,$timeout,$interval) {

$scope.post = {};
$scope.temp = {};
$scope.test = {};
$scope.index="indexPage";
$scope.PAGE = "HOME";
$scope.GradeList = ['Pre-K','1','2','3','4','5','6','7','8','9','10','11','12','College','Other'];
$scope.cardList = [
    {'title':'1 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'2 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'3 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'4 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'5 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'6 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'7 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'8 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'9 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'},
    {'title':'10 Title','subtitle':'Card subtitle','desc':'Some quick example text to build on the card title and make up the bulk of the card content.'}
];

var url = 'code/index.php';

$scope.DisplayProductPlan=function (id) {
    sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
    sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
    window.location.assign(`DisplayProduct.html?pl=${id.PDMID}&pt=${id.PTYPE}&pn=${encodeURIComponent(id.DISPLAY_PRODUCT).replace(/&/g, '\u{1F984}')}`,'_Blank');
}

$scope.gotoProductPage = function(id){
    // console.log(id);
    var dt = {
                'PDMID':id.LOC_ID,
                'DISPLAY_PRODUCT':id.LOCATION,
                'PTYPE':'L',
            }
    $scope.DisplayProductPlan(dt);
}


$scope.gotoContactUs=function(){
    window.location.assign(`Contact-us.html?xsh=1`,'_Blank');
}


// GET COMMON DATA
$scope.ANNSHOW = false;
$scope.init = function () {
    $scope.getOnlineTeachers();
    $scope.getCarouselMaster();
    $scope.getScrollInfo();
    $scope.getBottomScrollData();

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
            console.log(data.data);
            $scope.navigationItems = data.data.success ? data.data.data : [];
            
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getHomeMenu();
    

    /* ========== GET HOME FOOTER MENU =========== */
    $scope.getHomeFooterMenu = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getHomeFooterMenu');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getHomeFooterMenu = data.data.success ? data.data.data : [];
            
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getHomeFooterMenu();
    /* ========== GET HOME FOOTER MENU =========== */


}


// $timeout(function () {
//     jQuery('#Coursesdropdown').addClass('show');
// },1000);
// document.getElementById('Coursesdropdown').className += ' show';


/* ========== GET Products Display =========== */
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
        console.log(data.data);
        $scope.post.getProductDisplays = data.data.success ? data.data.data : [];
        $scope.post.getLocationDisplays = $scope.post.getProductDisplays['LOCATIONS'].sort((a, b) => {
            if (a.PDMID === 1) {
                return -1;
            }
            if (b.PDMID === 1) {
                return 1;
            }
            return a.DISPLAY_PRODUCT.localeCompare(b.DISPLAY_PRODUCT); // Descending order for the rest
        });
        console.log($scope.post.getLocationDisplays)
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
$scope.getProductDisplay();



/* ========== SEARCH COURSES =========== */
$scope.searchIndex = '';
$scope.searchCourses = function(search){
    sessionStorage.setItem("SERACH_TEXT", search);
    window.open('indexSearch.html','_self');

}



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
        $scope.getLocations = data.data.data;
        console.log($scope.getLocations)
        $scope.post.getLocations = $scope.getLocations.sort((a, b) => {
            if (a.LOC_ID === 1) {
                return -1;
            }
            if (b.LOC_ID === 1) {
                return 1;
            }
            return a.LOCATION.localeCompare(b.LOCATION); // Descending order for the rest
        });
        // console.log($scope.post.getLocations)
        
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
$scope.getLocation();


$scope.DEFAULT_CAROUSEL = [
    {PIC: 'images/banner1.jpg', PIC_CAPTION: '',PIC_INTERVAL:2000},
    {PIC: 'images/banner2.jpg', PIC_CAPTION: '',PIC_INTERVAL:2000},
    {PIC: 'images/banner3.jpg', PIC_CAPTION: '',PIC_INTERVAL:2000}
];
$scope.DEFAULT_SCROLLBAR_DATA = [
    {OBJECTTYPE:'IMAGE', OBJECTNAME: 'images/Professional.jpg', TITLE: 'PROFESSIONAL SERVICE',TITLE_DESC:"As experts in our field and leaders in our industry, we're committed to upholding the highest standard of service for all our customers.",LINK:''},
    {OBJECTTYPE:'IMAGE', OBJECTNAME: 'images/Personal.jpeg', TITLE: 'PERSONAL APPROACH',TITLE_DESC:'We take the time to get to know our Students, their strengths, and weaknesses, ensuring effective tutoring.',LINK:''},
    {OBJECTTYPE:'IMAGE', OBJECTNAME: 'images/Qualified.jpg', TITLE: 'QUALIFIED TEACHERS',TITLE_DESC:'All of our teachers are are highly qualified and experienced ensuring quick and improved results.',LINK:''}
];

 /* ========== GET CAROUSEL =========== */
 $scope.getCarouselMaster = function () {
    $http({
        method: 'POST',
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'getCarouselMaster');
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCarouselMaster = data.data.success ? data.data.data : $scope.DEFAULT_CAROUSEL;
        $timeout(()=>{
            jQuery('#carouselExampleInterval').carousel('cycle');
        },1000);
        
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getCarouselMaster(); --INIT
/* ========== GET CAROUSEL =========== */


/* ========== GET SCROLL INFO =========== */
$scope.getScrollInfo = function () {
    $http({
        method: 'POST',
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'getScrollInfo');
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getScrollInfo = data.data.success ? data.data.SCROLLS : {};  
        $scope.Scrolls_length = (!$scope.post.getScrollInfo || Object.keys($scope.post.getScrollInfo).length<=0) ? 0 : Object.keys($scope.post.getScrollInfo).length;
        // console.log(Object.keys($scope.post.getScrollInfo).length);
        
        // console.log()
        // $timeout(()=>{
        //     var height = document.getElementById('carousel_tab').clientHeight;
        //     var per = 40;
        //     console.log('reduse Height:', (Math.floor((height * per) / 100)));
        //     height = Math.floor(height-((height * per) / 100));
        //     console.log('Height of the div:', height,'/',(Math.floor(height/per)));
        //     // $scope.Scrolls_height = $scope.Scrolls_length <= 2 ? '160px' : '80px';
        //     $scope.Scrolls_height = $scope.Scrolls_length <= 2 ? `${height}px` : `${Math.floor(height/2)}px`;
        // },700);
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getCarouselMaster(); --INIT
/* ========== GET SCROLL INFO =========== */

/* ========== GET BOTTOM SCROLLBAR DATA =========== */
 $scope.getBottomScrollData = function () {
    $http({
        method: 'POST',
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'getBottomScrollData');
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getBottomScrollData = data.data.success ? data.data.data : $scope.DEFAULT_SCROLLBAR_DATA;
        // $scope.post.getBottomScrollData =  $scope.DEFAULT_SCROLLBAR_DATA;
        if($scope.post.getBottomScrollData.length>4){
            jQuery(document).ready(function () {
                jQuery("#demo1").als({
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
                jQuery("#demo1").als({
                    // visible_items: "auto",
                    visible_items: 4,
                    scrolling_items: 2,
                    orientation: "horizontal",
                    circular: "no",
                    autoscroll: "no"
                });
            });
        }
        
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getBottomScrollData(); --INIT
/* ========== GET BOTTOM SCROLLBAR DATA =========== */





/* =========== SAVE TEST FORM =========== */
$scope.saveTestForm = function(){
    jQuery(".btnSave").attr('disabled', 'disabled').text('Saving...');
    $http({
        method: 'POST',
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'saveTestForm');
            formData.append("txtFName", $scope.test.txtFName);
            formData.append("txtLName", $scope.test.txtLName);
            formData.append("txtGrade", $scope.test.txtGrade);
            formData.append("txtPhone", $scope.test.txtPhone);
            formData.append("txtEmail", $scope.test.txtEmail);
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
            sessionStorage.setItem("REGID_index", data.data.REGID);
            $scope.clearTestForm();
            $scope.messageSuccess(data.data.message);
            window.location.assign('Free-Assessment.html');
        }
        else {
            $scope.messageFailure(data.data.message);
            // console.log(data.data)
        }
        jQuery('.btnSave').removeAttr('disabled').text('Next');
    });
}
/* =========== SAVE TEST FORM =========== */


/* =========== CLEAR TEST FORM =========== */
$scope.clearTestForm = function () { 
    $scope.test = {};
}
/* =========== CLEAR TEST FORM =========== */



/* ========== GET ONLINE TEACHERS =========== */
$scope.itsChecking = false;
$scope.TOTALTEACHERS = 0;
$scope.getOnlineTeachers = function () {
    // alert();
    $scope.itsChecking = true;
    $http({
        method: 'POST',
        url: 'code/index.php',
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'getOnlineTeachers');
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getAvailableSubjects = data.data.success ? data.data.data : [];
        $scope.TOTALTEACHERS = data.data.successTOTAL ? data.data.TOTAL_ONLINE_TEACHERS : 0;
        $scope.itsChecking = false;
        
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getOnlineTeachers();

var fetchDataInterval = $interval(()=>{
    if(!$scope.itsChecking) $scope.getOnlineTeachers();
},5000);

// Clean up the interval when the controller is destroyed
$scope.$on('$destroy', function () {
    $interval.cancel(fetchDataInterval);
});
/* ========== GET ONLINE TEACHERS =========== */

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