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
    $scope.editModeMain = false;
    $scope.editMode = false;
    $scope.Page = "L&A";
    $scope.PageSub = "TSK_MANG";
    $scope.PageSub1 = "TASK_CATE";
    
    var url = 'code/LA_TASK_CATEGORY.php';

    $scope.setMyOrderBYMain = function(COL){
        $scope.myOrderBYMain = COL==$scope.myOrderBYMain ? `-${COL}` : ($scope.myOrderBYMain == `-${COL}` ? myOrderBYMain = COL : myOrderBYMain = `-${COL}`);
    }
    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
    }


    
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN" && $scope.userrole != "LA_MASTER")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
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

    // =======================================================================================================================================
    //                                                          MAIN CATEGORY
    // =======================================================================================================================================    

    $scope.saveMain = function(){
        $(".btn-save-main").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update-main").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveMain');
                formData.append("TASKMAINCATID", $scope.temp.TASKMAINCATID);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtTaskMainCat", $scope.temp.txtTaskMainCat);
                formData.append("ddlMainPriority", $scope.temp.ddlMainPriority);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTaskMainCategory();
                // $scope.temp.TASKMAINCATID='';
                // $scope.temp.txtTaskMainCat='';
                // $scope.clear();

                $scope.temp.TASKMAINCATID = data.data.TASKMAINCATID;
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-main').removeAttr('disabled').text('SAVE');
            $('.btn-update-main').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET TASK MAIN CATEGORY =========== */
     $scope.getTaskMainCategory = function () {
         $scope.SpinMainCat = true;
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskMainCategory','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskMainCategory = data.data.success ? data.data.data : [];
            $scope.SpinMainCat = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTaskMainCategory();




    /* ============ Edit Button ============= */ 
    $scope.editMain = function (id) {
        $scope.clear();
        $scope.post.getTaskCategory = [];

        $scope.temp.TASKMAINCATID = id.TASKMAINCATID;
        $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.txtTaskMainCat= id.TASKMAINCAT;
        $scope.temp.ddlMainPriority=id.PRIORITY;
        $scope.editModeMain = true;
        $scope.index = $scope.post.getTaskMainCategory.indexOf(id);
        $scope.getTaskCategory();
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearMain = function(){
        // $scope.temp={};
        $scope.temp.TASKMAINCATID='';
        $scope.temp.txtTaskMainCat='';
        $scope.temp.ddlMainPriority='';
        $scope.editModeMain = false;
        // $scope.getLocations();
        $scope.post.getTaskCategory = [];
        $scope.clear();
    }



    /* ========== DELETE =========== */
    $scope.deleteMain = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TASKMAINCATID': id.TASKMAINCATID, 'type': 'deleteMain' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTaskMainCategory.indexOf(id);
		            $scope.post.getTaskMainCategory.splice(index, 1);
                      $scope.clearMain();
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }



    // =======================================================================================================================================
    //                                                          SUB CATEGORY
    // =======================================================================================================================================    


    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("TASKCATID", $scope.temp.TASKCATID);
                formData.append("TASKMAINCATID", $scope.temp.TASKMAINCATID);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtTaskCAt", $scope.temp.txtTaskCAt);
                formData.append("ddlPriority", $scope.temp.ddlPriority);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTaskCategory();
                $scope.clear();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET TASK CATEGORY =========== */
     $scope.getTaskCategory = function () {
         $scope.SpinCat = true;
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskCategory','TASKMAINCATID':$scope.temp.TASKMAINCATID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskCategory = data.data.success ? data.data.data : [];
            $scope.SpinCat = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTaskCategory();


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp.TASKCATID=id.TASKCATID;
        $scope.temp.txtTaskCAt=id.TASKCAT;
        $scope.temp.ddlPriority=id.PRIORITY;
        $scope.editMode = true;
        $scope.index = $scope.post.getTaskCategory.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp.TASKCATID='';
        $scope.temp.txtTaskCAt='';
        $scope.temp.ddlPriority='';
        $scope.editMode = false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TASKCATID': id.TASKCATID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTaskCategory.indexOf(id);
		            $scope.post.getTaskCategory.splice(index, 1);
                    $scope.clear();
		            console.log(data.data.message)
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

    // =======================================================================================================================================
    //                                                          OTHER
    // =======================================================================================================================================

    $scope.printTable = function(FOR){
        if(FOR == 'CAT'){
            $('#CAT_RPT').removeClass('col-lg-6');
            // $('#sumHead').removeClass('text-white').addClass('text-dark');
            $('#SUB_RPT').hide();
            window.print();
            $timeout(()=>{
                $('#CAT_RPT').addClass('col-lg-6');
                // $('#sumHead').removeClass('text-dark').addClass('text-white');
                $('#SUB_RPT').show();
            },300);
        }else{
            $('#SUB_RPT').removeClass('col-lg-6');
            // $('#detHead').removeClass('text-white').addClass('text-dark');
            $('#CAT_RPT').hide();
            window.print();
            $timeout(()=>{
                $('#SUB_RPT').addClass('col-lg-6');
                // $('#detHead').removeClass('text-dark').addClass('text-white');
                $('#CAT_RPT').show();
            },300);
        }
    }


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
            if($scope.temp.ddlLocation > 0) $scope.getTaskMainCategory();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

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