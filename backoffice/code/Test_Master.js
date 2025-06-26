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
    $scope.PageSub = "TEST_MASTER";
    $scope.temp.selectQue = [];
    $scope.ChKDisabled = false;
    // $scope.temp.txtSEQNo='2';
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Test_Master_code.php';



    // =============== Open Categories Page =============
    $scope.OpenCategory = function (id) {
        window.open('Question_Categories.html?SEC_ID='+id.SECID,"");
    }
    // =============== Open Categories Page =============




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
                $scope.getTestMaster();
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





    /* ################################### TEST MATER START ################################### */ 

            // =========== SAVE TEST MASTER DATA ==============
            $scope.saveTestMaster = function(){
                $(".btn-saveTM").attr('disabled', 'disabled');
                $(".btn-saveTM").text('Saving...');
                $(".btn-updateTM").attr('disabled', 'disabled');
                $(".btn-updateTM").text('Updating...');
                $http({
                    method: 'POST',
                    url: url,
                    processData: false,
                    transformRequest: function (data) {
                        var formData = new FormData();
                        formData.append("type", 'saveTestMaster');
                        formData.append("testid", $scope.temp.testid);
                        formData.append("txtTestDesc", $scope.temp.txtTestDesc);
                        formData.append("txtTestYear", $scope.temp.txtTestYear);
                        formData.append("rdTestOpen", $scope.temp.rdTestOpen);
                        formData.append("rdMultipleAttempts", $scope.temp.rdMultipleAttempts);
                        formData.append("txtNumOfAttempts", $scope.temp.txtNumOfAttempts);
                        formData.append("txtTestRemark", $scope.temp.txtTestRemark);
                        formData.append("chkFreeTest", $scope.temp.chkFreeTest);
                        return formData;
                    },
                    data: $scope.temp,
                    headers: { 'Content-Type': undefined }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if (data.data.success) {
                        $scope.temp.testid = data.data.GET_TESTID;
                        $scope.messageSuccess(data.data.message);
                        
                        $scope.getTestMaster();

                        if($scope.editModeTM == false){
                            $scope.clearTestMaster();
                        }
                        // document.getElementById("txtSection").focus();
                    }
                    else {
                        $scope.messageFailure(data.data.message);
                        // console.log(data.data)
                    }
                    $('.btn-saveTM').removeAttr('disabled');
                    $(".btn-saveTM").text('SAVE');
                    $('.btn-updateTM').removeAttr('disabled');
                    $(".btn-updateTM").text('UPDATE');
                });
            }
            // =========== SAVE TEST MASTER DATA ==============



            /* ========== GET TEST MASTER =========== */
            $scope.getTestMaster = function () {
                $('.spinTestMaster').show();
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'type': 'getTestMaster'}),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if(data.data.success){
                        $scope.post.getTestMaster = data.data.data;
                    }else{
                        $scope.post.getTestMaster = [];
                    }
                    $('.spinTestMaster').hide();
                },
                function (data, status, headers, config) {
                    console.log('Failed');
                })
            }
            // $scope.getTestMaster(); --INIT
            /* ========== GET TEST MASTER =========== */





            /* ============ Edit TEST MASTER Button ============= */ 
            $scope.editTestMaster = function (id) {
                console.log(`TESTID: ${id.TESTID}`);
                // document.getElementById("TestSectionHead").focus();
                $scope.clearTestSection();
                
                window.location.hash = '#TestSectionHead';
                $scope.TESTID = 0;
                $scope.temp.testid=id.TESTID;
                $scope.temp.txtTestDesc=id.TESTDESC;
                $scope.temp.txtTestYear=Number(id.TESTYEAR);
                $scope.temp.rdTestOpen=(id.TESTOPEN).toString();
                $scope.temp.rdMultipleAttempts=(id.MULTIPLEATTEMPTS).toString();
                $scope.temp.txtNumOfAttempts= id.MULTIPLEATTEMPTS == 1 ? Number(id.NUMOFATTEMPTS) : '' ;
                $scope.temp.txtTestRemark=id.REMARKS;
                $scope.temp.chkFreeTest=id.FREE_TEST>0?'1':'0';
            
                $scope.editModeTM = true;
                $scope.index = $scope.post.getTestMaster.indexOf(id);

                if($scope.temp.testid > 0){$scope.getTestSection();}
            }
            /* ============ Edit TEST MASTER Button ============= */ 
            
            


            /* ============ Clear TEST MASTER Form =========== */ 
            $scope.clearTestMaster = function(){
                // document.getElementById("txtSection").focus();
                $scope.temp.testid=0;
                $scope.temp.txtTestDesc='',
                $scope.temp.txtTestYear='',
                $scope.temp.rdTestOpen='Yes',
                $scope.temp.rdMultipleAttempts='0';
                $scope.temp.txtNumOfAttempts=0;
                $scope.temp.txtTestRemark='',
                $scope.temp.chkFreeTest='0',
                $scope.editModeTM = false;

                 $scope.clearTestSection();
                 $scope.post.getTestSection=[];
                 

                // $scope.post.getTestMaster = [];
            }
            /* ============ Clear TEST MASTER Form =========== */ 




            /* ========== DELETE TEST MASTER =========== */
            $scope.deleteTestMaster = function (id) {
                var r = confirm("Are you sure want to delete this record!");
                if (r == true) {
                    $http({
                        method: 'post',
                        url: url,
                        data: $.param({ 'testid': id.TESTID, 'type': 'deleteTestMaster' }),
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                    }).
                    then(function (data, status, headers, config) {
                        // console.log(data.data)
                        if (data.data.success) {
                            var index = $scope.post.getTestMaster.indexOf(id);
                            $scope.post.getTestMaster.splice(index, 1);
                            // console.log(data.data.message)
                            $scope.post.getTestSection=[];
                            
                            $scope.messageSuccess(data.data.message);
                        } else {
                            $scope.messageFailure(data.data.message);
                        }
                    })
                }
            }
            /* ========== DELETE TEST MASTER =========== */
            


    /* ################################### TEST MATER END ################################### */ 
    
    









    /* ################################### SECTION GROUP START ################################### */ 
    $scope.TESTMASTER_NAME = '';
    $scope.TESTID = 0;
    $scope.chkTestSection =[];

    // ====== SECTION GROUP MODAL =======
    $scope.SectionGroupModal=function (id) {  
        $scope.clearScaledScore(); $scope.post.getSectionGroup=[]; $scope.post.getScaledScore=[]; 
        $scope.SS_GROUP = 0;
        $scope.SS_TESTID = 0;
        $scope.TESTSECTION_NAME = '';

        $scope.TESTMASTER_NAME = id.TESTDESC;
        $scope.TESTID = id.TESTID;
        
        if($scope.TESTID > 0){$scope.getTestSection(); $scope.getSectionGroup();}
    }
    // ====== SECTION GROUP MODAL =======



    /* ========== Add TEST SECTION IN GROUP =========== */
    $scope.addSectionInGroup = function(){
        $('.TSG_SPIN').show();
        $(".btn-save-TSG").attr('disabled', 'disabled');
        $(".btn-save-TSG").text('Create Group...');
        $(".btn-update-TSG").attr('disabled', 'disabled');
        $(".btn-update-TSG").text('Updating...');
        if($scope.TESTID > 0){
            // alert(id.TSECID +'----------'+ val);
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'addSectionInGroup');
                    formData.append("testid", $scope.TESTID);
                    formData.append("chkTestSection", $scope.chkTestSection);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.messageSuccess(data.data.message);
                    $scope.chkTestSection =[];
                    
                    $scope.getTestSection();
                    $scope.getSectionGroup();

                    // document.getElementById("txtSection").focus();
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
            });
            $('.TSG_SPIN').hide();
            $('.btn-save-TSG').removeAttr('disabled');
            $(".btn-save-TSG").text('Create Group');
            $('.btn-update-TSG').removeAttr('disabled');
            $(".btn-update-TSG").text('Update Group');
        }else{
            console.warn('TESTID Error.');
        }
    }
    /* ========== Add TEST SECTION IN GROUP =========== */
    
    
    
    
    /* ========== REMOVE TEST SECTION IN GROUP =========== */
    $scope.removeSectionGroup = function (id) {  
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'testid': id.TESTID, 'GROUPNO':id.GROUPNO, 'type': 'removeSectionGroup' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data)
            if (data.data.success) {

                if($scope.TESTID > 0){$scope.getTestSection();$scope.getSectionGroup();}

                $scope.messageSuccess(data.data.message);
            } else {
                $scope.messageFailure(data.data.message);
            }
        })
    }
    /* ========== REMOVE TEST SECTION IN GROUP =========== */
    
    
    
    
    /* ========== Get SECTION GROUP =========== */
    $scope.getSectionGroup = function () {
        // $('#removeSec').html("<div class='spinner-grow' role='status'><span class='sr-only'>Loading...</span></div>");
        $('#RemoveSEC').show();
        if($scope.TESTID > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getSectionGroup','TESTID':$scope.TESTID}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getSectionGroup = data.data.data;
                }else{
                    $scope.post.getSectionGroup = [];
                }
                $('#RemoveSEC').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        else{
            console.warn('TestID Not Found.');
        }
    }
    // $scope.getSectionGroup();
    /* ========== Get SECTION GROUP =========== */



    /* ################################### SECTION GROUP END ################################### */ 











    /* ################################### TEST SECTION START ################################### */ 

    // $scope.files=[];
    /*========= Pdf Preview =========*/ 
    $scope.UploadPdf = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.slide_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Pdf Preview =========*/ 

    $scope.post.getTestSection=[];
            // =========== SAVE TEST SECTION DATA ==============
            $scope.saveTestSection = function(){
                $(".btn-saveTS").attr('disabled', 'disabled');
                $(".btn-saveTS").text('Saving...');
                $(".btn-updateTS").attr('disabled', 'disabled');
                $(".btn-updateTS").text('Updating...');
                $scope.temp.txtContentPdf = $scope.files[0];
                $http({
                    method: 'POST',
                    url: url,
                    processData: false,
                    transformRequest: function (data) {
                        var formData = new FormData();
                        formData.append("type", 'saveTestSection');
                        formData.append("tsecid", $scope.temp.tsecid);
                        formData.append("testid", $scope.temp.testid);
                        formData.append("txtTestSection", $scope.temp.txtTestSection);
                        formData.append("rdDisplayAll", $scope.temp.rdDisplayAll);
                        formData.append("txtDisplayNOP", $scope.temp.txtDisplayNOP);
                        formData.append("txtSectionMaxQs", $scope.temp.txtSectionMaxQs);
                        formData.append("txtSectionMaxRowScore", $scope.temp.txtSectionMaxRowScore);
                        formData.append("txtSectionMaxScaledScore", $scope.temp.txtSectionMaxScaledScore);
                        formData.append("txtTestDurationMin", $scope.temp.txtTestDurationMin);
                        formData.append("txtSEQNo", $scope.temp.txtSEQNo);
                        formData.append("ddlSection_TS", $scope.temp.ddlSection_TS);
                        formData.append("rdShowCalc", $scope.temp.rdShowCalc);
                        formData.append("txtContentPdf", $scope.temp.txtContentPdf);
                        formData.append("existingContentPdf", $scope.temp.existingContentPdf);
                        formData.append("chkRemoveImgOnUpdate", ((!$scope.slide_src || $scope.slide_src.length<=0) && $scope.editModeTS)?1:0);
                        return formData;
                    },
                    data: $scope.temp,
                    headers: { 'Content-Type': undefined }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if (data.data.success) {
                        // $scope.temp.tsecid = data.data.GET_TSECID;
                        $scope.messageSuccess(data.data.message);
                        
                        $scope.getTestSection();

                        if($scope.editModeTM == false){
                            $scope.clearTestSection();
                        }
                        // document.getElementById("txtSection").focus();
                    }
                    else {
                        $scope.messageFailure(data.data.message);
                        // console.log(data.data)
                    }
                    $('.btn-saveTS').removeAttr('disabled');
                    $(".btn-saveTS").text('SAVE');
                    $('.btn-updateTS').removeAttr('disabled');
                    $(".btn-updateTS").text('UPDATE');
                });
            }
            // =========== SAVE TEST SECTION DATA ==============



            /* ========== GET TEST SECTION =========== */
            $scope.getTestSection = function () {
                // alert('TESTID : ' + $scope.TESTID);
                $('#AddSEC').show();
                $('.spinTestSection').show();
                if($scope.temp.testid > 0 || $scope.TESTID > 0){
                    $http({
                        method: 'post',
                        url: url,
                        data: $.param({ 'type': 'getTestSection','testid':$scope.temp.testid, 'TESTID':$scope.TESTID}),
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                    }).
                    then(function (data, status, headers, config) {
                        // console.log(data.data);
                        if(data.data.success){
                            if(data.data.data != undefined){
                                $scope.post.getTestSection = data.data.data;
                            }else{
                                $scope.post.getTestSection=[];
                            }

                        }else{
                            $scope.post.getTestSection = [];
                        }
                        $('#AddSEC').hide();
                        $('.spinTestSection').hide();
                    },
                    function (data, status, headers, config) {
                        console.log('Failed');
                    })
                }
                else{
                    console.warn('TestID Not Found.');
                }
            }
            // $scope.getTestSection();
            /* ========== GET TEST SECTION =========== */





            /* ============ Edit TEST SECTION Button ============= */ 
            $scope.editTestSection = function (id) {
                // document.getElementById("txtSection").focus();

                $scope.temp.tsecid=id.TSECID;
                $scope.temp.txtTestSection=id.TESTSECTION;
                $scope.temp.rdDisplayAll=(id.DISPLAYALL).toString();
                $scope.temp.txtDisplayNOP=Number(id.QUEPERPAGE);
                $scope.temp.txtSectionMaxQs=Number(id.MAXQUESTIONS);
                $scope.temp.txtSectionMaxRowScore=Number(id.MAXSCORE);
                $scope.temp.txtSectionMaxScaledScore=Number(id.MAXSCALE);
                $scope.temp.txtTestDurationMin=Number(id.DURATION);
                $scope.temp.txtSEQNo=Number(id.SEQNO);
                $scope.temp.ddlSection_TS= id.SECID > 0 ? (id.SECID).toString() : '';
                $scope.temp.rdShowCalc=(id.SHOWCALC).toString();

                /*########### PDF #############*/
                $scope.temp.existingContentPdf=id.PDFFILE;
                
                $scope.temp.existingContentImage = id.PDFFILE;
                if(id.PDFFILE != ''){
                    $scope.slide_src='images/question_master/'+id.PDFFILE;
                }else{
                    $scope.slide_src='images/question_master/default.png';
                }
            
                $scope.editModeTS = true;
                $scope.index = $scope.post.getTestSection.indexOf(id);
            }
            /* ============ Edit TEST SECTION Button ============= */ 
            
            


            /* ============ Clear TEST SECTION Form =========== */ 
            $scope.clearTestSection = function(){
                // document.getElementById("txtSection").focus();
                $scope.temp.tsecid=0;
                $scope.temp.txtTestSection='',
                $scope.temp.rdDisplayAll='1',
                $scope.temp.txtDisplayNOP='',
                $scope.temp.txtSectionMaxQs='',
                $scope.temp.txtSectionMaxRowScore='',
                $scope.temp.txtSectionMaxScaledScore='',
                $scope.temp.txtTestDurationMin='',
                $scope.temp.txtSEQNo='';
                $scope.temp.ddlSection_TS = '';
                $scope.temp.rdShowCalc='0';
                $scope.temp.txtContentPdf='';
                $scope.TSECID = 0;
                $scope.clearSlideImage();
                
                $scope.editModeTS = false;
                $scope.clearQuestionSection();
            }
            $scope.clearSlideImage=()=>{
                angular.element('#txtContentPdf').val(null);
                $scope.slide_src = '';
                $scope.files=[];
                $scope.temp.existingContentPdf='';
            };
            /* ============ Clear TEST SECTION Form =========== */ 




            /* ========== DELETE TEST SECTION =========== */
            $scope.deleteTestSection = function (id) {
                var r = confirm("Are you sure want to delete this record!");
                if (r == true) {
                    $http({
                        method: 'post',
                        url: url,
                        data: $.param({ 'tsecid': id.TSECID, 'type': 'deleteTestSection' }),
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                    }).
                    then(function (data, status, headers, config) {
                        // console.log(data.data)
                        if (data.data.success) {
                            var index = $scope.post.getTestSection.indexOf(id);
                            $scope.post.getTestSection.splice(index, 1);

                            $scope.messageSuccess(data.data.message);
                        } else {
                            $scope.messageFailure(data.data.message);
                        }
                    })
                }
            }
            /* ========== DELETE TEST SECTION =========== */
            
            

    /* ################################### TEST SECTION END ################################### */ 







    
    /* ################################### QUESTION SECTION START ################################### */ 
    $scope.TESTSECTION_NAME='';
    $scope.TSECID = 0;

    $scope.QuestionModal=function (id) {  
        $scope.clearQuestionSection();
        
        $scope.TESTSECTION_NAME = id.TESTSECTION;
        // $scope.temp.tsecid = id.TSECID;
        $scope.TSECID = id.TSECID;
        console.log(`TSECID: ${id.TSECID}`);



        if($scope.TSECID > 0){$scope.getQuestionSection();}
    }


    /* ========== GET SECTION MASTER =========== */
    $scope.getSectionMaster = function () {
        $('.spinMainCat').show();
        $http({
            method: 'post',
            url: 'code/Question_Master_code.php',
            data: $.param({ 'type': 'getSectionMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSectionMaster = data.data.success ? data.data.data : [];
            $('.spinMainCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSectionMaster(); --INIT
    /* ========== GET SECTION MASTER =========== */




    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function (secid) {
        $('.spinCategory').show();
        $scope.post.getCategories=[];
        $scope.post.getSubCategories=[];
        $scope.post.getTopic=[];
        $scope.post.getQuestions=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getCategories', 'secid' : secid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.data;
            $('.spinCategory').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories();
    /* ========== GET CATEGORIES =========== */




    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function (catid) {
        $('.spinSubCat').show();
        $scope.post.getSubCategories=[];
        $scope.post.getTopic=[];
        $scope.post.getQuestions=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'catid' : catid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubCategories = data.data.data;
            $('.spinSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */





    /* ========== GET TOPICS =========== */
    $scope.getTopic = function (subcatid) {
        $('.spinTopic').show();
        $scope.post.getTopic=[];
        $scope.post.getQuestions=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getTopic', 'subcatid' : subcatid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTopic = data.data.data;
            $('.spinTopic').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTopic();
    /* ========== GET TOPICS =========== */




    // $scope.temp.ddlSection
    // $scope.temp.ddlCategory
    // $scope.temp.ddlSubCategory
    /* ========== GET QUESTIONS =========== */
    $scope.getQuestions = function () {
        // $scope.post.getQuestions=[];
        if($scope.temp.ddlSection > 0){
            $('.spinQuestion').show();
            
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getQuestions', 
                                'ddlSection' : $scope.temp.ddlSection,
                                'ddlCategory' : $scope.temp.ddlCategory,
                                'ddlSubCategory' : $scope.temp.ddlSubCategory,
                                'ddlTopic' : $scope.temp.ddlTopic,
                                }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                if(data.data.success){
                    $scope.post.getQuestions = data.data.data;
                    $scope.QueOP = data.data.QueOP;

                    $scope.temp.selectQue = [];
                    // $scope.getQuestionSection();
                }
                else{
                    $scope.post.getQuestions=[];
                }
                // $scope.ChKDisabled = false;
                $('.spinQuestion').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getQuestions();
    /* ========== GET QUESTIONS =========== */





    // =========== SAVE TEST QUESTION DATA ==============
    $scope.saveQuestionSection = function(){
        $(".btn-saveQS").attr('disabled', 'disabled');
        $(".btn-saveQS").text('Saving...');
        $(".btn-updateQS").attr('disabled', 'disabled');
        $(".btn-updateQS").text('Updating...');

        // alert($scope.temp.testid + ' /// ' + $scope.temp.tsecid);
        if($scope.temp.testid > 0 && $scope.TSECID > 0){
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'saveQuestionSection');
                    formData.append("tsqid", $scope.temp.tsqid);
                    formData.append("testid", $scope.temp.testid);
                    formData.append("tsecid", $scope.TSECID);
                    formData.append("ddlQuestion", $scope.QUEID);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    // $scope.temp.tsecid = data.data.GET_TSECID;
                    $scope.messageSuccess(data.data.message);
                    
                    $scope.getQuestionSection();
                    $scope.getQuestions();
                    $scope.temp.selectQue = [];
                    // $scope.ChKDisabled = false;
    
                    // if($scope.editModeTQ == false){
                    //     $scope.clearQuestionSection();
                    // }
                    // document.getElementById("txtSection").focus();
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
            });
        }
        else{
            console.warn('testid & tsecid Not Found.');
        }
        $('.btn-saveQS').removeAttr('disabled');
        $(".btn-saveQS").text('SAVE');
        $('.btn-updateQS').removeAttr('disabled');
        $(".btn-updateQS").text('UPDATE');
    }
    // =========== SAVE TEST QUESTION DATA ==============
    
    
    

    /* ========== GET TEST QUESTION =========== */
    $scope.getQuestionSection = function () {
        // $scope.post.QuestionSection = [];
        $('.spinSelQue').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getQuestionSection',
            'testid':$scope.temp.testid,
            'tsecid':$scope.TSECID,
            'ddlSection' : $scope.temp.ddlSection,
            'ddlCategory' : $scope.temp.ddlCategory,
            'ddlSubCategory' : $scope.temp.ddlSubCategory,
            'ddlTopic' : $scope.temp.ddlTopic}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.QuestionSection = data.data.data;
            }else{
                $scope.post.QuestionSection = [];
            }
            $('.spinSelQue').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getQuestionSection();
    /* ========== GET TEST QUESTION =========== */
    
    
    
    
    /* ========== SAVE SELECTED QUESTION =========== */
    
    $scope.SaveSelectedQuestion = (id,index,val)=>{
        // $scope.ChKDisabled = true;
        $scope.QUEID=id.QUEID;
        if(val){

            $scope.saveQuestionSection();
        }
        
        
        // else{
        //     $scope.deleteQuestionSection(id);
        // }
        // console.log(`${index} / ${val}`);
        // console.info(id);
    }
    /* ========== SAVE SELECTED QUESTION =========== */




    /* ============ Edit TEST QUESTION Button ============= */ 
    $scope.editQuestionSection = function (id) {
        // document.getElementById("txtSection").focus();
        console.log(` ${id.TSQID}  /`);

        $scope.temp.tsqid=id.TSQID;
        $scope.temp.ddlSection=(id.SECID).toString();
        
        if(id.SECID > 0){$scope.getCategories(id.SECID);}
        $timeout(function () { 
            $scope.temp.ddlCategory=(id.CATID).toString();

            if(id.CATID > 0){$scope.getSubCategories(id.CATID);}
            $timeout(function () {
                $scope.temp.ddlSubCategory=(id.SUBCATID).toString();
            },700);
            
            if(id.SUBCATID > 0){$scope.getTopic(id.SUBCATID);}
            $timeout(function () {
                $scope.temp.ddlTopic=(id.TOPICID).toString();
    
                $scope.getQuestions();
                $scope.temp.ddlQuestion=(id.QUEID).toString();
            },900);

         },500);
        
    
        $scope.editModeTQ = true;
        $scope.index = $scope.post.QuestionSection.indexOf(id);
    }
    /* ============ Edit TEST SECTION Button ============= */ 
    
    


    /* ============ Clear TEST QUESTION Form =========== */ 
    $scope.clearQuestionSection = function(){
        // document.getElementById("txtSection").focus();
        $scope.temp.tsqid=0;
        $scope.temp.ddlSection='';
        $scope.temp.ddlCategory='';
        $scope.temp.ddlSubCategory='';
        $scope.temp.ddlTopic='';
        $scope.temp.ddlQuestion='';
        $scope.QUEID = 0;
        $scope.post.getQuestions=[];
        $scope.post.QuestionSection=[];
        
        $scope.editModeTQ = false;
    }
    /* ============ Clear TEST QUESTION Form =========== */ 




    /* ========== DELETE TEST QUESTION =========== */
    $scope.deleteQuestionSection = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tsqid': id.TSQID, 'type': 'deleteQuestionSection' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.QuestionSection.indexOf(id);
                    $scope.post.QuestionSection.splice(index, 1);

                    $scope.getQuestionSection();
                    $scope.getQuestions();

                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    /* ========== DELETE TEST QUESTION =========== */


    /* ################################### QUESTION SECTION END ################################### */ 
    
    
    
    
    
    
    
    
    
    /* ################################### SCALED SCORE START ################################### */ 
    $scope.SS_GROUP = 0;
    $scope.SS_TESTID = 0;
    $scope.TESTSECTION_NAME = '';
    // =========== OPEN SCALED SCORE MODAL ==============
    $scope.ScaledScoreModal=function (id) {  
        $scope.SS_GROUP = 0;
        $scope.SS_TESTID = 0;
        $scope.TESTSECTION_NAME = '';

        $scope.TESTSECTION_NAME = id.TESTSECTION;
        $scope.SS_GROUP = id.GROUPNO;
        // $scope.temp.tsecid = id.TSECID;
        $scope.SS_TESTID = id.TESTID;
        
        if($scope.SS_GROUP > 0 && $scope.SS_TESTID>0){$scope.getScaledScore();}
    }
    // =========== OPEN SCALED SCORE MODAL ==============
    

    // ============ GET EXCEL FILE NAME ===========
    $scope.files = [];
// $scope.Img_src='';
    $scope.GetFileName = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.Img_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        
        reader.readAsDataURL(element.files[0]);
    }
    // ============ GET EXCEL FILE NAME ===========
    




    // =========== SAVE SCALED SCORE DATA ==============
    $scope.saveScaledScore = function(){
        if($scope.temp.txtScore > 0 || $scope.temp.txtScale>0){
            $scope.temp.txtUploadExcel ='';
            var fileElement = angular.element('#txtUploadExcel');
            angular.element(fileElement).val(null);
        }
        $scope.temp.txtUploadExcel = $scope.files[0];
        // alert($scope.temp.txtUploadExcel);
        $(".btn-saveSS").attr('disabled', 'disabled');
        $(".btn-saveSS").text('Saving...');
        $(".btn-updateSS").attr('disabled', 'disabled');
        $(".btn-updateSS").text('Updating...');

        // alert($scope.temp.testid + ' /// ' + $scope.temp.tsecid);
        if($scope.SS_GROUP > 0 && $scope.SS_TESTID > 0){
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'saveScaledScore');
                    formData.append("ssmid", $scope.temp.ssmid);
                    formData.append("SS_GROUP", $scope.SS_GROUP);
                    formData.append("SS_TESTID", $scope.SS_TESTID);
                    formData.append("txtScore", $scope.temp.txtScore);
                    formData.append("txtScale", $scope.temp.txtScale);
                    formData.append("txtUploadExcel", $scope.temp.txtUploadExcel);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    // $scope.temp.tsecid = data.data.GET_TSECID;
                    $scope.messageSuccess(data.data.message);
                    
                    $scope.getScaledScore();
                    $scope.temp.ssmid=0;
                    $scope.temp.txtScore='';
                    $scope.temp.txtScale='';
                    $scope.temp.txtUploadExcel ='';
                    var fileElement = angular.element('#txtUploadExcel');
                    angular.element(fileElement).val(null);
                    // $scope.SS_GROUP = 0;
                    $scope.clearScaledScore();
                    // document.getElementById("txtSection").focus();
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
            });
        }
        else{
            console.warn('testid & tsecid Not Found.');
        }
        $('.btn-saveSS').removeAttr('disabled');
        $(".btn-saveSS").text('SAVE');
        $('.btn-updateSS').removeAttr('disabled');
        $(".btn-updateSS").text('UPDATE');
    }
    // =========== SAVE SCALED SCORE DATA ==============





    /* ========== GET SCALED SCORED =========== */
    $scope.getScaledScore = function () {
        $('.spinScaleScore').show();
        if($scope.SS_GROUP > 0 && $scope.SS_TESTID > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type' : 'getScaledScore',
                                'SS_GROUP' : $scope.SS_GROUP,
                                'SS_TESTID' : $scope.SS_TESTID}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                if(data.data.success){
                    $scope.post.getScaledScore = data.data.data;
                }else{
                    $scope.post.getScaledScore = [];
                }
                $('.spinScaleScore').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }else{
            console.warn('GroupNo Or Testid Not Found.');
        }

    }
    // $scope.getScaledScore();
    /* ========== GET SCALED SCORED =========== */






    /* ============ Edit SCALED SCORE Button ============= */ 
    $scope.editScaledScore= function (id) {
        // document.getElementById("txtSection").focus();
        var fileElement = angular.element('#txtUploadExcel');
        angular.element(fileElement).val(null);

        $scope.temp.ssmid=id.SSMID;
        $scope.temp.txtScore=Number(id.SCORE);
        $scope.temp.txtScale=Number(id.SCALE);
        
        $scope.editModeSS = true;
        $scope.index = $scope.post.getScaledScore.indexOf(id);
    }
    /* ============ Edit SCALED SCORE Button ============= */ 
    
    


    /* ============ Clear SCALED SCORE Form =========== */ 
    $scope.clearScaledScore = function(){
        // document.getElementById("txtSection").focus();
        $scope.temp.ssmid=0;
        $scope.temp.txtScore='';
        $scope.temp.txtScale='';
        $scope.temp.txtUploadExcel ='';
        // $scope.SS_GROUP = 0;
        var fileElement = angular.element('#txtUploadExcel');
        angular.element(fileElement).val(null);
        
        $scope.editModeSS = false;
    }
    /* ============ Clear SCALED SCORE Form =========== */ 




    /* ========== DELETE SCALED SCORE =========== */
    $scope.deleteScaledScore = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ssmid': id.SSMID, 'type': 'deleteScaledScore' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getScaledScore.indexOf(id);
                    $scope.post.getScaledScore.splice(index, 1);

                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
    /* ========== DELETE SCALED SCORE =========== */

    /* ################################### SCALED SCORE END ################################### */ 



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