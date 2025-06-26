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
    $scope.editModeCat = false;
    $scope.editModeSubCat = false;
    $scope.editModeTopic = false;
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "QUESTION_CATEGORIES";
    $scope.GetCatid = 0;
    $scope.GetSubCatid = 0;
    $scope.SEC_ID = 0;
    
    
    var url = 'code/Question_Categories_code.php';



    // =============== GET SECID BY QUESTION MASTER PAGE =============
    $scope.SEC_ID=new URLSearchParams(window.location.search).get('SEC_ID');
    if($scope.SEC_ID > 0){
        $scope.temp.ddlSection = ($scope.SEC_ID).toString();
        $timeout(function () {  
            $('#txtCategory').focus();
            $scope.getCategories();
        },500);
    }
    // =============== GET SECID BY QUESTION MASTER PAGE =============





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

                $scope.getSectionMaster();
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


    /* ========== GET SECTION MASTER =========== */
     $scope.getSectionMaster = function () {
        $http({
            method: 'post',
            url: 'code/Question_Master_code.php',
            data: $.param({ 'type': 'getSectionMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSectionMaster = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSectionMaster(); --INIT
    /* ========== GET SECTION MASTER =========== */



    // ##################################################### CATEGORY SECTION START #####################################################

    // =========== SAVE DATA ==============
    $scope.saveDataCat = function(){
        $(".btn-save-cat").attr('disabled', 'disabled');
        $(".btn-save-cat").text('Saving...');
        $(".btn-update-cat").attr('disabled', 'disabled');
        $(".btn-update-cat").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataCat');
                formData.append("catid", $scope.temp.catid);
                formData.append("ddlSection", $scope.temp.ddlSection);
                formData.append("txtCategory", $scope.temp.txtCategory);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.GetCatid = data.data.CATID;
                // alert(data.data.CATID);
                $scope.messageSuccess(data.data.message);
                
                $scope.getCategories();
                // $scope.clearFormCat();
                // $scope.temp.txtCategory='';
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-cat').removeAttr('disabled');
            $(".btn-save-cat").text('SAVE');
            $('.btn-update-cat').removeAttr('disabled');
            $(".btn-update-cat").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCategories', 'secid' : $scope.temp.ddlSection}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories();
    /* ========== GET CATEGORIES =========== */


    /* ============ Edit Button ============= */ 
    $scope.editFormCat = function (id) {
        // document.getElementById("txtCategory").focus();

        $scope.GetCatid = id.CATID;
        $scope.temp.catid=id.CATID;
        $scope.temp.txtCategory= id.CATEGORY;
       
        $scope.editModeCat = true;
        $scope.index = $scope.post.getCategories.indexOf(id);

        $scope.getSubCategories();

        $scope.clearFormSubCat();
        $scope.clearFormTopic();
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormCat = function(){
        // document.getElementById("txtCategory").focus();
        $scope.temp.catid = 0;
        $scope.GetCatid = 0;
        $scope.temp.txtCategory = '';
        $scope.editModeCat = false;
        // $scope.post.getCategories=[];

        $scope.clearFormSubCat();
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteCat = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'catid': id.CATID, 'type': 'deleteCat' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCategories.indexOf(id);
		            $scope.post.getCategories.splice(index, 1);
                    $scope.getCategories();
                    $scope.getSubCategories();
                    $scope.getTopic();
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

    // ##################################################### CATEGORY SECTION END #####################################################








    // ##################################################### SUB CATEGORY SECTION START #####################################################

    // =========== SAVE DATA ==============
    $scope.saveDataSubCat = function(){
        $(".btn-save-subcat").attr('disabled', 'disabled');
        $(".btn-save-subcat").text('Saving...');
        $(".btn-update-subcat").attr('disabled', 'disabled');
        $(".btn-update-subcat").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataSubCat');
                formData.append("subcatid", $scope.temp.subcatid);
                formData.append("GetCatid", $scope.GetCatid);
                formData.append("txtSubCategory", $scope.temp.txtSubCategory);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.GetSubCatid = data.data.SUBCATID;
                // alert(data.data.CATID);
                $scope.messageSuccess(data.data.message);
                
                $scope.getSubCategories();
                // $scope.clearFormSubCat();
                // $scope.temp.txtSubCategory = '';
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-subcat').removeAttr('disabled');
            $(".btn-save-subcat").text('SAVE');
            $('.btn-update-subcat').removeAttr('disabled');
            $(".btn-update-subcat").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function () {
        if($scope.GetCatid > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getSubCategories', 'catid' : $scope.GetCatid}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getSubCategories = data.data.data;

                $timeout(function () { 
                    window.location.hash = '#SUBCAT';
                 },500);
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */


    /* ============ Edit Button ============= */ 
    $scope.editFormSubCat = function (id) {
        // document.getElementById("txtSubCategory").focus();

        $scope.GetSubCatid = id.SUBCATID;
        $scope.temp.subcatid=id.SUBCATID;
        $scope.temp.txtSubCategory= id.SUBCATEGORY;
       
        $scope.editModeSubCat = true;
        $scope.index = $scope.post.getSubCategories.indexOf(id);

        $scope.getTopic();
        $scope.clearFormTopic();
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormSubCat = function(){
        // document.getElementById("txtSubCategory").focus();
        $scope.GetSubCatid = 0;
        $scope.temp.subcatid = 0;
        $scope.temp.txtSubCategory = '';
        $scope.editModeSubCat = false;
        // $scope.post.getSubCategories=[];

        $scope.clearFormTopic();
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteSubCat = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'subcatid': id.SUBCATID, 'type': 'deleteSubCat' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getSubCategories.indexOf(id);
		            $scope.post.getSubCategories.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.getTopic();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

    // ##################################################### SUB CATEGORY SECTION END #####################################################









    // ##################################################### TOPIC SECTION START #####################################################

    // =========== SAVE DATA ==============
    $scope.saveDataTopic = function(){
        $(".btn-save-topic").attr('disabled', 'disabled');
        $(".btn-save-topic").text('Saving...');
        $(".btn-update-topic").attr('disabled', 'disabled');
        $(".btn-update-topic").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataTopic');
                formData.append("topicid", $scope.temp.topicid);
                formData.append("GetSubCatid", $scope.GetSubCatid);
                formData.append("txtTopic", $scope.temp.txtTopic);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                
                $scope.getTopic();
                // $scope.clearFormTopic();
                $scope.temp.txtTopic = '';
                $scope.temp.topicid = 0;
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save-topic').removeAttr('disabled');
            $(".btn-save-topic").text('SAVE');
            $('.btn-update-topic').removeAttr('disabled');
            $(".btn-update-topic").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============


    /* ========== GET TOPICS =========== */
    $scope.getTopic = function () {
        if($scope.GetSubCatid > 0){
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getTopic', 'subcatid' : $scope.GetSubCatid}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getTopic = data.data.data;

                $timeout(function () { 
                    window.location.hash = '#TOPIC';
                 },500);
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
    }
    // $scope.getTopic();
    /* ========== GET TOPICS =========== */


    /* ============ Edit Button ============= */ 
    $scope.editFormTopic = function (id) {
        // document.getElementById("txtTopic").focus();

        $scope.temp.topicid=id.TOPICID;
        $scope.temp.txtTopic= id.TOPIC;
       
        $scope.editModeTopic = true;
        $scope.index = $scope.post.getTopic.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    /* ============ Clear Form =========== */ 
    $scope.clearFormTopic = function(){
        document.getElementById("txtTopic").focus();
        $scope.temp.topicid = 0;
        $scope.temp.txtTopic = '';
        $scope.editModeTopic = false;

        // $scope.post.getTopic=[];
    }
    /* ============ Clear Form =========== */ 


    /* ========== DELETE =========== */
    $scope.deleteTopic = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'topicid': id.TOPICID, 'type': 'deleteTopic' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTopic.indexOf(id);
		            $scope.post.getTopic.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ========== DELETE =========== */

    // ##################################################### TOPIC SECTION END #####################################################





    
    




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