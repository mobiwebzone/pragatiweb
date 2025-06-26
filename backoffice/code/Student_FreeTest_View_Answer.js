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
    $scope.PageSub = "ST_FREE_TEST_VIEW_ANS";
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Student_FreeTest_View_Answer.php';






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
                $scope.getFreeTestUsersList();
                $scope.getFreeTests();
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







    /* ========== GET FREE TEST USERS =========== */
    $scope.getFreeTestUsersList = function () {
        $('.spinUsers').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getFreeTestUsersList'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getFreeTestUsersList =data.data.success?data.data.data:[];
            $('.spinUsers').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getFreeTestUsersList(); --INIT
    /* ========== GET FREE TEST USERS =========== */






    /* ========== GET FREE TESTS =========== */
    $scope.getFreeTests = function () {
        $('.spinPlans').show();
        // $scope.post.getTestSection = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getFreeTests'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getFreeTests =data.data.success?data.data.data:[];
            $('.spinPlans').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getFreeTests(); --INIT
    /* ========== GET FREE TESTS =========== */




    /* ============ VIEW TEST MODAL =========== */
    $scope.USERNAME = '';
    $scope.TESTID=$scope.REGID=$scope.TSECID=0;
    $scope.openViewTestModal = function(id,id2){
        $scope.USERNAME = `${id.FIRSTNAME} ${id.LASTNAME}`;
        $scope.TESTID=id2.TESTID;
        $scope.REGID=id.REGID;
        $scope.getTestSection(id2.TESTID,id.REGID);
    }
    /* ============ VIEW TEST MODAL =========== */ 



     /* ========== GET TEST SECTION BY TEST =========== */
     $scope.getTestSection = function (TESTID,REGID) {
        $scope.post.getTestSection = [];
        $scope.post.getStudentAnswer=[];
        $scope.post.getTestSectionAttempts = [];
        $scope.SectionName = "";
        $scope.ATTEMPT = 0;
         $('.spinTestSec').show();
         $('.spinSections').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTestSection',
                            'ddlTest' : TESTID,
                            'ddlStudent' : REGID,
                            'txtTestDate' : (!$scope.temp.txtTestDate || $scope.temp.txtTestDate=='') ? '' : $scope.temp.txtTestDate.toLocaleString('sv-SE')}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTestSection = data.data.data;
                $scope.TSECID = $scope.post.getTestSection[0]['TSECID'];
                // $scope.getTestSectionAttempts();
            }else{
                $scope.post.getTestSection = [];
                $scope.TSECID=0;
                // console.info(data.data.message);
            }
            $('.spinTestSec').hide();
            $('.spinSections').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestSection();
    /* ========== GET TEST SECTION BY TEST =========== */



    


     /* ========== GET TEST SECTION ATTEMPTS =========== */
    $scope.SectionName = "";
     $scope.getTestSectionAttempts = function (id) {
        $scope.TSECID = id.TSECID;
         $scope.SectionName = id.SECTION;
         $scope.TSAttID = id;
         
         $scope.post.getStudentAnswer=[];
         $scope.STID=0;

         $scope.post.getTestSectionAttempts = [];
        $scope.ATTEMPT = 0;
        //  $('.spinTestAns').show();
         $('.spinAttempts').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTestSectionAttempts','TESTID' : $scope.TESTID,
                            'TSECID': id.TSECID,'REGID':$scope.REGID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTestSectionAttempts = data.data.data;
                $scope.getStudentAnswer(data.data.data[0]);
            }else{
                $scope.post.getTestSectionAttempts = [];
                console.info(data.data.message);
            }
            $('.spinAttempts').hide();
            // $('.spinTestAns').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestSectionAttempts();
    /* ========== GET TEST SECTION ATTEMPTS =========== */



    /* ========== GET STUDENT ANSWERS =========== */
    $scope.myid = [];
    $scope.ATTEMPT = 0;
    $scope.STID = 0;
    $scope.getStudentAnswer = function (id) {
        // alert($scope.TSECID);
        $('.btnDelAtt').attr('disabled','disabled');
        // $('.spinTestAns').show();
        $('.spinMainDataAtt').show();
        $scope.myid = id;
        $scope.ATTEMPT = id.ATTEMPT;
       $http({
           method: 'post',
           url: url,
           data: $.param({ 'type': 'getStudentAnswer',
                           'TESTID':$scope.TESTID,
                           'TSECID': $scope.TSECID,
                           'REGID':$scope.REGID,
                           'ATTEMPT':id.ATTEMPT
                        }),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
           if(data.data.success){
               $scope.post.getStudentAnswer = data.data.data;
               $scope.STID = data.data.data[0]['STID'];
           }else{
               $scope.post.getStudentAnswer = [];
               $scope.STID = 0;
               console.info(data.data.message);
           }
           $('.spinMainDataAtt').hide();
        //    $('.spinTestAns').hide();
           $('.btnDelAtt').removeAttr('disabled');
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getStudentAnswer();
   /* ========== GET STUDENT ANSWERS =========== */



    /* ============ DELETE ATTEMPT =========== */ 
    $scope.DeleteAttempt = function () {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STID': $scope.STID, 'type': 'DeleteAttempt' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
                    $scope.getTestSectionAttempts($scope.TSAttID);
                    $scope.getFreeTestUsersList();
		            console.log(data.data.message)
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ============ DELETE ATTEMPT =========== */ 


    
    /* ============ DELETE FREE USER =========== */ 
    $scope.deleteFreeTestUser = function (id) {
        var r = confirm("Are you sure want to delete this user!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'REGID': id.REGID, 'type': 'deleteFreeTestUser' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                console.log(data.data)
		        if (data.data.success) {
                    var index = $scope.post.getFreeTestUsersList.indexOf(id);
		            $scope.post.getFreeTestUsersList.splice(index, 1);
                    // $scope.getFreeTestUsersList();
		            console.log(data.data.message)
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ============ DELETE FREE USER =========== */ 







    /*============ GET STUDENT BY PLAN =============*/ 
    $scope.getStudentByPlan = function () {
        $('.spinStudent').show();
        $scope.post.getTestByRegid = [];
        $scope.post.getTestSection = [];
        $scope.post.getStudentAnswer = [];
        $scope.post.getTestSectionAttempts = [];
        $scope.SectionName = "";
        $scope.ATTEMPT = 0;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByPlan', 'ddlPlan' : $scope.temp.ddlPlan,'ddlLocation':0}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentByPlan = data.data.data;
            }else{
                console.info(data.data.message);
            }
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlan();
    /*============ GET STUDENT BY PLAN =============*/ 






    /*============ GET STUDENT TEST BY REGID =============*/ 
    $scope.getTestByRegid = function () {
        $('.spinTest').show();
        $scope.temp.ddlTest='';
        $scope.post.getTestSection = [];
        $scope.post.getStudentAnswer = [];
        $scope.post.getTestSectionAttempts = [];
        $scope.SectionName = "";
        $scope.ATTEMPT = 0;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTestByRegid','ddlStudent' : $scope.temp.ddlStudent}),
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
    // $scope.getTestByRegid(); --INIT
    /*============ GET STUDENT TEST BY REGID =============*/ 




    
    /* ============ UPDATE ANS MODAL ============= */ 
    // $scope.updateAnswerModal = (id) => {
    //     $scope.temp.ddlAns = '';
    //     $scope.temp.txtAns = '';
    //     $scope.RID = id.RID;
    //     $scope.QUESTION = id.QUESTION;
    //     $scope.QUETYPE = id.QUETYPE;
    //     $scope.CORRECTANS = id.CORRECTANS;
    //     $scope.STUDENTANS = id.STUDENTANS != '' ? id.STUDENTANS : '-';
    //     if($scope.QUETYPE === 'MCQ'){
    //         $scope.QUE_OPTIONS = id.QUE_OPTIONS.split(", ");
    //         $scope.temp.ddlAns = id.STUDENTANS;
    //     }else if($scope.QUETYPE === 'TYPE-IN'){
    //         $scope.temp.txtAns = id.STUDENTANS;
    //     }
    // }
    /* ============ UPDATE AND MODAL ============= */ 
    /* ============ CLEAR MODAL DATA ============= */ 
    // $scope.clearModalData =()=>{
    //     $('#AnsModal').trigger({ type: "click" });
    //     $scope.temp.ddlAns = '';
    //     $scope.temp.txtAns = '';
    //     $scope.RID = '';
    //     $scope.QUESTION = '';
    //     $scope.QUETYPE = '';
    //     $scope.CORRECTANS = '';
    //     $scope.STUDENTANS = '';
    //     $scope.QUE_OPTIONS = '';
    // }
    /* ============ CLEAR MODAL DATA ============= */ 









    // =========== UPDATE ANSWER ==============
    // $scope.UpdateAnswerFinal = function(){
    //     $(".btnupdate").attr('disabled', 'disabled');
    //     $(".btnupdate").text('Updating...');
    //     $('.spinUpdateAns').show();

    //     // console.log($scope.myid);
    //     // alert($scope.temp.ddlCollege);
    //     $scope.ansFinal =  $scope.QUETYPE === 'MCQ' ? $scope.temp.ddlAns : $scope.temp.txtAns;
    //     $http({
    //         method: 'POST',
    //         url: url,
    //         processData: false,
    //         transformRequest: function (data) {
    //             var formData = new FormData();
    //             formData.append("type", 'UpdateAnswerFinal');
    //             formData.append("RID", $scope.RID);
    //             formData.append("ansFinal", $scope.ansFinal);
    //             formData.append("CORRECTANS", $scope.CORRECTANS);
    //             return formData;
    //         },
    //         data: $scope.temp,
    //         headers: { 'Content-Type': undefined }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         if (data.data.success) {
    //             $scope.messageSuccess(data.data.message);
    //             $scope.getStudentAnswer($scope.myid);
    //             $('#AnsModal').trigger({ type: "click" });
    //             $scope.clearModalData();
                
    //         }
    //         else {
    //             $scope.messageFailure(data.data.message);
    //             // console.log(data.data)
    //         }
    //         $('.spinUpdateAns').hide();
    //         $('.btnupdate').removeAttr('disabled');
    //         $(".btnupdate").text('UPDATE');
    //     });
    // }
    // =========== UPDATE ANSWER ==============


    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlPlan").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getTestSection = [];
        $scope.SectionName = "";
        $scope.post.getTestByRegid = [];
        $scope.post.getStudentByPlan =[];
        $scope.post.getStudentAnswer = [];
        $scope.post.getTestSectionAttempts = [];
        $scope.clearModalData();


        $scope.SectionName = "";
        $scope.TESTID = 0;
        $scope.TSECID = 0;
        $scope.REGID = 0;
        $scope.TSAttID = [];
        $scope.myid = [];
        $scope.ATTEMPT = 0;
        $scope.STID = 0;
    }
    /* ============ Clear Form =========== */ 
    
    
    
    



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