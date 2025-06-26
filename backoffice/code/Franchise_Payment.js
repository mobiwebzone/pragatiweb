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
    $scope.Page = "LA";
    $scope.PageSub = "FRANCHISEMANAGEMENT";
    $scope.PageSub2 = "FRANCHISEPAYMENT";
    $scope.temp.txtPaidAmtDate = new Date();
    $scope.files = [];
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
    }
    
    var url = 'code/Franchise_Payment.php';

    $scope.setNetPayableAmt = function(BasisAmt,FractionAmt){
        $scope.temp.txtNetPayAsh_HQ = (!BasisAmt || !FractionAmt) ? 0 : Number((BasisAmt*FractionAmt).toFixed(2));
    }

    /*========= Image Preview =========*/ 
    $scope.FILE_EXTENTION = '';
    $scope.UploadImage = function (element) {
        
        if (!element || !element.files[0] || element.files[0].length === 0){return;}
        
        const fileType = element.files[0]['type'].split('/')[0];
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {

            if(fileType == 'image'){$scope.logo_src = event.target.result}
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);



        /////////////////////
        // GET FILE EXTENTION
        /////////////////////
        const name = element.files[0].name;
        const lastDot = name.lastIndexOf('.');
        // const fileName = name.substring(0, lastDot);
        const ext = name.substring(lastDot + 1);

        $scope.FILE_EXTENTION = ext;
        // console.log(fileType+'/'+$scope.FILE_EXTENTION);
        if(fileType != 'image') $scope.FileTypeImage(fileType,ext);
    }
    /*========= Image Preview =========*/ 
    
    $scope.FileTypeImage = function (FType,EXT) {
        alert('')
        if(['xlsx','xlsm','xlsb','xltx','xltm','xls','xlt','xls','csv','xml','xlam','xla','xlw','xlr'].includes(EXT)){
            $scope.logo_src = '../images/FileEx/xls.png';
        } 
        else if(['pdf'].includes(EXT)){$scope.logo_src = '../images/FileEx/pdf.png';} 
        else if(['doc','docx'].includes(EXT)){$scope.logo_src = '../images/FileEx/doc.png';} 
        else if(['pptx','pptm','ppt'].includes(EXT)){$scope.logo_src = '../images/FileEx/ppt.png';} 
        else if(['txt'].includes(EXT)){$scope.logo_src = '../images/FileEx/txt.png';}
        else{$scope.logo_src = '../images/FileEx/document.png';}
    }



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
            console.log(data.data);
            if (data.data.success) {
                $scope.post.user = data.data.data;
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;
                // window.location.assign("dashboard.html");

                // $scope.getChannels();
                $scope.getLocations();
                $scope.getCategories();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CHANNEL SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        // console.log($scope.logo_src,$scope.logo_src.length, $scope.editMode)
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        $scope.temp.DocsUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("fpid", $scope.temp.fpid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlCategory", $scope.temp.ddlCategory);
                formData.append("ddlSubCategory", $scope.temp.ddlSubCategory);
                formData.append("txtPB_ID", $scope.temp.txtPB_ID);
                formData.append("txtPBP_Ref", $scope.temp.txtPBP_Ref);
                formData.append("txtPB_Desc", $scope.temp.txtPB_Desc);
                formData.append("txtPB_Period", $scope.temp.txtPB_Period);
                formData.append("txtPB_Amount", $scope.temp.txtPB_Amount);
                formData.append("txtPB_Fraction", $scope.temp.txtPB_Fraction);
                formData.append("txtNetPayAsh_HQ", $scope.temp.txtNetPayAsh_HQ);
                formData.append("txtPaidAmt", $scope.temp.txtPaidAmt);
                formData.append("txtPaidAmtDate", (!$scope.temp.txtPaidAmtDate || $scope.temp.txtPaidAmtDate=='') ? '' : $scope.temp.txtPaidAmtDate.toLocaleDateString('sv-SE'));
                formData.append("txtPaidMethod", $scope.temp.txtPaidMethod);
                formData.append("txtRemarks", $scope.temp.txtRemarks);
                formData.append("chkSettled", $scope.temp.chkSettled);
                formData.append("DocsUpload", $scope.temp.DocsUpload);
                formData.append("existingDocsUpload", $scope.temp.existingDocsUpload);
                formData.append("chkRemoveImgOnUpdate", ((!$scope.logo_src || $scope.logo_src.length<=0) && $scope.editMode)?1:0);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getFranchisePayment('M');
                $scope.messageSuccess(data.data.message);
                $scope.clearForm();
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
    // =========== SAVE DATA ==============






    /* ========== GET FRANCHISE PAYMENT =========== */
    $scope.getFranchisePayment = function (FOR) {
        if(FOR=='M'){
            $scope.ddlLocationSearch=$scope.temp.ddlLocation;
        }
        $scope.post.getFranchisePayment = [];
        if(!$scope.ddlLocationSearch || $scope.ddlLocationSearch<=0) return;
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getFranchisePayment',
                            'ddlLocation':$scope.ddlLocationSearch,
                            'chkSettledSearch':$scope.temp.chkSettledSearch
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getFranchisePayment = data.data.success?data.data.data:[];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getFranchisePayment(); --INIT
    /* ========== GET FRANCHISE PAYMENT =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        if($scope.USER_LOCATION.indexOf('HQ')>=0){
            $("#ddlLocation").focus();
            $scope.temp={
                fpid : id.FPID,
                ddlLocation : id.LOC_ID.toString(),
                ddlCategory : (!id.LMCID || id.LMCID<=0) ? '' : id.LMCID.toString(),
                txtPB_ID : id.PID,
                txtPBP_Ref : id.PREF,
                txtPB_Desc : id.PDESC,
                txtPB_Period : id.PERIOD,
                txtPB_Amount : Number(id.AMOUNT),
                txtPB_Fraction : Number(id.FRACTION),
                txtNetPayAsh_HQ : Number(id.NETPAYABLE),
                txtPaidAmt : Number(id.AMOUNTPAID),
                txtPaidAmtDate : id.PAIDDATE !== '' ? new Date(id.PAIDDATE) : '',
                txtPaidMethod : id.PAYMODE,
                txtRemarks : id.REMARKS,
                chkSettled : id.SETTLED>0?'1':'0',
                existingDocsUpload : id.DOC_FILE,
            }

            if($scope.temp.ddlCategory > 0){
                $scope.getSubCategories();
                $scope.$watch('post.getSubCategories', function () {
                    $scope.temp.ddlSubCategory = id.LSCID.toString();
                }, true);
            }

            /*########### IMG #############*/
            if(id.DOC_FILE != ''){
    
                const name_edit = id.DOC_FILE;
                const lastDot_edit = name_edit.lastIndexOf('.');
                const ext_edit = name_edit.substring(lastDot_edit + 1);
    
                // alert(name_edit+'....'+ext_edit);
    
                if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
                    $scope.logo_src='images/franchise_payment/'+id.DOC_FILE;
                }else{
                    $scope.FileTypeImage('',ext_edit);
                }
            }else{
                $scope.logo_src='';
            }
            
            $scope.editMode = true;
            $scope.index = $scope.post.getFranchisePayment.indexOf(id);
        }
    }
    /* ============ Edit Button ============= */ 





    /* ============ Copy Button ============= */ 
    $scope.copyForm = function (id) {
        $scope.temp={};
        $scope.logo_src = '';
        $scope.files = [];
        angular.element('#DocsUpload').val(null);
        $scope.editMode = false;
        if($scope.USER_LOCATION.indexOf('HQ')>=0){
            $("#ddlLocation").focus();
            $scope.temp={
                // fpid : id.FPID,
                ddlLocation : id.LOC_ID.toString(),
                ddlCategory : (!id.LMCID || id.LMCID<=0) ? '' :id.LMCID.toString(),
                txtPB_ID : id.PID,
                txtPBP_Ref : id.PREF,
                txtPB_Desc : id.PDESC,
                txtPB_Period : id.PERIOD,
                txtPB_Amount : Number(id.AMOUNT),
                txtPB_Fraction : Number(id.FRACTION),
                txtNetPayAsh_HQ : Number(id.NETPAYABLE),
                txtPaidAmt : Number(id.AMOUNTPAID),
                txtPaidAmtDate : id.PAIDDATE !== '' ? new Date(id.PAIDDATE) : '',
                txtPaidMethod : id.PAYMODE,
                txtRemarks : id.REMARKS,
                chkSettled : id.SETTLED>0?'1':'0',
                existingDocsUpload : id.DOC_FILE,
            }

            if($scope.temp.ddlCategory > 0){
                $scope.getSubCategories();
                $scope.$watch('post.getSubCategories', function () {
                    $scope.temp.ddlSubCategory = id.LSCID.toString();
                }, true);
            }
            /*########### IMG #############*/
            if(id.DOC_FILE != ''){
    
                const name_edit = id.DOC_FILE;
                const lastDot_edit = name_edit.lastIndexOf('.');
                const ext_edit = name_edit.substring(lastDot_edit + 1);
    
                // alert(name_edit+'....'+ext_edit);
    
                if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
                    $scope.logo_src='images/franchise_payment/'+id.DOC_FILE;
                }else{
                    $scope.FileTypeImage('',ext_edit);
                }
            }else{
                $scope.logo_src='';
            }
            
            // $scope.editMode = true;
            // $scope.index = $scope.post.getFranchisePayment.indexOf(id);
        }
    }
    /* ============ Copy Button ============= */ 
    
    
    $scope.clearLogo_src = function(){
        $scope.logo_src = '';
    }

    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
        $scope.logo_src = '';
        $scope.files = [];
        angular.element('#DocsUpload').val(null);
        $scope.editMode = false;
        $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'FPID': id.FPID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getFranchisePayment.indexOf(id);
		            $scope.post.getFranchisePayment.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearForm();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    



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
            $scope.ddlLocationSearch = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getFranchisePayment();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */



    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function () {
        $scope.post.getSubCategories=[];
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: 'code/Franchise_Categories.php',
            data: $.param({ 'type': 'getCategories'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.success?data.data.data:[];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories(); --INIT
    /* ========== GET CATEGORIES =========== */


    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function () {
        $('#spinSubHead').show();
        $http({
            method: 'post',
            url: 'code/Franchise_Categories.php',
            data: $.param({ 'type': 'getSubCategories', 'lmcid' : $scope.temp.ddlCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubCategories = data.data.success?data.data.data:[];
            $('#spinSubHead').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */




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