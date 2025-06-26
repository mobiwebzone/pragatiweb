$postModule = angular.module("myApp", [ "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.PAGE = "CONTACTUS";
    
    var url = 'code/Contact_code.php';
    
    
    /* ========== Open Display Product Page =========== */
    $scope.DisplayProductPlan=function (id) {
        sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
        sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
        window.location.assign(`DisplayProduct.html?pl=${id.PDMID}&pt=${id.PTYPE}&pn=${encodeURIComponent(id.DISPLAY_PRODUCT).replace(/&/g, '\u{1F984}')}`,'_Blank');
    }
    /* ========== Open Display Product Page =========== */


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
                    formData.append("PAGE", 'CONTACT US');
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
    

    /* ========== Save Contact =========== */
    $scope.saveContact = function(){
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
                formData.append("type", 'saveContact');
                formData.append("cid", $scope.temp.cid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtname", $scope.temp.txtname);
                formData.append("txtemail", $scope.temp.txtemail);
                formData.append("txtphone", $scope.temp.txtphone);
                formData.append("txtsubject", $scope.temp.txtsubject);
                formData.append("txtmessage", $scope.temp.txtmessage);                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.clearForm();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            jQuery('.btn-save').removeAttr('disabled');
            jQuery(".btn-save").text('SAVE');
            jQuery('.btn-update').removeAttr('disabled');
            jQuery(".btn-update").text('UPDATE');
        });
    }
    /* ========== Save Contact =========== */


    
    

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
    /* ========== GET Products =========== */
    
    
    
    

    /* ========== GET Location =========== */
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
            $scope.post.getLocations = data.data.data;

            // CHECK 
            $scope.xsh=new URLSearchParams(window.location.search).get('xsh');
            if($scope.xsh && $scope.xsh==1) $scope.temp.ddlLocation = '1';
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getLocation();
    /* ========== GET Location =========== */
    
    
    
    
    
    /* ========== CLEAR =========== */
    $scope.clearForm=function () {  
        $scope.temp={};
        $scope.editMode = false;
    }
    
    /* ========== CLEAR =========== */

    
    
   
    
    
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
    /* ========== Logout =========== */





    /* ========== Message =========== */
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
    /* ========== Message =========== */
    



});