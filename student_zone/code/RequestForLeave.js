

$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = 'REQUEST';
    $scope.PageSub = 'RFL';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.RegID=[];
    $scope.Att=[];
    
    var url = 'code/RequestForLeave_code.php';

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
                    $scope.USER_LOCATION=data.data.LOCATION;
                    $scope.REGID=data.data.data[0]['REGID'];
                    $scope.PLAN=data.data.data[0]['PLAN'];
                    $scope.GRADE=data.data.data[0]['GRADE'];
                    $scope.LOCID=data.data.data[0]['LOCATIONID'];
                    $scope.PLANID=data.data.data[0]['PLANID'];
                    $scope.LOC_CONTACT=data.data.data[0]['LOC_CONTACT'];
                    $scope.LOC_EMAIL=data.data.data[0]['LOC_EMAIL'];
    
                    if($scope.REGID != undefined){
                        $scope.getRFL();
                    }
                    
                    $scope.ActivePlan = data.data.ActivePlan;
                    if(data.data.ActivePlan == 'YES'){
                        window.location.assign('dashboard.html#!/dashboard');
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
                formData.append("reqid", $scope.temp.reqid);
                formData.append("txtFromDate", $scope.dateFormat($scope.temp.txtFromDate));
                formData.append("txtToDate", $scope.dateFormat($scope.temp.txtToDate));
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("REGID", $scope.REGID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) { 
                
                $scope.getRFL();
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
    
    /* ========== GET RFL =========== */
    $scope.getRFL = function () {
        $('.spinMainData').show();
        $scope.post.getRFL=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFL',
            'REGID':$scope.REGID,
            'GET_FOR':'STUDENT',
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
            if(data.data.success){
                $scope.post.getRFL=data.data.data;
            }
            $('.spinMainData').hide();
            // $scope.post.getAttendance = data.data.data;
            // $scope.temp.txtDate=new date
            // $scope.RegID = data.data.RegID;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
//    $scope.getRFL();



    // EDIT
    $scope.edit = function(x){
        if(x.CANCELLED <= 0){
            $scope.temp={
                reqid : x.REQID,
                txtFromDate : new Date(x.FROMDT),
                txtToDate : new Date(x.TODT),
                txtRemark : x.REMARKS,
            }
    
            $scope.index = $scope.post.getRFL.indexOf(x);
        }
    }

    //Clear
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
        $scope.temp.txtToDate=new Date();
    }

    // Cancel Modal
    $scope.CancelModal=function(id){
        $scope.CANCELREQID = id.REQID;
    }

    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'reqid': id.REQID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    $scope.clear();
                    $scope.getRFL();
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