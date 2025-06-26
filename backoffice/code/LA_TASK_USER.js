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
    $scope.PageSub1 = "TASK_USERS";
    
    var url = 'code/LA_TASK_USER.php';
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
                    $scope.getPlans();
                    $scope.getGrade();
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
                formData.append("TASKMGMTID", $scope.temp.TASKMGMTID);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlTask_Category", $scope.temp.ddlTask_Category);
                formData.append("txtAssignedTo", $scope.temp.txtAssignedTo);
                formData.append("ddlAssignedToID", $scope.temp.ddlAssignedToID);
                formData.append("txtStartDT", $scope.temp.txtStartDT.toLocaleDateString('sv-SE'));
                formData.append("txtEndDT", $scope.temp.txtEndDT.toLocaleDateString('sv-SE'));
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("txtTaskDesc", $scope.temp.txtTaskDesc);
                formData.append("txtlink", $scope.temp.txtlink);
                formData.append("txtUpdateDate", (!$scope.temp.txtUpdateDate || $scope.temp.txtUpdateDate=='') ? '' : $scope.temp.txtUpdateDate.toLocaleDateString('sv-SE'));
                // REVIEW BY
                formData.append("ddlReviewBy1", $scope.temp.ddlReviewBy1);
                formData.append("ddlReviewBy2", $scope.temp.ddlReviewBy2);
                formData.append("ddlReviewBy3", $scope.temp.ddlReviewBy3);
                formData.append("txtAssignReview1", $scope.temp.txtAssignReview1);
                formData.append("txtAssignReview2", $scope.temp.txtAssignReview2);
                formData.append("txtAssignReview3", $scope.temp.txtAssignReview3);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTaskUsers();
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


    

    /* ========== GET Plan =========== */
    $scope.getTaskUsers = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskUsers','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskUsers = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTaskUsers(); 



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
            // console.log(data.data);
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
            if($scope.temp.ddlLocation > 0) $scope.getTaskUsers();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET Plan =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT


    /* ========== GET AssignedToUser =========== */
    $scope.getAssignedToUser = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAssignedToUser','ddlPlan':$scope.temp.ddlPlan,'txtAssignedTo':$scope.temp.txtAssignedTo,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            //console.log(data.data);
            $scope.post.getAssignedToUser = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAssignedToUser(); --INIT
    /* ========== GET AssignedToUser =========== */


    /* ========== GET Review 1 =========== */
    $scope.getReview1 = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReview1','ddlPlan':$scope.temp.ddlAssignPlanReview1,'txtAssignedTo':$scope.temp.txtAssignReview1,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReview1 = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReview1(); --INIT
    /* ========== GET Review 1 =========== */


    /* ========== GET Review 2 =========== */
    $scope.getReview2 = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReview2','ddlPlan':$scope.temp.ddlAssignPlanReview2,'txtAssignedTo':$scope.temp.txtAssignReview2,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReview2 = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReview2(); --INIT
    /* ========== GET Review 2 =========== */


    /* ========== GET Review 3 =========== */
    $scope.getReview3 = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReview3','ddlPlan':$scope.temp.ddlAssignPlanReview3,'txtAssignedTo':$scope.temp.txtAssignReview3,'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReview3 = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReview3(); --INIT
    /* ========== GET Review 2 =========== */

    /* ========== GET grade =========== */
    $scope.getGrade = function () {
        $scope.spinGrade =  true;
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getGradeMaster','ddlLocation':1}),
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

   
    
    /* ============ Edit Form =========== */ 
    $scope.edit = function(id){
        if(id.TASKSTATUS == 'CLOSED') return;
        $('#ddlTask_MainCategory').focus();
        $scope.temp.TASKMGMTID=id.TASKMGMTID;
        $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.ddlTask_MainCategory=id.TASKMAINCATID.toString();
        if($scope.temp.ddlTask_MainCategory>0){
            $scope.getTaskCategory();
            $timeout(()=>{
                $scope.temp.ddlTask_Category=id.TASKCATID.toString();
            },1000);
        }
        $scope.temp.txtAssignedTo=id.ASSIGNEDTO;

        $scope.temp.ddlPlan = id.PLANID>0 ?id.PLANID.toString():'';
        $timeout(function () { 
            $scope.getAssignedToUser();
            
            $timeout(function () {
                $scope.temp.ddlAssignedToID=id.ASSIGNEDTO_ID.toString();
            }, 1500)
        },500);


        $scope.temp.txtAssignReview1=id.ASSIGNEDTO_R1;
        $scope.temp.ddlAssignPlanReview1 = id.REVIEW1_PLANID>0 ?id.REVIEW1_PLANID.toString():'';
        $timeout(function () {
            $scope.getReview1();
            $timeout(function () {
                $scope.temp.ddlReviewBy1=id.REVIEW1_ID.toString();
            }, 1500)
        },500);

        $scope.temp.txtAssignReview2=id.ASSIGNEDTO_R2;
        $scope.temp.ddlAssignPlanReview2 = '';
        $scope.temp.ddlReviewBy2 = '';
        if(id.ASSIGNEDTO_R2 && id.ASSIGNEDTO_R2!=''){
            $scope.temp.ddlAssignPlanReview2 = id.REVIEW2_PLANID>0 ?id.REVIEW2_PLANID.toString():'';
            $timeout(function () {
                $scope.getReview2();
                $timeout(function () {
                    $scope.temp.ddlReviewBy2=id.REVIEW2_ID.toString();
                }, 1500)
            },500);
        }


        $scope.temp.txtAssignReview3=id.ASSIGNEDTO_R3;
        $scope.temp.ddlAssignPlanReview3 = '';
        $scope.temp.ddlReviewBy3 = '';
        if(id.ASSIGNEDTO_R3 && id.ASSIGNEDTO_R3!=''){
            $scope.temp.ddlAssignPlanReview3 = id.REVIEW3_PLANID>0 ?id.REVIEW3_PLANID.toString():'';
            $timeout(function () {
                $scope.getReview3();
                $timeout(function () {
                    $scope.temp.ddlReviewBy3=id.REVIEW3_ID.toString();
                }, 1500)
            },500);
        }
        $scope.temp.txtStartDT=new Date(id.STARTDATE);
        $scope.temp.txtEndDT=new Date(id.ENDDATE);
        $scope.temp.ddlGrade = (!id.GRADEID && id.GRADEID==0)?'':id.GRADEID.toString();
        $scope.temp.ddlSubject = (!id.CSUBID && id.CSUBID==0)?'':id.CSUBID.toString();
        $scope.temp.txtTaskDesc = !id.TASK_DESC ? '' : id.TASK_DESC;
        $scope.temp.txtlink = !id.TASKFILE ? '' : id.TASKFILE;
        $scope.temp.txtUpdateDate = id.TASKUPDATEON=='' ? '' : new Date(id.TASKUPDATEON);
    }
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp.TASKMGMTID='';
        $scope.temp.ddlTask_MainCategory='';
        $scope.temp.ddlTask_Category='';
        $scope.temp.txtAssignedTo='';
        $scope.temp.ddlAssignedToID='';
        $scope.temp.txtStartDT='';
        $scope.temp.txtEndDT='';
        $scope.temp.txtEndDT='';
        $scope.temp.ddlGrade='';
        $scope.temp.ddlSubject='';
        $scope.temp.txtTaskDesc='';
        $scope.temp.txtlink='';
        $scope.temp.txtUpdateDate='';

        $scope.temp.ddlReviewBy1='';
        $scope.temp.ddlReviewBy2='';
        $scope.temp.ddlReviewBy3='';
        $scope.temp.txtAssignReview1='';
        $scope.temp.txtAssignReview2='';
        $scope.temp.txtAssignReview3='';

      
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TASKMGMTID': id.TASKMGMTID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTaskUsers.indexOf(id);
		            $scope.post.getTaskUsers.splice(index, 1);
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