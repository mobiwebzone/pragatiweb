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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "ST_TEST_RPT";
    $scope.dt = new Date().toLocaleString('sv-SE');
    $scope.temp.txtFromDT=new Date();
    $scope.temp.txtToDT=new Date();

    var url = 'code/Student_Test_Report_code.php';


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */
    
    
    
    
    /* =============== CHECK SESSION ============== */
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
                $scope.locid = data.data.locid;

                if($scope.userrole != "TSEC_USER")
                {
                    // $scope.getStudentTest();
                    $scope.getLocations();
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
    /* =============== CHECK SESSION ============== */





    /* ========== GET Student Test =========== */
    $scope.temp.txtASC_DESC = 'ASC';
     $scope.getStudentTest = function () {
         $('.spinMainData').show();
         $('.btn-get').attr('disabled','disabled');
         $scope.post.getStudentTest = '';
        if($scope.temp.txtFromDT != undefined && $scope.temp.txtToDT != undefined && $scope.temp.ddlLocation>0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentTest',
                                'ddlLocation':$scope.temp.ddlLocation,
                                'txtOrderby':$scope.temp.txtOrderby,
                                'txtASC_DESC':$scope.temp.txtASC_DESC,
                                'txtFromDT':$scope.temp.txtFromDT.toLocaleString('sv-SE'),
                                'txtToDT':$scope.temp.txtToDT.toLocaleString('sv-SE'),
                                'txtSerarch':$scope.temp.txtSerarch
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getStudentTest = data.data.StudentTest;

                $('.spinMainData').hide();
                $('.btn-get').removeAttr('disabled');
            },
            function (data, status, headers, config) {
                // console.log('Failed');
                $scope.messageFailure('Failed');
                $('.spinMainData').hide();
                $('.btn-get').removeAttr('disabled');
            })
        }else{
            $('.spinMainData').hide();
            $('.btn-get').removeAttr('disabled');
        }
    }
    // $scope.getStudentTest(); --INIT
    /* ========== GET Student Test =========== */
    


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
            if($scope.temp.ddlLocation > 0) $scope.getStudentTest();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    
    /* ========== LOGOUT =========== */
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
    /* ========== LOGOUT =========== */






    /* =============== ALERT MESSAGE ============== */
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
    /* =============== ALERT MESSAGE ============== */




});