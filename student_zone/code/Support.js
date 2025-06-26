
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


$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = 'SUPPORT';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    // $scope.jsVersion = Math.random();
    $scope.jsVersion = Math.floor(Math.random() * 90000) + 10000;
    
    var url = 'code/Support_code.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 




        // GET DATA
        $scope.init = function () {
            // Check Session
            // alert($scope.jsVersion);
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
                    $scope.USER_LOCATION=data.data.LOCATION;
                    $scope.REGID=data.data.data[0]['REGID'];
                    $scope.PLAN=data.data.data[0]['PLAN'];
                    $scope.GRADE=data.data.data[0]['GRADE'];
                    $scope.LOCID=data.data.data[0]['LOCATIONID'];
                    $scope.PLANID=data.data.data[0]['PLANID'];
                    $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                    $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];
    
                    $scope.ActivePlan = data.data.ActivePlan;
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


        /* ========== GET Support Ticket =========== */
        $scope.getAllTicket = function () {
            $('.spinTicket').show();
            $('.refresh-ticket').addClass('fa-spin');
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getAllTicket','FOR':'STUDENT',
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
                $('.refresh-ticket').removeClass('fa-spin');
                $('.spinTicket').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
    
       }
       $scope.getAllTicket();

    




    // =========== Open Ticket ============
        $scope.OpenTicket = function(){
            $(".btn-save").attr('disabled', 'disabled');
            $(".btn-save").text('GENERATING...');
            $(".btn-update").attr('disabled', 'disabled');
            $(".btn-update").text('Updating...');
            // alert($scope.temp.ddlCollege);
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'OpenTicket');
                    formData.append("textSubject", $scope.temp.textSubject);
                    formData.append("ddlPriority", $scope.temp.ddlPriority);
                    formData.append("txtComment", $scope.temp.txtComment);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
            
                    $scope.getAllTicket();
                    $timeout(function () {
                        $scope.getSupportTicket(data.data.TICKETID);
                    },800);
                    $scope.messageSuccess(data.data.message);
                    
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
                $('.btn-save').removeAttr('disabled');
                $(".btn-save").text('OPEN NEW TICKET');
                $('.btn-update').removeAttr('disabled');
                $(".btn-update").text('UPDATE');
            });
        }
    
    
    


    $scope.ticketid=0;
    /* ========== GET Support Ticket =========== */
    $scope.getSupportTicket = function (TICKETID) {
        $scope.ticketid = TICKETID;
        if($scope.ticketid == 0){
            $('#loader').removeClass('LOADER');
        }
        $('.refresh-comment').addClass('fa-spin');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSupportTicket','TICKETID':TICKETID,'FOR':'STUDENT'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getSupportTicket=data.data.ST_HTML;
                // $scope.TICKETID = data.data.data[0]['TICKETID'];

                // $scope.getComment();

                $('#collapseOne').collapse('hide');
                $timeout(function(){
                    document.getElementById('txtNewComment').focus();
                },500);
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
            url: url,
            data: $.param({ 'type': 'saveComment',
                            'userid':$scope.userid,
                            'TICKETID':ticketid,
                            'txtNewComment':comment,
                            'COMMENTBY':'STUDENT'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.getSupportTicket(ticketid);
                $scope.temp.txtNewComment0='';
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
    $scope.SaveReply = function (REPLY,MINDEX,CINDEX,PARENTTID,TICKETID) {
        $(".btn-save-reply"+CINDEX).attr('disabled', 'disabled');
        $(".btn-save-reply"+CINDEX).text('REPLY...');
        if(REPLY != ''){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'SaveReply',
                                'userid':$scope.userid,
                                'TICKETID':TICKETID,
                                'txtReply':REPLY,
                                'PARENTTID':PARENTTID,
                                'REPLYBYID':$scope.REGID,
                                'REPLYBY':'STUDENT'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.getSupportTicket(TICKETID);
                    // $scope.post.getComment=data.data.data;
                }
                $('.replyBox'+MINDEX+''+CINDEX).slideToggle('fast','linear');
                $('.btn-save-reply'+CINDEX).removeAttr('disabled');
                $(".btn-save-reply"+CINDEX).text('REPLY');
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }

   }
   



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'vrid': id.VRID,'txtCancelRemark':$scope.temp.txtCancelRemark, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    // $('#cancelModal').trigger({type:"click"});
                    $scope.clear();
                    $scope.getRFV();
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

    // Open Reply Box
    $scope.openReplyBox = function (MINDEX,CINDEX) {
        $('.replyBox'+MINDEX+''+CINDEX).slideToggle('fast','linear');
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
                    window.location.assign('login.html#!/login');
                }
                else {
                    window.location.assign('dashboard.html#!/dashboard');
                }
            },
            function (data, status, headers, config) {
                console.log('Not login Failed');
            })
    }

    $scope.closeTicket=function(){
        $scope.ticketid=0;
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