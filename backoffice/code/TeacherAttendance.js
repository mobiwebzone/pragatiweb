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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$filter) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = "TEACHER";
    $scope.PageSub = "TEACHERATT";
    $scope.date = new Date();
    $scope.temp.txtAttDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];
    $scope.PAGEFOR = 'ADMIN';
    
    var url = '../teacher_backoffice/code/TeacherAttendance_code.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 


    // -========= Set time ==============
    $scope.SetTime=function (time) {
        var d = new Date(),
        // s = "01.25 PM",
        s = time,
        parts = s.match(/(\d+)\:(\d+)(\w+)/),
        hours = /am/i.test(parts[3]) ? parseInt(parts[1], 10) : parseInt(parts[1], 10) + 12,
        minutes = parseInt(parts[2], 10);

        d.setHours(hours);
        d.setMinutes(minutes);
        d.setSeconds(0,0);

        return d
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

    $scope.Save = function(){
        $scope.temp.ddlPlan=0;
        // alert($scope.temp.txtTimeIN.toLocaleString('en-US', {hour: 'numeric',minute: 'numeric'}));
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Save');
                formData.append("taid", $scope.temp.taid);
                formData.append("txtAttDate", $scope.dateFormat($scope.temp.txtAttDate));
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlProduct1", $scope.temp.ddlProduct1);
                formData.append("ddlProduct2", $scope.temp.ddlProduct2);
                formData.append("ddlProduct3", $scope.temp.ddlProduct3);
                formData.append("txtTimeIN", $scope.temp.txtTimeIN.toLocaleString('en-US', {hour: 'numeric',minute: 'numeric'}));
                formData.append("txtTimeOUT", $scope.temp.txtTimeOUT.toLocaleString('en-US', {hour: 'numeric',minute: 'numeric'}));
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("txtLearnToday", $scope.temp.txtLearnToday);
                formData.append("txtOtherWork", $scope.temp.txtOtherWork);
                formData.append("txtSupervisorComm", $scope.temp.txtSupervisorComm);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) { 
                
                $scope.getAtt();
                $scope.clear();
                $scope.messageSuccess(data.data.message);
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }
    
/* ========== GET Att =========== */
$scope.getAtt = function () {
    $('#spinAtt').show();
    $scope.post.getAtt=[];
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getAtt',
                        'ddlTeacher':$scope.temp.ddlTeacher,
                        'txtAttDate':$scope.dateFormat($scope.temp.txtAttDate),
                        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if(data.data.success){

            $scope.post.getAtt=data.data.data;
        }
        $('#spinAtt').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })

}
//    $scope.getAtt();

/* ========== GET Teacher =========== */
$scope.getTeacher = function () {
    $scope.post.getTeacher = [];
    $scope.post.getTeacherProduct=[];
    $scope.post.getTeacherPlans=[];
    if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
    $('.spinTeacher').show();
$http({
    method: 'post',
    url: '../teacher_backoffice/code/TeacherAttendance_code.php',
    data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation,'userrole':$scope.userrole}),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
}).
then(function (data, status, headers, config) {
    // console.log(data.data);
    $scope.post.getTeacher = data.data.data;
    $('.spinTeacher').hide();

    if($scope.userrole == 'TEACHER' || $scope.userrole == 'VOLUNTEER'){
        // alert($scope.userid);
        $scope.temp.ddlTeacher = ($scope.userid).toString();
        $scope.getTeacherPlans();
        $scope.getTeacherProduct();
        $scope.getAtt();
    }
},
function (data, status, headers, config) {
    console.log('Failed');
})
}
// $scope.getTeacher();



/* ========== GET Teacher Plans =========== */
$scope.getTeacherPlans = function () {
    $('.spinPlan').show();
    $http({
        method: 'post',
        url: 'code/Teacher_Product_code.php',
        data: $.param({ 'type': 'getTeacherPlans','ddlTeacher' : $scope.temp.ddlTeacher}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getTeacherPlans = data.data.data;
        $('.spinPlan').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getTeacherPlans();


/* ========== GET TEACHER PRODUCT =========== */
$scope.getTeacherProduct = function () {
    $('.spinProduct').show();
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getTeacherProduct','ddlTeacher':$scope.temp.ddlTeacher}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if(data.data.success){
            $scope.post.getTeacherProduct = data.data.data;
        }else{
            $scope.post.getTeacherProduct = [];
        }
        $('.spinProduct').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getTeacherProduct();
/* ========== GET TEACHER PRODUCT =========== */



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
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    // EDIT
    $scope.edit = function(x){
        console.log(x);
        $scope.temp={
            taid : x.TAID,
            txtAttDate : new Date(x.ATTDATE),
            ddlLocation : (x.LOCID).toString(),
            // ddlTeacher : (x.TEACHERID).toString(),
            // ddlPlan : (x.PLANID).toString(),
            txtTimeIN : $scope.SetTime(x.TIME_IN),
            txtTimeOUT : $scope.SetTime(x.TIME_OUT),
            txtRemark : x.REMARKS,
            txtLearnToday : x.LEARNED_TODAY,
            txtOtherWork : x.OTHER_WORK,
            txtSupervisorComm : x.SUPERVISOR_COMMENT,
        }
        $scope.getTeacher();
        $timeout(()=>{
            $scope.temp.ddlTeacher = (x.TEACHERID).toString();
            $scope.getTeacherProduct();
        },700);
        // $scope.getTeacherPlans();
        $timeout(()=>{
            $scope.temp.ddlProduct1 = x.PRODUCTID1>0 ? (x.PRODUCTID1).toString() : '';
            $scope.temp.ddlProduct2 = x.PRODUCTID2>0 ? (x.PRODUCTID2).toString() : '';
            $scope.temp.ddlProduct3 = x.PRODUCTID3>0 ? (x.PRODUCTID3).toString() : '';
            // $scope.temp.ddlPlan = x.PLANID>0 ? (x.PLANID).toString() : '';
        },1000);
        $scope.index = $scope.post.getAtt.indexOf(x);
    }

    

    //Clear
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtAttDate=new Date();

        $scope.post.getTeacherProduct=[];
        $scope.post.getTeacherPlans=[];
        $scope.getLocations();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'taid': id.TAID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
                    var index = $scope.post.getAtt.indexOf(id);
		            $scope.post.getAtt.splice(index, 1);
                    // $scope.clear();
                    $scope.getAtt();
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