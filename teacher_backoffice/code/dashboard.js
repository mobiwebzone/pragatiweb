$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.isOnline = false;
    $scope.Page="HOME";
    $scope.minFromDT = new Date().toLocaleTimeString('sv-SE');
    
    var url = 'code/dashboard_code.php';

    // GET DATA
    $scope.init = function () {
        $scope.getOnlineStatus(); 
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
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;
                $scope.LOC_ID=data.data.locid;
                $scope.ALWAYS_ACTIVE=data.data.data[0]['ALWAYS_ACTIVE'];
    
                if($scope.LOC_ID > 0){
                    $scope.getAnnouncement();
                }
                // window.location.assign("dashboard.html");
                if($scope.LOC_ID > 0)$scope.getReferralMaster();
                $scope.getReferrals();
                $scope.getTotals(); 
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

    
    $scope.GetDetails=function (id){
        $scope.CLOSEDBYNAME = id.CLOSEDBYNAME;
        $scope.CLOSEDON = id.CLOSEDON;
        $scope.TASKSTATUS = id.TASKSTATUS;
        $scope.GET_TASKMGMTID=id.TASKMGMTID;
        $scope.ASSIGNEDTO_NAME=id.ASSIGNEDTO_NAME;

        $timeout(function () {
            document.getElementById('ReviewTab').scrollIntoView({ behavior: 'smooth' });
            $('#txtReview').focus();
        }, 300);

        $scope.getTaskTrackDetails();
    }

    
    
    $scope.saveTaskTrackingDetails = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: '../backoffice/code/dashboard_code.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveTaskTrackingDetails');
                formData.append("TTDETID", $scope.temp.TTDETID);
                formData.append("userid", $scope.userid);
                formData.append("TASKMGMTID", $scope.GET_TASKMGMTID);
                formData.append("txtReview", $scope.temp.txtReview);
                formData.append("txtLinkReview", $scope.temp.txtLinkReview);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
              
                $scope.getTaskTrackDetails();
                $scope.temp.TTDETID=0;
                $scope.temp.txtReview='';
                $scope.temp.txtLinkReview='';

                
                $timeout(function () {
                    var container = document.querySelector('.chat-container'); // replace with your actual container class or element
                    container.scrollTop = 0;
                }, 0);
            }
            else {
                if(data.data.TASK_CLOSED){
                    $scope.clearTaskTrack_Detials();
                    $scope.TASKSTATUS = 'CLOSED';
                    $scope.getTotals();
                    // $scope.GetDetails($scope.SELECTED_TASK_DATA);
                }
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }




    /* ========== GET Task Tracking Deatils =========== */
    $scope.getTaskTrackDetails = function (){
    $scope.SpinTaskTrack=true;
        $http({
            method: 'post',
            url: '../backoffice/code/dashboard_code.php',
            data: $.param({ 'type': 'getTaskTrackDetails','TASKMGMTID':$scope.GET_TASKMGMTID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTaskTrackDetails = data.data.data;
            $scope.SpinTaskTrack=false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTaskTrackDetails(); --INIT
    /* ========== GET Task Tracking Deatils =========== */

    $scope.clearTaskReviewFiels = function(){
        $scope.GET_TASKMGMTID=0
        $scope.post.getTaskTrackDetails = [];
        $scope.clearTaskTrack_Detials();
    }

    $scope.clearTaskTrack_Detials=function(){
        $scope.temp.TTDETID=0;
        $scope.temp.txtReview='';
        $scope.temp.txtLinkReview='';
    }




    /* ========== Task Closed =========== */
    $scope.ClosedTask = function (id) {
        var r = confirm("Are you sure want to close this Task!");
        if (r == true) {
            $http({
                method: 'post',
                url: '../backoffice/code/dashboard_code.php',
                data: $.param({ 'TASKMGMTID': id.TASKMGMTID, 'type': 'ClosedTask','CLOSEDBY':$scope.userrole }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                    // console.log(data.data)
                if (data.data.success) {
                    // var index = $scope.post.TotalTaskView.indexOf(id);
                    // $scope.post.TotalTaskView.splice(index, 1);
                    $scope.getTotals();
                    $scope.GET_TASKMGMTID=0;
                    console.log(data.data.message)
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    
    
    /* ========== Get Totals =========== */
    $scope.getTotals = function () {    
        $scope.spinTaskTo = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTotals','LOC_ID':$scope.LOC_ID }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.post.TotalTaskView = data.data.TotalTaskView;
                $scope.TotalTaskReviewCount = data.data.TotalTaskReviewCount['TOTAL'];
            }
            $scope.spinTaskTo = false;
        },
        function (data, status, headers, config) {
            $scope.spinTaskTo = false;
            console.log('Login Failed');
        })
    }
    // $scope.getTotals(); --INIT
    /* ========== Get Totals =========== */

    /* =========== Get TeacherAtt =========== */ 
    $scope.getTeacherAtt = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherAtt'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.post.getTeacherAtt = data.data.data;
            }
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    $scope.getTeacherAtt();



    /* =========== Get Announcement =========== */ 
    $scope.getAnnouncement = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAnnouncement','LOC_ID':$scope.LOC_ID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.post.getAnnouncement = data.data.data;
            }
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // $scope.getAnnouncement();



    /* =========== GET REFERRAL MASTER =========== */ 
    $scope.getReferralMaster = function () {
        $('.spinRef').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Referral_Master.php',
            data: $.param({ 'type': 'getReferralMaster','LOCID':$scope.LOC_ID}),
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
                url: '../student_zone/code/dashboard_code.php',
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'saveReferral');
                    formData.append("REFBYID", $scope.userid);
                    formData.append("REF_BY", "TEACHER");
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
                // console.log(data.data);
                if (data.data.success) {
                    $scope.getReferrals();
                    $scope.messageSuccess(data.data.message);
                    $('#referralModal').modal('hide');
                    $scope.clearReferralForm();
                    // $scope.clearForm();
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
            url: '../student_zone/code/dashboard_code.php',
            data: $.param({ 'type': 'getReferrals','REF_BY':'TEACHER','REF_BYID':$scope.userid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReferrals = data.data.success ? data.data.data : [];
            $('.spinReferrals').hide();
        },
        function (data, status, headers, config) {
            console.log('Url Failed');
        })
    }
    // $scope.getReferrals(); --INIT




    /* ========== ALWAYS ONLINE =========== */
    $scope.setAlwaysOnline = function(){
        // return;
        // alert($scope.temp.chkAlwaysActive)
        $('.alwayAct').attr('disabled',true);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'setAlwaysOnline');
                formData.append("chkAlwaysActive", $scope.temp.chkAlwaysActive);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getOnlineStatus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.alwayAct').attr('disabled',false);
        });
    }


    /* ========== SAVE ONLINE =========== */
    $scope.saveOnline = function(){
        $(".btn-save-onoff").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update-onoff").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        console.log($scope.temp.txtFromDT.toLocaleString('sv-SE'),'/',$scope.temp.txtToDT.toLocaleString('sv-SE'))
        // return;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveOnline');
                formData.append("tosid", $scope.temp.tosid);
                formData.append("LOC_ID", $scope.LOC_ID);
                formData.append("txtFromDT", $scope.temp.txtFromDT.toLocaleString('sv-SE'));
                formData.append("txtToDT", $scope.temp.txtToDT.toLocaleString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.temp.tosid = data.data.GET_TOSID;
                $scope.getOnlineStatus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-onoff').removeAttr('disabled').text('SAVE');
            $('.btn-update-onoff').removeAttr('disabled').text('UPDATE');
        });
    }



    $scope.gotoOffline = function(){
        $(".btn-close-onoff").attr('disabled', 'disabled').text('Wait...');
        var currentDate = new Date();
        // Subtract 1 minute from the current date
        currentDate.setMinutes(currentDate.getMinutes() - 1);
        console.log(currentDate);

        // return;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'gotoOffline');
                formData.append("tosid", $scope.temp.tosid);
                formData.append("txtToDT", currentDate.toLocaleString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.temp.tosid = '';
                var todayD = new Date().toLocaleDateString('sv-SE');
                var todayT = new Date().toLocaleTimeString('sv-SE');
                $scope.temp.txtFromDT = new Date(`${todayD}T${todayT}`);
                $scope.temp.txtToDT = new Date(`${todayD}T${todayT}`);
                
                $scope.getOnlineStatus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-close-onoff').removeAttr('disabled').text('OFFLINE');
        });
    }



    /* =========== GET ONLINE STATUS =========== */ 
    $scope.itsChecking = false;
    $scope.getOnlineStatus = function () {
        $('.spinOnline').show();
        // $('#btnStatus').attr('disabled',true);
        $scope.itsChecking = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getOnlineStatus'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.ALWAYS_ACTIVE = data.data.ALWAYS_ACTIVE;
            $scope.isOnline = $scope.ALWAYS_ACTIVE==1 ? 'ONLINE' : '';
            $scope.temp.chkAlwaysActive = Number($scope.ALWAYS_ACTIVE);

            if(data.data.success){
                $scope.post.getOnlineStatus = data.data.data;
                if($scope.ALWAYS_ACTIVE==1){
                    $scope.isOnline = 'ONLINE';
                    $scope.temp.tosid = 0;
                }else{
                    $scope.isOnline = $scope.post.getOnlineStatus['ONLINE_STATUS'];
                    $scope.temp.tosid = $scope.isOnline ? $scope.post.getOnlineStatus['TOSID'] : 0;
                }

                // alert($scope.ALWAYS_ACTIVE)
                // $scope.temp.txtFromDT = new Date('2023-01-01T'+$scope.post.getOnlineStatus['ONTIME_SET']);
                // $scope.temp.txtToDT = new Date('2023-01-01T'+$scope.post.getOnlineStatus['OUTTIME_SET']);
                $scope.ONLINE_FTIME = $scope.post.getOnlineStatus['ONTIME'];
                $scope.ONLINE_TTIME = $scope.post.getOnlineStatus['OUTTIME'];
                // $scope.minToDT = $scope.post.getOnlineStatus['ONTIME_SET'];
            }else{
                $scope.post.getOnlineStatus = [];
                // $scope.isOnline = false;
                $scope.temp.tosid = '';
                // $scope.temp.txtFromDT = '';
                // $scope.temp.txtToDT = '';
                $scope.ONLINE_FTIME = '';
                $scope.ONLINE_TTIME = '';
                // $scope.minToDT = '';
            }

            // TOP 5
            $scope.post.getOnlineStatusTop5 = data.data.success2 ? data.data.data2 : [];
            $('.spinOnline').hide();
            // $('#btnStatus').attr('disabled',false);
            $scope.itsChecking = false;
        },
        function (data, status, headers, config) {
            console.log('Url Failed');
        })
    }
    // $scope.getOnlineStatus(); --INIT

    var fetchDataInterval = $interval(()=>{
        if(!$scope.itsChecking) $scope.getOnlineStatus();
    },5000);

    // Clean up the interval when the controller is destroyed
    $scope.$on('$destroy', function () {
        $interval.cancel(fetchDataInterval);
    });

    $scope.setOnlineTime = function () {
        // alert($scope.temp.tosid);
        if($scope.isOnline){
            // $scope.temp.tosid = $scope.post.getOnlineStatus['TOSID'];
            $scope.temp.txtFromDT = new Date('2023-01-01T'+$scope.post.getOnlineStatus['ONTIME_SET']);
            $scope.temp.txtToDT = new Date('2023-01-01T'+$scope.post.getOnlineStatus['OUTTIME_SET']);
            $scope.minToDT = $scope.post.getOnlineStatus['ONTIME_SET'];
        }else{
            var todayD = new Date().toLocaleDateString('sv-SE');
            var todayT = new Date().toLocaleTimeString('sv-SE');
            $scope.temp.txtFromDT = new Date(`${todayD}T${todayT}`);
            $scope.temp.txtToDT = new Date(`${todayD}T${todayT}`);
            // $scope.temp.tosid = '';
            $scope.minToDT = '';
        }
    }


    $scope.setTomin = function(){
        $scope.minToDT = '';
        if(!$scope.temp.txtToDT || $scope.temp.txtToDT=='')return;
        $scope.minToDT = $scope.temp.txtToDT.toLocaleTimeString('sv-SE');
    }
    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: '../student_zone/code/logout.php',
            data: $.param({ 'type': 'logout' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                window.location.assign('login.html#!/login');
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
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});