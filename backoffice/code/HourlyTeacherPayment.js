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
    $scope.PageSub1 = "T_HOURLY_PAYMENT";
    $scope.date = new Date();
    $scope.temp.txtPaymentDate=new Date();
    $scope.studentPay=[];
    $scope.PAGEFOR = 'ADMIN';
    
    var url = 'code/HourlyTeacherPayment.php';
    var Masterurl = 'code/MASTER_API.php';


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
                    $scope.getPaymentModes();
                    // $scope.getClassSubjectMaster();
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
        if(!$scope.temp.txtTotalAmount || $scope.temp.txtTotalAmount<=0) return
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Save');
                formData.append("paymentid", $scope.temp.paymentid);
                formData.append("txtPaymentDate", $scope.temp.txtPaymentDate.toLocaleString('sv-SE'));
                formData.append("ddlTeacher", $scope.temp.ddlTeacher);
                formData.append("txtTotalAmount", $scope.temp.txtTotalAmount);
                formData.append("ddlPaymode", $scope.temp.ddlPaymode);         
                formData.append("txtRefeNo", $scope.temp.txtRefeNo);         
                formData.append("txtRefDate", !$scope.temp.txtRefDate || $scope.temp.txtRefDate=='' ? '' : $scope.temp.txtRefDate.toLocaleString('sv-SE'));
                formData.append("paymentDetails", JSON.stringify($scope.post.getStudentByTeacher));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) { 
                $scope.temp.paymentid = '';
                $scope.temp.txtPaymentDate=new Date();
                $scope.temp.ddlPaymode = '';
                $scope.temp.txtRefeNo = '';
                $scope.temp.txtRefDate = '';

                // $scope.clearStudent();
                
                $scope.messageSuccess(data.data.message);
                $scope.getPayment();
                $scope.getStudentByTeacher();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    
    /* ========== GET PAYMENT =========== */
    $scope.getPayment = function () {
        $('#spinPay').show();
        $scope.post.getPayment=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPayment',
                            // 'ddlLocation':$scope.temp.ddlLocation,
                            'ddlTeacher':$scope.temp.ddlTeacher
                            }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getPayment=data.data.data;
            }
            $('#spinPay').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })

    }
    //$scope.getPayment();

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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.LOC_ID.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getTeacher();
            // if($scope.temp.ddlLocation > 0) $scope.getAtt();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ========== GET CLASS SUBJECTS =========== */
    $scope.getPaymentModes = function () {
        $scope.spinPM =  true;
        $http({
            method: 'post',
            url: Masterurl,
            data: $.param({ 'type': 'getPaymentModesMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPaymentModes = data.data.success ? data.data.data : [];
            $scope.spinPM =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPaymentModes(); --INIT
        /* ========== GET SUBJECTS =========== */
    
    /* ========== GET CLASS SUBJECTS =========== */
    $scope.getStudentByTeacher = function () {
        $scope.selectedName = !$scope.temp.ddlTeacher || $scope.temp.ddlTeacher==0 ? '-' : $('#ddlTeacher option:selected').text();
        $scope.post.getStudentByTeacher=[];
        $scope.temp.txtTotalAmount = 0;
        $scope.studentPay=[];
        if(!$scope.temp.ddlTeacher || $scope.temp.ddlTeacher<=0)return;
        $scope.spinSubject =  true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByTeacher','ddlTeacher':$scope.temp.ddlTeacher,'REGID':0}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getStudentByTeacher = data.data.success ? data.data.data : [];
            $scope.spinSubject =  false;
            $scope.temp.txtTotalAmount = $scope.getTotalAmount($scope.post.getStudentByTeacher);
            // if($scope.REGID>0  && $scope.post.getStudentByTeacher.length>0)
            // {
            //     $scope.temp.ddlStudent = $scope.REGID.toString();
            //     $scope.studentDetails();
                
            // }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubjectMaster(); --INIT
        /* ========== GET Location =========== */

    $scope.post.studentListDetails = [];
    $scope.selectedName = '-';
    $scope.studentDetails = function(){
        $scope.post.studentListDetails = [];
        $scope.studentPay=[];
        $scope.selectedName='-';
        $scope.temp.txtTotalAmount = 0;
        if(!$scope.temp.ddlStudent || $scope.temp.ddlStudent<=0)return;
        $scope.post.studentListDetails = angular.copy($scope.post.getStudentByTeacher.filter(x=>x.REGID==$scope.temp.ddlStudent))
        // GET TOTAL AMOUNT
        $scope.temp.txtTotalAmount = $scope.getTotalAmount($scope.post.studentListDetails);
        // console.log( $scope.post.studentListDetails)
        $scope.selectedName = $('#ddlStudent option:selected').text();
    }

    $scope.setPayAmount = function (id,val,index)
    {
        $scope.post.getStudentByTeacher[index]['PAY'] = (!val) ? 0 : Number(val);
        // GET TOTAL AMOUNT
        $scope.temp.txtTotalAmount = $scope.getTotalAmount($scope.post.getStudentByTeacher);
    }

    $scope.getTotalAmount = function(data){
        return !data ? 0 : data.map(x=>x.PAY).reduce((a, b) => a + b, 0);
    }


    // EDIT
    $scope.edit = function(x){
        // console.log(x);
        $('#ddlLocation,#ddlTeacher').attr('disabled','disabled');
        $scope.temp.paymentid = x.PAYMENTID;
        $scope.temp.txtTotalAmount = Number(x.AMOUNT);
        $scope.temp.txtPaymentDate = new Date(x.PAYMENTDATE);
        $scope.temp.ddlPaymode = x.PMID.toString();
        $scope.temp.txtRefeNo = x.REFNO;
        $scope.temp.txtRefDate = (!x.REFDATE || x.REFDATE=='') ? '' : new Date(x.REFDATE);

        $scope.index = $scope.post.getPayment.indexOf(x);
        // $scope.getStudentByTeacher();
    }

    

    //Clear
    $scope.clear=function(){
        $('#ddlLocation,#ddlTeacher').removeAttr('disabled');
        $scope.temp={};
        $scope.temp.txtPaymentDate=new Date();
        $scope.post.getPayment = [];
        $scope.temp.txtTotalAmount = 0;
        $scope.studentPay=[];
        $scope.selectedName='-';
        $scope.post.getStudentByTeacher = [];
    }

    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'PAYMENTID': id.PAYMENTID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
                    var index = $scope.post.getPayment.indexOf(id);
		            $scope.post.getPayment.splice(index, 1);
                    $scope.getPayment();
                    $scope.getStudentByTeacher();
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