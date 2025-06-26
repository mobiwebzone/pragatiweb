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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "ST_COURSE_PENDING_NEW_RPT";
    $scope.dt = new Date().toLocaleDateString('es-US');
    $scope.temp.txtFromDT='';
    $scope.temp.txtToDT='';
    $scope.ToDT =$scope.dt;
    $scope.FromDT = new Date();
    $scope.FromDT.setDate($scope.FromDT.getDate() - 180);
    $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');

    var url = 'code/Student_Course_Pending_New_Report.php';


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
                    $scope.getStudentCoursePending();
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



     /* ========== GET Student Course Pending =========== */
     $scope.getStudentCoursePending = function () {
         $('#spinRpt').show();
         if(!$scope.temp.txtFromDT || $scope.temp.txtFromDT==''){
            $scope.FromDT_S = new Date();
            $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 180);
            $scope.txtFromDT=new Date($scope.FromDT_S).toLocaleDateString('sv-SE');
        }
        else{
            $scope.txtFromDT = $scope.temp.txtFromDT.toLocaleDateString('sv-SE');
        }
        if(!$scope.temp.txtToDT || $scope.temp.txtToDT==''){
            $scope.txtToDT = new Date().toLocaleDateString('sv-SE');
        }else{
            $scope.txtToDT = $scope.temp.txtToDT.toLocaleDateString('sv-SE');
        }

        // console.log($scope.txtFromDT +' || '+ $scope.txtToDT);

        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentCoursePending',
                            'txtFromDT': $scope.txtFromDT,
                            'txtToDT': $scope.txtToDT,
                }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentCoursePending = data.data.success ? data.data.data : [];
            $('#spinRpt').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
         
    }
    // $scope.getStudentCoursePending(); --INIT
    /* ========== GET Student Course Pending =========== */
    


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
        $scope.temp.txtFromDT='';$scope.temp.txtToDT='';
        $scope.ToDT = new Date().toLocaleDateString('es-US');
        $scope.FromDT = new Date();
        $scope.FromDT.setDate($scope.FromDT.getDate() - 180);
        $scope.FromDT=new Date($scope.FromDT).toLocaleDateString('es-US');
        $scope.getStudentCoursePending();
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