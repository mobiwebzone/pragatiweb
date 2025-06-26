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
    $scope.Page = "MISC";
    $scope.PageSub = "EXP";
    $scope.PageSub1 = "EXPENSE_BUDGET";
    $scope.temp.txtDate = new Date();
    $scope.txtAmount = [];
    $scope.txtBudget = [];

    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Expense_Budget.php';




    /* ========== GET EXPENSE BUDGET =========== */
    // $scope.temp.txtYear=2022;
    // $scope.temp.NoOfYear='1';
    // $scope.temp.txtFactor=1.1;
    $scope.prepared = false;
    $scope.getExpenseBudget = function () {
        $scope.SEL_LOCID = $scope.temp.ddlLocation;
        $scope.SEL_YEAR = $scope.temp.txtYear;
        $scope.SEL_NOOFYEAR = $scope.temp.NoOfYear;
        $scope.SEL_FECTOR = $scope.temp.txtFactor;
        $scope.post.getExpenseBudget = [];
        $('.btnExpBud').text('Wait...').attr('disabled','disabled');
        $('#SpinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getExpenseBudget');
                formData.append("ddlLocation", $scope.SEL_LOCID);
                formData.append("txtYear", $scope.SEL_YEAR);
                formData.append("NoOfYear", $scope.SEL_NOOFYEAR);
                formData.append("txtFactor", $scope.SEL_FECTOR);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getExpenseBudget = data.data.success?data.data.FINAL_DATA:[];
            $scope.prepared = data.data.success ? true : false;
            $('.btnExpBud').text('PREPARE').removeAttr('disabled');
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getExpenseBudget(); --INIT
    /* ========== GET EXPENSE BUDGET =========== */


    /* ========== REPREPARE BUDGET =========== */
    $scope.rePrepareBudget = function(){
        var r = confirm("Are you sure want to Re-Prepare this Budget!");
        if (r == true) {
            $scope.prepared = false;
            // $scope.SEL_LOCID = $scope.temp.ddlLocation;
            // $scope.SEL_YEAR = $scope.temp.txtYear;
            // $scope.SEL_NOOFYEAR = $scope.temp.NoOfYear;
            // $scope.SEL_FECTOR = $scope.temp.txtFactor;
            // $scope.getExpenseBudget();
        }
    }
    /* ========== REPREPARE BUDGET =========== */


    /* ========== GET BUDGET BY YEAR =========== */
    $scope.getBudgetByYear = function () {
        $scope.prepared = false;
        $scope.post.getBudgetByYear=[];
        $scope.post.getExpenseBudget=[];
        $scope.temp.NoOfYear=$scope.temp.txtFactor='';
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.txtYear || $scope.temp.txtYear.length<4) return;
        // $('#SpinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getBudgetByYear');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtYear", $scope.temp.txtYear);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getBudgetByYear = data.data.success?data.data.data:[];
            if(data.data.success && data.data.data.length==1){
                $scope.temp.NoOfYear = data.data.data[0].NOOFYEAR.toString();
                $scope.temp.txtFactor = Number(data.data.data[0].FACTOR);
                $scope.prepared = true;
                $scope.getExpenseBudget();
            }
            // $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBudgetByYear(); --INIT
    /* ========== GET BUDGET BY YEAR =========== */
    
    
    /* ========== SET VALUES =========== */
    $scope.setValues = function(id){
        $scope.temp.NoOfYear = id.NOOFYEAR.toString();
        $scope.temp.txtFactor = Number(id.FACTOR);

        $scope.SEL_LOCID = $scope.temp.ddlLocation;
        $scope.SEL_YEAR = $scope.temp.txtYear;
        $scope.SEL_NOOFYEAR = $scope.temp.NoOfYear;
        $scope.SEL_FECTOR = $scope.temp.txtFactor;
        $scope.getExpenseBudget();
    }
    /* ========== SET VALUES =========== */

    $scope.clearData = function(){
        $scope.temp.NoOfYear = '';
        $scope.temp.txtFactor = '';
        $scope.post.getExpenseBudget=[];
        $scope.prepared = false; 
        $scope.getBudgetByYear();
    }
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    



    // =========== SAVE DATA ==============
    $scope.saveBudget = function(EHID,MONTH,VAL){
        // console.log(`${EHID}  || ${MONTH} || ${VAL}`);

        // return;
        // $(".btnSaveBudget").attr('disabled', 'disabled').text('Saving...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveBudget');
                formData.append("ddlLocation", $scope.SEL_LOCID);
                formData.append("txtYear", $scope.SEL_YEAR);
                formData.append("EHID", EHID);
                formData.append("MONTH", MONTH);
                formData.append("VAL", VAL);
                formData.append("NOOFYEAR", $scope.SEL_NOOFYEAR);
                // formData.append("txtBudget", (!$scope.txtBudget || $scope.txtBudget.length<=0) ? '' : JSON.stringify($scope.txtBudget));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnSaveBudget').removeAttr('disabled').text('SAVE');
        });
    }
    // =========== SAVE DATA ==============









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
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */





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
    /* ========== Logout =========== */


     // =============== Check Session =============
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
                // window.location.assign("dashboard.html");

                // $scope.getChannels();
                $scope.getLocations();
                
            }    
            else {
                // window.location.assign('index.html#!/login')
                // alert
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    // =============== Check Session =============




    /* ========== MESSAGE =========== */
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
    /* ========== MESSAGE =========== */




});