$postModule = angular.module("myApp", ["ngSanitize","angularjs-dropdown-multiselect","textAngular"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
    $scope.post = {};
    $scope.temp = {};
    $scope.Page = "L&A";
    $scope.PageSub = "LA_MASTER";
    $scope.PageSub1 = "LA_QUESTION_M";
    $scope.editMode = false;
    $scope.editModePs = false;
    $scope.editModeOp = false;
    $scope.temp.txtPassage = '';
    $scope.passage_src='';
    $scope.files = [];
    $scope.option_src='';
    $scope.filesOp = [];

    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
        ['bold', 'italics', 'underline', 'strikeThrough', 'insertImage' ,'ul', 'ol', 'redo', 'undo', 'clear'],
        // ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========
    
    var url = 'code/LA-QUESTION-MASTER.php';

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


    /*========= Image Preview ST =========*/ 
    $scope.FILE_EXTENTION = '';
    $scope.UploadImage = function (element) {
        
        if (!element || !element.files[0] || element.files[0].length === 0){return;}
        
        const fileType = element.files[0]['type'].split('/')[0];
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {

            if(fileType == 'image'){$scope.passage_src = event.target.result}
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
        // if(fileType != 'image') $scope.passage_src = $scope.FileTypeImage(fileType,ext);
    }
    $scope.UploadImageOp = function (element) {
        
        if (!element || !element.files[0] || element.files[0].length === 0){return;}
        
        const fileType = element.files[0]['type'].split('/')[0];
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {

            if(fileType == 'image'){$scope.option_src = event.target.result}
            $scope.$apply(function ($scope) {
                $scope.filesOp = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview ST =========*/ 


    /* ============ CHECK SESSION ============= */ 
    $scope.init = function () {
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
                $scope.locid = data.data.locid;
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN" && $scope.userrole != "LA_MASTER")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ============ CHECK SESSION ============= */ 



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
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
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

                $scope.getMainQuestions();
                angular.element('#txtUploadExcel').val(null); 
                angular.element('.uploadBtn').attr('disabled','disabled');
                // if($scope.temp.mqueid > 0) $scope.getSubQuestions(); 

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


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MAIN QUESTIONS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

    /* ============ SAVE DATA ============= */ 
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("mqueid", $scope.temp.mqueid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("ddlTopic", $scope.temp.ddlTopic);
                formData.append("txtDayNo",$scope.temp.txtDayNo);
                formData.append("txtTestCode",$scope.temp.txtTestCode);
                formData.append("txtQuestion", $scope.temp.txtQuestion);
                formData.append("txtPassage", $scope.temp.txtPassage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.mqueid = data.data.GET_MQUEID;
                $scope.getMainQuestions();
                if($scope.temp.mqueid > 0) $scope.getSubQuestions(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#ddlLocation").focus();},500);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */ 





    /* ========== GET MAIN QUESTIONS =========== */
    $scope.getMainQuestions = function () {
        // if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getMainQuestions');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("ddlTopic", $scope.temp.ddlTopic);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMainQuestions = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMainQuestions();
    /* ========== GET MAIN QUESTIONS =========== */

    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlGrade").focus();
        $scope.clearFormDET();

        $scope.temp.mqueid = id.MQUEID;
        // $scope.temp.ddlLocation = (id.LOCID && id.LOCID>0) ? id.LOCID.toString() : '';
        if(id.LOCID > 0){
            // $scope.getGrades();
            // $scope.getSubjects();
            $scope.temp.ddlGrade = id.GRADEID.toString();
            $scope.temp.ddlSubject = id.SUBID.toString();
            if($scope.temp.ddlGrade>0 && $scope.temp.ddlSubject>0)$scope.getTopics();
            $scope.$watch('post.getTopics', function () {
                $scope.temp.ddlTopic = id.TOPICID;
            }, true);
            $scope.temp.txtDayNo=Number(id.DAYNO);
            $scope.temp.txtTestCode=id.TESTCODE;
            $scope.temp.txtQuestion = id.QUESTION;
            $scope.temp.txtPassage = id.PASSAGE;
        }

        $scope.getPassageImages();
        $scope.getSubQuestions();
        $scope.editMode = true;
        $scope.index = $scope.post.getMainQuestions.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    


    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp.mqueid='';
        $scope.temp.ddlGrade='';
        $scope.temp.ddlSubject='';
        $scope.temp.ddlTopic='';
        $scope.temp.txtDayNo='';
        $scope.temp.txtTestCode='';
        $scope.temp.txtQuestion='';
        $scope.temp.txtPassage='';
        $scope.editMode = false;

        $scope.clearFormDET();
        $scope.clearFormPs();
        $scope.clearFormOPT();
        $("#ddlGrade").focus();
    }
    /* ============ Clear Form =========== */ 





    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'MQUEID': id.MQUEID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getMainQuestions.indexOf(id);
		            $scope.post.getMainQuestions.splice(index, 1);
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MAIN QUESTIONS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE SUB QUESIONS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
    $scope.gridTextArr = [];
    $scope.gridFilled = false;
    /* ============ CREATE TABLE DYNAMIC ========= */
    $scope.createTable = function() {
        $scope.gridFilled =false;
        $scope.gridTextArr = [];
        $scope.tableRows=$scope.tableCols = [];
        if($scope.temp.txtRows > 0 && $scope.temp.txtCols>0){
            $scope.tableRows = new Array($scope.temp.txtRows);
            $scope.tableCols = new Array($scope.temp.txtCols);
        }   
        $scope.gArr = [];
        for (var i = 0; i < $scope.tableRows.length; i++) {
            $scope.gArr[i] = {};
        
            for (var j = 0; j < $scope.tableCols.length; j++) {
              $scope.gArr[i][j] = '';
            }
        }
        $scope.gridTextArr=$scope.gArr;
    }

    $scope.checkGridFilled = function(){
        $scope.gridFilled = $scope.gridTextArr.every(obj => Object.values(obj).every(value => value !== '' && value !== null && value !== undefined));
    }
    /* ============ CREATE TABLE DYNAMIC ========= */


    /* ============ SAVE DATA ============= */ 
    $scope.saveDataDET = function(){
        $(".btn-save-DET").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-DET").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET');
                formData.append("squeid", $scope.temp.squeid);
                formData.append("mqueid", $scope.temp.mqueid);
                formData.append("txtQuestionSub", $scope.temp.txtQuestionSub);
                formData.append("ddlQueType", $scope.temp.ddlQueType);
                formData.append("txtAnswer", $scope.temp.txtAnswer);
                formData.append("txtRows", $scope.temp.txtRows);
                formData.append("txtCols", $scope.temp.txtCols);
                formData.append("gridTextArr", JSON.stringify($scope.gridTextArr));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.getSubQuestions();
                if($scope.temp.ddlQueType == 'TYPE IN' || $scope.temp.ddlQueType == 'GRID'){
                    $scope.clearFormDET();
                }else{
                    $scope.temp.squeid = data.data.GET_MQUEID;
                    if($scope.temp.squeid > 0) $scope.getOptions();
                }
                $scope.temp.txtQuestionSub = '';
                $scope.temp.txtAnswer = '';
                // if($scope.temp.ddlQueType == 'TYPEIN')
                $scope.messageSuccess(data.data.message);
                
                $("#ddlQueType").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-DET').removeAttr('disabled').text('ADD');
            $('.btn-update-DET').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */






    /* ========== GET SUB QUESTIONS =========== */
    $scope.getSubQuestions = function () {
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getSubQuestions','mqueid':$scope.temp.mqueid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubQuestions = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubQuestions();
    /* ========== GET SUB QUESTIONS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#ddlQueType").focus();

        $scope.temp.squeid = id.SQUEID;
        $scope.temp.txtQuestionSub = id.QUESTION;
        $scope.temp.ddlQueType = id.QUE_TYPE;
        $scope.temp.txtAnswer = id.ANSWER;
        $scope.temp.txtRows = Number(id.ROWS);
        $scope.temp.txtCols = Number(id.COLUMNS);

        if(id.QUE_TYPE === 'GRID'){
            // $scope.createTable();
            $scope.tableRows = new Array($scope.temp.txtRows);
            $scope.tableCols = new Array($scope.temp.txtCols);
            $scope.gridTextArr = id.QUESTION_GRID;
            $timeout(()=>{
                $scope.checkGridFilled();
            },1000);
        }

        if($scope.temp.squeid > 0)$scope.getOptions();
        $scope.index = $scope.post.getSubQuestions.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#ddlQueType").focus();
        $scope.temp.squeid = '';
        $scope.temp.txtQuestionSub = '';
        $scope.temp.ddlQueType = '';
        $scope.temp.txtAnswer = '';

        $scope.clearFormOPT();
        $scope.clearFieldsOnChangeQueType();
    }
    $scope.clearFieldsOnChangeQueType = function(){
        $scope.temp.txtAnswer='';
        $scope.temp.txtRows='';
        $scope.temp.txtCols='';
        if($scope.ddlQueType=='GRID')$scope.temp.txtQuestionSub='';

        $scope.gridFilled =false;
        $scope.gridTextArr = [];
        $scope.tableRows=$scope.tableCols = [];
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SQUEID': id.SQUEID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getSubQuestions.indexOf(id);
		            $scope.post.getSubQuestions.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormDET();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE SUB QUESIONS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 






/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE OPTIONS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
    $scope.openOptionsModal = function(id){
        $scope.temp.squeid=id.SQUEID;
        $scope.OPT_SQUEID = id.SQUEID;
        $scope.QUESTION_NAME = id.QUESTION;
        $scope.getOptions();
    }


    /* ============ SAVE DATA ============= */ 
    $scope.saveDataOPT = function(){
        $(".btn-save-OPT").attr('disabled', 'disabled').text('Add...');
        $(".btn-update-OPT").attr('disabled', 'disabled').text('Updating...');
        $scope.temp.optionImg = $scope.filesOp[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataOPT');
                formData.append("optionid", $scope.temp.optionid);
                formData.append("squeid", $scope.OPT_SQUEID);
                formData.append("txtOption", $scope.temp.txtOption);
                formData.append("chkIsCorrect", $scope.temp.chkIsCorrect);
                formData.append("optionImg", $scope.temp.optionImg);
                formData.append("existingOptionImg", $scope.temp.existingOptionImg);
                formData.append("chkRemoveImgOnUpdate", ((!$scope.option_src || $scope.option_src.length<=0) && $scope.editModeOp)?1:0);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getOptions();
                $scope.clearFormOPT();
                $scope.messageSuccess(data.data.message);
                
                $("#txtOption").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-OPT').removeAttr('disabled').text('ADD');
            $('.btn-update-OPT').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */






    /* ========== GET SUB QUESTIONS =========== */
    $scope.getOptions = function () {
        $('#spinOPT').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getOptions','squeid':$scope.temp.squeid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getOptions = data.data.success ? data.data.data : [];
             $('#spinOPT').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getOptions();
    /* ========== GET SUB QUESTIONS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormOPT = function (id) {
        $("#txtOption").focus();

        $scope.temp.optionid = id.OPTIONID;
        $scope.temp.txtOption = id.OPTIONS;
        $scope.temp.existingOptionImg = id.IMAGE;
        $scope.temp.chkIsCorrect = id.ISCORRECT > 0 ? '1' : '0';

        /*########### IMG #############*/
        if(id.IMAGE!= ''){
            $scope.option_src='images/la_question_master/'+id.IMAGE;
        }else{
            $scope.option_src='';
        }

        $scope.index = $scope.post.getOptions.indexOf(id);
        $scope.editModeOp = true;
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormOPT = function(){
        $("#txtOption").focus();
        $scope.temp.optionid = '';
        $scope.temp.txtOption = '';
        $scope.temp.chkIsCorrect = '0';
        $scope.temp.optionImg = '';
        $scope.temp.existingOptionImg = '';
        $scope.editModeOp = false;
        $scope.clearPassageImage('OP');
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteOPT = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'OPTIONID': id.OPTIONID, 'type': 'deleteOPT' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getOptions.indexOf(id);
		            $scope.post.getOptions.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormOPT();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE OPTIONS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
    
    
    
    
    






/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE PASSAGE IMAGE START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

/* ============ SAVE DATA ============= */ 
$scope.saveDataPs = function(){
    $(".btn-savePs").attr('disabled', 'disabled').text('Save...');
    $(".btn-updatePs").attr('disabled', 'disabled').text('Updating...');
    $scope.temp.passageImg = $scope.files[0];
    $http({
        method: 'POST',
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'saveDataPs');
            formData.append("piid", $scope.temp.piid);
            formData.append("mqueid", $scope.temp.mqueid);
            formData.append("passageImg", $scope.temp.passageImg);
            formData.append("existingPassageImg", $scope.temp.existingPassageImg);
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }        
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
            $scope.getPassageImages();
            $scope.clearFormPs();
            $scope.messageSuccess(data.data.message);
            
            $("#txtOption").focus();
        }
        else {
            $scope.messageFailure(data.data.message);
            // console.log(data.data)
        }
        $('.btn-savePs').removeAttr('disabled').text('SAVE');
        $('.btn-updatePs').removeAttr('disabled').text('UPDATE');
    });
}
/* ============ SAVE DATA ============= */






/* ========== GET PASSAGE IMAGES =========== */
$scope.getPassageImages = function () {
    $('#spinPs').show();
    $http({
         method: 'post',
         url: url,
        data: $.param({ 'type': 'getPassageImages','mqueid':$scope.temp.mqueid}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getPassageImages = data.data.success ? data.data.data : [];
         $('#spinPs').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getPassageImages();
/* ========== GET PASSAGE IMAGES =========== */






/* ============ Edit Button ============= */ 
$scope.editFormPs = function (id) {
    $("#passageImg").focus();

    $scope.temp.piid = id.PIID;
    $scope.temp.existingPassageImg = id.PASSAGE_IMAGE;
    $scope.editModePs = true;

    /*########### IMG #############*/
    if(id.PASSAGE_IMAGE!= ''){
        const name_edit = id.PASSAGE_IMAGE;
        const lastDot_edit = name_edit.lastIndexOf('.');
        const ext_edit = name_edit.substring(lastDot_edit + 1);

        $scope.passage_src='images/la_question_master/'+id.PASSAGE_IMAGE;
        // if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
        // }else{
        //     $scope.passage_src = $scope.FileTypeImage('',ext_edit);
        // }
    }else{
        $scope.passage_src='';
    }
    $scope.index = $scope.post.getPassageImages.indexOf(id);
}
/* ============ Edit Button ============= */ 




/* ============ Clear Form =========== */ 
$scope.clearFormPs = function(){
    $("#passageImg").focus();
    $scope.temp.piid = '';
    $scope.temp.passageImg = '';
    $scope.temp.existingPassageImg = '';
    $scope.clearPassageImage('PI');
    $scope.editModePs = false;
}
/* ============ Clear Form =========== */ 




/* ========== DELETE =========== */
$scope.deletePs = function (id) {
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'PIID': id.PIID, 'type': 'deletePs' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data)
            if (data.data.success) {
                var index = $scope.post.getPassageImages.indexOf(id);
                $scope.post.getPassageImages.splice(index, 1);
                // console.log(data.data.message)
                $scope.clearFormPs();
                
                $scope.messageSuccess(data.data.message);
            } else {
                $scope.messageFailure(data.data.message);
            }
        })
    }
}
/* ========== DELETE =========== */

$scope.copyToClipboard = function(text) {
    var finalText = `https://www.myexamsprep.us/backoffice/images/la_question_master/${text}`;
    var dummy = document.createElement('textarea');
    document.body.appendChild(dummy);
    dummy.value = finalText;
        // Select the text field
    dummy.select();
    dummy.setSelectionRange(0, 99999); // For mobile devices

    // Copy the text inside the text field
    navigator.clipboard.writeText(dummy.value);
    document.body.removeChild(dummy);
};
/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE PASSAGE IMAGE END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 








    
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */

    /* ============ CLEAR ============= */ 
    $scope.clearPassageImage=(FOR)=>{
        if(FOR=='PI'){
            $scope.passage_src='';
            angular.element('#passageImg').val(null);
            $scope.files = [];
        }else if(FOR=='OP'){
            $scope.option_src='';
            angular.element('#optionImg').val(null);
            $scope.filesOp = [];
        }
    }
    /* ============ CLEAR ============= */ 

    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getStudentByLoc = [];
        $scope.post.getLocReviewByLoc = [];
        $('.spinLoc').show();
        $http({
            method: 'post',
            // url: masterUrl,
            // data: $.param({ 'type': 'getUserLocationsWithMainLocation'}),
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getMainQuestions();
            // if($scope.temp.ddlLocation > 0) $scope.getGrades();
            // if($scope.temp.ddlLocation > 0) $scope.getSubjects();
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $('.spinGrade').show();
        $http({
            method: 'POST',
            url: 'code/LA-GRADE-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrades = data.data.success ? data.data.data : [];
            $('.spinGrade').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
     $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $('.spinSubject').show();
        $http({
            method: 'POST',
            url: 'code/LA-SUBJECT-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjects');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubjects = data.data.success ? data.data.data : [];
            $('.spinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
     $scope.getSubjects();
    /* ========== GET SUBJECT =========== */

    /* ========== GET TOPICS =========== */
    $scope.getTopics = function () {
        $scope.post.getTopics=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0)return;
        $('.spinTopic').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTopics');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getTopics = data.data.success ? data.data.data : [];
            $('.spinTopic').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getTopics();
    /* ========== GET TOPICS =========== */




/* ######################################################################################################################### */
/*                                           GET EXTRA DATA END                                                              */
/* ######################################################################################################################### */
    


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