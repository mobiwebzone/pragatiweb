$postModule = angular.module("myApp", ["angularjs-dropdown-multiselect","ngSanitize","angularjs-dropdown-multiselect"]);
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
// taOptions.toolbar = [
//     ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
//     ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
//     ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
//     ['html', 'insertImage','insertLink', 'insertVideo', 'wordcount', 'charcount']
// ];
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.BATCHES_model = [];
    $scope.Page = "L&A";
    $scope.PageSub = "LA_REPORTS";
    $scope.PageSub1 = "LA_TOPIC_COVERD";
    $scope.editMode = false;
    $scope.editModePs = false;
    var url = 'code/LA-TOPICS-COVERED-REPORT.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.temp.txtFromDT = new Date();
    $scope.temp.txtToDT = new Date();    

    $scope.BATCHES_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};



    /* ============ CHECK SESSION ============= */ 
    $scope.init = function () {
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
                    $scope.getLocations();
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ============ CHECK SESSION ============= */ 





    /* ========== GET REPORT =========== */
    $scope.getReport = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        // console.log($scope.BATCHES_model);
        var batchids = $scope.BATCHES_model.length>0 ? $scope.BATCHES_model.map(x=>x.id).toString() : '';
        $('#SpinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getReport');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtFromDT", $scope.temp.txtFromDT.toLocaleDateString('sv-SE'));
                formData.append("txtToDT", $scope.temp.txtToDT.toLocaleDateString('sv-SE'));
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("batches", batchids);
                formData.append("chkAttended", $scope.temp.chkAttended);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getReportTOPIC = !data.data.dataByTopic ? [] : data.data.dataByTopic;
            $scope.post.getReportSTUDENT = !data.data.dataByStudent ? [] : data.data.dataByStudent;
            if(!data.data.success) $scope.messageFailure(data.data.message);
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReport(); --INIT
    /* ========== GET REPORT =========== */

    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        // $scope.post.getGrades = [];
        // $scope.post.getSubjects = [];
        $scope.BATCHES_model = [];
        $scope.post.getBatches=[];
        $("#ddlLocation").focus();
    }
    /* ============ Clear Form =========== */ 




    
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */

    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getStudentByLoc = [];
        $scope.post.getLocReviewByLoc = [];
        $('.spinLoc').show();
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
            if($scope.temp.ddlLocation > 0) $scope.getBatches();
            // if($scope.temp.ddlLocation > 0) $scope.getTestMasters();
            // if($scope.temp.ddlLocation > 0) $scope.getGrades();
            // if($scope.temp.ddlLocation > 0) $scope.getSubjects();
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $('.spinGrade').show();
        $http({
            method: 'POST',
            url: 'code/LA-GRADE-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
                // formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlLocation", 1);
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
     $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $('.spinSubject').show();
        $http({
            method: 'POST',
            url: 'code/LA-SUBJECT-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjects');
                // formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlLocation", 1);
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
     $scope.getSubjects();
    /* ========== GET SUBJECT =========== */


    /* ========== GET BATCHES =========== */
    $scope.getBatches = function () {
        $scope.post.getBatches=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinBatch').show();
        $http({
            method: 'POST',
            url: masterUrl,
            processData:false,
            transformRequest: function (data){
                var formData = new FormData();
                formData.append("type",'getBatchesByLocationMultiple');
                formData.append("LOCID",$scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getBatches = data.data.success ? data.data.data : [];
            $('.spinBatch').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //    $scope.getBathes();
    /* ========== GET BATCHES =========== */

    /* ========== GET TOPICS =========== */
    $scope.getTopics = function () {
        $scope.post.getTopics=[];
        if(!$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0)return;
        $('.spinTopic').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTopics');
                // formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
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
/*                                           GET EXTRA DATA END                                                              */
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
    /* ========== Logout =========== */


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
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
    //         jQuery('.alert-success').show();
    //     });
    //     jQuery('.alert-success').delay(1000).slideUp(700,'easeOutBounce',function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
    //         jQuery('.alert-danger').show();
    //     });
    //     jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }




});