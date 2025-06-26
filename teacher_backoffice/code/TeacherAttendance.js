
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$filter) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page="ATT";
    $scope.formTitle = '';
    $scope.date = new Date();
    $scope.temp.txtAttDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];
    $scope.PAGEFOR = 'TEACHER';
    
    var url = 'code/TeacherAttendance_code.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */
    
    
    
    
    
    
    /* =============== SET TIME ============== */
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
    /* =============== SET TIME ============== */



    

    /* =============== CHECK SESSION ============== */
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: '../backoffice/code/checkSession.php',
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
                
                if(data.data.locid > 0){
                    $scope.chkAttDT($scope.LOC_ID);
                    $scope.getTeacher();
                }
                // window.location.assign("dashboard.html");
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
    /* =============== CHECK SESSION ============== */





    /* =============== SAVE DATA ============== */
    $scope.Save = function(){
        if(!$scope.temp.txtAttDate || $scope.temp.txtAttDate=='') return;
        $scope.temp.ddlPlan=0;
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
    /* =============== SAVE DATA ============== */





    
    /* ========== GET Att =========== */
    $scope.getAtt = function () {
        $scope.post.getAtt=[];
        if(!$scope.temp.txtAttDate || $scope.temp.txtAttDate=='') return;
        $('#spinAtt').show();
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
    /* ========== GET Att =========== */





   /* ========== GET Teacher =========== */
   $scope.getTeacher = function () {
    $('.spinTeacher').show();
    $scope.post.getTeacherProduct=[];
    $scope.post.getTeacherPlans=[];
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.LOC_ID,'userrole':$scope.userrole}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getTeacher = data.data.data;

        $('.spinTeacher').hide();

        if($scope.userrole == 'TEACHER' || $scope.userrole == 'VOLUNTEER'){
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
    // $scope.getTeacher(); --INIT
    /* ========== GET Teacher =========== */






    /* ========== GET Teacher Plans =========== */
    $scope.getTeacherPlans = function () {
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Teacher_Product_code.php',
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
    /* ========== GET Teacher Plans =========== */
    






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






    /* ========== GET ATT DT =========== */
    $scope.CHK_DT='';
    $scope.chkAttDT = function (locid) {
        if(!locid && locid<=0) return;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'chkAttDT','locid':locid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            console.log(new Date().toLocaleDateString('fr-CA'));
            $scope.CHK_DT = data.data.success ? data.data.data : '';
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.chkAttDT();
    /* ========== GET ATT DT =========== */
    
    
    

    
    
    /* ========== Edit Data =========== */
    $scope.edit = function(x){
        if(x.EDITABLE==0)return;
        $scope.temp={
            taid : x.TAID,
            txtAttDate : new Date(x.ATTDATE),
            // ddlTeacher : (x.TEACHERID).toString(),
            ddlLocation : (x.LOCID).toString(),
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
        // $scope.getTeacherProduct();
        $timeout(()=>{
            // $scope.temp.ddlTeacher = (x.TEACHERID).toString();
            $scope.temp.ddlProduct1 = x.PRODUCTID1>0 ? (x.PRODUCTID1).toString() : '';
            $scope.temp.ddlProduct2 = x.PRODUCTID2>0 ? (x.PRODUCTID2).toString() : '';
            $scope.temp.ddlProduct3 = x.PRODUCTID3>0 ? (x.PRODUCTID3).toString() : '';
            // $scope.temp.ddlPlan = x.PLANID>0 ? (x.PLANID).toString() : '';
        },1000);
        $scope.index = $scope.post.getAtt.indexOf(x);
    }
    /* ========== Edit Data =========== */
    
    


    /* =============== Clear Form ============== */
    $scope.clear=function(){
        // $scope.temp={};
        $scope.temp.taid='';
        $scope.temp.txtTimeIN='';
        $scope.temp.txtTimeOUT='';
        $scope.temp.ddlPlan='';
        $scope.temp.ddlProduct1='';
        $scope.temp.ddlProduct2='';
        $scope.temp.ddlProduct3='';
        $scope.temp.txtAttDate=new Date();
        $scope.temp.txtRemark='';
        $scope.temp.txtLearnToday='';
        $scope.temp.txtOtherWork='';
        $scope.temp.txtSupervisorComm='';

        $scope.post.getTeacherProduct=[];
        $scope.post.getTeacherPlans=[];
    }
    /* =============== Clear Form ============== */






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
    /* ========== DELETE =========== */





    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: '../student_zone/code/logout.php',
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
    /* ========== Logout =========== */
    




    /* =============== Message ============== */
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
    /* =============== Message ============== */




});