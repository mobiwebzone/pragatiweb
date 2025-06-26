
$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = 'REQUEST';
    $scope.PageSub = 'RFV';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.ADMIN = false;
    
    var url = 'code/RequestForVolunteer_code.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */


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
                    $scope.getRFV();
                    $scope.getStudentProduct();
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


    /* =================== SAVE DATA =================== */
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
                formData.append("vrid", $scope.temp.vrid);
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
                $scope.GET_VRID = data.data.VRID;
                $scope.temp.vrid = data.data.VRID;
        
                $scope.getRFV();
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
    /* =================== SAVE DATA =================== */
    
    // Add Product
    $scope.AddProduct = function(){
        $(".btn-add").attr('disabled', 'disabled');
        $(".btn-add").text('Add...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'AddProduct');
                formData.append("vrdid", $scope.temp.vrdid);
                formData.append("GET_VRID", $scope.GET_VRID);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) { 
                $scope.getRFVD($scope.GET_VRID);
                $scope.getRFV();
            
                $scope.messageSuccess(data.data.message);
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-add').removeAttr('disabled');
            $(".btn-add").text('Add');
        });
    }
    
    /* ========== GET RFV =========== */
    $scope.getRFV = function () {
        // alert($scope.REGID);
        $('.spinMainData').show();
        $scope.post.getRFV=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFV',
                            'REGID':$scope.REGID,
                            'GET_FOR':'STUDENT',
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRFV=data.data.data;
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
//    $scope.getRFV();


/* ========== GET RFVD =========== */
    $scope.getRFVD = function (GET_VRID) {
        $('.spinProduct').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRFVD',
                            'GET_VRID':$scope.GET_VRID,
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRFVD=data.data.data;
            }
            $('.spinProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
//    $scope.getRFV();



/* ========== GET Student Product =========== */
    $scope.getStudentProduct = function () {
        $scope.post.getStudentProduct=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentProduct',
                            'REGID':$scope.REGID,
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){

                $scope.post.getStudentProduct=data.data.data;
            }

        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

   }
//    $scope.getStudentProduct();



    // EDIT
    $scope.edit = function(x){
        if(x.APPROVED > 0 || x.CANCELLED > 0){
            console.log('Edit not allowed after admin response.');
        }
        else{
            $scope.temp={
                vrid : x.VRID,
                txtFromDate : new Date(x.FROMDT),
                txtToDate : new Date(x.TODT),
                txtRemark : x.REMARKS,
            }
            $scope.GET_VRID=x.VRID;
            $scope.getRFVD(x.VRID);
            // $scope.index = $scope.post.getRFV.indexOf(id);
        }
    }

    //Clear
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
        $scope.temp.txtToDate=new Date();
        $scope.GET_VRID=0;
        $scope.post.getRFVD=[];
        $scope.temp.ddlProduct='';
    }

    // Cancel Modal
    $scope.CancelModal=function(id){
        $scope.CANCELVRID = id.VRID;
    }

    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'vrid': id.VRID,'txtCancelRemark':$scope.temp.txtCancelRemark, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    // $('#cancelModal').trigger({type:"click"});
                    $scope.clear();
                    $scope.getRFV();
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    
    
    /* ========== Cancel Peoduct =========== */
    $scope.CancelPeoduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'vrdid': id.VRDID, 'type': 'CancelPeoduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    $scope.getRFV();
                    $scope.getRFVD();
                    $scope.post.getRFVD=[];
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