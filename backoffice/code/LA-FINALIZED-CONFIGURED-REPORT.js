$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","angular.filter"]);
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
    $scope.Page = "L&A";
    $scope.PageSub = "LA_OPEN_ISSUE";
    $scope.PageSub1 = "";
    $scope.temp.txtAttDate=new Date();
    
    var url = 'code/LA-FINALIZED-CONFIGURED-REPORT.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
    }

    $scope.print = function(){
        $('.mainTable').removeClass('col-md-8');
        window.print();
        $('.mainTable').addClass('col-md-8');
    }

    $scope.exportToExl = function(){
        var table = $(this).prev('.table2excel');
        var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
        var date = new Date();
        date = date.getDate()+"-"+(date.getMonth()+1)+"-"+date.getFullYear();
        $('.table2excel').table2excel({
            exclude: ".noExl",
            name: "Excel Document Name",
            filename: date + "_LA_SLIDES_OPEN_ISSUE_REPORT.xls",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true,
            preserveColors: preserveColors
        });
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    // $scope.getLocations();
                    $scope.getReport();
                }
                // window.location.assign("dashboard.html");
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




    /* ========== GET Report =========== */
    $scope.getReport = function () {
        $('.spinRPT').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getReport');
                formData.append("ddlReportType", $scope.ddlReportType);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReport = data.data.success ? data.data.data : [];
            $('.spinRPT').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReport(); --INIT
    /* ========== GET Reposr =========== */


 


/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */
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
            if($scope.temp.ddlLocation > 0) $scope.getGrades();
            if($scope.temp.ddlLocation > 0) $scope.getSubjects();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $scope.post.getGrades=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinGrade').show();
        $http({
            method: 'POST',
            url: 'code/LA-GRADE-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrades = data.data.success ? data.data.data : [];
            $('.spinGrade').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $scope.post.getSubjects=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinSubject').show();
        $http({
            method: 'POST',
            url: 'code/LA-SUBJECT-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjects');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubjects = data.data.success ? data.data.data : [];
            $('.spinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getSubjects();
    /* ========== GET SUBJECT =========== */

    /* ========== GET TOPICS =========== */
    $scope.getTopics = function () {
        $scope.post.getTopics=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0)return;
        $('.spinTopic').show();
        $http({
            method: 'POST',
            url: masterUrl,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTopicsByLoc_Grade_Subject');
                formData.append("LOCID", $scope.temp.ddlLocation);
                formData.append("GRADEID", $scope.temp.ddlGrade);
                formData.append("SUBID", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTopics = data.data.success ? data.data.data : [];
            $('.spinTopic').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTopics();
    /* ========== GET TOPICS =========== */
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA END                                                               */
/* ######################################################################################################################### */


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


    $scope.eyepass = function(For,index) {
        if(For == 'MPASS'){
            var input = $("#txtMeetingPasscode");
        }else if(For == 'EPASS'){
            var input = $("#txtEmailPassword");
        }
        
        // var input = $("#txtMeetingPasscode");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon'+index).removeClass('fa-eye');
            $('.Eyeicon'+index).addClass('fa-eye-slash');
        }else{
            $('.Eyeicon'+index).removeClass('fa-eye-slash');
            $('.Eyeicon'+index).addClass('fa-eye');
        }
    };

});