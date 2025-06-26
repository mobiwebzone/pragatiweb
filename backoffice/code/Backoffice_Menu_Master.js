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
    $scope.editModeLoc = false;
    $scope.Page = "SETTING";
    $scope.PageSub = "BO_MENU";
    $scope.PageSub1 = "BO_MENU_MASTER";
    
    var url = 'code/Backoffice_Menu_Master.php';
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
                    $scope.getUnderMenu();
                    $scope.getMenuData();
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
                formData.append("menuid", $scope.temp.menuid);
                formData.append("txtMenu", $scope.temp.txtMenu);
                formData.append("txtShortName", $scope.temp.txtShortName);
                formData.append("ddlUnderMenu", $scope.temp.ddlUnderMenu);
                formData.append("ddlfunctionid", $scope.temp.ddlfunctionid);
                formData.append("hasLink", $scope.temp.hasLink);
                formData.append("txtPageLink", $scope.temp.txtPageLink);
                formData.append("txtPageDesc", $scope.temp.txtPageDesc);
                formData.append("txtSEQNO", $scope.temp.txtSEQNO);
                formData.append("isHeader", $scope.temp.isHeader);
                formData.append("txtHeader", $scope.temp.txtHeader);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getMenuData();
                $scope.getUnderMenu();
                $scope.clear();
                $("#txtMenu").focus();
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


    /* ========== GET MENU DATA =========== */
    $scope.getUnderMenu = function () {
         $scope.spinMenu = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getUnderMenu'}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getUnderMenu = data.data.success ? data.data.data : [];
            $scope.spinMenu = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getUnderMenu(); 


    // Get Functions
  $scope.getFunction = function () {
    $scope.post.getFunction = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: 'code/MEP_OBJECT_Master_code.php',
      data: $.param({
        type: "getFunction",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //   console.log(data.data);
        $scope.post.getFunction = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getFunction();

    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $("#txtMenu").focus();
        $scope.temp.menuid=id.MENUID;
        $scope.temp.txtMenu = id.MENU;
        $scope.temp.txtShortName = id.MENU_SHORTNAME;
        $scope.temp.ddlUnderMenu = id.UNDER_MENUID>0?id.UNDER_MENUID.toString():'';
        $scope.temp.ddlfunctionid = id.FUNCTIONID>0?id.FUNCTIONID.toString():'';
        $scope.temp.hasLink = id.HAS_LINK;
        $scope.temp.txtPageLink = !id.PAGE_LINK ? '' : id.PAGE_LINK;
        $scope.temp.txtPageDesc = !id.PAGE_DESC ? '' : id.PAGE_DESC;
        $scope.temp.txtSEQNO = Number(id.SEQNO);
        $scope.temp.isHeader= id.ISHEADER;
        $scope.temp.txtHeader= id.HEADER;

        $scope.editMode = true;
        $scope.index = $scope.post.getMenuData.indexOf(id);
        // $scope.getMenuItemData();
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $("#txtMenu").focus();
        $scope.temp={};
        $scope.temp.isHeader='0';
        $scope.editMode = false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'MENUID': id.MENUID, 'type': 'delete' }),
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