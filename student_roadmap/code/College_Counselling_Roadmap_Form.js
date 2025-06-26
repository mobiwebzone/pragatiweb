
$postModule = angular.module("myApp", ["ngSanitize","angularjs-dropdown-multiselect"]);

$postModule.controller("myCtrl", function ($scope, $http,$window,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.temp.ddlStudentType = 'Non-Registered';
    
    var url = '../backoffice/code/Student_College_Roadmap.php';



    $(window).bind('beforeunload', function(){
        return "Do you want to exit this page?";
    });
    



    /* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveData = function(){
        $scope.UID = '';
        $scope.UPASS = '';
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $scope.temp.ddlLocation = 1;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("roadmapid",$scope.temp.roadmapid);
                formData.append("ddlStudentType",$scope.temp.ddlStudentType);
                formData.append("ddlLocation",$scope.temp.ddlLocation);
                formData.append("ddlStudent",$scope.temp.ddlStudent);
                formData.append("txtFirstName",$scope.temp.txtFirstName);
                formData.append("txtLastName",$scope.temp.txtLastName);
                formData.append("txtPhone",$scope.temp.txtPhone);
                formData.append("txtEmail",$scope.temp.txtEmail);
                formData.append("txtP1FName",$scope.temp.txtP1FName);
                formData.append("txtP1LName",$scope.temp.txtP1LName);
                formData.append("txtP1Phone",$scope.temp.txtP1Phone);
                formData.append("txtP1Email",$scope.temp.txtP1Email);
                formData.append("txtP2FName",$scope.temp.txtP2FName);
                formData.append("txtP2LName",$scope.temp.txtP2LName);
                formData.append("txtP2Phone",$scope.temp.txtP2Phone);
                formData.append("txtP2Email",$scope.temp.txtP2Email);
                formData.append("ddlGrade",$scope.temp.ddlGrade);
                formData.append("ddlSchoolYear",$scope.temp.ddlSchoolYear);
                formData.append("txtClassof",$scope.temp.txtClassof);
                formData.append("txtSchool",$scope.temp.txtSchool);
                formData.append("txtCounty",$scope.temp.txtCounty);
                formData.append("ddlCountry",$scope.temp.ddlCountry);
                // formData.append("ddlState",$scope.temp.ddlState);
                // formData.append("ddlCity",$scope.temp.ddlCity);
                formData.append("txtState",$scope.temp.txtState);
                formData.append("txtCity",$scope.temp.txtCity);
                formData.append("txtRemarks",$scope.temp.txtRemarks);
                formData.append("BackOff",$scope.BackOff);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.roadmapid = data.data.GET_ROADMAPID;
                $scope.temp.regid = data.data.GET_REGID;

                $scope.UID = (data.data.ID && data.data.ID !='') ? data.data.ID : '';
                $scope.UPASS = (data.data.PASS && data.data.PASS !='') ? data.data.PASS : '';

                if($scope.UID !='' && $scope.UPASS!=''){
                    $('#StudentIDModal').modal('show');
                }

                // console.log(`${$scope.temp.roadmapid}  /  ${$scope.temp.regid}`);

                if($scope.temp.roadmapid>0 && $scope.temp.regid>0)$scope.getStudentCollegeRoadmap($scope.temp.roadmapid,$scope.temp.regid);
                $scope.messageSuccess(data.data.message);
                
                // $timeout(()=>{$("#ddlLocation").focus();},500);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('Save and go to next section');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */ 


    /* ========== GET STUDENT COLLEGE ROADMAP =========== */
    $scope.getStudentCollegeRoadmap = function (roadmapid,regid) {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getStudentCollegeRoadmap','roadmapid':roadmapid,'regid':regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentCollegeRoadmap = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();

            //  console.log($scope.post.getStudentCollegeRoadmap[0]);
             data.data.success ? $scope.editData($scope.post.getStudentCollegeRoadmap[0]) : '';
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentCollegeRoadmap(); --INIT
    /* ========== GET STUDENT COLLEGE ROADMAP =========== */


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {

        // HIDE AND CLEAR ALL COLLAPSE
        $('.collapse').collapse('hide');
        $scope.clearFormClass();
        $scope.post.getRoadmapClasses=[];
        $scope.clearFormActivity();
        $scope.post.getRoadmapActivities=[];
        $scope.clearFormTest();
        $scope.post.getRoadmapTests=[];
        $scope.clearFormMM();
        $scope.post.getRoadmapMajorMinor=[];
        $scope.clearFormCollege();        
        $scope.post.getRoadmapColleges=[];
        $scope.REC_UPD = false; 
        $scope.ForSecID = 0;
        $scope.ForSec = '';
        // HIDE AND CLEAR ALL COLLAPSE

        $("#ddlStudentType").focus();
        // $scope.clearFormClass();

        $scope.temp.roadmapid = id.ROADMAPID;
        $scope.temp.ddlStudentType = id.STUDENT_TYPE;
        $scope.temp.ddlLocation = (id.LOCID).toString();
        // if(id.LOCID > 0 && $scope.temp.ddlLocation>0) $scope.getStudentByLoc();
        $timeout(()=>{
            // alert(id.REGID);
            $scope.temp.ddlStudent =  (id.REGID && id.REGID>0) ? (id.REGID).toString() : ''; 
            $scope.temp.regid = id.REGID;
        },1000);
        $scope.temp.txtFirstName = (id.FIRSTNAME && id.FIRSTNAME!='') ? id.FIRSTNAME : '';
        $scope.temp.txtLastName = (id.LASTNAME && id.LASTNAME!='') ? id.LASTNAME : '';
        $scope.temp.txtPhone = (id.PHONE && id.PHONE!='') ? id.PHONE : '';
        $scope.temp.txtEmail = (id.EMAILID && id.EMAILID!='') ? id.EMAILID : '';
        $scope.temp.txtP1FName = (id.PARENT1_FIRST_NAME && id.PARENT1_FIRST_NAME!='') ? id.PARENT1_FIRST_NAME : '';
        $scope.temp.txtP1LName = (id.PARENT1_LAST_NAME && id.PARENT1_LAST_NAME!='') ? id.PARENT1_LAST_NAME : '';
        $scope.temp.txtP1Phone = (id.PARENT1_PHONE && id.PARENT1_PHONE!='') ? id.PARENT1_PHONE : '';
        $scope.temp.txtP1Email = (id.PARENT1_EMAILID && id.PARENT1_EMAILID!='') ? id.PARENT1_EMAILID : '';
        $scope.temp.txtP2FName = (id.PARENT2_FIRST_NAME && id.PARENT2_FIRST_NAME!='') ? id.PARENT2_FIRST_NAME : '';
        $scope.temp.txtP2LName = (id.PARENT2_LAST_NAME && id.PARENT2_LAST_NAME!='') ? id.PARENT2_LAST_NAME : '';
        $scope.temp.txtP2Phone = (id.PARENT2_PHONE && id.PARENT2_PHONE!='') ? id.PARENT2_PHONE : '';
        $scope.temp.txtP2Email = (id.PARENT2_EMAILID && id.PARENT2_EMAILID!='') ? id.PARENT2_EMAILID : '';
        $scope.temp.ddlGrade = (id.CURRENT_GRADEID && id.CURRENT_GRADEID>0) ? (id.CURRENT_GRADEID).toString() : ''; 
        $scope.temp.ddlSchoolYear = (id.ADMYEARID && id.ADMYEARID>0) ? (id.ADMYEARID).toString() : ''; 
        $scope.temp.txtClassof = (id.CLASSOF && id.CLASSOF>0) ? Number(id.CLASSOF) : ''; 
        $scope.temp.txtSchool = (id.SCHOOL && id.SCHOOL!='') ? id.SCHOOL : '';
        $scope.temp.txtCounty = (id.COUNTY && id.COUNTY!='') ? id.COUNTY : '';
        $scope.temp.ddlCountry = (id.COUNTRYID && id.COUNTRYID>0) ? (id.COUNTRYID).toString() : ''; 
        // if(id.COUNTRYID > 0 && $scope.temp.ddlCountry>0) $scope.getStates();
        // $timeout(()=>{
        //     $scope.temp.ddlState =  (id.STATEID && id.STATEID>0) ? (id.STATEID).toString() : ''; 
            
        //     if(id.STATEID > 0 && $scope.temp.ddlState>0) $scope.getCities();
        //     $timeout(()=>{
        //         $scope.temp.ddlCity =  (id.CITYID && id.CITYID>0) ? (id.CITYID).toString() : ''; 
        //     },800);
        // },1000);
        $scope.temp.txtState = (id.STATENAME && id.STATENAME!='') ? id.STATENAME : '';
        $scope.temp.txtCity = (id.CITYNAME && id.CITYNAME!='') ? id.CITYNAME : '';
        $scope.temp.txtRemarks = (id.REMARKS && id.REMARKS!='') ? id.REMARKS : ''; 


        // if($scope.temp.applid > 0)$scope.getStudentApplications_DET();

        $scope.editMode = true;
        $scope.index = $scope.post.getStudentCollegeRoadmap.indexOf(id);
    }
    /* ============ Edit Button ============= */ 

    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;

        $('.collapse').collapse('hide');
        $scope.clearFormClass();
        $scope.post.getRoadmapClasses=[];
        $scope.clearFormActivity();
        $scope.post.getRoadmapActivities=[];
        $scope.clearFormTest();
        $scope.post.getRoadmapTests=[];
        $scope.clearFormMM();
        $scope.post.getRoadmapMajorMinor=[];
        $scope.clearFormCollege();        
        $scope.post.getRoadmapColleges=[];

        $scope.REC_UPD = false; 
        $scope.ForSecID = 0;
        $scope.ForSec = '';
        $("#ddlStudentType").focus();
    }
    /* ============ Clear Form =========== */ 

    /* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 









    /* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE CLASSES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveDataClass = function(){
        $(".btn-save-Class").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-Class").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataClass');
                formData.append("roadmapcid", $scope.temp.roadmapcid);
                formData.append("roadmapid", $scope.temp.roadmapid);
                formData.append("regid", $scope.temp.regid);
                formData.append("ddlGrade_Class", $scope.temp.ddlGrade_Class);
                formData.append("ddlClassSubject_Class", $scope.temp.ddlClassSubject_Class);
                formData.append("txtRemarks_Class", $scope.temp.txtRemarks_Class);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRoadmapClasses();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlGrade_Class").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-Class').removeAttr('disabled').text('ADD');
            $('.btn-update-Class').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */


    /* ========== GET ROADMAP CLASSES =========== */
    $scope.getRoadmapClasses = function () {
        $('#spinClasses').show();
        $http({
                method: 'post',
                url: url,
            data: $.param({ 'type': 'getRoadmapClasses',
                            'roadmapid':$scope.temp.roadmapid,
                            'regid':$scope.temp.regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRoadmapClasses = data.data.success ? data.data.data : [];
                $('#spinClasses').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRoadmapClasses();
    /* ========== GET ROADMAP CLASSES =========== */

    
    /* ============ Edit Button ============= */ 
    $scope.editFormClass = function (id) {
        $("#ClassesModal").modal('show');
        $scope.getClassSubject();
        $("#ddlGrade_Class").focus();
    
        $scope.temp.roadmapcid = id.ROADMAPCID;
        $scope.temp.ddlGrade_Class = (id.GRADEID && id.GRADEID>0) ? id.GRADEID.toString() : '';
        $scope.temp.ddlClassSubject_Class = (id.CSUBID && id.CSUBID>0) ? id.CSUBID.toString() : '';
        $scope.temp.txtRemarks_Class = id.REMARKS;

        $scope.index = $scope.post.getRoadmapClasses.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormClass = function(){
        $("#ddlGrade_Class").focus();
        $scope.temp.roadmapcid = '';
        $scope.temp.ddlGrade_Class = '';
        $scope.temp.ddlClassSubject_Class = '';
        $scope.temp.txtRemarks_Class = '';
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteClass = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ROADMAPCID': id.ROADMAPCID, 'type': 'deleteClass' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRoadmapClasses.indexOf(id);
		            $scope.post.getRoadmapClasses.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormClass();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE CLASSES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE ACTIVITY START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveDataActivity = function(){
        $(".btn-save-Activity").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-Activity").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();               
                formData.append("type", 'saveDataActivity');
                formData.append("roadmapactid", $scope.temp.roadmapactid);
                formData.append("roadmapid", $scope.temp.roadmapid);
                formData.append("regid", $scope.temp.regid);
                formData.append("ddlGrade_Activity", $scope.temp.ddlGrade_Activity);
                formData.append("ddlActivity_Activity", $scope.temp.ddlActivity_Activity);
                formData.append("ddlJuniorVarsity_Activity", $scope.temp.ddlJuniorVarsity_Activity);
                formData.append("ddlVarsity_Activity", $scope.temp.ddlVarsity_Activity);
                formData.append("txtLocalClub_Activity", $scope.temp.txtLocalClub_Activity);
                // formData.append("ddlDurationType_Activity", $scope.temp.ddlDurationType_Activity);
                // formData.append("txtDuration_Activity", $scope.temp.txtDuration_Activity);
                formData.append("Hours_Week_Activity", $scope.temp.Hours_Week_Activity);
                formData.append("txtNoOFWeeks_Activity", $scope.temp.txtNoOFWeeks_Activity);
                formData.append("ACTIVITY_model", $scope.ACTIVITY_model.map((x)=>{return x.id;}));
                formData.append("txtRemarks_Activity", $scope.temp.txtRemarks_Activity);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRoadmapActivities();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlGrade_Activity").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-Activity').removeAttr('disabled').text('ADD');
            $('.btn-update-Activity').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */


    /* ========== GET ROADMAP ACTIVITY =========== */
    $scope.getRoadmapActivities = function () {
        $('#spinActivities').show();
        $http({
                method: 'post',
                url: url,
            data: $.param({ 'type': 'getRoadmapActivities',
                            'roadmapid':$scope.temp.roadmapid,
                            'regid':$scope.temp.regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRoadmapActivities = data.data.success ? data.data.data : [];
                $('#spinActivities').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRoadmapActivities();
    /* ========== GET ROADMAP ACTIVITY =========== */

    
    /* ============ Edit Button ============= */ 
    $scope.editFormActivity = function (id) {
        $("#ActivityModal").modal('show');
        $scope.getActivities();
        $scope.getActivityLegend();
        $("#ddlGrade_Activity").focus();


        $scope.temp.roadmapactid = id.ROADMAPACTID;
        $scope.temp.ddlGrade_Activity = (id.GRADEID && id.GRADEID>0) ? id.GRADEID.toString() : '';
        $scope.temp.ddlActivity_Activity = (id.ACTIVITYID && id.ACTIVITYID>0) ? id.ACTIVITYID.toString() : '';
        $scope.temp.ddlJuniorVarsity_Activity = (id.JUNIOR_VARSITY && id.JUNIOR_VARSITY!='') ? id.JUNIOR_VARSITY : '';
        $scope.temp.ddlVarsity_Activity = (id.VARSITY && id.VARSITY!='') ? id.VARSITY : '';
        $scope.temp.txtLocalClub_Activity = (id.LOCAL_CLUB && id.LOCAL_CLUB!='') ? id.LOCAL_CLUB : '';
        // $scope.temp.ddlDurationType_Activity = (id.DURATION_TYPE && id.DURATION_TYPE!='') ? id.DURATION_TYPE : '';
        // $scope.temp.txtDuration_Activity = (id.DURATION && id.DURATION!='') ? Number(id.DURATION) : '';
        $scope.temp.Hours_Week_Activity = (id.HOURS_PER_WEEK && id.HOURS_PER_WEEK!='') ? Number(id.HOURS_PER_WEEK) : '';
        $scope.temp.txtNoOFWeeks_Activity = (id.NO_OF_WEEKS && id.NO_OF_WEEKS!='') ? Number(id.NO_OF_WEEKS) : '';
        // $scope.ACTIVITY_model = (id.VARSITY && id.VARSITY!='') ? id.VARSITY : '';
        $scope.temp.txtRemarks_Activity = (id.REMARKS && id.REMARKS!='') ? id.REMARKS : '';

        $timeout(()=>{
            if(id.ROADMAPACTID > 0) $scope.getSelectedActivityLegend(id.ROADMAPACTID);
        },1000);
    

        $scope.index = $scope.post.getRoadmapActivities.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ========== GET SELECTED ACTIVITY LEGENDS =========== */
    $scope.getSelectedActivityLegend = function (ROADMAPACTID) {
        $scope.ACTIVITY_model = [];
        $('.spinActivityLegend').show();
        $http({
                method: 'post',
                url: url,
            data: $.param({ 'type': 'getSelectedActivityLegend','ROADMAPACTID' : ROADMAPACTID,'ROADMAPID':$scope.temp.roadmapid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $('.spinActivityLegend').hide();
            if(!data.data.success) return;
            
            $scope.post.getActivityLegend.forEach(function (o1,index) {
                data.data.data.some(function (o2) {
                    if(o1.id === o2.id) $scope.ACTIVITY_model.push($scope.post.getActivityLegend[index]);
                });
            });
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedActivityLegend();
    /* ========== GET SELECTED ACTIVITY LEGENDS  =========== */


    /* ============ Clear Form =========== */ 
    $scope.clearFormActivity = function(){
        $("#ddlGrade_Activity").focus();
        
        $scope.temp.roadmapactid = '';
        $scope.temp.ddlGrade_Activity = '';
        $scope.temp.ddlActivity_Activity = '';
        $scope.temp.ddlJuniorVarsity_Activity = '';
        $scope.temp.ddlVarsity_Activity = '';
        $scope.temp.txtLocalClub_Activity = '';
        // $scope.temp.ddlDurationType_Activity = '';
        // $scope.temp.txtDuration_Activity = '';
        $scope.temp.Hours_Week_Activity = '';
        $scope.temp.txtNoOFWeeks_Activity = '';
        $scope.temp.txtRemarks_Activity = '';
        $scope.ACTIVITY_model = [];
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteActivity = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ROADMAPACTID': id.ROADMAPACTID, 'type': 'deleteActivity' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRoadmapActivities.indexOf(id);
		            $scope.post.getRoadmapActivities.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormActivity();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE ACTIVITY END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE TESTS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveDataTest = function(){
        $(".btn-save-Test").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-Test").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataTest');
                formData.append("roadmaptestid", $scope.temp.roadmaptestid);
                formData.append("roadmapid", $scope.temp.roadmapid);
                formData.append("regid", $scope.temp.regid);
                formData.append("ddlGrade_Test", $scope.temp.ddlGrade_Test);
                formData.append("txtApproxTestDT_Test", ($scope.temp.txtApproxTestDT_Test && $scope.temp.txtApproxTestDT_Test!='') ? $scope.temp.txtApproxTestDT_Test.toLocaleString('sv-SE') : '');
                formData.append("txtTestName_Test", $scope.temp.txtTestName_Test);
                formData.append("txtTestScore_Test", $scope.temp.txtTestScore_Test);
                formData.append("txtTestSuperScore_Test", $scope.temp.txtTestSuperScore_Test);
                formData.append("txtRemarks_Test", $scope.temp.txtRemarks_Test);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRoadmapTests();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlGrade_Test").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-Test').removeAttr('disabled').text('ADD');
            $('.btn-update-Test').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */


    /* ========== GET ROADMAP TESTS =========== */
    $scope.getRoadmapTests = function () {
        $('#spinTests').show();
        $http({
                method: 'post',
                url: url,
            data: $.param({ 'type': 'getRoadmapTests',
                            'roadmapid':$scope.temp.roadmapid,
                            'regid':$scope.temp.regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRoadmapTests = data.data.success ? data.data.data : [];
                $('#spinTests').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRoadmapTests();
    /* ========== GET ROADMAP TESTS =========== */

    
    /* ============ Edit Button ============= */ 
    $scope.editFormTest = function (id) {
        $("#TestModal").modal('show');
        $("#ddlGrade_Test").focus();

        $scope.temp.roadmaptestid = id.ROADMAPTESTID;
        $scope.temp.ddlGrade_Test = (id.GRADEID && id.GRADEID>0) ? id.GRADEID.toString() : '';
        $scope.temp.txtApproxTestDT_Test = (id.APPROX_TEST_DATE && id.APPROX_TEST_DATE!='') ? new Date(id.APPROX_TEST_DATE) : '';
        $scope.temp.txtTestName_Test = (id.TESTNAME && id.TESTNAME!='') ? id.TESTNAME : '';
        $scope.temp.txtTestScore_Test = (id.TESTSCORE && id.TESTSCORE!='') ? id.TESTSCORE : '';
        $scope.temp.txtTestSuperScore_Test = (id.TESTSUPERSCORE && id.TESTSUPERSCORE!='') ? id.TESTSUPERSCORE : '';
        $scope.temp.txtRemarks_Test = (id.REMARKS && id.REMARKS!='') ? id.REMARKS : '';

        $scope.index = $scope.post.getRoadmapTests.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormTest = function(){
        $("#ddlGrade_Test").focus();
        $scope.temp.roadmaptestid = '';
        $scope.temp.ddlGrade_Test = '';
        $scope.temp.txtApproxTestDT_Test = '';
        $scope.temp.txtTestName_Test = '';
        $scope.temp.txtTestScore_Test = '';
        $scope.temp.txtTestSuperScore_Test = '';
        $scope.temp.txtRemarks_Test = '';
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteTest = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ROADMAPTESTID': id.ROADMAPTESTID, 'type': 'deleteTest' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRoadmapTests.indexOf(id);
		            $scope.post.getRoadmapTests.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormTest();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE TESTS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */     












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MAJOR/MINOR START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveDataMM = function(){
        $(".btn-save-MM").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-MM").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();         
                formData.append("type", 'saveDataMM');
                formData.append("roadmapmajorid", $scope.temp.roadmapmajorid);
                formData.append("roadmapid", $scope.temp.roadmapid);
                formData.append("regid", $scope.temp.regid);
                formData.append("ddlType_MM", $scope.temp.ddlType_MM);
                // formData.append("ddlMajorMinor_MM", $scope.temp.ddlMajorMinor_MM);
                formData.append("txtMajor_MM", $scope.temp.txtMajor_MM);
                formData.append("txtRemarks_MM", $scope.temp.txtRemarks_MM);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRoadmapMajorMinor();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlType_MM").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-MM').removeAttr('disabled').text('ADD');
            $('.btn-update-MM').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */


    /* ========== GET ROADMAP MAJOR/MINOR =========== */
    $scope.getRoadmapMajorMinor = function () {
        $('#spinMM').show();
        $http({
                method: 'post',
                url: url,
            data: $.param({ 'type': 'getRoadmapMajorMinor',
                            'roadmapid':$scope.temp.roadmapid,
                            'regid':$scope.temp.regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRoadmapMajorMinor = data.data.success ? data.data.data : [];
                $('#spinMM').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRoadmapMajorMinor();
    /* ========== GET ROADMAP MAJOR/MINOR =========== */

    
    /* ============ Edit Button ============= */ 
    $scope.editFormMM = function (id) {
        $("#MajorMinorModal").modal('show');
        $("#ddlType_MM").focus();

        $scope.temp.roadmapmajorid = id.ROADMAPMAJORID;
        $scope.temp.ddlType_MM = (id.MTYPE && id.MTYPE!='') ? id.MTYPE : '';
        // $scope.temp.ddlMajorMinor_MM = (id.MAJORID && id.MAJORID>0) ? id.MAJORID.toString() : '';
        $scope.temp.txtMajor_MM = (id.MAJOR && id.MAJOR!='') ? id.MAJOR : '';
        $scope.temp.txtRemarks_MM = (id.REMARKS && id.REMARKS!='') ? id.REMARKS : '';
        $scope.index = $scope.post.getRoadmapMajorMinor.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormMM = function(){
        $("#ddlType_MM").focus();
        $scope.temp.roadmapmajorid = '';
        $scope.temp.ddlType_MM = '';
        $scope.temp.txtMajor_MM = '';
        $scope.temp.txtRemarks_MM = '';
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteMM = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ROADMAPMAJORID': id.ROADMAPMAJORID, 'type': 'deleteMM' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRoadmapMajorMinor.indexOf(id);
		            $scope.post.getRoadmapMajorMinor.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormMM();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MAJOR/MINOR END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */     












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE COLLEGES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveDataCollege = function(){
        $(".btn-save-College").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-College").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();    
                formData.append("type", 'saveDataCollege');
                formData.append("roadmapclid", $scope.temp.roadmapclid);
                formData.append("roadmapid", $scope.temp.roadmapid);
                formData.append("regid", $scope.temp.regid);
                formData.append("ddlUniversity_College", $scope.temp.ddlUniversity_College);
                formData.append("ddlCollege_College", $scope.temp.ddlCollege_College);
                formData.append("ddlInState_College", $scope.temp.ddlInState_College);
                formData.append("ddlCollegeType_College", $scope.temp.ddlCollegeType_College);
                formData.append("txtRemarks_College", $scope.temp.txtRemarks_College);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRoadmapColleges();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlUniversity_College").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-College').removeAttr('disabled').text('ADD');
            $('.btn-update-College').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */


    /* ========== GET ROADMAP COLLEGES =========== */
    $scope.getRoadmapColleges = function () {
        $('#spinCollege').show();
        $http({
                method: 'post',
                url: url,
            data: $.param({ 'type': 'getRoadmapColleges',
                            'roadmapid':$scope.temp.roadmapid,
                            'regid':$scope.temp.regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRoadmapColleges = data.data.success ? data.data.data : [];
                $('#spinCollege').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRoadmapColleges();
    /* ========== GET ROADMAP COLLEGES =========== */

    
    /* ============ Edit Button ============= */ 
    $scope.editFormCollege = function (id) {
        $("#CollegeModal").modal('show');
        // $scope.getUniversity();
        $("#ddlUniversity_College").focus();

        $scope.temp.roadmapclid = id.ROADMAPCLID;
        $scope.temp.ddlUniversity_College = id.UNIVERSITYID.toString();
        if($scope.temp.ddlUniversity_College > 0 && id.UNIVERSITYID>0){
            $scope.getCollegeByUniversity();
            $timeout(()=>{$scope.temp.ddlCollege_College=(id.CLID && id.CLID>0)?id.CLID.toString():'';},1000);
        }
        $scope.temp.ddlInState_College = (id.IN_STATE && id.IN_STATE!='') ? id.IN_STATE : '';
        $scope.temp.ddlCollegeType_College = (id.COLTYPE && id.COLTYPE!='') ? id.COLTYPE : '';
        $scope.temp.txtRemarks_College = (id.REMARKS && id.REMARKS!='') ? id.REMARKS : '';
        $scope.index = $scope.post.getRoadmapColleges.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormCollege = function(){
        $("#ddlUniversity_College").focus();
        $scope.temp.roadmapclid = '';
        $scope.temp.ddlUniversity_College = '';
        $scope.temp.ddlCollege_College = '';
        $scope.temp.ddlInState_College = '';
        $scope.temp.ddlCollegeType_College = '';
        $scope.temp.txtRemarks_College = '';
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteCollege = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ROADMAPCLID': id.ROADMAPCLID, 'type': 'deleteCollege' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRoadmapColleges.indexOf(id);
		            $scope.post.getRoadmapColleges.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormCollege();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE COLLEGES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE RECOMMENDATION START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
$scope.temp.txtRecDT_Rec=new Date();
$scope.REC_UPD = false; 
$scope.ForSecID = 0;
$scope.ForSec = '';

/* ============ GET ROADMAP SECTION DATA ============= */ 
$scope.recommendation=function(id,data,FOR){
    $scope.temp.txtRecommendation_Rec = '';
    $scope.temp.txtRecDT_Rec = new Date();
    $scope.ForSecID = id; 
    $scope.ForSec = FOR; 

    if(data.RECOMMENDATION && data.RECOMMENDATION!=''){
        $scope.temp.txtRecommendation_Rec = (data.RECOMMENDATION && data.RECOMMENDATION!='') ? data.RECOMMENDATION : ''; 
        $scope.temp.txtRecDT_Rec = (data.RECOMMENDDATE && data.RECOMMENDDATE!='') ? new Date(data.RECOMMENDDATE) : '';
        $scope.REC_UPD = true; 
    }else{
        $scope.REC_UPD = false; 
    }
    // console.log(id);
    // console.log(data);
    // console.log(FOR);
}
/* ============ GET ROADMAP SECTION DATA ============= */ 



/* ============ SAVE DATA ============= */ 
$scope.saveDataRec = function(){
    if($scope.ForSecID <= 0 || $scope.ForSec == '') return;
    $(".btn-save-Rec").attr('disabled', 'disabled').text('Submit...');
    $(".btn-update-Rec").attr('disabled', 'disabled').text('Updating...');
    $http({
        method: 'POST',
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();    
            formData.append("type", 'saveDataRec');
            formData.append("ForSecID", $scope.ForSecID);
            formData.append("ForSec", $scope.ForSec);
            formData.append("roadmapid", $scope.temp.roadmapid);
            formData.append("regid", $scope.temp.regid);
            formData.append("txtRecDT_Rec", $scope.temp.txtRecDT_Rec.toLocaleString('sv-SE'));
            formData.append("txtRecommendation_Rec", $scope.temp.txtRecommendation_Rec);
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }        
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {

            // $scope.temp.txtRecDT_Rec=new Date();
            // $scope.temp.txtRecommendation_Rec='';

            if($scope.ForSec === 'Classes') $scope.getRoadmapClasses();
            if($scope.ForSec === 'Activity') $scope.getRoadmapActivities();
            if($scope.ForSec === 'Tests') $scope.getRoadmapTests();
            if($scope.ForSec === 'MajorMinor') $scope.getRoadmapMajorMinor();
            if($scope.ForSec === 'College') $scope.getRoadmapColleges();
            // $scope.clearForm();
            $scope.messageSuccess(data.data.message);
            
        }
        else {
            $scope.messageFailure(data.data.message);
            // console.log(data.data)
        }
        $('.btn-save-Rec').removeAttr('disabled').text('SUBMIT');
        $('.btn-update-Rec').removeAttr('disabled').text('UPDATE');
    });
}
/* ============ SAVE DATA ============= */



/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE RECOMMENDATION END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */     















/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */
$scope.ACTIVITY_model=[]

$scope.BackOff = false;

/* ========== SET STUDENT DETAILS =========== */
$scope.DETAILS = [];
$scope.setStudentDetails = () => {
    $scope.DETAILS = [];
    $scope.temp.txtFirstName = '';
    $scope.temp.txtLastName = '';
    $scope.temp.txtPhone = '';
    $scope.temp.txtEmail = '';
    $scope.temp.txtP1FName = '';
    $scope.temp.txtP1LName = '';
    $scope.temp.txtP2FName = '';
    $scope.temp.txtP2LName = '';
    $scope.temp.txtP1Phone = '';
    $scope.temp.txtP2Phone = '';
    $scope.temp.txtP1Email = '';
    $scope.temp.txtP2Email = '';
    
    if($scope.temp.ddlStudentType != 'Registered'){
        $scope.temp.ddlLocation = '';
        $scope.temp.ddlStudent = '';
    }

    if(!$scope.temp.ddlStudent || $scope.temp.ddlStudent <= 0) return
    $scope.DETAILS = $scope.post.getStudentByLoc.filter((x)=> x.REGID === Number($scope.temp.ddlStudent)); 
    // console.log($scope.DETAILS);

    $scope.temp.txtFirstName = `${($scope.DETAILS[0]['FIRSTNAME'] && $scope.DETAILS[0]['FIRSTNAME']!='' && $scope.DETAILS[0]['FIRSTNAME']!='null') ? $scope.DETAILS[0]['FIRSTNAME'] : ''}`;
    $scope.temp.txtLastName = `${($scope.DETAILS[0]['LASTNAME'] && $scope.DETAILS[0]['LASTNAME']!='' && $scope.DETAILS[0]['LASTNAME']!='null') ? $scope.DETAILS[0]['LASTNAME']:''}`;
    $scope.temp.txtPhone = `${($scope.DETAILS[0]['PHONE'] && $scope.DETAILS[0]['PHONE']!='' && $scope.DETAILS[0]['PHONE']!='null') ? $scope.DETAILS[0]['PHONE'] : ''}`;
    $scope.temp.txtEmail = `${($scope.DETAILS[0]['EMAIL'] && $scope.DETAILS[0]['EMAIL']!='' && $scope.DETAILS[0]['EMAIL']!='null') ? $scope.DETAILS[0]['EMAIL']:''}`;

    $scope.temp.txtP1FName = `${$scope.DETAILS[0]['P1_FIRSTNAME']}`;
    $scope.temp.txtP1LName = `${$scope.DETAILS[0]['P1_LASTNAME']}`;
    $scope.temp.txtP1Phone = `${$scope.DETAILS[0]['P1_PHONE']}`;
    $scope.temp.txtP1Email = `${$scope.DETAILS[0]['P1_EMAIL']}`;
    
    $scope.temp.txtP2FName = `${$scope.DETAILS[0]['P2_FIRSTNAME']}`;
    $scope.temp.txtP2LName = `${$scope.DETAILS[0]['P2_LASTNAME']}`;
    $scope.temp.txtP2Phone = `${$scope.DETAILS[0]['P2_PHONE']}`;
    $scope.temp.txtP2Email = `${$scope.DETAILS[0]['P2_EMAIL']}`;
    
}
/* ========== SET STUDENT DETAILS =========== */



/* ========== GET Location =========== */
// $scope.getLocations = function () {
//     $scope.post.getStudentByLoc = [];
//     $scope.post.getLocReviewByLoc = [];
//     $('.spinLoc').show();
//     $http({
//         method: 'post',
//         url: '../backoffice/code/Locations_code.php',
//         data: $.param({ 'type': 'getLocations'}),
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
//     }).
//     then(function (data, status, headers, config) {
//         console.log(data.data);
//         $scope.post.getLocations = data.data.data;
//         $('.spinLoc').hide();
//     },
//     function (data, status, headers, config) {
//         console.log('Failed');
//     })
// }
// // $scope.getLocations(); --INIT
/* ========== GET Location =========== */




/* ========== GET STUDENT BY LOCATION =========== */
// $scope.getStudentByLoc = function () {
//     $('.spinUser').show();
//     $http({
//         method: 'post',
//         url: url,
//         data: $.param({ 'type': 'getStudentByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
//     }).
//     then(function (data, status, headers, config) {
//         // console.log(data.data);
//         if(data.data.success){
//             $scope.post.getStudentByLoc = data.data.data;
//             if($scope.editMode)$timeout(()=>{if($scope.temp.ddlStudent>0)$scope.setStudentDetails()},500);
//         }else{
//             $scope.post.getStudentByLoc = [];
//         }
//         $('.spinUser').hide();
//     },
//     function (data, status, headers, config) {
//         console.log('Failed');
//     })
// }
// // $scope.getStudentByLoc();
/* ========== GET STUDENT BY LOCATION =========== */



/* ========== GET GRADES =========== */
$scope.getGrades = function () {
    $('.spinGrade').show();
    $http({
        method: 'post',
        url: '../backoffice/code/Grades_Master.php',
        data: $.param({ 'type': 'getGrades'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
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
// $scope.getGrades(); --INIT
/* ========== GET GRADES =========== */



/* ========== GET ADM YEARS =========== */
$scope.getAdmYears = function () {
    $('.spinAdmYaer').show();
    $http({
        method: 'post',
        url: '../backoffice/code/Admission_Year_Master.php',
        data: $.param({ 'type': 'getAdmYears'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getAdmYears = data.data.success ? data.data.data : [];
        $('.spinAdmYaer').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getAdmYears(); --INIT
/* ========== GET ADM YEARS =========== */





/* ========== GET COUNTRIES =========== */
$scope.getCountries = function () {
    $('.spinCountries').show();
    $http({
        method: 'post',
        url: '../backoffice/code/Countries_code.php',
        data: $.param({ 'type': 'getCountries'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCountry = data.data.success ? data.data.data : [];
        $('.spinCountries').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getCountries(); --INIT
/* ========== GET COUNTRIES =========== */




/* ========== GET STATE =========== */
$scope.getStates = function () {
$('.spinState').show();
$http({
    method: 'post',
    url: '../backoffice/code/Geolocation_code.php',
    data: $.param({ 'type': 'getStates','ddlCountry':$scope.temp.ddlCountry}),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
}).
then(function (data, status, headers, config) {
    // console.log(data.data);
    $scope.post.getStates = data.data.success ? data.data.data : [];
    $('.spinState').hide();
},
function (data, status, headers, config) {
    console.log('Failed');
})
}
// $scope.getStates();
/* ========== GET STATE =========== */




/* ========== GET CITY =========== */
$scope.getCities = function () {
    $('.spinCity').show();
    $http({
        method: 'post',
        url: '../backoffice/code/Geolocation_code.php',
        data: $.param({ 'type': 'getCities','ddlState':$scope.temp.ddlState}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCities = data.data.success ? data.data.data : [];
        $('.spinCity').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
/* ========== GET CITY =========== */





/* ========== GET CLASS/SUBJECT =========== */
$scope.getClassSubject = function () {
    if(!$scope.post.getClassSubject || $scope.post.getClassSubject.length<=0){
        $('#spinClassSubject').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Class_Subject_Master.php',
            data: $.param({ 'type': 'getClassSubject'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getClassSubject = data.data.success ? data.data.data : [];
            $('#spinClassSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
}
// $scope.getClassSubject();
/* ========== GET CLASS/SUBJECT =========== */





/* ========== GET ACTIVITY LEGEND =========== */
$scope.getActivityLegend = function () {
    if(!$scope.post.getActivityLegend || $scope.post.getActivityLegend.length<=0){
        $('.spinActivityLegend').show();
        $http({
            method: 'post',
            url: url,
           data: $.param({ 'type': 'getActivityLegend'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getActivityLegend = data.data.success ? data.data.data : [];
            $('.spinActivityLegend').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
    }
}
// $scope.getActivityLegend();
/* ========== GET ACTIVITY LEGEND =========== */





/* ========== GET ACTIVITIES =========== */
$scope.getActivities = function () {
    if(!$scope.post.getActivities || $scope.post.getActivities.length<=0){
        $('.spinActivity').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Activities_Master.php',
           data: $.param({ 'type': 'getActivities'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getActivities = data.data.success ? data.data.data : [];
            $('.spinActivity').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
    }
}
// $scope.getActivities();
/* ========== GET ACTIVITIES =========== */




/* ========== GET COLLEGE MAJOR =========== */
$scope.getCollegeMajor = function () {
    if(!$scope.post.getCollegeMajor || $scope.post.getCollegeMajor.length<=0){
        $('.spinCollegeMajor').show();
        $http({
            method: 'post',
            url: '../backoffice/code/College_Major_Master_code.php',
            data: $.param({ 'type': 'getCollegeMajor'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCollegeMajor = data.data.success ? data.data.data : [];
            $('.spinCollegeMajor').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
}
// $scope.getCollegeMajor();
/* ========== GET COLLEGE MAJOR =========== */

   


/* ========== GET UNIVERSITY =========== */
$scope.getUniversity = function () {
    if(!$scope.post.getUniversity || $scope.post.getUniversity.length<=0){
        $('.spinUniversity').show();
        $http({
            method: 'post',
            url: '../backoffice/code/University_Master_code.php',
            data: $.param({ 'type': 'getUniversity'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getUniversity = data.data.success ? data.data.data : [];
            $('.spinUniversity').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
}
// $scope.getUniversity();
/* ========== GET UNIVERSITY =========== */


/* ========== GET COLLEGES =========== */
$scope.getCollegeByUniversity = function () {
    $('.spinCollege').show();
     $http({
         method: 'post',
        url: '../backoffice/code/Student_Final_Result_code.php',
        data: $.param({ 'type': 'getCollegeByUniversity','UNIVERSITYID':$scope.temp.ddlUniversity_College}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCollegeByUniversity = data.data.success ? data.data.data : [];
        $('.spinCollege').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getCollegeByUniversity();
/* ========== GET COLLEGES =========== */





/* ######################################################################################################################### */
/*                                           GET EXTRA DATA END                                                              */
/* ######################################################################################################################### */    





    // GET DATA
    $scope.RID = 0;
    $scope.RMAPID = 0;
    $scope.init = function () {

        // $scope.getLocations();
        $scope.getGrades();
        $scope.getAdmYears();
        $scope.getCountries();
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
                $scope.UNAME = `${data.data.userFName} ${data.data.userLName}`;
                
                // $scope.post.user = data.data.data;
                $scope.USER_LOCATION=data.data.LOCATION;

                $scope.RID = data.data.RID;
                $scope.RMAPID = data.data.RMAPID;
                if($scope.RID > 0 && $scope.RMAPID > 0){
                    $scope.temp.roadmapid = $scope.RMAPID;
                    $scope.temp.regid = $scope.RID;
                    $scope.getStudentCollegeRoadmap($scope.temp.roadmapid,$scope.temp.regid);
                }else{
                    $scope.logout();
                }
            }
            else {
                
            }
        },
        function (data, status, headers, config) {
            //console.log(data)
            console.log('Failed');
        })

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
                    window.location.assign('College_Counselling_Roadmap.html#!/login')
                }
                else {
                    //window.location.assign('backoffice/index#!/')
                }
            },
            function (data, status, headers, config) {
                console.log('Not login Failed');
            })
    }


    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-success').show();
        });
        jQuery('.alert-success').delay(5000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-danger').show();
        });
        jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});