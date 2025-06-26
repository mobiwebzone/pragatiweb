$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","textAngular"]);
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
// taOptions.toolbar = [
//     ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
//     ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
//     ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
//     ['html', 'insertImage','insertLink', 'insertVideo', 'wordcount', 'charcount']
// ];
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$sce,taOptions) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "QUESTION_BANK";
    $scope.QuestionOptions = [];
    $scope.QueOP = [];
    $scope.files = [];
    $scope.filesExp = [];
    $scope.filesExcel = [];
    $scope.Tdate = new Date().toLocaleString('en-US');

    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        // ['p'],
        ['p','bold', 'italics', 'underline', 'strikeThrough', 'redo', 'undo', 'clear'],
        // ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========

    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Question_Bank_code.php';




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




    /*========= Image Preview =========*/ 
    $scope.UploadLogo = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.logo_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview =========*/ 




    /*========= Image Preview EXP =========*/ 
    $scope.UploadLogoExp = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.logo_srcExp = event.target.result
            $scope.$apply(function ($scope) {
                $scope.filesExp = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview EXP =========*/ 





    // =============== Add Remove Question =============
    $scope.AddRemoveQuestion=function(For,option){
        // alert(For +'///'+ option);
        // console.log(For +'///'+ option);
        if(For == 'add'){

            if($scope.QuestionOptions.includes(option)){
                // alert('" '+option + ' " alredy exist.');
            }else{
                if(option.length>0){
                    $scope.QuestionOptions.push(option);
                    $scope.temp.txtQueOption = '';
                }
                $('#txtQueOption').focus();
            }

        }
        else if(For == 'remove'){
            if (option > -1) {
                $scope.QuestionOptions.splice(option, 1);
                $('#txtQueOption').focus();
            }
        }
    }
    // =============== Add Remove Question =============





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

                $scope.getSectionMaster();
                // $scope.getQuestions();
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





    /* ========== GET SECTION MASTER =========== */
    $scope.getSectionMaster = function () {
        $('.SectionSpin').show();
        $http({
            method: 'post',
            url: 'code/Question_Master_code.php',
            data: $.param({ 'type': 'getSectionMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSectionMaster = data.data.data;

            $('.SectionSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSectionMaster(); --INIT
    /* ========== GET SECTION MASTER =========== */





    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function (secid) {
        $('.CategorySpin').show();
        $scope.post.getCategories=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getCategories', 'secid' : secid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.data;
            $('.CategorySpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories();
    /* ========== GET CATEGORIES =========== */




    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function (catid) {
        $('.SubCatSpin').show();
        $scope.post.getSubCategories=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'catid' : catid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubCategories = data.data.data;
            $('.SubCatSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */





    /* ========== GET TOPICS =========== */
    $scope.getTopic = function (subcatid) {
        $('.TopicSpin').show();
        $scope.post.getTopic=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getTopic', 'subcatid' : subcatid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTopic = data.data.data;
            $('.TopicSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTopic();
    /* ========== GET TOPICS =========== */







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
                formData.append("ddlTopic", $scope.temp.ddlTopic);
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
                angular.element('#txtUploadExcel').val(null);
                $scope.temp.txtUploadExcel='';
                $scope.filesExcel=[];

                $scope.getQuestions();
                $scope.messageSuccess(data.data.message);                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            // $('.uploadBtn').removeAttr('disabled');
            $(".uploadBtn").text('Upload');
        });
    }
    // =========== SAVE EXCEL DATA ==============






    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        $scope.temp.txtUploadExcelData='';
        angular.element('#txtUploadExcel').val(null);
        $scope.temp.txtUploadExcel='';
        $scope.filesExcel=[];
        $('.uploadBtn').attr('disabled','disabled');


        $scope.QP = [];
        for(var i=0;i<$scope.QuestionOptions.length;i++){
            $scope.QuestionOptions[i]=$scope.QuestionOptions[i]+";#;";
        }
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        
        $scope.temp.txtQueImage = $scope.files[0];
        $scope.temp.txtExpImage = $scope.filesExp[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("queid", $scope.temp.queid);
                formData.append("ddlTopic", $scope.temp.ddlTopic);
                formData.append("txtQuestion", $scope.temp.txtQuestion);
                formData.append("ddlQueType", $scope.temp.ddlQueType);
                formData.append("rdCalcAllow", $scope.temp.rdCalcAllow);
                formData.append("QuestionOptions", $scope.QuestionOptions);
                formData.append("txtAnswer", $scope.temp.txtAnswer);
                formData.append("txtQueImage", $scope.temp.txtQueImage);
                formData.append("existingQueImage", $scope.temp.existingQueImage);
                formData.append("rdGridIn", $scope.temp.rdGridIn);
                formData.append("rdWordProblem", $scope.temp.rdWordProblem);
                formData.append("txtAnswerExplanation", $scope.temp.txtAnswerExplanation);
                formData.append("txtExpImage", $scope.temp.txtExpImage);
                formData.append("existingExpImage", $scope.temp.existingExpImage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {

                $scope.messageSuccess(data.data.message);

                $scope.QuestionOptions = $scope.QuestionOptions.map(x => {
                    return x.replace(';#;','');
                })

                if($scope.editMode == false){
                    $scope.QuestionOptions =[];
                    $scope.temp.txtQueOption='';
                    $scope.temp.txtAnswer='';
                    $scope.temp.ddlQueType='';
                    $scope.temp.rdCalcAllow='';
                    $scope.temp.txtQuestion='';
                    $scope.temp.txtQueImage='';
                    $scope.temp.existingQueImage='';
                    $scope.logo_src='';
                    $scope.files=[];
                    $scope.temp.rdGridIn='';
                    $scope.temp.rdWordProblem='';
                    
                    $scope.temp.ddlQueType='MCQ';
                    $scope.temp.rdCalcAllow='0';
                    $scope.temp.rdGridIn='0';
                    $scope.temp.rdWordProblem='0';
                    $scope.temp.txtAnswerExplanation='';
                    $scope.logo_srcExp='';
                    $scope.filesExp=[];
                    $scope.temp.txtExpImage='';
                    $scope.temp.existingExpImage='';
                }

                
                $scope.getQuestions();
                // $scope.getSectionMaster();
                // $scope.clearForm();
                // document.getElementById("txtSection").focus();
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

    




    /* ========== GET QUESTIONS =========== */
    $scope.getQuestions = function () {
        $('.AllDataTable').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getQuestions', 
                            'ddlSection' : $scope.ddlSearchSection,
                            'ddlCategory' : $scope.ddlSearchCategory,
                            'ddlSubCategory' : $scope.ddlSearchSubCategory,
                            'ddlTopic' : $scope.ddlSearchTopic}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getQuestions = data.data.success ? data.data.data : [];
            $scope.QueOP = data.data.success ? data.data.QueOP : [];
            $('.AllDataTable').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getQuestions(); --INIT
    /* ========== GET QUESTIONS =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {

        $scope.temp.txtUploadExcelData='';
        angular.element('#txtUploadExcel').val(null);
        $scope.temp.txtUploadExcel='';
        $scope.filesExcel=[];
        $('.uploadBtn').attr('disabled','disabled');

        document.getElementById("txtQuestion").focus();

        $scope.temp.ddlSection=(id.SECID).toString();

        if($scope.temp.ddlSection > 0){
            $scope.getCategories(id.SECID);
            $timeout(function () {  
                $scope.temp.ddlCategory = (id.CATID).toString();
                if($scope.temp.ddlCategory > 0){
                    $scope.getSubCategories(id.CATID);
                    $timeout(function () { 
                        $scope.temp.ddlSubCategory = (id.SUBCATID).toString();
                        if($scope.temp.ddlSubCategory > 0){
                            $scope.getTopic(id.SUBCATID);
                            $timeout(function () { 
                                $scope.temp.ddlTopic = (id.TOPICID).toString();
                            },550);
                        }
                    },450);
    
                }
            },300);

        }
        
        $scope.temp.queid=id.QUEID;
        $scope.temp.txtQuestion=id.QUESTION;
        $scope.temp.ddlQueType=id.QUETYPE;
        $scope.temp.rdCalcAllow=(id.ALLOWEDCALC).toString();
        $scope.temp.txtAnswer=id.CORRECTANSWER;
        $scope.QuestionOptions = id.QUETYPE == 'MCQ' ? id.QUEOPTIONS.split(" ,") : [];
        $scope.temp.existingQueImage=id.QUEIMAGE;
        $scope.temp.rdGridIn=(id.GRIDIN).toString();
        $scope.temp.rdWordProblem=(id.WORDPROBLEM).toString();

        $scope.temp.txtAnswerExplanation=id.ANS_EXPLANATION;
        $scope.temp.existingExpImage=id.ANS_EXPIMAGE;
        
        /*########### IMG #############*/
        if(id.QUEIMAGE != ''){
            $scope.logo_src='question_images/'+id.QUEIMAGE;
        }else{
            $scope.logo_src='question_images/default.png';
        }
        if(id.ANS_EXPIMAGE != ''){
            $scope.logo_srcExp='question_images/explanation_images/'+id.ANS_EXPIMAGE;
        }else{
            $scope.logo_srcExp='question_images/default.png';
        }

        $scope.editMode = true;
        $scope.index = $scope.post.getQuestions.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    




    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlSection").focus();
        $scope.temp={};
        $scope.QuestionOptions =[];
        $scope.editMode = false;
        $scope.logo_src='';
        $scope.files=[];

        $scope.temp.ddlQueType='MCQ';
        $scope.temp.rdCalcAllow='0';
        $scope.temp.rdGridIn='0';
        $scope.temp.rdWordProblem='0';

        $scope.logo_srcExp='';
        $scope.filesExp=[];
    }
    /* ============ Clear Form =========== */ 






    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'queid': id.QUEID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getQuestions.indexOf(id);
		            $scope.post.getQuestions.splice(index, 1);
		            // console.log(data.data.message)

                    $scope.QuestionOptions =[];
                    $scope.temp.txtQueOption='';
                    $scope.temp.txtAnswer='';
                    $scope.temp.ddlQueType='';
                    $scope.temp.rdCalcAllow='';
                    $scope.temp.txtQuestion='';
                    $scope.temp.txtQueImage='';
                    $scope.temp.existingQueImage='';
                    $scope.logo_src='';
                    $scope.files=[];
                    $scope.temp.rdGridIn='';
                    $scope.temp.rdWordProblem='';

                    $scope.temp.ddlQueType='MCQ';
                    $scope.temp.rdCalcAllow='0';
                    $scope.temp.rdGridIn='0';
                    $scope.temp.rdWordProblem='0';

                    $scope.temp.txtAnswerExplanation='';
                    $scope.logo_srcExp='';
                    $scope.filesExp=[];
                    $scope.temp.txtExpImage='';
                    $scope.temp.existingExpImage='';
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
    




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
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }
    /* ========== MESSAGE =========== */




});