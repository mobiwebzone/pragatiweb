$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect","angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.Page = "STUDENT";
    $scope.PageSub = "SMS";
    $scope.temp.txtCoverageDT = new Date();
    $scope.PLANS_model = [];
    $scope.CLASSOF_model = [];
    $scope.SUBJECT_model = [];
    $scope.PRODUCTS_model = [];
    $scope.STUDENTS_model = [];
    $scope.files = [];

    $scope.FromDT_S = new Date();
    $scope.FromDT_S.setDate($scope.FromDT_S.getDate() - 7);
    // $scope.txtFromDT=new Date($scope.FromDT_S).toLocaleDateString('sv-SE');

    $scope.temp.txtFromDT=$scope.temp.txtFromDTEmail = new Date($scope.FromDT_S);
    $scope.temp.txtToDT=$scope.temp.txtToDTEmail = new Date();
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============


    $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.CLASSOF_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.SUBJECT_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.PRODUCTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.STUDENTS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    
    var url = 'code/Student_SMS.php';



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

                $scope.getPlans();
                $scope.getClassof();
                $scope.getSubject();
                $scope.getMSGHistory();
                $scope.getEMAILHistory();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENT COURSE COVERAGE SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


    // =========== SAVE DATA ==============
    $scope.saveData = function(SMS_EMAIL){
        $(".btn-saveSms,.btn-saveEmail").attr('disabled', 'disabled');
        if(SMS_EMAIL == 'SMS'){$(".btn-saveSms").text('Sending...')};
        if(SMS_EMAIL == 'EMAIL'){$(".btn-saveEmail").text('Sending...')};

        // $scope.F_STUDENTID = [];
        // $scope.F_STUDENTID = $scope.STUDENTS_model.map(x=>x.id);

        // console.log($scope.FinalChapters);
        $TYPE = SMS_EMAIL==='SMS' ? 'saveDataSms' : 'saveDataEmail';
        if(SMS_EMAIL==='EMAIL'){$scope.temp.txtAttachment=$scope.files[0];}else{$scope.temp.txtAttachment='';};

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", $TYPE);
                formData.append("ddlStudentType", $scope.temp.ddlStudentType);
                // formData.append("STUDENTID", $F_STUDENTID);
                formData.append("txtFirstName", $scope.temp.txtFirstName);
                formData.append("txtLastName", $scope.temp.txtLastName);
                formData.append("txtMobile", $scope.temp.txtMobile);
                formData.append("txtEmail", $scope.temp.txtEmail);
                formData.append("txtMessage", $scope.temp.txtMessage);
                formData.append("STUDENTID", JSON.stringify($scope.STUDENT_LIST));
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
    // =========== SAVE DATA ==============






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




    /* ========== GET CLASS OF =========== */
    $scope.getClassof = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinClassof').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getClassof'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.Classof = data.data.success ? data.data.data : [];
            $('.spinClassof').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassof(); --INIT
    /* ========== GET CLASS OF =========== */




    /* ========== GET SUBJECT =========== */
    $scope.getSubject = function () {
        $scope.STUDENT_LIST = [];
        $scope.STUDENTS_model = [];
        $scope.post.getStudentByPlanProduct = [];
        $('.spinSubject').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSubject'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.Subject = data.data.success ? data.data.data : [];
            $('.spinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubject(); --INIT
    /* ========== GET SUBJECT =========== */






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
        $FINAL_CLASSOF = [];
        $FINAL_CLASSOF = $scope.CLASSOF_model.map(x=>x.id);
        $FINAL_SUBJECT = [];
        $FINAL_SUBJECT = $scope.SUBJECT_model.map(x=>x.id);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByPlanProduct', 
                            'PLANID' : $FINAL_PLANID,
                            'CLASSOF' : $FINAL_CLASSOF,
                            'SUBJECT' : $FINAL_SUBJECT,
                            'ddlLocation' : 0
                            // 'PRODUCTID' : $FINAL_PRODUCTID
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
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
        $scope.CLASSOF_model=[];
        $scope.SUBJECT_model=[];
        $scope.PRODUCTS_model=[];
        $scope.post.getPlanProduct=[];
        $scope.STUDENTS_model=[];
        $scope.post.getStudentByPlanProduct=[];
        $scope.STUDENT_LIST=[];

        $scope.files = [];
        angular.element('#txtAttachment').val(null);

        $scope.temp.txtFromDT=$scope.temp.txtFromDTEmail = new Date($scope.FromDT_S);
        $scope.temp.txtToDT=$scope.temp.txtToDTEmail = new Date();
    }
    $scope.clearBYStudentType = function(){
        
        $scope.PLANS_model=[];
        $scope.CLASSOF_model=[];
        $scope.SUBJECT_model=[];
        $scope.PRODUCTS_model=[];
        $scope.post.getPlanProduct=[];
        $scope.STUDENTS_model=[];
        $scope.post.getStudentByPlanProduct=[];
        $scope.STUDENT_LIST=[];
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SCCID': id.SCCID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentCourseCoverage.indexOf(id);
		            $scope.post.getStudentCourseCoverage.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENTS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $scope.selectedStudentData=[];
    $scope.STUDENT_LIST = [];
    // =========== ADD STUDENTS ==============
    $scope.addRemoveStudents=function(){
        $scope.STUDENT_LIST = $scope.STUDENTS_model;
        // console.log($scope.STUDENTS_model);
        // $('#alert-AddStudent').hide();
        
        // if(FOR === 'ADD'){
        //     $('.btn-save-Students').attr('disabled','disabled');
        //     if(!$scope.temp.ddlStudent || $scope.temp.ddlStuden<=0) return
        //     if($scope.selectedStudentData.find((x)=>x.PRODUCTID == $scope.temp.ddlStudent) != undefined){
        //         $('#alert-AddStudent').show();
        //         $timeout(()=>$('#alert-AddStudent').hide(),5000);
        //     }else{
        //         // console.log($scope.selectedStudentData.find((x)=>x.studentid == $scope.temp.ddlStudent));
        //         $scope.selectedStudentData.push({
        //             studentid : Number($scope.temp.ddlStudent),
        //             student : $scope.post.getStudentByPlan.filter((x)=>x.REGID==$scope.temp.ddlStudent).map((x)=>x.STUDENT).toString(), 
        //             remark : (!$scope.temp.txtRemark || $scope.temp.txtRemark == '') ? '' : $scope.temp.txtRemark
        //         });
        //     }
        //     $('.btn-save-Students').removeAttr('disabled');
        //     $scope.temp.ddlStudent = '';
        //     $scope.temp.txtRemark = '';
        // }else{
        //     var ss = $scope.selectedStudentData.indexOf(id);
        //     $scope.selectedStudentData.splice(ss,1);
        // }
    }
    // =========== ADD STUDENTS ==============





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