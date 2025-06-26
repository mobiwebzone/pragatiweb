$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect","angularUtils.directives.dirPagination","angular.filter", "ngSanitize","chart.js",'ngDraggable']);
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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "ST_PROGRESS_RPT";
    $scope.dt = new Date().toLocaleString('sv-SE')
    $scope.temp.txtFromDT=new Date();
    // $scope.temp.txtFromDT=new Date('01-12-2021');
    $scope.temp.txtToDT=new Date();
    $scope.PLANS_model = [];
    $scope.STUDENTS_model = [];
    $scope.spinLATopics = false;;
    $scope.spinLASlideHead = false;
    $scope.spinLASlide = false;
    $scope.isBO = true;

    var url = 'code/Student_progress_Report.php';
    var StudentAssiUrl = '../student_zone/code/LearnAssis.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};


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

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
                    $scope.getPlans();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
                
            }else{

                // window.location.assign('index.html#!/login');
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
        var REGIDS = $scope.STUDENTS_model.map(s=>s.id);
        if(!REGIDS || REGIDS.length==0) return;
        $scope.spinMainDT = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentTest','REGIDS':REGIDS}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getStudentTest=data.data.success ? data.data.data : [];
            $scope.post.CHART_DATASET = data.data.CHART_DATASET;
            var DATA_LEN = data.data.CHART_DATASET.length;

            
            $timeout(()=>{
                if(DATA_LEN>0){
                    for($i=0;$i<DATA_LEN;$i++){
                        var FINAL_DT =  $scope.post.CHART_DATASET[$i];
                        
                        FINAL_DT.forEach((val,index) => {
                            console.log(val,index);

                            const DATE = val['DATE'];
                            const RAW = val['RAW'];
                            const SCALE = val['SCALE'];
                            const TOTAL_Q = val['TOTAL_Q'];
        
                            var containerId = `#canvasContainer${$i}${index}`;
                            console.log(containerId);
                            var container = document.querySelector(containerId);
                            container.innerHTML='';
        
                            // CREATE CANVAS
                            var canvas = document.createElement('canvas');
                            var id_name = `canvas${$i}${index}`;
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
        var REGIDS = $scope.STUDENTS_model.map(s=>s.id);
        if(!REGIDS || REGIDS.length==0) return;
        $scope.spinWA = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentWrongAnswers','REGIDS':REGIDS}),
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
        var REGIDS = $scope.STUDENTS_model.map(s=>s.id);
        if(!REGIDS || REGIDS.length==0) return;
        $scope.spinTWA = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentTopicWiseAnalysis','REGIDS':REGIDS}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
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
        var REGIDS = $scope.STUDENTS_model.map(s=>s.id);
        if(!REGIDS || REGIDS.length==0) return;
        $scope.Title='COURSE COVERAGE';
        $scope.spinCC = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentCourseCoverage','REGIDS':REGIDS}),
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
            // if($scope.temp.ddlLocation > 0) $scope.getInventories();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: masterUrl,
            data: $.param({ 'type': 'getPlans_MultiSelect'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.success ? data.data.data : [];
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */


    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
    $scope.getStudentByPlanProduct = function () {
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct=[];
        $scope.post.weeks = [];
        $scope.post.GET_REPORT_VIEW = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinStudent').show();
        // $FINAL_PRODUCTID = [];
        // $FINAL_PRODUCTID = $scope.PRODUCTS_model.map(x=>x.id);
        $FINAL_PLANID = [];
        $FINAL_PLANID = $scope.PLANS_model.map(x=>x.id);
        $http({
            method: 'post',
            url: 'code/Student_Attendance_Payment_Report_P2.php',
            data: $.param({ 'type': 'getStudentByPlanProduct', 
                            'PLANID' : $FINAL_PLANID,
                            'ddlLocation' : $scope.temp.ddlLocation
                            // 'PRODUCTID' : $FINAL_PRODUCTID
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByPlanProduct = data.data.success ? data.data.data : [];
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlanProduct();
    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
    


    // #####################################################################
    //                            SLIDE START
    // #####################################################################
    
    $scope.SELECTED_SLIDE_TOPICID = 0;
    /*============ GET LA TOPICS BY SECTION TOPIC =============*/ 
    $scope.getLaTopics = function (id) {
        console.log(id);
        $scope.REGID=id.REGID;
        $scope.post.getLaSlideHeads = [];
        $scope.post.getLaSlidesbyHead = [];
        $scope.spinLATopics = true;
        $scope.SELECTED_SLIDE_TOPICID = 0;
        $scope.ModalHeadTitle = `${id.CATEGORY} / ${id.SUBCATEGORY} / ${id.TOPIC}`;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLaTopics','SECTION_TOPICID':id.TOPICID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getLaTopics = data.data.success ? data.data.data : [];
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
            url: url,
            data: $.param({ 'type': 'getLaSlideHeads','LA_TOPICID':id.LA_TOPICID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLaSlideHeads = data.data.success ? data.data.data : [];

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
            url: url,
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