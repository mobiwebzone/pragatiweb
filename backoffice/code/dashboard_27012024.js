$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","chart.js"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.Page = "HOME";
    $scope.editMode = false;

    $scope.TotalRegistrations = 0;
    $scope.TotalApproved = 0;
    $scope.TotalAdmins = 0;
    $scope.TotalTeachers = 0;
    $scope.TotalVolunteers = 0
    $scope.TotalTaskReviewCount = 0

    // Teacher Expenses
    $scope.temp.txtFromDate = new Date(new Date().setDate(1));
    $scope.temp.txtToDate = new Date();

    // alert(new Date(new Date().setDate(1)));
    // Student Attendance
    $scope.temp.txtFromDate_AT = new Date(new Date().setDate(1));
    $scope.temp.txtToDate_AT = new Date();
    // Student Rec Analysis
    $scope.temp.txtFromDate_SRA = new Date(new Date().setDate(1));
    $scope.temp.txtToDate_SRA = new Date();
    
    
    $scope.temp.txtFromDateTotal = new Date(new Date().setDate(1));
    $scope.temp.txtToDateTotal = new Date();


    
    var url = 'code/dashboard_code.php';


    $scope.openTeacher_Att_Expenses = function () {
        let STRA_FROM_date_TAE=$scope.temp.txtFromDate.toLocaleString('sv-SE');
        let STRA_TO_date_TAE=$scope.temp.txtToDate.toLocaleString('sv-SE');
        // Teacher_Att_Expenses_Report
        window.location.assign("Teacher_Att_Expenses_Report.html?FDT="+STRA_FROM_date_TAE+"&TDT="+STRA_TO_date_TAE);
    }


    $scope.openSt_Rec_Analy = function () {
        if($scope.userrole == 'SUPERADMIN'){
            let STRA_FROM_date=$scope.temp.txtFromDate_SRA.toLocaleString('sv-SE');
            let STRA_TO_date=$scope.temp.txtToDate_SRA.toLocaleString('sv-SE');
            // Student_Rec_Analysis_Report
            window.location.assign("Student_Rec_Analysis_Report.html?STRA_F_DT="+STRA_FROM_date+"&STRA_T_DT="+STRA_TO_date);
        }
    }


    

    /* ============ Check Session =========== */
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
                $scope.USER_LOCID=data.data.locid;
                $scope.USER_LOCATION=data.data.LOCATION;
                $scope.IS_ET=data.data.IS_ET;
                // window.location.assign("dashboard.html");

                if($scope.userrole != undefined && $scope.userrole != "TSEC_USER"){
                    $scope.getLocations();
                    $scope.getTotals();
                    // $scope.getTotalTeacherHour();
                    // $scope.getTotalST_Rec_Analysis();
                    // $scope.getTotalStudentAtt();
                    if($scope.userrole != 'TSEC_USER'  && $scope.IS_ET==1 ){
                        // $scope.getStudentFeesOutstanding();
                        $scope.getTeacher();
                        $scope.getTeacherLeave();
                        // $scope.getStudentLeave();
                        // $scope.getTeacherWrongAttMark();
                        // $scope.getDiscontinueReq();
                        // $scope.getVolunteerReq();   
                        // $scope.getST_NO_ATT();   
                        // $scope.getTeacher_NO_ATT();   
                        // $scope.getDuplicateStudent();   
                    }
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
    /* ============ Check Session =========== */



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
            
            $scope.temp.ddlLocationTotal = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocationTotal > 0) $scope.getTotalsByDT();
            if($scope.temp.ddlLocationTotal > 0) $scope.getStudentLeave();
            if($scope.temp.ddlLocationTotal > 0) $scope.getVolunteerReq();
            if($scope.temp.ddlLocationTotal > 0) $scope.getDiscontinueReq();
            if($scope.temp.ddlLocationTotal > 0) $scope.getST_NO_ATT();
            if($scope.temp.ddlLocationTotal > 0) $scope.getTeacherWrongAttMark();
            if($scope.temp.ddlLocationTotal > 0) $scope.getDuplicateStudent();
            if($scope.temp.ddlLocationTotal > 0) $scope.getTeacher_NO_ATT();   ;


            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getTotalTeacherHour();

            $scope.temp.ddlLocation_SRA = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation_SRA > 0) $scope.getTotalST_Rec_Analysis();

            if($scope.userrole != 'TSEC_USER'  && $scope.IS_ET==1 ){
                $scope.temp.ddlLocation_AT = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
                // if($scope.temp.ddlLocation_AT > 0) $scope.getTotalStudentAtt();
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */





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
            url: url,
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
    $scope.getTaskTrackDetails = function () {
    $scope.SpinTaskTrack=true;
        $http({
            method: 'post',
            url: url,
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
                url: url,
                data: $.param({ 'TASKMGMTID': id.TASKMGMTID, 'type': 'ClosedTask','CLOSEDBY':'ADMIN'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    // var index = $scope.post.TotalTaskView.indexOf(id);
                    // $scope.post.TotalTaskView.splice(index, 1);
                    $scope.getTotals();
                    // $scope.GET_TASKMGMTID=0
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
        if($scope.userrole != 'TSEC_USER'){
            $scope.spinTaskTo = true;
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTotals' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.TotalRegistrations = data.data.TotalRegistrations['TOTAL'];
                    $scope.TotalApproved = data.data.TotalApproved['TOTAL'];
                    $scope.TotalAdmins = data.data.TotalAdmins['TOTAL'];
                    $scope.TotalTeachers = data.data.TotalTeachers['TOTAL'];
                    $scope.TotalVolunteers = data.data.TotalVolunteers['TOTAL'];
                    $scope.post.TotalTaskView = data.data.TotalTaskView;
                    $scope.TotalTaskReviewCount = data.data.TotalTaskReviewCount['TOTAL'];

                    let animation = anime({
                        targets: '.TopCard',
                        // translateY: -40,
                        scale:.9,
                        duration: 100,
                        direction: 'alternate',
                        elasticity: 880,
                        // easing: 'easeInOutElastic(1, .6)',
                        // delay : anime.stagger(100),
                        delay: function(el, i, l) {
                            return i * 100;
                        },
                        endDelay: function(el, i, l) {
                            return (l - i) * 100;
                        },
                    });
                }
                $scope.spinTaskTo = false;
                
            },
            function (data, status, headers, config) {
                $scope.spinTaskTo = false;
                console.log('Login Failed');
            })
        }
    }
    // $scope.getTotals(); --INIT
    /* ========== Get Totals =========== */
    



    /* ========== Get Totals NEW =========== */
    $scope.getTotalsByDT = function () {
        if($scope.userrole != 'TSEC_USER'){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTotalsByDT',
                                'txtFromDate':$scope.temp.txtFromDateTotal.toLocaleDateString('sv-SE'),
                                'txtToDate':$scope.temp.txtToDateTotal.toLocaleString('sv-SE'),
                                'ddlLocation':$scope.temp.ddlLocationTotal,
                                'USER_LOCATION':$scope.USER_LOCATION
                }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.post.TOTALS_NEW = data.data.data_final;

                    $(function() {
                        setTimeout(()=>{
                          VanillaTilt.init(document.querySelectorAll(".totalCard"), {
                            max: 25,
                            speed: 400
                          });
                        },300);
                    });
                }
    
            },
            function (data, status, headers, config) {
                console.log('Login Failed');
            })
        }
    }
    // $scope.getTotalsByDT(); --INIT
    /* ========== Get Totals NEW =========== */





    /* ========== Get Total Teacher Hours =========== */
    let TE_BarChart;
    $scope.getTotalTeacherHour = function () {
        $('.spinTeacherExpen').show();
        if($scope.userrole != 'TSEC_USER'){
            if($scope.temp.txtFromDate != undefined && $scope.temp.txtToDate != undefined){
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'type': 'getTotalTeacherHour',
                                    'txtFromDate':$scope.temp.txtFromDate.toLocaleString('sv-SE'),
                                    'txtToDate':$scope.temp.txtToDate.toLocaleString('sv-SE'),
                                    'ddlLocation':$scope.temp.ddlLocation
                                }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if (data.data.success) {
                        $scope.post.getTotalTeacherHour = data.data.data;
    
                        var ctx = document.getElementById("TeacherExpensesChart").getContext("2d");
                        if (TE_BarChart) {
                            TE_BarChart.destroy()
                        }
    
                        
                        var data = {
                            labels: ['Current Year','Previous Year','Previous Month'],
                            datasets: [{
                                label: "Hours (Decimal)",
                                backgroundColor: "rgba(253,180,92,0.2)",
                                borderColor: "rgba(253,180,92,1)",
                                data: data.data.hours
                            }]
                        };
                        
    
                        var opt = {
                            events: false,
                            tooltips: {
                                enabled: false
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
    
    
                        TE_BarChart = new Chart(ctx, {
                            type: 'bar',
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
                    $('.spinTeacherExpen').hide();
                },
                function (data, status, headers, config) {
                    console.log('Login Failed');
                })
            }
        }
    }
    // $scope.getTotalTeacherHour(); --INIT
    /* ========== Get Total Teacher Hours =========== */
    
    
    
    
    
    /* ========== Get Total Student Rec Analysis =========== */
    let SRA_BarChart;
    $scope.getTotalST_Rec_Analysis = function () {
        $('.spinStudentRecAna').show();
        if($scope.userrole == 'SUPERADMIN'){
            if($scope.temp.txtFromDate_SRA != undefined && $scope.temp.txtToDate_SRA != undefined){
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'type': 'getTotalST_Rec_Analysis',
                                    'txtFromDate_SRA':$scope.temp.txtFromDate_SRA.toLocaleString('sv-SE'),
                                    'txtToDate_SRA':$scope.temp.txtToDate_SRA.toLocaleString('sv-SE'),
                                    'ddlLocation':$scope.temp.ddlLocation_SRA
                                }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if (data.data.success) {
                        $scope.post.getTotalST_Rec_Analysis = data.data.data;
    
                        var ctx = document.getElementById("StudentRecAlaysisChart").getContext("2d");
                        if (SRA_BarChart) {
                            SRA_BarChart.destroy()
                        }
    
                        
                        var data = {
                            labels: ['Current Year','Previous Year','Previous Month'],
                            datasets: [{
                                label: "Amount",
                                backgroundColor: "rgba(70,191,189,0.2)",
                                borderColor: "rgba(70,191,189,1)",
                                data: data.data.amount
                            }]
                        };
                        
    
                        var opt = {
                            events: false,
                            tooltips: {
                                enabled: false
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
    
    
                        SRA_BarChart = new Chart(ctx, {
                            type: 'bar',
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
                    $('.spinStudentRecAna').hide();
                },
                function (data, status, headers, config) {
                    console.log('Login Failed');
                })
            }
        }
    }
    // $scope.getTotalST_Rec_Analysis(); --INIT
    /* ========== Get Total Student Rec Analysis =========== */





    /* ========== Get Total Student Att =========== */
    let SATT_BarChart;
    $scope.getTotalStudentAtt = function () {
        // alert($scope.temp.ddlChartType_AT);
        $('.spinStudentAtt').show();
        if($scope.userrole != 'TSEC_USER'){
            if($scope.temp.txtFromDate_AT != undefined && $scope.temp.txtToDate_AT != undefined){
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'type': 'getTotalStudentAtt',
                                    'txtFromDate_AT':$scope.temp.txtFromDate_AT.toLocaleString('sv-SE'),
                                    'txtToDate_AT':$scope.temp.txtToDate_AT.toLocaleString('sv-SE'),
                                    'ddlLocation':$scope.temp.ddlLocation_AT
                                }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if (data.data.success) {
                        
                        $scope.post.getTotalStudentAtt = data.data.data;
                        $scope.plan=data.data.plansTS;
                        $scope.CYhours=data.data.CYhours;
                        $scope.PYhours=data.data.PYhours;
                        $scope.PMhours=data.data.PMhours;
    
                        var ctx = document.getElementById("StudentAttChart").getContext("2d");
                        if (SATT_BarChart) {
                            SATT_BarChart.destroy()
                        }
    
    
                        var data = {
                            labels: $scope.plan,
                            datasets: [{
                                label: "Current Year",
                                backgroundColor: "rgba(253,180,92,0.2)",
                                borderColor: "rgba(253,180,92,1)",
                                data: $scope.CYhours
                            }, {
                                label: "Previous Year",
                                backgroundColor: "rgba(247,70,74,0.2)",
                                borderColor: "rgba(247,70,74,1)",
                                data: $scope.PYhours
                            }, {
                                label: "Previous Month",
                                backgroundColor: "rgba(70,191,189,0.2)",
                                borderColor: "rgba(70,191,189,1)",
                                data: $scope.PMhours
                            }]
                        };
    
    
                        var opt = {
                            events: false,
                            tooltips: {
                                enabled: false
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
    
    
                        SATT_BarChart = new Chart(ctx, {
                        type: 'bar',
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

                    $('.spinStudentAtt').hide();
        
                },
                function (data, status, headers, config) {
                    console.log('Login Failed');
                })
            }
        }
    }
    // $scope.getTotalStudentAtt(); --INIT
    /* ========== Get Total Student Att =========== */





    /* ========== Get Student Fees Outstanding =========== */
    $scope.getStudentFeesOutstanding = function () {
        $('.spinStudentFeeOut').show();
        if($scope.userrole != 'TSEC_USER'){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentFeesOutstanding' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.post.getStudentFeesOutstanding = data.data.data;
                }
                $('.spinStudentFeeOut').hide();
            },
            function (data, status, headers, config) {
                console.log('Login Failed');
            })
        }
    }
    // $scope.getStudentFeesOutstanding(); --INIT
    /* ========== Get Student Fees Outstanding =========== */






    /* ========== Teacher =========== */
    $scope.getTeacher = function () {
        $('.spinTeacher').show();
        if($scope.userrole != 'TSEC_USER'){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTeacher','USER_LOCID':$scope.USER_LOCID }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.post.getTeacher = data.data.data;
                }else{
                    $scope.post.getTeacher = [];
                }
                $('.spinTeacher').hide();
            },
            function (data, status, headers, config) {
                console.log('Login Failed');
            })
        }
    }
    // $scope.getTeacher(); --INIT
    /* ========== Teacher =========== */






    /* ========== Teacher Leave =========== */
    $scope.getTeacherLeave = function () {
        $('.spinTeacherLeave').show();
        if($scope.userrole != 'TSEC_USER'){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTeacherLeave' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.post.getTeacherLeave = data.data.data;
                }
                $('.spinTeacherLeave').hide();
            },
            function (data, status, headers, config) {
                console.log('Login Failed');
            })
        }
    }
    // $scope.getTeacherLeave(); --INIT
    /* ========== Teacher Leave =========== */





    //========= Cancel Modal ==========
    $scope.CancelModal=function(id){
        $scope.CANCELREQID = id.REQID;
    }
    //========= Cancel Modal ==========





    /* ========== DELETE =========== */
    $scope.CancelTeacherLeave = function () {
        if($scope.userrole != 'TSEC_USER'){
            var r = confirm("Are you sure want to cancel this request!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'reqid': $scope.CANCELREQID,'txtCancelRemark':$scope.temp.txtCancelRemark, 'type': 'CancelTeacherLeave' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        $('#cancelModal').trigger({type:"click"});
                        $scope.getTeacherLeave();
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== DELETE =========== */





    //========= Teacher Substitute Modal ==========
    $scope.SubstituteTeacherModal=function(id){
        $scope.TSUBS_REQID = id.REQID;
        $scope.temp.ddlTeacherSubs = id.SUBSTITUTE_TEACHER > 0 ? (id.SUBSTITUTE_TEACHER).toString() : '';
        $scope.temp.txtRemarkSubs = id.SUBSTITUTE_REMARK;
    }
    //========= Teacher Substitute Modal ==========





    /* ========== SAVE TEACHER SUBSTITUTE =========== */
    $scope.SubstituteTeacher = function () {
        if($scope.userrole != 'TSEC_USER'){
            var r = confirm("Are you sure want to add this teacher!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'reqid': $scope.TSUBS_REQID,
                                    'type': 'SubstituteTeacher',
                                    'ddlTeacherSubs' : $scope.temp.ddlTeacherSubs,
                                    'txtRemarkSubs':$scope.temp.txtRemarkSubs
                                }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        $('#SubstituteModal').trigger({type:"click"});
                        $scope.getTeacherLeave();
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== SAVE TEACHER SUBSTITUTE =========== */
    
    



    
    /* ========== Student Leave =========== */
    $scope.getStudentLeave = function () {
        $('.spinStudentLeave').show();
        if($scope.userrole != 'TSEC_USER'){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentLeave','ddlLocation':$scope.temp.ddlLocationTotal }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.post.getStudentLeave = data.data.data;
                }
                $('.spinStudentLeave').hide();
            },
            function (data, status, headers, config) {
                console.log('Login Failed');
            })
        }
    }
    // $scope.getStudentLeave(); --INIT
    /* ========== Student Leave =========== */





    //========= Cancel Modal Student ==========
    $scope.CancelModalStudent=function(id){
        $scope.CANCELREQID = id.REQID;
    }
    //========= Cancel Modal Student ==========





    /* ========== DELETE =========== */
    $scope.CancelStudentLeave = function () {
        if($scope.userrole != 'TSEC_USER'){
            var r = confirm("Are you sure want to cancel this request!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'reqid': $scope.CANCELREQID,'txtCancelRemark':$scope.temp.txtCancelRemarkST, 'type': 'CancelStudentLeave' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        $('#cancelModalStudent').trigger({type:"click"});
                        $scope.getStudentLeave();
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== DELETE =========== */




    

    /* ========== GET Volunteer Request =========== */
    $scope.getVolunteerReq = function () {
        // alert($scope.REGID);
        $('.spinVolunteerReq').show();
        if($scope.userrole != 'TSEC_USER'){
            $scope.post.getVolunteerReq=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getVolunteerReq','ddlLocation':$scope.temp.ddlLocationTotal }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getVolunteerReq=data.data.data;
                }
                $('.spinVolunteerReq').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
   }
//    $scope.getVolunteerReq(); --INIT
   /* ========== GET Volunteer Request =========== */





    //======== Cancel-Approve Modal Open ==========
    $scope.CancelModalVolunteer=function(id,For){
        $scope.ModalNM = For;
        $scope.VRID = id.VRID;
    }
    //======== Cancel-Approve Modal Open ==========






    /* ========== Cancel-Approve =========== */
    $scope.CancelApprove = function () {
        if($scope.userrole != 'TSEC_USER'){
            var r = confirm("Are you sure want to " + $scope.ModalNM + " this request!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'VRID': $scope.VRID,'txtRemarkCA':$scope.temp.txtRemarkCA,'FOR':$scope.ModalNM, 'type': 'CancelApprove' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        // console.log(data.data.message)
                        $('#cancelModalCV').trigger({type:"click"});
                        $scope.getVolunteerReq();
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
        }
    }
    /* ========== Cancel-Approve =========== */






    /* ========== GET Discontinue Request =========== */
    $scope.getDiscontinueReq = function () {
        // alert($scope.REGID);
        $('.spinStudentDisReq').show();
        if($scope.userrole != 'TSEC_USER'){
            $scope.post.getDiscontinueReq=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getDiscontinueReq','ddlLocation':$scope.temp.ddlLocationTotal }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getDiscontinueReq=data.data.data;
                }
                $('.spinStudentDisReq').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
   }
//    $scope.getDiscontinueReq(); --INIT
   /* ========== GET Discontinue Request =========== */






   /* ========== Discontinue Approve =========== */
   $scope.DiscontinueApprove = function (x) {
    //    alert(x.REGDID);
    if($scope.userrole != 'TSEC_USER'){
        var r = confirm("Are you sure want to approve this request!");
        if (r == true) {
            
            $('#appr_loader').removeClass('d-none');
            
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'REGDID': x.REGDID,'REGID':x.REGID, 'type': 'DiscontinueApprove' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    // console.log(data.data.message)
    
                    $timeout(function () {
                        $('#appr_loader').addClass('d-none');
                        $scope.getDiscontinueReq();
                        $scope.messageSuccess(data.data.message);
                    },2000);
                } else {
                    $timeout(function () {
                        $('#appr_loader').addClass('d-none');
                        $scope.messageFailure(data.data.message);
                    },2000);
                }
            })
            
        }
    }
    // $scope.appr_loader=0;
    }
    /* ========== Discontinue Approve =========== */





    /* ========== GET WRONG ATTENDANCE MARKED =========== */
    $scope.getTeacherWrongAttMark = function () {
    // alert($scope.REGID);
        $('.spinTeacherWAM').show();
        if($scope.userrole != 'TSEC_USER'){
            $scope.post.getTeacherWrongAttMark=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTeacherWrongAttMark','ddlLocation':$scope.temp.ddlLocationTotal }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getTeacherWrongAttMark=data.data.data;
                }
                $('.spinTeacherWAM').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getTeacherWrongAttMark(); --INIT
    /* ========== GET WRONG ATTENDANCE MARKED =========== */





    /* ========== STUDENT NO ATTENDANCE LAST TOW WEEK =========== */
    $scope.getST_NO_ATT= function () {
    // alert($scope.REGID);
        $('.spinStudentNoAtt').show();
        if($scope.userrole != 'TSEC_USER'){
            $scope.post.getST_NO_ATT=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getST_NO_ATT','ddlLocation':$scope.temp.ddlLocationTotal }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getST_NO_ATT=data.data.data;
                }else{
                    console.info(data.data.message);
                }
                $('.spinStudentNoAtt').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getST_NO_ATT(); --INIT
    /* ========== STUDENT NO ATTENDANCE LAST TOW WEEK =========== */





    /* ========== TEACHER NO ATTENDANCE LAST TOW WEEK =========== */
    $scope.getTeacher_NO_ATT= function () {
    // alert($scope.REGID);
        $('.spinTeacherNoAtt').show();
        if($scope.userrole != 'TSEC_USER'){
            $scope.post.getTeacher_NO_ATT=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTeacher_NO_ATT','ddlLocation':$scope.temp.ddlLocationTotal}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getTeacher_NO_ATT=data.data.data;
                }else{
                    console.info(data.data.message);
                }
                $('.spinTeacherNoAtt').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getTeacher_NO_ATT(); --INIT
    /* ========== TEACHER NO ATTENDANCE LAST TOW WEEK =========== */





    /*============ DUPLICATE STUDENT LIST =============*/
    $scope.getDuplicateStudent= function () {
    // alert($scope.REGID);
        $('.spinDuplicateST').show();
        if($scope.userrole != 'TSEC_USER'){
            $scope.post.getDuplicateStudent=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getDuplicateStudent','ddlLocation':$scope.temp.ddlLocationTotal }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getDuplicateStudent=data.data.data;
                }else{
                    // console.info(data.data.message);
                }
                $('.spinDuplicateST').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getDuplicateStudent(); --INIT
    /*============ DUPLICATE STUDENT LIST =============*/







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





    /* ========== Message =========== */
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
    /* ========== Message =========== */


    
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