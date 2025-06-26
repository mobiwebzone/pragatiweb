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
    $scope.PageSub = "STUDENT_COLLEGE_PROCESS";
    $scope.editMode = false;
    $scope.chkSelectedSteps = [];
    $scope.txtSEQNO = [];
    
    var url = 'code/Student_College_Process.php';



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
                    $scope.getStudentFromSTApllication();
                    // $scope.getUniversity();
                    $scope.getStepMasters();

                    $scope.getStudentCollegeProcess();
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
                formData.append("scpid", $scope.temp.scpid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("ddlCollege", $scope.temp.ddlCollege);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.scpid = data.data.GET_SCPID;
                $scope.getStudentCollegeProcess();
                if($scope.temp.scpid > 0) $scope.getSelectedSteps(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#chkSteps0").focus();},500);
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





    /* ========== GET Student College Process =========== */
    $scope.getStudentCollegeProcess = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getStudentCollegeProcess','FROM':'BACKOFF','UID':0}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentCollegeProcess = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentCollegeProcess(); --INIT
    /* ========== GET Student College Process =========== */


    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlLocation").focus();
        $scope.clearFormDET();

        $scope.temp.scpid = id.SCPID;
        // $scope.temp.ddlLocation = (id.LOCID).toString();
        // if($scope.temp.ddlLocation > 0){
        //     $scope.getStudentByLoc();
        //     $timeout(()=>{$scope.temp.ddlStudent = (id.REGID).toString();},500);
        // }
        $scope.temp.ddlStudent = (id.REGID).toString();
        if($scope.temp.ddlStudent > 0){
            $scope.getUniversityByREGID();
            $timeout(()=>{
                $scope.temp.ddlUniversity = (id.UNIVERSITYID && id.UNIVERSITYID>0) ? (id.UNIVERSITYID).toString():'';

                if($scope.temp.ddlUniversity > 0){
                    $scope.getCollegeByUniversityID();
                    $timeout(()=>{$scope.temp.ddlCollege = (id.CLID && id.CLID>0)?(id.CLID).toString():'';},1000);
                }
            },500);
        }
        
        


        if($scope.temp.scpid > 0)$scope.getSelectedSteps();

        $scope.editMode = true;
        $scope.index = $scope.post.getStudentCollegeProcess.indexOf(id);
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
                data: $.param({ 'SCPID': id.SCPID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentCollegeProcess.indexOf(id);
		            $scope.post.getStudentCollegeProcess.splice(index, 1);
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
    $scope.LINK = [];
    $scope.saveDataDET = function(){
        $(".btn-save-DET").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-DET").attr('disabled', 'disabled').text('Updating...');

        // console.log($scope.post.getStepMasters);

        $scope.LINK = $scope.post.getStepMasters.map(x=>x.STEPLINK);
        // console.log($scope.LINK);

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET');
                formData.append("scpid", $scope.temp.scpid);
                formData.append("chkSelectedSteps", $scope.chkSelectedSteps);
                formData.append("txtSEQNO", $scope.txtSEQNO);
                formData.append("LINKS", $scope.LINK);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormDET();
                $scope.getSelectedSteps();
                $scope.getStudentCollegeProcess();
                $scope.messageSuccess(data.data.message);
                
                $("#chkSteps0").focus();
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









    /* ========== GET SELECTED STEPS =========== */
    $scope.getSelectedSteps = function () {
        $('.spinSteps').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSelectedSteps','SCPID':$scope.temp.scpid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.chkSelectedSteps = data.data.success ? data.data.data : [];
            $scope.txtSEQNO = data.data.success ? data.data.ALL_SEQNO : [];
            $('.spinSteps').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedSteps();
    /* ========== GET SELECTED STEPS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {

    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $scope.chkSelectedSteps = new Array($scope.chkSelectedSteps.length).fill('0');
    }
    /* ============ Clear Form =========== */ 



/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
    
    
    
    
    










    
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */




    // /* ========== GET Location =========== */
    // $scope.getLocations = function () {
    //     $scope.post.getStudentByLoc = [];
    //     $scope.post.getLocReviewByLoc = [];
    //     $('.spinLoc').show();
    //     $http({
    //         method: 'post',
    //         url: 'code/Users_code.php',
    //         data: $.param({ 'type': 'getLocations'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getLocations = data.data.data;
    //         $('.spinLoc').hide();
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // // $scope.getLocations(); --INIT
    // /* ========== GET Location =========== */


    

    /* ========== GET STUDENT FROM STUDENT APPLICATION =========== */
    $scope.getStudentFromSTApllication = function () {
        $('.spinUser').show();
        $http({
            method: 'post',
            url: 'code/StudentApplication.php',
            data: $.param({ 'type': 'getStudentApplications'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentApplications = data.data.success ? data.data.data : [];
            $('.spinUser').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentFromSTApllication(); --INIT
    /* ========== GET STUDENT FROM STUDENT APPLICATION =========== */


       




   /* ========== GET UNIVERSITY =========== */
   $scope.getUniversityByREGID = function () {
    $('.spinUniversity').show();
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getUniversityByREGID','REGID':$scope.temp.ddlStudent}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
         $scope.post.getUniversityByREGID = data.data.success ? data.data.data : [];
        $('.spinUniversity').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getUniversityByREGID();
/* ========== GET UNIVERSITY =========== */


 


 /* ========== GET COLLEGES =========== */
 $scope.getCollegeByUniversityID = function () {
     $('.spinCollege').show();
      $http({
          method: 'post',
         url: url,
         data: $.param({ 'type': 'getCollegeByUniversityID','REGID':$scope.temp.ddlStudent,'UNIVERSITYID':$scope.temp.ddlUniversity}),
         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
     }).
     then(function (data, status, headers, config) {
         // console.log(data.data);
         $scope.post.getCollegeByUniversityID = data.data.success ? data.data.data : [];
         $('.spinCollege').hide();
     },
     function (data, status, headers, config) {
         console.log('Failed');
     })
}
// $scope.getCollegeByUniversityID();
/* ========== GET COLLEGES =========== */




/* ========== GET STEPS MASTER =========== */
$scope.getStepMasters = function () {
    $('.spinSteps').show();
    $http({
         method: 'post',
         url: 'code/Stepts_Master.php',
        data: $.param({ 'type': 'getStepMasters'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getStepMasters = data.data.success ? data.data.data : [];
         $('.spinSteps').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getStepMasters(); --INIT
/* ========== GET STEPS MASTER =========== */





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




});