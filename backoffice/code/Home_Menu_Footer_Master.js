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
    $scope.editMode = false;
    $scope.editModeItem = false;
    $scope.Page = "SETTING";
    $scope.PageSub = "HOME_MENU_FOOTER";
    
    var url = 'code/Home_Menu_Footer_Master.php';
    var masterUrl = 'code/MASTER_API.php';
    
    // GET DATA
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
                    $scope.getMenuData();
                    $scope.getProductDisplayWithoutLoc();
                    $scope.getResources();
                    // $scope.getLocations();
                    // $scope.getMeetingLinks();
                }
                // window.location.assign("dashboard.html");
            }
            else {
                // window.location.assign('index.html#!/login')
                // alert
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }


    $scope.printTable = function(FOR){
        if(FOR == 'MENU'){
            $('#MENU_TAB').removeClass('col-lg-6');
            $('#ITEM_TAB').hide();
            window.print();
            $timeout(()=>{
                $('#MENU_TAB').addClass('col-lg-6');
                $('#ITEM_TAB').show();
            },300);
        }else{
            $('#ITEM_TAB').removeClass('col-lg-6');
            $('#MENU_TAB').hide();
            window.print();
            $timeout(()=>{
                $('#ITEM_TAB').addClass('col-lg-6');
                $('#MENU_TAB').show();
            },300);
        }
    }

    // ##############################################
    //                      MENU
    // ##############################################
    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("footid", $scope.temp.footid);
                formData.append("txtHeading", $scope.temp.txtHeading);
                formData.append("txtSeqno", $scope.temp.txtSeqno);
                formData.append("txtColor", $scope.temp.txtColor);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.footid = data.data.FOOTID;
                $scope.messageSuccess(data.data.message);
                $scope.getMenuData();
                $("#txtHeading").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET MENU DATA =========== */
    $scope.getMenuData = function () {
         $scope.spinMenu = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getMenuData'}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMenuData = data.data.success ? data.data.data : [];
            $scope.spinMenu = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMenuData(); 


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $("#txtHeading").focus();
        $scope.temp.footid=id.FOOTID;
        $scope.temp.txtHeading=id.HEADING;
        $scope.temp.txtSeqno=Number(id.SEQNO);
        $scope.temp.txtColor=id.COLOR;
        $scope.editMode = true;
        $scope.index = $scope.post.getMenuData.indexOf(id);
        $scope.getMenuItemData();
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $("#txtHeading").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getMenuItemData=[];
        $scope.clearItem();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'FOOTID': id.FOOTID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getMenuData.indexOf(id);
		            $scope.post.getMenuData.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }



    // ##############################################
    //                  MENU ITEM
    // ##############################################
    $scope.saveItem = function(){
        $(".btn-save-item").attr('disabled', true).text('Saving...');
        $(".btn-update-item").attr('disabled', true).text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveItem');
                formData.append("footdetid", $scope.temp.footdetid);
                formData.append("footid", $scope.temp.footid);
                formData.append("ddlProductResource", $scope.temp.ddlProductResource);
                formData.append("PROC_RES_ID", $scope.temp.ddlProductResource=='PRODUCT' ? $scope.temp.ddlProduct : $scope.temp.ddlResource);
                formData.append("txtSeqno", $scope.temp.txtItemSeqno);
                formData.append("isHeader", $scope.temp.isHeader);
                formData.append("txtHeader", $scope.temp.txtHeader);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getMenuItemData();
                $scope.clearItem();
                $("#ddlProduct").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-item').attr('disabled',false).text('SAVE');
            $('.btn-update-item').attr('disabled',false).text('UPDATE');
        });
    }


    /* ========== GET MENU ITEM DATA =========== */
    $scope.getMenuItemData = function () {
         $scope.spinItem = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getMenuItemData','FOOTID':$scope.temp.footid}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMenuItemData = data.data.success ? data.data.data : [];
            $scope.spinItem = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMenuItemData(); 


    /* ========== GET MENU ITEM DATA =========== */
    $scope.getProductDisplayWithoutLoc = function () {
         $scope.spinPro = true;
         $http({
             method: 'post',
             url: masterUrl,
             data: $.param({ 'type': 'getProductDisplayWithoutLoc'}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProductDisplayWithoutLoc = data.data.success ? data.data.data : [];
            $scope.spinPro = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProductDisplayWithoutLoc(); 

    /* ========== GET RESOURCE DATA =========== */
    $scope.getResources = function () {
        $scope.spinRes = true;
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getResourcesTopLavel'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getResources = data.data.success ? data.data.data : [];
           $scope.spinRes = false;
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getResources(); 


    /* ============ Edit Button ============= */ 
    $scope.editItem = function (id) {
        $("#ddlProductResource").focus();
        $scope.temp.footdetid=id.FOOTDETID;
        $scope.temp.ddlProductResource = id.PRODUCT_RESOURCE;
        $scope.temp.ddlProduct= id.PRODUCT_RESOURCE=='PRODUCT' ? id.PRODUCT_RESOURCE_ID.toString() : '';
        $scope.temp.ddlResource= id.PRODUCT_RESOURCE=='RESOURCE' ? id.PRODUCT_RESOURCE_ID.toString() : '';
        $scope.temp.txtItemSeqno=Number(id.SEQNO);
        $scope.temp.isHeader= id.ISHEADER;
        $scope.temp.txtHeader= id.HEADER;
        $scope.editModeItem = true;
        $scope.index = $scope.post.getMenuItemData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearItem = function(){
        $("#ddlProductResource").focus();
        $scope.temp.footdetid='';
        $scope.temp.ddlProductResource='';
        $scope.temp.ddlProduct='';
        $scope.temp.ddlResource='';
        $scope.temp.txtItemSeqno='';
        $scope.temp.isHeader='0';
        $scope.temp.txtHeader='';
        $scope.editModeItem = false;
    }


    /* ========== DELETE =========== */
    $scope.deleteItem = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'FOOTDETID': id.FOOTDETID, 'type': 'deleteItem' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getMenuItemData.indexOf(id);
		            $scope.post.getMenuItemData.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
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


    $scope.eyepass = function(For,index) {
        if(For == 'MPASS'){
            var input = $("#txtMeetingPasscode");
        }else if(For == 'EPASS'){
            var input = $("#txtEmailPassword");
        }
        
        // var input = $("#txtMeetingPasscode");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon'+index).removeClass('fa-eye');
            $('.Eyeicon'+index).addClass('fa-eye-slash');
        }else{
            $('.Eyeicon'+index).removeClass('fa-eye-slash');
            $('.Eyeicon'+index).addClass('fa-eye');
        }
    };

});