$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect", "ngSanitize","angular.filter"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$sce) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "L&A";
    $scope.PageSub = "LA_TOPICS_COVERED";
    $scope.PageSub1 = "";
    $scope.chkStudentidList=[];
    $scope.attRemarksList=[];
    $scope.StudentListLength=0;
    $scope.temp.txtAttDate=new Date();
    $scope.PAGEFOR = 'ADMIN';

    $scope.SLIDE_model = [];

    // $scope.SLIDE_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.SLIDE_settings = {enableSearch: true,scrollable: true};
    
    var url = 'code/LA-TOPICS-COVERED.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
    }


    $scope.checkStudentList_Blank = (val,index) =>{
       $scope.StudentListLength = $scope.chkStudentidList.filter(x=>x!=='0').length;
       $scope.temp.attAll = ($scope.StudentListLength==$scope.chkStudentidList.length) && $scope.StudentListLength>0?'1':'0';
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
                $(document).ready(function() { 
                    $("#SLIDE").find('div').css({'width':'100%'});
                    $("#SLIDE").find('button').addClass('btn-block');
                    // $('.dropdown-toggle').attr('disabled','disabled');
                });
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

    $scope.saveAttendance = function(id,index){
        $('#attRemarks'+index,'#studentList'+index).attr('disabled','disabled');
        // $(".btnAttSave").attr('disabled', 'disabled').text('Saving...');
        // $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveAttendance');
                formData.append("attid", id.ATTID);
                formData.append("tcid", $scope.temp.tcid);
                formData.append("remark", $scope.attRemarksList[index]);
                formData.append("attendance", $scope.chkStudentidList[index]);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // $scope.clear();
                // $scope.getBatchStudentsData();
                // $scope.getStudentBatchesData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('#attRemarks'+index,'#studentList'+index).removeAttr('disabled');
            // $('.btnAttSave').removeAttr('disabled').text('SAVE ATTENDANCE');
            // $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


    $scope.saveAllAttendance = function(){
        $scope.chkStudentidList = new Array($scope.post.getStudents.length).fill($scope.temp.attAll);
        $scope.studentList = $scope.post.getStudents.map(x=>x.REGID);
        // return;
        $('#attAll,.Mremark,.MchkAtt').attr('disabled','disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveAllAttendance');
                formData.append("tcid", $scope.temp.tcid);
                formData.append("txtAttDate", $scope.temp.txtAttDate.toLocaleDateString('sv-SE'));
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlBatches", $scope.temp.ddlBatches);
                formData.append("studentIdList", $scope.studentList);
                formData.append("attAll", $scope.temp.attAll);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // $scope.clear();
                // $scope.getBatchStudentsData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('#attAll,.Mremark,.MchkAtt').removeAttr('disabled');
            // $('.btnAttSave').removeAttr('disabled').text('SAVE ATTENDANCE');
            // $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }




    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $('#ddlLocation').focus();
        // $scope.temp={};
        $scope.temp.txtAttDate=new Date();
        $scope.temp.ddlTeacher='';
        $scope.temp.ddlBatches='';
        $scope.post.getStudents=[];
        $scope.chkStudentidList=[];
        $scope.attRemarksList=[];
        $scope.post.getCoveredTopics=[];
        $scope.editMode = false;
        $scope.clearTC();
        // $('#ddlLocation, #ddlPlan').removeAttr('disabled');
    }





    // ####################################################################################
    //                                      ADD STUDENT START
    // ####################################################################################
    $scope.saveTC = function(){
        // alert($scope.temp.ddlTopic)
        // return;
        $(".btnSaveTC").attr('disabled', 'disabled').text('Saving...');
        $(".btnUpdateTC").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.FINAL_SLIDEID = [];
        $scope.FINAL_SLIDEID = $scope.SLIDE_model.map(x=>x.id);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveTC');
                formData.append("tcid", $scope.temp.tcid);
                formData.append("txtAttDate", $scope.temp.txtAttDate.toLocaleDateString('sv-SE'));
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlBatches", $scope.temp.ddlBatches);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("ddlTopic", $scope.temp.ddlTopic);
                formData.append("ddlSlide", $scope.FINAL_SLIDEID);
                formData.append("txtRemark_CP", $scope.temp.txtRemark_CP);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.tcid = data.data.GET_TCID;
                $scope.getStudents();
                $scope.messageSuccess(data.data.message);
                // $scope.clear();
                $scope.getCoveredTopics();
                // $scope.getBatchStudentsData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnSaveTC').removeAttr('disabled').text('SAVE');
            $('.btnUpdateTC').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET DATA =========== */
    $scope.getCoveredTopics = function () {
        $scope.post.getCoveredTopics=[];
        if(!$scope.temp.txtAttDate || $scope.temp.txtAttDate=='' || !$scope.temp.ddlTeacher || $scope.temp.ddlTeacher==0 || !$scope.temp.ddlBatches || $scope.temp.ddlBatches==0) return;
        $('.spinCoverTopics').show();
        $http({
            method: 'post',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getCoveredTopics');
                formData.append("txtAttDate", $scope.temp.txtAttDate.toLocaleDateString('sv-SE'));
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlBatches", $scope.temp.ddlBatches);
                // formData.append("ddlGrade", $scope.temp.ddlGrade);
                // formData.append("ddlSubject", $scope.temp.ddlSubject);
                // formData.append("ddlTopic", $scope.temp.ddlTopic);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getCoveredTopics = data.data.success ? data.data.data : [];
            $('.spinCoverTopics').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getAssignedData();


    /* ============ Edit Button ============= */ 
    $scope.editTC = function (id) {
        $scope.clearTC();
        $('#ddlGrade').focus();
        // $('#ddlLocation, #ddlPlan').attr('disabled','disabled');

        $scope.temp.tcid = id.TCID;
        $scope.temp.ddlGrade = id.GRADEID.toString();
        $scope.temp.ddlSubject = id.SUBID.toString();
        
        $scope.getTopics();
        // $scope.$watch('post.getTopics', function () {
            $timeout(()=>{
                $scope.temp.ddlTopic = id.TOPICID.toString();
                $scope.temp.ddlTopicSet = id.TOPICID.toString();
                $scope.getSlides();
                $timeout(()=>{
                    var myArr = [];
                    $scope.SLIDE_model=[];
                    if(id.SLIDE_LIST.length>0){
                        for(i=0;i<id.SLIDE_LIST.length;i++){
                            $scope.post.getSlides.filter(function(x){
                                if(x['id']==id.SLIDE_LIST[i]) {
                                    $scope.SLIDE_model.push(x);
                                }
                            })
                        }
                    }
                    // $scope.SLIDE_model = myArr;
                },1000);
            },1000);
        // }, true);
        $scope.temp.txtRemark_CP = id.REMARK;
        
        $scope.editMode = true;
        $scope.index = $scope.post.getCoveredTopics.indexOf(id);

        $scope.getStudents();
    }
    
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearTC = function(){
        $('#ddlGrade').focus();
        $scope.temp.tcid ='';
        $scope.temp.ddlGrade='';
        $scope.temp.ddlSubject='';
        $scope.temp.ddlTopic='';
        $scope.temp.ddlTopicSet='';
        $scope.temp.ddlTopic='';
        $scope.temp.txtRemark_CP='';
        $scope.post.getTopics=[];
        $scope.selectDesign = '';
        $FINAL_SLIDEID = [];
        $scope.SLIDE_model = [];

        $scope.post.getStudents=[];
        $scope.chkStudentidList=[];
        $scope.attRemarksList=[];
        // $('#ddlLocation, #ddlPlan').removeAttr('disabled');
    }



    /* ========== DELETE =========== */
    $scope.deleteTC = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TCID': id.TCID, 'type': 'deleteTC' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                //  console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getCoveredTopics.indexOf(id);
                    $scope.post.getCoveredTopics.splice(index, 1);
                    $scope.clearTC();
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }

    // ####################################################################################
    //                                      ADD STUDENT END
    // ####################################################################################
    



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









/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */
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
            if($scope.temp.ddlLocation > 0) $scope.getTeachers();
            if($scope.temp.ddlLocation > 0) $scope.getGrades();
            if($scope.temp.ddlLocation > 0) $scope.getBatches();
            if($scope.temp.ddlLocation > 0) $scope.getSubjects();
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
        $scope.attRemarksList=[];
        if(!$scope.temp.txtAttDate || $scope.temp.txtAttDate=='' || !$scope.temp.ddlTeacher || $scope.temp.ddlTeacher==0 || !$scope.temp.ddlBatches || $scope.temp.ddlBatches==0) return;
        $('.spinStudents, .spinStudentLT').show();
        $('#ddlBatches').attr('disabled','true');
        $http({
            method: 'post',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getStudents');
                formData.append("tcid", $scope.temp.tcid);
                formData.append("txtAttDate", $scope.temp.txtAttDate.toLocaleDateString('sv-SE'));
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlBatches", $scope.temp.ddlBatches);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data);
            $scope.post.getStudents = data.data.success ? data.data.data : [];
            $timeout(()=>{
                $scope.checkStudentList_Blank();
            },500);

            $('.spinStudents, .spinStudentLT').hide();
            $('#ddlBatches').removeAttr('disabled').focus();
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
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinTeacher').show();
        $http({
            method: 'POST',
            url: masterUrl,
            processData:false,
            transformRequest: function (data){
                var formData = new FormData();
                formData.append("type",'getTeachersByLocation');
                formData.append("LOCID",$scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getTeachers = data.data.success ? data.data.data : [];

            $('.spinTeacher').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getTeachers();
    /* ========== GET TEACHERS =========== */


    /* ========== GET BATCHES =========== */
    $scope.getBatches = function () {
        $scope.post.getBatches=[];
        $scope.post.getStudents=[];
        $scope.chkStudentidList=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinBatch').show();
        $http({
            method: 'POST',
            url: masterUrl,
            processData:false,
            transformRequest: function (data){
                var formData = new FormData();
                formData.append("type",'getBatchesByLocation');
                formData.append("LOCID",$scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getBatches = data.data.success ? data.data.data : [];
            $('.spinBatch').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getBathes();
    /* ========== GET BATCHES =========== */


    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $scope.post.getGrades=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
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
    //  $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $scope.post.getSubjects=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
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
            $('.spinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getSubjects();
    /* ========== GET SUBJECT =========== */

    /* ========== GET TOPICS =========== */
    $scope.getTopics = function () {
        $scope.temp.ddlTopic='';
        $scope.post.getTopics=[];
        $scope.selectDesign = '';
        $scope.post.getSlides=[];
        $scope.SLIDE_model = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0)return;
        $('.spinTopic').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTopicsByLoc_Grade_Subject');
                formData.append("LOCID", $scope.temp.ddlLocation);
                formData.append("TEACHERID", $scope.temp.ddlTeacher);
                // formData.append("LOCID", 1);
                formData.append("GRADEID", $scope.temp.ddlGrade);
                formData.append("SUBID", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getTopics = data.data.success ? data.data.data : [];
            $scope.selectDesign = data.data.success ? $sce.trustAsHtml(data.data.finalData): '';
            $('.spinTopic').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    $scope.setTopicId=function(optionElement){
        $scope.temp.ddlTopic = optionElement.value.toString();
        $scope.getSlides();
    }
    /* ========== GET TOPICS =========== */

    /* ========== GET SLIDES =========== */
    $scope.getSlides = function () {
        $scope.post.getSlides=[];
        $FINAL_SLIDEID = [];
        if(!$scope.temp.ddlTopic || $scope.temp.ddlTopic<=0) return;
        $('.spinSlide').show();
        $http({
            method: 'POST',
            url: masterUrl,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSlidesByTopic_ForMulti');
                formData.append("TOPICID", $scope.temp.ddlTopic);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSlides = data.data.success ? data.data.data : [];
            $('.spinSlide').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getSlides();
    /* ========== GET SLIDES =========== */

/* ######################################################################################################################### */
/*                                          GET EXTRA DATA END                                                               */
/* ######################################################################################################################### */


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