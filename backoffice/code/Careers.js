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
    $scope.temp.chkPlans = [];
    $scope.PAGE = "CAREERS";
    $scope.editMode = false;

    var url = 'code/Careers.php';

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
    
    $scope.saveCareers = function(){
        $scope.SELECTEDPLANS='';
        for(i=0; i<$scope.temp.chkPlans.length; i++){
            $scope.plan=$scope.temp.chkPlans[i];

            if($scope.plan != '0'){
                $scope.SELECTEDPLANS=$scope.SELECTEDPLANS +' , '+ $scope.plan;
            }
        }
        
        // alert($scope.SELECTEDPLANS);
        jQuery(".btn-save").attr('disabled', 'disabled');
        jQuery(".btn-save").text('Saving...');
        jQuery(".btn-update").attr('disabled', 'disabled');
        jQuery(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCareers');
                formData.append("cid", $scope.temp.cid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtname", $scope.temp.txtname);
                formData.append("txtemail", $scope.temp.txtemail);
                formData.append("txtphone", $scope.temp.txtphone);
                formData.append("txtmaddress", $scope.temp.txtmaddress);
                formData.append("ddlplan", $scope.SELECTEDPLANS);
                formData.append("txtedubackground", $scope.temp.txtedubackground);
                formData.append("txtworkexp", $scope.temp.txtworkexp);
                formData.append("txtaddinfo", $scope.temp.txtaddinfo);
                //formData.append("txtIagree", $scope.temp.txtIagree);                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                
                $scope.messageSuccess(data.data.message);
                $scope.clearForm();
                
                //document.getElementById("txtfirstname").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            jQuery('.btn-save').removeAttr('disabled');
            jQuery(".btn-save").text('SAVE');
            jQuery('.btn-update').removeAttr('disabled');
            jQuery(".btn-update").text('UPDATE');
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
    $scope.getLocation();

    /* ========== GET Plan =========== */
    $scope.getPlans = function () {
        $scope.temp.chkPlans=[];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getPlans');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    $scope.getPlans();

   
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