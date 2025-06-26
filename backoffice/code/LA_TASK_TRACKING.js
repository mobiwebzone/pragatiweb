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
    $scope.Page = "L&A";
    $scope.PageSub = "TSK_MANG";
    $scope.PageSub1 = "TASK_TRACKING";

    var url = 'code/LA_TASK_TRACKING.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        console.log($scope.myOrderBY);
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
                    $scope.getClassSubjectMaster();
                  
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
                formData.append("TTID", $scope.temp.TTID);
                formData.append("txtTaskDT", $scope.temp.txtTaskDT.toLocaleDateString('sv-SE'));
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlTask_Category", $scope.temp.ddlTask_Category);
                formData.append("txtAssignedTo", $scope.temp.txtAssignedTo);
                
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("txtTask", $scope.temp.txtTask);
                formData.append("txtlink", $scope.temp.txtlink);
                formData.append("txtUploaddate", $scope.temp.txtUploaddate.toLocaleDateString('sv-SE'));
                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTaskTracking();
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


    
    /* ========== Get Task Tracking =========== */
    $scope.getTaskTracking = function () {
        $scope.spinSubject =  true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskTracking','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskTracking = data.data.success ? data.data.data : [];
            $scope.spinSubject =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getTaskTracking();
    /* ========== Get Task Tracking =========== */



    /* ========== GET CATEGORY =========== */
    $scope.getTaskMainCategory = function () {
        $scope.SpinTMC=true;
        $scope.post.getTaskCategory = [];
        $http({
           method: 'post',
           url: masterUrl,
           data: $.param({ 'type': 'getTaskMainCategory','LOCID':$scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
           $scope.post.getTaskMainCategory = data.data.success ? data.data.data : [];
           $scope.SpinTMC=false;
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   //  $scope.getTaskMainCategory();


    /* ========== GET SUBCATEGORY =========== */
    $scope.getTaskCategory = function () {
        $scope.SpinTSC=true;
         $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getTaskCategory','TASKMAINCATID':$scope.temp.ddlTask_MainCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskCategory = data.data.success ? data.data.data : [];
            $scope.SpinTSC=false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTaskCategory();

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
            if($scope.temp.ddlLocation > 0) $scope.getTaskTracking();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET Assigned To =========== */
    $scope.getAssignedTo = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAssignedTo','ddlTask_Category':$scope.temp.ddlTask_Category,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAssignedTo = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAssignedTo();






    /* ========== GET grade =========== */
    $scope.getGrade = function () {
        $scope.spinGrade =  true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getGrade','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrade = data.data.success ? data.data.data : [];
            $scope.spinGrade =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getGrade(); --INIT
    /* ========== GET grade =========== */


    /* ========== GET CLASS SUBJECTS =========== */
    $scope.getClassSubjectMaster = function () {
        $scope.spinSubject =  true;
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getClassSubjectMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getClassSubjectMaster = data.data.success ? data.data.data : [];
            $scope.spinSubject =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubjectMaster(); --INIT
        /* ========== GET SUBJECTS =========== */


    
      /* ============ Edit Button ============= */ 
      $scope.edit = function (id) {
        $("#txtTaskDT").focus();
        $scope.temp.TTID=id.TTID;
        $scope.temp.txtTaskDT=new Date(id.TTDATE);
        $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.ddlTask_MainCategory=id.TASKMAINCATID.toString();
        if($scope.temp.ddlTask_MainCategory>0){
            $scope.getTaskCategory();
            $timeout(()=>{
                $scope.temp.ddlTask_Category=id.TASKCATID.toString();
                $scope.getAssignedTo();
                $timeout(()=>{
                    $scope.temp.txtAssignedTo=id.ASSIGNEDTO_ID.toString();
                },1000);

            },1000);
        }
        $scope.temp.ddlGrade=id.GRADEID.toString();
        $scope.temp.ddlSubject=id.CSUBID.toString();
        $scope.temp.txtTask=id.TASK;
        $scope.temp.txtUploaddate=new Date(id.TASKUPLOADEDON);
        $scope.temp.txtlink = id.TASKFILE;

    }
    /* ============ Edit Button ============= */ 



    /* ============ Clear Form =========== */ 
    $scope.clear = function(){    
        $scope.temp={};
        $scope.getLocations();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TTID': id.TTID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTaskTracking.indexOf(id);
		            $scope.post.getTaskTracking.splice(index, 1);
                      $scope.clear();
		            console.log(data.data.message)
                    
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