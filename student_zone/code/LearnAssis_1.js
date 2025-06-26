$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","angular.filter",'ngDraggable']);
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
    $scope.formTitle = '';
    $scope.Page = 'REQUEST';
    $scope.PageSub = 'RFL';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];

    $scope.onDragComplete=function(){
        console.log("drag success");
     }
     $scope.onDropComplete=function(data,m){
        $scope[m] = data;
        console.log("drop success data : "+ data+' / '+m);
        // $scope.saveAnswer(TESTID,AID,ANS,QUE_TYPE,MINDEX);
     }
    
    var url = 'code/LearnAssis.php';

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
                    $scope.USER_LOCATION=data.data.LOCATION;
                    $scope.REGID=data.data.data[0]['REGID'];
                    $scope.PLAN=data.data.data[0]['PLAN'];
                    $scope.GRADE=data.data.data[0]['GRADE'];
                    $scope.LOCID=data.data.data[0]['LOCATIONID'];
                    $scope.PLANID=data.data.data[0]['PLANID'];
                    $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                    $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];
    
                    if($scope.REGID != undefined){
                        $scope.getGradeSubject();
                    }
                    
                    $scope.ActivePlan = data.data.ActivePlan;
                    if(data.data.ActivePlan == 'YES'){
                        window.location.assign('dashboard.html#!/dashboard');
                    }
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


    
    /* ========== GET GRADE & SUBJECT =========== */
    $scope.getGradeSubject = function () {
        $('.GradeExist').hide();
        $('.spinSubjectList').slideDown();
        $scope.post.getGradeSubject=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getGradeSubject','REGID':$scope.REGID}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
            if(data.data.success){
                $scope.post.GRADE_SUBJECT_LIST=data.data.GRADE_SUBJECT_LIST;
            }else{
                $('.GradeExist').text('Grades Not Found.').fadeIn();
            }
            $('.spinSubjectList').slideUp();
            // $scope.post.getAttendance = data.data.data;
            // $scope.temp.txtDate=new date
            // $scope.RegID = data.data.RegID;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
    //    $scope.getGradeSubject();


    /* ========== GET TOPICS =========== */
    $scope.getTopics = function (GRADEID,SUBID,e) {
        $scope.GRADEID = GRADEID;
        $scope.SUBID = SUBID;
        $('.GradeList').find('.active').removeClass('active');
        angular.element(e.target).addClass('active');

        $('.TopicExist').hide();
        $('.spinTopics').slideDown();
        // $scope.TOPIC_LIST='';
        $scope.post.getTopics = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTopics',
            'LOCID':$scope.LOCID,
            'GRADEID':GRADEID,
            'SUBID':SUBID,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if(data.data.success){
            $scope.post.getTopics = data.data.data;
        }else{
            $('.TopicExist').text('Topics Not Found.').fadeIn();
        }
        $('.spinTopics').slideUp();
        // $scope.post.getAttendance = data.data.data;
        // $scope.temp.txtDate=new date
        // $scope.RegID = data.data.RegID;
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })

    }


    /* ========== GET TOPICS =========== */
    $scope.getTopicsOld = function (GRADEID,SUBID,e) {
        $('.GradeList').find('.active').removeClass('active');
        angular.element(e.target).addClass('active');

        $('.TopicExist').hide();
        $('.spinTopics').slideDown();
        $scope.TOPIC_LIST='';
        $http({
            method: 'post',
            url: 'code/LearnAssisTopicList.php',
            data: $.param({ 'type': 'getTopics',
            'LOCID':$scope.LOCID,
            'GRADEID':GRADEID,
            'SUBID':SUBID,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
            if(data.data.data.success){
                $scope.TOPIC_LIST=data.data.data.data;
            }else{
                $('.TopicExist').text('Topics Not Found.').fadeIn();
            }
            $('.spinTopics').slideUp();
            // $scope.post.getAttendance = data.data.data;
            // $scope.temp.txtDate=new date
            // $scope.RegID = data.data.RegID;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
    //    $scope.getTopics();


    /* ========== GET SLIDES =========== */
    $scope.TOPIC = '';
    $scope.getSlides = function (TOPICID,TOPIC,e) {
        $('.btnNext, .btnPrev').attr('disabled','disabled');
        $scope.INDEX = 0;
        $scope.TOPIC_NAME = TOPIC;
        $scope.post.getSlides=[];

        $('.TopicList').find('.active').removeClass('active');
        angular.element(e.target).addClass('active');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSlides','TOPICID':TOPICID}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
            $scope.post.getSlides=data.data.data;
            // $scope.Next_Previous('');
            $scope.FinalAssifnmentData = $scope.post.getSlides[0];
            $scope.SLIDEID = $scope.FinalAssifnmentData['SLIDEID'];
            $scope.getSlideContent($scope.SLIDEID);
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
    }
    //    $scope.getSlides();


    // ============== NEXT PREVIOUS BUTTON SLIDES START ==============
    $scope.currentPageIndex_SL = 0;
    $scope.nextPageSL = function() {
        $scope.currentPageIndex_SL++;
        if ($scope.currentPageIndex_SL >= $scope.post.getSlides.length) {
            $scope.currentPageIndex_SL = 0;
        }
        $scope.FinalAssifnmentData = $scope.post.getSlides[$scope.currentPageIndex_SL];
        $scope.SLIDEID = $scope.FinalAssifnmentData['SLIDEID'];
        $scope.getSlideContent($scope.SLIDEID);
    };
    $scope.previousPageSL = function() {
        $scope.currentPageIndex_SL--;
        if ($scope.currentPageIndex_SL < 0) {
            $scope.currentPageIndex_SL = $scope.content.length - 1;
        }
        $scope.FinalAssifnmentData = $scope.post.getSlides[$scope.currentPageIndex_SL];
        $scope.SLIDEID = $scope.FinalAssifnmentData['SLIDEID'];
        $scope.getSlideContent($scope.SLIDEID);
    };
    // ============== NEXT PREVIOUS BUTTON SLIDES END ==============
    
    
    // $scope.Next_Previous = function(NP){
    //     $('.spinSlideContent').fadeIn();
    //     $scope.INDEX = NP == 'NEXT' ? $scope.INDEX+1 : NP == 'PREV' ? $scope.INDEX-1 : 0;
    //     $('.btnNext, .btnPrev').attr('disabled','disabled');

    //     $scope.SLIDE_LENGTH = $scope.post.getSlides ? $scope.post.getSlides.length : 0;
    //     if($scope.SLIDE_LENGTH<=0)return;
        
    //     const SHOW_HIDE= $scope.SLIDE_LENGTH-$scope.INDEX;
    //     // console.log(SHOW_HIDE +' / '+$scope.SLIDE_LENGTH+' / '+$scope.INDEX);


    //     // NEXT
    //     if(NP=='NEXT' || NP==''){
    //         if(SHOW_HIDE == 1){
    //             $('.btnPrev').removeAttr('disabled');
    //             $('.btnNext').attr('disabled','disabled');
    //         }else if(SHOW_HIDE == $scope.SLIDE_LENGTH){
    //             $('.btnPrev').attr('disabled','disabled');
    //             $('.btnNext').removeAttr('disabled');
    //         }else{
    //             $('.btnNext, .btnPrev').removeAttr('disabled');
    //         }
    //     }

    //     // PREVIOUS
    //     if(NP=='PREV'){
    //         if(SHOW_HIDE == $scope.SLIDE_LENGTH){
    //             $('.btnNext').removeAttr('disabled');
    //             $('.btnPrev').attr('disabled','disabled');
    //         }else{
    //             $('.btnNext, .btnPrev').removeAttr('disabled');
    //         }
    //     }
    //     $scope.getSlideContent($scope.post.getSlides[$scope.INDEX]['SLIDEID']);

    //     // console.log($scope.SLIDE_LENGTH);
    //     // console.log($scope.INDEX);

    // }

    /* ========== GET SLIDE CONTENT =========== */
    $scope.getSlideContent = function (SLIDEID) {
        // $scope.post.getSlideContent=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSlideContent','SLIDEID':SLIDEID}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
            $scope.post.getSlideContent=data.data.success ? data.data.data : [];
            $('.spinSlideContent').fadeOut();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
    //    $scope.getSlideContent();

    $scope.clearSelectedSlide=()=>{
        $('.TopicList').find('.active').removeClass('active');
        $scope.post.getSlideContent=[];
    }

    // #####################################################################
    //                            ASSIGNMENTS START
    // #####################################################################
    $scope.mcqOPTIONS =[];
    $scope.typeinText =[];
    $scope.currentPageIndex_AS = 0;
    $scope.FinalAssifnmentData=[];
    /* ======= NEXT PREV BUTTON ======= */ 
    $scope.nextPage = function() {
        $scope.mcqOPTIONS =[];
        $scope.typeinText =[];

        $scope.currentPageIndex_AS++;
        if ($scope.currentPageIndex_AS >= $scope.post.getAssignmentsQuestions.length) {
            $scope.currentPageIndex_AS = 0;
        }
        $scope.FinalAssifnmentData = $scope.post.getAssignmentsQuestions[$scope.currentPageIndex_AS];
        $scope.MQUEID = $scope.FinalAssifnmentData['MQUEID'];
        $scope.getAssQueOptions($scope.MQUEID);
    };
    $scope.previousPage = function() {
        $scope.currentPageIndex_AS--;
        if ($scope.currentPageIndex_AS < 0) {
            $scope.currentPageIndex_AS = $scope.content.length - 1;
        }
        $scope.FinalAssifnmentData = $scope.post.getAssignmentsQuestions[$scope.currentPageIndex_AS];
        $scope.MQUEID = $scope.FinalAssifnmentData['MQUEID'];
        $scope.getAssQueOptions($scope.MQUEID);
    };

    /* ======= FINAL TEST START ======= */ 
    $scope.startAssis = function(TOPICID,TOPIC){
        $scope.TOPIC_NAME = TOPIC;
        $scope.TOPICID=TOPICID;
        $scope.post.getAssignmentsQuestions = [];
        // var r = confirm("Are you sure want to start this test!");
        // if (r == true) {
        //     $scope.FINALSTART = true;
        //     $scope.getAssQueOptions($scope.MQUEID);
        // }
    }

    /* ======= GET ASSIGNMENTS ======= */ 
    $scope.REVIEW = false;
    $scope.getAssignmentsQuestions = function (){
        $('.spinMQue').show();
        $scope.currentPageIndex_AS = 0;
        $scope.mcqOPTIONS =[];
        $scope.typeinText =[];
        // $scope.TOPIC_NAME = TOPIC;
        // $scope.TOPICID=TOPICID;
        // $scope.post.getAssignmentsQuestions = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAssignmentsQuestions',
            'REGID':$scope.REGID,
            'LOCID':$scope.LOCID,
            'GRADEID':$scope.GRADEID,
            'SUBID':$scope.SUBID,
            'TOPICID':$scope.TOPICID,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
            if(data.data.success){
                $scope.post.getAssignmentsQuestions = data.data.data;
                $scope.REVIEW = data.data.REVIEW;
                $scope.FinalAssifnmentData = $scope.post.getAssignmentsQuestions[0];
                $scope.MQUEID = $scope.FinalAssifnmentData['MQUEID'];
                $scope.getAssQueOptions($scope.MQUEID);
                
                // console.log($scope.FinalAssifnmentData);
                $('#recordNotFoundTEST').html('');
            }
            else{
                $('#recordNotFoundTEST').html('<h3 class="text-center text-danger"><b>Test Not Found</b></h3>');
            }
            $('.spinMQue').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ======== GET ASSIGNMENTS MAIN QUESTIONS & OPTIONS ======= */ 
    $scope.dragModalNameList =[];
    $scope.getAssQueOptions = function (MQUEID){
        $scope.dragModalNameList =[];
        $timeout(()=>{
            $('.spinQuestions').show()
        },10);
        $scope.post.getAssQueOptions = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAssQueOptions',
            'MQUEID':$scope.MQUEID,
            'REVIEW':$scope.REVIEW
        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getAssQueOptions = data.data.success ? data.data.data : [];
            $scope.dragModalNameList = !data.data.dragModalNameList ? [] : data.data.dragModalNameList;
            $scope.$watch("post.getAssQueOptions", function() {
                $timeout(()=>{
                    $('.spinQuestions').hide();
                },10);
            }, true);
            $timeout(()=>{
                $('#typein0').focus();
            },1000);
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    /* ======== SAVE ANSWER ======= */ 
    $scope.saveAnswer = function (TESTID,AID,ANS,QUE_TYPE,MINDEX){
        if($scope.REVIEW)return;
        console.log(QUE_TYPE);
        $scope.ANS=JSON.stringify(ANS);
        if(QUE_TYPE == 'TYPE IN' || QUE_TYPE == 'DRAG & DROP'){
            // var Fans = 
            var pera = document.querySelector('#pera'+MINDEX);
            var ngModelElements = pera.querySelectorAll('[ng-model]');
            var ngModelValues = [];
            for (var i = 0; i < ngModelElements.length; i++) {
                var ngModelElement = ngModelElements[i];
                $scope.ngModelName = ngModelElement.getAttribute('ng-model');
                // console.log($('#'+$scope.ngModelName).val());
                // var ngModelValue = $scope.$eval($scope.ngModelName);
                var ngModelValue = $('#'+$scope.ngModelName).val();

                ngModelValues.push(ngModelValue);
            }
            console.log(ngModelValues);
            $scope.ANS=JSON.stringify(ngModelValues);
            // $scope.ANS=$scope.ANS.replaceAll(',',';');
            console.log($scope.ANS);
        }else if(QUE_TYPE == 'GRID'){
            // var Fans = 
            var table = document.querySelector('#grid'+MINDEX);
            console.log(table);
            var ngModelElements = table.querySelectorAll('[ng-model]');

            var ngModelValues = [];
            for (var i = 0; i < ngModelElements.length; i++) {
                var ngModelElement = ngModelElements[i];
                $scope.ngModelName = ngModelElement.getAttribute('ng-model');
                // console.log($('#'+$scope.ngModelName).val());
                // var ngModelValue = $scope.$eval($scope.ngModelName);
                var ngModelValue = $('#'+$scope.ngModelName).val();

                ngModelValues.push(ngModelValue);
            }
            console.log(ngModelValues);
            $scope.ANS=JSON.stringify(ngModelValues);
            // $scope.ANS=$scope.ANS.replaceAll(',',';');
            
        }
        // return;
        console.log($scope.ANS);

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveAnswer');
                formData.append("TESTID", TESTID);
                formData.append("AID", AID);
                formData.append("ANS", $scope.ANS);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    /* ======== RE-TEST ======= */ 
    $scope.resetTest = function(){
        var r = confirm("RESET will remove all previous marked answers and results permanently with to continue [RESET]?");
        if (r == true) {
            $scope.post.getAssignmentsQuestions=[];
            $scope.currentPageIndex_AS = 0;
            $scope.mcqOPTIONS =[];
            $scope.typeinText =[];
            $scope.REVIEW = false;

            $('.btn-reset').attr('disabled','disabled');
            $('.spinResetTest').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'resetTest',
                'REGID':$scope.REGID,
                'LOCID':$scope.LOCID,
                'GRADEID':$scope.GRADEID,
                'SUBID':$scope.SUBID,
                'TOPICID':$scope.TOPICID,
            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                if(data.data.success){
                    $scope.messageSuccess(data.data.message);
                    // $scope.getAssignmentsQuestions($scope.TOPICID,$scope.TOPIC_NAME);
                    // console.log($scope.FinalAssifnmentData);
                }
                $('.btn-reset').removeAttr('disabled');
                $('.spinResetTest').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }

    /* ======== CLOSE TEST ======== */ 
    $scope.closeTest = function(VAL){
        if(!$scope.REVIEW && $scope.post.getAssignmentsQuestions.length>0){
            var r = confirm(`Are you sure want to ${VAL} this test!`);
            if (r == true) {
                if($scope.dragModalNameList.length>0){
                    $scope.dragModalNameList.forEach(element => {
                        // console.log(element);
                        $scope[element] = '';
                    });
                }
                // $scope.post.getAssQueOptions = [];
                // $scope.dragDropText01 = '';

                $('#assignmentModal').modal('hide');
                $scope.REVIEW = false;
                $scope.TOPIC_NAME = '';
                $scope.TOPICID=0;
                $scope.post.getAssignmentsQuestions = [];

            }
        }else{
            $('#assignmentModal').modal('hide');
            $scope.REVIEW = false;
            $scope.TOPIC_NAME = '';
            $scope.TOPICID=0;
            $scope.post.getAssignmentsQuestions = [];
        }
    }
    $scope.isCursorInsideInput = false;
    
    // #####################################################################
    //                            ASSIGNMENTS END
    // #####################################################################
    
    
    
    
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