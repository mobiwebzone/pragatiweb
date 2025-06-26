
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "TASK";
    $scope.temp.txtUploaddate = new Date();

    var url = 'code/TASK_TRACKING.php';
    var Masterurl = '../backoffice/code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        console.log($scope.myOrderBY);
    }



    
    /* =============== CHECK SESSION ============== */
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
                $scope.LOC_ID=data.data.locid;
                $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];
                
                $scope.getClassSubjectMaster();
                $scope.getTaskCategory();
                $scope.getGrade();
                $scope.getTaskTracking();

            }
            else {
                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* =============== CHECK SESSION ============== */

    $scope.GetTTID=function (id) 
    {
        $scope.GET_TTID=id.TTID;
        $scope.ASSIGNEDTO_NAME=id.ASSIGNEDTO_NAME;
        $scope.TASKSTATUS=id.TASKSTATUS;
        $scope.getTaskTrackDetails();
    }
    
        /* ========== GET Task Tracking Deatils =========== */
        $scope.getTaskTrackDetails = function () {
            $scope.SpinTaskTrack=true;
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'type': 'getTaskTrackDetails','TTID':$scope.GET_TTID}),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    $scope.post.getTaskTrackDetails = data.data.data;
                    $scope.SpinTaskTrack=false;
                },
                function (data, status, headers, config) {
                    console.log('Failed');
                })
            }
            // $scope.getTaskTrackDetails(); --INIT
            /* ========== GET Task Tracking Deatils =========== */

    $scope.saveTaskTrackingDetails = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveTaskTrackingDetails');
                formData.append("TTDETID", $scope.temp.TTDETID);
                formData.append("TTID", $scope.GET_TTID);
                formData.append("txtReview", $scope.temp.txtReview);
                formData.append("txtLinkReview", $scope.temp.txtLinkReview);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTaskTrackDetails();
                $scope.temp.TTDETID=0;
                $scope.temp.txtReview='';
                $scope.temp.txtLinkReview='';

                $timeout(function () {
                    var container = document.querySelector('.chat-container'); // replace with your actual container class or element
                    container.scrollTop = 0;
                  }, 0);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }

    $scope.clearTaskTrack_Detials=function () 
    {
        $scope.temp.TTDETID=0;
        $scope.temp.txtReview='';
        $scope.temp.txtLinkReview='';
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
                formData.append("ddlLocation", $scope.LOC_ID);
                formData.append("ddlTask_Category", $scope.temp.ddlTask_Category);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("txtTask", $scope.temp.txtTask);
                formData.append("txtlink", $scope.temp.txtlink);
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

     /* ========== GET GRADES =========== */
     $scope.getTaskCategory = function () {
        // if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskCategory','ddlLocation':$scope.LOC_ID}),
            // data: $.param({ 'type': 'getTaskCategory'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskCategory = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTaskCategory();

        /* ========== Get Task Tracking =========== */
        $scope.getTaskTracking = function () {
            $scope.spinSubject =  true;
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTaskTracking','ddlLocation':$scope.LOC_ID}),
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
        // $scope.getTaskTracking();
        /* ========== Get Task Tracking =========== */


            /* ========== GET grade =========== */
    $scope.getGrade = function () {
        $scope.spinSubject =  true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getGrade','ddlLocation':$scope.LOC_ID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrade = data.data.success ? data.data.data : [];
            $scope.spinSubject =  false;
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
            url: Masterurl,
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
        $scope.temp.ddlTask_Category=id.TASKCATID.toString();
        $scope.temp.ddlGrade=id.GRADEID.toString();
        $scope.temp.ddlSubject=id.CSUBID.toString();
        $scope.temp.txtTask=id.TASK;
        $scope.temp.txtlink=id.TASKFILE;
      }

    /* ============ Clear Form =========== */ 
    $scope.clear = function(){       
        $scope.temp={};
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
                    window.location.assign('login.html#!/login');
                }
                else {
                    window.location.assign('dashboard.html#!/dashboard');
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