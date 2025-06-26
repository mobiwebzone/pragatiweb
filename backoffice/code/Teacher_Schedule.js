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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.Page = "TEACHER";
    $scope.PageSub = "TEACHER_SCHEDULE";
    $scope.editMode = false;
    $scope.post.weekday = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    var url = 'code/Teacher_Schedule_code.php';



    

    /* ========== CHECK SESSION =========== */
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
                $scope.LOC_ID=data.data.locid;
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    // $scope.getTeacher();
                    $scope.getPlans();
                    $scope.getProduct();
                    // $scope.getTeacherSchedules();
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
    /* ========== CHECK SESSION =========== */


    
    

    /* ========== SAVE DATA =========== */
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("schid", $scope.temp.schid);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlDay", $scope.temp.ddlDay);
                formData.append("txtEffectiveFromDT", $scope.temp.txtEffectiveFromDT.toLocaleString('sv-SE'));
                formData.append("txtEffectiveToDT", $scope.temp.txtEffectiveToDT.toLocaleString('sv-SE'));
                formData.append("txtTimeFrom", $scope.temp.txtTimeFrom.toLocaleString('sv-SE'));
                formData.append("txtTimeTo", $scope.temp.txtTimeTo.toLocaleString('sv-SE'));
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtMeetingID", $scope.temp.txtMeetingID);
                formData.append("ddlClassType", $scope.temp.ddlClassType);
                formData.append("ddlClassIn", $scope.temp.ddlClassIn);
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.schid=data.data.GET_SCHID;

                $scope.getTeacherSchedules();
                if($scope.temp.schid > 0){
                    $scope.getTeacherSchStudents();
                }
                $timeout(()=>{$("#ddlPlan").focus();},500);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }
    /* ========== SAVE DATA =========== */



    /* ========== GET TEACHER SCHEDULES =========== */
     $scope.getTeacherSchedules = function () {
        $scope.post.getTeacherSchedules = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
         $('#SpinMainData').show();
         $('.btn-search').attr('disabled','disabled');

         $scope.txtScrhFromDT = ($scope.temp.txtScrhFromDT == undefined || $scope.temp.txtScrhFromDT == '') ? '' : $scope.temp.txtScrhFromDT.toLocaleString('sv-SE');
         $scope.txtScrhToDT = ($scope.temp.txtScrhToDT == undefined || $scope.temp.txtScrhToDT == '') ? '' : $scope.temp.txtScrhToDT.toLocaleString('sv-SE');
         $http({
             method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getTeacherSchedules',
                            'ddlLocation' : $scope.temp.ddlLocation,
                            'txtScrhFromDT' : $scope.txtScrhFromDT,
                            'txtScrhToDT' : $scope.txtScrhToDT,
                            'ddlScrhDay' : $scope.temp.ddlScrhDay,
                            'ddlScrhTeacher' : $scope.temp.ddlScrhTeacher,
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTeacherSchedules = data.data.data;
            }else{
                $scope.post.getTeacherSchedules = [];
            }
            $('#SpinMainData').hide();
            $('.btn-search').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherSchedules() --INIT
    /* ========== GET TEACHER SCHEDULES =========== */




    /* ========== GET TEACHERS =========== */
    $scope.getTeacher = function () {
        $('.spinTeacher').show();
        $http({
            method: 'post',
            url: 'code/Teacher_Product_code.php',
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacher = data.data.data;
            $('.spinTeacher').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    // $scope.getTeacher()--;
    /* ========== GET TEACHERS =========== */




    /* ========== GET PRODUCT =========== */
    $scope.getProduct = function () {
        $('.spinProduct').show();
        $http({
            method: 'post',
            url: 'code/Products_code.php',
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
            $('.spinProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT
    /* ========== GET PRODUCT =========== */


    
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
            if($scope.temp.ddlLocation > 0) $scope.getTeacher();
            if($scope.temp.ddlLocation > 0) $scope.getTeacherSchedules();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */



    /* ============ Edit Button ============= */ 
    $scope.editDate = function (id) {
        $scope.post.getTeacherSchStudents = [];
        $("#ddlTeacher").focus();
        $scope.temp.schid = id.SCHID;
        $scope.temp.ddlTeacher = (id.TEACHERID).toString();
        $scope.temp.ddlDay = id.DAY;
        $scope.temp.txtEffectiveFromDT = new Date(id.EFFECTIVE_FROM);
        $scope.temp.txtEffectiveToDT = new Date(id.EFFECTIVE_TO);
        $scope.temp.txtTimeFrom = new Date(id.TIME_FROM_SET);
        $scope.temp.txtTimeTo = new Date(id.TIME_TO_SET);
        $scope.temp.ddlProduct = (id.PRODUCTID).toString();
        $scope.temp.txtMeetingID = id.MEETINGID;
        $scope.temp.ddlClassType = id.CLASSTYPE;
        $scope.temp.ddlClassIn = id.CLASSIN;
        $scope.temp.txtRemark = id.REMARKS;

        $scope.getTeacherSchStudents();

        $scope.editMode = true;
        $scope.index = $scope.post.getTeacherSchedules.indexOf(id);
    }
    /* ============ Edit Button ============= */ 

    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlTeacher").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getTeacherSchStudents = [];

        $scope.clearFormStudents();
        $scope.getLocations();
    } 
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SCHID': id.SCHID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTeacherSchedules.indexOf(id);
		            $scope.post.getTeacherSchedules.splice(index, 1);
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
    






// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% ADD STUDENTS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



    // =========== SAVE DATA ==============
    $scope.saveDataStudents = function(){
        $(".btn-save-Students").attr('disabled', 'disabled');
        $(".btn-save-Students").text('Saving...');
        $(".btn-update-Students").attr('disabled', 'disabled');
        $(".btn-update-Students").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataStudents');
                formData.append("schregid", $scope.temp.schregid);
                formData.append("schid", $scope.temp.schid);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormStudents();
                $scope.getTeacherSchStudents();
                $scope.messageSuccess(data.data.message);
                $scope.getTeacherSchedules();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-Students').removeAttr('disabled');
            $(".btn-save-Students").text('SAVE');
            $('.btn-update-Students').removeAttr('disabled');
            $(".btn-update-Students").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============
    
    
    
    
        
        
    /* ========== GET TEACHER SCHEDULE STUDENTS =========== */
    $scope.getTeacherSchStudents = function () {
        $('#spinTSS').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherSchStudents', 'schid' : $scope.temp.schid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTeacherSchStudents = data.data.data;
            }else{
                $scope.post.getTeacherSchStudents = [];
            }
            $('#spinTSS').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherSchStudents();
    /* ========== GET TEACHER SCHEDULE STUDENTS =========== */



        


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getPlan = data.data.data;
            }else{
                $scope.post.getPlan = [];
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */




    /* ========== GET STUDENT BY PLAN =========== */
    $scope.getStudentByPlan = function () {
        $('.spinStudent').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByPlan','PLANID':$scope.temp.ddlPlan}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentByPlan = data.data.data;
            }else{
                $scope.post.getStudentByPlan = [];
            }
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlan();
    /* ========== GET STUDENT BY PLAN =========== */
    
    
    
    
    
    /* ============ Edit Button ============= */ 
    $scope.editFormStudents = function (id) {
        $("#ddlPlan").focus();
        $scope.temp.schregid = id.SCHREGID;
        $scope.temp.ddlPlan = (id.PLANID).toString();
        $scope.getStudentByPlan();
        $timeout(()=>{$scope.temp.ddlStudent = (id.REGID).toString();},700);

        $scope.editModeStudents = true;
        $scope.index = $scope.post.getTeacherSchStudents.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
        
        
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearFormStudents = function(){
        $("#ddlPlan").focus();
        $scope.temp.schregid = '';
        $scope.temp.ddlPlan = '';
        $scope.temp.ddlStudent = '';
        $scope.editModeStudents = false;
        $scope.post.getStudentByPlan = [];
    }
    /* ============ Clear Form =========== */ 
    
    
    
    
    /* ========== DELETE =========== */
    $scope.deleteStudents = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SCHREGID': id.SCHREGID, 'type': 'deleteStudents' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTeacherSchStudents.indexOf(id);
                    $scope.post.getTeacherSchStudents.splice(index, 1);
                    // console.log(data.data.message)
                    $scope.getTeacherSchedules();
                    $scope.clearFormStudents();
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    /* ========== DELETE =========== */



    /* ========== CLEAR SEARCH =========== */
    $scope.clearSearch=()=>{
        $scope.temp.txtSerarch = '';
        $scope.temp.txtScrhFromDT = '';
        $scope.temp.txtScrhFromDT = '';
        $scope.temp.ddlScrhDay = '';
        $scope.temp.ddlScrhTeacher = '';

        $scope.getTeacherSchedules();
    }
    /* ========== CLEAR SEARCH =========== */


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




});