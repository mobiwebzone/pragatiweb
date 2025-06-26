$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","angular.filter","chart.js",'ngDraggable']);
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
    $scope.Tab_Title='TEST PROGRESS';
    $scope.Page = 'REQUEST';
    $scope.PageSub = 'RFL';
    $scope.PAGEFOR = 'STUDENT';
    $scope.isBO = false;
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.onExcel = false;
    $scope.onExcelClick = function(){
        $scope.onExcel = true;
        $timeout(()=>{$scope.onExcel = false;},1000);
    }


    var url = 'code/TestProgress.php';
    var urlBO = '../backoffice/code/Student_progress_Report.php';
    var StudentAssiUrl = 'code/LearnAssis.php';
    var masterUrl = '../backoffice/code/MASTER_API.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 



    // GET DATA
    $scope.init = function () {
        // Check Session
        $('#progress-tab,#wrongAns-tab,#analysis-tab,#course-tab').attr('disabled','disabled');
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
                    $('#progress-tab,#wrongAns-tab,#analysis-tab,#course-tab').removeAttr('disabled');
                    $scope.getStudentTest();
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


    
    /* ========== GET STUDENT TEST PROGRESS =========== */
    $scope.getStudentTest = function () {
        if(!$scope.REGID || $scope.REGID==0) return;
        $scope.Tab_Title='TEST PROGRESS';
        $scope.spinMainDT = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentTest','REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentTest=data.data.success ? data.data.data : [];
            $scope.post.CHART_DATASET = data.data.CHART_DATASET;
            var DATA_LEN = data.data.CHART_DATASET.length;

            $timeout(()=>{
                if(DATA_LEN>0){
                    for($i=0;$i<DATA_LEN;$i++){
                        const DATE = $scope.post.CHART_DATASET[$i]['DATE'];
                        const RAW = $scope.post.CHART_DATASET[$i]['RAW'];
                        const SCALE = $scope.post.CHART_DATASET[$i]['SCALE'];
                        const TOTAL_Q = $scope.post.CHART_DATASET[$i]['TOTAL_Q'];
    
                        var containerId = `#canvasContainer${$i}`;
                        console.log(containerId);
                        var container = document.querySelector(containerId);
                        container.innerHTML='';
    
                        // CREATE CANVAS
                        var canvas = document.createElement('canvas');
                        var id_name = `canvas${$i}`;
                        canvas.setAttribute('id', id_name);
                        // canvas.setAttribute('class', 'mb-4');
                        canvas.setAttribute('height', '100');
    
                        // SET ELEMNT MAIN DIV
                        // container.appendChild(head)
                        container.appendChild(canvas)
    
                        var ctx = document.getElementById(id_name).getContext("2d");
                        let canvasID = eval('let ' + 'ST_BarChart' + $i);
                        if (canvasID) {
                            canvasID.destroy()
                        }
    
                        var data = {
                            labels: DATE,
                            datasets: [
                            //     {
                            //     label: "Total",
                            //     backgroundColor: "rgba(253,180,92,0.2)",
                            //     borderColor: "rgba(253,180,92,1)",
                            //     data: $scope.TOTALS
                            // },
                            {
                                label: "Total Correct Answer",
                                backgroundColor: "rgba(247,70,74,0.2)",
                                borderColor: "rgba(247,70,74,1)",
                                data: RAW
                            },
                            {
                                label: "Total Questions",
                                backgroundColor: "rgba(148,159,177,0.2)",
                                borderColor: "rgba(148,159,177,1)",
                                data: TOTAL_Q
                            }]
                        };
    
                        var opt = {
                            // events: false,
                            tooltips: {
                                enabled: true
                            },
                            hover: {
                                animationDuration: 0
                            },
                            animation: {
                                duration: 500,
                                onComplete: function () {
                                    
                                    var chartInstance = this.chart,
                                    ctx = chartInstance.ctx;
                                    ctx.fillStyle = '#00000080';
                                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';
                        
                                    this.data.datasets.forEach(function (dataset, i) {
                                        var meta = chartInstance.controller.getDatasetMeta(i);
                                        meta.data.forEach(function (bar, index) {
                                            var data = dataset.data[index];                            
                                            ctx.fillText(data, bar._model.x, bar._model.y - 5);
                                        });
                                    });
                                }
                            },
                            legend: {
                                display: true,
                                labels: {
                                    fontColor: '#000000'
                                }
                            },
                        };
        
        
                        // SATT_BarChart = new Chart(ctx, {
                        canvasID = new Chart(ctx, {
                        type: 'line',
                        data: data,
                        options: opt,
                        plugins: [{ //leagend spacing bottom
                            beforeInit: function(chart, options) {
                              chart.legend.afterFit = function() {
                                this.height = this.height + 15;
                              };
                            }
                          }]
                        });
                    }
                }
            },1000);
            $scope.spinMainDT = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.spinMainDT = false;
        })

    }
    //    $scope.getStudentTest();





    /* ========== GET STUDENT WRONG ANSWERS =========== */
    $scope.getStudentWrongAnswers = function () {
        if(!$scope.REGID || $scope.REGID==0) return;
        $scope.Tab_Title='WRONG ANSWERS';
        $scope.spinWA = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentWrongAnswers','REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getStudentWrongAnswers=data.data.success ? data.data.data : [];
            $scope.spinWA = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.spinWA = false;
        })

   }
    //    $scope.getStudentWrongAnswers();





    /* ========== GET STUDENT TOPICWISE ANALYSIS =========== */
    $scope.getStudentTopicWiseAnalysis = function () {
        if(!$scope.REGID || $scope.REGID==0) return;
        $scope.Tab_Title='TOPIC-WISE ANALYSIS';
        $scope.spinTWA = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentTopicWiseAnalysis','REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentTopicWiseAnalysis=data.data.success ? data.data.data : [];
            $scope.spinTWA = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.spinTWA = false;
        })

   }
    //    $scope.getStudentTopicWiseAnalysis();





    /* ========== GET STUDENT COURSE COVERAGE =========== */
    $scope.getStudentCourseCoverage = function () {
        if(!$scope.REGID || $scope.REGID==0) return;
        $scope.Tab_Title='COURSE COVERAGE';
        $scope.spinCC = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentCourseCoverage','REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentCourseCoverage=data.data.success ? data.data.data : [];
            $scope.spinCC = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $scope.spinCC = false;
        })

   }
    //    $scope.getStudentCourseCoverage();



    // #####################################################################
    //                            SLIDE START
    // #####################################################################

    $scope.spinLATopics = false;
    $scope.spinLASlideHead = false;
    $scope.spinLASlide = false;
    $scope.SELECTED_SLIDE_TOPICID = 0;
    /*============ GET LA TOPICS BY SECTION TOPIC =============*/ 
    $scope.getLaTopics = function (id) {
        $scope.post.getLaSlideHeads = [];
        $scope.post.getLaSlidesbyHead = [];
        $scope.spinLATopics = true;
        $scope.SELECTED_SLIDE_TOPICID = 0;
        $scope.ModalHeadTitle = `${id.CATEGORY} / ${id.SUBCATEGORY} / ${id.TOPIC}`;
        $http({
            method: 'post',
            url: urlBO,
            data: $.param({ 'type': 'getLaTopics','SECTION_TOPICID':id.TOPICID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLaTopics = data.data.success ? data.data.data : [];
            if($scope.post.getLaTopics.length==1) $scope.getLaSlideHeads($scope.post.getLaTopics[0]);
            $scope.spinLATopics = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /*============ GET LA TOPICS BY SECTION TOPIC =============*/ 
    
    
    $scope.SLIDE_HEAD_TITLE = '';
    $scope.SELECTED_TOPICNAME = '';
    $scope.SELECTED_HEADNAME = '';
    /*============ GET LA SLIDE HEADS =============*/ 
    $scope.getLaSlideHeads = function (id) {
        $scope.post.getLaSlidesbyHead = [];
        $scope.SELECTED_SLIDE_SLIDEID = '';
        $scope.SELECTED_SLIDE_TOPICID = id.LA_TOPICID;
        $scope.SELECTED_SLIDE_GRADEID = id.GRADEID;
        $scope.SELECTED_SLIDE_SUBID = id.SUBID;
        $scope.SELECTED_TOPICNAME = id.TOPIC;
        $scope.SLIDE_HEAD_TITLE = `${$scope.SELECTED_TOPICNAME} / ${$scope.SELECTED_HEADNAME}`;
        $scope.spinLASlideHead = true;
        $http({
            method: 'post',
            url: urlBO,
            data: $.param({ 'type': 'getLaSlideHeads','LA_TOPICID':id.LA_TOPICID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLaSlideHeads = data.data.success ? data.data.data : [];
            if($scope.post.getLaSlideHeads.length==1) $scope.getLaSlidesbyHead($scope.post.getLaSlideHeads[0]);
            $scope.spinLASlideHead = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /*============ GET LA SLIDE HEADS =============*/ 
    
    
    
    /*============ GET LA SLIDE HEADS =============*/ 
    $scope.SELECTED_SLIDE_SLIDEID = 0 ;
    $scope.getLaSlidesbyHead = function (id) {
        $scope.closeTest();
        $('#pills-slide-tab').tab('show');
        $scope.SELECTED_SLIDE_SLIDEID = id.SLIDEID;
        $scope.SELECTED_HEADNAME = id.SLIDEHEADING;
        $scope.SLIDE_HEAD_TITLE = `${$scope.SELECTED_TOPICNAME} / ${$scope.SELECTED_HEADNAME}`;
        $scope.spinLASlide = true;
        $http({
            method: 'post',
            url: urlBO,
            data: $.param({ 'type': 'getLaSlidesbyHead','SLIDEID':id.SLIDEID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLaSlidesbyHead = data.data.success ? data.data.data : [];

            $scope.spinLASlide = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /*============ GET LA SLIDE HEADS =============*/ 
    

    $scope.clearTopicModal = function(){
        $scope.SLIDE_HEAD_TITLE = '';
        $scope.SELECTED_TOPICNAME = '';
        $scope.SELECTED_HEADNAME = '';
        $scope.post.getLaTopics = [];
        $scope.post.getLaSlideHeads = [];
        $scope.post.getLaSlidesbyHead = [];
        $scope.SELECTED_SLIDE_TOPICID = 0;
        $scope.SELECTED_SLIDE_SLIDEID = 0;
        $scope.spinLATopics = false;
        $scope.spinLASlideHead = false;
        $scope.spinLASlide = false;
        
    }


    // #####################################################################
    //                            SLIDE END
    // #####################################################################
    


    // #####################################################################
    //                            ASSIGNMENTS START
    // #####################################################################
    $scope.onDragComplete=function(){
        console.log("drag success");
    }
    $scope.onDropComplete=function(data,m){
    $scope[m] = data;
    console.log("drop success data : "+ data+' / '+m);
    // $scope.saveAnswer(TESTID,AID,ANS,QUE_TYPE,MINDEX);
    }
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
    $scope.post.getAssignmentsQuestions = [];
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
            url: StudentAssiUrl,
            data: $.param({ 'type': 'getAssignmentsQuestions',
            'REGID':$scope.REGID,
            'LOCID':1,
            'GRADEID':$scope.SELECTED_SLIDE_GRADEID,
            'SUBID':$scope.SELECTED_SLIDE_SUBID,
            'TOPICID':$scope.SELECTED_SLIDE_TOPICID,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
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
            url: StudentAssiUrl,
            data: $.param({ 'type': 'getAssQueOptions',
            'MQUEID':$scope.MQUEID,
            'REGID':$scope.REGID,
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
            url: StudentAssiUrl,
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
                url: StudentAssiUrl,
                data: $.param({ 'type': 'resetTest',
                'REGID':$scope.REGID,
                'LOCID':1,
                'GRADEID':$scope.SELECTED_SLIDE_GRADEID,
                'SUBID':$scope.SELECTED_SLIDE_SUBID,
                'TOPICID':$scope.SELECTED_SLIDE_TOPICID,
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

                // $('#assignmentModal').modal('hide');
                // $scope.post.getLaSlideHeads=[];
                $scope.REVIEW = false;
                // $scope.TOPIC_NAME = '';
                // $scope.SELECTED_SLIDE_TOPICID=0;
                $scope.post.getAssignmentsQuestions = [];

            }
        }else{
            // $('#assignmentModal').modal('hide');
            // $scope.post.getLaSlideHeads=[];
            $scope.REVIEW = false;
            // $scope.TOPIC_NAME = '';
            // $scope.TOPICID=0;
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