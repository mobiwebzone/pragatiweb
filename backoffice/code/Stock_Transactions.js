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
    $scope.Page = "INVENTORY";
    $scope.PageSub = "STOCK_TRANS";

    $scope.temp.txtRate = 0;
    $scope.temp.txtDiscount = 0;
    $scope.temp.txtAmmount = 0;
    $scope.temp.txtFreeQty = 0;
    $scope.temp.txtNetAmmount = 0;

    
    var url = 'code/Stock_Transactions.php';


    $scope.dateFormat = function (datetime) {
        if (datetime != undefined) {
            return datetime.getFullYear() + '-' + ("0" + (datetime.getMonth() + 1)).slice(-2) + '-' + datetime.getDate();
        }
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
    



    /* ========== Save Stock Transaction =========== */
    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        $scope.clearDetails();
        // alert($scope.temp.txtBillDate);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("transid", $scope.temp.transid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtBillDate", $scope.dateFormat($scope.temp.txtBillDate));
                formData.append("txtTRANSTYPE", $scope.temp.txtTRANSTYPE);
                formData.append("txtTransactionFor", $scope.temp.txtTransactionFor);
                formData.append("tranforid", $scope.temp.tranforid);
                formData.append("txtRemark", $scope.temp.txtRemark);
                
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
                $scope.getStockItemTransactions();
                $scope.getStockItemTransactionDetails();
                // document.getElementById("ddlPlan").focus();
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
    /* ========== Save Stock Transaction =========== */




    /* ========== Save Stock Item Transaction Details =========== */
    $scope.saveStockDetails = function(){
        $(".btn-saveDet").attr('disabled', 'disabled');
        $(".btn-saveDet").text('Saving...');
        $(".btn-updateDet").attr('disabled', 'disabled');
        $(".btn-updateDet").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveStockDetails');
                formData.append("transdetid", $scope.temp.transdetid);
                formData.append("transid", $scope.temp.transid);
                formData.append("STOCKDETID", $scope.STOCKDETID);
                formData.append("itemID", $scope.temp.itemID);
                formData.append("txtRate", $scope.temp.txtRate);
                formData.append("txtDiscount", $scope.temp.txtDiscount);
                formData.append("txtAmmount", $scope.temp.txtAmmount);
                formData.append("txtFreeQty",$scope.temp.txtFreeQty);
                formData.append("txtNetAmmount", $scope.temp.txtNetAmmount);
                formData.append("txtRemarks",$scope.temp.txtRemarks);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);

                $scope.getStockItemTransactionDetails();
                $scope.clearDetails();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveDet').removeAttr('disabled');
            $(".btn-saveDet").text('SAVE');
            $('.btn-updateDet').removeAttr('disabled');
            $(".btn-updateDet").text('UPDATE');
        });
    }
    /* ========== Save Stock Item Transaction Details =========== */



    /* ========== GET SSTOCK_ITEM_TRANSACTIONS  =========== */
    $scope.getStockItemTransactions = function () {
        // $scope.post.getStockItemTransactionDetails=[];
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStockItemTransactions','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStockItemTransactions = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStockItemTransactions();--INIT
    /* ========== GET SSTOCK_ITEM_TRANSACTIONS  =========== */


    
    /* ========== GET STOCK_ITEM_TRANSACTIONS_DETAILS  =========== */
    $scope.getStockItemTransactionDetails = function () {
        $scope.post.getStockItemTransactionDetails=[];
        $('#SpinDetail').show();
        $http({
            method: 'post',
        url: url,
        data: $.param({ 'type': 'getStockItemTransactionDetails','transid':$scope.temp.transid}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getStockItemTransactionDetails = data.data.success ? data.data.data : [];
        $('#SpinDetail').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    /* ========== GET STOCK_ITEM_TRANSACTIONS_DETAILS  =========== */



    /* ============ Edit ITEM Stock Detail ============= */ 
    $scope.edit = function (id) {
        $scope.temp.transid=id.TRANSID;
        $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.txtBillDate=id.TRANSDATE== '-' ? '' : new Date(id.TRANSDATE_ED);
        $scope.temp.txtTRANSTYPE=id.TRANSTYPE;
        $scope.temp.txtTransactionFor=id.TRANSFOR;
        $scope.temp.tranforid=id.TRANSFORID.toString();
        $scope.temp.txtRemark=id.REMARKS;

        $scope.editMode1 = true;
        $scope.index = $scope.post.getStockItemTransactions.indexOf(id);
        $scope.getStockItemTransactionDetails();
    }

    
    /* ============ Edit STOCK_ITEM_TRANSACTIONS_DETAILS ============= */ 
    $scope.edit1 = function (id) {
        $scope.temp.transdetid=id.TRANSDETID;
        $scope.temp.Item_Categories=id.ICATID>0?id.ICATID:'';
        $scope.getItemIDName();
        $timeout(function(){
            $scope.temp.itemID= id.ITEMID>0 ? (id.ITEMID).toString():'';
            $scope.STOCKDETID = $scope.post.getItem.filter(x=>x.ITEMID==$scope.temp.itemID).map(x=>x.STOCKDETID).toString();
            // console.log('EDIT : ' + $scope.STOCKDETID);
            // $scope.GetCpUnit();
        },1000);
        
        $scope.temp.txtRate=Number(id.RATE);
        $scope.temp.txtDiscount=Number(id.DISCOUNT);
        $scope.temp.txtAmmount=Number(id.AMOUNT);
        $scope.temp.txtFreeQty=Number(id.QTY);
        $scope.temp.txtNetAmmount=Number(id.NETAMOUNT);
        $scope.temp.txtRemarks=id.REMARKS;
        
        $scope.editMode1 = true;
        $scope.index = $scope.post.getStockItemTransactionDetails.indexOf(id);
    }
    

    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // $scope.temp={};
        $scope.temp.transid='';
        // $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.txtBillDate='';
        $scope.temp.txtTRANSTYPE='';
        $scope.temp.txtTransactionFor='';
        $scope.temp.tranforid='';
        $scope.temp.txtRemark='';
        $scope.editMode = false;
        $scope.clearDetails();
        $scope.post.getStockItemTransactionDetails=[];
        $scope.getLocations();
    }

    $scope.clearDetails = function(){
        $scope.temp.transdetid='';
        $scope.temp.Item_Categories='';
        $scope.temp.itemID='';
        $scope.temp.txtRate=0;
        $scope.temp.txtDiscount=0;
        $scope.temp.txtAmmount=0;
        $scope.temp.txtFreeQty=0;
        $scope.temp.txtNetAmmount=0;
        $scope.temp.txtRemarks='';
        $scope.editMode1 = false;
    }

    $scope.clearSecondForm = function () {
        $scope.temp.transid='';
        // $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.txtBillDate='';
        $scope.temp.txtTRANSTYPE='';
        $scope.temp.txtTransactionFor='';
        $scope.temp.tranforid='';
        $scope.temp.txtRemark='';
        $scope.editMode = false;
        $scope.clearDetails();
        $scope.post.getStockItemTransactionDetails=[];
    }
    /* ============ Clear Form =========== */ 

     


    /* ========== GET Item Rate Cp/Unit ID  =========== */
    $scope.STOCKDETID = 0;
    $scope.GetCpUnit = function () {
        if(!$scope.temp.itemID || $scope.temp.itemID<=0) return;
        $scope.STOCKDETID = $scope.post.getItem.filter(x=>x.ITEMID==$scope.temp.itemID).map(x=>x.STOCKDETID).toString();
        $('.spinRate').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'GetCpUnit','TRANSTYPE':$scope.temp.txtTRANSTYPE, 'STOCKDETID':$scope.STOCKDETID}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.temp.txtRate = data.data.success ? Number(data.data.data['RATE']) : 0;
            $scope.temp.txtAmmount = $scope.temp.txtRate - $scope.temp.txtDiscount;
            $scope.temp.txtNetAmmount = $scope.temp.txtAmmount * $scope.temp.txtFreeQty;
            $('.spinRate').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.GetCpUnit();



    /* ========== SetStorage ID  =========== */
    $scope.SetStorage = function () {
        $scope.STMID = $scope.post.gettxtTRANSTYPE.filter(x=>x.ITEMID==$scope.temp.itemid).map(x=>x.STMID).toString();
        $scope.temp.ddlItemMasterStorage = $scope.STMID > 0 ? $scope.STMID : '';
    }





    
    /* ========== GET Item ID  =========== */
    $scope.getItemIDName = function () {
    $('.spinItem').show();
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getItemIDName','Item_Categories':$scope.temp.Item_Categories }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded'}
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getItem = data.data.success ? data.data.data : [];
        $('.spinItem').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
        $('.spinItem').hide();
       })
    }    
    // $scope.getItemIDName();



    
    /* ========== GET Item Category  =========== */
    $scope.getItem_Categories = function () {
        $scope.post.getItem_Categories = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinItemCat').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getItem_Categories','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded'}
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getItem_Categories = data.data.success ? data.data.data : [];
            $('.spinItemCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getItem_Categories();





   /* ========== GET STAFF NaME Id  Details =========== */
    $scope.getStaffID = function () {
        $scope.post.getStaffID=[];
        if(!$scope.temp.ddlLocation  || $scope.temp.ddlLocation <=0)return;
        $http({
            method: 'post',
        url: url,
        data: $.param({ 'type': 'getStaffID', 'ddlLocation':$scope.temp.ddlLocation}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStaffID = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStaffID();
    /* ========== GET STAFF NaME Id  Details =========== */



    /* ========== GET Student Name Id  Details =========== */
    $scope.getStudentID = function () {
        $scope.post.getStudentID=[];
        if(!$scope.temp.ddlLocation  || $scope.temp.ddlLocation <=0)return;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentID', 'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentID = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentID();
    /* ========== GET Student Name Id  Details =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getStockItemTransactions();
            if($scope.temp.ddlLocation > 0) $scope.getItem_Categories();
            if($scope.temp.ddlLocation > 0) $scope.getStaffID();
            if($scope.temp.ddlLocation > 0) $scope.getStudentID();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'transid': id.TRANSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStockItemTransactions.indexOf(id);
		            $scope.post.getStockItemTransactions.splice(index, 1);
		            console.log(data.data.message)

                    $scope.temp.stockid='';
                    $scope.clear();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    

    /* ========== DELETE  ITEM STOCK DETAILS=========== */
    $scope.delete1 = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'transdetid': id.TRANSDETID, 'type': 'delete1' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                    // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getStockItemTransactionDetails.indexOf(id);
                    $scope.post.getStockItemTransactionDetails.splice(index, 1);
                    console.log(data.data.message)
                    $scope.clearDetails();
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }



    /* ========== PRINT STOCK TRANSACTIONS =========== */
    $scope.printStockTransaction = function (id) {
        // console.log(id);
        window.open('Receipt/StockTransactionReceipt.html?TRANS='+id.TRANSID,"");
    }
    /* ========== PRINT STOCK TRANSACTIONS =========== */
    


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