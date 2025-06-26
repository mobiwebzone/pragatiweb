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
    $scope.PageSub1 = "COLLEGE_SPECIFIC_ESSAYS_MASTER";
    $scope.editMode = false;
    
    var url = 'code/College_SpecificEssays_Master.php';

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
                    $scope.getUniversity();
                    $scope.getCollegeMajor(); 
                    $scope.getAdmYears();
                    $scope.getAppEssays();

                    $scope.getCollegeSpecMaster();
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
                formData.append("cseid", $scope.temp.cseid);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("ddlCollege", $scope.temp.ddlCollege);
                formData.append("ddlCollegeMajor", $scope.temp.ddlCollegeMajor);
                formData.append("ddlAdmType", $scope.temp.ddlAdmType);
                formData.append("txtAdmTypeOther", $scope.temp.txtAdmTypeOther);
                formData.append("ddlAdmYear", $scope.temp.ddlAdmYear);
                formData.append("chkUseMainEssay", $scope.temp.chkUseMainEssay);
                formData.append("ddlAppEssay", $scope.temp.ddlAppEssay);
                formData.append("txtRemarks", $scope.temp.txtRemarks);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.cseid = data.data.GET_CSEID;
                $scope.getCollegeSpecMaster();
                if($scope.temp.cseid > 0) $scope.getCollgeSpecEssay_DET(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#ddlEssayType").focus();},500);
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





    /* ========== GET COLLEGE SPECIFIC ESSAYS =========== */
    $scope.getCollegeSpecMaster = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getCollegeSpecMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCollegeSpecMaster = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCollegeSpecMaster(); --INIT
    /* ========== GET COLLEGE SPECIFIC ESSAYS =========== */


    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlUniversity").focus();
        $scope.clearFormDET();

        $scope.temp.cseid = id.CSEID;
        $scope.temp.ddlUniversity = (!id.UNIVERSITYID || id.UNIVERSITYID<=0) ? '' : id.UNIVERSITYID.toString();
        if($scope.temp.ddlUniversity > 0){
            $scope.getCollegeByUniversity();
            $timeout(()=>{$scope.temp.ddlCollege = (!id.CLID || id.CLID<=0) ? '' : id.CLID.toString();},500);
        }
        $scope.temp.ddlCollegeMajor = (!id.MAJORID || id.MAJORID<=0) ? '' : id.MAJORID.toString();
        $scope.temp.ddlAdmType = id.ADMTYPE;
        $scope.temp.txtAdmTypeOther = id.ADMTYPE_OTHER;
        $scope.temp.ddlAdmYear = (!id.ADMYEARID || id.ADMYEARID<=0) ? '' : id.ADMYEARID.toString();
        $scope.temp.chkUseMainEssay = id.USE_MAIN_ESSAY == 'Yes' ? true : false;
        $scope.temp.ddlAppName = (!id.APPESSAYID || id.APPESSAYID<=0) ? '' : id.APPESSAYID.toString();
        if($scope.temp.ddlAppName > 0){
            $scope.getAppEssayDet();
            $timeout(()=>{$scope.temp.ddlAppEssay = (!id.APPESSAYDETID || id.APPESSAYDETID<=0) ? '' : id.APPESSAYDETID.toString();},500);
        }
        $scope.temp.txtRemarks = id.REMARKS;
        
        if($scope.temp.cseid > 0)$scope.getCollgeSpecEssay_DET();

        $scope.editMode = true;
        $scope.index = $scope.post.getCollegeSpecMaster.indexOf(id);
    }
    /* ============ Edit Button ============= */ 



    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;

        $scope.clearFormDET();
        $("#ddlUniversity").focus();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'CSEID': id.CSEID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCollegeSpecMaster.indexOf(id);
		            $scope.post.getCollegeSpecMaster.splice(index, 1);
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
                formData.append("csedetid", $scope.temp.csedetid);
                formData.append("cseid", $scope.temp.cseid);
                formData.append("ddlEssayType", $scope.temp.ddlEssayType);
                formData.append("txtEssayTypeOther", $scope.temp.txtEssayTypeOther);
                formData.append("txtEssay", $scope.temp.txtEssay);
                formData.append("ddlLimitOn", $scope.temp.ddlLimitOn);
                formData.append("txtMinLimit", $scope.temp.txtMinLimit);
                formData.append("txtMaxLimit", $scope.temp.txtMaxLimit);
                formData.append("txtRemarksDET", $scope.temp.txtRemarksDET);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormDET();
                $scope.getCollgeSpecEssay_DET();
                $scope.messageSuccess(data.data.message);
                
                $("#txtEssayTitle").focus();
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






    /* ========== GET COLLEGE SPECIFIC ESSAY DETAILS =========== */
    $scope.getCollgeSpecEssay_DET = function () {
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getCollgeSpecEssay_DET','cseid':$scope.temp.cseid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCollgeSpecEssay_DET = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCollgeSpecEssay_DET();
    /* ========== GET COLLEGE SPECIFIC ESSAY DETAILS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#ddlEssayType").focus();

        $scope.temp.csedetid = id.CSEDETID;
        $scope.temp.ddlEssayType = (!id.ESSAYTYPE || id.ESSAYTYPE=='') ? '' : id.ESSAYTYPE;
        $scope.temp.txtEssayTypeOther = id.ESSAYTYPE_OTHER;
        $scope.temp.txtEssay = id.ESSAY;
        $scope.temp.ddlLimitOn = (!id.LIMITON || id.LIMITON=='') ? '' : id.LIMITON;
        $scope.temp.txtMinLimit = (!id.MINLIMIT || id.MINLIMIT<=0) ? '' : Number(id.MINLIMIT);
        $scope.temp.txtMaxLimit = (!id.MAXLIMIT || id.MAXLIMIT<=0) ? '' : Number(id.MAXLIMIT);
        $scope.temp.txtRemarksDET = id.REMARKS;

        $scope.index = $scope.post.getCollgeSpecEssay_DET.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#ddlEssayType").focus();
        $scope.temp.csedetid = '';
        $scope.temp.ddlEssayType = '';
        $scope.temp.txtEssayTypeOther = '';
        $scope.temp.txtEssay = '';
        $scope.temp.ddlLimitOn = '';
        $scope.temp.txtMinLimit = '';
        $scope.temp.txtMaxLimit = '';
        $scope.temp.txtRemarksDET = '';
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'CSEDETID': id.CSEDETID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCollgeSpecEssay_DET.indexOf(id);
		            $scope.post.getCollgeSpecEssay_DET.splice(index, 1);
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


   


    /* ========== GET APPS ESSAY =========== */
    $scope.getAppEssays = function () {
        $('.spinApps').show();
        $http({
            method: 'post',
            url: 'code/App_Essays_Master.php',
           data: $.param({ 'type': 'getAppEssays'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getAppEssays = data.data.success ? data.data.data : [];
            $('.spinApps').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getAppEssays(); --INIT
   /* ========== GET APPS ESSAY =========== */


   

   /* ========== GET APP ESSAY DET =========== */
   $scope.getAppEssayDet = function () {
    $('.spinAppEssay').show();
    $http({
         method: 'post',
         url: 'code/App_Essays_Master.php',
        data: $.param({ 'type': 'getAppEssays_DET', 'appessayid' : $scope.temp.ddlAppName}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getAppEssayDet = data.data.success ? data.data.data : [];
         $('.spinAppEssay').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
}
// $scope.getAppEssayDet(); --INIT
/* ========== GET APP ESSAY DET =========== */




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