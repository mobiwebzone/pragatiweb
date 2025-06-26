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
    $scope.Page = "L&A";
    $scope.PageSub = "HourlyTutoring";
    $scope.PageSub1 = "ST_HOURLY_ATTENDANCE";
    $scope.date = new Date();
    $scope.temp.txtAttDate=new Date();
    $scope.selectedStudent=[];
    $scope.PAGEFOR = 'ADMIN';
    
    var url = 'code/HourlyAttendance.php';
    var Masterurl = 'code/MASTER_API.php';

    /* =============== DATE CONVERT ============== */
    function getTotalTime(dt1, dt2) {
        const date1 = new Date(dt1);
        const date2 = new Date(dt2);
    
        if (date2 < date1) {
            date2.setDate(date2.getDate() + 1);
        }
    
        const diffInMillis = date2 - date1;
        const seconds = Math.floor(diffInMillis / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
    
        const formattedHours = (hours % 24).toString().padStart(2, '0');
        const formattedMinutes = (minutes % 60).toString().padStart(2, '0');
        const formattedSeconds = (seconds % 60).toString().padStart(2, '0');
    
        // return formattedHours + ':' + formattedMinutes + ':' + formattedSeconds;
        return formattedHours;
    }
    
    // Example usage:
    // const dt1 = '2024-02-01 12:00:00';
    // const dt2 = '2024-02-02 14:30:45';
    // const result = getTotalTime(dt1, dt2);
    // console.log(result);

    $scope.checkTotalTime=function(){
        if(!$scope.temp.txtTimeIN || $scope.temp.txtTimeIN=='' || !$scope.temp.txtTimeOUT || $scope.temp.txtTimeOUT==''){
            alert("Invalid Att From Time or Att To Time.");
        }else{
            var dt1 = $scope.temp.txtTimeIN.toLocaleString('sv-SE');
            var dt2 = $scope.temp.txtTimeOUT.toLocaleString('sv-SE');
            var totalHours = getTotalTime(dt1, dt2);
            console.info(`TOTAL HOURS : ${totalHours}`);
            if(totalHours>3){
                $('#warningHours').modal('show');
            }else{
                $scope.Save();
            }
        }
    }

    $scope.focusToTime = function(){
        $timeout(()=>{$('#txtTimeOUT').focus();},700);
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
                    $scope.getClassSubjectMaster();
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

    $scope.setFromMin = function(){
        $scope.FromMin ='';
        if(!$scope.temp.txtTimeIN || $scope.temp.txtTimeIN=='') return;
        $scope.FromMin = $scope.temp.txtTimeIN.toLocaleTimeString('sv-SE',{ hour: '2-digit', minute: '2-digit' });
    }

    $scope.Save = function(){        
        // alert($scope.temp.txtTimeOUT.toLocaleTimeString('sv-SE'));
        
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Save');
                formData.append("attid", $scope.temp.attid);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlSubject", $scope.temp.ddlSubject);                
                formData.append("txtAttDate", $scope.temp.txtAttDate.toLocaleString('sv-SE'));
                formData.append("selectedStudent", JSON.stringify($scope.selectedStudent));
                formData.append("txtTimeIN", $scope.temp.txtTimeIN.toLocaleString('sv-SE'));
                formData.append("txtTimeOUT", $scope.temp.txtTimeOUT.toLocaleString('sv-SE'));
                formData.append("txtRemark", $scope.temp.txtRemark);         
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) { 
                $('#warningHours').modal('hide');
                $scope.temp.attid='';
                $scope.temp.ddlSubject='';
                $scope.temp.txtTimeIN='';
                $scope.temp.txtTimeOUT='';
                $scope.temp.txtRemark='';
                $scope.temp.txtAttDate=new Date();
                $scope.post.getStudentByTeacher=[];
                $scope.selectedStudent=[];
                $scope.temp.studentcheck={};

                $scope.getAtt();
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
                            'ddlLocation':$scope.temp.ddlLocation,
                            'ddlTeacher':$scope.temp.ddlTeacher,
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
    //$scope.getAtt();

    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $scope.post.getTeacher = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinTeacher').show();
    $http({
        method: 'post',
        url: url,
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
            $scope.getAtt();
        }
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getTeacher();



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
            if($scope.temp.ddlLocation > 0) $scope.getAtt();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET CLASS SUBJECTS =========== */
    $scope.getClassSubjectMaster = function () {
        $scope.spinSubject =  true;
        $http({
            method: 'post',
            url: Masterurl,
            data: $.param({ 'type': 'getClassSubjectMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            //console.log(data.data);
            $scope.post.getClassSubjectMaster = data.data.success ? data.data.data : [];
            $scope.spinSubject =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubjectMaster(); --INIT
        /* ========== GET SUBJECTS =========== */
    
    /* ========== GET CLASS SUBJECTS =========== */
    $scope.getStudentByTeacher = function () {
        $scope.post.getStudentByTeacher=[];
        $scope.selectedStudent=[];
        $scope.temp.studentcheck={};
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0)return;
        $scope.spinSubject =  true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByTeacher','ddlTeacher':$scope.temp.ddlTeacher,'REGID':$scope.GET_REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            //console.log(data.data);
            $scope.post.getStudentByTeacher = data.data.success ? data.data.data : [];
            $scope.spinSubject =  false;
            if($scope.GET_REGID>0  && $scope.post.getStudentByTeacher.length>0)
            {
                $scope.selectedStudent.push($scope.post.getStudentByTeacher[0]);
                $scope.temp.studentcheck = {0:true};
                
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubjectMaster(); --INIT
        /* ========== GET Location =========== */

    $scope.selectStudent = function (id,val)
    {
        if(val){   
            $scope.selectedStudent.push(id);
        }
        else{
            var index = $scope.selectedStudent.indexOf(id);
		    $scope.selectedStudent.splice(index, 1);
        }
    }


    // EDIT
    $scope.edit = function(x){
        // console.log(x);
        $('#ddlSubject').focus();
        $scope.GET_REGID=x.REGID;
        $scope.temp.attid = x.ATTID;
        $scope.temp.ddlTeacher = (x.TEACHERID).toString();
        $scope.temp.ddlSubject = (x.CSUBID).toString();
        $scope.temp.txtAttDate = new Date(x.ATTDATE);
        $scope.temp.txtTimeIN = new Date('2023-01-01T'+x.ATTFROMTIME_SET);
        $scope.setFromMin();
        $scope.temp.txtTimeOUT = new Date('2023-01-01T'+x.ATTTOTIME_SET);
        $scope.temp.txtRemark = x.REMARKS;
        $scope.index = $scope.post.getAtt.indexOf(x);

        $scope.getStudentByTeacher();
    }

    

    //Clear
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtAttDate=new Date();
        $scope.getLocations();
        $scope.post.getStudentByTeacher=[];
        $scope.selectedStudent=[];
        $scope.temp.studentcheck={};
        $scope.GET_REGID='';
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ATTID': id.ATTID, 'type': 'delete' }),
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