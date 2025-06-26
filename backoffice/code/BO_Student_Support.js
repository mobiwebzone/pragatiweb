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
    $scope.PageSub = "ST_SUPPORT";
    $scope.temp.txtAMDate = new Date();
    
    var url = 'code/BO_Student_Support_code.php';
    var masterUrl = 'code/MASTER_API.php';


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
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
                $scope.LOC_ID=data.data.locid;
                // window.location.assign("dashboard.html");

                if($scope.userrole != "TSEC_USER")
                {
                    
                    $scope.getAllTicket();
                    $scope.getPlans();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
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

   

    // UPDATE STATUS
    $scope.UPDATE_STATUS = function(){
        // alert($scope.ticketid);
        $("#ddlStatus").attr('disabled', 'disabled');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'UPDATE_STATUS',
                            'TICKETID':$scope.ticketid,
                            'ddlStatus':$scope.temp.ddlStatus}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.getAllTicket();
                $scope.getSupportTicket($scope.ticketid);

                $scope.temp.ddlStatus='';

                $('#Status').modal('hide');
                // $scope.post.getComment=data.data.data;
            }
            $("#ddlStatus").removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    /* ========== GET Support Ticket =========== */
    $scope.getAllTicket = function () {
        $('.spinTicket').show();
        $('.refresh-ticket').addClass('fa-spin');
        $http({
            method: 'post',
            url: '../student_zone/code/Support_code.php',
            data: $.param({ 'type': 'getAllTicket',
                            'FOR':'ADMIN',
                            'txtFromDt':(!$scope.temp.txtFromDt || $scope.temp.txtFromDt == '') ? '' : $scope.temp.txtFromDt.toLocaleDateString('sv-SE'),
                            'txtToDt':(!$scope.temp.txtToDt || $scope.temp.txtToDt == '') ? '' : $scope.temp.txtToDt.toLocaleDateString('sv-SE')
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getAllTicket=data.data.data;
            }
            $('.spinTicket').hide();
            $('.refresh-ticket').removeClass('fa-spin');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    // $scope.getAllTicket(); --INIT


    $scope.ticketid=0;
    $scope.RCOUNT=0;
    /* ========== GET Support Ticket =========== */
    $scope.getSupportTicket = function (TICKETID) {
        if($scope.ticketid == 0){
            $('#loader').removeClass('LOADER');
        }
        $scope.ticketid = TICKETID;
        $('.refresh-comment').addClass('fa-spin');
        $http({
            method: 'post',
            url: '../student_zone/code/Support_code.php',
            data: $.param({ 'type': 'getSupportTicket','TICKETID':TICKETID,'FOR':'ADMIN'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getSupportTicket=data.data.ST_HTML;
                // alert(data.data.RCOUNT);
                $scope.RCOUNT = data.data.RCOUNT;
                // $timeout(function(){$('#txtNewComment').focus();},500);
            }
            $('#loader').addClass('LOADER');
            $('.refresh-comment').removeClass('fa-spin');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
   }
//    $scope.getSupportTicket();
    

    // ========= refresh comment ===========
    $interval(function () {
        if($scope.ticketid > 0){
            $scope.getSupportTicket($scope.ticketid);
        } 
    },5000);

    /* ========== save Comment =========== */
    $scope.saveComment = function (comment,ticketid) {
        // alert(comment +' - '+ ticketid);
        $(".btn-save-comment").attr('disabled', 'disabled');
        $(".btn-save-comment").text('COMMENT...');
        $http({
            method: 'post',
            url: '../student_zone/code/Support_code.php',
            data: $.param({ 'type': 'saveComment',
                            'userid':$scope.userid,
                            'TICKETID':ticketid,
                            'txtNewComment':comment,
                            'COMMENTBY':'ADMIN'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.getSupportTicket(ticketid);
                $scope.getAllTicket();
                // $scope.post.getComment=data.data.data;
            }
            $('.btn-save-comment').removeAttr('disabled');
            $(".btn-save-comment").text('COMMENT');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
   
   
   
   /* ========== save Reply =========== */
   $scope.INSERT = true;
   $scope.temp.TID=0;
    $scope.SaveReply = function (REPLY,MINDEX,CINDEX,PARENTTID,TICKETID) {
        // alert(REPLY +' - '+ MINDEX +' - '+ CINDEX +' - '+ PARENTTID);
        $(".btn-save-reply").attr('disabled', 'disabled');
        $(".btn-save-reply").text('REPLY...');
        if(REPLY != ''){
            $http({
                method: 'post',
                url: '../student_zone/code/Support_code.php',
                data: $.param({ 'type': 'SaveReply',
                                'userid':$scope.userid,
                                'TICKETID':TICKETID,
                                'txtReply':REPLY,
                                'PARENTTID':PARENTTID,
                                'REPLYBYID':$scope.userid,
                                'FOR_INSERT':$scope.INSERT,
                                'TID':$scope.temp.TID,
                                'REPLYBY':'ADMIN'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.getSupportTicket(TICKETID);
                    // $scope.post.getComment=data.data.data;
                }
                $('.replyBox'+MINDEX+''+CINDEX).slideToggle('fast','linear');
                $('#'+MINDEX+''+CINDEX).val('');
                $('.btn-save-reply').removeAttr('disabled');
                $(".btn-save-reply").text('REPLY');
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }

   }

    // Open Reply Box
    $scope.INSERT = true;
    $scope.openReplyBox = function (MINDEX,CINDEX,FOR,TID) {
        $scope.temp.TID=TID;
        console.log(`${MINDEX}||${CINDEX}||${FOR}||${TID}`);

        // if($('.replyBox'+MINDEX+''+CINDEX).is(":hidden"))
        // {
        //     $('.replyBox'+MINDEX+''+CINDEX).slideToggle('fast','linear');
        // }
        var final_state = $('.replyBox'+MINDEX+''+CINDEX).is(':hidden') ? 'hidden' : 'visible';
        if(final_state == 'hidden'){
            $('.replyBox'+MINDEX+''+CINDEX).slideDown('fast','linear');
        }
        if(FOR=='U'){
            $scope.INSERT = false;
            var CM = $('.viewcomment'+MINDEX+''+CINDEX).text();
            $('#'+MINDEX+''+CINDEX).val(CM)
            $('.btn-save-reply'+CINDEX).removeAttr('disabled');
            $('.btn-REP'+MINDEX+''+CINDEX).removeClass('shadow active')
            $('.btn-UPD'+MINDEX+''+CINDEX).addClass('shadow active');
        }else{
            $scope.INSERT = true;
            $('#'+MINDEX+''+CINDEX).val('');
            $('.btn-save-reply'+CINDEX).attr('disabled','disabled');
            $('.btn-UPD'+MINDEX+''+CINDEX).removeClass('shadow active');
            $('.btn-REP'+MINDEX+''+CINDEX).addClass('shadow active');
        }

        
    }








    /* =================================================================================== */
    /* ============================== CREATE STUDENT TICKET ============================== */
    /* =================================================================================== */
    
    
    /* ========== CREATE TICKET =========== */
    $scope.createTicket = function(){
        $(".btnCreate").attr('disabled', 'disabled').text('Creat...');
        // $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'createTicket');
                formData.append("ddlPlanCT", $scope.temp.ddlPlanCT);
                formData.append("ddlStudentCT", $scope.temp.ddlStudentCT);
                formData.append("ddlPriorityCT", $scope.temp.ddlPriorityCT);
                formData.append("textSubjectCT", $scope.temp.textSubjectCT);
                formData.append("txtCommentCT", $scope.temp.txtCommentCT);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $('#TicketModal').modal('hide');
                $scope.getAllTicket();
                // $timeout(function () {
                //     $scope.getSupportTicket(data.data.TICKETID);
                // },800);
                $scope.messageSuccess(data.data.message);
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnCreate').removeAttr('disabled').text('CREATE');
            // $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ========== CREATE TICKET =========== */


    
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
            $scope.post.getPlan = data.data.success?data.data.data:[];
            $('.spinPlan').hide();
            
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */


    /* ========== GET STUDENT BY PLAN =========== */
    $scope.getStudentByPlan = function () {
        $scope.post.getStudentByPlan=[];
        if(!$scope.LOC_ID || $scope.LOC_ID<=0)return;
        $('.spinStudent').show();
        $scope.temp.txtFirstName='';
        $scope.temp.txtLastName='';
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getStudentByPlanLocation','PLANID':$scope.temp.ddlPlanCT,'LOCID':$scope.LOC_ID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByPlan = data.data.success?data.data.data:[];
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlan();
    /* ========== GET STUDENT BY PLAN =========== */
    
    
    /* ========== CLEAR CREATE TICKET =========== */
    $scope.clearCreateTicket=function () {
        $scope.temp.ddlPlanCT = '';
        $scope.temp.ddlStudentCT = '';
        $scope.temp.ddlPriorityCT = '';
        $scope.temp.textSubjectCT = '';
        $scope.temp.txtCommentCT = '';
        $scope.post.getStudentByPlan = [];
    }
    /* ========== CLEAR CREATE TICKET =========== */




    /* =================================================================================== */
    /* ================================== DELETE TICKET ================================== */
    /* =================================================================================== */    
    
    /* ========== OPEN CANCEL TICKET MODAL =========== */
    $scope.temp.GET_TICKETID='';
    $scope.OpenCancelTicketModal=function(TICKETID){
        $scope.temp.GET_TICKETID=TICKETID;
        $timeout(()=>{$('#txtReasoneTC').focus();},1000);
    }
    /* ========== OPEN CANCEL TICKET MODAL =========== */



    /* ========== DELETE =========== */
    $scope.deleteTicket = function () {
        if($scope.temp.GET_TICKETID > 0){
            var r = confirm("Are you sure want to delete this record!");
            if (r == true) {
                $('.btnCancelTicket').attr('disabled','disabled').text('Save....');
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'type': 'deleteTicket','TICKETID': $scope.temp.GET_TICKETID,'txtReasoneTC':$scope.temp.txtReasoneTC }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        // $('#cancelModal').trigger({type:"click"});
                        $scope.messageSuccess(data.data.message);
                        $scope.getAllTicket();
                        $scope.ticketid=0;
                        $scope.RCOUNT=0;
                        $scope.post.getSupportTicket=[];
                        $scope.temp.GET_TICKETID='';
                        $scope.temp.txtReasoneTC='';
                        $('#CancelTicketModal').modal('hide');
                        $('.btnCancelTicket').removeAttr('disabled').text('Save');
                        
                    } else {
                        $scope.messageFailure(data.data.message);
                        $('.btnCancelTicket').removeAttr('disabled').text('Save');
                    }
                })
            }
        }
        else{
            $scope.messageFailure('Ticket ID Invalid.');
        }
    }



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

    $scope.closeTicket=function(){
        $scope.ticketid=0;
        $scope.RCOUNT=0;
        $scope.post.getSupportTicket=[];
        $('#collapseOne').collapse('show');
        document.getElementById('main-table').scrollIntoView()
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