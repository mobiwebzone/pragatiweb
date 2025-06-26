$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect","angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.PageSub = "LA_GRADE_SUB";
    $scope.PageSub1 = "";
    $scope.chkStudentidList=[];
    $scope.chkTeacheridList=[];
    $scope.StudentListLength=0;
    $scope.TeacherListLength=0;
    $scope.SUBJECT_model = [];
    
    $scope.SUBJECT_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    
    var url = 'code/LA-ASSIGN-GRADE-SUBJECT.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
    }


    $scope.checkList_Blank = (FOR,val,index) =>{
        // console.log($scope.chkStudentidList.filter(x=>x!=='0'));
        if(FOR=='ST') $scope.StudentListLength = $scope.chkStudentidList.filter(x=>x!=='0').length;
        if(FOR=='TH') $scope.TeacherListLength = $scope.chkTeacheridList.filter(x=>x!=='0').length;
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
                $scope.locid=data.data.locid;

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    $scope.getPlans();
                    // $scope.getSubjects();
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

    $scope.clearFields = function(){
        $scope.post.getStudents = [];
        $scope.chkStudentidList = [];
        $scope.post.getTeachers=[];
        $scope.chkTeacheridList=[];
        $scope.StudentListLength=0;
        $scope.TeacherListLength=0;

        if($scope.temp.ddlStudentTeacher == 'STUDENT'){
            $scope.getStudents();
        }else if($scope.temp.ddlStudentTeacher == 'TEACHER'){
            $scope.getTeachers();
        }
    }

    $scope.assignGradeSubject = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        const teacher_student = $scope.temp.ddlStudentTeacher;
        if(!teacher_student || teacher_student=='') return;
        const assignFor = teacher_student=='STUDENT' ? 'assignGradeSubject' : 'assignGradeSubject_Teacher';
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", assignFor);
                formData.append("sgsid", $scope.temp.sgsid);
                formData.append("studentIdList", $scope.chkStudentidList.filter(x=>x!=0));
                formData.append("teacheridList", $scope.chkTeacheridList.filter(x=>x!=0));
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.SUBJECT_model.map(x=>x.id));
                // formData.append("ddlSubject", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.temp.sgsid='';
                if($scope.editMode){
                    $scope.temp.ddlPlan='';
                    $scope.chkStudentidList=[];
                    $scope.post.getStudents=[];
                    $scope.post.getTeachers=[];
                    $scope.chkTeacheridList=[];
                    $scope.StudentListLength=0;
                    $scope.TeacherListLength=0;
                    $scope.temp.ddlGrade='';
                    $scope.temp.ddlSubject='';
                    $("#ddlSubject").find('.dropdown-toggle').attr('disabled',false);
                    $scope.SUBJECT_model = [];
                }
                $scope.editMode = false;
                $scope.getAssignedData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('Assign Grade & Subject');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


     /* ========== GET ASSIGNED DATA =========== */
     $scope.getAssignedData = function () {
        // if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        if($scope.editMode) return;
        $scope.SpinMainData=true;
        $http({
            method: 'post',
            url: url,
            processData:false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", "getAssignedData");
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlStudentTeacher", $scope.temp.ddlStudentTeacher);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAssignedData = data.data.success ? data.data.data : [];
            $scope.SpinMainData=false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getAssignedData();

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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            // if($scope.temp.ddlLocation > 0) $scope.getGrades();
            // if($scope.temp.ddlLocation > 0) $scope.getSubjects();
            if($scope.temp.ddlLocation > 0) $scope.getAssignedData();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.data;
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */
    
    
    
    /* ========== GET STUDENTS =========== */
    $scope.getStudents = function () {
        $scope.post.getStudents=[];
        $scope.chkStudentidList=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlPlan || $scope.temp.ddlPlan<=0) return;
        $scope.spinStudents = true;
        $scope.spinStudentLT = true;
        $('#ddlPlan').attr('disabled','true');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getStudents',
                            'ddlPlan':$scope.temp.ddlPlan,
                            'ddlLocation':$scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getStudents = data.data.success ? data.data.data : [];

            $scope.spinStudents = false;
            $scope.spinStudentLT = false;
            $('#ddlPlan').removeAttr('disabled').focus();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getStudents();
    /* ========== GET STUDENTS =========== */
    
    
    
    /* ========== GET TEACHERS =========== */
    $scope.getTeachers = function () {
        $scope.post.getTeachers=[];
        $scope.chkTeacheridList=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $scope.spinTeachers = true;
        $http({
            method: 'POST',
            url: masterUrl,
            processData:false,
            transformRequest: function(data){
                var formData = new FormData();
                formData.append('type', 'getTeacherVolunteerByLocation');
                formData.append('LOCID', $scope.temp.ddlLocation);   
                return formData;
            },
            data:$scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getTeachers = data.data.success ? data.data.data : [];

            $scope.spinTeachers = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getStudents();
    /* ========== GET TEACHERS =========== */


    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $('.spinGrade').show();
        $http({
            method: 'POST',
            url: 'code/LA-GRADE-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
                formData.append("ddlLocation", 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrades = data.data.success ? data.data.data : [];
            $('.spinGrade').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
     $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $('.spinSubject').show();
        $http({
            method: 'POST',
            url: 'code/LA-SUBJECT-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjects');
                formData.append("ddlLocation", 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubjects = data.data.success ? data.data.data : [];
            if($scope.post.getSubjects.length>0){

                var editedData = $scope.post.getSubjects.map(x=>({
                    ...x,
                    id:x.SUBID,
                    label:x.SUBJECTNAME
                }))
                $scope.post.getSubjects = editedData;
            }
            $('.spinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
     $scope.getSubjects();
    /* ========== GET SUBJECT =========== */



    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        console.log(id);
        $scope.clear();
        $scope.temp.ddlStudentTeacher =id.STUDENT_TEACHER;
        // $scope.getAssignedData();
        $('#ddlLocation, #ddlPlan').attr('disabled','disabled');
        $scope.temp.sgsid = id.SGSID;
        $scope.temp.ddlGrade = id.GRADEID.toString();
        // $scope.temp.ddlSubject = id.SUBID.toString();
        $scope.SUBJECT_model = [{'id':id.SUBID,'label':id.SUBJECT}];
        $("#ddlSubject").find('.dropdown-toggle').attr('disabled',true);

        $scope.editMode = true;
        $scope.index = $scope.post.getAssignedData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // $scope.temp={};
        $scope.temp.sgsid='';
        $scope.temp.ddlPlan='';
        $scope.temp.ddlStudentTeacher = '';
        $scope.chkStudentidList=[];
        $scope.post.getStudents=[];
        $scope.post.getTeachers=[];
        $scope.chkTeacheridList=[];
        $scope.StudentListLength=0;
        $scope.TeacherListLength=0;
        $scope.temp.ddlGrade='';
        $scope.temp.ddlSubject='';
        $scope.editMode = false;
        $('#ddlLocation, #ddlPlan').removeAttr('disabled');
        $("#ddlSubject").find('.dropdown-toggle').attr('disabled',false);
        $scope.SUBJECT_model = [];
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                processData: false,
                transformRequest: function(data){
                    var formData = new FormData();
                    formData.append('type','delete');
                    formData.append('SGSID',id.SGSID);
                    formData.append('ddlStudentTeacher',$scope.temp.ddlStudentTeacher);
                    return formData;
                },
                data:$scope.temp,
                headers: {'Content-Type': undefined}
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getAssignedData.indexOf(id);
		            $scope.post.getAssignedData.splice(index, 1);
		            console.log(data.data.message)
                    // $scope.clear();
                    
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