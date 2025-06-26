$postModule = angular.module("myApp", [ "ngSanitize","textAngular"]);
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$window,taOptions,$compile) {
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
        ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
        ['wordcount', 'charcount']
    ];
    

    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.serial = 1;
    $scope.Page = 'ASSESSMENT';
    $scope.date = new Date();
    $scope.temp.txtFromDate=new Date();
    $scope.temp.txtToDate=new Date();
    $scope.SectionsData=[];
    $scope.CheckTestStart = false;
    $scope.correctAnswer = [];
    $scope.TEST_YES_NO = false;
    $scope.PerPageQue = 0;
    var interval;
    $scope.BREAK = false;
    $scope.REGID_INDEX=0;

    // ESSAY
    $scope.txtEssay = '';
    $scope.STESSID = 0;
    $scope.completeESSAY = false;
    // ESSAY

    // FLAGS
    $scope.flags = [];
    $scope.flagsList = [];
    // FLAGS


    $scope.unsaved = true;

    var url = 'code/Free-Assessment.php';

    /*========== Alert Before Submit ==========*/ 
    window.onload = function() {
        window.addEventListener("beforeunload", function (e) {
            if ($scope.unsaved) {
                return undefined;
            }
    
            var confirmationMessage = 'Do you really want to leave our Test?.';
            
            (e || window.event).returnValue = confirmationMessage; //Gecko + IE
            return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        });
    };
    /*========== Alert Before Submit ==========*/ 
    
    
    /*========== CREATE CANVAS ==========*/
    $scope.SketchpadArray = [];
    $scope.bindCanvas=function(index){
        $timeout(function() {
            // GET CANVAS LOCATION
            var column = document.querySelector('#CANVA'+index)
            if(column) column.innerHTML='';

            // CREATE CANVAS
            var canvas = document.createElement('canvas');
            var id_name = `sketchpad${index}`
            canvas.setAttribute('id', id_name);
            canvas.setAttribute('style', 'box-shadow:#090909 0px 0px 5px 0px inset');
            
            column.appendChild(canvas);
            let canvasID = eval('var ' + 'sketchpad' + index);
            if (canvasID) {
                canvasID.destroy()
            }
            
            canvasID = new Sketchpad({
                element: '#'+id_name,
                // width: 'auto',
                // height: 'auto',
                color:'#000',
                penSize: 3
            });
            var ids = 'sketchpad' + index;
            $scope.SketchpadArray.push({[ids] : canvasID});

            canvas.setAttribute('height', 'auto');
            canvas.setAttribute('width', 'auto');
        },1000);
    }


        /*######### UNDO,REDO,CLEAR */
        $scope.canvasUtility=function (action,index,colorVal) {
            // console.log($scope.SketchpadArray[index]['sketchpad'+index]);
            if(action=='undo'){
                $scope.SketchpadArray[index]['sketchpad'+index].undo();
            }else if(action=='redo'){
                $scope.SketchpadArray[index]['sketchpad'+index].redo();
            }else if(action=='clear'){
                $scope.SketchpadArray[index]['sketchpad'+index].clear();
            }else if(action=='color'){
                $scope.SketchpadArray[index]['sketchpad'+index].color = colorVal;
            }
        }
        /*######### UNDO,REDO,CLEAR */

    /*========== CREATE CANVAS ==========*/

    
    
    /*========== SET FLAG LIST ==========*/ 
    $scope.setFlagList = function (val,id,index) {
        // console.log(val,id,index);
        if (val) {
            $scope.flagsList.push({'id':id,'que':`Q${index+1}`});
        } else {
            // var idx = $scope.flagsList.indexOf({'id':id,'que':`Q${index+1}`});
            var idx = $scope.flagsList.map(e => e.id).indexOf(id);
            if (idx > -1) {
                $scope.flagsList.splice(idx, 1);
            }
        }
    }
    /*========== SET FLAG LIST ==========*/ 



    /*========== FOCUS FLAG QUESTION ==========*/ 
    $scope.focusQuestion = (id)=>{
        console.log(id)
        var scrollPos =  $('#'+id.id).offset().top;
        $('html, body').animate({scrollTop:(scrollPos-100)},function() {
            $timeout(()=>{
                $('#'+id.id).parent().closest('div').addClass('animate__animated animate__flash');
                $timeout(()=>{$('#'+id.id).parent().closest('div').removeClass('animate__animated animate__flash animate__delay-2s')},3000);
            },1000);
        });
        
        // $('#selectCon').focus();
    //    $("html, body").animate({ scrollTop: $(id.id).offset().top},2000);
    }
    /*========== FOCUS FLAG QUESTION ==========*/ 



    /*========== Pagination Count ==========*/ 
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * $scope.PerPageQue - ($scope.PerPageQue-1);
    }
    /*========== Pagination Count ==========*/ 
    


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */
    
    
    
    



    // GET COMMON DATA
    $scope.init = function () {
        // alert(sessionStorage.getItem("REGID_index"))
        $scope.REGID_INDEX = sessionStorage.getItem("REGID_index");
        if(!$scope.REGID_INDEX || $scope.REGID_INDEX==0){
            window.open('index.html','_self');
        }

        
        $scope.getTestByStudentProducts($scope.REGID_INDEX);
        $http({
            method: 'post',
            url: 'code/Common.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getDashBoardAnnouncement');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            
            if (data.data.success) {
                $scope.ANNSHOW = true;
                $scope.post.Announcement = data.data.data;
                $scope.ANN_DATE = data.data.data[0]['ANDATE'];
                $scope.ANN_TILLDATE = data.data.data[0]['DB_ANNOUNCE_TILLDATE'];
                $scope.ANN = data.data.data[0]['ANNOUNCEMENT'];
                $scope.ANN_LOC = data.data.data[0]['LOCATION'];
            }else{
                $scope.ANNSHOW = false;
                $scope.post.Announcement =[];
            }
            $scope.CATEGORIES = data.data.CATEGORIES;
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    

     /*============ Get Test By Student Products =============*/ 
     $scope.AllSectionId = [];
     $scope.getTestByStudentProducts = function (REGID) {
         $('.lds-hourglass').show();
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getTestByStudentProducts','REGID_INDEX':REGID}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
         }).
         then(function (data, status, headers, config) {
             // console.info(data.data);
             if(data.data.success){
                 $scope.post.getTestByStudentProducts=data.data.data;
                 $scope.AllSectionId = data.data.AllTSECID;
 
                 // CONVERT OBJECT TO ARRAY
                 if($scope.post.getTestByStudentProducts.length>0){
                     $timeout(function () { 
                         for(t=0; t<$scope.chkSectionForTest.length; t++){
                             // console.error(t);
                             $scope.chkSectionForTest[t] = new Array(Object.keys($scope.chkSectionForTest[t]).length).fill("0");
                         }
                     },200);
                 }
             }else{
                 alert(data.data.message);
             }
             $('.lds-hourglass').hide();
         },
         function (data, status, headers, config) {
             console.log('Failed');
         })
 
    }
 //    $scope.getTestByStudentProducts(); --INIT
    /*============ Get Test By Student Products =============*/ 
 
 
 
 
 
 
 
    /* ========== CHK/UNCHK ALL =========== */
     $scope.chkSectionSelect = false;
     $scope.chkSectionForTest = [];
     $scope.SectionIDForTest = [];
     $scope.id = 0;
     $scope.GROUP_CHECKED = false;
     $scope.checkSelectAll = function(parent_index,index,testid,GROUPNO,TSECID,infoData){
         
         $scope.SectionIDForTest = [];
 
         $scope.TOTAL_GROUPDATA = 0;
         if(Number(GROUPNO) > 0){
             $scope.TOTAL_GROUPDATA = infoData.filter(x => x.GROUPNO == GROUPNO);
             $scope.GROUPDATA = infoData.filter(x => x.GROUPNO == GROUPNO && x.TSECID != TSECID);
         }else{
             $scope.TOTAL_GROUPDATA = 0;
         }
 
 
         $scope.SectionIDForTest = $scope.chkSectionForTest[parent_index.toString()];
         if($scope.TOTAL_GROUPDATA.length>1 && !$scope.GROUP_CHECKED){
             $scope.GROUP_CHECKED =true;
             var gindex = infoData.findIndex(x => x.TSECID == $scope.GROUPDATA[0]['TSECID']);
             $scope.SectionIDForTest[gindex] = $scope.GROUPDATA[0]['TSECID'].toString();
             
         }else{
             if($scope.TOTAL_GROUPDATA.length>1){
                 $scope.GROUP_CHECKED = false;
                 var gindex = infoData.findIndex(x => x.TSECID == $scope.GROUPDATA[0]['TSECID']);
                 $scope.SectionIDForTest[gindex] = '0';
             }
         }
 
         if($scope.id >= 0 && $scope.id != testid){
             if($scope.PI != undefined){
                 
                 $scope.chkSectionForTest[$scope.PI] = new Array($scope.chkSectionForTest[$scope.PI].length).fill("0");
                 $('#nextbtn_'+Number($scope.PI)).attr('disabled','disabled');
 
                 if($scope.TOTAL_GROUPDATA.length>1){
                     var gindex = infoData.findIndex(x => x.TSECID == $scope.GROUPDATA[0]['TSECID']);
                     $scope.SectionIDForTest[gindex] = $scope.GROUPDATA[0]['TSECID'].toString();
                     $scope.GROUP_CHECKED = true;
                 }  
             }
             
             $scope.id = testid;
             $scope.PI = parent_index.toString();
             
         }
         else{
             if($scope.id != testid){
                 $scope.chkSectionForTest[$scope.PI] = new Array($scope.chkSectionForTest[$scope.PI].length).fill("0");
                 $scope.id = 0;
             }
 
         }
 
 
             // ===========================================================
             // =============== NEXT BUTTON ENABLE/DISABLED ===============
             // ===========================================================
             if($scope.SectionIDForTest.length>0){
                 $scope.chkSectionSelect = $scope.SectionIDForTest.reduce((a, b) => a + b, 0) > 0 ? true : false;
                 if($scope.SectionIDForTest.reduce((a, b) => a + b, 0) > 0){
                     $('#nextbtn_'+parent_index).removeAttr('disabled');
                 }
                 else{
                     $('#nextbtn_'+parent_index).attr('disabled','disabled');
                 }
                 // console.info($scope.SectionIDForTest.reduce((a, b) => a + b, 0));
             }
             // ===========================================================
             // =============== NEXT BUTTON ENABLE/DISABLED ===============
             // ===========================================================
 
     }
     /* ========== CHK/UNCHK ALL =========== */
 
 
    
 
 
 
    
    /* ========== CLEAR FORM =========== */
    $scope.clear=function(){
        $scope.temp={};
        $scope.temp.txtFromDate=new Date();
     }
     /* ========== CLEAR FORM =========== */
     
 
 
 
 
     
 
     /* ========== OPEN REVIEW PAGE =========== */
     $scope.openReviewPage=function(testid,tsecid,groupno,ess,ind,FOR){
         sessionStorage.setItem("asstest", testid);
         sessionStorage.setItem("asstsec", tsecid);
         sessionStorage.setItem("assin", ind);
         sessionStorage.setItem("assgroupno", groupno);
         sessionStorage.setItem("assess", ess);
         sessionStorage.setItem("assfor", FOR);
         $window.open('Free-Assessment-Review.html','_self');
     }
     /* ========== OPEN REVIEW PAGE =========== */
 
 
 
 
 
 
 
 
     /* ========== TEST START MODAL =========== */
     $scope.OpenTestStartModal=function(FOR,id){
         $scope.TEST_YES_NO = false;
         $scope.TEST_FOR = FOR;
         if(FOR === 'ESSAY'){
             if(id.ATTEMPT<=0){
                 if(id != ''){
                     $scope.EssEssid = id.ESSID;
                     $scope.EssTestid = id.TESTID;
                     $scope.EssTsecid = id.TSECID;
                     $scope.EssTopic = id.ESSTOPIC;
                     $scope.EssLimitOn = id.LIMITON;
                     $scope.EssLimit = id.LIMIT;
                     $scope.EssTimeAllowed = id.TIMEALLOWED;
                     $scope.EssTestSection = id.TESTSECTION;
                     $scope.unsaved = false;
                 }
                 else{
                     $scope.unsaved = true;
                     $scope.EssEssid = '';
                     $scope.EssTestid = '';
                     $scope.EssTsecid = '';
                     $scope.EssTopic = '';
                     $scope.EssLimitOn = '';
                     $scope.EssLimit = '';
                     $scope.EssTimeAllowed = '';    
                     $scope.EssTestSection = '';
                 }
             }
             else{
                 alert('Essay Already Attempt.');
                 $timeout(()=>{
                     $('#TestStartModal').trigger({ type: "click" });
                 },1000);
             }
         }
     }
     /* ========== TEST START MODAL =========== */
     
     
     
     
 
     
 
 
     /* ==================== START TEST ==================== */
     $scope.CURRENT_SECTION = 1;
     $scope.Tsecid = 0;
     $scope.tsecid_array=[];
     $scope.testid_array=[];
     $scope.testid_tsecid_array=[];
 
     // ++++++++ START TEST ALERT ++++++++
     $scope.StartTestAlert = function(){
         $(".startbtn").attr('disabled', 'disabled');
         $(".confirmTestStart").slideDown();
     }
     // ++++++++ START TEST ALERT ++++++++
     
 
     // ++++++++ START TEST ++++++++
     $scope.StartTest = function () {
         $scope.STESSID = 0;
         $scope.completeESSAY = false;
         $('.btn-st').removeAttr('disabled');
         $('.FinalTestStartBtn').hide();
         $('.FinalTestStartmsg').text('');
         $scope.testid_array=[];
         $scope.tsecid_array=[];
         $scope.testid_tsecid_array=[];
         $scope.RUBRICS = [];
         if($scope.TEST_YES_NO){
             if($scope.TEST_FOR == 'SECTION')
             {
                 // ============================= FOR SECTION ===============================
                 $http({
                     method: 'post',
                     url: url,
                     data: $.param({ 'type': 'getTestSections', 'chkSectionForTest' : $scope.SectionIDForTest}),
                     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                 }).
                 then(function (data, status, headers, config) {
                     // console.log(data.data);
                     if(data.data.success){
                         $scope.post.getTestSections=data.data.data;
                         $scope.tsecid_array = data.data.tsecid_array;
                         $scope.testid_array = data.data.testid_array;
                         $scope.testid_tsecid_array = data.data.TEST_TSEC_ID;
     
                         if($scope.post.getTestSections.length > 0){
                             $scope.SectionsData = data.data.data[$scope.CURRENT_SECTION-1];
                             $scope.Tsecid = $scope.SectionsData['TSECID'];
                             $scope.Testid = $scope.SectionsData['TESTID'];
                             $scope.PerPageQue = $scope.SectionsData['QUEPERPAGE'];
                             $scope.DisplayAll = $scope.SectionsData['DISPLAYALL'];
                             $scope.SectionName = $scope.SectionsData['TESTSECTION'];
                             $scope.SectionMaxQue = $scope.SectionsData['MAXQUESTIONS'];
                             $scope.SectionMaxScore = $scope.SectionsData['MAXSCORE'];
                             $scope.SectionMaxScale = $scope.SectionsData['MAXSCALE'];
     
                             $SN = $scope.SectionName.charAt(0).toUpperCase() + $scope.SectionName.slice(1);
     
                             if($SN.includes("Break")){
                                 $scope.BREAK = true;
                             }else{
                                 // console.warn('no-break');
                                 $scope.BREAK = false;
                             }
     
                             if($scope.DisplayAll == 0){
                                 // console.log($scope.SectionsData);
                             }
                         }
                         else{
                             $scope.SectionsData=[];
                         }
                     }
                     $(".startbtn").removeAttr('disabled');
                 },
                 function (data, status, headers, config) {
                     console.log('Failed');
                 })
             }
             else if($scope.TEST_FOR == 'ESSAY'){
                 
                 // ============================= FOR ESSAY ===============================
                 $http({
                     method: 'post',
                     url: url,
                     data: $.param({ 'type': 'saveEssay', 
                                    'REGID_INDEX':$scope.REGID_INDEX,
                                     'EssEssid' : $scope.EssEssid,
                                     'EssTestid' : $scope.EssTestid,
                                     'EssTsecid' : $scope.EssTsecid
                                 }),
                     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                 }).
                 then(function (data, status, headers, config) {
                     // console.log(data.data);
                     if(data.data.success){
                         $scope.STESSID = data.data.STESSID;
                         $scope.SectionName = $scope.EssTestSection;
                         $scope.RUBRICS = (data.data.RUBRICS && data.data.RUBRICS.length>0) ? data.data.RUBRICS : [];
 
                         //====== Duration =====
                         var TotalMinutes = 60 * $scope.EssTimeAllowed,
                         display = document.querySelector('#timer');
                         $scope.startTimer(TotalMinutes, display);
                         //====== Duration =====
                     }
                     else{
                         $scope.STESSID = 0;
                         console.info(data.data.message);
                     }
                 },
                 function (data, status, headers, config) {
                     console.log('Failed');
                 })
             }
             else{
                 console.log('Error : Test For Missing.');
             }
             $('#TestStartModal').delay(1000).modal('hide');
             $(".confirmTestStart").slideUp();
         }
         else{
             $(".confirmTestStart").slideUp();
             $(".startbtn").removeAttr('disabled');
         }
     }
     // ++++++++ START TEST ++++++++
 
     
     // ++++++++ CLEAR TOTAL SECTION ++++++++
     $scope.clearTotalSection=function () {
         $scope.post.getTestSections=[];
         $scope.SectionsData=[];
         $scope.Tsecid=0;
         $scope.CURRENT_SECTION = 1;
         $(".startbtn").removeAttr('disabled');
     }
     // ++++++++ CLEAR TOTAL SECTION ++++++++
 
     /* ==================== START TEST ==================== */
     
 
 
 
 
 
 
     /* =============== SAVE TEST DATA ============== */
     $scope.CURRENT_ATTEMPT = 0;
     $scope.GET_STID = [];
     $scope.SaveTest = function(){
         $scope.CURRENT_ATTEMPT = 0;
         $('.btn-st').attr('disabled','disabled');
         $('.FinalTestStartBtn').show();
         // console.info($scope.testid_tsecid_array);
         $http({
             method: 'POST',
             url: url,
             processData: false,
             transformRequest: function (data) {
                 var formData = new FormData();
                 formData.append("type", 'SaveTest');
                 formData.append("REGID_INDEX", $scope.REGID_INDEX);
                 formData.append("testid_array", $scope.testid_array);
                 formData.append("Attempt", $scope.Attempt);
                 formData.append("tsecid_array", $scope.tsecid_array);
                 formData.append("testid_tsecid_array", JSON.stringify($scope.testid_tsecid_array));
                 return formData;
             },
             data: $scope.temp,
             headers: { 'Content-Type': undefined }
         }).
         then(function (data, status, headers, config) {
             console.log(data.data);
             if (data.data.success) { 
                 $scope.CURRENT_ATTEMPT = data.data.CURRENT_ATTEMPT;
                 $scope.GET_STID = data.data.GET_STID;
                 $scope.FinalTestStart($scope.SectionsData['DURATION']);
                 $scope.messageSuccess(data.data.message);
             }
             else {
                 // alert(data.data.message);
                 $scope.messageFailure(data.data.message);
                 $('.FinalTestStartBtn').hide();
                 $('.FinalTestStartmsg').text(data.data.message);
 
                 $('#TestStartModal').delay(1000).modal('hide');
                 $(".confirmTestStart").slideUp();
             }
         });
     }
     /* =============== SAVE TEST DATA ============== */
 
 
 
 
 
 
 
     /*============ UPDATE ESSAY =============*/
     $scope.updateEssay = function(){
         $('.spinEssResult').show();
         $('.btn-updEssay').attr('disabled','disabled');
         $('.btn-updEssay').text('Saving...');
         var WC = Number(document.getElementById('toolbarWC').textContent.replace('Words: ',''));
         var CC = Number(document.getElementById('toolbarCC').textContent.replace('Characters: ',''));
 
         if($scope.EssLimitOn == 'WORDS'){
             if(WC > $scope.EssLimit){
                 console.info('Words limit only "'+ $scope.EssLimit + '"');
             }
         }else{
             if(CC > $scope.EssLimit){
                 console.info('Characters limit only "'+ $scope.EssLimit + '"');
             }
         }
         // console.log(taOptions);
 
         if($scope.STESSID > 0){
             $http({
                 method: 'POST',
                 url: url,
                 processData: false,
                 transformRequest: function (data) {
                     var formData = new FormData();
                     formData.append("type", 'UpdateEssay');
                     formData.append("REGID_INDEX", $scope.REGID_INDEX);
                     formData.append("STESSID", $scope.STESSID);
                     formData.append("txtEssay", $scope.txtEssay);
                     formData.append("total_words", WC);
                     formData.append("total_chars", CC);
                     return formData;
                 },
                 data: $scope.temp,
                 headers: { 'Content-Type': undefined }
             }).
             then(function (data, status, headers, config) {
                 // console.log(data.data);
                 if (data.data.success) { 
 
                     $interval.cancel(interval);
                     $scope.completeESSAY = true;
                     $scope.getEssayResult();
                     $scope.unsaved = true;
 
                     $scope.messageSuccess(data.data.message);
                     console.info(data.data.message);
                     $('.btn-updEssay').text('Save');
                     $('.btn-updEssay').removeAttr('disabled');
                 }
                 else {
                     $scope.messageFailure(data.data.message);
                     console.info(data.data.message);
                     // console.log(data.data)
                 }
             });
         }
         else{
             console.info('STESSID Missing.');
         }
     }
     /*============ UPDATE ESSAY =============*/
 
 
 
 
 
 
 
     /*============ UPDATE ANSWER =============*/
     $scope.UpdateAnswer = function(ans,queid,correct_ans,STID,sketchpadID){
        if($scope.Testid > 0 && $scope.Tsecid > 0 && queid > 0 && ans != ''){
            const canvas = document.getElementById(sketchpadID)
            const img = canvas.toDataURL('image/png');
            $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'UpdateAnswer');
            formData.append("REGID_INDEX", $scope.REGID_INDEX);
            formData.append("STID", STID);
            formData.append("Testid", $scope.Testid);
            formData.append("Tsecid", $scope.Tsecid);
            formData.append("queid", queid);
            formData.append("ans", ans);
            formData.append("correct_ans", correct_ans);
            formData.append("CanvasImg", img);
            return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) { 
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
            });
        }
        else{
            console.info('Somthing Missing.');
        }
     }
     /*============ UPDATE ANSWER =============*/
 
 
 
 
 
 
     
     /* ========== NEXT PREVIOUS SECTION (CHANGE SECTION) =========== */
     $scope.complete = false;
     $scope.changeSection=function(){
        $scope.flagsList=[];
        $scope.SketchpadArray=[];
         $scope.BREAK = false;
         if($scope.CURRENT_SECTION > $scope.post.getTestSections.length){
             $scope.unsaved = true;
             $scope.complete = true;
             $scope.SectionsData = [];
             $scope.post.getSectionQuestions=[];
             $scope.NextPrevious = 1;
             $scope.TotalChunks = 0;
             $scope.chunkData=[];
             $interval.cancel(interval);
             $(".confirmNextSection").slideUp();
             $(".next-sec-submit-btn").removeAttr('disabled');
 
             $scope.getResultBySections();
         }else{
             $scope.SectionsData = $scope.post.getTestSections[$scope.CURRENT_SECTION-1];
             $scope.Tsecid = $scope.SectionsData['TSECID'];
             $scope.Testid = $scope.SectionsData['TESTID'];
             $scope.PerPageQue = $scope.SectionsData['QUEPERPAGE'];
             $scope.DisplayAll = $scope.SectionsData['DISPLAYALL'];
             $scope.SectionName = $scope.SectionsData['TESTSECTION'];
             $scope.SectionMaxQue = $scope.SectionsData['MAXQUESTIONS'];
             $scope.SectionMaxScore = $scope.SectionsData['MAXSCORE'];
             $scope.SectionMaxScale = $scope.SectionsData['MAXSCALE'];
 
             $SN = $scope.SectionName.charAt(0).toUpperCase() + $scope.SectionName.slice(1);
 
             if($SN.includes("Break")){
                 // console.warn('break');
                 $scope.BREAK = true;
             }else{
                 // console.warn('no-break');
                 $scope.BREAK = false;
             }
     
             $scope.FinalTestStart($scope.SectionsData['DURATION']);
         }
     }
     /* ========== NEXT PREVIOUS SECTION (CHANGE SECTION) =========== */
     
     
 
 
 
     
     /* ========== START TEST FINAL =========== */
     $scope.CheckTestStart = false;
     $scope.Duration = 0;
     $scope.TimeMessage = '';
     
     $scope.NextPrevious = 1;
     $scope.TotalChunks = 0;
     $scope.chunkData=[];
     
     $scope.FinalTestStart = function(TestDuration){
         // alert('s');
         $('.FinalTestStartBtn').show();
         $(".confirmNextSection").slideUp();
         $(".next-sec-submit-btn").removeAttr('disabled');
         $scope.correctAnswer=[];
         $scope.Duration = 0;
         $scope.chunkData = [];
         $scope.NextPrevious = 1;
         $scope.serial = 1;
         $scope.TotalChunks = 0;
 
         $timeout(function(){
             if($scope.Testid > 0 && $scope.Tsecid > 0){
                 $http({
                     method: 'post',
                     url: url,
                     data: $.param({ 'type': 'getSectionQuestions', 
                                     'REGID_INDEX' : $scope.REGID_INDEX,
                                     'testid' : $scope.Testid,
                                     'tsecid' : $scope.Tsecid,
                                     'DisplayAll':$scope.DisplayAll,
                                     'PerPageQue':$scope.PerPageQue,
                                 }),
                     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                 }).
                 then(function (data, status, headers, config) {
                     console.log(data.data);
                     if(data.data.success){
                         
                         $scope.unsaved = false;
                         $scope.CheckTestStart = true;
                         if($scope.DisplayAll > 0){
                             $scope.post.getSectionQuestions=data.data.data;
                         }
                         else{
                             $scope.TotalChunks = data.data.ChunkQuestion[0].length;
                             $scope.chunkData = data.data.ChunkQuestion[0];
                             $scope.post.getSectionQuestions=$scope.chunkData[$scope.NextPrevious-1];
                         }
                         if($scope.post.getSectionQuestions.length > 0){


         
                            // for($i=0; $i<$scope.post.getSectionQuestions.length;$i++){
                            //     var column = document.querySelector('#CANVA'+$i)
                            //     console.log(column);
                            //     if(column) column.innerHTML='';

                            //     // CREATE CANVAS
                            //     var canvas = document.createElement('span');
                            //     var id_name = `canvas${$i}`
                            //     canvas.setAttribute('id', id_name);
                            //     canvas.setAttribute('class', 'mb-4');
                            //     canvas.innerHTML=`CANVAS -: ${$i}`;

                            //         // SET ELEMNT MAIN DIV
                            //     // column.appendChild(canvas)
                            // }

                             //===== Focus =====
                             $timeout(function(){
                                 window.location.hash = '#pageHead';
                             },1000);
                             //===== Focus =====
                             
                             //====== Duration =====
                             var TotalMinutes = 60 * TestDuration,
                             display = document.querySelector('#timer');
                             $scope.startTimer(TotalMinutes, display);
                             //====== Duration =====
                         }
     
                     }
                     else{
                         $scope.post.getSectionQuestions=[];
                     }
                     $('.btn-st').removeAttr('disabled');
                     $('.FinalTestStartBtn').hide();
                 },
                 function (data, status, headers, config) {
                     console.log('Failed');
                 })
             }
             else{
                 console.warn('Testid OR Tsecid Not Found.');
             }
         },1000);
 
     }
     
 
     //++++++++++++ CHANGE PAGE (NEXT PREVOIUS) +++++++++++++++
     $scope.changePage = function(N_P){
         if($scope.chunkData[$scope.NextPrevious-1]){
             $scope.post.getSectionQuestions=$scope.chunkData[$scope.NextPrevious-1];
             $timeout(function(){
                 window.location.hash = '#pageHead';
             },200);
         }
     }
     //++++++++++++ CHANGE PAGE (NEXT PREVOIUS) +++++++++++++++
 
 
     
     //++++++++++++ DURATION +++++++++++++++
     $scope.startTimer = function(duration, display) {
         var timer = duration, minutes, seconds;
         $interval.cancel(interval);
 
         if(interval){$interval.cancel(interval);}
             interval = $interval(function () {
             minutes = parseInt(timer / 60, 10);
             seconds = parseInt(timer % 60, 10);
     
             minutes = minutes < 10 ? "0" + minutes : minutes;
             seconds = seconds < 10 ? "0" + seconds : seconds;
             
             display.textContent = minutes + ":" + seconds;
             
                 //**** alert ****
                 if(seconds < 10 && minutes == 00){ display.classList.add("timer"); }
                 else{ display.classList.remove("timer"); }
                 
                 if(seconds == 00 && minutes == 00){ display.textContent = minutes + ":" + seconds + " " + " Time Over"; }
                 //**** alert ****
             
             
             if (--timer < 0) {
                 $interval.cancel(interval);
                 timer = duration;
                 //***** Change section when time over *****
                     // if($scope.STESSID > 0){
                     //     $scope.updateEssay();
                     // }else{
                     //     $scope.CURRENT_SECTION = $scope.CURRENT_SECTION+1;
                     //     $scope.changeSection();
                     // }
                 //***** Change section when time over *****
             }
         }, 1000);
     }
     //++++++++++++ DURATION +++++++++++++++
 
 
     //++++++++++++ SUBMIT CONFIRM +++++++++++
     $scope.submitNextSecAlert = function(reply){
         if(reply == 'no'){
            $(".confirmNextSection").slideUp();
            $(".next-sec-submit-btn").removeAttr('disabled');
         }
         else{
            $(".next-sec-submit-btn").attr('disabled','disabled');
            $(".confirmNextSection").slideDown();
            $timeout(()=>{$("html, body").animate({ scrollTop: $(document).height()-$(window).height() });},700); 
         }
     }
     //++++++++++++ SUBMIT CONFIRM +++++++++++
 
     /* ========== START TEST FINAL =========== */
     
     
     
     
     
     
     
     
     /* ========== GET RESULT ESSAY =========== */
     $scope.getEssayResult=function () { 
         if($scope.STESSID > 0){
             $http({
                 method: 'post',
                 url: url,
                 data: $.param({ 'type': 'getEssayResult',
                                 'STESSID':$scope.STESSID,
                             }),
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
             }).
             then(function (data, status, headers, config) {
                 // console.log(data.data);
                 if(data.data.success){
                     $scope.post.getEssayResult = data.data.data;
                     $('.spinEssResult').hide();
                 }
                 else{
                     $scope.post.getEssayResult=[];
                 }
      
             },
             function (data, status, headers, config) {
                 console.log('Failed');
             })
         }
         else{
             console.info('STESSID Missing.');
         }
     }
     /* ========== GET RESULT ESSAY =========== */
     
     
     
     
     
     
     
     
     /* ========== GET RESULT BY SECTION =========== */
     $scope.getResultBySections=function () { 
         if($scope.testid_array.length > 0 || $scope.tsecid_array.length > 0){
             $http({
                 method: 'post',
                 url: url,
                 data: $.param({ 'type': 'getResultBySections',
                                 'REGID_INDEX' : $scope.REGID_INDEX,
                                 'testid_array' : $scope.testid_array,
                                 'tsecid_array':$scope.tsecid_array,
                                 'CURRENT_ATTEMPT':$scope.CURRENT_ATTEMPT,
                                 'GET_STID':$scope.GET_STID,
                             }),
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
             }).
             then(function (data, status, headers, config) {
                 // console.log(data.data);
                 if(data.data.success){
                     $scope.post.getResultBySections = data.data.data;
                     
                 }
                 else{
                     // $scope.post.getSectionQuestions=[];
                 }
      
             },
             function (data, status, headers, config) {
                 console.log('Failed');
             })
         }
         else{
             console.warn('Tsecid or Testid Missing');
         }
     }
     /* ========== GET RESULT BY SECTION =========== */
    
   
    
    
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


    /* ========== Clear All Data =========== */
    $scope.ClearAllData = function () { 
        $scope.SectionsData = [];
        $scope.Tsecid = '';
        $scope.Testid = '' ;
        $scope.PerPageQue = '';
        $scope.DisplayAll = '';
        $scope.SectionName = '';
        $scope.SectionMaxQue = '';
        $scope.SectionMaxScore = '';
        $scope.SectionMaxScale = '';
        $scope.unsaved = true;
        $scope.CheckTestStart = false;
        $scope.post.getSectionQuestions = [];
        $scope.TotalChunks = 0;
        $scope.chunkData = [];
        $scope.Duration = 0;
        $scope.TimeMessage = '';
        $scope.NextPrevious = 1;
        $interval.cancel(interval);
        $scope.post.getTestByStudentProducts=[];
        $scope.AllSectionId = [];
        $scope.chkSectionSelect = false;
        $scope.chkSectionForTest = [];
        $scope.testid_array=[];
        $scope.tsecid_array=[];
        $scope.post.getTestSections=[];
        $scope.flagsList=[];
        $scope.SketchpadArray=[];
     }
    /* ========== Clear All Data =========== */


    /* ========== Message =========== */
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
    /* ========== Message =========== */
    



});