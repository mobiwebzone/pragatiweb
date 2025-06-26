
$postModule = angular.module("myApp", ["angularjs-dropdown-multiselect","ngSanitize","angular.filter"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$sce) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page="DOCS";
    $scope.temp.txtAttDate=new Date();
    $scope.chkStudentidList=[];
    $scope.attRemarksList=[];
    $scope.StudentListLength=0;
    $scope.ADMIN = false;
    $scope.PAGEFOR = 'TEACHER';
    
    // var url = 'code/LaTopicCovered_T.php';
    var url = '../student_zone/code/Documents_ST.php';
    var masterUrl = '../backoffice/code/MASTER_API.php';



    /* ========== CHECK SESSION =========== */
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: '../backoffice/code/checkSession.php',
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
                $scope.LOC_ID=data.data.locid;
                // window.location.assign("dashboard.html");
                if($scope.LOC_ID>0) $scope.getDocuments();
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
    /* ========== CHECK SESSION =========== */



    /* ========== GET DOCUMENTS =========== */
    $scope.getDocuments = function () {
        // alert($scope.temp.ddlPaidDue);
        $scope.spinSum = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getDocuments',
                            'ddlLocation':$scope.LOC_ID,
                            'userid':$scope.userid,
                            'docFor':'TEACHER',
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDocuments=data.data.success ? data.data.data : [];
            $scope.spinSum = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }


    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: '../student_zone/code/logout.php',
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




});