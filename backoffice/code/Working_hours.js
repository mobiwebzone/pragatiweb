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
    $scope.PAGE_UNDER = "WORK_HOURS";
    $scope.editMode = false;
    // $scope.WORKING_HOURS=[];

    var url = 'code/Working_hours.php';

    $scope.dateFormat=function(datetime){
        if(datetime!=undefined){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+datetime.getDate();        
        }
    }

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

    

    
    // $scope.DisplayProductPlan=function (id) {
    //     sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
    //     sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
    //     window.location.assign('DisplayProduct.html');
    // }


        /* ========== GET Working Hours Data =========== */
        $scope.WorkingHoursData = function () {
            jQuery('.loader').removeClass('loaderHide');
            $http({
                method: 'post',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'WorkingHoursData');
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                $scope.WORKING_HOURS = data.data.WORKING_HOURS;

                jQuery('.loader').addClass('loaderHide');
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        $scope.WorkingHoursData();
        
        
        /* ========== GET Working Hours =========== */
        // $scope.getWorkingHours = function () {
        //     $http({
        //         method: 'post',
        //         url: 'backoffice/code/Working_Hours_BackOffice_code.php',
        //         processData: false,
        //         transformRequest: function (data) {
        //             var formData = new FormData();
        //             formData.append("type", 'getWorkingHours');
        //             return formData;
        //         },
        //         data: $scope.temp,
        //         headers: { 'Content-Type': undefined }
        //     }).
        //     then(function (data, status, headers, config) {
        //         // console.log(data.data);
        //         $scope.post.getWorkingHours = data.data.data;
        //     },
        //     function (data, status, headers, config) {
        //         console.log('Failed');
        //     })
        // }
        // $scope.getWorkingHours();

   
        /* ========== GET Products =========== */
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