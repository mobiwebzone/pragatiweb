$postModule = angular.module("myApp", ["angularUtils.directives.dirPagination", "ngSanitize"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.temp_c = {};
    $scope.Page = "STUDENT";
    $scope.PageSub = "REG";
    $scope.editMode = false;
    $scope.selectedStudents = [];
    $scope.FormName = 'Show Entry Form';
    $scope.SelectedRegid=0;
    $scope.serial = 1;
    $scope.files = [];
    $scope.filesExcel = [];
    $scope.filesAttach = [];
    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
    $scope.temp.txtFromDT=$scope.temp.txtFromDTEmail = new Date($scope.FromDT_S);
    $scope.temp.txtToDT=$scope.temp.txtToDTEmail = new Date();
    $scope.temp.txtFromDT_ST=$scope.temp.txtToDT_ST = new Date();

    $scope.selectedCourse = [];

    /*========== Pagination Count ==========*/ 
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 50 - 49;
    }
    /*========== Pagination Count ==========*/ 
    
    var url = 'code/Registration_code.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.focusDiv = function () {
        var scrollPos =  $("#selectCon").offset().top;
        $(window).scrollTop(scrollPos);
    }



    /* ========== SELECT COURSES =========== */
    $scope.addStudentProCourses = function(FOR,id){
        if(FOR=='ADD'){
            var item = {
                        'GRADEID':$scope.temp.ddlGrade,
                        'GRADE':$('#ddlGrade').find(":selected").text(),
                        'CSUBID':$scope.temp.ddlClassSubject,
                        'SUBJECT':$('#ddlClassSubject').find(":selected").text(),
                        'FINAL_DRAFT':$scope.temp.ddlFinalDraft
                        };
    
            $scope.selectedCourse.findIndex(x => x.GRADEID == item.GRADEID && x.CSUBID==item.CSUBID && x.FINAL_DRAFT==item.FINAL_DRAFT) == -1 ? $scope.selectedCourse.push(item) : $scope.messageFailure("Course already added.");
            $scope.temp.ddlGrade = '';
            $scope.temp.ddlClassSubject = '';
            $scope.temp.ddlFinalDraft = '';
        }else{
            var idx = $scope.selectedCourse.indexOf(id);
		    $scope.selectedCourse.splice(idx, 1);
        }
        // console.log($scope.selectedCourse);
    }
    /* ========== SELECT COURSES =========== */




    /* ========== SELECT CONTACT =========== */
    $scope.selectStudents = function(item,val,AR){
        // console.log(index);
        // alert(val);
        // console.log(item);
        var idx = $scope.selectedStudents.findIndex(x => x.REGID === item.REGID);
        if(AR=='add'){
            if(idx<0){
                $scope.selectedStudents.push(item);
            }else{
                if(!val){
                    $scope.selectedStudents.splice(idx,1);
                    $('#chkSelect'+item.REGID).prop('checked', false);
                }
            }
        }else{
            $scope.selectedStudents.splice(idx,1);
            $('#chkSelect'+item.REGID).prop('checked', false);
        }

        $scope.selectAllST =  ($scope.selectedStudents.length == $scope.post.getRegistration.length) ? true : false;
    }
    $scope.selectAllStudents = function(val){
        if(val){
            $scope.selectedStudents=[];
            $scope.selectedStudents = angular.copy($scope.post.getRegistration);
            angular.forEach($scope.selectedStudents, function(value, key) {
                $('#chkSelect'+value.REGID).prop('checked', true);
            });
        }else{
            angular.forEach($scope.selectedStudents, function(value, key) {
                $('#chkSelect'+value.REGID).prop('checked', false);
            });
            $scope.selectedStudents=[];
        }
    }
    $scope.checkSelectedStudents=function(){
        $scope.temp.chkSelect={};
        $timeout(function(){
            angular.forEach($scope.selectedStudents, function(value, key) {
                $('#chkSelect'+value.REGID).prop('checked', true);
                // $scope.temp.chkSelect = [{key : fa}];
        },1000);
        });
        $scope.selectAllST =  ($scope.selectedStudents.length == $scope.post.getRegistration.length) && $scope.selectedStudents.length>0 ? true : false;
    }
    /* ========== SELECT CONTACT =========== */




    /*========= ATTACHMENT =========*/ 
    $scope.AttachmentFileName = function (element) {
        $scope.currentFile = element.files[0];
        // console.log(element.files[0]);
        if(element.files[0]['size'] > 26214400){
            alert('File size limit of 25MB.');
            angular.element('#txtAttachment').val(null);
        }else{
            var reader = new FileReader();
            reader.onload = function (event) {
                $scope.logo_src = event.target.result
                $scope.$apply(function ($scope) {
                    $scope.filesAttach = element.files;
                });
            }
            reader.readAsDataURL(element.files[0]);
        }
    }
    /*========= ATTACHMENT =========*/ 





    /*========= For Excel File Name =========*/ 
    $scope.temp.txtUploadExcel ='';
    $scope.ExcelFileName = function (element) {
        $scope.temp.txtUploadExcel ='';

        if(element.files[0] != undefined){
            var reader = new FileReader();
            reader.onload = function (event) {
                $scope.$apply(function ($scope) {
                    $scope.filesExcel = element.files;
                });
            }
            reader.readAsDataURL(element.files[0]);

            $scope.temp.txtUploadExcel = element.files[0]['name'];
            $('.uploadBtn').removeAttr('disabled');
        }
        else{
            $scope.temp.txtUploadExcel = '';
            $('.uploadBtn').attr('disabled','disabled');
        }
        // console.info($scope.temp.txtUploadExcel);
    }
    /*========= For Excel File Name =========*/ 



    
    // =========== SAVE EXCEL DATA ==============
    $scope.saveExcelFile = function(){
        $(".uploadBtn").attr('disabled', 'disabled');
        $(".uploadBtn").text('Uploading...');
        $scope.temp.txtUploadExcelData = $scope.filesExcel[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveExcelFile');
                formData.append("txtUploadExcel", $scope.temp.txtUploadExcel);
                formData.append("txtUploadExcelData", $scope.temp.txtUploadExcelData);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                angular.element('#txtUploadExcel').val(null);
                $scope.temp.txtUploadExcel='';
                $scope.filesExcel=[];

                $scope.getRegistrations();
                $scope.messageSuccess(data.data.message);                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            // $('.uploadBtn').removeAttr('disabled');
            $(".uploadBtn").text('Upload');
        });
    }
    // =========== SAVE EXCEL DATA ==============



    /*========== Check Session ==========*/ 
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
                // window.location.assign("dashboard.html");
                $scope.status="Login";

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
                    // $scope.getTerm();
                    $scope.getGrades();
                    $scope.getClassSubject();
                    $scope.getProductDisplay();
                    $scope.getCountries();
                    // $scope.getLocation();
                    // $scope.getRegistrations();
                    $scope.getMSGHistory();
                    $scope.getEMAILHistory();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
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
    /*========== Check Session ==========*/ 




    /*========== Form Show Hide ==========*/ 
    $scope.FormShowHide=function (){
        $scope.FormName ='';
        var isMobileVersion = document.getElementsByClassName('collapsed');
        if (isMobileVersion.length > 0) {
            $scope.FormName = 'Hide Entry Form';
        }else{
            $scope.FormName = 'Show Entry Form';
        }
        $('.ShowHideIcon').toggleClass("fa-plus-circle fa-minus-circle");
    }
    /*========== Form Show Hide ==========*/ 





    /*========== Save Data ==========*/ 
    $scope.GET_REGID = 0;
    $scope.saveRegistrations = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('submitting...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveRegistrations');
                formData.append("regid", $scope.temp.regid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlMode", $scope.temp.ddlMode);
                formData.append("ddlBrand", $scope.temp.ddlBrand);

                formData.append("txtFirstName", $scope.temp.txtFirstName);
                formData.append("txtLastName", $scope.temp.txtLastName);
                formData.append("txtPhone", $scope.temp.txtPhone);
                formData.append("txtEmail", $scope.temp.txtEmail);
                formData.append("txtGrade", $scope.temp.txtGrade);
                formData.append("txtClassof", $scope.temp.txtClassof);
                formData.append("txtSchool", $scope.temp.txtSchool);

                formData.append("txtAddressL1", $scope.temp.txtAddressL1);
                formData.append("txtAddressL2", $scope.temp.txtAddressL2);
                formData.append("txtCity", $scope.temp.txtCity);
                formData.append("txtState", $scope.temp.txtState);
                formData.append("txtZipCode", $scope.temp.txtZipCode);
                formData.append("ddlCountry", $scope.temp.ddlCountry);

                formData.append("txtP1_FName", $scope.temp.txtP1_FName);
                formData.append("txtP1_LName", $scope.temp.txtP1_LName);
                formData.append("txtP1_Phone", $scope.temp.txtP1_Phone);
                formData.append("txtP1_Email", $scope.temp.txtP1_Email);

                formData.append("txtP2_FName", $scope.temp.txtP2_FName);
                formData.append("txtP2_LName", $scope.temp.txtP2_LName);
                formData.append("txtP2_Phone", $scope.temp.txtP2_Phone);
                formData.append("txtP2_Email", $scope.temp.txtP2_Email);

                formData.append("txtAllergies", $scope.temp.txtAllergies);
                formData.append("txtRefferedBy", $scope.temp.txtRefferedBy);
                formData.append("txtHowFind", $scope.temp.txtHowFind);
                formData.append("txtAdditionIntruc", $scope.temp.txtAdditionIntruc);
                formData.append("Agreed", $scope.temp.Agreed);
                formData.append("txtLoginPwd", $scope.temp.txtLoginPwd);
                formData.append("txtLoginID", $scope.temp.txtLoginID);
                formData.append("ENROLL_PLANID", '');
                formData.append("BOOKEDBY", 'ADMIN');
                formData.append("selectedCourse", JSON.stringify($scope.selectedCourse));
                // formData.append("REG_FROM", 'BACKOFFICE');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.GET_REGID=data.data.REGID;
                $scope.temp.regid = data.data.REGID;
                $scope.getRegistrations();
                // $scope.clearForm();
                // document.getElementById("ddlLocation").focus();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SUBMIT');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }
    /*========== Save Data ==========*/ 


     


    /* ========== GET Registration =========== */
    $scope.getRegistrations = function () {
        $scope.post.getRegistration=[];
        if(!$scope.temp.ddlLocationSearch || $scope.temp.ddlLocationSearch<=0) return;
        $('#spinDET').show();
        $('.btnGetDT').attr('disabled','disabled');
        $('#ddlLocationSearch, #ddlGradeSearch, #ddlClassSubjectSearch, #ddlFinalDraftSearch').attr('disabled','disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getRegistrations');
                formData.append("ddlLocationSearch", $scope.temp.ddlLocationSearch);
                formData.append("ddlGradeSearch", $scope.temp.ddlGradeSearch);
                formData.append("ddlClassSubjectSearch", $scope.temp.ddlClassSubjectSearch);
                formData.append("ddlFinalDraftSearch", $scope.temp.ddlFinalDraftSearch);
                formData.append("txtFromDT_ST", !$scope.temp.txtFromDT_ST || $scope.temp.txtFromDT_ST==''? '' : $scope.temp.txtFromDT_ST.toLocaleDateString('sv-SE'));
                formData.append("txtToDT_ST", !$scope.temp.txtToDT_ST || $scope.temp.txtToDT_ST==''? '' : $scope.temp.txtToDT_ST.toLocaleDateString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        // $scope.temp.txtFromDT_ST=$scope.temp.txtToDT_ST
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getRegistration = data.data.data;
            $('#spinDET').hide();
            $('.btnGetDT').removeAttr('disabled');
            $('#ddlLocationSearch, #ddlGradeSearch, #ddlClassSubjectSearch, #ddlFinalDraftSearch').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRegistrations(); --INIT
    /* ========== GET Registration =========== */


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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.post.user[0]['LOCID'].toString():'';
            $scope.temp.ddlLocationSearch = ($scope.post.getLocations) ? $scope.post.user[0]['LOCID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getBrands();
            if($scope.temp.ddlLocationSearch > 0) $scope.getRegistrations();
            if($scope.temp.ddlLocationSearch > 0) $scope.getTerm();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET BRANDS =========== */
    $scope.getBrands = function () {
        $scope.spinBrand = true;
        $scope.post.getBrands = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $http({
            method: 'POST',
            url: masterUrl,
            processData: false,
            transformRequest : function(data){
                var formData = new FormData();
                formData.append('type','getBrandsByLocation');
                formData.append('LOCID',$scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: {'Content-Type':undefined}
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getBrands = data.data.success ? data.data.data : [];
            $scope.spinBrand = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ========== GET BRANDS =========== */





    // /* ========== GET Locations =========== */
    // $scope.getLocation = function () {
    //      $http({
    //          method: 'post',
    //         url: url,
    //         data: $.param({ 'type': 'getLocation'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getLocations = data.data.data;
    //         if($scope.post.getLocations.length>0){
    //             $timeout(()=>{
    //                 $scope.temp.ddlLocation = ($scope.post.getLocations.length==1) ? $scope.post.getLocations[0]['LOC_ID'].toString() : '';
    //             },4000);
    //         }
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // // $scope.getLocation(); --INIT
    // /* ========== GET Locations =========== */
    




    /* ========== GET Countries =========== */
    $scope.getCountries = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCountries'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCountry = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCountries(); --INIT
    /* ========== GET Countries =========== */






    /* ========== GET Products =========== */
    $scope.getProductDisplay = function () {
        $http({
            method: 'POST',
            url: 'code/ProductDisplayMaster_code.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getProductDisplay');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProductDisplays = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProductDisplay(); --INIT
    /* ========== GET Products =========== */




    
    
    /* ========== GET Products plan =========== */
    $scope.getProductPlans = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getProductPlans');
                formData.append("ddlCourse", $scope.temp.ddlCourse);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProductPlan = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProductDisplay();
    /* ========== GET Products plan =========== */



    
    
    
    /* ========== GET plan all details =========== */
    $scope.getAllPlanDetail = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getAllPlanDetail');
                formData.append("ddlplan", $scope.temp.ddlplan);
                formData.append("locid", $scope.SelectedLOCID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAllPlanDetail = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProductDisplay();
    /* ========== GET plan all details =========== */
    
    
    
    
    
    
    
    /* ========== GET plans =========== */
    $scope.getPlans=function (id) {
        $scope.SelectedRegid=id.REGID;
        $scope.SelectedLOCID=id.LOCATIONID;
        $scope.getPlans2();
    }
    // Get Plans
    $scope.getPlans2=function(){

        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans', 'regid':$scope.SelectedRegid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStPlans = data.data.data;
                $('#exampleModalPlan').modal('show');
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ========== GET plans =========== */
    
    
    
    
    
    
    /* ========== EnrollStudent PlanByAdmin =========== */
    $scope.EnrollStudent_PlanByAdmin=function (id) {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'EnrollStudent_PlanByAdmin');
                formData.append("ddlplan", $scope.temp.ddlplan);
                formData.append("txtEnrollRemark", $scope.temp.txtEnrollRemark);
                formData.append("regid", $scope.SelectedRegid);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            if(data.data.success){
                $scope.messageSuccess(data.data.message);
                $scope.getPlans2();
                $scope.getRegistrations();
            }else{
                $scope.messageFailure(data.data.message);
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ========== EnrollStudent PlanByAdmin =========== */





    
    /* ========== GET Terms =========== */
    $scope.getTerm = function () {
        $scope.AllTerm = '';
        $scope.post.getTerms = [];
        $http({
            method: 'POST',
            url: masterUrl,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTermByLocation');
                formData.append('LOCID',$scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTerms = data.data.data;
            $scope.AllTerm = data.data.data && data.data.data.length>0 ? data.data.data[0]['TERM'] : '';
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTerm(); --INIT
    /* ========== GET Terms =========== */



    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $('.spinGrade').show();
        $http({
            method: 'post',
            url: 'code/Grades_Master.php',
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
            url: 'code/Class_Subject_Master.php',
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




    

    /* ============ Edit Button ============= */ 
    $scope.editRegistration = function (id) {
        $('.collapse').collapse('show');
        $timeout(function() {
            $scope.FormShowHide();
            $scope.FormName = 'Hide Entry Form';
        },500);
        document.getElementById("ddlLocation").focus();
        $scope.GET_REGID = id.REGID;
        $scope.temp = {
            regid:id.REGID,
            ddlLocation: (id.LOCATIONID).toString(),
            ddlMode: id.MODE,
            txtFirstName: id.FIRSTNAME,
            txtLastName: id.LASTNAME,
            txtPhone: id.PHONE,
            txtEmail: id.EMAIL,
            txtGrade: id.GRADE,
            txtClassof: Number(id.CLASSOF),
            txtSchool: id.SCHOOL,

            txtAddressL1: id.ADDRESSLINE1,
            txtAddressL2: id.ADDRESSLINE2,
            txtCity: id.CITY,
            txtState: id.STATE,
            ddlCountry: (id.COUNTRYID).toString(),
            txtZipCode: id.ZIPCODE,

            txtP1_FName: id.P1_FIRSTNAME,
            txtP1_LName: id.P1_LASTNAME,
            txtP1_Phone: id.P1_PHONE,
            txtP1_Email: id.P1_EMAIL,
            
            txtP2_FName: id.P2_FIRSTNAME,
            txtP2_LName: id.P2_LASTNAME,
            txtP2_Phone: id.P2_PHONE,
            txtP2_Email: id.P2_EMAIL,
            
            txtAllergies: id.ALLERGIES,
            txtRefferedBy: id.REFERREDBY,
            txtHowFind: id.FINDUS,
            txtAdditionIntruc: id.INSTRUCTIONS,
            Agreed: id.AGREED,
            
        };
        $timeout(()=>{
            $scope.temp.txtLoginID = id.LOGINID;
            $scope.temp.txtLoginPwd = id.LOGIN_PWD;
        },1000);
        if(id.AGREED == 1){
            $scope.temp.Agreed=true;
        }
        else{
            $scope.temp.Agreed=false;
        }

        if($scope.temp.ddlLocation>0){
            $scope.getBrands();
            $timeout(()=>{
                $scope.temp.ddlBrand= id.BRANDID>0 ? id.BRANDID.toString() : '';
            },700);
        }

        $scope.getStudentProposedCourses($scope.temp.regid);
        
        $scope.editMode = true;
        $scope.index = $scope.post.getRegistration.indexOf(id);
    }
    /* ============ Edit Button ============= */ 



    /* ========== GET STUDENT PROPOSED COURSES =========== */
    $scope.getStudentProposedCourses = function (REGID) {
        // $('#spinClassSubject').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentProposedCourses','REGID':REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.selectedCourse = data.data.success ? data.data.data : [];
            // $('#spinClassSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentProposedCourses();
    /* ========== GET STUDENT PROPOSED COURSES =========== */




    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlLocation").focus();
        $scope.GET_REGID = 0;
        $scope.temp={};
        $scope.editMode = false;
        $scope.txtStudentName = '';
        $scope.txtWordExclude = '';

        $scope.selectedCourse = [];

        $scope.FromDT_S = new Date();
        $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
        $scope.temp.txtFromDT=$scope.temp.txtFromDTEmail = new Date($scope.FromDT_S);
        $scope.temp.txtToDT=$scope.temp.txtToDTEmail = new Date();
        $scope.getLocations();
    }
    $scope.clearForNewChild = function(){
        $scope.temp.txtFirstName='';
        $scope.temp.txtLastName='';
        $scope.temp.txtGrade='';
        $scope.temp.txtClassof='';
        $scope.temp.txtAllergies='';
        $scope.temp.txtLoginID='';
        $scope.temp.txtLoginPwd='';
        $scope.GET_REGID = 0;
        $scope.temp.regid = 0;
        $scope.editMode = false;
        $scope.selectedCourse = [];

        $('#txtFirstName').focus();
    }
    /* ============ Clear Form =========== */ 





    /* ========== Cancel Plan =========== */
    $scope.CancelPlan = function (id,remark) {
        var r = confirm("Are you sure want to cancel this plan!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'regdid': id.REGDID, 'remark':remark,'type': 'CancelPlan' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
                    var index = $scope.post.getStPlans.indexOf(id);
		            $scope.post.getStPlans.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.temp_c={};
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== Cancel Plan =========== */




    
    /* ========== DELETE =========== */
    $scope.deleteRegistration = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'regid': id.REGID, 'type': 'deleteRegistration' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
                    var index = $scope.post.getRegistration.indexOf(id);
		            $scope.post.getRegistration.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
                    $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */



















    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EMAIL / SMS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    /* ========== SMS/EMAIL =========== */
    $scope.saveData = function(SMS_EMAIL){
        $(".btn-saveSms,.btn-saveEmail").attr('disabled', 'disabled');
        if(SMS_EMAIL == 'SMS'){$(".btn-saveSms").text('Sending...')};
        if(SMS_EMAIL == 'EMAIL'){$(".btn-saveEmail").text('Sending...')};

        $TYPE = SMS_EMAIL==='SMS' ? 'saveDataSms' : 'saveDataEmail';
        if(SMS_EMAIL==='EMAIL'){$scope.temp.txtAttachment=$scope.filesAttach[0];}else{$scope.temp.txtAttachment='';};

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", $TYPE);
                formData.append("txtMessage", $scope.temp.txtMessage);
                formData.append("StudentData", JSON.stringify($scope.selectedStudents));
                formData.append("txtAttachment", $scope.temp.txtAttachment);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if(SMS_EMAIL=='SMS'){
                    $scope.getMSGHistory();
                }else{
                    $scope.getEMAILHistory();
                }
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveSms,.btn-saveEmail').removeAttr('disabled');
            if(SMS_EMAIL == 'SMS'){$(".btn-saveSms").html('<i class="fa fa-comments font-15"></i> SEND SMS')};
            if(SMS_EMAIL == 'EMAIL'){$(".btn-saveEmail").html('<i class=" fa fa-envelope font-15"></i> SEND EMAIL')};
        });
    }
    /* ========== SMS/EMAIL =========== */




    /* ========== GET SMS =========== */
    $scope.getMSGHistory = function () {
        if(($scope.temp.txtFromDT && $scope.temp.txtFromDT!='') && ($scope.temp.txtToDT && $scope.temp.txtToDT!='')){
            $('#SpinMainData').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getMSGHistory',
                                'txtFromDT':$scope.temp.txtFromDT.toLocaleDateString(),
                                'txtToDT':$scope.temp.txtToDT.toLocaleDateString()
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getMSGHistory = data.data.success ? data.data.data : [];
                $('#SpinMainData').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getMSGHistory(); --INIT
    /* ========== GET SMS =========== */





    /* ========== GET EMAIL =========== */
    $scope.getEMAILHistory = function () {
        if(($scope.temp.txtFromDTEmail && $scope.temp.txtFromDTEmail!='') && ($scope.temp.txtToDTEmail && $scope.temp.txtToDTEmail!='')){
            $('#SpinMainDataEmail').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getEMAILHistory',
                                'txtFromDT':$scope.temp.txtFromDTEmail.toLocaleDateString(),
                                'txtToDT':$scope.temp.txtToDTEmail.toLocaleDateString()
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getEMAILHistory = data.data.success ? data.data.data : [];
                $('#SpinMainDataEmail').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getEMAILHistory(); --INIT
    /* ========== GET EMAIL =========== */

    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EMAIL / SMS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    


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
    /* ========== Logout =========== */
    

    $scope.eyepass= function() {
        var input = $("#txtLoginPwd");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon').removeClass('fa-eye');
            $('.Eyeicon').addClass('fa-eye-slash');
        }else{
            $('.Eyeicon').removeClass('fa-eye-slash');
            $('.Eyeicon').addClass('fa-eye');
        }
    };
    
    
    
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
    // /* ========== Message =========== */
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').show();
    //     jQuery('.alert-success').delay(5000).slideUp(function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }
    
    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').show();
    //     jQuery('.alert-danger').delay(5000).slideUp(function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }
    // /* ========== Message =========== */

});