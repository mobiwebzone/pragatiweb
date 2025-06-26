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
    $scope.Page = "REPORTS";
    $scope.PageSub = "ST_REC_ANALYSIS";
    
    var url = 'code/Student_Rec_Analysis_Report_code.php';


    

    // if($scope.temp.txtFromDate_SRA != undefined && $scope.temp.txtToDate_SRA != undefined){
    //     $scope.getSTRecAnalysisDetail();
    // }

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

                if($scope.userrole == 'SUPERADMIN'){
                    // get Branchid By Url
                    $scope.GetST_ANAY_F_DT=new URLSearchParams(window.location.search).get('STRA_F_DT');
                    $scope.GetST_ANAY_T_DT=new URLSearchParams(window.location.search).get('STRA_T_DT');
                    console.warn($scope.GetST_ANAY_F_DT +"------"+ $scope.GetST_ANAY_T_DT);
    
    
                    $scope.temp.txtFromDate_SRA = $scope.GetST_ANAY_F_DT == null ? new Date(new Date().setDate(1)) : new Date($scope.GetST_ANAY_F_DT);
                    $scope.temp.txtToDate_SRA = $scope.GetST_ANAY_T_DT == null ? new Date() : new Date($scope.GetST_ANAY_T_DT);
                }else{

                    window.location.assign("dashboard.html#!/dashboard");
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }



     /* ========== GET Student Rec Analysis Details =========== */
     $scope.getSTRecAnalysisDetail = function () {
         if($scope.temp.txtFromDate_SRA != undefined && $scope.temp.txtToDate_SRA != undefined){
             $http({
                 method: 'post',
                 url: url,
                 data: $.param({ 'type': 'getSTRecAnalysisDetail',
                                'txtFromDate_SRA':$scope.temp.txtFromDate_SRA.toLocaleString('sv-SE'),
                                'txtToDate_SRA':$scope.temp.txtToDate_SRA.toLocaleString('sv-SE')}),
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
             }).
             then(function (data, status, headers, config) {
                //  console.log(data.data);
                 $scope.post.Current_Year = data.data.Current_Year;
                 $scope.post.Previous_Year = data.data.Previous_Year;
                 $scope.post.Previous_Month = data.data.Previous_Month;


                 //  Total Amount
                $scope.TOTAL_CY = data.data.TOTAL_CY;
                $scope.TOTAL_PY = data.data.TOTAL_PY;
                $scope.TOTAL_PM = data.data.TOTAL_PM;
             },
             function (data, status, headers, config) {
                 console.log('Failed');
             })
         }else{
             console.info('Check from-to date.');
         }
    }
    $scope.getSTRecAnalysisDetail();
    
    


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