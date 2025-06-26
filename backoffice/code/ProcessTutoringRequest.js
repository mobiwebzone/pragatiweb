$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "angularjs-dropdown-multiselect","ngSanitize"]);
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
$postModule.directive('tooltip', function () {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        // Initialize Bootstrap Tooltip
        $(element).tooltip({
            placement: 'bottom'
        });
      }
    };
  });
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.editModeDet = false;
    $scope.Page = "L&A";
    $scope.PageSub = "HourlyTutoring";
    $scope.PageSub1 = "ST_PROCESS_TUTORING_REQ";
    // $scope.PLANS_model = [];
    // $scope.STUDENTS_model = [];
    $scope.SUBJECT_model = [];
    $scope.temp.txtReqDate = new Date();

    // $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px',scrollableWidth:'200px'};
    // $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.SUBJECT_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};

    
    var url = 'code/ProcessTutoringRequest.php';
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
                    // $scope.getPendingRequests();
                    $scope.getClassSubjectMaster();
                }
            }
            else {
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }


    /*************************** START PROCESSING ***************************************/

    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        const DtOptions = { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' };
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("reqpid", $scope.temp.reqpid);
                formData.append("GET_REQID", $scope.GET_REQID);
                formData.append("GET_REGID", $scope.GET_REGID);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("txtRatePerHour", $scope.temp.txtRatePerHour);
                formData.append("txtTeacherShare", $scope.temp.txtTeacherShare);
                formData.append("txtMEPShare", $scope.temp.txtMEPShare);
                formData.append("txtStartDate", $scope.temp.txtStartDate.toLocaleDateString('sv-SE'));
                formData.append("txtEndDate", (!$scope.temp.txtEndDate || $scope.temp.txtEndDate=='') ? '' : $scope.temp.txtEndDate.toLocaleDateString('sv-SE'));
                formData.append("STUBJECT_NAME", $scope.SUBJECT_NAME);
                formData.append("STARTDT_EMAIL", $scope.temp.txtStartDate.toLocaleDateString('sv-SE', DtOptions));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTutoringRequests();
                $scope.getPendingRequests();
                $scope.clear();
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
       
        $scope.SpinProcessData = true;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTutoringRequests');
                formData.append("REQID", $scope.GET_REQID);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTutoringRequests = data.data.success ? data.data.data : [];
            $scope.SpinProcessData = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.SpinProcessData = false;
        })
    }
    // $scope.getTutoringRequests(); --INIT

     /* ========== GET SUBJECT TEACHERS =========== */
     $scope.getSubjectTeacher = function (id) {
        //  console.log(id);
        $scope.GET_CSUBID = id.CSUBID;
        $scope.GET_REQID = id.REQID;
        $scope.SUBJECT_NAME = id.SUBJECT;
        
        $scope.GET_REGID = id.REGID;
        $scope.MODAL_TITLE = (!id.STUDENTNAME || id.STUDENTNAME=='') ? '' : `(${id.STUDENTNAME})`;
        $scope.TEACHER_LIST_TITLE = (!id.SUBJECT || id.SUBJECT=='') ? '' : `Subject :- ${id.SUBJECT}`;
        $scope.post.getTutoringRequests=[];
        $scope.getTutoringRequests();
        $scope.clear();
        $scope.SELECTED_TEACHERID='';
        $scope.temp.ddlTeacher='';
        // return;
        $scope.SpinSubjectTeacher = true;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjectTeacher');
                formData.append("CSUBID", $scope.GET_CSUBID);
                formData.append("REGID", $scope.GET_REGID);
                formData.append("ST_LOCID",id.ST_LOCID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubjectTeacher = data.data.success ? data.data.data : [];
            $scope.SpinSubjectTeacher = false;
        },
        function (data, status, headers, config) {
            
            console.log('Failed');
            $scope.SpinSubjectTeacher = false;
        })
    }
    // $scope.getSubjectTeacher();

    

    $scope.setMEPShare = function(){
        $scope.temp.txtTeacherShare = $scope.temp.txtTeacherShare>100?0:$scope.temp.txtTeacherShare;
        $scope.temp.txtMEPShare=(100-(!$scope.temp.txtTeacherShare ? 0 : $scope.temp.txtTeacherShare));
    }

    $scope.setProcessFields = function(){
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0){
            $scope.temp.txtRatePerHour = '';
            $scope.temp.txtTeacherShare = '';
            $scope.temp.txtMEPShare = '';
        }else{
            $scope.temp.txtRatePerHour = Number($scope.post.getSubjectTeacher.filter(x=>x.TEACHERID==$scope.temp.ddlTeacher).map(x=>x.RATE_PER_HOUR).toString());
            $scope.temp.txtTeacherShare = Number($scope.post.getSubjectTeacher.filter(x=>x.TEACHERID==$scope.temp.ddlTeacher).map(x=>x.TEACHER_SHARE).toString());
            // $scope.temp.txtMEPShare = '';
            $scope.setMEPShare();
        }
    }


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $("#ddlTeacher").focus();
        $scope.temp.reqpid=id.REQPID;
        // $scope.temp.ddlTeacher=id.TEACHERID.toString();
        $scope.temp.txtRatePerHour=Number(id.RATE_PER_HOUR);
        $scope.temp.txtTeacherShare=Number(id.TEACHER_SHARE);
        $scope.temp.txtMEPShare=Number(id.MEP_SHARE);
        $scope.temp.txtStartDate=new Date(id.STARTDATE);
        $scope.temp.txtEndDate=new Date(id.ENDDATE);

        $scope.editMode = true;
        $scope.index = $scope.post.getTutoringRequests.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $("#ddlTeacher").focus();
        $scope.temp.reqpid='';
        // $scope.GET_REQID='';
        // $scope.GET_REGID='';
        // $scope.temp.ddlTeacher='';
        $scope.temp.txtRatePerHour='';
        $scope.temp.txtTeacherShare='';
        $scope.temp.txtMEPShare='';
        $scope.temp.txtStartDate='';
        $scope.temp.txtEndDate='';
        $scope.editMode = false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'REQPID': id.REQPID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTutoringRequests.indexOf(id);
		            $scope.post.getTutoringRequests.splice(index, 1);
		            console.log(data.data.message)
                    $scope.getPendingRequests();
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }


    $scope.seletedTeacher = function (id)
    {
        // console.log(id);
        $scope.SELECTED_TEACHERID=id.TEACHERID;
        $scope.temp.ddlTeacher=id.TEACHERID.toString();
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0){
            $scope.temp.txtRatePerHour = '';
            $scope.temp.txtTeacherShare = '';
            $scope.temp.txtMEPShare = '';
        }else{
            $scope.temp.txtRatePerHour = Number(id.RATE_PER_HOUR);
            $scope.temp.txtTeacherShare = Number(id.TEACHER_SHARE);
            // $scope.temp.txtMEPShare = '';
            $scope.setMEPShare();
        }
        $scope.getTutoringRequests();
        $('#txtRatePerHour').focus();
    }


    /*************************** END  PROCESSING ***************************************/

  



     /* ========== GET PENDING REQUESTS =========== */
     $scope.getPendingRequests = function () {
        $scope.SpinPendingData = true;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getPendingRequests');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtFromDT", !$scope.temp.txtFromDT || $scope.temp.txtFromDT=='' ? '' : $scope.temp.txtFromDT.toLocaleDateString('sv-SE'));
                formData.append("txtToDT", !$scope.temp.txtToDT || $scope.temp.txtToDT=='' ? '' : $scope.temp.txtToDT.toLocaleDateString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPendingRequests = data.data.success ? data.data.data : [];
            $scope.SpinPendingData = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.SpinPendingData = false;
        })
    }
    // $scope.getPendingRequests(); --INIT


    $scope.clearFilter = function(){
        $scope.temp.txtFromDT ='';
        $scope.temp.txtToDT='';
        $scope.getPendingRequests();
    }




 /*************************** START HOURLY TUTORING ***************************************/

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
            if($scope.temp.ddlLocation > 0) $scope.getPendingRequests();
            //if($scope.temp.ddlLocation > 0) $scope.getTutoringRequests();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


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


    $scope.savehourly = function(){
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
                formData.append("type", 'savehourly');
                formData.append("reqid", $scope.temp.reqid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("txtReqDate", $scope.temp.txtReqDate.toLocaleDateString('sv-SE'));
                formData.append("subjectIDs", $scope.selected_subjects);
                formData.append("ddlPriority", $scope.temp.ddlPriority);
                formData.append("txtStartDate", $scope.temp.txtStartDate.toLocaleDateString('sv-SE'));
                formData.append("txtEndDate", (!$scope.temp.txtEndDate || $scope.temp.txtEndDate=='') ? '' : $scope.temp.txtEndDate.toLocaleDateString('sv-SE'));                
                formData.append("txtExpectedFrom", $scope.temp.txtExpectedFrom);
                formData.append("txtExpectedTo", $scope.temp.txtExpectedTo);
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
                if($scope.selected_subjects.length==1)
                {
                    $scope.temp.reqid = data.data.REQID;
                }
                else
                {
                    $scope.clearhourly();

                }

                $scope.messageSuccess(data.data.message);
                $scope.getPendingRequests();
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



    /* ============ Edit Button ============= */ 
    $scope.edithourly = function (id) {
        console.log(id);
        $scope.clearDet();
        $("#ddlLocation").focus();
        $(".dropdown-toggle,#ddlPlan").attr('disabled','disabled');
        $scope.selected_subjects=[];
        $scope.SUBJECT_model=[];

        $scope.temp.reqid = id.REQID;
        $scope.temp.txtReqDate = new Date(id.REQDATE);
        $scope.temp.ddlPriority = id.REQPRIORITY.toString();
        $scope.temp.txtStartDate = (!id.STARTDATE || id.STARTDATE=='') ? '' : new Date(id.STARTDATE);
        $scope.temp.txtEndDate = (!id.ENDDATE || id.ENDDATE=='') ? '' : new Date(id.ENDDATE);
        $scope.temp.txtExpectedFrom = Number(id.RATEFROM);
        $scope.temp.txtExpectedTo = Number(id.RATETO);
        $scope.temp.txtComments = id.COMMENTS;
        $scope.temp.ddlStatus = id.REQSTATUS;
        
        $scope.editMode = true;
        $scope.index = $scope.post.getPendingRequests.indexOf(id);

        $scope.getTutoringReqDetails();
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearhourly = function(){
        $("#ddlLocation").focus();
        $(".dropdown-toggle,#ddlPlan").removeAttr('disabled');
        $scope.temp.reqid = '';
        $scope.temp.txtReqDate = '';
        $scope.temp.ddlPriority = '1';
        $scope.temp.txtStartDate = '';
        $scope.temp.txtEndDate = '';
        $scope.temp.txtExpectedFrom = '';
        $scope.temp.txtExpectedTo = '';
        $scope.temp.txtComments = '';
        $scope.temp.ddlStatus = 'OPEN';
        $scope.selected_subjects=[];
        $scope.SUBJECT_model=[];
        $scope.editMode = false;
        $scope.post.getTutoringReqDetails=[];
        $scope.clearDet();
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


    /* ========== DELETE =========== */
    $scope.deletehourly = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'REQID': id.REQID, 'type': 'deletehourly' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getPendingRequests.indexOf(id);
		            $scope.post.getPendingRequests.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clear();
                    $scope.getPendingRequests();
                    
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
        $(".dropdown-toggle,#ddlPlan").removeAttr('disabled');
    }
    



 /*************************** END HOURLY TUTORING ***************************************/


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