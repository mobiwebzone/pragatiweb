$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);
$postModule.filter('capitalize', function() {
    return function(input) {
      return (angular.isString(input) && input.length > 0) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : input;
    }
});
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$sce) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = 'ASSESSMENT';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];
    
    var url = 'code/review.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */
    
    
    
    // $scope.uCanTrust = function(string){
    // return $sce.trustAsHtml(string);
    // }
    
    /* =============== GET SESSION FROM Assessment ============== */
    if(sessionStorage.getItem("asstest") != undefined)
    {
        $scope.asstestid=sessionStorage.getItem("asstest");
        $scope.asstsecid=sessionStorage.getItem("asstsec");
        $scope.assgroupno=sessionStorage.getItem("assgroupno");
        $scope.assEssid=sessionStorage.getItem("assess");
        $scope.assattempt=sessionStorage.getItem("assin");
        $scope.assfor=sessionStorage.getItem("assfor");

        // console.info(`
        // TESTID : ${$scope.asstestid}
        // TSECID : ${$scope.asstsecid}
        // GROUPNO : ${$scope.assgroupno}
        // ATTEMPT : ${$scope.assattempt}
        // FOR : ${$scope.assfor}`);
    }
    /* =============== GET SESSION FROM Assessment ============== */
    
    
    

    
    /* =============== Check Session ============== */
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
                $scope.USER_LOCATION=data.data.LOCATION;
                $scope.REGID=data.data.data[0]['REGID'];
                $scope.PLAN=data.data.data[0]['PLAN'];
                $scope.GRADE=data.data.data[0]['GRADE'];
                $scope.LOCID=data.data.data[0]['LOCATIONID'];
                $scope.PLANID=data.data.data[0]['PLANID'];
                $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];

                $scope.ActivePlan = data.data.ActivePlan;
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
    /* =============== Check Session ============== */






    /*============ GET TEST REVIEW =============*/ 
    $scope.RESULT_RIGHT = 0;
    $scope.RESULT_WRONG = 0;
    $scope.getTestReview = function () {
        if($scope.asstestid>0 && $scope.asstsecid>0){
            $scope.post.getTestReview=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTestReview',
                                'testid':$scope.asstestid,
                                'tsecid':$scope.asstsecid,
                                'attempt':$scope.assattempt,
                                'assgroupno':$scope.assgroupno,
                                'assEssid':$scope.assEssid,
                                'assfor':$scope.assfor}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.REV_DATA = data.data.successRev ? data.data.DATA_REV : [];

                if(data.data.success){
                    $scope.post.getTestReview=data.data.data;
                    $scope.SECTIONNAME = data.data.SECTIONNAME;


                    if($scope.assfor === 'ESSAY'){
                        // const EssayDetails = $scope.post.getStudentEssay.filter((x) => {
                        //     return Number($scope.temp.ddlStudent) === x.INSERTID;
                        // });
                        // console.log(data.data.data[0].ESSAY);
                        $scope.STESSID = data.data.data[0].STESSID;
                        $scope.REGID = data.data.data[0].INSERTID;
                        $scope.ESSAY = $sce.trustAsHtml(data.data.data[0].ESSAY);
                        $scope.TOPIC = data.data.data[0].TOPIC;
                        $scope.TOTAL_CHAR = data.data.data[0].TOTAL_CHAR;
                        $scope.TOTAL_WORD = data.data.data[0].TOTAL_WORD;
                        $scope.NEW_STARTTIME = data.data.data[0].NEW_STARTTIME;
                        $scope.NEW_ENDTIME = data.data.data[0].NEW_ENDTIME;
                        $scope.LIMITON = data.data.data[0].LIMITON;
                        $scope.LIMIT = data.data.data[0].LIMIT;
                        $scope.TIMEALLOWED = data.data.data[0].TIMEALLOWED;

                        $scope.getGradingData();
                    }
                    else{
                        $scope.STESSID = '';
                        $scope.REGID = '';
                        $scope.ESSAY = '';
                        $scope.TOPIC = '';
                        $scope.TOTAL_CHAR = '';
                        $scope.TOTAL_WORD = '';
                        $scope.NEW_STARTTIME = '';
                        $scope.NEW_ENDTIME = '';
                        $scope.LIMITON = '';
                        $scope.LIMIT = '';
                        $scope.TIMEALLOWED = '';
                        $scope.post.getGradingData =[];
                        $scope.RESULT_RIGHT = data.data.RESULT_RIGHT;
                        $scope.RESULT_WRONG = data.data.RESULT_WRONG;
                    }
                }else{
                    $scope.post.getTestReview=[];
                    $scope.SECTIONNAME = '';
                    // console.info(data.data.message);
                }
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }else{
            console.info('Id Missing.');
        }
    }
    $scope.getTestReview();
    /*============ GET TEST REVIEW =============*/ 



    /* ========== GET GRADING DATA =========== */
    $scope.getGradingData = function () {
        $('.spinGrading').show();
        if($scope.STESSID > 0 && $scope.REGID > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getGradingData' , 
                                'STESSID':$scope.STESSID,
                                'REGID':$scope.REGID}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getGradingData = data.data.data;
                }else{
                    $scope.post.getGradingData = [];
                    // console.info(data.data.message);
                }
                $('.spinGrading').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        else{
            console.error('STESSID / REGID Missing.');
        }
    }
    // $scope.getGradingData();
    /* ========== GET GRADING DATA =========== */


    /* ========== OPEN EXPLANATION MODAL =========== */
    $scope.openExplanationModal=(id)=>{
        $scope.ANS_EXPLANATION = id.ANS_EXPLANATION;
        $scope.ANS_EXPIMAGE=(id.ANS_EXPIMAGE!=='') ? 'backoffice/question_images/explanation_images/'+id.ANS_EXPIMAGE : '';
    }
    /* ========== OPEN EXPLANATION MODAL =========== */
    





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