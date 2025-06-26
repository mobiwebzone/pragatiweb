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
    $scope.PageSub = "REBERIC_MASTER";
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Ruberic_Master_code.php';



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

                $scope.getTestMaster();
                $scope.getRubericData();
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
                formData.append("type", 'saveData');
                formData.append("rmid", $scope.temp.rmid);
                formData.append("ddlTest", $scope.temp.ddlTest);
                formData.append("txtCriteria", $scope.temp.txtCriteria);
                formData.append("txtAllotedMarks", $scope.temp.txtAllotedMarks);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getRubericData();
                $scope.clearForm();
                document.getElementById("ddlTest").focus();
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
    // =========== SAVE DATA ==============






    // =========== COPY CRITERIA ==============
    $scope.setOldTestName = (x)=>{
        $scope.OLD_TEST_NAME = `${x.TESTDESC} (${x.TESTYEAR})`;
        $scope.OLD_CRITERIA = x.CRITERIA;
        $scope.OLD_ALLOTEDMARKS = x.ALLOTEDMARKS;
    }
    $scope.CopyCriteria = function(){
        $(".btn-CC").attr('disabled', 'disabled');
        $(".btn-CC").text('Submit...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("rmid", 0);
                formData.append("ddlTest", $scope.temp.ddlCopyTest);
                formData.append("txtCriteria", $scope.OLD_CRITERIA);
                formData.append("txtAllotedMarks", $scope.OLD_ALLOTEDMARKS);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $('#copyCriteria').delay(500).modal('hide');
                $scope.getRubericData();
                $scope.clearForm();
                document.getElementById("ddlTest").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-CC').removeAttr('disabled');
            $(".btn-CC").text('SUBMIT');
        });
    }
    // =========== COPY CRITERIA ==============






    /* ========== GET RUBERIC DATA =========== */
    $scope.getRubericData = function () {
        $('.spinMainData').show();
        // $scope.post.getRubericData = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getRubericData'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getRubericData = data.data.data;
            }else{
                $scope.post.getRubericData = [];
                console.info(data.data.message);
            }
            $('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getRubericData(); --INIT
    /* ========== GET RUBERIC DATA =========== */






    /* ========== GET TEST MASTER =========== */
    $scope.getTestMaster = function () {
        $('.spinTest').show();
        $scope.post.getRubericData = [];
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
            }
            $('.spinTest').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestMaster(); --INIT
    /* ========== GET TEST MASTER =========== */






    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        document.getElementById("ddlTest").focus();

        
        $scope.temp.rmid = id.RMID;
        $scope.temp.ddlTest = (id.TESTID).toString();
        $scope.temp.txtCriteria = id.CRITERIA;
        $scope.temp.txtAllotedMarks = Number(id.ALLOTEDMARKS);
        
        $scope.editMode = true;
        $scope.index = $scope.post.getRubericData.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlTest").focus();
        $scope.temp={};
        $scope.editMode = false;
        // $scope.post.getRubericData = [];

        $scope.OLD_TEST_NAME = `-`;
        $scope.OLD_CRITERIA = '';
        $scope.OLD_ALLOTEDMARKS = '';
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'rmid': id.RMID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getRubericData.indexOf(id);
		            $scope.post.getRubericData.splice(index, 1);
		            // console.log(data.data.message)
                    
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