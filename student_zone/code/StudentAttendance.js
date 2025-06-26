
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
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

    /* Go to Admin Dashboard */
    $scope.adminDashboad = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'adminDashboad' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                
            }
            else {
                $scope.loginFailure(data.data.message);
            }
        },
        function (data, status, headers, config) {
            console.log('Login Failed');
        })
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
                $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];
                
                $scope.ActivePlan = data.data.ActivePlan;
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
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
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

                $scope.messageSuccess(data.data.message);
                // $scope.getStudentData(FOR);
                
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
        $scope.post.getAttendance=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAttendance',
                            'txtDate':$scope.dateFormat($scope.temp.txtDate),
                            'ddlLocation':$scope.temp.ddlLocation,
                            'ddlPlan':$scope.temp.ddlPlan,
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
   // $scope.getStudentData();


    /* ========== GET STUDENT =========== */
    $scope.getStudentData = function () {
        $scope.post.getStudentData=[];
        $scope.Att=[];
        $scope.RegID=[];
        if($scope.temp.txtDate!=undefined && $scope.temp.ddlLocation>0 && $scope.temp.ddlPlan>0 && $scope.temp.ddlTeacher){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentData',
                                'txtDate':$scope.dateFormat($scope.temp.txtDate),
                                'ddlLocation':$scope.temp.ddlLocation,
                                'ddlPlan':$scope.temp.ddlPlan,
                                'ddlTeacher':$scope.temp.ddlTeacher}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getStudentData = data.data.data;
                $scope.RegID = data.data.RegID;
                $scope.getAttendance();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        else{
           //  alert("Fill All Fields.");
        }
   }
   // $scope.getStudentData();



    /* ========== DELETE =========== */
    $scope.deleteProduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'productid': id.PRODUCT_ID, 'type': 'deleteProduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getProduct.indexOf(id);
		            $scope.post.getProduct.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }


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
        $http({
            method: 'post',
            url: '../backoffice/code/Teacher_Product_code.php',
            data: $.param({ 'type': 'getTeacher','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacher = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacher();
    
    
    /* ========== GET Location =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getPlans();



   
    


    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: '../backoffice/code/logout.php',
            data: $.param({ 'type': 'logout' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    window.location.assign('../backoffice/index.html#!/login');
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