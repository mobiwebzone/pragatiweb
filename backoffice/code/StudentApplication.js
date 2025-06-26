$postModule = angular.module("myApp", ["ngSanitize","angularjs-dropdown-multiselect"]);
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
    $scope.Page = "COLLEGE_APP";
    $scope.PageSub = "STUDENT_APPLICATION";
    $scope.editMode = false;
    
    var url = 'code/StudentApplication.php';

    /* ============ CHECK SESSION ============= */ 
    $scope.init = function () {
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
                    $scope.getAdmYears();
                    $scope.getAppNames();
                    $scope.getUniversity();
                    $scope.getCollegeMajor(); 
                    $scope.getDeadlineTypes();

                    $scope.getStudentApplications();
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ============ CHECK SESSION ============= */ 






/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("applid", $scope.temp.applid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("ddlAdmYear", $scope.temp.ddlAdmYear);
                // formData.append("ddlApp", $scope.temp.ddlApp);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.applid = data.data.GET_APPLID;
                $scope.getStudentApplications();
                if($scope.temp.applid > 0) $scope.getStudentApplications_DET(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#ddlLocation").focus();},500);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */ 





    /* ========== GET STUDENT APPLICATION =========== */
    $scope.getStudentApplications = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getStudentApplications'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentApplications = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentApplications(); --INIT
    /* ========== GET STUDENT APPLICATION =========== */

    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlLocation").focus();
        $scope.clearFormDET();

        $scope.temp.applid = id.APPLID;
        $scope.temp.ddlLocation = (id.LOCID && id.LOCID>0) ? id.LOCID.toString() : '';
        if(id.LOCID > 0 && $scope.temp.ddlLocation>0) $scope.getStudentByLoc();
        $timeout(()=>{
            $scope.temp.ddlStudent =  (id.REGID && id.REGID>0) ? id.REGID.toString() : ''; 
            // $timeout(()=>{if($scope.temp.ddlStudent>0)$scope.setStudentDetails()},500);
        },700);
        $scope.temp.ddlAdmYear = (id.ADMYEARID && id.ADMYEARID>0) ? id.ADMYEARID.toString() : '';
        // $scope.temp.ddlApp = (id.APPID && id.APPID>0) ? id.APPID.toString() : '';


        // if(id.APPLICATIONID > 0) $scope.CollegeAppDeadlines_DET();
        if($scope.temp.applid > 0)$scope.getStudentApplications_DET();

        $scope.editMode = true;
        $scope.index = $scope.post.getStudentApplications.indexOf(id);
    }
    /* ============ Edit Button ============= */ 



    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;

        $scope.clearFormDET();
        $("#ddlLocation").focus();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'APPLID': id.APPLID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentApplications.indexOf(id);
		            $scope.post.getStudentApplications.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearForm();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveDataDET = function(){
        $(".btn-save-DET").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-DET").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET');
                formData.append("appldid", $scope.temp.appldid);
                formData.append("applid", $scope.temp.applid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("ddlCollege", $scope.temp.ddlCollege);
                formData.append("ddlCollegeMajor", $scope.temp.ddlCollegeMajor);
                formData.append("ddlDeadlineType", $scope.temp.ddlDeadlineType);
                formData.append("ddlApp", $scope.temp.ddlApp);
                formData.append("txtCommentsDetails", $scope.temp.txtCommentsDetails);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getStudentApplications_DET();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlUniversity").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-DET').removeAttr('disabled').text('ADD');
            $('.btn-update-DET').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */






    /* ========== GET STUDENT APPLICATION DETAILS =========== */
    $scope.getStudentApplications_DET = function () {
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getStudentApplications_DET','applid':$scope.temp.applid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentApplications_DET = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentApplications_DET();
    /* ========== GET STUDENT APPLICATION DETAILS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#ddlUniversity").focus();
    
        $scope.temp.appldid = id.APPLDID;
        $scope.temp.ddlUniversity = (id.UNIVERSITYID && id.UNIVERSITYID>0) ? id.UNIVERSITYID.toString() : '';
        if($scope.temp.ddlUniversity > 0 && id.UNIVERSITYID>0){
            $scope.getCollegeByUniversity();
            $timeout(()=>{$scope.temp.ddlCollege=(id.CLID && id.CLID>0)?id.CLID.toString():'';},500);
        }
        $scope.temp.ddlCollegeMajor = (id.MAJORID && id.MAJORID>0) ? id.MAJORID.toString() : '';
        $scope.temp.ddlDeadlineType = (id.DEADLINETYPEID && id.DEADLINETYPEID>0) ? id.DEADLINETYPEID.toString() : '';
        $scope.temp.ddlApp = (id.APPID && id.APPID>0) ? id.APPID.toString() : '';
        $scope.temp.txtCommentsDetails = id.COMMENTS;

        $scope.index = $scope.post.getStudentApplications_DET.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#ddlUniversity").focus();
        $scope.temp.appldid = '';
        $scope.temp.ddlUniversity = '';
        $scope.temp.ddlCollege = '';
        $scope.temp.ddlCollegeMajor = '';
        $scope.temp.ddlDeadlineType = '';
        $scope.temp.ddlApp = '';
        $scope.temp.txtCommentsDetails = '';
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'APPLDID': id.APPLDID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentApplications_DET.indexOf(id);
		            $scope.post.getStudentApplications_DET.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormDET();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
    
    
    
    
    










    
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */

    /* ========== SET STUDENT DETAILS =========== */
    $scope.DETAILS = [];
    $scope.setStudentDetails = () => {
        $scope.DETAILS = [];
        $scope.temp.txtPhone = '';
        $scope.temp.txtEmail = '';
        $scope.temp.txtP1Name = '';
        $scope.temp.txtP2Name = '';
        $scope.temp.txtP1Phone = '';
        $scope.temp.txtP2Phone = '';
        $scope.temp.txtP1Email = '';
        $scope.temp.txtP2Email = '';

        if($scope.temp.ddlStudent <= 0) return
        $scope.DETAILS = $scope.post.getStudentByLoc.filter((x)=> x.REGID === Number($scope.temp.ddlStudent)); 
        console.log($scope.DETAILS);

        $scope.temp.txtPhone = `${($scope.DETAILS[0]['PHONE'] && $scope.DETAILS[0]['PHONE']!='' && $scope.DETAILS[0]['PHONE']!='null') ? $scope.DETAILS[0]['PHONE'] : ''}`;
        $scope.temp.txtEmail = `${($scope.DETAILS[0]['EMAIL'] && $scope.DETAILS[0]['EMAIL']!='' && $scope.DETAILS[0]['EMAIL']!='null') ? $scope.DETAILS[0]['EMAIL']:''}`;

        $scope.temp.txtP1Name = `${$scope.DETAILS[0]['P1_FIRSTNAME']} ${$scope.DETAILS[0]['P1_LASTNAME']}`;
        $scope.temp.txtP1Phone = `${$scope.DETAILS[0]['P1_PHONE']}`;
        $scope.temp.txtP1Email = `${$scope.DETAILS[0]['P1_EMAIL']}`;
        
        $scope.temp.txtP2Name = `${$scope.DETAILS[0]['P2_FIRSTNAME']} ${$scope.DETAILS[0]['P2_LASTNAME']}`;
        $scope.temp.txtP2Phone = `${$scope.DETAILS[0]['P2_PHONE']}`;
        $scope.temp.txtP2Email = `${$scope.DETAILS[0]['P2_EMAIL']}`;
        
    }
    /* ========== SET STUDENT DETAILS =========== */



    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getStudentByLoc = [];
        $scope.post.getLocReviewByLoc = [];
        $('.spinLoc').show();
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    

    /* ========== GET STUDENT BY LOCATION =========== */
    $scope.getStudentByLoc = function () {
        $('.spinUser').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentByLoc = data.data.data;
                if($scope.editMode)$timeout(()=>{if($scope.temp.ddlStudent>0)$scope.setStudentDetails()},500);
            }else{
                $scope.post.getStudentByLoc = [];
            }
            $('.spinUser').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByLoc();
    /* ========== GET STUDENT BY LOCATION =========== */


    /* ========== GET ADM YEARS =========== */
    $scope.getAdmYears = function () {
        $('.spinAdmYaer').show();
        $http({
            method: 'post',
            url: 'code/Admission_Year_Master.php',
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
   




    /* ========== GET APPS NAMES =========== */
    $scope.getAppNames = function () {
        $('.spinApps').show();
        $http({
            method: 'post',
            url: 'code/App_Master.php',
           data: $.param({ 'type': 'getAppNames'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getAppNames = data.data.success ? data.data.data : [];
            $('.spinApps').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getAppNames(); --INIT
   /* ========== GET APPS NAMES =========== */
   




   /* ========== GET UNIVERSITY =========== */
   $scope.getUniversity = function () {
       $('.spinUniversity').show();
       $http({
           method: 'post',
           url: 'code/University_Master_code.php',
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
   // $scope.getUniversity(); --INIT
   /* ========== GET UNIVERSITY =========== */


    


    /* ========== GET COLLEGES =========== */
    $scope.getCollegeByUniversity = function () {
        $('.spinCollege').show();
         $http({
             method: 'post',
            url: 'code/Student_Final_Result_code.php',
            data: $.param({ 'type': 'getCollegeByUniversity','UNIVERSITYID':$scope.temp.ddlUniversity}),
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





   /* ========== GET COLLEGE MAJOR =========== */
   $scope.getCollegeMajor = function () {
    $('.spinCollegeMajor').show();
    $http({
        method: 'post',
        url: 'code/College_Major_Master_code.php',
        data: $.param({ 'type': 'getCollegeMajor'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCollegeMajor = data.data.success?data.data.data:[];
        $('.spinCollegeMajor').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getCollegeMajor(); --INIT
    /* ========== GET COLLEGE MAJOR =========== */





    
    /* ========== GET DEADLINE TYPE =========== */
    $scope.getDeadlineTypes = function () {
    $('.spinDeadlineType').show();
        $http({
            method: 'post',
            url: 'code/Application_DeadlineType_Master.php',
        data: $.param({ 'type': 'getDeadlineTypes'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDeadlineTypes = data.data.success ? data.data.data : [];
            $('.spinDeadlineType').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getDeadlineTypes(); --INIT
    /* ========== GET DEADLINE TYPE =========== */



/* ######################################################################################################################### */
/*                                           GET EXTRA DATA END                                                              */
/* ######################################################################################################################### */
    


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
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
    //         jQuery('.alert-success').show();
    //     });
    //     jQuery('.alert-success').delay(1000).slideUp(700,'easeOutBounce',function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
    //         jQuery('.alert-danger').show();
    //     });
    //     jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }




});