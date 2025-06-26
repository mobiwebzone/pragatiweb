$postModule = angular.module("myApp", ["ngSanitize"]);
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
    $scope.editModeStudent = false;
    $scope.Page = "MISC";
    $scope.PageSub = "REFERRAL";
    $scope.temp.txtCoverageDT = new Date();

    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 30);
    // $scope.txtFromDT=new Date($scope.FromDT_S).toLocaleDateString('sv-SE');

    $scope.temp.txtFromDT = new Date($scope.FromDT_S);
    $scope.temp.txtToDT = new Date();
    

    
    var url = 'code/Referrals_bg.php';



    // =============== Check Session =============
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

                // $scope.getPlans();
                $scope.getReferrals();
                // $scope.getStudentCourseCoverage();
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
    // =============== Check Session =============



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENT COURSE COVERAGE SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


    // =========== SAVE PAYMENT ==============
    $scope.savePayment = function(id,regid){
        console.log(id);
        var r = confirm("Are you sure want to save this transaction?");
        if(r){
            // console.log(`regid : ${regid}`);
            // console.log(`AMOUNT : ${id['AMOUNT']} || TRANSID : ${id['TRANSID']} || TRANSDATE : ${id['TRANSDATE']}`);
            $(".btnPay").attr('disabled', 'disabled');
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'savePayment');
                    formData.append("REGID", regid);
                    formData.append("TID", id['TID']);
                    formData.append("AMOUNT", id['AMOUNT']);
                    formData.append("TRANSID", id['TRANSID']);
                    formData.append("TRANSDATE", id['TRANSDATE']);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.getOnlineTransactions();
                    // $scope.clearForm();
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
                $(".btnPay").removeAttr('disabled');
            });
        }
    }
    // =========== SAVE PAYMENT ==============






    /* ========== GET REFERRALS =========== */
    $scope.getReferrals = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReferrals',
                            'txtFromDT':$scope.temp.txtFromDT.toLocaleDateString(),
                            'txtToDT':$scope.temp.txtToDT.toLocaleDateString()
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getReferrals_Student = data.data.success ? data.data.student : [];
            $scope.post.getReferrals_Teacher = data.data.success ? data.data.teacher : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
    }
    // $scope.getReferrals(); --INIT
    /* ========== GET REFERRALS =========== */






    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.success ? data.data.data : [];
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */








   /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 
    $scope.getStudentByPlanProduct = function () {
        $('.spinStudent').show();
        // $FINAL_PRODUCTID = [];
        // $FINAL_PRODUCTID = $scope.PRODUCTS_model.map(x=>x.id);
        $FINAL_PLANID = [];
        $FINAL_PLANID = $scope.PLANS_model.map(x=>x.id);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByPlanProduct', 
                            'PLANID' : $FINAL_PLANID,
                            // 'PRODUCTID' : $FINAL_PRODUCTID
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByPlanProduct = data.data.success ? data.data.data : [];
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlanProduct();
    /*============ GET STUDENT BY PLAN_PRODUCT =============*/ 




    $scope.getTableData = function(id){
        $scope.temp.ddlStatus = id.REF_STATUS;
        $scope.temp.txtRemarkUser = id.REF_REMARKS;
        $scope.temp.txtRemarkOffice = id.REF_NOTES_OFFICE;
        $scope.temp.txtReferredAmtPaid = id.REFERRED_AMT > 0 ? Number(id.REFERRED_AMT):'';
        $scope.temp.txtReferredPaidOn = id.REFERRED_ON !='' ? new Date(id.REFERRED_ON) : '';
        $scope.temp.txtReferralAmtPaid = id.REFERRAL_AMT > 0 ? Number(id.REFERRAL_AMT):'';
        $scope.temp.txtReferralPaidOn = id.REFERRAL_ON !='' ? new Date(id.REFERRAL_ON) : '';
        $scope.GET_REFID = id.REFID;
        $scope.GET_REFMID = id.REFMID;
        $scope.GET_REFBY_NAME = id.REFBY_NAME;
    }

    /* ========== UPDATE REFERRAL =========== */
    $scope.UpdateReferral = function () {
        // var r = confirm("Are you sure want to delete this transaction!");
        // if (r == true) {
            $('.btnDelete').attr('disabled','disabled');
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'UpdateReferral',
                                'REFID': $scope.GET_REFID, 
                                'ddlStatus':$scope.temp.ddlStatus,
                                'txtRemarkUser': $scope.temp.txtRemarkUser, 
                                'txtRemarkOffice': $scope.temp.txtRemarkOffice, 
                                'txtReferredAmtPaid': $scope.temp.txtReferredAmtPaid,
                                'txtReferredPaidOn': (!$scope.temp.txtReferredPaidOn || $scope.temp.txtReferredPaidOn=='')?'':$scope.temp.txtReferredPaidOn.toLocaleDateString('sv-SE'),
                                'txtReferralAmtPaid': $scope.temp.txtReferralAmtPaid,
                                'txtReferralPaidOn': (!$scope.temp.txtReferralPaidOn || $scope.temp.txtReferralPaidOn=='')?'':$scope.temp.txtReferralPaidOn.toLocaleDateString('sv-SE'),
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                console.log(data.data)
		        if (data.data.success) {
                    $scope.getReferrals();
                    $('#UpdateReferral').modal('hide');
                    $('.btnDelete').removeAttr('disabled');
		            // console.log(data.data.message)
                    $scope.temp.ddlStatus='';
                    $scope.temp.txtRemarkUser='';
                    $scope.temp.txtRemarkOffice='';
                    $scope.temp.txtReferredAmtPaid='';
                    $scope.temp.txtReferredPaidOn='';
                    $scope.temp.txtReferralAmtPaid='';
                    $scope.temp.txtReferralPaidOn='';
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        // }
    }
    /* ========== UPDATE REFERRAL =========== */



    /* ========== REFERRAL PAYMENT =========== */
    // $scope.ReferralPayment = function () {
    //     $('.btnPay').attr('disabled','disabled');
    //     $http({
    //         method: 'post',
    //         url: url,
    //         data: $.param({ 'type': 'ReferralPayment',
    //                         'REFID': $scope.GET_REFID, 
    //                         'REFMID': $scope.GET_REFMID, 
    //                         'txtReferredAmtPaid':$scope.temp.txtReferredAmtPaid,
    //                         'txtReferredPaidOn':$scope.temp.txtReferredPaidOn,
    //                         'txtReferralAmtPaid':$scope.temp.txtReferralAmtPaid,
    //                         'txtReferralPaidOn':$scope.temp.txtReferralPaidOn,
    //                         'txtRemark':$scope.temp.txtRemark,
    //                     }),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data)
    //         if (data.data.success) {
    //             $scope.getReferrals();
    //             $('#PaymentReferral').modal('hide');
    //             $('.btnPay').removeAttr('disabled');
    //             // console.log(data.data.message)
    //             $scope.temp.ddlStatus='';
    //             $scope.temp.txtRemarkUser='';
    //             $scope.temp.txtRemarkOffice='';
                
    //             $scope.messageSuccess(data.data.message);
    //         } else {
    //             $scope.messageFailure(data.data.message);
    //         }
    //     })
    // }
    /* ========== REFERRAL PAYMENT =========== */
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENTS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%







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
    /* ========== Logout =========== */




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