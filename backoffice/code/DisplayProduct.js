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
    $scope.PAGE = "HOME";
    $scope.PDMID=0;
    
    var url = 'code/DisplayProduct_code.php';

    $scope.DisplayProductPlan=function (id) {
        sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
        sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
        window.location.assign('DisplayProduct.html', '_Blank');
    }
    // Get PDMID
    if(sessionStorage.getItem("PRODUCT_PLANID") === null){
        window.location.assign('index.html#!/Home');
    }else
    {
        $scope.PDMID = sessionStorage.getItem("PRODUCT_PLANID");
        $scope.PRODUCTNAME = sessionStorage.getItem("PRODUCT_NAME");
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

     /*============ Get Display Plan =============*/ 
     $scope.DisplayPlan=function () {
        // alert($scope.PDMID);
        jQuery('.loader').show();
        
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'DisplayPlan');
                formData.append("PDMID", $scope.PDMID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDisplayPlan = data.data.data;
            $scope.post.GetPlanDetails = data.data.PlanDetail;

            jQuery('.loader').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.DisplayPlan();

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






    // ENROLL STUDENT
    $scope.EnrollStudent = function (locationid,planid) {
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