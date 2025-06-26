$postModule = angular.module("myApp", ["ngSanitize","angularjs-dropdown-multiselect"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.Page = "L&A";
    $scope.PageSub = "LA_MASTER";
    $scope.PageSub1 = "LA_TEST_M";
    $scope.editMode = false;
    $scope.editModePs = false;
    var url = 'code/LA-TEST-MASTER.php';



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
                $scope.locid=data.data.locid;
                
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
                formData.append("testid", $scope.temp.testid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                formData.append("txtTestDesc", $scope.temp.txtTestDesc);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.testid = data.data.GET_TESTID;
                $scope.getTestMasters();
                if($scope.temp.testid > 0 && !$scope.editMode) {
                    $scope.openMapModal($scope.temp.testid);
                    $scope.getMapedQuestions(); 
                }else{

                    $scope.clearForm();
                }
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





    /* ========== GET TEST MASTERS =========== */
    $scope.getTestMasters = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTestMasters');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                // formData.append("ddlTopic", $scope.temp.ddlTopic);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTestMasters = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestMasters();
    /* ========== GET TEST MASTERS =========== */

    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlGrade").focus();
        $scope.clearFormDET();

        $scope.temp.testid = id.TESTID;
        // $scope.openMapModal(id.TESTID);
        // $scope.temp.ddlLocation = (id.LOCID && id.LOCID>0) ? id.LOCID.toString() : '';
        // if(id.LOCID > 0 && $scope.temp.ddlLocation>0){
        if(id.LOCID > 0){
            // $scope.getGrades();
            // $scope.getSubjects();
            $scope.temp.ddlGrade = id.GRADEID.toString();
            $scope.temp.ddlSubject = id.SUBID.toString();
            if($scope.temp.ddlGrade>0 && $scope.temp.ddlSubject>0)$scope.getTopics();
            $scope.$watch('post.getTopics', function () {
                $scope.temp.ddlTopic = id.TOPICID;
            }, true);
            $scope.temp.txtTestDesc = id.TESTDESC;
        }

        // $scope.getQueForMap();
        $scope.getMapedQuestions();
        $scope.editMode = true;
        $scope.index = $scope.post.getTestMasters.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    


    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp.testid='';
        $scope.temp.ddlGrade='';
        $scope.temp.ddlSubject='';
        $scope.temp.ddlTopic='';
        $scope.temp.txtTestDesc='';
        $scope.editMode = false;

        $scope.clearFormDET();
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
                data: $.param({ 'TESTID': id.TESTID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTestMasters.indexOf(id);
		            $scope.post.getTestMasters.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.clearForm();
                    
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
    $scope.mappedQueArr = [];
    
    /* ============ OPEN MAP MODAL ========= */
    $scope.openMapModal = function(id){
        $scope.editData(id);
        $('#mapQueModal').modal('show');
        // $scope.getQueForMap();
    }
    /* ============ OPEN MAP MODAL ========= */

    $scope.closeMapModal = function(){
        $scope.clearForm();
    }


    /* ============ SAVE DATA ============= */ 
    $scope.saveDataDET = function(id,val,index){
        if(!val || val==0 || val=='0') return;
        $(".mappedChk"+index).attr('disabled', 'disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET');
                formData.append("testid", $scope.temp.testid);
                formData.append("subid", id.SUBID);
                formData.append("topicid", $scope.temp.ddlTopic);
                formData.append("val", val);
                // formData.append("mappedQueArr", JSON.stringify($scope.mappedQueArr));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getMapedQuestions();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlTopic").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            // $(".mappedChk").removeAttr('disabled');
        });
    }
    /* ============ SAVE DATA ============= */






    /* ========== GET MAPPED QUESTIONS =========== */
    $scope.getMapedQuestions = function () {
        $scope.post.getMapedQuestions=[];
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getMapedQuestions','testid':$scope.temp.testid,'topicid':$scope.temp.ddlTopic}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getMapedQuestions = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getMapedQuestions();
    /* ========== GET MAPPED QUESTIONS =========== */



    /* ========== GET QUESTIONS FOR MAP =========== */
    $scope.getQueForMap = function () {
        $scope.post.getQueForMap = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0 || !$scope.temp.ddlTopic || $scope.temp.ddlTopic<=0) return;
        $('#spinQue').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getQueForMap');
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
            $scope.post.getQueForMap = data.data.success ? data.data.data : [];
                $('#spinQue').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getQueForMap(); --INIT
    /* ========== GET QUESTIONS FOR MAP =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#ddlTopic").focus();

        $scope.temp.ddlTopic = id.TOPICID;
        $scope.getQueForMap();
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#ddlTopic").focus();
        $scope.temp.ddlTopic = '';

        $scope.post.getQueForMap = [];
        // $scope.clearFormOPT();
        // $scope.clearFieldsOnChangeQueType();
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'TESTDID': id.TESTDID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getMapedQuestions.indexOf(id);
		            $scope.post.getMapedQuestions.splice(index, 1);
		            // console.log(data.data.message)
                    // $scope.clearFormDET();
                    $scope.getQueForMap();
                    $scope.getMapedQuestions();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE SUB QUESIONS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 







    
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
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getTestMasters();
            if($scope.temp.ddlLocation > 0) $scope.getTopics();
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
            // console.log(data.data);
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