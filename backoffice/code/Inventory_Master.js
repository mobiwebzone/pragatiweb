$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.Page = "SETTING";
    $scope.PageSub = "INVENTORY";
    $scope.editMode = false;
    
    var url = 'code/Inventory_Master_code.php';



    

    /* ========== CHECK SESSION =========== */
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: 'code/checkSession.php',
            data: $.param({ 'type': 'checkSession' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);

            if (data.data.success) {
                $scope.post.user = data.data.data;
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    $scope.getMainCategory();
                    $scope.getProduct(); 
                    $scope.getPublishers(); 
                    $scope.getInventoryType();
                    // $scope.getInventories();
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ========== CHECK SESSION =========== */



    /* ========== SAVE DATA =========== */
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("invid", $scope.temp.invid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlMainCategory", $scope.temp.ddlMainCategory);
                formData.append("ddlCategory", $scope.temp.ddlCategory);
                formData.append("ddlSubCategory", $scope.temp.ddlSubCategory);
                formData.append("ddlTopic", $scope.temp.ddlTopic);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtTitle", $scope.temp.txtTitle);
                formData.append("txtDescription", $scope.temp.txtDescription);
                formData.append("txtCost", $scope.temp.txtCost);
                formData.append("ddlPublisher", $scope.temp.ddlPublisher);
                formData.append("ddlCapitalExpense", $scope.temp.ddlCapitalExpense);
                formData.append("ddlInventoryType", $scope.temp.ddlInventoryType);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getInventories();
                $scope.clearForm();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }
    /* ========== SAVE DATA =========== */



    /* ========== GET INVENTORIES =========== */
     $scope.getInventories = function () {
        $scope.post.getInventories = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getInventories','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getInventories = data.data.data;
            }else{
                $scope.post.getInventories = [];
            }
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getInventories(); --INIT
    /* ========== GET INVENTORIES =========== */


    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getInventories();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ========== GET MAIN CATEGORY =========== */
    $scope.getMainCategory = function () {
        $('.SectionSpin').show();
        $http({
            method: 'post',
            url: 'code/Question_Master_code.php',
            data: $.param({ 'type': 'getSectionMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMainCategory = data.data.data;
            $('.SectionSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMainCategory(); --INIT
    /* ========== GET MAIN CATEGORY =========== */




    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function () {
        $('.CategorySpin').show();
        $scope.post.getCategories=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getCategories', 'secid' : $scope.temp.ddlMainCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.data;
            $('.CategorySpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories();
    /* ========== GET CATEGORIES =========== */




    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function (catid) {
        $('.SubCatSpin').show();
        $scope.post.getSubCategories=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'catid' : $scope.temp.ddlCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubCategories = data.data.data;
            $('.SubCatSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */





    /* ========== GET TOPICS =========== */
    $scope.getTopic = function (subcatid) {
        $('.TopicSpin').show();
        $scope.post.getTopic=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getTopic', 'subcatid' : $scope.temp.ddlSubCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTopic = data.data.data;
            $('.TopicSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTopic();
    /* ========== GET TOPICS =========== */



    /* ========== GET PRODUCT =========== */
    $scope.getProduct = function () {
        $('.ProductSpin').show();
        $http({
            method: 'post',
            url: 'code/Products_code.php',
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
            $('.ProductSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT
    /* ========== GET PRODUCT =========== */




    /* ========== GET PUBLISHERS =========== */
    $scope.getPublishers = function () {
        $('#SpinPublisher').show();
        $http({
            method: 'post',
            url: 'code/Make_Publishers_code.php',
            data: $.param({ 'type': 'getPublishers'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getPublishers = data.data.data;
            }else{
                $scope.post.getPublishers = [];
            }
            $('#SpinPublisher').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPublishers(); --INIT
    /* ========== GET PUBLISHERS =========== */
    


    /* ========== GET INVENTORY TYPE =========== */
    $scope.getInventoryType = function () {
        $('#SpinINVType').show();
        $http({
            method: 'post',
           url: 'code/Inventory_Types_Master_code.php',
           data: $.param({ 'type': 'getInventoryType'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           if(data.data.success){
               $scope.post.getInventoryType = data.data.data;
           }else{
               $scope.post.getInventoryType = [];
           }
           $('#SpinINVType').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getInventoryType(); --INIT
   /* ========== GET INVENTORY TYPE =========== */
    


    /* ============ Edit Button ============= */ 
    $scope.editDate = function (id) {
        $("#ddlMainCategory").focus();
        $scope.temp = {
            invid:id.INVID,
            ddlLocation:id.LOCID.toString(),
            ddlMainCategory:(id.SECID).toString(),
            // ddlCategory:,
            // ddlSubCategory:,
            // ddlTopic:,
            ddlProduct:id.PRODUCTID>0?(id.PRODUCTID).toString():'',
            txtTitle:id.TITLE,
            txtDescription:id.DESCR,
            txtCost:id.COST>0?Number(id.COST):'',
            ddlPublisher:id.PUBID>0?(id.PUBID).toString():'',
            ddlCapitalExpense:id.ITYPE!=''?id.ITYPE:'',
            ddlInventoryType:id.ITID>0?(id.ITID).toString():'',
        };

        if($scope.temp.ddlMainCategory > 0){
            $scope.getCategories();
            $timeout(function () {  
                $scope.temp.ddlCategory = (id.CATID).toString();
                if($scope.temp.ddlCategory > 0){
                    $scope.getSubCategories();
                    $timeout(function () { 
                        $scope.temp.ddlSubCategory = (id.SUBCATID).toString();
                        if($scope.temp.ddlSubCategory > 0){
                            $scope.getTopic();
                            $timeout(function () { 
                                $scope.temp.ddlTopic = (id.TOPICID).toString();
                            },550);
                        }
                    },450);
    
                }
            },300);

        }

        $scope.editMode = true;
        $scope.index = $scope.post.getInventories.indexOf(id);
    }
    /* ============ Edit Button ============= */ 

    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlMainCategory").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'INVID': id.INVID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getInventories.indexOf(id);
		            $scope.post.getInventories.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.clearForm();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    


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