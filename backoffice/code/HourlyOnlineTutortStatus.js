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
    $scope.formTitle = '';
    $scope.Page = "L&A";
    $scope.PageSub = "HourlyTutoring";
    $scope.PageSub1 = "T_HOURLY_PAY_STATUS";
    $scope.date = new Date();
    $scope.temp.txtPaymentDate=new Date();

    $scope.minFromDT = new Date().toLocaleTimeString('sv-SE');
    var todayD = new Date().toLocaleDateString('sv-SE');
    var todayT = new Date().toLocaleTimeString('sv-SE');
    $scope.temp.txtFromDT = new Date(`${todayD}T${todayT}`);
    $scope.temp.txtToDT = new Date(`${todayD}T${todayT}`);
    // $scope.temp.tosid = '';
    $scope.minToDT = '';

    $scope.studentPay=[];
    $scope.PAGEFOR = 'ADMIN';
    
    var url = 'code/HourlyOnlineTutortStatus.php';
    var Masterurl = 'code/MASTER_API.php';


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
                $scope.LOC_ID=data.data.locid;
                // alert(data.data.userrole);
                // window.location.assign("dashboard.html");
                
                if($scope.userrole != "TSEC_USER")
                {
                    // if(data.data.locid > 0){
                    // }
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
            if($scope.temp.ddlLocation > 0) $scope.getTeacher();
            if($scope.temp.ddlLocation > 0) $scope.getTeacher_Online_Status();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $scope.post.getTeacher = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $scope.spinTeacher = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacher = data.data.data;
            $scope.spinTeacher = false;
        
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
// $scope.getTeacher();


    /* ========== SAVE ONLINE =========== */
    $scope.saveOnline = function(){
        $(".btn-save-onoff").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update-onoff").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        console.log($scope.temp.txtFromDT.toLocaleString('sv-SE'),'/',$scope.temp.txtToDT.toLocaleString('sv-SE'))
        // return;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveOnline');
                formData.append("TOSID", $scope.temp.TOSID);
                formData.append("LOC_ID", $scope.temp.ddlLocation);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("txtFromDT", $scope.temp.txtFromDT.toLocaleString('sv-SE'));
                formData.append("txtToDT", $scope.temp.txtToDT.toLocaleString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // $scope.clear();
                $scope.getTeacher_Online_Status();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-onoff').removeAttr('disabled').text('SAVE');
            $('.btn-update-onoff').removeAttr('disabled').text('UPDATE');
        });
    }



    
    /* =========== Get Teacher Active Status =========== */ 
    $scope.GetActiveStatus = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'GetActiveStatus','ddlTeacher':$scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.post.GetActiveStatus = data.data.data;
                $scope.temp.chkAlwaysActive = data.data.data[0]['ALWAYS_ACTIVE'];
            }
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
    }
    // $scope.GetActiveStatus();


    /* =========== Get Teacher Online Status =========== */ 
    $scope.getTeacher_Online_Status = function () {
        $scope.SpinMainData = true;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTeacher_Online_Status');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.post.getTeacher_Online_Status = data.data.data;
            }
            $scope.SpinMainData = false;
        },
        function (data, status, headers, config) {
            $scope.SpinMainData = false;
            console.log('Login Failed');
        })
    }
    // $scope.getTeacher_Online_Status();

    $scope.clear = function(){
        $scope.temp={};
        $scope.post.getTeacher_Online_Status = [];

        $scope.minFromDT = new Date().toLocaleTimeString('sv-SE');
        var todayD = new Date().toLocaleDateString('sv-SE');
        var todayT = new Date().toLocaleTimeString('sv-SE');
        $scope.temp.txtFromDT = new Date(`${todayD}T${todayT}`);
        $scope.temp.txtToDT = new Date(`${todayD}T${todayT}`);
        // $scope.temp.tosid = '';
        $scope.minToDT = '';
    }


    /* ========== ALWAYS ONLINE =========== */
    $scope.setAlwaysOnline = function(){
        // return;
        // alert($scope.temp.chkAlwaysActive)
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0){
            $scope.temp.chkAlwaysActive = 0;
            return;
        }
        $('.alwayAct').attr('disabled',true);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'setAlwaysOnline');
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("chkAlwaysActive", $scope.temp.chkAlwaysActive);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.alwayAct').attr('disabled',false);
        });
    }
    $scope.setTomin = function(){
        $scope.minToDT = '';
        if(!$scope.temp.txtToDT || $scope.temp.txtToDT=='')return;
        $scope.minToDT = $scope.temp.txtToDT.toLocaleTimeString('sv-SE');
    }

    $scope.setOnlineTime = function () {
        // alert($scope.temp.tosid);
        if($scope.isOnline){
            // $scope.temp.tosid = $scope.post.getOnlineStatus['TOSID'];
            $scope.temp.txtFromDT = new Date('2023-01-01T'+$scope.post.getOnlineStatus['ONTIME_SET']);
            $scope.temp.txtToDT = new Date('2023-01-01T'+$scope.post.getOnlineStatus['OUTTIME_SET']);
            $scope.minToDT = $scope.post.getOnlineStatus['ONTIME_SET'];
        }else{
            var todayD = new Date().toLocaleDateString('sv-SE');
            var todayT = new Date().toLocaleTimeString('sv-SE');
            $scope.temp.txtFromDT = new Date(`${todayD}T${todayT}`);
            $scope.temp.txtToDT = new Date(`${todayD}T${todayT}`);
            // $scope.temp.tosid = '';
            $scope.minToDT = '';
        }
    }

    /* ============ Edit ITEM MASTER Details ============= */ 
    // $scope.edit = function (id) {
    //     $scope.temp.TOSID=id.TOSID;
    //     $scope.temp.ddlLocation=id.LOCID.toString();
    //     $scope.getTeacher();

    //     $timeout(function() {
    //         $scope.temp.ddlTeacher=id.TEACHERID.toString();
    //     }, 2000 );
        
        
    //     $scope.temp.chkAlwaysActive=id.ALWAYS_ACTIVE;
    //     $scope.temp.txtFromDT=(id.ONTIME_SET != '') ? new Date("2023-01-01T" + id.ONTIME_SET) : '';
    //     $scope.temp.txtToDT=(id.OUTTIME_SET != '') ? new Date("2023-01-01T" + id.OUTTIME_SET) : '';
    //     $scope.editMode = true;
    //     $scope.index = $scope.post.getTeacher_Online_Status.indexOf(id);
    // }
    

    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TOSID': id.TOSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getTeacher_Online_Status.indexOf(id);
                    $scope.post.getTeacher_Online_Status.splice(index, 1);
                    // $scope.clear();
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
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
                window.location.assign('index.html#!/login');
            }
            else {
                window.location.assign('dashboard.html#!/dashboard');
            }
        },
        function (data, status, headers, config) {
            console.log('Not login Failed');
        })
    }



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