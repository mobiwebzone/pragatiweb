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
    $scope.PageSub = "ONLINE_TRANSACTION";
    $scope.temp.txtCoverageDT = new Date();

    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 30);
    // $scope.txtFromDT=new Date($scope.FromDT_S).toLocaleDateString('sv-SE');

    $scope.temp.txtFromDT = new Date($scope.FromDT_S);
    $scope.temp.txtToDT = new Date();
    

    
    var url = 'code/Online_Transactions.php';

    $scope.setMyOrderBY = function (COL) {
  $scope.myOrderBY =
    COL == $scope.myOrderBY
      ? `-${COL}`
      : $scope.myOrderBY == `-${COL}`
      ? (myOrderBY = COL)
      : (myOrderBY = `-${COL}`);
  console.log($scope.myOrderBY);
};

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
                $scope.getOnlineTransactions();
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



    //======= GET SETTLED/UNSETTLED TRANSACTIONS =========
    $scope.testAuthorizeAPI = function () {
        if(!$scope.temp.txtFromDT || $scope.temp.txtFromDT=='' || !$scope.temp.txtToDT || $scope.temp.txtToDT=='') return;
        $('.btn-GET').html('GET... <i class="fa fa-spin fa-spinner"></i>').attr('disabled','disabled');
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'testAuthorizeAPI',
                            'FromDT':$scope.temp.txtFromDT.toISOString(),
                            'ToDT':$scope.temp.txtToDT.toISOString()
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.messageSuccess(data.data.message);
            }else{
                $scope.messageFailure(data.data.message);
            }
            $scope.getOnlineTransactions();
            //    $scope.post.testAuthorizeAPI = data.data.success ? data.data.data : [];
            $('.btn-GET').html('GET').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.testAuthorizeAPI();
    //======= GET SETTLED/UNSETTLED TRANSACTIONS =========

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






    /* ========== GET ONLINE TRANSACTIONS =========== */
    $scope.getOnlineTransactions = function () {
        // if(($scope.temp.txtFromDT && $scope.temp.txtFromDT!='') && ($scope.temp.txtToDT && $scope.temp.txtToDT!=''))
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getOnlineTransactions',
                            'txtFromDT':$scope.temp.txtFromDT.toLocaleDateString(),
                            'txtToDT':$scope.temp.txtToDT.toLocaleDateString()
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getOnlineTransactions_Settled = data.data.success ? data.data.settled : [];
            $scope.post.getOnlineTransactions_Unsettled = data.data.success ? data.data.unsettled : [];

            // $scope.post.getUnsettledTransactions = data.data.UnsettledSuccess ? data.data.UNSETTLED_TRANS : [];
            // if(!data.data.UnsettledSuccess) $scope.messageFailure(data.data.Unsettledmessage);
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        
    }
    // $scope.getOnlineTransactions(); --INIT
    /* ========== GET ONLINE TRANSACTIONS =========== */






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





    /* ========== GET PRODUCTS BY PLANID =========== */
    // $scope.getProductByPlanID = function () {
    //     $('.spinPlanProduct').show();
    //     // console.clear();
    //     // console.log($scope.PLANS_model);
    //     $scope.STUDENT_LIST = [];
    //     $FINAL_PLANID = [];
    //     $FINAL_PLANID = $scope.PLANS_model.map(x=>x.id);
    //     // console.log($FINAL_PLANID);
    //     $http({
    //         method: 'post',
    //         url: url,
    //         data: $.param({ 'type': 'getProductsByPlan','PLANID':$FINAL_PLANID}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getPlanProduct = data.data.success ? data.data.data : [];
    //         $('.spinPlanProduct').hide();
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // $scope.getProductByPlanID();
    /* ========== GET PRODUCTS BY PLANID =========== */





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

    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtChannel").focus();
        $scope.temp={};
        $scope.PLANS_model=[];
        $scope.PRODUCTS_model=[];
        $scope.post.getPlanProduct=[];
        $scope.STUDENTS_model=[];
        $scope.post.getStudentByPlanProduct=[];
        $scope.STUDENT_LIST=[];
    }
    $scope.clearBYStudentType = function(){
        
        $scope.PLANS_model=[];
        $scope.PRODUCTS_model=[];
        $scope.post.getPlanProduct=[];
        $scope.STUDENTS_model=[];
        $scope.post.getStudentByPlanProduct=[];
        $scope.STUDENT_LIST=[];
    }
    /* ============ Clear Form =========== */ 


    $scope.getTSID = function(id){
        // console.log(id);
        $scope.temp.txtReasone='';
        $scope.GET_TRANSID = id.TRANSID;
        $scope.GET_TID = id.TID;
    }

    /* ========== DELETE =========== */
    $scope.deleteTransaction = function () {
        var r = confirm("Are you sure want to delete this transaction!");
        if (r == true) {
            $('.btnDelete').attr('disabled','disabled');
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TID': $scope.GET_TID, 'type': 'deleteTransaction','txtReasone':$scope.temp.txtReasone }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
                    $scope.getOnlineTransactions();
                    $('#DeleteTrans').modal('hide');
                    $('.btnDelete').removeAttr('disabled');
		            // console.log(data.data.message)
                    $scope.temp.txtReasone='';
                    $scope.GET_TID =0;
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    




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