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

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$window) {
    $scope.post = {};
    $scope.temp = {};
    $scope.parent = {};
    $scope.gradeClass = {};
    $scope.personal = {};
    $scope.classAtSch = {};
    $scope.editMode = false;
    $scope.Page ='HOME';
    $scope.files = [];

    // $scope.temp.UP=[];
    // $scope.temp.DOWN=[];
    
    var url = 'code/dashboard_code.php';

    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.logo_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview =========*/ 


    $scope.LinkVoting=function(VOTE,RESLID,val){
        // alert(val);

        // console.log(`${VOTE} ${RESLID} ${val}`);
        $scope.voteVal = val;
        if($scope.voteVal != undefined){
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'LinkVoting');
                    formData.append("RESLID", RESLID);
                    formData.append("VOTE", VOTE);
                    formData.append("voteVal",$scope.voteVal);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
            
                    $scope.messageSuccess_old(data.data.message);
                    
                }
                else {
                    console.log('VOTING FAIL');
                    $scope.messageFailure_old(data.data.message);
                    // console.log(data.data)
                }
            });
        }
        else{
            console.log('voteVal Error.');
        }
    }
    
    
    $scope.openLearnAssisPage = function(){
        $window.open('LearnAssis.html', '_blank');
    }
    $scope.openTestProgress = function(){
        $window.open('TestProgress.html', '_blank');
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
                $scope.USER_LOCATION=data.data.LOCATION;
                $scope.REGID=data.data.data[0]['REGID'];
                $scope.PLAN=data.data.data[0]['PLAN'];
                $scope.GRADE=data.data.data[0]['GRADE'];
                $scope.LOCID=data.data.data[0]['LOCATIONID'];
                $scope.PLANID=data.data.data[0]['PLANID'];
                $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];

                //======== FILL REFERRAL FIELDS
                // $scope.temp.txtFirstNameRef = data.data.data[0]['FIRSTNAME'];
                // $scope.temp.txtLastNameRef = data.data.data[0]['LASTNAME'];
    
                // $scope.getStudentPlanData();
                // if($scope.userid > 0)$scope.getUserDetails();
                
                $scope.getUserDetailsForUpdate();
                $scope.getGrades();
                $scope.getClassSubject();

                if($scope.LOCID > 0)$scope.getAttendance();
                $scope.getReferrals();
                $scope.getSms()
                $scope.getMeetingLinks()
                if($scope.LOCID > 0)$scope.getLocationReview();
                $scope.getResources();
                if($scope.LOCID > 0)$scope.getAnnouncement();
                $scope.getSupport();
                if($scope.LOCID > 0)$scope.getReferralMaster();
                
                $scope.ActivePlan = data.data.ActivePlan;
                // alert(data.data.ActivePlan);
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

    // #############################################################################################
    // ###### UPDATE DETAILS START
    // #############################################################################################
    $scope.isValidEmail = function(email) {
        // Regular expression for basic email validation
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    };
    $scope.containsTBD = function(str) {
        str = str.toLowerCase();
        return str.includes('tbd');
    }
    /* ========== GET Student Details =========== */
    $scope.getUserDetailsForUpdate = function () {
        // if($scope.REGID!=1)return;
        $scope.spinUserDet = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getUserDetailsForUpdate','REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.Parent1 = data.data.data_P1;
            $scope.PARENT1_EXIST = data.data.success_P1;
            $scope.post.Parent2 = data.data.data_P2;
            $scope.PARENT2_EXIST = data.data.success_P2;
            $scope.post.GradeClassof = data.data.data_GC;
            $scope.GRADE_CLASSOF_EXIST = data.data.success_GC;
            $scope.post.PersonalDet = data.data.data_P;
            $scope.PERSONAL_DET_EXIST = data.data.success_P;
            $scope.post.ClassAtSchool = data.data.data_CAS;
            $scope.CLASSATSCHOOL_EXIST = data.data.success_CAS;
            // $scope.CLASSATSCHOOL_EXIST = (!data.data.data_CAS || data.data.data_CAS.length<=0) ? true : data.data.success_CAS;
            // alert($scope.CLASSATSCHOOL_EXIST);

            $scope.ANY_DETAIL_EXIST = ($scope.PARENT1_EXIST || $scope.PARENT2_EXIST || $scope.GRADE_CLASSOF_EXIST || $scope.PERSONAL_DET_EXIST || $scope.CLASSATSCHOOL_EXIST) ? true : false;

            if(data.data.success_P1 || data.data.success_P2 || data.data.success_GC || data.data.success_P || data.data.success_CAS) $('#userInfoModal').modal('show');

            // PARENT 1
            if($scope.post.Parent1 && Object.keys($scope.post.Parent1).length){
                var P1_FIRSTNAME = $scope.post.Parent1['P1_FIRSTNAME'];
                var P1_LASTNAME = $scope.post.Parent1['P1_LASTNAME'];
                var P1_EMAIL = $scope.post.Parent1['P1_EMAIL'];
                var P1_PHONE = $scope.post.Parent1['P1_PHONE'];
                $scope.parent.txtFirstNameP1 = (!P1_FIRSTNAME || P1_FIRSTNAME=='') ? '' : P1_FIRSTNAME;
                if(P1_FIRSTNAME && P1_FIRSTNAME!='' && P1_FIRSTNAME.length>2 && !$scope.containsTBD(P1_FIRSTNAME))  $('#txtFirstNameP1').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');

                $scope.parent.txtLastNameP1 = (!P1_LASTNAME || P1_LASTNAME=='') ? '' : P1_LASTNAME;
                if(P1_LASTNAME && P1_LASTNAME!='' && P1_LASTNAME.length>2 && !$scope.containsTBD(P1_LASTNAME))  $('#txtLastNameP1').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');

                $scope.parent.txtPhoneNumberP1 = (!P1_PHONE || P1_PHONE=='') ? '' : P1_PHONE;
                if(P1_PHONE && P1_PHONE!='' && isNaN(P1_PHONE)==false && P1_PHONE.length>=10 && P1_PHONE.length<=12 && !$scope.containsTBD(P1_PHONE))  $('#txtPhoneNumberP1').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');
                
                $scope.parent.txtEmailIdP1 = (!P1_EMAIL || P1_EMAIL=='') ? '' : P1_EMAIL;
                if(P1_EMAIL && P1_EMAIL!='' && $scope.isValidEmail(P1_EMAIL)==true && !$scope.containsTBD(P1_EMAIL))  $('#txtEmailIdP1').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');
            }
            // PARENT 2
            if($scope.post.Parent2 && Object.keys($scope.post.Parent2).length){
                var P2_FIRSTNAME = $scope.post.Parent2['P2_FIRSTNAME'];
                var P2_LASTNAME = $scope.post.Parent2['P2_LASTNAME'];
                var P2_EMAIL = $scope.post.Parent2['P2_EMAIL'];
                var P2_PHONE = $scope.post.Parent2['P2_PHONE'];
                $scope.parent.txtFirstNameP2 = (!P2_FIRSTNAME || P2_FIRSTNAME=='') ? '' : P2_FIRSTNAME;
                if(P2_FIRSTNAME && P2_FIRSTNAME!='' && P2_FIRSTNAME.length>2 && !$scope.containsTBD(P2_FIRSTNAME))  $('#txtFirstNameP2').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');

                $scope.parent.txtLastNameP2 = (!P2_LASTNAME || P2_LASTNAME=='') ? '' : P2_LASTNAME;
                if(P2_LASTNAME && P2_LASTNAME!='' && P2_LASTNAME.length>2 && !$scope.containsTBD(P2_LASTNAME))  $('#txtLastNameP2').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');

                $scope.parent.txtPhoneNumberP2 = (!P2_PHONE || P2_PHONE=='') ? '' : P2_PHONE;
                if(P2_PHONE && P2_PHONE!='' && isNaN(P2_PHONE)==false && P2_PHONE.length>=10 && P2_PHONE.length<=12 && !$scope.containsTBD(P2_PHONE))  $('#txtPhoneNumberP2').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');
                
                $scope.parent.txtEmailIdP2 = (!P2_EMAIL || P2_EMAIL=='') ? '' : P2_EMAIL;
                if(P2_EMAIL && P2_EMAIL!='' && $scope.isValidEmail(P2_EMAIL)==true && !$scope.containsTBD(P2_EMAIL))  $('#txtEmailIdP2').attr('disabled',true).toggleClass('form-control bg-light form-control-plaintext');
            }
            // GRADE OR CLASSOFF
            if($scope.post.GradeClassof && Object.keys($scope.post.GradeClassof).length){
                var GRADE = $scope.post.GradeClassof['GRADE'];
                var CLASSOF = $scope.post.GradeClassof['CLASSOF'];

                $scope.gradeClass.txtGrade = (!GRADE || GRADE=='') ? '' : GRADE;
                $scope.gradeClass.txtGrade_old = (!GRADE || GRADE=='') ? '' : GRADE;
                $scope.gradeClass.txtClassof = (!CLASSOF || CLASSOF==0) ? '' : Number(CLASSOF);
                $scope.gradeClass.txtClassof_old = (!CLASSOF || CLASSOF==0) ? '' : Number(CLASSOF);
            }
            // PERSONAL DET
            if($scope.post.PersonalDet && Object.keys($scope.post.PersonalDet).length){
                var PHONE = $scope.post.PersonalDet['PHONE'];
                var EMAIL = $scope.post.PersonalDet['EMAIL'];
                var SCHOOL = $scope.post.PersonalDet['SCHOOL'];

                $scope.personal.txtPhoneNumberPD = (!PHONE || PHONE=='') ? '' : PHONE;
                $scope.personal.txtEmailIdPD = (!EMAIL || EMAIL=='') ? '' : EMAIL;
                $scope.personal.txtSchoolPD = (!SCHOOL || SCHOOL=='') ? '' : SCHOOL;
            }
            // CLASSES AT SCHOOL
            if($scope.post.ClassAtSchool && Object.keys($scope.post.ClassAtSchool).length){
                $scope.selectedCourse = angular.copy($scope.post.ClassAtSchool);
            }else{
                $scope.selectedCourse = [];
            }

            $scope.spinUserDet = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }


    /* ========== UPDATE PARENT DETAILS =========== */
    $scope.updateParent = function(){
        $(".btnP2").attr('disabled', true).text('Update...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateParent');
                formData.append("txtPhoneNumberPD", $scope.personal.txtPhoneNumberPD);
                formData.append("txtPhoneNumberPD_old", $scope.personal.txtPhoneNumberPD_old);
                formData.append("txtEmailIdPD", $scope.personal.txtEmailIdPD);
                formData.append("txtEmailIdPD_old", $scope.personal.txtEmailIdPD_old);
                formData.append("txtSchoolPD", $scope.personal.txtSchoolPD);
                formData.append("txtSchoolPD_old", $scope.personal.txtSchoolPD_old);

                formData.append("txtFirstNameP1", $scope.parent.txtFirstNameP1);
                formData.append("txtLastNameP1", $scope.parent.txtLastNameP1);
                formData.append("txtPhoneNumberP1", $scope.parent.txtPhoneNumberP1);
                formData.append("txtEmailIdP1", $scope.parent.txtEmailIdP1);
                formData.append("txtFirstNameP2", $scope.parent.txtFirstNameP2);
                formData.append("txtLastNameP2", $scope.parent.txtLastNameP2);
                formData.append("txtPhoneNumberP2", $scope.parent.txtPhoneNumberP2);
                formData.append("txtEmailIdP2", $scope.parent.txtEmailIdP2);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.parent = {};
                $scope.personal = {};
                $scope.PERSONAL_DET_EXIST = false;
                $scope.PARENT1_EXIST = false;
                $scope.PARENT2_EXIST = false;
                $scope.ANY_DETAIL_EXIST = ($scope.PARENT1_EXIST || $scope.PARENT2_EXIST || $scope.GRADE_CLASSOF_EXIST || $scope.PERSONAL_DET_EXIST || $scope.CLASSATSCHOOL_EXIST) ? true : false;
                if(!$scope.ANY_DETAIL_EXIST) $('#userInfoModal').modal('hide');
                // $scope.clearForm();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btnP2").attr('disabled', false).text('UPDATE');
        });                                
    }

    /* ========== UPDATE GRADE & CLASSOF DETAILS =========== */
    $scope.updateGradeClassof = function(){
        $(".btnGC").attr('disabled', true).text('Update...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateGradeClassof');
                formData.append("txtGrade", $scope.gradeClass.txtGrade);
                formData.append("txtGrade_old", $scope.gradeClass.txtGrade_old);
                formData.append("txtClassof", $scope.gradeClass.txtClassof);
                formData.append("txtClassof_old", $scope.gradeClass.txtClassof_old);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.gradeClass = {};
                $scope.GRADE_CLASSOF_EXIST = false;
                $scope.ANY_DETAIL_EXIST = ($scope.PARENT1_EXIST || $scope.PARENT2_EXIST || $scope.GRADE_CLASSOF_EXIST || $scope.PERSONAL_DET_EXIST || $scope.CLASSATSCHOOL_EXIST) ? true : false;
                if(!$scope.ANY_DETAIL_EXIST) $('#userInfoModal').modal('hide');
                // $scope.clearForm();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btnGC").attr('disabled', false).text('UPDATE');
        });                                
    }

    /* ========== UPDATE PERSONAL DETAILS =========== */
    // $scope.updatePersonalDet = function(){
    //     $(".btnPD").attr('disabled', true).text('Update...');
    //     $http({
    //         method: 'POST',
    //         url: url,
    //         processData: false,
    //         transformRequest: function (data) {
    //             var formData = new FormData();
    //             formData.append("type", 'updatePersonalDet');
    //             formData.append("txtPhoneNumberPD", $scope.personal.txtPhoneNumberPD);
    //             formData.append("txtPhoneNumberPD_old", $scope.personal.txtPhoneNumberPD_old);
    //             formData.append("txtEmailIdPD", $scope.personal.txtEmailIdPD);
    //             formData.append("txtEmailIdPD_old", $scope.personal.txtEmailIdPD_old);
    //             formData.append("txtSchoolPD", $scope.personal.txtSchoolPD);
    //             formData.append("txtSchoolPD_old", $scope.personal.txtSchoolPD_old);
    //             return formData;
    //         },
    //         data: $scope.temp,
    //         headers: { 'Content-Type': undefined }
    //     }).
    //     then(function (data, status, headers, config) {
    //         console.log(data.data);
    //         if (data.data.success) {
    //             $scope.messageSuccess(data.data.message);
    //             $scope.personal = {};
    //             $scope.PERSONAL_DET_EXIST = false;
    //             $scope.ANY_DETAIL_EXIST = ($scope.PARENT1_EXIST || $scope.PARENT2_EXIST || $scope.GRADE_CLASSOF_EXIST || $scope.PERSONAL_DET_EXIST || $scope.CLASSATSCHOOL_EXIST) ? true : false;
    //             if(!$scope.ANY_DETAIL_EXIST) $('#userInfoModal').modal('hide');
    //             // $scope.clearForm();
    //         }
    //         else {
    //             $scope.messageFailure(data.data.message);
    //             // console.log(data.data)
    //         }
    //         $(".btnPD").attr('disabled', false).text('UPDATE');
    //     });                                
    // }

    /* ========== UPDATE PERSONAL DETAILS =========== */
    // $scope.updatePersonalDet = function(){
    //     $(".btnPD").attr('disabled', true).text('Update...');
    //     $http({
    //         method: 'POST',
    //         url: url,
    //         processData: false,
    //         transformRequest: function (data) {
    //             var formData = new FormData();
    //             formData.append("type", 'updatePersonalDet');
    //             formData.append("txtPhoneNumberPD", $scope.personal.txtPhoneNumberPD);
    //             formData.append("txtPhoneNumberPD_old", $scope.personal.txtPhoneNumberPD_old);
    //             formData.append("txtEmailIdPD", $scope.personal.txtEmailIdPD);
    //             formData.append("txtEmailIdPD_old", $scope.personal.txtEmailIdPD_old);
    //             formData.append("txtSchoolPD", $scope.personal.txtSchoolPD);
    //             formData.append("txtSchoolPD_old", $scope.personal.txtSchoolPD_old);
    //             return formData;
    //         },
    //         data: $scope.temp,
    //         headers: { 'Content-Type': undefined }
    //     }).
    //     then(function (data, status, headers, config) {
    //         console.log(data.data);
    //         if (data.data.success) {
    //             $scope.messageSuccess(data.data.message);
    //             $scope.personal = {};
    //             $scope.PERSONAL_DET_EXIST = false;
    //             $scope.ANY_DETAIL_EXIST = ($scope.PARENT1_EXIST || $scope.PARENT2_EXIST || $scope.GRADE_CLASSOF_EXIST || $scope.PERSONAL_DET_EXIST || $scope.CLASSATSCHOOL_EXIST) ? true : false;
    //             if(!$scope.ANY_DETAIL_EXIST) $('#userInfoModal').modal('hide');
    //             // $scope.clearForm();
    //         }
    //         else {
    //             $scope.messageFailure(data.data.message);
    //             // console.log(data.data)
    //         }
    //         $(".btnPD").attr('disabled', false).text('UPDATE');
    //     });                                
    // }

    $scope.selectedCourse = [];
    /* ========== SELECT COURSES =========== */
    $scope.addStudentProCourses = function(FOR,id){
        if(FOR=='ADD'){
            var item = {
                        'GRADEID':$scope.classAtSch.ddlGrade,
                        'GRADE':$('#ddlGrade').find(":selected").text(),
                        'CSUBID':$scope.classAtSch.ddlClassSubject,
                        'SUBJECT':$('#ddlClassSubject').find(":selected").text(),
                        'FINAL_DRAFT':$scope.classAtSch.ddlFinalDraft
                        };
    
            $scope.selectedCourse.findIndex(x => x.GRADEID == item.GRADEID && x.CSUBID==item.CSUBID && x.FINAL_DRAFT==item.FINAL_DRAFT) == -1 ? $scope.selectedCourse.push(item) : $scope.messageFailure("Course already added.");
            $scope.classAtSch.ddlGrade = '';
            $scope.classAtSch.ddlClassSubject = '';
            $scope.classAtSch.ddlFinalDraft = '';
        }else{
            var idx = $scope.selectedCourse.indexOf(id);
		    $scope.selectedCourse.splice(idx, 1);
        }
        // console.log($scope.selectedCourse);
    }

    /* ========== UPDATE CLASSES AT SCHOOL =========== */
    $scope.updateClassesAtSchool = function(){
        $(".btnCAS").attr('disabled', true).text('Update...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateClassesAtSchool');
                formData.append("selectedCourse", JSON.stringify($scope.selectedCourse));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.classAtSch = {};
                $scope.selectedCourse = [];
                $scope.CLASSATSCHOOL_EXIST = false;
                $scope.ANY_DETAIL_EXIST = ($scope.PARENT1_EXIST || $scope.PARENT2_EXIST || $scope.GRADE_CLASSOF_EXIST || $scope.PERSONAL_DET_EXIST || $scope.CLASSATSCHOOL_EXIST) ? true : false;
                if(!$scope.ANY_DETAIL_EXIST) $('#userInfoModal').modal('hide');
                // $scope.clearForm();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btnCAS").attr('disabled', false).text('UPDATE');
        });                                
    }

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



    /* ========== GET CLASS/SUBJECT =========== */
    $scope.getClassSubject = function () {
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
    // $scope.getClassSubject();
    /* ========== GET CLASS/SUBJECT =========== */


    // #############################################################################################
    // ###### UPDATE DETAILS END
    // #############################################################################################


    /* ========== GET Student Plan Data =========== */
    $scope.getStudentPlanData = function () {
        $('#loader').removeClass('d-none');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentPlanData1',
                            'REGID':$scope.REGID,
                            'LOCID':$scope.LOCID
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.StudentPlanData = data.data.ALLDATA;
            }else{
                $scope.ChkNoPlan=data.data.PlanCount;
            }

            $('#loader').addClass('d-none');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentPlans();


    /* ========== GET Student Plans =========== */
    $scope.getStudentPlans = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentPlans',
                            'REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentPlans();
    


    /* ========== GET Attendance =========== */
    $scope.getAttendance = function () {
        $('.spinATT').show();
        $('#loader').show();
        
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAttendance',
                            'REGID':$scope.REGID,
                            'LOCID':$scope.LOCID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAttendance = data.data.success ? data.data.data : [];
            $('.spinATT').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAttendance();

    
    
    /* ========== GET SMS =========== */
    $scope.getSms = function () {
        $('.spinSms').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSms','REGID':$scope.REGID,'FROM':'DASH'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSms = data.data.success ? data.data.data : [];
            $('.spinSms').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSms(); --INIT
    /* ========== GET SMS =========== */


    
    
    /* ========== GET Meeting links =========== */
    $scope.getMeetingLinks = function () {
        $('.spinMeeting').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getMeetingLinks'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMeetingLinks = data.data.success ? data.data.data : [];
            $('.spinMeeting').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMeetingLinks(); --INIT
    
    
    
    /* ========== GET Location Reviews =========== */
    $scope.getLocationReview = function () {
        $('.spinReviewLinks').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLocationReview',
                            'LOCID':$scope.LOCID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocationReview = data.data.data;
            $('.spinReviewLinks').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocationReview(); --INIT
    
    
    
    
    /* ========== GET Resources =========== */
    $scope.getResources = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getResources'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getResources = data.data.success ? data.data.ALLDATA : [];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getResources(); --INIT


    
    /* =========== Get Announcement =========== */ 
    $scope.getAnnouncement = function () {
        $('.spinAnnounce').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAnnouncement','LOCID':$scope.LOCID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAnnouncement = data.data.success ? data.data.data : [];
            $('.spinAnnounce').hide();
            $('#loader').hide();
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // $scope.getAnnouncement(); --INIT


    
    /* =========== GET REFERRAL MASTER =========== */ 
    $scope.getReferralMaster = function () {
        $('.spinRef').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Referral_Master.php',
            data: $.param({ 'type': 'getReferralMaster','LOCID':$scope.LOCID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReferralMaster = data.data.success ? data.data.data : [];
            $('.spinRef').hide();
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // $scope.getReferralMaster(); --INIT


    /* =========== Get Support Ticket =========== */ 
    $scope.getSupport = function () {
        $('.spinSupport').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSupport','LOCID':$scope.LOCID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSupport = data.data.success ? data.data.data : [];
            $('.spinSupport').hide();
            $('#loader').hide();
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // $scope.getSupport(); --INIT


    /* =========== SAVE REFERRALS =========== */ 
    $scope.checkEmailPhone = false;
    $scope.saveReferral = function(){
        if((!$scope.temp.txtPhoneRef || $scope.temp.txtPhoneRef=='') && (!$scope.temp.txtEmailRef || $scope.temp.txtEmailRef=='') &&
            (!$scope.temp.txtP1PhoneRef || $scope.temp.txtP1PhoneRef=='') && (!$scope.temp.txtP1EmailRef || $scope.temp.txtP1EmailRef=='') &&
            (!$scope.temp.txtP2PhoneRef || $scope.temp.txtP2PhoneRef=='') && (!$scope.temp.txtP2EmailRef || $scope.temp.txtP2EmailRef=='')){
            $scope.checkEmailPhone = true;
            return;
        }else{
            $scope.checkEmailPhone = false;

            $(".btn-save").attr('disabled', 'disabled');
            $(".btn-save").text('Saving...');
            $(".btn-update").attr('disabled', 'disabled');
            $(".btn-update").text('Updating...');
            // alert($scope.temp.ddlCollege);
    
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'saveReferral');
                    formData.append("REFBYID", $scope.userid);
                    formData.append("REF_BY", "STUDENT");
                    formData.append("ddlReferralTypeRef", $scope.temp.ddlReferralTypeRef);
                    formData.append("txtRelationRef", $scope.temp.txtRelationRef);
                    formData.append("txtCourseRef", $scope.temp.txtCourseRef);
                    formData.append("txtFirstNameRef", $scope.temp.txtFirstNameRef);
                    formData.append("txtLastNameRef", $scope.temp.txtLastNameRef);
                    formData.append("txtPhoneRef", $scope.temp.txtPhoneRef);
                    formData.append("txtEmailRef", $scope.temp.txtEmailRef);
                    formData.append("txtP1FirstNameRef", $scope.temp.txtP1FirstNameRef);
                    formData.append("txtP1LastNameRef", $scope.temp.txtP1LastNameRef);
                    formData.append("txtP1PhoneRef", $scope.temp.txtP1PhoneRef);
                    formData.append("txtP1EmailRef", $scope.temp.txtP1EmailRef);
                    formData.append("txtP2FirstNameRef", $scope.temp.txtP2FirstNameRef);
                    formData.append("txtP2LastNameRef", $scope.temp.txtP2LastNameRef);
                    formData.append("txtP2PhoneRef", $scope.temp.txtP2PhoneRef);
                    formData.append("txtP2EmailRef", $scope.temp.txtP2EmailRef);
                    formData.append("ddlDiscloseRef", $scope.temp.ddlDiscloseRef);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                if (data.data.success) {
                    $scope.messageSuccess_old(data.data.message);
                    $('#referralModal').modal('hide');
                    $scope.clearReferralForm();
                    $scope.getReferrals();
                    // $scope.clearForm();
                }
                else {
                    $scope.messageFailure_old(data.data.message);
                    // console.log(data.data)
                }
                $('.btn-save').removeAttr('disabled');
                $(".btn-save").text('SAVE');
                $('.btn-update').removeAttr('disabled');
                $(".btn-update").text('UPDATE');
            });
        }
                                        
    }


    /* =========== CLEAR REFERRALS =========== */ 
    $scope.clearReferralForm=function(){
        $scope.temp.ddlReferralTypeRef='';
        $scope.temp.txtRelationRef='';
        $scope.temp.txtCourseRef='';
        $scope.temp.txtFirstNameRef='';
        $scope.temp.txtLastNameRef='';
        $scope.temp.txtPhoneRef='';
        $scope.temp.txtEmailRef='';
        $scope.temp.txtP1FirstNameRef='';
        $scope.temp.txtP1LastNameRef='';
        $scope.temp.txtP1PhoneRef='';
        $scope.temp.txtP1EmailRef='';
        $scope.temp.txtP2FirstNameRef='';
        $scope.temp.txtP2LastNameRef='';
        $scope.temp.txtP2PhoneRef='';
        $scope.temp.txtP2EmailRef='';
        $scope.temp.ddlDiscloseRef='';
        $scope.checkEmailPhone = false;
    }


    
    
    /* =========== GET REFERRALS =========== */ 
    $scope.getReferrals = function () {
        $('.spinReferrals').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReferrals','REF_BY':'STUDENT','REF_BYID':$scope.userid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReferrals = data.data.success ? data.data.data : [];
            $('.spinReferrals').hide();
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // $scope.getReferrals(); --INIT



    /* ========== OPEN HOMEWORK MODAL =========== */
    $scope.HOMEWORK_DIC_IS_IMG=$scope.DOC_IS_IMG = false;
    $scope.HW_DOC_PREVIEW=$scope.HW_DOC2_PREVIEW = '';
    $scope.openHomeWorkModal = function(id){
        $scope.clearHomeWork();
        // console.log(id);
        $scope.HEAD_HW = `(${id.CDATE}/${id.INVENTORY}/${id.CHAPTER})`;
        $scope.HOMEWORK = id.HOMEWORK;
        $scope.HOMEWORK_DONE = id.HOMEWORK_DONE;
        $scope.HOMEWORK_IMG = id.HOMEWORK_IMG;
        $scope.HOMEWORK_DOC = id.HOMEWORK_DOC;
        // $scope.DOC = id.DOC;
        // $scope.DOC_TYPE = id.DOC_TYPE;
        $scope.HOMEWORK_DOC_TYPE = id.HOMEWORK_DOC_TYPE;
        $scope.SCCID_HW = id.SCCID;

        // SET HOMEWORK IMAGE
        if($scope.HOMEWORK_DOC!==''){
            if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes($scope.HOMEWORK_DOC_TYPE)){
                $scope.HOMEWORK_DIC_IS_IMG = true;
                $scope.HW_DOC_PREVIEW='../backoffice/images/course_coverage_hw/'+id.HOMEWORK_DOC;
            }else{
                $scope.HOMEWORK_DIC_IS_IMG = false;
                $scope.HW_DOC_PREVIEW = $scope.FileTypeImage('',$scope.HOMEWORK_DOC_TYPE);
            }
        }
        // SET HOMEWORK_DOC2
        // if($scope.DOC!==''){
        //     if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes($scope.DOC_TYPE)){
        //         $scope.DOC_IS_IMG = true;
        //         $scope.HW_DOC2_PREVIEW='../backoffice/images/course_coverage_hw/'+id.DOC;
        //     }else{
        //         $scope.DOC_IS_IMG = false;
        //         $scope.HW_DOC2_PREVIEW = $scope.FileTypeImage('',$scope.DOC_TYPE);
        //     }
        // }

        $scope.temp.hwDone = id.HOMEWORK_DONE.toString();
        $scope.temp.txtStudentwork = id.STUDENTWORK.toString();
        if(id.HOMEWORK_IMG != ''){
            $scope.logo_src='images/homework/'+id.HOMEWORK_IMG;
        }else{
            $scope.logo_src='';
        }
        $scope.temp.existingHWImage = id.HOMEWORK_IMG;
    }

    $scope.checkIsImg = function(DOC_TYPE,DOC){
        if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(DOC_TYPE)){
            $scope.DOC_IS_IMG = true;
            $scope.HW_DOC2_PREVIEW='../backoffice/images/course_coverage_hw/'+DOC;
        }else{
            $scope.DOC_IS_IMG = false;
            $scope.HW_DOC2_PREVIEW = $scope.FileTypeImage('',DOC_TYPE);
        }
        return $scope.DOC_IS_IMG;
    }

    /* =========== SAVE HOMEWORK =========== */ 
    $scope.saveHomeWork = function(){
        $(".btn-saveHw").attr('disabled', 'disabled');
        $(".btn-saveHw").text('Saving...');
        $scope.temp.txtHwImg = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveHomeWork');
                formData.append("SCCID", $scope.SCCID_HW);
                formData.append("hwDone", ($scope.temp.hwDone=='0' || !$scope.temp.hwDone || $scope.temp.hwDone=='')?0:1);
                formData.append("txtStudentwork", $scope.temp.txtStudentwork);
                formData.append("txtHwImg", $scope.temp.txtHwImg);
                formData.append("existingHWImage", $scope.temp.existingHWImage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccessHW(data.data.message);
                $scope.getAttendance();
                // $('#homeWorkModal').modal('hide');

            }
            else {
                $scope.messageFailureHW(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveHw').removeAttr('disabled');
            $(".btn-saveHw").text('SAVE');
        });
    
                                        
    }

    $scope.clearHomeWork = function(){
        $scope.temp.hwDone='0';
        $scope.temp.txtStudentwork='';
        $scope.HOMEWORK_DIC_IS_IMG = false;
        $scope.HW_DOC_PREVIEW = '';
        $scope.temp.existingHWImage='';
        $scope.logo_src = '';
        $scope.files = [];
        angular.element('#txtHwImg').val(null);
    }

    

    $scope.FileTypeImage = function (FType,EXT) {
        if(['xlsx','xlsm','xlsb','xltx','xltm','xls','xlt','xls','csv','xml','xlam','xla','xlw','xlr'].includes(EXT)){
            var src = '../images/FileEx/xls.png';
        } 
        else if(['pdf'].includes(EXT)){var src = '../images/FileEx/pdf.png';} 
        else if(['doc','docx'].includes(EXT)){var src = '../images/FileEx/doc.png';} 
        else if(['pptx','pptm','ppt'].includes(EXT)){var src = '../images/FileEx/ppt.png';} 
        else if(['txt'].includes(EXT)){var src = '../images/FileEx/txt.png';}
        else{var src = '../images/FileEx/document.png';}
        return src;
    }

    // =========== VIEW STUDENT HOME WORK IMAGE ==============
    $scope.viewHomeWorkImages=function(HW,HW2_DOC){
        $scope.HOMEWORK_IMAGE_SET = [];
        $scope.HW_FOR = HW=='HW1'?$scope.HOMEWORK_DOC:HW2_DOC;
        if($scope.HW_FOR !='' && $scope.HW_FOR.length>0) $scope.HOMEWORK_IMAGE_SET.push({src: `../backoffice/images/course_coverage_hw/${$scope.HW_FOR}`,title: $scope.HW_FOR})
        
        // define options (if needed)
        var options = {
            // optionName: 'option value'
            // for example:
            index: 0, // this option means you will start at first image
            keyboard:true,
            title:true,
            fixedModalSize:true,
            modalWidth: 500,
            modalHeight: 500,
            fixedModalPos:true,
            footerToolbar: ['zoomIn','zoomOut','prev','fullscreen','next','actualSize','rotateRight','myCustomButton'],
            customButtons: {
                myCustomButton: {
                  text: '',
                  title: 'Click To Download',
                  click: function (context, e) {
                    // alert('clicked the custom button!');
                    var link = document.createElement('a');
                    link.href = `../backoffice/images/course_coverage_hw/${$scope.HW_FOR}`;
                    link.download = $scope.HW_FOR;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                  }
                }
              }
        };
        
        // Initialize the plugin
        var photoviewer = new PhotoViewer($scope.HOMEWORK_IMAGE_SET, options);    
        // $('.photoviewer-button-myCustomButton').addClass('bg-success rounded border brder-success mt-2').css({"height": "34px"});            
        $('.photoviewer-button-myCustomButton').html('<i class="fa fa-download" aria-hidden="true"></i>');            
        
    }
    // =========== VIEW STUDENT HOME WORK IMAGE ==============


    // ========== GET TUTORING ATTENDANCE ==========
    $scope.getTutoringAttendance = function(){
        $scope.spinTUTATT = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTutoringAttendance'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTutoringAttendance = data.data.success ? data.data.data : [];
            $scope.spinTUTATT = false;
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // ========== GET TUTORING ATTENDANCE ==========

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



    $scope.messageSuccessHW = function (msg) {
        jQuery('.alertSuccessHW > span').html(msg);
        jQuery('.alertSuccessHW').show();
        jQuery('.alertSuccessHW').delay(5000).slideUp(function () {
            jQuery('.alertSuccessHW > span').html('');
        });
    }

    $scope.messageFailureHW = function (msg) {
        jQuery('.alertSDangerHW > span').html(msg);
        jQuery('.alertSDangerHW').show();
        jQuery('.alertSDangerHW').delay(5000).slideUp(function () {
            jQuery('.alertSDangerHW > span').html('');
        });
    }


    $scope.messageSuccess_old = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure_old = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
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

    



});