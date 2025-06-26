$postModule = angular.module("myApp", ["ngSanitize","textAngular"]);
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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "PRODUCTS";
    $scope.PageSub = "TOPICS";
    $scope.formTitle = 'Topics';
    $scope.serial = 1;
    $scope.files = [];

    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        // ['p'],
        ['p','bold', 'italics', 'underline', 'strikeThrough', 'redo', 'undo', 'clear'],
        // ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========

    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.Img_src = event.target.result;
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    $scope.clearImg_src=()=>{
        $scope.Img_src='';
        $scope.files = [];
        angular.element('#pictureUpload').val(null);
    }
    /*========= Image Preview =========*/  

    $scope.objectTypeChange =  () =>{
        $scope.clearImg_src();
        var objtype = !$scope.temp.ddlObjType?'':$scope.temp.ddlObjType;
        $scope.objectAccept = objtype == 'IMAGE' ? '.jpg, .jpeg, .png' : objtype == 'VIDEO' ? 'video/*' : objtype == 'PDF' ? 'application/pdf' : '';
        $scope.maxSize = objtype == 'IMAGE' ? '(Max : 1mb)' : objtype == 'VIDEO' ? '' : objtype == 'PDF' ? '(Max : 2mb)' : '';
    }


    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Topics_code.php';

    // GET DATA
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
                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getProduct();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
            }
            else {
                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.saveTopics = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.pictureUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveTopics');
                formData.append("topicid", $scope.temp.topicid);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtTopic", $scope.temp.txtTopic);
                formData.append("txtTopicDesc", $scope.temp.txtTopicDesc);
                formData.append("txtDisplayOrder", $scope.temp.txtDisplayOrder);
                formData.append("ddlObjType",$scope.temp.ddlObjType);
                formData.append("pictureUpload",$scope.temp.pictureUpload);
                formData.append("existingPictureUpload", $scope.temp.existingPictureUpload);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.getTopics();
                $scope.clearForm();
                
                document.getElementById("ddlProduct").focus();
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


     /* ========== GET Topics =========== */
     $scope.getTopics = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getTopics','ddlProduct':$scope.temp.ddlProduct}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getTopic = data.data.data;
                $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    



     /* ========== GET Products =========== */
     $scope.getProduct = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT




    /* ============ Edit Button ============= */ 
    $scope.editTopic = function (id) {
        document.getElementById("ddlProduct").focus();
        $scope.temp = {
            topicid:id.TOPIC_ID,
            ddlProduct:(id.PRODUCTID).toString(),
            txtTopic: id.TOPIC,
            txtTopicDesc: id.TOPIC_DESC,
            txtDisplayOrder: Number(id.DISPLAY_ORDER),
            ddlObjType:id.OBJECTTYPE,
            pictureUpload:id.OBJECTNAME,
            existingPictureUpload:id.OBJECTNAME,
        };
        $scope.objectTypeChange();

        $scope.Img_src= id.OBJECTNAME != '' ? 'images/product_topic/'+id.OBJECTNAME : '';
        $scope.editMode = true;
        $scope.index = $scope.post.getTopic.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlProduct").focus();
        // $scope.temp={};
        $scope.temp.txtTopic='';
        $scope.temp.txtTopicDesc='';
        $scope.temp.txtDisplayOrder='';
        $scope.temp.topicid=0;
        $scope.editMode = false;
        $scope.clearImg_src();
    }



    /* ========== DELETE =========== */
    $scope.deleteTopic = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'topicid': id.TOPIC_ID, 'type': 'deleteTopic' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTopic.indexOf(id);
		            $scope.post.getTopic.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    


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




});