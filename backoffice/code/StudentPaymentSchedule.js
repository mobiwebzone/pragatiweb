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
    $scope.formTitle = '';
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_PAYMENT_Schedule";
    $scope.temp.regid=0;
    $scope.temp.planid=0;
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    
    var url = 'code/StudentPaymentSchedule_code.php';



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
                // window.location.assign("dashboard.html");

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
                    // $scope.getPayments();
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


    /* ========== GET STUDENT =========== */
    $scope.getStudent = function () {
        $scope.post.getStudent=[];
        $scope.post.getStudentPlan=[];
        $scope.temp.regid=0;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudent',
                            'ddlLocation':$scope.temp.ddlLocation,
                            // 'ddlPlan':$scope.temp.ddlPlan
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudent = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudent();
     

    
    /* ========== GET Student Plans =========== */
    $scope.getStudentPlan = function (id) {
        $scope.temp.regid=id.REGID;
        $scope.post.getStudentPlan=[];
        $scope.temp.planid=0;
        // alert($scope.temp.regid);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentPlan',
                            'REGID':$scope.temp.regid,
                            'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentPlan = data.data.data;
            window.location.hash = '#PLANS_TAB';
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans();




    /* ========== SELECT PLAN =========== */
    $scope.SelectPlan = function (id) {
        $scope.temp.planid=id.PLANID;
        $scope.temp.PLANNAME = id.PLANNAME;
        // $scope.temp.txtAmt = Number(id.INST_AMOUNT);
        $scope.temp.ActPrice = id.PRICE;
        $scope.temp.Act_Inst_Price = id.INST_AMOUNT;
        $scope.temp.Act_Inst = id.INST_NO;
        window.location.hash = '#ddlPayType';
        
        $scope.getSelectedPlan_Record();
    }




    /* ========== GET Selected Plan Payment Record =========== */
    $scope.getSelectedPlan_Record = function () {
        // alert($scope.temp.regid);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSelectedPlan_Record',
                            'ddlLocation':$scope.temp.ddlLocation,
                            'regid':$scope.temp.regid,
                            'planid':$scope.temp.planid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.editMode=true;
                $scope.temp.psid = data.data.data['PSID'];
                $scope.temp.ddlPayType = data.data.data['PAYPLAN'];
                $scope.temp.txtAmt = Number(data.data.data['AMOUNT']);
                $scope.temp.txtInstallments = Number(data.data.data['INSTALLMENTS']);
                $scope.temp.txtRemark = data.data.data['REMARKS'];
            }else{
                $scope.editMode=false;
                $scope.temp.psid = '';
                $scope.temp.ddlPayType = '';
                $scope.temp.txtAmt = '';
                $scope.temp.txtRemark = '';
            }
            // $scope.post.getSelectedPlan_Record = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedPlan_Record();


    

    // ====== Set Amount Payment type =======
    $scope.setAmount = function (amtType) {
        $scope.temp.txtAmt = (amtType == 'ONE TIME') ? (Number($scope.temp.ActPrice)) : ((amtType == 'INSTALMENT') ? (Number($scope.temp.Act_Inst_Price)) : (''));
    }


    /* ========== PAY =========== */
    $scope.Pay = function(){
        if($scope.temp.psid > 0){
            $(".btn-pay").attr('disabled', 'disabled');
            $(".btn-pay").text('Updating..');
        }else{
            $(".btn-pay").attr('disabled', 'disabled');
            $(".btn-pay").text('Saving...');
        }

        $scope.Act_Inst = $scope.temp.ddlPayType == 'ONTIME' ? 1 : $scope.temp.Act_Inst;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Pay');
                formData.append("psid", $scope.temp.psid);
                formData.append("regid", $scope.temp.regid);
                formData.append("planid", $scope.temp.planid);
                formData.append("ActPrice", $scope.temp.ActPrice);
                formData.append("ActInstPrice", $scope.temp.Act_Inst_Price);
                formData.append("ActInst", $scope.Act_Inst);
                formData.append("ddlPayType", $scope.temp.ddlPayType);
                formData.append("txtAmt", $scope.temp.txtAmt);
                formData.append("Installment", $scope.temp.txtInstallments);
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                if($scope.editMode == false){
                    $scope.temp.ddlPayType = '';
                    $scope.temp.txtAmt = '';
                    $scope.temp.txtRemark = '';
                    $scope.getSelectedPlan_Record();
                }
                $scope.getPayments();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            if($scope.temp.psid > 0){
                $('.btn-pay').removeAttr('disabled');
                $(".btn-pay").text('UPDATE');
            }else{
                $('.btn-pay').removeAttr('disabled');
                $(".btn-pay").text('SAVE');
            }
        });
    }

    

    /* ========== GET Payments =========== */
    $scope.TopRecord=500;
    $scope.getPayments = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.spinMainData').show();
        // alert($scope.temp.regid);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPayments','ddlLocation':$scope.temp.ddlLocation,'TopRecord':$scope.TopRecord}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPayments = data.data.data;
            $('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPayments(); --INIT



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
            if($scope.temp.ddlLocation > 0) $scope.getStudent();
            if($scope.temp.ddlLocation > 0) $scope.getPayments();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */
    


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        // alert(id.INSTALLMENTS);
        // document.getElementById("txtLocation").focus();
        $scope.temp = {
            psid:id.PSID,
            ddlLocation: (id.LOCATIONID).toString(),
        };

        if($scope.temp.ddlLocation > 0){
            $scope.getStudent();
            
            $scope.temp.regid=id.REGID;
            if($scope.temp.regid > 0){

                $scope.getStudentPlan(id);
    
                $scope.temp.planid=id.PLANID;
                if($scope.temp.planid > 0){
                    $scope.temp.PLANNAME = id.PLANNAME;
                    $scope.temp.ActPrice = id.ACTPRICE;
                    $scope.temp.Act_Inst_Price = id.ACTINST_AMOUNT;
                    $scope.temp.Act_Inst = id.ACTINSTALLMENTS;
                    window.location.hash = '#ddlPayType';
                    
                    $scope.temp.ddlPayType = id.PAYPLAN;
                    $scope.temp.txtAmt = Number(id.AMOUNT);
                    $scope.temp.txtInstallments = Number(id.INSTALLMENTS);
                    $scope.temp.txtRemark = id.REMARKS;
                }
            }
        }

        $scope.editMode = true;
        $scope.index = $scope.post.getPayments.indexOf(id);
    }


    


    /* ========== Clear Button =========== */
    $scope.Clear = function () {  
        $scope.temp={};

        $scope.post.getStudent=[];
        $scope.post.getStudentPlan=[];

        $scope.temp.regid=0;
        $scope.temp.planid=0;
        $scope.temp.psid=0;
        $scope.editMode = false;
    } 



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'PSID': id.PSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getPayments.indexOf(id);
		            $scope.post.getPayments.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.Clear();
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



});