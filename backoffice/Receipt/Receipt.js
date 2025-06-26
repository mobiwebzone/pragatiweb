
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.temp.txtDate = new Date();
    $scope.recid=0;
    $scope.ToDayDate = $scope.temp.txtDate.toLocaleString('en-US');

    
    var url = 'Receipt.php';

    /* =============== GET RECID ============== */
    $scope.recid = new URLSearchParams(window.location.search).get('REC');
    if($scope.recid == null){
        window.location.assign('../StudentPaymentRec.html#!/StudentPayment');
    }
    console.log('RECID : '+$scope.recid);




    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 






    

    // GET DATA
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: '../code/checkSession.php',
            data: $.param({ 'type': 'checkSession' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.user = data.data.data;
            $scope.userid=data.data.userid;
            $scope.userFName=data.data.userFName;
            $scope.userLName=data.data.userLName;
            $scope.userrole=data.data.userrole;
            $scope.USER_LOCATION=data.data.LOCATION;

            
            if (data.data.success) {
                // window.location.assign("dashboard.html");
            }
            else {
                // window.location.assign('index.html#!/login')
                // alert
                // $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }


     /* ========== GET RECEIPTS =========== */
    $scope.getReceipts = function () {
        $('.spinnermy').removeClass('d-none');
        $scope.post.getReceipts =[];
        $scope.LOC_DETAIL=[];

        if($scope.recid > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getReceipts','recid':$scope.recid}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                if(data.data.success){
                    $scope.post.getReceipts = data.data.data;
                    $scope.LOC_DETAIL = data.data.LOC_DETAIL;

                    // window.open('testPDF.php','_blank');
                }else{
                    $scope.post.getReceipts =[];
                    $scope.LOC_DETAIL =[];
                }
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        $('.spinnermy').addClass('d-none');
    }
    $scope.getReceipts();

    $scope.PDF=function(){
        $http({
            method: 'post',
            url: 'testPDF.php',
            data: $.param({ 'type': 'PDF','Test':'hello' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                
            }
            else {
                // $scope.loginFailure(data.data.message);
            }
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    


    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: '../code/logout.php',
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