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
    $scope.PageSub = "STOCK_INV";
    
    var url = 'code/Stock_Inventory.php';


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
                    // $scope.getPlans();
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

    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.txtBillDate);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("stockid", $scope.temp.stockid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtStype", $scope.temp.txtStype);
                formData.append("txtDesc", $scope.temp.txtDesc);
                formData.append("ddlVendorID", $scope.temp.ddlVendorID);
                formData.append("txtBillno", $scope.temp.txtBillno);
                formData.append("txtBillDate", $scope.dateFormat($scope.temp.txtBillDate));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getStockInventory();
               $scope.temp.stockid=data.data.STOCKID;
               $scope.post.getItemStockDetail = [];
              
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




    // SAVE DETAILS 2
    $scope.save1 = function(){
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
                formData.append("type", 'save1');
                formData.append("stockdetailid", $scope.temp.stockdeid);
                formData.append("stockid", $scope.temp.stockid);
                formData.append("itemid", $scope.temp.itemid);
                formData.append("txtUno", $scope.temp.txtUno);
                formData.append("txtRemarks", $scope.temp.txtRemarks);
                formData.append("txtQty", $scope.temp.txtQty);
                formData.append("txtFreeQty",$scope.temp.txtFreeQty);
                formData.append("txtRate", $scope.temp.txtRate);
                formData.append("txtMrp",$scope.temp.txtMrp);
                formData.append("ddlItemMasterStorage",$scope.temp.ddlItemMasterStorage);
                // formData.append("hideMeetingPass", $scope.temp.hideMeetingPass);
                // formData.append("hideEmailPass", $scope.temp.hideEmailPass);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        // console.log(data.data);
            if (data.data.success) {
                if(!$scope.editMode1){
                    $scope.temp.txtUno = '';
                    $scope.temp.txtRemarks = '';
                    $scope.temp.txtQty = '';
                    $scope.temp.txtFreeQty = '';
                    $scope.temp.txtRate = '';
                    $scope.temp.txtMrp = '';
                }
                $scope.getItemStockDetail();
                
                $scope.messageSuccess(data.data.message);
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


    /* ========== GET Item Stock STMID  Details =========== */
    $scope.getItemStorageMaster = function () {
        $scope.post.getItemStorageMaster=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.SpinItemStorage').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'getItemStorageMaster','ddlLocation':$scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getItemStorageMaster = data.data.success ? data.data.data : [];
           $('.SpinItemStorage').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
    }
    // $scope.getItemStorageMaster();


    /* ========== GET STOCK DETAILS MAIN DATA =========== */
    $scope.getStockInventory = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getStockInventory','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStockInventory = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStockInventory();
    /* ========== GET STOCK DETAILS MAIN DATA =========== */


    /* ========== GET Item Stock Details =========== */
    $scope.getItemStockDetail = function () {
        $scope.post.getItemStockDetail = [];
        $('#SpinMainDTData').show();
        $http({
            method: 'post',
        url: url,
        data: $.param({ 'type': 'getItemStockDetail','stockid':$scope.temp.stockid}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getItemStockDetail = data.data.success ? data.data.data : [];
        $('#SpinMainDTData').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    //$scope.getItemStockDetail();
    /* ========== GET Item Stock Details =========== */



    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.post.getItemStockDetail = [];
        $scope.clearDetails();

        $scope.temp.stockid=id.STOCKID;
        // $scope.temp.ddlLocation=id.LOCID.toString();
        $scope.temp.txtStype= id.STOCKTYPE;
        $scope.temp.txtDesc= id.STOCKDESC;
        $scope.temp.ddlVendorID= (id.VENDORID).toString();
        $scope.temp.txtBillno= id.BILLNO;
        $scope.temp.txtBillDate=id.BILLDATE == '-' ? '' : new Date(id.BILLDATE_ED);

        $scope.getItemStockDetail();

        $scope.editMode = true;
        $scope.index = $scope.post.getStockInventory.indexOf(id);
    }
    
    $scope.edit1 = function (id) {
            $scope.temp.stockdeid=id.STOCKDETID;
        $scope.temp.stockid=id.STOCKID;
        $scope.temp.itemid=id.ITEMID.toString();
        $scope.temp.txtUno=id.UNIQUENO;
        $scope.temp.txtRemarks=id.REMARKS;
        $scope.temp.txtQty=Number(id.QTY);
        $scope.temp.txtFreeQty=Number(id.FREE_QTY);
        $scope.temp.txtRate=Number(id.RATE);
        $scope.temp.txtMrp=Number(id.MRP);
        $scope.temp.ddlItemMasterStorage=(id.STMID).toString();
        
        $scope.editMode1 = true;
        $scope.index = $scope.post.getItemStockDetail.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // $scope.temp={};
        $scope.temp.stockid='';
        $scope.temp.txtStype= '';
        $scope.temp.txtDesc= '';
        $scope.temp.ddlVendorID='';
        $scope.temp.txtBillno= '';
        $scope.temp.txtBillDate='';
        $scope.editMode = false;
        $scope.clearDetails();
        $scope.getLocations();
    }

    $scope.clearDetails = function(){
        $scope.temp.stockdeid='';
        $scope.temp.itemid='';
        $scope.temp.ddlItemMasterStorage='';
        $scope.temp.txtUno='';
        $scope.temp.txtRemarks='';
        $scope.temp.txtQty='';
        $scope.temp.txtFreeQty='';
        $scope.temp.txtRate='';
        $scope.temp.txtMrp='';
        $scope.editMode1 = false;
    }
    /* ============ Clear Form =========== */ 




     
    /* ========== GET STOCK ID  =========== */
    $scope.getStockID = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'getStockID'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getStockID = data.data.success ? data.data.data : [];
           $('#SpinMainData').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
    }
    $scope.getStockID();
    /* ========== GET STOCK ID  =========== */


    /* ========== SetStorage ID  =========== */
    $scope.SetStorage = function () {
        $scope.STMID = $scope.post.getItemID.filter(x=>x.ITEMID==$scope.temp.itemid).map(x=>x.STMID).toString();
        $scope.temp.ddlItemMasterStorage = $scope.STMID > 0 ? $scope.STMID : '';
    }
    /* ========== SetStorage ID  =========== */


      
    /* ========== GET ITEM ID  =========== */
    $scope.getItemID = function () {
        $scope.post.getItemID=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.SpinItem').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'getItemID','ddlLocation':$scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getItemID = data.data.success ? data.data.data : [];
           $('.SpinItem').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
    }
    // $scope.getItemID();
    /* ========== GET ITEM ID  =========== */



    /* ========== GET VENDOR =========== */
    $scope.getVendorID = function () {
        $scope.post.getVendorID=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('.SpinVendor').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getVendorID','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getVendorID = data.data.success ? data.data.data : [];
            $('.SpinVendor').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getVendorID();
    /* ========== GET VENDOR =========== */



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
            if($scope.temp.ddlLocation > 0) $scope.getStockInventory();
            if($scope.temp.ddlLocation > 0) $scope.getVendorID();
            if($scope.temp.ddlLocation > 0) $scope.getItemID();
            if($scope.temp.ddlLocation > 0) $scope.getItemStorageMaster();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    $scope.clearSecondForm = function () { 
        $scope.temp.stockid='';
        $scope.temp.txtStype= '';
        $scope.temp.txtDesc= '';
        $scope.temp.ddlVendorID='';
        $scope.temp.txtBillno= '';
        $scope.temp.txtBillDate='';
        $scope.editMode = false;
        $scope.clearDetails();
        $scope.post.getItemStockDetail = [];
    }




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'stockid': id.STOCKID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStockInventory.indexOf(id);
		            $scope.post.getStockInventory.splice(index, 1);
		            console.log(data.data.message)

                    $scope.temp.stockid='';
                    $scope.clearDetails();
                    
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
                data: $.param({ 'stockdeid': id.STOCKDETID, 'type': 'delete1' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                    // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getItemStockDetail.indexOf(id);
                    $scope.post.getItemStockDetail.splice(index, 1);
                    console.log(data.data.message)
                    
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