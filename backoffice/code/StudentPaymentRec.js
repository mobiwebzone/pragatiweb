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
    $scope.PageSub = "ST_PAYMENT_Rec";
    $scope.temp.regid=0;
    $scope.temp.planid=0;
    $scope.temp.txtDate = new Date();
    $scope.temp.txtDate_Oth = new Date();
    $scope.instalment = 0;
    $scope.serial = 1;
    $scope.SHOW_SHEDULE = true;
    $scope.files = [];
    $scope.filesExcel = [];
    $scope.filesAttach = [];
    $scope.selectedStudents = [];

    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
    $scope.temp.txtFromDT=$scope.temp.txtFromDTEmail = new Date($scope.FromDT_S);
    $scope.temp.txtToDT=$scope.temp.txtToDTEmail = new Date();

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }

    
    
    $scope.focusDiv = function () {
        var scrollPos =  $("#selectCon").offset().top;
        $(window).scrollTop(scrollPos);
    }
    
    var url = 'code/StudentPaymentRec_code.php';



    /* ========== SELECT CONTACT =========== */
    $scope.selectStudents = function(item,val,AR){
        // console.log(index);
        // alert(val);
        // console.log(item);
        var idx = $scope.selectedStudents.findIndex(x => x.REGID === item.REGID);
        if(AR=='add'){
            if(idx<0){
                $scope.selectedStudents.push(item);
            }else{
                if(!val){
                    $scope.selectedStudents.splice(idx,1);
                    $('#chkSelect'+item.REGID).prop('checked', false);
                }
            }
        }else{
            $scope.selectedStudents.splice(idx,1);
            $('#chkSelect'+item.REGID).prop('checked', false);
        }

        $scope.selectAllST =  ($scope.selectedStudents.length == $scope.post.getStudent.length) ? true : false;
    }
    $scope.selectAllStudents = function(val){
        if(val){
            $scope.selectedStudents=[];
            $scope.selectedStudents = angular.copy($scope.post.getStudent);
            angular.forEach($scope.selectedStudents, function(value, key) {
                $('#chkSelect'+value.REGID).prop('checked', true);
            });
        }else{
            angular.forEach($scope.selectedStudents, function(value, key) {
                $('#chkSelect'+value.REGID).prop('checked', false);
            });
            $scope.selectedStudents=[];
        }
    }
    $scope.checkSelectedStudents=function(){
        $scope.temp.chkSelect={};
        $timeout(function(){
            angular.forEach($scope.selectedStudents, function(value, key) {
                $('#chkSelect'+value.REGID).prop('checked', true);
                // $scope.temp.chkSelect = [{key : fa}];
        },1000);
        });
        $scope.selectAllST =  ($scope.selectedStudents.length == $scope.post.getStudent.length) && $scope.selectedStudents.length>0 ? true : false;
    }
    /* ========== SELECT CONTACT =========== */



    /*========= For Excel File Name =========*/ 
    $scope.temp.txtUploadExcel ='';
    $scope.ExcelFileName = function (element) {
        $scope.temp.txtUploadExcel ='';

        if(element.files[0] != undefined){
            var reader = new FileReader();
            reader.onload = function (event) {
                $scope.$apply(function ($scope) {
                    $scope.filesExcel = element.files;
                });
            }
            reader.readAsDataURL(element.files[0]);

            $scope.temp.txtUploadExcel = element.files[0]['name'];
            $('.uploadBtn').removeAttr('disabled');
        }
        else{
            $scope.temp.txtUploadExcel = '';
            $('.uploadBtn').attr('disabled','disabled');
        }
        // console.info($scope.temp.txtUploadExcel);
    }
    /*========= For Excel File Name =========*/ 

    /*========= ATTACHMENT =========*/ 
    $scope.AttachmentFileName = function (element) {
        $scope.currentFile = element.files[0];
        // console.log(element.files[0]);
        if(element.files[0]['size'] > 26214400){
            alert('File size limit of 25MB.');
            angular.element('#txtAttachment').val(null);
        }else{
            var reader = new FileReader();
            reader.onload = function (event) {
                $scope.logo_src = event.target.result
                $scope.$apply(function ($scope) {
                    $scope.filesAttach = element.files;
                });
            }
            reader.readAsDataURL(element.files[0]);
        }
    }
    /*========= ATTACHMENT =========*/ 


    // ======= Open Print Receipt =========
    $scope.OpenPrintRec = function(id){
        window.open('Receipt/Receipt.html?REC='+id.RECID,"");
    }
    // ======= Open Print Receipt =========


    // GET DATA
    $scope.init = function () {
        // Check Session
        
    // alert($scope.REGID_FROM_REGISTRATION);
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
                    $scope.getPaymodes();
                    $scope.getLocations();
                    $scope.getMSGHistory();
                    $scope.getEMAILHistory();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
            }
            else {
                // window.location.assign('index.html#!/login');
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    /* ========== PAY =========== */
    $scope.Pay = function(){
        // alert($scope.temp.txtDate.toLocaleString('sv-SE'));
        if($scope.temp.recid > 0){
            $(".btn-pay").attr('disabled', 'disabled');
            $(".btn-pay").text('Updating..');
        }else{
            $(".btn-pay").attr('disabled', 'disabled');
            $(".btn-pay").text('Pay...');
        }

        $scope.Act_Inst = $scope.temp.ddlPayType == 'ONTIME' ? 1 : $scope.temp.Act_Inst;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'Pay');
                formData.append("recid", $scope.temp.recid);
                formData.append("txtDate", $scope.temp.txtDate.toLocaleString('sv-SE'));
                formData.append("regid", $scope.temp.regid);
                formData.append("planid", $scope.temp.planid);
                formData.append("instalment", $scope.temp.ddlInstallment);
                formData.append("txtAmt", $scope.temp.txtAmt);
                formData.append("ddlPaymode", $scope.temp.ddlPaymode);
                formData.append("txtRefno", $scope.temp.txtRefno);
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
                    $scope.temp.txtDate = new Date();
                    $scope.temp.ddlPaymode = '';
                    $scope.temp.txtAmt = '';
                    $scope.temp.txtRefno = '';
                    $scope.temp.ddlInstallment = '';
                    $scope.temp.txtRemark = '';

                }
                $scope.getReceipts()
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            if($scope.temp.recid > 0){
                $('.btn-pay').removeAttr('disabled');
                $(".btn-pay").text('UPDATE');
            }else{
                $('.btn-pay').removeAttr('disabled');
                $(".btn-pay").text('PAY');
            }
        });
    }



    // =========== SAVE EXCEL DATA ==============
    $scope.saveExcelFile = function(){
        $(".uploadBtn").attr('disabled', 'disabled');
        $(".uploadBtn").text('Uploading...');
        $scope.temp.txtUploadExcelData = $scope.filesExcel[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveExcelFile');
                // formData.append("regid", $scope.temp.regid);
                formData.append("txtUploadExcel", $scope.temp.txtUploadExcel);
                formData.append("txtUploadExcelData", $scope.temp.txtUploadExcelData);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // console.log($scope.temp.regid);
                $scope.getPaymentsShedule($scope.temp.regid);
                $scope.messageSuccess(data.data.message);                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            angular.element('#txtUploadExcel').val(null);
            $scope.temp.txtUploadExcel='';
            $scope.filesExcel=[];
            // $('.uploadBtn').removeAttr('disabled');
            $(".uploadBtn").text('Upload');
        });
    }
    // =========== SAVE EXCEL DATA ==============


    

    /* ========== PAY OTHER PAYMENT =========== */
    $scope.PayOther = function(){
        // alert($scope.temp.txtDate.toLocaleString('sv-SE'));
        if($scope.temp.recid > 0){
            $(".btn-pay").attr('disabled', 'disabled');
            $(".btn-pay").text('Updating..');
        }else{
            $(".btn-pay").attr('disabled', 'disabled');
            $(".btn-pay").text('Pay...');
        }

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'PayOther');
                formData.append("recid", $scope.temp.recid);
                formData.append("txtDate_Oth", $scope.temp.txtDate_Oth.toLocaleString('sv-SE'));
                formData.append("regid", $scope.temp.regid);
                formData.append("txtAmt_Oth", $scope.temp.txtAmt_Oth);
                formData.append("ddlPaymode_Oth", $scope.temp.ddlPaymode_Oth);
                formData.append("txtRefno_Oth", $scope.temp.txtRefno_Oth);
                formData.append("txtRemark_Oth", $scope.temp.txtRemark_Oth);
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
                    $scope.temp.txtDate_Oth = new Date();
                    $scope.temp.ddlPaymode_Oth = '';
                    $scope.temp.txtAmt_Oth = '';
                    $scope.temp.txtRefno_Oth = '';
                    $scope.temp.txtRemark_Oth = '';
                }
                $scope.getReceipts()
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            if($scope.temp.recid > 0){
                $('.btn-pay').removeAttr('disabled');
                $(".btn-pay").text('UPDATE');
            }else{
                $('.btn-pay').removeAttr('disabled');
                $(".btn-pay").text('PAY');
            }
        });
    }



    /* ========== GET STUDENT =========== */
    $scope.getStudent = function () {
        $scope.temp.psid=0;
        $scope.temp.planid=0;
        $scope.post.getStudent=[];
        $scope.post.getPaymentsShedule =[];
        $scope.post.getReceipts =[];
        $scope.temp.regid=0;
        $('.spinStList').show();
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

            $scope.REGID_FROM_REGISTRATION = new URLSearchParams(window.location.search).get('eixcfsf');
            if($scope.REGID_FROM_REGISTRATION && $scope.REGID_FROM_REGISTRATION>0){
                $scope.getPaymentsShedule($scope.REGID_FROM_REGISTRATION); 
                $scope.temp.planid=0; 
                $scope.temp.recid=0; 
                $scope.ClearPaymentSection()
            }

            $('.spinStList').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudent();
     


    /* ========== GET Payments =========== */
    $scope.getPaymentsShedule = function (REGID) {
        $('#pills-tab li:first-child a').tab('show');

        // $scope.post.getPaymentsShedule =[];
        $scope.post.getReceipts =[];
        $('.spinnermy').removeClass('d-none');
        $scope.temp.regid=REGID;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPaymentsShedule','regid':$scope.temp.regid, 
                            'ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if(data.data.success){
                $scope.post.getPaymentsShedule = data.data.data;
                $scope.getReceipts();
            }else{
                $scope.post.getPaymentsShedule =[];
            }
            window.location.hash = '#PLANS_TAB';
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        $('.spinnermy').addClass('d-none');
    }
    // $scope.getPaymentsShedule();
    
    
    
    /* ========== GET RECEIPTS =========== */
    $scope.getReceipts = function () {
        $('.spinnermy').removeClass('d-none');
        $scope.post.getReceipts =[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReceipts','regid':$scope.temp.regid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getReceipts = data.data.data;
            }else{
                $scope.post.getReceipts =[];
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        $('.spinnermy').addClass('d-none');
    }
    // $scope.getPaymentsShedule();
    


    /* ========== SELECT SHEDULE =========== */
    $scope.SelectShedule = function (id) {
        // $scope.temp.psid=id.PSID;
        $scope.temp.planid=id.PLANID;
        $scope.temp.PLANNAME = id.PLANNAME;

        if(id.PAYPLAN == 'ONE TIME'){
            $scope.instalment = 0;
        }else{
            $scope.instalment = id.INSTALLMENTS;
        }

        window.location.hash = '#ddlPayType';
        
        // $scope.setAmount(id.PAYPLAN,id.AMOUNT);
        $scope.temp.txtAmt = (id.PAYPLAN == 'ONE TIME') ? (Number(id.AMOUNT)) : ((id.PAYPLAN == 'INSTALMENT') ? (Number(id.AMOUNT)) : (''));

        
    }

    
    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        
        $('#pills-tab li:first-child a').tab('show');
        // document.getElementById("txtLocation").focus();

        $scope.temp.recid=id.RECID;
        if(id.PLANID > 0){
            $scope.SHOW_SHEDULE = true;
            $scope.temp.planid=id.PLANID;
            $scope.temp.txtDate=new Date(id.RECDATE);
            $scope.temp.ddlPaymode=(id.PMID).toString();
            $scope.temp.txtAmt=Number(id.AMOUNT);
            $scope.temp.txtRemark=id.REMARK;
            
            $scope.instalment = id.PAYPLAN == 'ONE TIME' ? 0 : id.TOTAL_INST;
            
            $timeout(function () {  
                $scope.temp.ddlInstallment= id.PAYPLAN == 'ONE TIME' ? '' : (id.INSTALLMENT).toString();
                $scope.temp.txtRefno=id.REFNO;
            },1000);
        }
        else{
            $('#pills-tab li:last-child a').tab('show');
            
            $scope.SHOW_SHEDULE = false;
            
            $scope.temp.txtDate_Oth = new Date(id.RECDATE);
            $scope.temp.ddlPaymode_Oth = (id.PMID).toString();
            $scope.temp.txtAmt_Oth = Number(id.AMOUNT);
            $scope.temp.txtRefno_Oth = id.REFNO;
            $scope.temp.txtRemark_Oth = id.REMARK;
        }


        $scope.editMode = true;
        $scope.index = $scope.post.getReceipts.indexOf(id);
    }

     
    

    /* ========== Clear Button =========== */
    $scope.Clear = function () {  
        $scope.temp={};

        $scope.post.getReceipts =[];
        $scope.post.getStudent=[];
        $scope.post.getPaymentsShedule =[];

        $scope.temp.regid=0;
        $scope.temp.planid=0;
        $scope.temp.psid=0;
        $scope.editMode = false;
        $scope.ClearPaymentSection();
        $scope.SHOW_SHEDULE = true;

        angular.element('#txtUploadExcel').val(null);
        $scope.temp.txtUploadExcel='';
        $scope.filesExcel=[];
    } 

    $scope.ClearPaymentSection = function () {
        $scope.temp.planid=0;
        $scope.temp.recid=0;
        $scope.temp.txtDate=new Date();
        $scope.temp.ddlPaymode='';
        $scope.temp.txtAmt='';
        $scope.temp.txtRefno='';
        $scope.temp.ddlInstallment='';
        $scope.temp.txtRemark='';
        $scope.instalment=0;

        $scope.temp.txtDate_Oth = new Date();
        $scope.temp.ddlPaymode_Oth = '';
        $scope.temp.txtAmt_Oth = '';
        $scope.temp.txtRefno_Oth = '';
        $scope.temp.txtRemark_Oth = '';
        $scope.SHOW_SHEDULE = true;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'RECID': id.RECID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data);
		        if (data.data.success) {
		            var index = $scope.post.getReceipts.indexOf(id);
		            $scope.post.getReceipts.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.ClearPaymentSection();
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }


    /* ========== GET Location =========== */
    // $scope.getLocations = function () {
    //     $http({
    //         method: 'post',
    //         url: 'code/Users_code.php',
    //         data: $.param({ 'type': 'getLocations'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getLocations = data.data.data;
    //         if($scope.post.getLocations.length>0){
    //             $timeout(()=>{
    //                 $scope.temp.ddlLocation = ($scope.post.getLocations.length==1) ? $scope.post.getLocations[0]['LOC_ID'].toString() : '';
    //                 $scope.getStudent();
    //             },700);
    //         }
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // $scope.getLocations(); --INIT

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
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */
    
    
    
    /* ========== GET Paymodes =========== */
    $scope.getPaymodes = function () {
        $http({
            method: 'post',
            url: 'code/Payment_Modes_code.php',
            data: $.param({ 'type': 'getPaymode'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPaymodes = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPaymodes(); --INIT





    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EMAIL / SMS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    /* ========== SMS/EMAIL =========== */
    $scope.saveData = function(SMS_EMAIL){
        $(".btn-saveSms,.btn-saveEmail").attr('disabled', 'disabled');
        if(SMS_EMAIL == 'SMS'){$(".btn-saveSms").text('Sending...')};
        if(SMS_EMAIL == 'EMAIL'){$(".btn-saveEmail").text('Sending...')};

        $TYPE = SMS_EMAIL==='SMS' ? 'saveDataSms' : 'saveDataEmail';
        if(SMS_EMAIL==='EMAIL'){$scope.temp.txtAttachment=$scope.filesAttach[0];}else{$scope.temp.txtAttachment='';};

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", $TYPE);
                formData.append("txtMessage", $scope.temp.txtMessage);
                formData.append("StudentData", JSON.stringify($scope.selectedStudents));
                formData.append("txtAttachment", $scope.temp.txtAttachment);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                if(SMS_EMAIL=='SMS'){
                    $scope.getMSGHistory();
                }else{
                    $scope.getEMAILHistory();
                }
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveSms,.btn-saveEmail').removeAttr('disabled');
            if(SMS_EMAIL == 'SMS'){$(".btn-saveSms").html('<i class="fa fa-comments font-15"></i> SEND SMS')};
            if(SMS_EMAIL == 'EMAIL'){$(".btn-saveEmail").html('<i class=" fa fa-envelope font-15"></i> SEND EMAIL')};
        });
    }
    /* ========== SMS/EMAIL =========== */




    /* ========== GET SMS =========== */
    $scope.getMSGHistory = function () {
        if(($scope.temp.txtFromDT && $scope.temp.txtFromDT!='') && ($scope.temp.txtToDT && $scope.temp.txtToDT!='')){
            $('#SpinMainData').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getMSGHistory',
                                'txtFromDT':$scope.temp.txtFromDT.toLocaleDateString(),
                                'txtToDT':$scope.temp.txtToDT.toLocaleDateString()
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getMSGHistory = data.data.success ? data.data.data : [];
                $('#SpinMainData').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getMSGHistory(); --INIT
    /* ========== GET SMS =========== */





    /* ========== GET EMAIL =========== */
    $scope.getEMAILHistory = function () {
        if(($scope.temp.txtFromDTEmail && $scope.temp.txtFromDTEmail!='') && ($scope.temp.txtToDTEmail && $scope.temp.txtToDTEmail!='')){
            $('#SpinMainDataEmail').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getEMAILHistory',
                                'txtFromDT':$scope.temp.txtFromDTEmail.toLocaleDateString(),
                                'txtToDT':$scope.temp.txtToDTEmail.toLocaleDateString()
                            }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getEMAILHistory = data.data.success ? data.data.data : [];
                $('#SpinMainDataEmail').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getEMAILHistory(); --INIT
    /* ========== GET EMAIL =========== */

    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EMAIL / SMS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




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