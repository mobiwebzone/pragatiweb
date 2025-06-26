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
    $scope.PageSub = "ST_LIST_DAYWISE";
    $scope.dt = new Date().toLocaleDateString('es-US');
    $scope.temp.txtFromDT='';
    $scope.temp.txtToDT='';
    $scope.ToDT =$scope.dt;
    $scope.FromDT = new Date();
    $scope.FromDT.setDate($scope.FromDT.getDate() - 60);
    $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
    // $scope.FromDT =$scope.dt;
    // $scope.ToDT = new Date();
    // $scope.ToDT.setDate($scope.ToDT.getDate() + 60);
    // $scope.ToDT=new Date($scope.ToDT).toLocaleDateString('es-US');

    $scope.daysInWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    var url = 'code/Student_ListDayWise_Report.php';


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
                $scope.locid=data.data.locid;

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
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
        $scope.post.getReport=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinReport').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport',
            'txtFromDT': (!$scope.temp.txtFromDT || $scope.temp.txtFromDT == '') ? '' : $scope.temp.txtFromDT.toLocaleDateString('sv-SE'),
            'txtToDT': (!$scope.temp.txtToDT || $scope.temp.txtToDT == '') ? '' :$scope.temp.txtToDT.toLocaleDateString('sv-SE'),
            'ddlLocation': $scope.temp.ddlLocation
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getReport = data.data.success?data.data.data:[];
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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getReport();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $('.spinPlans').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.data;
            $('.spinPlans').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */




    
    
    /*============ CHANGE PRINT DATE =============*/ 
    $scope.changePrintDate=()=>{
        if($scope.temp.txtFromDT && $scope.temp.txtFromDT!=''){
            $scope.FromDT = new Date($scope.temp.txtFromDT).toLocaleDateString('es-US');
        }else{
            $scope.FromDT = new Date();
            $scope.FromDT.setDate($scope.FromDT.getDate() - 60);
            $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
        }
        
        if($scope.temp.txtToDT && $scope.temp.txtToDT!=''){
            $scope.ToDT = new Date($scope.temp.txtToDT).toLocaleDateString('es-US');
        }
        else{
            $scope.ToDT = new Date().toLocaleDateString('es-US');
            // $scope.ToDT = new Date();
            // $scope.ToDT.setDate($scope.ToDT.getDate() + 60);
            // $scope.ToDT=new Date($scope.ToDT).toLocaleDateString('es-US');
        }
    }
    $scope.clearDate=function(){
        $scope.temp.txtFromDT='';$scope.temp.txtToDT='';
        $scope.ToDT = new Date().toLocaleDateString('es-US');
        $scope.FromDT = new Date();
        $scope.FromDT.setDate($scope.FromDT.getDate() - 60);
        $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
        // $scope.getReport();
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