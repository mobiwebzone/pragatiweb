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
    $scope.Page = "MISC";
    $scope.PageSub = "CONTACTUS";
    $scope.editMode = false;
    $scope.selectedContacts = [];
    $scope.serial = 1;
    $scope.FormName = 'Show Entry Form';
    $scope.files = [];

    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
    $scope.temp.txtFromDT=$scope.temp.txtFromDTEmail = new Date($scope.FromDT_S);
    $scope.temp.txtToDT=$scope.temp.txtToDTEmail = new Date();

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/ContactUs_BackOffice_code.php';

    $scope.focusDiv = function () {
        var scrollPos =  $("#selectCon").offset().top;
        $(window).scrollTop(scrollPos);
    }



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
                    $scope.files = element.files;
                });
            }
            reader.readAsDataURL(element.files[0]);
        }
    }
    /*========= ATTACHMENT =========*/ 



    /*========== Form Show Hide ==========*/ 
    $scope.FormShowHide=function (){
        $scope.FormName ='';
        var isMobileVersion = document.getElementsByClassName('collapsed');
        if (isMobileVersion.length > 0) {
            $scope.FormName = 'Hide Entry Form';
            $('.ShowHideIcon').removeClass("fa-plus-circle");
            $('.ShowHideIcon').addClass("fa-minus-circle");
        }else{
            $('.ShowHideIcon').removeClass("fa-minus-circle");
            $('.ShowHideIcon').addClass("fa-plus-circle");
            $scope.FormName = 'Show Entry Form';
        }
        // $('.ShowHideIcon').toggleClass("fa-plus-circle fa-minus-circle");
    }
    /*========== Form Show Hide ==========*/ 



    /* ========== Check Session =========== */
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


                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocation();
                    $scope.getContact();
                    $scope.getMSGHistory();
                    $scope.getEMAILHistory();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
                
            }else{
                window.location.assign('index.html#!/login');
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ========== Check Session =========== */





    
    
    /* ========== SELECT CONTACT =========== */
    $scope.selectContact = function(item,val,AR){
        // console.log(index);
        // alert(val);
        // console.log(item);
        var idx = $scope.selectedContacts.findIndex(x => x.CID === item.CID);
        if(AR=='add'){
            if(idx<0){
                $scope.selectedContacts.push(item);
            }else{
                if(!val){
                    $scope.selectedContacts.splice(idx,1);
                    $('#chkSelect'+item.CID).prop('checked', false);
                }
            }
        }else{
            $scope.selectedContacts.splice(idx,1);
            $('#chkSelect'+item.CID).prop('checked', false);
        }

        $scope.selectAllContacts =  ($scope.selectedContacts.length == $scope.post.getContact.length) ? true : false;
    }
    $scope.selectAllContact = function(val){
        if(val){
            $scope.selectedContacts=[];
            $scope.selectedContacts = angular.copy($scope.post.getContact);
            angular.forEach($scope.selectedContacts, function(value, key) {
                $('#chkSelect'+value.CID).prop('checked', true);
            });
        }else{
            angular.forEach($scope.selectedContacts, function(value, key) {
                $('#chkSelect'+value.CID).prop('checked', false);
            });
            $scope.selectedContacts=[];
        }
    }
    $scope.checkSelectedContacts=function(){
        $scope.temp.chkSelect={};
        $timeout(function(){
            angular.forEach($scope.selectedContacts, function(value, key) {
                $('#chkSelect'+value.CID).prop('checked', true);
                // $scope.temp.chkSelect = [{key : fa}];
        },1000);
        });
        $scope.selectAllContacts =  ($scope.selectedContacts.length == $scope.post.getContact.length) ? true : false;
    }
    /* ========== SELECT CONTACT =========== */



    /* ========== Save Data =========== */
    $scope.saveContact = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveContact');
                formData.append("cid", $scope.temp.cid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtname", $scope.temp.txtname);
                formData.append("txtemail", $scope.temp.txtemail);
                formData.append("txtphone", $scope.temp.txtphone);
                formData.append("txtsubject", $scope.temp.txtsubject);
                formData.append("txtmessage", $scope.temp.txtmessage);            
                formData.append("txtResponse", $scope.temp.txtResponse);            
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.getContact();
                $scope.clearForm();
                
                $scope.messageSuccess(data.data.message);
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
    /* ========== Save Data =========== */







    /* ========== GET Contact =========== */
    $scope.getContact = function () {
        angular.forEach($scope.selectedContacts, function(value, key) {
            $('#chkSelect'+value.CID).prop('checked', false);
        });
        $scope.selectedContacts=[];
        $scope.selectAllContacts=false;
        $('.mainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getContact','ddlLocationSearch':$scope.temp.ddlLocationSearch}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data);
           $scope.post.getContact = data.data.data;
           $('.mainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getContact(); --INIT
    /* ========== GET Contact =========== */





    
    /* ========== GET Location =========== */
     $scope.getLocation = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLocation'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            if($scope.post.getLocations.length>0){
                $timeout(()=>{
                    $scope.temp.ddlLocation = ($scope.post.getLocations.length==1) ? $scope.post.getLocations[0]['LOC_ID'].toString() : '';
                },2000);
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocation(); --INIT
    /* ========== GET Location =========== */






    /* ============ Edit Button ============= */ 
    $scope.editContact = function (id) {
        $('.collapse').collapse('show');
        $timeout(function() {
            $scope.FormShowHide();
            $scope.FormName = 'Hide Entry Form';
            $('.ShowHideIcon').removeClass("fa-plus-circle");
            $('.ShowHideIcon').addClass("fa-minus-circle");
        },500);
        document.getElementById("ddlLocation").focus();

        $scope.temp.cid=id.CID;
        $scope.temp.ddlLocation= (id.LOCATIONID).toString();
        $scope.temp.txtname= id.FULLNAME;
        $scope.temp.txtemail= id.EMAILID;
        $scope.temp.txtphone= id.PHONE;
        $scope.temp.txtsubject= id.SUBJECT;
        $scope.temp.txtmessage= id.MESSAGE;
        $scope.temp.txtResponse=id.RESPONSE;


        $scope.editMode = true;
        $scope.index = $scope.post.getContact.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    




    

    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlLocation").focus();
        // $scope.temp={};
        $scope.temp.cid='';
        $scope.temp.ddlLocation= '';
        $scope.temp.txtname= '';
        $scope.temp.txtemail= '';
        $scope.temp.txtphone= '';
        $scope.temp.txtsubject= '';
        $scope.temp.txtmessage= '';
        $scope.temp.txtResponse= '';

        $scope.editMode = false;
    }
    /* ============ Clear Form =========== */ 





    /* ========== DELETE =========== */
    $scope.deleteContact = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'cid': id.CID, 'type': 'deleteContact' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getContact.indexOf(id);
		            $scope.post.getContact.splice(index, 1);
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



    // %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% EMAIL / SMS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




    /* ========== SMS/EMAIL =========== */
    $scope.saveData = function(SMS_EMAIL){
        $(".btn-saveSms,.btn-saveEmail").attr('disabled', 'disabled');
        if(SMS_EMAIL == 'SMS'){$(".btn-saveSms").text('Sending...')};
        if(SMS_EMAIL == 'EMAIL'){$(".btn-saveEmail").text('Sending...')};

        $TYPE = SMS_EMAIL==='SMS' ? 'saveDataSms' : 'saveDataEmail';
        if(SMS_EMAIL==='EMAIL'){$scope.temp.txtAttachment=$scope.files[0];}else{$scope.temp.txtAttachment='';};

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", $TYPE);
                formData.append("txtMessage", $scope.temp.txtMessage);
                formData.append("ContactData", JSON.stringify($scope.selectedContacts));
                formData.append("txtAttachment", $scope.temp.txtAttachment);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
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



    /* ========== Message =========== */
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').show();
    //     jQuery('.alert-success').delay(5000).slideUp(function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }
    
    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').show();
    //     jQuery('.alert-danger').delay(5000).slideUp(function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }
    // /* ========== Message =========== */
    
    


});