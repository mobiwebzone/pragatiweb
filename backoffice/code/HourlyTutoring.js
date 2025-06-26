$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "angularjs-dropdown-multiselect","ngSanitize"]);
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.editModeDet = false;
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_HOURLY_TUTORING";
    // $scope.PLANS_model = [];
    // $scope.STUDENTS_model = [];
    $scope.SUBJECT_model = [];
    $scope.temp.txtReqDate = new Date();

    // $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px',scrollableWidth:'200px'};
    // $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.SUBJECT_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};

    
    var url = 'code/HourlyTutoring.php';
    var Masterurl = 'code/MASTER_API.php';

    $scope.DAYNAME_LIST = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    $scope.getPlans();
                    $scope.getClassSubjectMaster();
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


    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $scope.selected_subjects = $scope.SUBJECT_model.map(x=>x.id);
        // alert($scope.selected_subjects);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("reqid", $scope.temp.reqid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("txtReqDate", $scope.temp.txtReqDate.toLocaleDateString('sv-SE'));
                formData.append("subjectIDs", $scope.selected_subjects);
                formData.append("ddlPriority", $scope.temp.ddlPriority);
                formData.append("txtStartDate", $scope.temp.txtStartDate.toLocaleDateString('sv-SE'));
                formData.append("txtEndDate", (!$scope.temp.txtEndDate || $scope.temp.txtEndDate=='') ? '' : $scope.temp.txtEndDate.toLocaleDateString('sv-SE'));
                formData.append("txtComments", $scope.temp.txtComments);
                formData.append("ddlStatus", $scope.temp.ddlStatus);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if($scope.selected_subjects.length==1) $scope.temp.reqid = data.data.REQID;
                $scope.messageSuccess(data.data.message);
                $scope.getTutoringRequests();
                // $scope.clear();
                $("#ddlLocation").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET TUTORING REQUESTS =========== */
     $scope.getTutoringRequests = function () {
        $scope.post.getTeacherSubjects=[];
        if(!$scope.temp.ddlStudent || $scope.temp.ddlStudent<=0) return
         $scope.SpinMainData = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getTutoringRequests','ddlStudent':$scope.temp.ddlStudent}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTutoringRequests = data.data.success ? data.data.data : [];
            $('#SpinSubject').hide();
            $scope.SpinMainData = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.SpinMainData = false;
        })
    }
    // $scope.getTutoringRequests(); --INIT


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: Masterurl,
            data: $.param({ 'type': 'getPlans_MultiSelect'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.success ? data.data.data : [];
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */


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
            // if($scope.temp.ddlLocation > 0) $scope.getInventories();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    
    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
    $scope.getStudentByPlanProduct = function () {
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinStudent').show();
        // $scope.FINAL_PLANID = [];
        // $scope.FINAL_PLANID = $scope.PLANS_model.map(x=>x.id);
        $http({
            method: 'post',
            // url: 'code/Student_Attendance_Payment_Report_P2.php',
            url: Masterurl,
            data: $.param({ 'type': 'getStudentByPlanLocation', 
                            'PLANID' : $scope.temp.ddlPlan,
                            'LOCID' : $scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByPlanProduct = data.data.success ? data.data.data : [];
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlanProduct();
    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 


    /* ========== GET CLASS SUBJECTS =========== */
    $scope.getClassSubjectMaster = function () {
        $scope.spinTeacher =  true;
        $http({
            method: 'post',
            url: Masterurl,
            data: $.param({ 'type': 'getClassSubjectMaster_Multi'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getClassSubjectMaster = data.data.success ? data.data.data : [];
            $scope.spinTeacher =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubjectMaster(); --INIT
        /* ========== GET Location =========== */







    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.clearDet();
        $("#ddlLocation").focus();
        $(".dropdown-toggle").attr('disabled','disabled');
        $scope.selected_subjects=[];
        $scope.SUBJECT_model=[];

        $scope.temp.reqid = id.REQID;
        $scope.temp.txtReqDate = new Date(id.REQDATE);
        $scope.temp.ddlPriority = id.REQPRIORITY;
        $scope.temp.txtStartDate = (!id.STARTDATE || id.STARTDATE=='') ? '' : new Date(id.STARTDATE);
        $scope.temp.txtEndDate = (!id.ENDDATE || id.ENDDATE=='') ? '' : new Date(id.ENDDATE);
        $scope.temp.txtComments = id.COMMENTS;
        $scope.temp.ddlStatus = id.REQSTATUS;
        
        $scope.editMode = true;
        $scope.index = $scope.post.getTutoringRequests.indexOf(id);

        $scope.getTutoringReqDetails();
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $("#ddlLocation").focus();
        $(".dropdown-toggle").removeAttr('disabled');
        $scope.temp.reqid = '';
        $scope.temp.txtReqDate = '';
        $scope.temp.ddlPriority = '1';
        $scope.temp.txtStartDate = '';
        $scope.temp.txtEndDate = '';
        $scope.temp.txtComments = '';
        $scope.temp.ddlStatus = 'OPEN';
        $scope.selected_subjects=[];
        $scope.SUBJECT_model=[];
        $scope.editMode = false;
        $scope.post.getTutoringReqDetails=[];
        $scope.clearDet();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'REQID': id.REQID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTutoringRequests.indexOf(id);
		            $scope.post.getTutoringRequests.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clear();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    


    //==================================================
    //                    DETAILS
    //==================================================
    $scope.saveDet = function(){
        $(".btn-saveDet").attr('disabled', 'disabled').text('Saving...');
        $(".btn-updateDet").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDet');
                formData.append("reqdetid", $scope.temp.reqdetid);
                formData.append("reqid", $scope.temp.reqid);
                formData.append("ddlDay", $scope.temp.ddlDay);
                formData.append("txtDayHours", $scope.temp.txtDayHours);
                formData.append("txtFromTime", (!$scope.temp.txtFromTime || $scope.temp.txtFromTime=='') ? '' : $scope.temp.txtFromTime.toLocaleString('sv-SE'));
                formData.append("txtToTime", (!$scope.temp.txtToTime || $scope.temp.txtToTime=='') ? '' : $scope.temp.txtToTime.toLocaleString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // $scope.clearDet();
                $scope.getTutoringReqDetails();
                // $scope.clear();
                $("#ddlDay").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveDet').removeAttr('disabled').text('SAVE');
            $('.btn-updateDet').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET TUTORING REQUEST DETAILS =========== */
    $scope.getTutoringReqDetails = function () {
        $scope.post.getTutoringReqDetails=[];
        if(!$scope.temp.reqid || $scope.temp.reqid<=0) return
        $scope.SpinDetData = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTutoringReqDetails','reqid':$scope.temp.reqid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTutoringReqDetails = data.data.success ? data.data.data : [];
            $scope.SpinDetData = false;
        },
        function (data, status, headers, config) {
            $scope.SpinDetData = false;
            console.log('Failed');
        })
    }
    // $scope.getTutoringReqDetails(); --INIT

    /* ============ Edit Button ============= */ 
    $scope.editDet = function (id) {
        $("#ddlDay").focus();
        $scope.temp.reqdetid=id.REQDETID;
        $scope.temp.ddlDay=id.DYNAME;
        $scope.temp.txtDayHours=Number(id.DYHOURS);
        $scope.temp.txtFromTime=(!id.FROMTIME || id.FROMTIME=='-') ? '' : new Date('2023-01-01T'+id.FROMTIME_SET);
        $scope.temp.txtToTime=(!id.TOTIME || id.TOTIME=='-') ? '' : new Date('2023-01-01T'+id.TOTIME_SET);
        $scope.editModeDet = true;
        $scope.index = $scope.post.getTutoringReqDetails.indexOf(id);
    }
        
        
    /* ============ Clear Form =========== */ 
    $scope.clearDet = function(){
        $("#ddlDay").focus();
        $scope.temp.reqdetid = '';
        $scope.temp.ddlDay = '';
        $scope.temp.txtDayHours = '';
        $scope.temp.txtFromTime = '';
        $scope.temp.txtToTime = '';
        $scope.editModeDet = false;
    }


    /* ========== DELETE =========== */
    $scope.deleteDet = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'REQDETID': id.REQDETID, 'type': 'deleteDet' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTutoringReqDetails.indexOf(id);
                    $scope.post.getTutoringReqDetails.splice(index, 1);
                    console.log(data.data.message)
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }


    $scope.closeDetForm = function(){
        $scope.clearDet();
        $scope.temp.reqid = '';
        $scope.post.getTutoringReqDetails=[];
        $(".dropdown-toggle").removeAttr('disabled');
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