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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editModeSUB = false;
    $scope.editModeTime = false;
    $scope.Page = "L&A";
    $scope.PageSub = "HourlyTutoring";
    $scope.PageSub1 = "TEACHERSUBJECTSET";
        $scope.SUBJECT_model = [];

    $scope.SUBJECT_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    
    var url = 'code/Teacher_Subject_Setting.php';
    var Masterurl = 'code/MASTER_API.php';

    $scope.DAYNAME_LIST = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
    
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

    $scope.setMEPShare = function(){
        $scope.temp.txtTeacherShare = $scope.temp.txtTeacherShare>100?0:$scope.temp.txtTeacherShare;
        $scope.temp.txtMEPShare=(100-(!$scope.temp.txtTeacherShare ? 0 : $scope.temp.txtTeacherShare));
    }

    $scope.saveSubject = function(){
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
                formData.append("type", 'saveSubject');
                formData.append("tsubid", $scope.temp.tsubid);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("subjectIDs", $scope.selected_subjects);
                formData.append("txtRatePerHour", $scope.temp.txtRatePerHour);
                formData.append("txtTeacherShare", $scope.temp.txtTeacherShare);
                formData.append("txtMEPShare", $scope.temp.txtMEPShare);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTeacherSubjects();
                // $scope.clear();
                $("#ddlTeacher").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET TEACHER ATT SETTINGS =========== */
     $scope.getTeacherSubjects = function () {
        $scope.post.getTeacherSubjects=[];
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0) return
         $('#SpinSubject').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherSubjects','ddlTeacher':$scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherSubjects = data.data.success ? data.data.data : [];
            $('#SpinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherSubjects(); --INIT


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
            if($scope.temp.ddlLocation > 0) $scope.getTeacherByLocation();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
        /* ========== GET Location =========== */


    /* ========== GET TEACHERS =========== */
    $scope.getTeacherByLocation = function () {
        $scope.spinTeacher =  true;
        $http({
            method: 'post',
            url: Masterurl,
            data: $.param({ 'type': 'getTeacherByLocation','LOCID':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherByLocation = data.data.success ? data.data.data : [];
            $scope.spinTeacher =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
        /* ========== GET Location =========== */


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
    $scope.editSubject = function (id) {
        $("#ddlTeacher").focus();
        $(".dropdown-toggle").attr('disabled','disabled');
        
        $scope.temp.tsubid=id.TSUBID;
        $scope.temp.txtRatePerHour=Number(id.RATE_PER_HOUR);
        $scope.temp.txtTeacherShare=Number(id.TEACHER_SHARE);
        $scope.temp.txtMEPShare=Number(id.MEP_SHARE);
        $scope.editModeSUB = true;
        $scope.index = $scope.post.getTeacherSubjects.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearSubject = function(){
        $("#ddlLocation").focus();
        $(".dropdown-toggle").removeAttr('disabled');
        $scope.temp.tsubid='';
        $scope.temp.txtRatePerHour='';
        $scope.temp.txtTeacherShare='';
        $scope.temp.txtMEPShare='';
        $scope.selected_subjects=[];
        $scope.SUBJECT_model=[];
        $scope.editModeSUB = false;
    }


    /* ========== DELETE =========== */
    $scope.deleteSubject = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TSUBID': id.TSUBID, 'type': 'deleteSubject' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTeacherSubjects.indexOf(id);
		            $scope.post.getTeacherSubjects.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    


    //==================================================
    //                    TIMING
    //==================================================
    $scope.saveTiming = function(){
        $(".btn-saveT").attr('disabled', 'disabled').text('Saving...');
        $(".btn-updateT").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveTiming');
                formData.append("tdtid", $scope.temp.tdtid);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlDay", $scope.temp.ddlDay);
                formData.append("txtFromTime", (!$scope.temp.txtFromTime || $scope.temp.txtFromTime=='') ? '' : $scope.temp.txtFromTime.toLocaleString('sv-SE'));
                formData.append("txtToTime", (!$scope.temp.txtToTime || $scope.temp.txtToTime=='') ? '' : $scope.temp.txtToTime.toLocaleString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTeacherTiming();
                // $scope.clear();
                $("#ddlDay").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveT').removeAttr('disabled').text('SAVE');
            $('.btn-updateT').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET TEACHER TIMING =========== */
    $scope.getTeacherTiming = function () {
        $scope.post.getTeacherTiming=[];
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0) return
            $('#SpinTiming').show();
            $http({
                method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherTiming','ddlTeacher':$scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherTiming = data.data.success ? data.data.data : [];
            $('#SpinTiming').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherTiming(); --INIT

    /* ============ Edit Button ============= */ 
    $scope.editTiming = function (id) {
        $("#ddlDay").focus();
        
        $scope.temp.tdtid=id.TDTID;
        $scope.temp.ddlDay=id.DYNAME;
        $scope.temp.txtFromTime=(!id.FROMTIME || id.FROMTIME=='-') ? '' : new Date('2023-01-01T'+id.FROMTIME_SET);
        $scope.temp.txtToTime=(!id.TOTIME || id.TOTIME=='-') ? '' : new Date('2023-01-01T'+id.TOTIME_SET);
        $scope.editModeTime = true;
        $scope.index = $scope.post.getTeacherTiming.indexOf(id);
    }
        
        
    /* ============ Clear Form =========== */ 
    $scope.clearTiming = function(){
        $("#ddlDay").focus();
        $scope.temp.tdtid='';
        $scope.temp.ddlDay='';
        $scope.temp.txtFromTime='';
        $scope.temp.txtToTime='';
        $scope.editModeTime = false;
    }


    /* ========== DELETE =========== */
    $scope.deleteTiming = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TDTID': id.TDTID, 'type': 'deleteTiming' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTeacherTiming.indexOf(id);
                    $scope.post.getTeacherTiming.splice(index, 1);
                    console.log(data.data.message)
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }

  /* ========== GET TEACHER FEEDBACK =========== */

    $scope.getTeacherFeedback = function () {
        //$scope.post.Feedback = [];
        $scope.temp.Feedbackdate='';
        $scope.temp.txtRating= '';
        $scope.temp.txtComment='';

        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0) return
            $('.SpinFeedback').show();
            $http({
                method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherFeedback','ddlTeacher':$scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
             console.log(data.data);
             $scope.post.Feedback = data.data.success ? data.data.data : [];
            if(data.data.success)
            {                
                //$scope.post.Feedback = data.data.success ? data.data.data : [];
                $scope.temp.Feedbackdate=$scope.post.Feedback['FBDATE'];
                $scope.temp.txtRating= $scope.post.Feedback['RATING']>=0 ? Number($scope.post.Feedback['RATING']):0;
                $scope.temp.txtComment=$scope.post.Feedback['COMMENT'];

            }
            
            $('.SpinFeedback').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    /* ========== UPDATE TEACHER FEEDBACK =========== */

    $scope.updateFeedback = function()
    {       
        $(".btnFB").attr('disabled', 'disabled').text('Updating...');               
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateFeedback');                
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);               
                formData.append("txtRating", $scope.temp.txtRating);
                formData.append("txtComment", $scope.temp.txtComment);                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);                                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            
            $('.btnFB').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== UPDATE TEACHER FEEDBACK =========== */



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