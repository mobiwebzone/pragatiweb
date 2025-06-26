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
    $scope.PageSub1 = "STEPS_MASTER";
    $scope.editMode = false;
    $scope.files = [];
    
    var url = 'code/Stepts_Master.php';

    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.logo_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview =========*/ 





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

                    $scope.getStepMasters();
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
                formData.append("stepid", $scope.temp.stepid);
                formData.append("txtStepNumber", $scope.temp.txtStepNumber);
                formData.append("txtDesc", $scope.temp.txtDesc);
                formData.append("txtLink", $scope.temp.txtLink);
                formData.append("txtComment", $scope.temp.txtComment);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.stepid = data.data.GET_STEPID;
                $scope.getStepMasters();
                if($scope.temp.stepid > 0) $scope.getStepsAttachments(); 
                // $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $timeout(()=>{$("#txtImage").focus();},500);
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





    /* ========== GET STEPS MASTER =========== */
    $scope.getStepMasters = function () {
        $('#SpinMainData').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getStepMasters'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStepMasters = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStepMasters(); --INIT
    /* ========== GET STEPS MASTER =========== */


    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#txtStepNumber").focus();
        $scope.clearFormDET();

        $scope.temp.stepid = id.STEPID;
        $scope.temp.txtStepNumber = Number(id.STEPNO);
        $scope.temp.txtDesc = id.STEPDESC;
        $scope.temp.txtLink = id.STEPLINK;
        $scope.temp.txtComment = id.COMMENTS;

        if($scope.temp.stepid > 0)$scope.getStepsAttachments();

        $scope.editMode = true;
        $scope.index = $scope.post.getStepMasters.indexOf(id);
    }
    /* ============ Edit Button ============= */ 



    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.editMode = false;

        $scope.clearFormDET();
        $("#txtStepNumber").focus();
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STEPID': id.STEPID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStepMasters.indexOf(id);
		            $scope.post.getStepMasters.splice(index, 1);
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

        $scope.temp.txtImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataDET');
                formData.append("attachid", $scope.temp.attachid);
                formData.append("stepid", $scope.temp.stepid);
                formData.append("txtImage", $scope.temp.txtImage);
                formData.append("txtCommentImg", $scope.temp.txtCommentImg);
                formData.append("existingCatImage", $scope.temp.existingCatImage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormDET();
                $scope.getStepsAttachments();
                $scope.getStepMasters();
                $scope.messageSuccess(data.data.message);
                
                $("#txtImage").focus();
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






    /* ========== GET STEP ATTECHMENTS =========== */
    $scope.getStepsAttachments = function () {
        $('#spinDET').show();
        $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getStepsAttachments','stepid':$scope.temp.stepid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStepsAttachments = data.data.success ? data.data.data : [];
             $('#spinDET').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStepsAttachments();
    /* ========== GET STEP ATTECHMENTS =========== */

    




    /* ============ Edit Button ============= */ 
    $scope.editFormDET = function (id) {
        $("#txtImage").focus();

        $scope.temp.attachid = id.ATTACHID;
        $scope.temp.existingCatImage = id.ATTACHMENT;
        $scope.temp.txtIMG_Caption = id.COMMENTS;


        /*########### IMG #############*/
        if(id.ATTACHMENT || id.ATTACHMENT != ''){
            $scope.logo_src='steps_master_images/'+id.ATTACHMENT;
        }else{
            $scope.logo_src='steps_master_images/default.png';
        }


        $scope.index = $scope.post.getStepsAttachments.indexOf(id);
    }
    /* ============ Edit Button ============= */ 




    /* ============ Clear Form =========== */ 
    $scope.clearFormDET = function(){
        $("#txtImage").focus();
        $scope.temp.attachid = '';
        $scope.temp.existingCatImage = '';
        $scope.temp.txtComment = '';
        $scope.logo_src = '';
        $scope.files = [];
        angular.element('#txtImage').val(null);
    }
    /* ============ Clear Form =========== */ 

    


    /* ========== DELETE =========== */
    $scope.deleteDET = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ATTACHID': id.ATTACHID, 'type': 'deleteDET' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStepsAttachments.indexOf(id);
		            $scope.post.getStepsAttachments.splice(index, 1);
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