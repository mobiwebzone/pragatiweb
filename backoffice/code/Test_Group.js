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
    $scope.PageSub = "TEST_GROUP";
    $scope.selectedTest = [];
    $scope.lengthOfProducts = 0;
    
    var url = 'code/Test_Group_code.php';



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

                // $scope.getProduct();
                $scope.getAllGroups();
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





    /* ========== CHECK PLAN SELECT OR NOT =========== */
    $scope.TESTExist = false;
    $scope.chkSelectPlan = function () {
        $scope.TESTExist = $scope.selectedTest.reduce((a, b) => a + b, 0) > 0 ? true : false;
        // console.info($scope.TESTExist);
    }
    /* ========== CHECK PLAN SELECT OR NOT =========== */
    




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
                formData.append("tgid", $scope.temp.tgid);
                formData.append("groupname", $scope.temp.groupname);
                formData.append("txtGroupName", $scope.temp.txtGroupName);
                formData.append("selectedTest", $scope.selectedTest);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {

                $scope.messageSuccess(data.data.message);
                
                $scope.clearForm();
                $scope.getAllGroups();
                $scope.temp.groupname = '';
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

    
    
    
    /* ========== GET ALL SELECTED PRODUCT =========== */
     $scope.getAllGroups = function () {
         $('.spinGroups').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAllGroups'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAllGroups = data.data.data;
            $('.spinGroups').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAllGroups(); --INIT
    /* ========== GET ALL SELECTED PRODUCT =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $timeout(function(){
            $('#txtGroupName').focus();
        },500);
        $scope.selectedTest = [];

        $scope.temp.tgid = id.TGID;
        $scope.temp.groupname = id.GROUPNAME;
        $scope.temp.txtGroupName = id.GROUPNAME;
        
        if($scope.temp.tgid > 0){
            $scope.getSelectedTestbyGroupName();
        }
        $scope.editMode = true;
        $scope.index = $scope.post.getAllGroups.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    




     /* ========== GET SELECTED TEST BY GROUPNAME =========== */
     $scope.getSelectedTestbyGroupName = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSelectedTestbyGroupName', 
                            'tgid' : $scope.temp.tgid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                if(data.data.TESTID_COUNT > 0){
                    $scope.selectedTest = data.data.TESTID;
                }
                else{
                    $scope.lengthOfProducts = $scope.selectedTest.length;
                    $scope.selectedTest = Array.apply(null, Array($scope.lengthOfProducts)).map(() => '0');
                }
            }
            else{
                $scope.messageFailure(data.data.message);
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedTestbyGroupName();
    /* ========== GET SELECTED TEST BY GROUPNAME =========== */

    
    
    
    
    /* ========== GET TEST MASTER =========== */
    $scope.getTestMaster = function () {
        $scope.selectedTest = [];
        $scope.temp.groupname = '';
        $('.spinTest').show();
        $http({
            method: 'post',
            url: 'code/Test_Master_code.php',
            data: $.param({ 'type': 'getTestMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTestMaster = data.data.data;
            $('.spinTest').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestMaster(); --INIT
    /* ========== GET TEST MASTER =========== */



    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $scope.temp={};
        $scope.selectedTest = [];
        $scope.temp.groupname = '';
        $scope.temp.txtGroupName='';
        $scope.lengthOfProducts = $scope.selectedTest.length;
        $scope.selectedTest = Array.apply(null, Array($scope.lengthOfProducts)).map(() => '0');
        $scope.getTestMaster();
        $scope.editMode = false;
        $scope.TESTExist = false;
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tgid': id.TGID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getAllGroups.indexOf(id);
		            $scope.post.getAllGroups.splice(index, 1);
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