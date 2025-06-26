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
    $scope.formTitle = '';
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_DISCONTINUE";
    $scope.STUDENTFOR ='ACTIVATE';
    $scope.temp.ddlActDis = 'DISCONTINUE';
    
    var url = 'code/BO_Student_Discontinue_code.php';



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
                    // $scope.getStudentData('ACTIVATE');
                    $scope.getLocations();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
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

     /* ========== GET DISCONTINUE STUDENT =========== */
     $scope.getStudentData = function (FOR) {
        $scope.post.getStudentData=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        if(FOR == 'DISCONTINUE'){
            $('.my-spinner').removeClass('d-none');
        }
        $('.my-loader').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentData',
            'ddlLocation' : $scope.temp.ddlLocation,
            'FOR':FOR, 
            'txtSearchStudentForDis' : $scope.temp.txtSearchStudentForDis}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentData = data.data.data;
            $('.my-loader').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
        if(FOR == 'DISCONTINUE'){
            $timeout(function () {
                $('.my-spinner').addClass('d-none');
            },1000);
        }
    }
    // $scope.getStudentData('ACTIVATE'); --INIT



    // Open Active Plan Modal
    $scope.ActivePlanModal = function (id,FOR_ACT_DIS){
        $scope.PLAN_NM = id.PLAN;
        $scope.REGDID = id.REGDID;
        $scope.FOR_ACT_DIS = FOR_ACT_DIS;
    }


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
            if($scope.temp.ddlLocation > 0 && $scope.temp.ddlActDis!='') $scope.getStudentData($scope.temp.ddlActDis);
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== DELETE =========== */
    $scope.ActivePlan = function () {
        // var r = confirm("Are you sure want to delete this record!");
        // if (r == true) {
            if($scope.REGDID > 0){
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'REGDID': $scope.REGDID, 'type': 'ActivePlan' ,'FOR_ACT_DIS':$scope.FOR_ACT_DIS}),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        console.log(data.data.message)

                        $scope.getStudentData('ACTIVATE');
                        $scope.PLAN_NM = '';
                        $scope.REGDID = '';
                        $scope.FOR_ACT_DIS = '';
                        $scope.temp.txtSearchStudentForDis ='';
                        
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                    $("#activeplan").trigger({ type: "click" });
                    
                })
            }
        // }
    }


    /* ========== REVOKE STUDENT =========== */
    $scope.getDetailsForRevoke =function(x){
        // console.log(x);
        $scope.REVOKE_REGID=x.REGID;
    }
    $scope.SetApproveRevoke = function(FOR){
        // alert($scope.temp.ddlCollege);
        $('.btnRevokeConfirm').attr('disabled','disabled');
        $http({
            method: 'POST',
            url: 'code/LoginApproval_code.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'SetApproveRevoke');
                formData.append("REGID", $scope.REVOKE_REGID);
                formData.append("FOR", FOR);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // $scope.getStudentData(FOR);
                $scope.getStudentData($scope.temp.ddlActDis);
                $('.btnRevokeConfirm').removeAttr('disabled');
                $('#revokeModal').modal('hide');
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
        });
    }
    /* ========== REVOKE STUDENT =========== */



    /* ========== Clear =========== */
    $scope.Clear = function () {
        $scope.PLAN_NM = '';
        $scope.REGDID = '';
        $scope.FOR_ACT_DIS = '';
        // $scope.getStudentData('ACTIVATE');
        $scope.temp.txtSearchStudentForDis ='';
        $scope.getLocations();
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