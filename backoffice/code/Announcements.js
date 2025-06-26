
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize",'ngMaterial']);
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
    $scope.Page = "SETTING";
    $scope.PageSub = "ANNOUNCE";
    $scope.TDate = new Date();
    $scope.temp.txtAMDate = new Date();
    $scope.temp.txtTillDate = new Date();
    $scope.temp.chkShowInDB =false;
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Announcements_code.php';


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 



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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getPlans();
                    $scope.getLocations();
                    $scope.getAnnouncements();
                }
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

    $scope.save = function(){
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
                formData.append("type", 'save');
                formData.append("anid", $scope.temp.anid);
                formData.append("txtAMDate", $scope.dateFormat($scope.temp.txtAMDate));
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("txtAnnouncement", $scope.temp.txtAnnouncement);
                formData.append("chkShowInDB", $scope.temp.chkShowInDB);
                formData.append("txtTillDate", $scope.dateFormat($scope.temp.txtTillDate));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.getAnnouncements();
                $scope.clear();
                document.getElementById("txtAMDate").focus();
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


     /* ========== GET Announcement =========== */
     $scope.getAnnouncements = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getAnnouncements'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getAnnouncements = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getAnnouncements(); --INIT


    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $http({
            method: 'post',
            url: 'code/Users_code.php',
            data: $.param({ 'type': 'getLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT


    /* ========== GET Location =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: 'code/LoginApproval_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlans = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT




    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("txtAMDate").focus();

        $scope.temp = {
            anid:id.ANID,
            txtAMDate: new Date(id.ANDATE),
            ddlLocation: id.LOCID == 0 ? '' : (id.LOCID).toString(),
            ddlPlan: id.PLANID == 0 ? '' : (id.PLANID).toString(),
            txtAnnouncement: id.ANNOUNCEMENT,
            chkShowInDB: id.DB_ANNOUNCE == 1 ? true : false,
            txtTillDate : id.DB_ANNOUNCE == 1 ? new Date(id.DB_ANNOUNCE_TILLDATE) : new Date(),
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getAnnouncements.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        document.getElementById("txtAMDate").focus();
        $scope.temp={};
        $scope.editMode = false;
        
        $scope.temp.txtAMDate = new Date();
        $scope.temp.txtTillDate = new Date();
        $scope.temp.chkShowInDB =false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'anid': id.ANID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getAnnouncements.indexOf(id);
		            $scope.post.getAnnouncements.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clear();
                    
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