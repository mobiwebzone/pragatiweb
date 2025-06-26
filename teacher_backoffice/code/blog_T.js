

$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$sce) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.formTitle = '';
    $scope.Page = 'BLOG';
    $scope.date = new Date();
    $scope.PAGE_INSIDE = true;
    $scope.trustAsHtml = $sce.trustAsHtml;
    
    // var url = 'code/blog_ST.php';

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    /* =============== DATE CONVERT ============== */




    

    /* =============== CHECK SESSION ============== */
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: '../backoffice/code/checkSession.php',
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

    /* =============== CHECK SESSION ============== */




    /* ========== GET BLOGS =========== */
    $scope.BlogData = [];
    $scope.getBlogs = function (f) {
        if(f!='') $('.btn-reload i').addClass('fa-spin');
        $('.loaderMy').show();
        $http({
            method: 'post',
            url: '../code/Blog_Home.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getBlogs');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getBlogs = data.data.data;
            $scope.post.getCategories = data.data.BLOG_CATEGORY;

            $scope.BlogData = $scope.post.getBlogs;
            $('.loaderMy').hide();
            if(f!='') $('.btn-reload i').removeClass('fa-spin');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getBlogs('');
    /* ========== GET BLOGS =========== */



    // ========== SELECT CATEGORY =========
    $scope.selectTopic = function(blogid){
        if(blogid > 0){
            const filterTopic = $scope.post.getBlogs.filter(x=>x.BLOGID == blogid);
            // console.log(filterTopic);
            $scope.BlogData = filterTopic;
            $timeout(()=>{
                jQuery('.home-title').focus();
            },300);
        }
    }
    // ========== SELECT CATEGORY =========



    // ========== SEARCH BLOG =========
    $scope.post.SearchList = [];
    $scope.searchBlog = function(){
        $scope.post.SearchList = [];
        if(!$scope.temp.searchBlog || $scope.temp.searchBlog =='') return;
        var searchText = [$scope.temp.searchBlog.toLowerCase()];

        function filter (blog, text) {
        return blog.filter(function (x) {
            return x.TAGS.toLowerCase().search(text) !== -1 || x.TOPIC.toLowerCase().search(text) !== -1 ;
        });
        }
        
        var filtered = filter($scope.post.getBlogs, searchText);
        $scope.post.SearchList = (filtered && filtered.length>0) ? filtered : [];
        // console.log(filtered);
        
    }
    // ========== SEARCH BLOG =========

    





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
                window.location.assign('login.html#!/login');
            }
            else {
                window.location.assign('dashboard.html#!/dashboard');
            }
        },
        function (data, status, headers, config) {
            console.log('Not login Failed');
        })
    }
    /* ========== Logout =========== */
    
    
    


    /* ========== Message =========== */
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
    /* ========== Message =========== */




});