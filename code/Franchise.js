$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.PAGE = "FRANCHISE";
    
    var url = 'code/Franchise_code.php';

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
                    formData.append("PAGE", 'FRANCHIES');
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

    
    $scope.saveFranchise = function(){
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
                formData.append("type", 'saveFranchise');
                formData.append("faid", $scope.temp.faid);
                formData.append("txtfirstname", $scope.temp.txtfirstname);
                formData.append("txtmiddlename", $scope.temp.txtmiddlename);
                formData.append("txtlastname", $scope.temp.txtlastname);
                formData.append("txtdob", $scope.dateFormat($scope.temp.txtdob));                
                //formData.append("txtdob", $scope.temp.txtdob.getFullYear() + "/" + $scope.temp.txtdob.getMonth() + "/" + $scope.temp.txtdob.getDate());                
                formData.append("txtcellphone", $scope.temp.txtcellphone);
                formData.append("txtemail", $scope.temp.txtemail);
                formData.append("txtaddress1", $scope.temp.txtaddress1);
                formData.append("txtaddress2", $scope.temp.txtaddress2);
                formData.append("txtcity", $scope.temp.txtcity);
                formData.append("txtstate", $scope.temp.txtstate);
                formData.append("txtzip", $scope.temp.txtzip);
                formData.append("txtcitizen", $scope.temp.txtcitizen);
                formData.append("txteducatBack", $scope.temp.txteducatBack);
                formData.append("txtjobexp", $scope.temp.txtjobexp);
                formData.append("txtbusiness", $scope.temp.txtbusiness);
                formData.append("txttutoringexp", $scope.temp.txttutoringexp);
                formData.append("txtliquidfin", $scope.temp.txtliquidfin);
                formData.append("txtlistallfel", $scope.temp.txtlistallfel);
                formData.append("txtlistallpast", $scope.temp.txtlistallpast);
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
                //$scope.getFranchise();
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

    /* ============ Clear Form =========== */
    $scope.clearForm = function () {
        // document.getElementById("ddlLocation").focus();
        // $scope.FormName = 'Show Entry Form';
        $scope.temp = {};
        $scope.editMode = false;
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