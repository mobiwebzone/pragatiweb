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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "TEST_SUB_PEND_RPT";
    $scope.dt = new Date().toLocaleString('sv-SE')
    $scope.temp.txtFromDT=new Date();
    $scope.temp.txtToDT=new Date();

    var url = 'code/Test_Submission_Pending_Report.php';

   

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
                    $scope.getTestSubmissionPending();
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



    /* ========== GET Student Submission Pendings =========== */
     $scope.getTestSubmissionPending = function () {
        $('.spinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTestSubmissionPending',
                            'txtFromDT':$scope.temp.txtFromDT.toLocaleString('sv-SE'),
                            'txtToDT':$scope.temp.txtToDT.toLocaleString('sv-SE')}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTestSubmissionPending = data.data.success ? data.data.data : [];
            $('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
         
    }
    // $scope.getTestSubmissionPending(); --INIT
    /* ========== GET Student Submission Pendings =========== */



    /* ========== TEST SUBMIT =========== */
    $scope.TestSubmit = function (id) {
        console.log(id);
        var r = confirm("Are you sure want to submit this test!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STID': id.STID, 
                                'CORRECT_SCORE_COUNT':id.CORRECT_SCORE_COUNT,
                                'TGID':id.TGID,
                                'TESTID':id.TESTID,
                                'type': 'TestSubmit' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTestSubmissionPending.indexOf(id);
                    $scope.post.getTestSubmissionPending.splice(index, 1);

                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    /* ========== TEST SUBMIT =========== */



    /* ========== TEST SUBMIT =========== */
    $scope.DeleteTest = function (id) {
        var r = confirm("Are you sure want to delete this test!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 
                                'type': 'DeleteTest',
                                'STID': id.STID, 
                                'CORRECT_SCORE_COUNT':id.CORRECT_SCORE_COUNT,
                                'TGID':id.TGID,
                                'TESTID':id.TESTID,
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTestSubmissionPending.indexOf(id);
                    $scope.post.getTestSubmissionPending.splice(index, 1);

                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    /* ========== TEST SUBMIT =========== */

    


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