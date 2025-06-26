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
    $scope.files = [];
    $scope.editMode = false;
    $scope.Page = "SETTING";
    $scope.PageSub = "CHEQUES_ISSUED";
   
    
    
    var url = 'code/CHEQUES_ISSUED_RECEIVED.php';


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
                    // $scope.getBankID();
                    // $scope.getBankAccID();
                    // $scope.getChequeDetails();
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
        $scope.temp.txtSignature = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("chid", $scope.temp.chid);
                formData.append("txtChequetype", $scope.temp.txtChequetype);
                formData.append("ddlBankID", $scope.temp.ddlBankID);
                formData.append("ddlBankAccNO", $scope.temp.ddlBankAccNO);
                formData.append("TXTBankAccNO", $scope.temp.TXTBankAccNO);
                formData.append("ddlchequeNO", $scope.temp.ddlchequeNO);
                formData.append("txtChequeDate", (!$scope.temp.txtChequeDate || $scope.temp.txtChequeDate=='') ? '' : $scope.dateFormat($scope.temp.txtChequeDate));
                formData.append("txtChequeToFrom", $scope.temp.txtChequeToFrom);
                formData.append("txtAmmount", $scope.temp.txtAmmount);
                formData.append("txtChequeDateON",  (!$scope.temp.txtChequeDateON || $scope.temp.txtChequeDateON=='') ? '' : $scope.dateFormat($scope.temp.txtChequeDateON));
                formData.append("txtSignature", $scope.temp.txtSignature);
                formData.append("txtSignatureUPD", $scope.temp.txtSignatureUPD);
                
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.clear();
                $scope.getChequeDetails();
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

    //Image Preview
    $scope.UploadSignature = function (element) {
    $scope.currentFile = element.files[0];
    var reader = new FileReader();
    reader.onload = function (event) {
        $scope.reeiptFile_src = event.target.result
        $scope.$apply(function ($scope) {
            $scope.files = element.files;
        });
    }
    reader.readAsDataURL(element.files[0]);
    console.log(element.files[0])
    }

    $scope.setAccountNo=function(){
    $scope.temp.TXTBankAccNO=($scope.temp.ddlBankAccNO && $scope.temp.ddlBankAccNO!='') ? $('#ddlBankAccNO option:selected').text() : '';
    }

    /* ========== GET Checque Issue recived  Details =========== */
    $scope.getChequeDetails = function () {
        $scope.post.getChequeDetails = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $('#SpinMainData').show();
        $scope.temp.txtSignature = $scope.files[0];
        $http({
            method: 'post',
        url: url,
        data: $.param({ 'type': 'getChequeDetails','ddlLocation':$scope.temp.ddlLocation}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        //    console.log(data.data);
        $scope.post.getChequeDetails = data.data.success ? data.data.data : [];
        $('#SpinMainData').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getChequeDetails();





  /* ========== GET Bank Name By ID Details =========== */
    $scope.getBankID = function () {
        $('.SpinBank').show();
        $http({
            method: 'post',
        url: 'code/Mep_Bank_Account.php',
        data: $.param({ 'type': 'getBankID','ddlLocation':$scope.temp.ddlLocation}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        //    console.log(data.data);
        $scope.post.getBankID = data.data.success ? data.data.data : [];
        $('.SpinBank').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getBankID();



    /* ========== GET Bank Account ID =========== */
    $scope.getBankAccID = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
        url: url,
        data: $.param({ 'type': 'getBankAccID','bankid':$scope.temp.ddlBankID}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getBankAccID = data.data.success ? data.data.data : [];
        $('#SpinMainData').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getBankAccID();


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
            if($scope.temp.ddlLocation > 0) $scope.getBankID();
            if($scope.temp.ddlLocation > 0) $scope.getChequeDetails();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




        

     /* ============ Edit ITEM Stock Detail ============= */ 

        $scope.edit = function (id) {
        $scope.temp.chid=id.CHID;
        $scope.temp.txtChequetype=id.CHTYPE.toString();
        $scope.temp.ddlBankID=id.BANKID.toString();
        if($scope.temp.ddlBankID > 0)$scope.getBankAccID();
        $timeout(()=>{
            $scope.temp.ddlBankAccNO=id.BANKACCID>0?id.BANKACCID.toString():'';
        },500);
        $scope.temp.TXTBankAccNO=id.ACCOUNTNO;
        $scope.temp.ddlchequeNO=id.CHNO;
        $scope.temp.txtChequeDate=id.CHDATE== '-' ? '' : new Date(id.CHDATE);
        $scope.temp.txtChequeToFrom=id.CHTOFROM;
        $scope.temp.txtAmmount=Number(id.AMOUNT);
        $scope.temp.txtChequeDateON=id.ENCASHEDON== '-' ? '' : new Date(id.ENCASHEDON);
        $scope.temp.txtSignature=id.CHIMG;
        $scope.temp.txtSignatureUPD=id.CHIMG;
        $scope.reeiptFile_src=id.CHIMG==''?'cheqIssRec_images/default.png':'cheqIssRec_images/'+id.CHIMG ;
        $scope.temp.txtRemark=id.REMARKS;
        
        $scope.editMode1 = true;
        $scope.index = $scope.post.getChequeDetails.indexOf(id);
    }
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // document.getElementById("ddlPlan").focus();
        $scope.temp={};
        $scope.reeiptFile_src='';
        $scope.editMode = false;
        $scope.getLocations();
    }

 


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'chid': id.CHID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getChequeDetails.indexOf(id);
		            $scope.post.getChequeDetails.splice(index, 1);
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



});