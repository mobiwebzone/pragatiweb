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
    $scope.Page = "INVENTORY";
    $scope.PageSub = "STOCK_RETURN";

    $scope.temp.txtReturnDate = new Date();
    $scope.txtReturnQty = [];
    $scope.RETURNID=0;

    
    var url = 'code/Stock_Item_Return.php';

    
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                   
                    $scope.getLocations();
                }
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
    



    /* ========== Check Return Qty =========== */
    $scope.setReturnQty=(index)=>{
        $scope.post.getTransactionsByTransForID[index]['RETURN_QTY']=Number($scope.txtReturnQty[index]);
    }
    /* ========== Check Return Qty =========== */




    /* ========== Save Return =========== */
    $scope.ReturnItemSave = function(id){
        // console.log(id);
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.txtBillDate);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'ReturnItemSave');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtReturnDate", (!$scope.temp.txtReturnDate || $scope.temp.txtReturnDate=='')?'':$scope.temp.txtReturnDate.toLocaleString('sv-SE'));
                formData.append("RETURN_ITEM", JSON.stringify(id));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.temp.transid=data.data.TRANSID;
                $scope.getTransactionsByTransForID();
                $scope.getReturnData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ========== Save Return =========== */



    
    /* ========== GET RETURN DATA  =========== */
    $scope.getReturnData = function () {
        $('#SpinReturnData').show();
        $http({
            method: 'post',
        url: url,
        data: $.param({ 'type': 'getReturnData','ddlLocation':$scope.temp.ddlLocation}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getReturnData = data.data.success ? data.data.data : [];
        $('#SpinReturnData').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    /* ========== GET RETURN DATA  =========== */

    

    /* ========== GET TRANSACTION FOR NAME =========== */
    $scope.getTransForName = function () {
        $scope.post.getTransForName =[];
        if(!$scope.temp.txtTRANSTYPE || !$scope.temp.txtTransactionFor || $scope.temp.txtTransactionFor == 'OFFICE' || !$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinTransFor').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTransForName',
                            'txtTRANSTYPE':$scope.temp.txtTRANSTYPE,
                            'txtTransactionFor':$scope.temp.txtTransactionFor,
                            'ddlLocation':$scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTransForName = data.data.success ? data.data.data : [];
            $('.spinTransFor').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTransForName();--INIT
    /* ========== GET TRANSACTION FOR NAME =========== */




    /* ========== GET TRANSACTIONS BY TRANSFORID =========== */
    $scope.getTransactionsByTransForID = function () {
        $scope.post.getTransactionsByTransForID =[];
        if($scope.temp.txtTransactionFor != 'OFFICE' && (!$scope.temp.ddlForTransName || $scope.temp.ddlForTransName<=0)) return;
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTransactionsByTransForID',
                            'txtTRANSTYPE':$scope.temp.txtTRANSTYPE,
                            'txtTransactionFor':$scope.temp.txtTransactionFor,
                            'ddlForTransName':$scope.temp.ddlForTransName,
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTransactionsByTransForID = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTransactionsByTransForID();--INIT
    /* ========== GET TRANSACTIONS BY TRANSFORID =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getReturnData();
            if($scope.temp.ddlLocation > 0) $scope.getTransForName();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    


    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // $scope.temp={};
        $scope.temp.txtReturnDate=new Date();
        $scope.temp.txtTRANSTYPE='';
        $scope.temp.txtTransactionFor='';
        $scope.temp.ddlForTransName='';
        $scope.temp.txtSerarch='';
        $scope.temp.cancelReasone='';
        $scope.RETURNID=0;
        $scope.post.getTransactionsByTransForID =[];
        $scope.post.getTransForName =[];
        $('#txtReturnDate').focus();
        $scope.temp.txtReturnDate = new Date();
    }

    /* ========== DELETE =========== */
    $scope.delete = function (RETURNID) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'RETURNID': RETURNID,
                                'CANCEL_REASONE':$scope.temp.cancelReasone,
                                'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            // var index = $scope.post.getReturnData.indexOf(id);
		            // $scope.post.getReturnData.splice(index, 1);
		            console.log(data.data.message)
                    $scope.getReturnData();
                    $('#txtReturnDate').focus();
                    $scope.clear();
                    // $scope.RETURNID=0;
                    // $scope.temp.cancelReasone = '';
                    $timeout(()=>{$('#cancelModal').modal('hide')},500);
                    
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


    $scope.eyepass = function(For,index) {
        if(For == 'MPASS'){
            var input = $("#txtMeetingPasscode");
        }else if(For == 'EPASS'){
            var input = $("#txtEmailPassword");
        }
        
        // var input = $("#txtMeetingPasscode");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon'+index).removeClass('fa-eye');
            $('.Eyeicon'+index).addClass('fa-eye-slash');
        }else{
            $('.Eyeicon'+index).removeClass('fa-eye-slash');
            $('.Eyeicon'+index).addClass('fa-eye');
        }
    };

});