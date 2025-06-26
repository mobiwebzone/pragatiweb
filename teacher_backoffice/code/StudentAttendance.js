
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.AttALL='0';
    $scope.editMode = false;
    $scope.Page="ST_ATT";
    $scope.formTitle = '';
    $scope.temp.txtDisplayColor="#000000";
    $scope.temp.txtDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];
    
    var url = 'code/StudentAttendance_code.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 

    // SELECT ALL STUDENT
    $scope.SelectAllST = function(AttALL) {
        // alert($scope.Att.length);
        if(AttALL == 1){
            $scope.Att = new Array($scope.Att.length).fill('1');
        }else{
            $scope.Att = new Array($scope.Att.length).fill('0');
        }
    }    

    // GET DATA
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

    // Save Attendance
    $scope.SaveAttendace = function(){
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
                formData.append("type", 'SaveAttendace');
                formData.append("txtDate", $scope.dateFormat($scope.temp.txtDate));
                formData.append("ddlAttType", $scope.temp.ddlAttType);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("RegID", $scope.RegID);
                formData.append("Att", $scope.Att);
                formData.append("upd", '');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.AttALL='0';

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
    
    /* ========== GET Attendance =========== */
    $scope.getAttendance = function () {

        if($scope.temp.txtDate != undefined && $scope.temp.ddlAttType.length>0 && $scope.temp.ddlLocation>0 && ($scope.temp.ddlPlan>0 || $scope.temp.ddlProduct>0) && $scope.temp.ddlTeacher>0)
        {
            $scope.post.Att=[];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getAttendance',
                                'txtDate':$scope.dateFormat($scope.temp.txtDate),
                                'ddlLocation':$scope.temp.ddlLocation,
                                'ddlPlan':$scope.temp.ddlPlan,
                                'ddlProduct':$scope.temp.ddlProduct,
                                'ddlAttType':$scope.temp.ddlAttType,
                                'ddlTeacher':$scope.temp.ddlTeacher}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
    
                    $scope.Att = data.data.Att;
                }
                // $scope.post.getAttendance = data.data.data;
                // $scope.temp.txtDate=new date
                // $scope.RegID = data.data.RegID;
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }

   }
   // $scope.getStudentData();


    /* ========== GET STUDENT =========== */
    $scope.getStudentData = function () {
        $scope.post.getStudentData=[];
        $scope.checkattEquel=false;
        $scope.Att=[];
        $scope.RegID=[];
        if($scope.temp.txtDate!=undefined && $scope.temp.ddlAttType.length>0 && $scope.temp.ddlLocation>0 && ($scope.temp.ddlPlan>0 || $scope.temp.ddlProduct>0)){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentData',
                                'txtDate':$scope.dateFormat($scope.temp.txtDate),
                                'ddlLocation':$scope.temp.ddlLocation,
                                'ddlAttType':$scope.temp.ddlAttType,
                                'ddlPlan':$scope.temp.ddlPlan,
                                'ddlProduct':$scope.temp.ddlProduct,
                                'ddlTeacher':$scope.temp.ddlTeacher}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getStudentData = data.data.data;
                    $scope.RegID = data.data.RegID;
                    $scope.getAttendance();
    
                    const allEqual = arr => arr.every(val => val === arr[0]);
                    $scope.checkattEquel=allEqual(data.data.ATTEN);   // true
    
                    // alert(data.data.ATTEN[0] +'----'+ $scope.AttALL);
    
                    if($scope.checkattEquel==true && data.data.ATTEN[0] == 1){
    
                        $scope.AttALL='1';
                       
                        // console.log($scope.checkattEquel);
                    }else{
                        $scope.AttALL='0';
                        // document.getElementById("AttALL").checked = false;
                        // console.log($scope.checkattEquel);
                    }
                }else{
                    $scope.post.getStudentData=[];
                    $scope.checkattEquel=false;
                    $scope.Att=[];
                    $scope.RegID=[];
                }
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        else{
            // alert("Fill All Fields.");
        }
   }
   // $scope.getStudentData();



    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getLocations();

    /* ========== GET Teacher =========== */
    $scope.getTeacher = function () {
        $('.spinTeacher').show();
        $http({
            method: 'post',
            url: '../backoffice/code/Teacher_Product_code.php',
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacher = data.data.data;
            // alert(data.data.data[0]['TEACHERID']);
            $scope.temp.ddlTeacher = ($scope.userid).toString();
            $scope.getPlans();
            $('.spinTeacher').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacher();
    
    
    /* ========== GET Location =========== */
    $scope.getPlans = function () {
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans', 'ddlTeacher':$scope.temp.ddlTeacher}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans();



    /* ========== GET TEACHER PRODUCT =========== */
    $scope.getTeacherProduct = function () {
        $('.spinProduct').show();
        $http({
            method: 'post',
            url: 'code/TeacherAttendance_code.php',
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




    // =================================================== Class/Home Work ===========================================
   
    // Save C/H WORK
    $scope.saveCH_Work = function(){
        $(".btn-save-CH").attr('disabled', 'disabled');
        $(".btn-save-CH").text('Saving...');
        $(".btn-update-CH").attr('disabled', 'disabled');
        $(".btn-update-CH").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCH_Work');
                formData.append("hwid", $scope.temp.hwid);
                formData.append("txtDate", $scope.dateFormat($scope.temp.txtDate));
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtCW", $scope.temp.txtCW);
                formData.append("txtHW", $scope.temp.txtHW);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.messageSuccess(data.data.message);
                $scope.getCH_Work();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-CH').removeAttr('disabled');
            $(".btn-save-CH").text('SAVE');
            $('.btn-update-CH').removeAttr('disabled');
            $(".btn-update-CH").text('UPDATE');
        });
    }

    /* ========== GET C/H WORK =========== */
    $scope.getCH_Work = function () {
        $scope.post.getCH_Work=[];

        if($scope.temp.txtDate!=undefined && $scope.temp.ddlLocation>0 && ($scope.temp.ddlPlan>0 || $scope.temp.ddlProduct>0)){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getCH_Work',
                                'txtDate':$scope.dateFormat($scope.temp.txtDate),
                                'ddlLocation':$scope.temp.ddlLocation,
                                'ddlPlan':$scope.temp.ddlPlan,
                                'ddlProduct':$scope.temp.ddlProduct,
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
    
                    $scope.post.getCH_Work = data.data.data;
                    $scope.temp.hwid = data.data.data[0]['HWID'];
                    $scope.temp.txtCW = data.data.data[0]['CLASSWORK'];
                    $scope.temp.txtHW = data.data.data[0]['HOMEWORK'];
    
                }
                else{
                    $scope.temp.hwid = 0;
                    $scope.temp.txtCW = '';
                    $scope.temp.txtHW = '';
                }
                // $scope.post.getCH_Work = data.data.data;
                // $scope.temp.txtDate=new date
                // $scope.RegID = data.data.RegID;
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }

   }
   // $scope.getStudentData();
    


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