$postModule = angular.module("myApp", ["ngSanitize"]);
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
    $scope.Page = "STUDENT";
    $scope.PageSub = "SMS_HISTORY";

    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
    // $scope.txtFromDT=new Date($scope.FromDT_S).toLocaleDateString('sv-SE');

    $scope.temp.txtFromDT = new Date($scope.FromDT_S);
    $scope.temp.txtToDT = new Date();
    
    var url = 'code/SMS_HISTORY.php';



    // =============== Check Session =============
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
                $scope.SHOW_UNDELIVERED = data.data.locid==1 && (data.data.userrole=='SUPERADMIN' || data.data.userrole=='ADMINISTRATOR') ? true : false;
                // window.location.assign("dashboard.html");

                $scope.getMSGHistory();
                // $scope.getStudentCourseCoverage();
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
    // =============== Check Session =============





    /* ========== GET PLANS =========== */
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
                $scope.post.OUTGOING_MSG = data.data.success ? data.data.OUTGOING : [];
                $scope.post.INCOMING_MSG = data.data.success ? data.data.INCOMING : [];
                $scope.post.FAILED_UNDELIVERED_MSG = data.data.success ? data.data.FAILED_UNDELIVERED : [];
                $('#SpinMainData').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getMSGHistory(); --INIT
    /* ========== GET PLANS =========== */




    /* ========== OPEN REPLY SMS MODAL =========== */
    $scope.Reply_numData = [];
    $scope.openReplySmsModal = function (id) {
        $scope.Reply_numData = [];
        // console.log(id);
        $scope.REPLY_NUMBER = id.MOBILENO;

        $scope.Reply_numData = id;
    }
    // $scope.openReplySmsModal(); --INIT
    /* ========== OPEN REPLY SMS MODAL =========== */
    
    
    
    
    /* ========== SEND SMS =========== */
    $scope.sendSms = function(){
        // console.log($scope.Reply_numData);
        if(!confirm('Are you sure want to send this SMS!')) return;
        $(".btnSend").attr('disabled', 'disabled').html('<i class="fa fa-send"></i> Sending...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'sendSms');
                formData.append("REPLY_NUMBER", $scope.REPLY_NUMBER);
                formData.append("ddlTextFrom", $scope.temp.ddlTextFrom);
                formData.append("txtReplySms", $scope.temp.txtReplySms);
                formData.append("Reply_numData", JSON.stringify($scope.Reply_numData));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $timeout(()=>{$('#replySmsModal').modal('hide');},700);
                $scope.temp.REPLY_NUMBER = '';
                $scope.temp.ddlTextFrom = '';
                $scope.temp.txtReplySms = '';
                $scope.getMSGHistory();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnSend').removeAttr('disabled').html('<i class="fa fa-send"></i> SEND');
        });
    }
    /* ========== SEND SMS =========== */
    
    
    
    
    /* ========== MARK TO REPLY =========== */
    $scope.markReply = function(x){
        // console.log(x);
        if(!confirm('Are you sure want to set reply this SMS!')) return;
        $(".btn-markReply").attr('disabled', 'disabled').addClass('fa-spin');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'markReply');
                formData.append("MSGID", x.MSGID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getMSGHistory();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-markReply").removeAttr('disabled', 'disabled').removeClass('fa-spin');
        });
    }
    /* ========== MARK TO REPLY =========== */
    
    
    
    /* ========== CLEAR =========== */
    $scope.clearForm = function(){
        $scope.FromDT_S = new Date();
        $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
        $scope.temp.txtFromDT = new Date($scope.FromDT_S);
        $scope.temp.txtToDT = new Date();

        $scope.getMSGHistory();
    }
    /* ========== CLEAR =========== */




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENTS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%






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