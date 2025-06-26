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
    $scope.PageSub = "STUDENT_COLLEGE_PROCESS_DETAILS";
    $scope.editMode = false;
    $scope.FROMPAGE = 'ADMIN';
    
    var url = 'code/Student_College_Process_Details.php';



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
                    // $scope.getLocations();
                    // $scope.getUniversity();
                    // $scope.getStepMasters();
                    $scope.getStudentCollegeProcess();
                    // $scope.getStudentFromSTApllication();

                    // $scope.getST_CLG_PROC_STEP_DET();
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
                formData.append("scpsdid", $scope.temp.scpsdid);
                formData.append("scpsid", $scope.temp.scpsid);
                formData.append("stepid", $scope.temp.stepid);
                formData.append("FROM", 'ST');
                formData.append("EDIT_ADD", $scope.EDIT_ADD);
                formData.append("ddlCurrentOwner", $scope.temp.ddlCurrentOwner);
                formData.append("txtComment", $scope.temp.txtComment);
                formData.append("txtGDriveLink", $scope.temp.txtGDriveLink);
                formData.append("ddlStatus", $scope.temp.ddlStatus);
                formData.append("txtStudentETA_DT", ($scope.temp.txtStudentETA_DT && $scope.temp.txtStudentETA_DT!='') ? $scope.temp.txtStudentETA_DT.toLocaleString('sv','SE'):'');
                formData.append("txtStudentCDT_DT", ($scope.temp.txtStudentCDT_DT && $scope.temp.txtStudentCDT_DT!='') ? $scope.temp.txtStudentCDT_DT.toLocaleString('sv','SE'):'');
                formData.append("txtMEP_ETA_DT", ($scope.temp.txtMEP_ETA_DT && $scope.temp.txtMEP_ETA_DT!='') ? $scope.temp.txtMEP_ETA_DT.toLocaleString('sv','SE'):'');
                formData.append("txtMEP_CDT_DT", ($scope.temp.txtMEP_CDT_DT && $scope.temp.txtMEP_CDT_DT!='') ? $scope.temp.txtMEP_CDT_DT.toLocaleString('sv','SE'):'');
                formData.append("txtRemarks", $scope.temp.txtRemarks);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getST_CLG_PROC_STEP_DET();
                // $scope.clearForm();
                $scope.temp.scpsdid='';
                $scope.messageSuccess(data.data.message);
                
                // $timeout(()=>{$("#chkSteps0").focus();},500);
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




    /* ========== Open Process Modal =========== */
    $scope.OpenProcessModal = function(id){
        $scope.post.getST_CLG_PROC_STEP_DET=[];
        $scope.temp.scpid = id.SCPID;
        if(id.SCPID > 0)$scope.getST_CLG_PROC_STEP_DET();
        if(id.SCPID > 0)$('#ProccessModal').modal('show');
    }
    /* ========== Open Process Modal =========== */




    /* ========== GET Student College Process Step Details =========== */
    $scope.getST_CLG_PROC_STEP_DET = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 
                        'type': 'getST_CLG_PROC_STEP_DET',
                        'SCPID' : $scope.temp.scpid,               
                    }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getST_CLG_PROC_STEP_DET = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getST_CLG_PROC_STEP_DET(); --INIT
    /* ========== GET Student College Process Step Details =========== */


    


    /* ============ CLOSE STEP FORM ============= */ 
    $scope.closeStepForm = function(){
        $scope.temp.scpsdid='';
        $scope.temp.scpid=''; 
        $scope.EDIT_ADD=false;
        $('#StepsForm').slideUp();
    }
    /* ============ CLOSE STEP FORM ============= */ 


    /* ============ Edit Button ============= */ 
    $scope.EDIT_ADD = false;
    $scope.addEditStep = function (EA,id) {
        console.log(id);
        $('#StepsForm').slideDown();

        $("#ddlStudent").focus();
        
        $scope.EDIT_ADD=EA == 'edit' ? true : false;
        $scope.temp.scpsdid = id.SCPSDID;
        $scope.temp.scpsid = id.SCPSID;
        $scope.temp.stepid = id.STEPID;

        if(EA == 'edit'){
            // $scope.temp.ddlStudent = (id.REGID && id.REGID>0) ? (id.REGID).toString() : '';
            // if($scope.temp.ddlStudent > 0){
            //     $scope.getUniversityByREGID();
            //     $timeout(()=>{$scope.temp.ddlUniversity = (id.UNIVERSITYID && id.UNIVERSITYID>0) ? (id.UNIVERSITYID).toString() : '';},500);
    
            //     if($scope.temp.ddlUniversity > 0){
            //         $scope.getCollegeByUniversityID();
            //         $timeout(()=>{$scope.temp.ddlCollege = (id.CLID && id.CLID>0)?(id.CLID).toString():'';},700);
            //     }
            // }
            $scope.temp.ddlCurrentOwner = (id.CURRENT_OWNER && id.CURRENT_OWNER!='')?id.CURRENT_OWNER:'';
            $scope.temp.txtComment = (id.COMMENTS && id.COMMENTS!='')?id.COMMENTS:'';
            $scope.temp.txtGDriveLink = (id.GDRIVE_LINK && id.GDRIVE_LINK!='')?id.GDRIVE_LINK:'';
            $scope.temp.ddlStatus = (id.STEP_STATUS && id.STEP_STATUS!='')?id.STEP_STATUS:'';
            $scope.temp.txtStudentETA_DT = (id.STUDENT_ETA && id.STUDENT_ETA!='')?new Date(id.STUDENT_ETA):'';
            $scope.temp.txtStudentCDT_DT = (id.STUDENT_CDT && id.STUDENT_CDT!='')?new Date(id.STUDENT_CDT):'';
            $scope.temp.txtMEP_ETA_DT = (id.MEP_ETA && id.MEP_ETA!='')?new Date(id.MEP_ETA):'';
            $scope.temp.txtMEP_CDT_DT = (id.MEP_CDT && id.MEP_CDT!='')?new Date(id.MEP_CDT):'';
            $scope.temp.txtRemarks = (id.REMARKS && id.REMARKS!='')?id.REMARKS:'';
    
            $scope.editMode = true;
            $scope.index = $scope.post.getST_CLG_PROC_STEP_DET.indexOf(id);
        }else{
            $scope.temp.ddlCurrentOwner = '';
            $scope.temp.txtComment = '';
            $scope.temp.txtGDriveLink = '';
            $scope.temp.ddlStatus = '';
            $scope.temp.txtStudentETA_DT = '';
            $scope.temp.txtStudentCDT_DT = '';
            $scope.temp.txtMEP_ETA_DT = '';
            $scope.temp.txtMEP_CDT_DT = '';
            $scope.temp.txtRemarks = '';
    
            $scope.editMode = false;
            
        }
    }
    /* ============ Edit Button ============= */ 



    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;
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
                // console.log(data.data)
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










    
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */



    /* ========== GET Student College Process =========== */
    $scope.getStudentCollegeProcess = function () {
        $('#SpinSCP').show();
        $http({
             method: 'post',
             url: 'code/Student_College_Process.php',
            data: $.param({ 'type': 'getStudentCollegeProcess','FROM':'BACKOFF','UID':0}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentCollegeProcess = data.data.success ? data.data.data : [];
             $('#SpinSCP').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentCollegeProcess(); --INIT
    /* ========== GET Student College Process =========== */


    
    
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