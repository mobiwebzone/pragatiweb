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
    $scope.PageSub = "INVENTORY_CHAPTER";
    $scope.editMode = false;
    
    var url = 'code/Inventory_Chapters_Master_code.php';



    

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
                    $scope.getProduct(); 
                    // $scope.getInventory();
                    // $scope.getInventoryChapters();
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
                formData.append("chapid", $scope.temp.chapid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("ddlInventory", $scope.temp.ddlInventory);
                formData.append("txtDescription", $scope.temp.txtDescription);
                formData.append("txtChapterNo", $scope.temp.txtChapterNo);
                formData.append("txtChapter", $scope.temp.txtChapter);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.chapid = '';
                $scope.temp.txtDescription = '';
                $scope.temp.txtChapterNo = '';
                $scope.temp.txtChapter = '';

                $scope.getInventoryChapters();
                // $scope.clearForm();
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



    /* ========== GET INVENTORY CHAPTERS =========== */
     $scope.getInventoryChapters = function () {
        $scope.post.getInventoryChapters = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getInventoryChapters','ddlLocation' : $scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getInventoryChapters = data.data.data;
            }else{
                $scope.post.getInventoryChapters = [];
            }
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getInventoryChapters(); --INIT
    /* ========== GET INVENTORY CHAPTERS =========== */


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
            if($scope.temp.ddlLocation > 0) $scope.getInventory();
            if($scope.temp.ddlLocation > 0) $scope.getInventoryChapters();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




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
    



    /* ========== GET INVENTORY =========== */
    $scope.getInventory = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.SpinINV').show();
        $http({
            method: 'post',
           url: 'code/Inventory_Master_code.php',
           data: $.param({ 'type': 'getInventories','ddlLocation' : $scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           if(data.data.success){
               $scope.post.getInventory = data.data.data;
           }else{
               $scope.post.getInventory = [];
           }
           $('.SpinINV').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getInventory(); --INIT
   /* ========== GET INVENTORY =========== */
   
   
   
   /* ========== GET INVENTORY DESC =========== */
   $scope.getInvDescription=()=>{
       if($scope.temp.ddlInventory > 0)
       $scope.temp.txtDescription = $scope.post.getInventory.filter((x)=> x.INVID == $scope.temp.ddlInventory).map((x)=> x.DESCR);
   }
   /* ========== GET INVENTORY DESC =========== */
    


    /* ============ Edit Button ============= */ 
    $scope.editDate = function (id) {
        $("#ddlProduct").focus();
        $scope.temp = {
            chapid:id.CHAPID,
            ddlLocation:id.LOCID.toString(),
            ddlProduct:id.PRODUCTID>0?(id.PRODUCTID).toString():'',
            ddlInventory:id.INVID>0?(id.INVID).toString():'',
            txtDescription:id.DESCR,
            txtChapterNo:id.CHAPNO>0?Number(id.CHAPNO):'',
            txtChapter:id.CHAPTER,
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getInventoryChapters.indexOf(id);
    }
    /* ============ Edit Button ============= */ 

    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlProduct").focus();
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
                data: $.param({ 'CHAPID': id.CHAPID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getInventoryChapters.indexOf(id);
		            $scope.post.getInventoryChapters.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearForm();
                    
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