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
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "UPD_TEST_RESULT";
    $scope.doc_src='';
    $scope.files = [];

    
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Upload_Test_Result_code.php';


    /*========= Image Preview =========*/ 
    $scope.FILE_EXTENTION = '';
    $scope.UploadImage = function (element) {
        
        if (!element || !element.files[0] || element.files[0].length === 0){return;}
        
        const fileType = element.files[0]['type'].split('/')[0];
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {

            if(fileType == 'image'){$scope.doc_src = event.target.result}
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
        if(fileType != 'image') $scope.doc_src=$scope.FileTypeImage(fileType,ext);
    }
    /*========= Image Preview =========*/ 
    $scope.FileTypeImage = function (FType,EXT) {
        if(['xlsx','xlsm','xlsb','xltx','xltm','xls','xlt','xls','csv','xml','xlam','xla','xlw','xlr'].includes(EXT)){
            var src = '../images/FileEx/xls.png';
        } 
        else if(['pdf'].includes(EXT)){var src = '../images/FileEx/pdf.png';} 
        else if(['doc','docx'].includes(EXT)){var src = '../images/FileEx/doc.png';} 
        else if(['pptx','pptm','ppt'].includes(EXT)){var src = '../images/FileEx/ppt.png';} 
        else if(['txt'].includes(EXT)){var src = '../images/FileEx/txt.png';}
        else{var src = '../images/FileEx/document.png';}

        return src;
    }
    $scope.clearDocs=()=>{
        $scope.doc_src='';
        $scope.files = [];
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
            // console.log(data.data);
            if (data.data.success) {
                $scope.post.user = data.data.data;
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;
                // window.location.assign("dashboard.html");

                $scope.getLocations();
                // $scope.getTestMaster();
                // $scope.getEssays();
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



    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.DocsUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("stid", $scope.temp.stid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("ddlTest", $scope.temp.ddlTest);
                formData.append("ddlTestSection", $scope.temp.ddlTestSection);
                formData.append("txtTestDate", (!$scope.temp.txtTestDate) ? '' : $scope.temp.txtTestDate.toLocaleString('sv-SE'));
                formData.append("txtstartDate", (!$scope.temp.txtstartDate) ? '' : $scope.temp.txtstartDate.toLocaleString('sv-SE'));
                formData.append("txtEndDate", (!$scope.temp.txtEndDate) ? '' : $scope.temp.txtEndDate.toLocaleString('sv-SE'));
                formData.append("txtScore", $scope.temp.txtScore);
                formData.append("txtScale", $scope.temp.txtScale);
                formData.append("txtReviewed", $scope.temp.txtReviewed);
                formData.append("DocsUpload", $scope.temp.DocsUpload);
                formData.append("existingDocsUpload", $scope.temp.existingDocsUpload);
                formData.append("chkRemoveImgOnUpdate", ((!$scope.doc_src || $scope.doc_src.length<=0) && $scope.editMode)?1:0);
                // formData.append("TGID", $scope.TGID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.getStudentTestResult();
                // $scope.clearForm();
                $scope.temp.stid = '';
                // $scope.temp.txtTestDate = '';
                $scope.temp.txtstartDate ='';
                $scope.temp.txtEndDate = '';
                $scope.temp.txtScore = '';
                $scope.temp.txtScale = '';
                // $scope.TGID = 0;

                $("#ddlLocation").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============






    /* ========== GET STUDENT TEST RESULT =========== */
    $scope.getStudentTestResult = function () {
        $scope.post.getStudentTestResult=[];
        if($scope.temp.ddlStudent > 0){
            $('#spinMainData').show();
            // $scope.post.getTestSection = [];
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getStudentTestResult','ddlStudent':$scope.temp.ddlStudent}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getStudentTestResult = data.data.success ? data.data.data : [];
                $('#spinMainData').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getStudentTestResult();
    /* ========== GET STUDENT TEST RESULT =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#ddlLocation").focus();

        $scope.temp.stid = id.STID;
        $scope.temp.ddlLocation = (id.LOCID).toString();

        if($scope.temp.ddlLocation > 0){
            $scope.getStudentByLoc();
            $timeout(()=>{
                $scope.temp.ddlStudent = (id.REGID).toString();
                if($scope.temp.ddlStudent > 0){
                    $scope.getTestByRegid();
                    $timeout(()=>{
                        $scope.temp.ddlTest = (id.TESTID).toString();
                        if($scope.temp.ddlTest > 0){
                            $scope.getTestSection();
                            $timeout(()=>{
                                $scope.temp.ddlTestSection = (id.TSECID).toString();
                            },700);
                        }
                    },700);
                }
            },700);
        }
        $scope.temp.txtTestDate = id.TESTDATE != '' ? new Date(id.TESTDATE) : '';
        $scope.temp.txtstartDate = id.STARTDATETIME != '' ? new Date(id.STARTDATETIME) : '';
        $scope.temp.txtEndDate = id.ENDDATETIME != '' ? new Date(id.ENDDATETIME) : '';
        $scope.temp.txtScore = Number(id.SCORE);
        $scope.temp.txtScale = Number(id.SCALE);
        $scope.temp.txtReviewed = id.REVIEWED.toString();
        $scope.temp.existingDocsUpload = id.DOCS;
        // $scope.TGID = id.TGID;
        
        /*########### IMG #############*/
        if(id.DOCS != ''){
            const name_edit = id.DOCS;
            const lastDot_edit = name_edit.lastIndexOf('.');
            const ext_edit = name_edit.substring(lastDot_edit + 1);

            // alert(name_edit+'....'+ext_edit);

            if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
                $scope.doc_src='images/upload_test_result/'+id.DOCS;
            }else{
                $scope.doc_src = $scope.FileTypeImage('',ext_edit);
            }
        }else{
            $scope.doc_src='';
        }

        $scope.editMode = true;
        $scope.index = $scope.post.getStudentTestResult.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getStudentByLoc = [];
        $scope.post.getTestByRegid=[];
        $scope.post.getTestSection = [];
        $scope.post.getStudentTestResult = [];

        $scope.doc_src = '';
        $scope.files = [];
        angular.element('#DocsUpload').val(null);
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STID': id.STID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentTestResult.indexOf(id);
		            $scope.post.getStudentTestResult.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */






/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */


    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getStudentByLoc = [];
        $scope.post.getTestByRegid=[];
        $scope.post.getTestSection = [];
        $('.spinLoc').show();
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
            if($scope.temp.ddlLocation > 0) $scope.getStudentByLoc();
            if($scope.temp.ddlLocation > 0) $scope.getStudentTestResult();
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    

    /* ========== GET STUDENT BY LOCATION =========== */
    $scope.getStudentByLoc = function () {
        $scope.post.getTestByRegid=[];
        $scope.post.getTestSection = [];
        $('.spinUser').show();
        $http({
            method: 'post',
            url: 'code/StudentApplication.php',
            data: $.param({ 'type': 'getStudentByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStudentByLoc = data.data.success ? data.data.data : [];
            $('.spinUser').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByLoc();
    /* ========== GET STUDENT BY LOCATION =========== */



    /*============ GET STUDENT TEST BY REGID =============*/ 
    // $scope.getTestByRegid = function () {
    //     $('.spinTest').show();
    //     $scope.post.getTestSection = [];
    //     $scope.post.getStudentAnswer = [];
    //     $http({
    //         method: 'post',
    //         url: url,
    //         data: $.param({ 'type': 'getTestByRegid','ddlStudent' : $scope.temp.ddlStudent}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getTestByRegid = data.data.success ? data.data.data : [];
    //         $('.spinTest').hide();
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // // $scope.getTestByRegid();
    $scope.getTestByRegid = function () {
        $('.spinTest').show();
        $scope.post.getTestSection = [];
        $http({
            method: 'post',
            url: 'code/Test_Master_code.php',
            data: $.param({ 'type': 'getTestMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTestByRegid = data.data.data;
            $('.spinTest').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getTestByRegid();
    /*============ GET STUDENT TEST BY REGID =============*/ 
    
    
    /*============ GET TGID =============*/ 
    $scope.getTGID = function(testid){
        // $scope.GROUPNO = $scope.post.getTestByRegid.filter(x=>x.TESTID == testid).map(x=>x.TGID).toString();
    }
    /*============ GET TGID =============*/ 
    
    


    /* ========== GET TEST SECTION BY TEST =========== */
    $scope.getTestSection = function () {
        $('.spinTestSec').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTestSection','ddlTest' : $scope.temp.ddlTest}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTestSection = data.data.success ? data.data.data : [];
            $('.spinTestSec').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
   }
   // $scope.getTestSection();
   /* ========== GET TEST SECTION BY TEST =========== */
/* ######################################################################################################################### */
/*                                            GET EXTRA DATA END                                                             */
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