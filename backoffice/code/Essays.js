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
    $scope.PageSub = "ESSAYS";
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Essays_code.php';



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
                $scope.getEssays();
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
                formData.append("essid", $scope.temp.essid);
                formData.append("ddlTest", $scope.temp.ddlTest);
                formData.append("ddlTestSection", $scope.temp.ddlTestSection);
                formData.append("txtEssayTopic", $scope.temp.txtEssayTopic);
                formData.append("ddlLimitOn", $scope.temp.ddlLimitOn);
                formData.append("txtLimit", $scope.temp.txtLimit);
                formData.append("txtTimeAllowed", $scope.temp.txtTimeAllowed);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getEssays();
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






    /* ========== GET ESSAYS =========== */
    $scope.getEssays = function () {
        $('.spinEssays').show();
        $scope.post.getTestSection = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getEssays'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getEssays = data.data.data;
            }else{
                console.info(data.data.message);
            }
            $('.spinEssays').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getEssays(); --INIT
    /* ========== GET ESSAYS =========== */






    /* ========== GET TEST MASTER =========== */
    $scope.getTestMaster = function () {
        $('.spinTest').show();
        $scope.post.getTestSection = [];
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



    


     /* ========== GET TEST SECTION =========== */
     $scope.getTestSection = function () {
         $('.spinTestSec').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTestSection','ddlTest' : $scope.temp.ddlTest}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTestSection = data.data.data;
            }else{
                $scope.post.getTestSection = [];
                console.info(data.data.message);
            }
            $('.spinTestSec').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTestSection();
    /* ========== GET TEST SECTION =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        document.getElementById("ddlTest").focus();

        
        $scope.temp.essid = id.ESSID;
        $scope.temp.ddlTest = (id.TESTID).toString();
        $scope.getTestSection();
        $timeout(function () {
            $scope.temp.ddlTestSection = (id.TSECID).toString();
        },500);
        $scope.temp.txtEssayTopic = id.ESSTOPIC;
        $scope.temp.ddlLimitOn = id.LIMITON;
        $scope.temp.txtLimit = Number(id.LIMIT);
        $scope.temp.txtTimeAllowed = Number(id.TIMEALLOWED);
        
        $scope.editMode = true;
        $scope.index = $scope.post.getEssays.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlTest").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getTestSection = [];
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'essid': id.ESSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getEssays.indexOf(id);
		            $scope.post.getEssays.splice(index, 1);
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