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
    $scope.editMode = false;
    $scope.Page = "L&A";
    $scope.PageSub = "LA_ST_BATHCES";
    $scope.PageSub1 = "";
    $scope.chkStudentidList=[];
    $scope.chkTeacheridList=[];
    $scope.StudentListLength=0;
    $scope.TeacherListLength=0;
    
    var url = 'code/LA-STUDENT-BATCHES.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
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

    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("batchid", $scope.temp.batchid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtBatchName", $scope.temp.txtBatchName);
                formData.append("txtBatchDesc", $scope.temp.txtBatchDesc);
                // formData.append("studentIdList", $scope.chkStudentidList);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.batchid = data.data.GET_BATCHID;
                $scope.messageSuccess(data.data.message);
                // $scope.clear();
                $scope.getBatchStudentsData();
                $scope.getBatchTeachersData();
                $scope.getStudentBatchesData();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET DATA =========== */
    $scope.getStudentBatchesData = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getStudentBatchesData',
                            'ddlLocation':$scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getStudentBatchesData = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
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
            if($scope.temp.ddlLocation > 0) $scope.getStudentBatchesData();
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
    



    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        // $scope.clear();
        $('#txtBatchName').focus();
        // $('#ddlLocation, #ddlPlan').attr('disabled','disabled');

        $scope.temp.batchid = id.BATCHID;
        $scope.temp.txtBatchName = id.BATCHNAME;
        $scope.temp.txtBatchDesc = id.BATCHDESC;

        $scope.getBatchStudentsData();
        $scope.getStudents();

        $scope.getBatchTeachersData();
        $scope.getTeachers();

        $scope.editMode = true;
        $scope.index = $scope.post.getStudentBatchesData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $('#ddlLocation').focus();
        // $scope.temp={};
        $scope.temp.batchid='';
        $scope.temp.ddlPlan='';
        $scope.chkStudentidList=[];
        $scope.post.getStudents=[];
        $scope.chkTeacheridList=[];
        $scope.post.getTeachers=[];
        $scope.temp.txtBatchName='';
        $scope.temp.txtBatchDesc='';
        $scope.editMode = false;
        $scope.clearFormDET();
        $scope.clearFormDET_TH();
        // $('#ddlLocation, #ddlPlan').removeAttr('disabled');
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'BATCHID': id.BATCHID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentBatchesData.indexOf(id);
		            $scope.post.getStudentBatchesData.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clear();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    


    // ####################################################################################
    //                                      ADD STUDENT START
    // ####################################################################################
    $scope.checkStudentList_Blank = (val,index) =>{
        // console.log($scope.chkStudentidList.filter(x=>x!=='0'));
        $scope.StudentListLength = $scope.chkStudentidList.filter(x=>x!=='0').length;
        // alert($scope.StudentListLength);
    }

    /* ========== GET STUDENTS =========== */
    $scope.getStudents = function () {
        $scope.post.getStudents=[];
        $scope.chkStudentidList=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlPlan || $scope.temp.ddlPlan<=0 || !$scope.temp.batchid || $scope.temp.batchid<=0) return;
        $('.spinStudents, .spinStudentLT').show();
        $('#ddlPlan').attr('disabled','true');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getStudents',
                            'ddlPlan':$scope.temp.ddlPlan,
                            'batchid':$scope.temp.batchid,
                            'ddlLocation':$scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getStudents = data.data.success ? data.data.data : [];

            $('.spinStudents, .spinStudentLT').hide();
            $('#ddlPlan').removeAttr('disabled').focus();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getStudents();
    /* ========== GET STUDENTS =========== */


    $scope.saveDataDET = function(){
        $(".btn-save-DET").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update-DET").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET');
                formData.append("bsid", $scope.temp.bsid);
                formData.append("batchid", $scope.temp.batchid);
                formData.append("studentIdList", $scope.chkStudentidList);
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
                $scope.getStudents();
                $scope.getBatchStudentsData();
                $scope.getStudentBatchesData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-DET').removeAttr('disabled').text('SAVE');
            $('.btn-update-DET').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET DATA =========== */
    $scope.getBatchStudentsData = function () {
        $('#SpinMainDataDET').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getBatchStudentsData',
                            'batchid':$scope.temp.batchid
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBatchStudentsData = data.data.success ? data.data.data : [];
            $('#SpinMainDataDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getAssignedData();

    
    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $('#ddlPlan').focus();
        $scope.temp.ddlPlan='';
        $scope.chkStudentidList=[];
        $scope.post.getStudents=[];
        // $('#ddlLocation, #ddlPlan').removeAttr('disabled');
    }


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'BSID': id.BSID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                //  console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getBatchStudentsData.indexOf(id);
                    $scope.post.getBatchStudentsData.splice(index, 1);
                    // console.log(data.data.message)
                    $scope.getStudents();
                    $scope.getStudentBatchesData();
                    // $scope.clearFormDET();
                    
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



    // ####################################################################################
    //                                      ADD TEACHER START (ADD TEACHER FOR TEST PROGREESS)
    // ####################################################################################
    $scope.checkTeacherList_Blank = (val,index) =>{
        // console.log($scope.chkStudentidList.filter(x=>x!=='0'));
        $scope.TeacherListLength = $scope.chkTeacheridList.filter(x=>x!=='0').length;
        // alert($scope.StudentListLength);
    }

    /* ========== GET TEACHERS =========== */
    $scope.getTeachers = function () {
        $scope.post.getTeachers=[];
        $scope.chkTeacheridList=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlPlan || $scope.temp.ddlPlan<=0 || !$scope.temp.batchid || $scope.temp.batchid<=0) return;
        $('.spinTeachers, .spinTeacherLT').show();
        $('#ddlPlan').attr('disabled','true');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getTeachers',
                            'ddlPlan':$scope.temp.ddlPlan,
                            'batchid':$scope.temp.batchid,
                            'ddlLocation':$scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getTeachers = data.data.success ? data.data.data : [];

            $('.spinTeachers, .spinTeacherLT').hide();
            $('#ddlPlan').removeAttr('disabled').focus();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getTeachers();
    /* ========== GET TEACHERS =========== */


    $scope.saveDataDET_TH = function(){
        $(".btn-save-DET-TH").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update-DET-TH").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET_TH');
                formData.append("btid", $scope.temp.btid);
                formData.append("batchid", $scope.temp.batchid);
                formData.append("teacherIdList", $scope.chkTeacheridList);
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
                $scope.getTeachers();
                $scope.getBatchTeachersData();
                $scope.getStudentBatchesData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-DET-TH').removeAttr('disabled').text('SAVE');
            $('.btn-update-DET-TH').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== GET DATA =========== */
    $scope.getBatchTeachersData = function () {
        $('#SpinMainDataDET_TH').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getBatchTeachersData',
                            'batchid':$scope.temp.batchid
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBatchTeachersData = data.data.success ? data.data.data : [];
            $('#SpinMainDataDET_TH').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    
    /* ============ Clear Form =========== */ 
    $scope.clearFormDET_TH = function(){
        $('#ddlPlan').focus();
        $scope.temp.ddlPlan='';
        $scope.chkTeacheridList=[];
        $scope.post.getTeachers=[];
        // $('#ddlLocation, #ddlPlan').removeAttr('disabled');
    }


    /* ========== DELETE =========== */
    $scope.deleteDET_TH = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'BTID': id.BTID, 'type': 'deleteDET_TH' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                //  console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getBatchTeachersData.indexOf(id);
                    $scope.post.getBatchTeachersData.splice(index, 1);
                    // console.log(data.data.message)
                    $scope.getStudents();
                    $scope.getStudentBatchesData();
                    // $scope.clearFormDET();
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }

    // ####################################################################################
    //                                      ADD TEACHER END
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