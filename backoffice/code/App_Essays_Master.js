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
    $scope.PageSub1 = "APP_ESSAYS_MASTER";
    $scope.editMode = false;
    
    var url = 'code/App_Essays_Master.php';

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
                    $scope.getAppNames();
                    $scope.getAdmYears();

                    $scope.getAppEssays();
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
                formData.append("appessayid", $scope.temp.appessayid);
                formData.append("ddlApp", $scope.temp.ddlApp);
                formData.append("ddlAdmType", $scope.temp.ddlAdmType);
                formData.append("txtAdmTypeOther", $scope.temp.txtAdmTypeOther);
                formData.append("ddlAdmYear", $scope.temp.ddlAdmYear);
                formData.append("txtComments", $scope.temp.txtComments);
                formData.append("txtNoOfEssays", $scope.temp.txtNoOfEssays);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.appessayid = data.data.GET_APPESSAYID;
                $scope.getAppEssays();
                if($scope.temp.appessayid > 0) $scope.getAppEssays_DET(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#txtEssayTitle").focus();},500);
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





    /* ========== GET APP ESSAYS =========== */
    $scope.getAppEssays = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getAppEssays'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAppEssays = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAppEssays(); --INIT
    /* ========== GET APP ESSAYS =========== */

    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlApp").focus();
        $scope.clearFormDET();

        $scope.temp.appessayid = id.APPESSAYID;
        $scope.temp.ddlApp = (!id.APPID || id.APPID<=0) ? '' : id.APPID.toString();
        $scope.temp.ddlAdmType = id.ADMTYPE;
        $scope.temp.txtAdmTypeOther = id.ADMTYPE_OTHER;
        $scope.temp.ddlAdmYear = (!id.ADMYEARID || id.ADMYEARID<=0) ? '' : id.ADMYEARID.toString();
        $scope.temp.txtComments = id.COMMENTS;
        $scope.temp.txtNoOfEssays = (!id.NO_OF_ESSAYS || id.NO_OF_ESSAYS<=0) ? '' : Number(id.NO_OF_ESSAYS);

        if($scope.temp.appessayid > 0)$scope.getAppEssays_DET();

        $scope.editMode = true;
        $scope.index = $scope.post.getAppEssays.indexOf(id);
    }
    /* ============ Edit Button ============= */ 



    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;

        $scope.clearFormDET();
        $("#ddlApp").focus();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'APPESSAYID': id.APPESSAYID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getAppEssays.indexOf(id);
		            $scope.post.getAppEssays.splice(index, 1);
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
                formData.append("appessaydetid", $scope.temp.appessaydetid);
                formData.append("appessayid", $scope.temp.appessayid);
                formData.append("txtEssayTitle", $scope.temp.txtEssayTitle);
                formData.append("ddlLimitOn", $scope.temp.ddlLimitOn);
                formData.append("txtMinLimit", $scope.temp.txtMinLimit);
                formData.append("txtMaxLimit", $scope.temp.txtMaxLimit);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormDET();
                $scope.getAppEssays_DET();
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






    /* ========== GET APP ESSAYS DETAILS =========== */
    $scope.getAppEssays_DET = function () {
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getAppEssays_DET','appessayid':$scope.temp.appessayid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAppEssays_DET = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAppEssays_DET();
    /* ========== GET APP ESSAYS DETAILS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#txtEssayTitle").focus();

        $scope.temp.appessaydetid = id.APPESSAYDETID;
        $scope.temp.txtEssayTitle = id.ESSAY_TITLE;
        $scope.temp.ddlLimitOn = id.LIMIT_ON;
        $scope.temp.txtMinLimit = (!id.MINLIMIT || id.MINLIMIT<=0) ? '' : Number(id.MINLIMIT);
        $scope.temp.txtMaxLimit = (!id.MAXLIMIT || id.MAXLIMIT<=0) ? '' : Number(id.MAXLIMIT);

        $scope.index = $scope.post.getAppEssays_DET.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#txtEssayTitle").focus();
        $scope.temp.appessaydetid = '';
        // $scope.temp.appessayid = '';
        $scope.temp.txtEssayTitle = '';
        $scope.temp.ddlLimitOn = '';
        $scope.temp.txtMinLimit = '';
        $scope.temp.txtMaxLimit = '';
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'APPESSAYDETID': id.APPESSAYDETID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getAppEssays_DET.indexOf(id);
		            $scope.post.getAppEssays_DET.splice(index, 1);
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



    /* ========== GET APPS NAMES =========== */
    $scope.getAppNames = function () {
        $('.spinApps').show();
        $http({
            method: 'post',
            url: 'code/App_Master.php',
           data: $.param({ 'type': 'getAppNames'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
            $scope.post.getAppNames = data.data.success ? data.data.data : [];
            $('.spinApps').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getAppNames(); --INIT
   /* ========== GET APPS NAMES =========== */






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