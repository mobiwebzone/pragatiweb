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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.Page = "COLLEGE_APP";
    $scope.PageSub = "CA_MASTER";
    $scope.PageSub1 = "CLG_APPLICATION_DEADLINE_MASTER";
    $scope.editMode = false;
    $scope.APPS_model = [];
    $scope.RECOMMEND_model = [];
    
    var url = 'code/College_Application_Deadline_Master.php';

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
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getAdmYears();
                    $scope.getTranscriptSends();
                    $scope.getUniversity();
                    $scope.getCollegeMajor(); 
                    $scope.getAppNames();
                    $scope.getRecommendations();
                    $scope.getDeadlineTypes();
                    $scope.CollegeAppDeadlines();
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






/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MASTERS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

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
                formData.append("applicationid", $scope.temp.applicationid);
                formData.append("ddlAdmYear", $scope.temp.ddlAdmYear);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("ddlCollege", $scope.temp.ddlCollege);
                formData.append("ddlCollegeMajor", $scope.temp.ddlCollegeMajor);
                formData.append("ddlTS_SendMethode", $scope.temp.ddlTS_SendMethode);
                formData.append("ddlTS_WhenSend", $scope.temp.ddlTS_WhenSend);
                formData.append("txtTS_SendOther", $scope.temp.txtTS_SendOther);
                formData.append("APPS_model", $scope.APPS_model.map((x)=>{return x.id;}));
                formData.append("RECOMMEND_model", $scope.RECOMMEND_model.map((x)=>{return x.id;}));
                formData.append("txtComments", $scope.temp.txtComments);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.temp.applicationid = data.data.GET_APPLICATIONID;
                $scope.CollegeAppDeadlines();
                if($scope.temp.applicationid > 0) $scope.CollegeAppDeadlines_DET(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#ddlDeadlineType").focus();},500);
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





    /* ========== GET COLLEGE APPLICATION DEADLINES =========== */
    $scope.CollegeAppDeadlines = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'CollegeAppDeadlines'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.CollegeAppDeadlines = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.CollegeAppDeadlines(); --INIT
    /* ========== GET COLLEGE APPLICATION DEADLINES =========== */

    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlAdmYear").focus();
        $scope.clearFormDET();

        $scope.temp.applicationid = id.APPLICATIONID;
        if($scope.temp.applicationid > 0)$scope.CollegeAppDeadlines_DET();
        $scope.temp.ddlAdmYear = (id.ADMYEARID && id.ADMYEARID>0) ? id.ADMYEARID.toString() : '';
        $scope.temp.ddlUniversity = (id.UNIVERSITYID && id.UNIVERSITYID>0) ? id.UNIVERSITYID.toString() : '';
        if($scope.temp.ddlUniversity > 0 && id.UNIVERSITYID>0){
            $scope.getCollegeByUniversity();
            $timeout(()=>{$scope.temp.ddlCollege=(id.CLID && id.CLID>0)?id.CLID.toString():'';},500);
        }
        $scope.temp.ddlCollegeMajor = (id.MAJORID && id.MAJORID>0) ? id.MAJORID.toString() : '';
        $scope.temp.ddlTS_SendMethode = (id.TSENDID && id.TSENDID>0) ? id.TSENDID.toString() : '';
        $scope.temp.ddlTS_WhenSend = (id.SENDWHEN && id.SENDWHEN!='') ? id.SENDWHEN : '';
        $scope.temp.txtTS_SendOther = id.SENDWHEN_OTHER;
        // $scope.APPS_model = id.;
        // $scope.RECOMMEND_model = id.;
        $scope.temp.txtComments = id.COMMENTS;


        if(id.APPLICATIONID > 0) $scope.getSelectedAppsAllowed(id.APPLICATIONID);
        if(id.APPLICATIONID > 0) $scope.getSelectedRecommend(id.APPLICATIONID);
        if(id.APPLICATIONID > 0) $scope.CollegeAppDeadlines_DET();

        $scope.editMode = true;
        $scope.index = $scope.post.CollegeAppDeadlines.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ========== GET SELECTED APPS =========== */
    $scope.getSelectedAppsAllowed = function (APPLICATIONID) {
        $scope.APPS_model = [];
        $('.spinApps').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getSelectedAppsAllowed','APPLICATIONID' : APPLICATIONID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $('.spinApps').hide();
            if(!data.data.success) return;
            
            $scope.post.getAppNames.forEach(function (o1,index) {
                data.data.data.some(function (o2) {
                    if(o1.id === o2.id) $scope.APPS_model.push($scope.post.getAppNames[index]);
                });
            });
            // $scope.APPS_model=[$scope.post.getAppNames[0], $scope.post.getAppNames[1]];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedAppsAllowed();
    /* ========== GET SELECTED APPS =========== */




    /* ========== GET SELECTED RECOMMEND =========== */
    $scope.getSelectedRecommend = function (APPLICATIONID) {
        $scope.RECOMMEND_model = [];
        $('.spinRecommend').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getSelectedRecommend','APPLICATIONID' : APPLICATIONID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $('.spinRecommend').hide();
            if(!data.data.success) return;
            
            $scope.post.getRecommendations.forEach(function (o1,index) {
                data.data.data.some(function (o2) {
                    if(o1.id === o2.id) $scope.RECOMMEND_model.push($scope.post.getRecommendations[index]);
                });
            });
            // $scope.RECOMMEND_model=[$scope.post.getRecommendations[0], $scope.post.getRecommendations[1]];
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedRecommend();
    /* ========== GET SELECTED RECOMMEND =========== */


    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.APPS_model = [];
        $scope.RECOMMEND_model = [];
        $scope.editMode = false;

        $scope.clearFormDET();
        $("#ddlAdmYear").focus();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'APPLICATIONID': id.APPLICATIONID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.CollegeAppDeadlines.indexOf(id);
		            $scope.post.CollegeAppDeadlines.splice(index, 1);
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

/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE MASTERS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 












/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE DETAILS START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 

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
                formData.append("applicationdetid", $scope.temp.applicationdetid);
                formData.append("applicationid", $scope.temp.applicationid);
                formData.append("ddlDeadlineType", $scope.temp.ddlDeadlineType);
                formData.append("txtDeadlineDT", ($scope.temp.txtDeadlineDT && $scope.temp.txtDeadlineDT!='') ? $scope.temp.txtDeadlineDT.toLocaleString('sv-SE') : '');
                formData.append("txtOpenFromDT", ($scope.temp.txtOpenFromDT && $scope.temp.txtOpenFromDT!='') ? $scope.temp.txtOpenFromDT.toLocaleString('sv-SE') : '');
                formData.append("txtCloseFromDT", ($scope.temp.txtCloseFromDT && $scope.temp.txtCloseFromDT!='') ? $scope.temp.txtCloseFromDT.toLocaleString('sv-SE') : '');
                formData.append("txtResultETADT", ($scope.temp.txtResultETADT && $scope.temp.txtResultETADT!='') ? $scope.temp.txtResultETADT.toLocaleString('sv-SE') : '');
                formData.append("txtCommentsDetails", $scope.temp.txtCommentsDetails);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.CollegeAppDeadlines_DET();
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlDeadlineType").focus();
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






    /* ========== GET COLLEGE APPLICATION DEADLINES DETAILS =========== */
    $scope.CollegeAppDeadlines_DET = function () {
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'CollegeAppDeadlines_DET','applicationid':$scope.temp.applicationid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.CollegeAppDeadlines_DET = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.CollegeAppDeadlines_DET();
    /* ========== GET COLLEGE APPLICATION DEADLINES DETAILS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#ddlDeadlineType").focus();
    
        $scope.temp.applicationdetid = id.APPLCATIONDETID;
        $scope.temp.ddlDeadlineType = (id.DEADLINETYPEID && id.DEADLINETYPEID>0) ? id.DEADLINETYPEID.toString() : '';
        $scope.temp.txtDeadlineDT = id.DEADLINE == '' ? '' : new Date(id.DEADLINE);
        $scope.temp.txtOpenFromDT = id.OPENDATE == '' ? '' : new Date(id.OPENDATE);
        $scope.temp.txtCloseFromDT = id.ENDDATE == '' ? '' : new Date(id.ENDDATE);
        $scope.temp.txtResultETADT = id.RESULT_ETA == '' ? '' : new Date(id.RESULT_ETA);
        $scope.temp.txtCommentsDetails = id.COMMENTS;

        $scope.index = $scope.post.CollegeAppDeadlines_DET.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#ddlDeadlineType").focus();
        $scope.temp.applicationdetid = '';
        $scope.temp.ddlDeadlineType = '';
        $scope.temp.txtDeadlineDT = '';
        $scope.temp.txtOpenFromDT = '';
        $scope.temp.txtCloseFromDT = '';
        $scope.temp.txtResultETADT = '';
        $scope.temp.txtCommentsDetails = '';
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'APPLCATIONDETID': id.APPLCATIONDETID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.CollegeAppDeadlines_DET.indexOf(id);
		            $scope.post.CollegeAppDeadlines_DET.splice(index, 1);
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


/* %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% SAVE DETAILS END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% */ 
    
    
    
    
    










    
/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */



    /* ========== GET ADM YEARS =========== */
    $scope.getAdmYears = function () {
        $('.spinAdmYaer').show();
        $http({
            method: 'post',
            url: 'code/Admission_Year_Master.php',
            data: $.param({ 'type': 'getAdmYears'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAdmYears = data.data.success ? data.data.data : [];
            $('.spinAdmYaer').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAdmYears(); --INIT
   /* ========== GET ADM YEARS =========== */

   



    /* ========== GET TRANSCRIPTS SEND =========== */
    $scope.getTranscriptSends = function () {
        $('.spinTranscript').show();
        $http({
            method: 'post',
            url: 'code/Training_Transaction_code.php',
        data: $.param({ 'type': 'getTranscriptSends'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getTranscriptSends = data.data.success ? data.data.data : [];
            $('.spinTranscript').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getTranscriptSends(); --INIT
    /* ========== GET TRANSCRIPTS SEND =========== */
   




   /* ========== GET UNIVERSITY =========== */
   $scope.getUniversity = function () {
       $('.spinUniversity').show();
       $http({
           method: 'post',
           url: 'code/University_Master_code.php',
           data: $.param({ 'type': 'getUniversity'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
            $scope.post.getUniversity = data.data.success ? data.data.data : [];
           $('.spinUniversity').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getUniversity(); --INIT
   /* ========== GET UNIVERSITY =========== */


    


    /* ========== GET COLLEGES =========== */
    $scope.getCollegeByUniversity = function () {
        $('.spinCollege').show();
         $http({
             method: 'post',
            url: 'code/Student_Final_Result_code.php',
            data: $.param({ 'type': 'getCollegeByUniversity','UNIVERSITYID':$scope.temp.ddlUniversity}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCollegeByUniversity = data.data.success ? data.data.data : [];
            $('.spinCollege').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
   }
   // $scope.getCollegeByUniversity();
   /* ========== GET COLLEGES =========== */





   /* ========== GET COLLEGE MAJOR =========== */
   $scope.getCollegeMajor = function () {
    $('.spinCollegeMajor').show();
    $http({
        method: 'post',
        url: 'code/College_Major_Master_code.php',
        data: $.param({ 'type': 'getCollegeMajor'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCollegeMajor = data.data.success?data.data.data:[];
        $('.spinCollegeMajor').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getCollegeMajor(); --INIT
    /* ========== GET COLLEGE MAJOR =========== */





    /* ========== GET APPS NAMES =========== */
    $scope.getAppNames = function () {
        $('.spinApps').show();
        $http({
            method: 'post',
            url: url,
           data: $.param({ 'type': 'getAppNames'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getAppNames = data.data.success ? data.data.data : [];
            data.data.success ? $("#APPS").find('.dropdown-toggle').removeAttr('disabled') : $("#APPS").find('.dropdown-toggle').attr('disabled','disabled');
            $('.spinApps').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getAppNames(); --INIT
   /* ========== GET APPS NAMES =========== */

   




    /* ========== GET RECOMMENDATIONS =========== */
    $scope.getRecommendations = function () {
        $('.spinRecommend').show();
        $http({
            method: 'post',
            url: url,
           data: $.param({ 'type': 'getRecommendations'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getRecommendations = data.data.success ? data.data.data : [];
            data.data.success ? $("#RECOMMEND").find('.dropdown-toggle').removeAttr('disabled') : $("#RECOMMEND").find('.dropdown-toggle').attr('disabled','disabled');
            $('.spinRecommend').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getRecommendations(); --INIT
   /* ========== GET RECOMMENDATIONS =========== */




    
    /* ========== GET DEADLINE TYPE =========== */
    $scope.getDeadlineTypes = function () {
    $('.spinDeadlineType').show();
        $http({
            method: 'post',
            url: 'code/Application_DeadlineType_Master.php',
        data: $.param({ 'type': 'getDeadlineTypes'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDeadlineTypes = data.data.success ? data.data.data : [];
            $('.spinDeadlineType').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getDeadlineTypes(); --INIT
    /* ========== GET DEADLINE TYPE =========== */



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
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
    //         jQuery('.alert-success').show();
    //     });
    //     jQuery('.alert-success').delay(1000).slideUp(700,'easeOutBounce',function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
    //         jQuery('.alert-danger').show();
    //     });
    //     jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }




});