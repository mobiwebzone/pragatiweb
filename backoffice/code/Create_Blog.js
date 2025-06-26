
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
    $scope.Page = "MISC";
    $scope.PageSub = "BLOG";
    $scope.PageSub1 = "CREATE_BLOG";
    $scope.temp.txtPostingDT = new Date();
    $scope.temp.txtBlog='';
    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
        ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========
    
    var url = 'code/Create_Blog.php';




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
                $scope.getBlogCategories();
                $scope.getBlog();
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
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("blogid", $scope.temp.blogid);
                formData.append("ddlBlogCategory", $scope.temp.ddlBlogCategory);
                formData.append("txtPostingDT", $scope.temp.txtPostingDT.toLocaleString('sv-SE'));
                formData.append("txtTopic", $scope.temp.txtTopic);
                formData.append("txtTags", $scope.temp.txtTags);
                formData.append("txtBlog", $scope.temp.txtBlog);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getBlog();
                $scope.clearForm();
                $("#ddlBlogCategory").focus();
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============






    /* ========== GET BLOG =========== */
    $scope.getBlog = function () {
        $scope.temp.txtSerarch = undefined;
        $('#SpinnerMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getBlog' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBlog = data.data.success ? data.data.data : [];

            $('#SpinnerMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBlog(); --INIT
    /* ========== GET BLOG =========== */





    /* ========== GET BLOG CATEGORIES =========== */
    $scope.getBlogCategories = function () {
        $scope.temp.txtSerarch = undefined;
        $('.SpinBlogCat').show();
        $http({
            method: 'post',
            url: 'code/Blog_Master.php',
            data: $.param({ 'type': 'getBlogCategories' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBlogCategories = data.data.success ? data.data.data : [];

            $('.SpinBlogCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getBlogCategories(); --INIT
    /* ========== GET BLOG CATEGORIES =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            $("#txtBlog").focus();
            $scope.temp.blogid = id.BLOGID;
            $scope.temp.ddlBlogCategory = id.BCATID.toString();
            $scope.temp.txtPostingDT = new Date(id.POSTING_DATE);
            $scope.temp.txtTopic = id.TOPIC;
            $scope.temp.txtTags = id.TAGS;
            $scope.temp.txtBlog = id.BLOG;
            $scope.editMode = true;
            $scope.index = $scope.post.getBlog.indexOf(id);
        }
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlBlogCategory").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.txtPostingDT = new Date();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        if($scope.userrole == 'ADMINISTRATOR' || $scope.userrole == 'SUPERADMIN'){
            var r = confirm("Are you sure want to delete this record!");
            if (r == true) {
                $http({
                    method: 'post',
                    url: url,
                    data: $.param({ 'BLOGID': id.BLOGID, 'type': 'delete' }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data)
                    if (data.data.success) {
                        var index = $scope.post.getBlog.indexOf(id);
                        $scope.post.getBlog.splice(index, 1);
                        // console.log(data.data.message)
                        
                        $scope.messageSuccess(data.data.message);
                    } else {
                        $scope.messageFailure(data.data.message);
                    }
                })
            }
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