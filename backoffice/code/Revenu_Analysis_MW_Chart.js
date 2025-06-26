
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","chart.js"]);
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
    $scope.Page = "REPORTS";
    $scope.PageSub = "REV_ANALYSIS_MW_CHART";
    $scope.dt = new Date().toLocaleDateString('es-US');
    $scope.ToDT =$scope.dt;
    $scope.FromDT = new Date();
    var year = new Date().getFullYear();
    var date = new Date(year, 0, 1);
    // $scope.FromDT.setDate($scope.FromDT.getDate() - 365);
    $scope.temp.txtFromDT=new Date(date);
    $scope.temp.txtToDT=new Date();

    $scope.month_list = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $scope.month_short_list = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
    $scope.month_list_num = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    var url = 'code/Revenu_Analysis_MW_Chart.php';


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
                    // $scope.getReport();
                    // $scope.getPlans();
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



    /* ========== GET REPORT =========== */
    $scope.getReport = function () {
        if(!$scope.temp.txtFromDT || $scope.temp.txtFromDT=='' || !$scope.temp.txtToDT || $scope.temp.txtToDT=='' || !$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinReport').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport',
            'txtFromDT': (!$scope.temp.txtFromDT || $scope.temp.txtFromDT == '') ? '' : $scope.temp.txtFromDT.toLocaleDateString('sv-SE'),
            'txtToDT': (!$scope.temp.txtToDT || $scope.temp.txtToDT == '') ? '' :$scope.temp.txtToDT.toLocaleDateString('sv-SE'),
            'ddlLocation': $scope.temp.ddlLocation,
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getReport = data.data.success?data.data.MAIN_DATA:[];
        var DATA_LEN = Object.keys($scope.post.getReport).length;
        if(DATA_LEN>0){

            
            // GET MAIN DIV
            var container = document.querySelector('#myCanvas')
            if(container) container.innerHTML='';

            
            for($d=1;$d<=$scope.month_list_num.length;$d++){
                $scope.YEAR=$scope.post.getReport[$d]['YEAR'];
                console.log($scope.YEAR);
                $scope.TOTALS=$scope.post.getReport[$d]['TOTAL'];
    
                // container = document.querySelector('#myCanvas')
                // CREATE COLUMN
                var col = document.createElement('div');
                col.setAttribute('class', 'col-sm-12 col-md-4 col-lg-3');
                col.setAttribute('id', 'CHART_DIV'+$d);
                container.appendChild(col)

                var column = document.querySelector('#CHART_DIV'+$d)
                if(column) column.innerHTML='';

                
                // CREATE CANVAS
                var canvas = document.createElement('canvas');
                var id_name = `canvas${$d}`
                canvas.setAttribute('id', id_name);
                canvas.setAttribute('class', 'mb-4');
                canvas.setAttribute('height', '300');

                // CREATE HEADING
                var head = document.createElement('h3');
                head.innerHTML='<u>'+$scope.month_list[$d-1].toUpperCase()+'</u>';
                head.setAttribute('class', 'text-center font-weight-bold');

                // SET ELEMNT MAIN DIV
                column.appendChild(head)
                column.appendChild(canvas)


                var ctx = document.getElementById(id_name).getContext("2d");
                let canvasID = eval('let ' + 'ST_BarChart' + $d);
                if (canvasID) {
                    canvasID.destroy()
                }

                var data = {
                    labels: $scope.YEAR,
                    datasets: [{
                        label: "Total",
                        backgroundColor: "rgba(253,180,92,0.2)",
                        borderColor: "rgba(253,180,92,1)",
                        data: $scope.TOTALS
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


                // SATT_BarChart = new Chart(ctx, {
                canvasID = new Chart(ctx, {
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
        }

        $('.spinReport').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
    }
    // $scope.getReport(); --INIT
    /* ========== GET REPORT =========== */

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
            if($scope.temp.ddlLocation > 0) $scope.getReport();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    
    
    /*============ CHANGE PRINT DATE =============*/ 
    $scope.clearDate=function(){
        var year = new Date().getFullYear();
        var date = new Date(year, 0, 1);
        $scope.temp.txtFromDT=new Date(date);
        $scope.temp.txtToDT=new Date();
        $scope.getLocations();
    }
    /*============ CHANGE PRINT DATE =============*/ 
    


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