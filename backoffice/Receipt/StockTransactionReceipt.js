
$postModule = angular.module("myApp", ["ngSanitize"]);
$postModule.filter('capitalize', function() {
    return function(input) {
      return (angular.isString(input) && input.length > 0) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : input;
    }
});
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.temp.txtDate = new Date();
    $scope.transid=0;
    $scope.ToDayDate = $scope.temp.txtDate.toLocaleString('en-US');

    
    var url = 'StockTransactionReceipt.php';

    /* =============== GET TRANSID ============== */
    $scope.transid = new URLSearchParams(window.location.search).get('TRANS');
    if(!$scope.transid || $scope.transid<=0){
        window.location.assign('../Stock_Transactions.html');
    }
    // if($scope.transid>0)$scope.getTransaction();
    // console.log('TRANSID : '+$scope.transid);




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




    $scope.TRANSTYPE = '';
    $scope.TRANSFOR_NAME = '';
    $scope.TRANS_DATE = '';
     /* ========== GET TRANSACTION =========== */
    $scope.getTransaction = function () {
        $('.spinnermy').removeClass('d-none');
        $scope.post.getTransaction =[];
        $scope.LOC_DETAIL=[];

        if(!$scope.transid || $scope.transid <= 0) return;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTransaction','transid':$scope.transid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
  
            $scope.post.getTransaction = data.data.success ? data.data.data : [];
            $scope.TRANSTYPE =  data.data.success ? data.data.TRANSTYPE : '';
            $scope.TRANSFOR_NAME =  data.data.success ? data.data.TRANSFOR_NAME : '';
            $scope.TRANS_DATE =  data.data.success ? data.data.TRANS_DATE : '';
            // $scope.LOC_DETAIL = data.data.LOC_DETAIL;
            $('.spinnermy').addClass('d-none');
        },
        function (data, status, headers, config) {
            console.log('Failed');
            $('.spinnermy').addClass('d-none');
        })
    }
    $scope.getTransaction();



    $scope.PDF=function(){
        $http({
            method: 'post',
            url: 'testPDF.php',
            data: $.param({ 'type': 'PDF','Test':'hello' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
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