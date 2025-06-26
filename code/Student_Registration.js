$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$timeout) {
    
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.PAGE = "HOME";
    $scope.FormName = 'Show Entry Form';
    $scope.selectedCourse = [];
    $scope.status = 'NoLogin';
    
    var url = 'backoffice/code/Registration_code.php';
    var masterUrl = 'backoffice/code/MASTER_API.php';

    $scope.DisplayProductPlan=function (id) {
        sessionStorage.setItem("PRODUCT_PLANID", id.PDMID);
        sessionStorage.setItem("PRODUCT_NAME", id.DISPLAY_PRODUCT);
        window.location.assign(`DisplayProduct.html?pl=${id.PDMID}&pn=${encodeURIComponent(id.DISPLAY_PRODUCT).replace(/&/g, '\u{1F984}')}`,'_Blank');
    }

    $scope.ENROLL_LOCID = sessionStorage.getItem("ENROLL_LOCATIONID");
    $scope.ENROLL_PLANID = sessionStorage.getItem("ENROLL_PLANID");


    /* ========== SELECT COURSES =========== */
    $scope.addStudentProCourses = function(FOR,id){
        if(FOR=='ADD'){
            var item = {
                        'GRADEID':$scope.temp.ddlGrade,
                        'GRADE':jQuery('#ddlGrade').find(":selected").text(),
                        'CSUBID':$scope.temp.ddlClassSubject,
                        'SUBJECT':jQuery('#ddlClassSubject').find(":selected").text(),
                        'FINAL_DRAFT':$scope.temp.ddlFinalDraft
                        };
    
            $scope.selectedCourse.findIndex(x => x.GRADEID == item.GRADEID && x.CSUBID==item.CSUBID && x.FINAL_DRAFT==item.FINAL_DRAFT) == -1 ? $scope.selectedCourse.push(item) : alert("Course already added.");
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


    // GET COMMON DATA
    $scope.ANNSHOW = false;
    $scope.init = function () {
        $http({
            method: 'post',
            url: 'code/Common.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getDashBoardAnnouncement');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            
            if (data.data.success) {
                $scope.ANNSHOW = true;
                $scope.post.Announcement = data.data.data;
                $scope.ANN_DATE = data.data.data[0]['ANDATE'];
                $scope.ANN_TILLDATE = data.data.data[0]['DB_ANNOUNCE_TILLDATE'];
                $scope.ANN = data.data.data[0]['ANNOUNCEMENT'];
                $scope.ANN_LOC = data.data.data[0]['LOCATION'];
            }else{
                $scope.ANNSHOW = false;
                $scope.post.Announcement =[];
            }
            $scope.CATEGORIES = data.data.CATEGORIES;
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

         /* ========== GET HOME MENU =========== */
        $scope.getHomeMenu = function () {
            $http({
                method: 'POST',
                url: 'code/index.php',
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'getHomeMenu');
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.navigationItems = data.data.success ? data.data.data : [];
                
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        $scope.getHomeMenu();
        /* ========== GET CAROUSEL =========== */

    }

     /* ========== GET Terms =========== */
     $scope.getTerm = function () {
        $scope.post.getTerms = [];
        $scope.AllTerm = '';
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
            $scope.AllTerm = data.data.data[0]['TERM'];
            // alert(data.data.data[0]['TERM']);
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    /* ========== GET Plan Country name =========== */
    $scope.getPlanCountryName = function () {
        $http({
            method: 'POST',
            url: 'code/Student_Registration_code.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getPlanCountryName');
                formData.append("ENROLL_LOCID", $scope.ENROLL_LOCID);
                formData.append("ENROLL_PLANID", $scope.ENROLL_PLANID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.planName = data.data.PLANNAME;
            $scope.countryID = data.data.COUNTRYID;
            if(data.data.COUNTRYID > 0){
                $scope.temp.ddlCountry=($scope.countryID).toString();
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    // alert($scope.ENROLL_LOCID);
    if($scope.ENROLL_LOCID > 0){
        $scope.temp.ddlLocation = ($scope.ENROLL_LOCID).toString();
        if($scope.temp.ddlLocation>0) $scope.getTerm();
    }
    if($scope.ENROLL_PLANID > 0){
        $scope.getPlanCountryName();
    }
    /* ========== GET PLAN NAME =========== */
    $scope.getProductDisplay = function () {
        $http({
            method: 'POST',
            url: 'backoffice/code/MASTER_API.php',
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
    $scope.getProductDisplay();
    
    
   
    // $scope.getTerm();

    $scope.FormShowHide=function (){
        $scope.FormName ='';
        var isMobileVersion = document.getElementsByClassName('collapsed');
        if (isMobileVersion.length > 0) {
            $scope.FormName = 'Hide Entry Form';
        }else{
            $scope.FormName = 'Show Entry Form';
        }
    }


    $scope.GET_REGID = 0;
    $scope.saveRegistrations = function(){
        jQuery(".btn-save").attr('disabled', 'disabled');
        jQuery(".btn-save-text").text('SUBMITTING...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveRegistrations');
                formData.append("regid", $scope.GET_REGID);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlMode", $scope.temp.ddlMode);

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
                formData.append("ENROLL_PLANID", $scope.ENROLL_PLANID);
                formData.append("BOOKEDBY", 'STUDENT');
                formData.append("selectedCourse", JSON.stringify($scope.selectedCourse));
                // formData.append("REG_FROM", 'HOME');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess("Form Successfully Submitted.");
                $scope.GET_REGID = data.data.REGID;
                // $scope.clearForm();
                // document.getElementById("ddlLocation").focus();
                jQuery(".btn-save-text").text('UPDATE');

                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            jQuery('.btn-save').removeAttr('disabled');
            // jQuery(".btn-save-text").text('THANKYOU FOR REGISTRATION');
            // jQuery('.btn-save-text').removeAttr('disabled');
        });
    }


    


     /* ========== GET Locations =========== */
     $scope.getLocation = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getLocation');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getLocation();



    /* ========== GET Countries =========== */
    $scope.getCountries = function () {
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getCountries');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCountry = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getCountries();

     /* ========== GET Products =========== */
     $scope.getProductDisplay = function () {
        $http({
            method: 'POST',
            url: 'backoffice/code/ProductDisplayMaster_code.php',
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
    $scope.getProductDisplay();



    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        jQuery('.spinGrade').show();
        $http({
            method: 'post',
            url: 'backoffice/code/Grades_Master.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrades = data.data.success ? data.data.data : [];
            jQuery('.spinGrade').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getGrades();
    /* ========== GET GRADES =========== */



    /* ========== GET CLASS/SUBJECT =========== */
    $scope.getClassSubject = function () {
        jQuery('#spinClassSubject').show();
        $http({
            method: 'post',
            url: 'backoffice/code/Class_Subject_Master.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getClassSubject');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getClassSubject = data.data.success ? data.data.data : [];
            jQuery('#spinClassSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getClassSubject();
    /* ========== GET CLASS/SUBJECT =========== */
     
    
    



    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        // document.getElementById("ddlLocation").focus();
        // $scope.FormName = 'Show Entry Form';
        
        $scope.temp={};
        $scope.GET_REGID = 0;
        $scope.editMode = false;

        if($scope.ENROLL_LOCID > 0){
            $scope.temp.ddlLocation = ($scope.ENROLL_LOCID).toString();
            if($scope.temp.ddlLocation>0) $scope.getTerm();
        }
        if($scope.ENROLL_PLANID > 0){
            $scope.getPlanCountryName();
        }

        $scope.selectedCourse = [];
        jQuery('#ddlMode').focus();
    }

    $scope.clearForNewChild = function(){
        $scope.temp.txtFirstName='';
        $scope.temp.txtLastName='';
        $scope.temp.txtGrade='';
        $scope.temp.txtClassof='';
        $scope.temp.txtAllergies='';
        $scope.GET_REGID = 0;
        $scope.editMode = false;
        $scope.selectedCourse = [];

        // jQuery(".btn-save-text").text('SUBMIT THE FORM');
        jQuery('.btn-save').attr('disabled','disabled');

        jQuery('#txtFirstName').focus();
    }
    


    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: 'code/logout.php',
            // data: $.param({ 'type': 'logout' }),
            data: { 'type': 'logout' },
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




    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        // jQuery('.checkIcon').show();
        // jQuery(".btn-save-text").text('Form Successfully Submitted');
        jQuery('.alert-success').delay(9000).slideUp(function () {
            jQuery('.alert-success > span').html('');
            // jQuery(".btn-save-text").text('Submit the Form');
            // jQuery('.checkIcon').hide();
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(10000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});