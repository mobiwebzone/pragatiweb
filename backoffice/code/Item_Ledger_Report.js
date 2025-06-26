$postModule = angular.module("myApp", ["ngSanitize"]);
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
    $scope.Page = "INVENTORY";
    $scope.PageSub = "ITEM_LEDGER_RPT";
    $scope.dt = new Date().toLocaleDateString('es-US');
    $scope.temp.txtFromDT='';
    $scope.temp.txtToDT='';
    // $scope.ToDT =$scope.dt;
    // $scope.FromDT = new Date();
    // $scope.FromDT.setDate($scope.FromDT.getDate() - 180);
    // $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');

    var url = 'code/Item_Ledger_Report.php';


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
                    // $scope.getStudentCoursePending();
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
        if(!$scope.temp.ddlItem || $scope.temp.ddlItem<=0) return;
        $('#spinRpt').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReport','ddlItem': $scope.temp.ddlItem}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReport = data.data.success ? data.data.data : [];
            $('#spinRpt').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
         
    }
    // $scope.getReport();
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
            if($scope.temp.ddlLocation > 0) $scope.getItemcategory();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */



    /* ========== GET ITEM CATEGORY =========== */
    $scope.getItemcategory = function () {
        $scope.post.getItemcategory=$scope.post.getItems=[];
        $('.spinItemCat').show();
        $http({
            method: 'post',
            url: 'code/Item_Categories.php',
            data: $.param({ 'type': 'getItemCategories','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getItemcategory = data.data.success ? data.data.data : [];
            $('.spinItemCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
         
    }
    // $scope.getItemcategory(); --INIT
    /* ========== GET ITEM CATEGORY =========== */




     /* ========== GET ITEMS =========== */
     $scope.getItems = function () {
         $('.spinItem').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getItems','ddlItemCategory':$scope.temp.ddlItemCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getItems = data.data.success ? data.data.data : [];
            $('.spinItem').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
         
    }
    // $scope.getItems();
    /* ========== GET ITEMS =========== */

    


    /*============ CHANGE PRINT DATE =============*/ 
    $scope.changePrintDate=()=>{
        if($scope.temp.txtFromDT && $scope.temp.txtFromDT!=''){
            $scope.FromDT = new Date($scope.temp.txtFromDT).toLocaleDateString('es-US');
        }else{
            $scope.FromDT = new Date();
            $scope.FromDT.setDate($scope.FromDT.getDate() - 180);
            $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
        }
        
        if($scope.temp.txtToDT && $scope.temp.txtToDT!=''){
            $scope.ToDT = new Date($scope.temp.txtToDT).toLocaleDateString('es-US');
        }
        else{
            $scope.ToDT = new Date().toLocaleDateString('es-US');
        }
    }
    $scope.clearDate=function(){
        $scope.temp.ddlItemCategory='';
        $scope.temp.ddlItem='';
        $scope.post.getItems=[]
        $scope.post.getReport=[]
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