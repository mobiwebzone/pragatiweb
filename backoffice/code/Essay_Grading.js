
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","textAngular"]);
$postModule.filter('capitalize', function() {
    return function(input) {
      return (angular.isString(input) && input.length > 0) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : input;
    }
});
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

$postModule.decorator('taOptions', ['taRegisterTool', '$delegate', function(taRegisterTool, taOptions){
    // $delegate is the taOptions we are decorating
    // register the tool with textAngular
    taRegisterTool('colourRed', {
        iconclass: "fas fa-highlighter red",
        action: function(){
            this.$editor().wrapSelection('backcolor', 'yellow');
        }
    });
    // add the button to the default toolbar definition
    taOptions.toolbar[1].push('colourRed');    
    return taOptions;
}]);
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
        ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear','colourRed'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
        ['wordcount', 'charcount']
    ];

    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "ESSAY_GRADING";
    $scope.PAGEFOR = 'ADMIN';
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Essay_Grading_code.php';



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
                $scope.locid=data.data.locid;
                // window.location.assign("dashboard.html");

                $scope.getEssays();
                $scope.getLocations();
                // $scope.getRubericData();
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






    // =========== SAVE ESSAY ==============
    $scope.txtEssay = '';
    $scope.SaveEssay = function(){
        $(".btn-saveEss").attr('disabled', 'disabled');
        $(".btn-saveEss").text('Saving...');

        var WC = Number(document.getElementById('toolbarWC').textContent.replace('Words: ',''));
        var CC = Number(document.getElementById('toolbarCC').textContent.replace('Characters: ',''));

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'SaveEssay');
                formData.append("STESSID", $scope.STESSID);
                formData.append("txtEssay", $scope.txtEssay);
                formData.append("total_words", $scope.TOTAL_WORD);
                formData.append("total_chars", $scope.TOTAL_CHAR);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $scope.getGradingData();
                // $scope.clearForm();
                $scope.getStudentEssay('GETDETAIL');
                $scope.ESSAY = $scope.txtEssay;
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveEss').removeAttr('disabled');
            $(".btn-saveEss").text('SAVE');
            // $('.btn-update').removeAttr('disabled');
            // $(".btn-update").text('UPDATE');
        });
    }
    // =========== SAVE ESSAY ==============






    // =========== SAVE GRADING ==============
    $scope.saveGrading = function(){
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
                formData.append("type", 'saveGrading');
                formData.append("egid", $scope.temp.egid);
                formData.append("RMID", $scope.temp.ddlCriteria);
                formData.append("STESSID", $scope.STESSID);
                formData.append("REGID", $scope.REGID);
                formData.append("txtScore", $scope.temp.txtScore);
                formData.append("txtComment", $scope.temp.txtComment);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getGradingData();
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
    // =========== SAVE GRADING ==============







    /* ========== GET ESSAYS =========== */
    $scope.getEssays = function () {
        $('.spinEssay').show();
        $http({
            method: 'post',
            url: 'code/Essays_code.php',
            data: $.param({ 'type': 'getEssays'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getEssays = data.data.data;
            }else{
                // console.info(data.data.message);
            }
            $('.spinEssay').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getEssays(); --INIT
    /* ========== GET ESSAYS =========== */






    /*============ GET STUDENT ESSAY =============*/ 
    $scope.getStudentEssay = function (GETDETAIL) {
        $scope.post.getStudentEssay = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0)return;
        $scope.SELECTED_ESSAY = $scope.post.getEssays.filter(x=>x.ESSID==$scope.temp.ddlEssay).map(x=>x.ESSTOPIC).toString();
        $('.spinStudent').show();
        
        $scope.clearAll();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentEssay', 'ESSID' : $scope.temp.ddlEssay, 'LOCID':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentEssay = data.data.data;
                // console.log(GETDETAIL);
                // console.log($scope.temp.ddlStudent);
                if(GETDETAIL && $scope.temp.ddlStudent > 0){
                    $scope.getEssayDetails();
                }
            }else{
                $scope.post.getStudentEssay = [];
                console.info(data.data.message);
            }
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentEssay(); --INIT
    /*============ GET STUDENT ESSAY =============*/ 
    
    
    
    
    
    
    $scope.STESSID = '';
    $scope.REGID = '';
    $scope.ESSAY = '';
    $scope.TOTAL_CHAR = '';
    $scope.TOTAL_WORD = '';
    $scope.NEW_STARTTIME = '';
    $scope.NEW_ENDTIME = '';
    $scope.LIMITON = '';
    $scope.LIMIT = '';
    $scope.TIMEALLOWED = '';
    $scope.txtEssay = '';
    $scope.EssayDetails=[];
    /*============ GET ESSAY DETAILS =============*/ 
    $scope.getEssayDetails = function () { 
        $scope.clearAll();
        
        $scope.EssayDetails = $scope.post.getStudentEssay.filter((x) => {
            return Number($scope.temp.ddlStudent) === x.INSERTID;
        });

        console.log($scope.EssayDetails);
        if($scope.EssayDetails.length>0){

            $scope.STESSID = $scope.EssayDetails[0].STESSID;
            $scope.TESTID = $scope.EssayDetails[0].TESTID;
            $scope.REGID = $scope.EssayDetails[0].INSERTID;
            $scope.ESSAY = $scope.EssayDetails[0].ESSAY;
            $scope.TOTAL_CHAR = $scope.EssayDetails[0].TOTAL_CHAR;
            $scope.TOTAL_WORD = $scope.EssayDetails[0].TOTAL_WORD;
            $scope.NEW_STARTTIME = $scope.EssayDetails[0].NEW_STARTTIME;
            $scope.NEW_ENDTIME = $scope.EssayDetails[0].NEW_ENDTIME;
            $scope.LIMITON = $scope.EssayDetails[0].LIMITON;
            $scope.LIMIT = $scope.EssayDetails[0].LIMIT;
            $scope.TIMEALLOWED = $scope.EssayDetails[0].TIMEALLOWED;
            $scope.txtEssay = $scope.EssayDetails[0].ESSAY;
            $('#EssaySection').slideDown();
            $scope.getGradingData();
            $scope.getRubericData();

            // $timeout(()=>{
            //     // console.log($('.ta-scroll-window > .ng-pristine > p').addClass('test-text').html());
            //     $('.ta-scroll-window > .ng-pristine > p').attr('id', 'test-text');;
            // },1000);
        }



        // console.log(EssayDetails);

    }
    /*============ GET ESSAY DETAILS =============*/ 







    /* ========== GET GRADING DATA =========== */
    $scope.getGradingData = function () {
        $('.spinGrading').show();
        if(Number($scope.STESSID) > 0 && Number($scope.REGID) > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getGradingData' , 
                                'STESSID':$scope.STESSID,
                                'REGID':$scope.REGID}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getGradingData = data.data.data;
                }else{
                    // console.info(data.data.message);
                }
                $('.spinGrading').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        else{
            console.error('STESSID / REGID Missing.');
        }
    }
    // $scope.getGradingData();
    /* ========== GET GRADING DATA =========== */




    /* ========== GET RUBERIC DATA =========== */
    $scope.getRubericData = function () {
        $('.spinRuberic').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRubericData', 'TESTID' : $scope.TESTID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRubericData = data.data.data;
            }else{
                $scope.post.getRubericData = [];
                // console.info(data.data.message);
            }
            $('.spinRuberic').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRubericData();
    /* ========== GET RUBERIC DATA =========== */
    
    
    
    /* ========== GET ALLOTED MARKS =========== */
    $scope.getAllotedMarks=function () {
        $scope.ALLOTEDMARKS = '';
        var allotedMarks = $scope.post.getRubericData.filter((x) => {
            return Number($scope.temp.ddlCriteria) === x.RMID;
        });
        $scope.ALLOTEDMARKS = allotedMarks[0]['ALLOTEDMARKS'];
        // console.log(allotedMarks);
    }
    /* ========== GET ALLOTED MARKS =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {        
        $scope.temp.egid = id.EGID;
        $scope.temp.ddlCriteria = (id.RMID).toString();
        $scope.ALLOTEDMARKS = id.ALLOTEDMARKS;
        $scope.temp.txtScore = Number(id.SCORE);
        $scope.temp.txtComment = id.REMARK;
        
        // CHECK ALLOTED < SCORE
        if(Number(id.SCORE) > $scope.ALLOTEDMARKS) $scope.temp.txtScore = '';

        
        $scope.editMode = true;
        $scope.index = $scope.post.getGradingData.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp.egid='';
        $scope.temp.ddlCriteria='';
        $scope.ALLOTEDMARKS = '';
        $scope.temp.txtScore = '';
        $scope.temp.txtComment = '';
        $scope.editMode = false;
        // $scope.post.getRubericData = [];
    }
    /* ============ Clear Form =========== */ 
    
    
    
    
    
    /* ============ Clear ALL =========== */ 
    $scope.clearAll = function () {
        $scope.clearForm();
        $scope.STESSID = '';
        $scope.REGID = '';
        $scope.ESSAY = '';
        $scope.TOTAL_CHAR = '';
        $scope.TOTAL_WORD = '';
        $scope.NEW_STARTTIME = '';
        $scope.NEW_ENDTIME = '';
        $scope.LIMITON = '';
        $scope.LIMIT = '';
        $scope.TIMEALLOWED = '';
        $scope.post.getGradingData=[];
        $('#EssaySection').slideUp();
    }
    /* ============ Clear ALL =========== */ 



    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getStudentByLoc = [];
        $scope.spinLocation = true;
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.spinLocation = false;
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getStudentEssay();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'egid': id.EGID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getGradingData.indexOf(id);
		            $scope.post.getGradingData.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */



    $scope.clearEssay = function(){
        $scope.temp.ddlEssay = '';
        $scope.temp.ddlStudent = '';
        $scope.post.getStudentEssay = [];
        $scope.clearAll();
    }

    /* ========== DELETE =========== */
    $scope.DeleteEssay = function (id) {
        var r = confirm("Are you sure want to delete this Essay!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STESSID': $scope.STESSID, 'type': 'DeleteEssay' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // console.log(data.data.message)
                    $scope.clearEssay();
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




    /* ========== HEIGHT LIGHT TEXT =========== */
    $scope.highlightRange = (range) => {
        var newNode = document.createElement('mark');
        range.surroundContents(newNode);
     }
     // original select range function
     $scope.highlight = () => {
         var userSelection = window.getSelection();
         console.log(userSelection);
         console.log(userSelection.baseNode.parentNode.tagName === 'MARK');
        //  console.log(userSelection.baseNode.nodeName);
        // const startA = userSelection.anchorNode.parentNode.tagName === 'MARK'
        // const endA = userSelection.focusNode.parentNode.tagName === 'MARK'
        // // return startA || endA
        //  console.log(startA || endA);
        if(userSelection.baseNode.parentNode.tagName === 'MARK'){
            // var element = document.getElementsByTagName("mark"), index;

            // for (index = element.length - 1; index >= 0; index--) {
            //     element[index].parentNode.removeChild(element[index]);
            // }
            $(window.getSelection().anchorNode.parentElement.localName).unwrap();

        }else{
            for(var i = 0; i < userSelection.rangeCount; i++) {
               $scope.highlightRange(userSelection.getRangeAt(i));
            }
        }


        // CLEAR SELECTION
        // if (window.getSelection) {
        //     if (window.getSelection().empty) {  // Chrome
        //         window.getSelection().empty();
        //     } else if (window.getSelection().removeAllRanges) {  // Firefox
        //         window.getSelection().removeAllRanges();
        //     }
        //     } else if (document.selection) {  // IE?
        //     document.selection.empty();
        // }
     }
    /* ========== HEIGHT LIGHT TEXT =========== */







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