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
    $scope.PageSub = "WORKING_HOURS";
    $scope.temp.chkClosed =false;
    $scope.temp.txtDate = new Date();
    $scope.temp.txtForYear = Number(new Date().getFullYear());

    $scope.days = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
    ];
    
    var url = 'code/Working_Hours_BackOffice_code.php';


    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 

    // -========= Set time ==============
    $scope.SetTime=function (time) {
        var d = new Date(),
        // s = "01.25 PM",
        s = time,
        parts = s.match(/(\d+)\:(\d+)(\w+)/),
        hours = /am/i.test(parts[3]) ? parseInt(parts[1], 10) : parseInt(parts[1], 10) + 12,
        minutes = parseInt(parts[2], 10);

        d.setHours(hours);
        d.setMinutes(minutes);
        d.setSeconds(0,0);

        return d
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
                    $scope.getLocations();
                    $scope.getWorkingHours();
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
        // alert($scope.temp.chkClosed);
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        $scope.txtTimeFrom=(!$scope.temp.txtTimeFrom || $scope.temp.txtTimeFrom == undefined) ? '' : $scope.temp.txtTimeFrom.toLocaleTimeString('en-GB');
        $scope.txtTimeTo=(!$scope.temp.txtTimeTo || $scope.temp.txtTimeTo == undefined )? '' : $scope.temp.txtTimeTo.toLocaleTimeString('en-GB');
        
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("whid", $scope.temp.whid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlWeek", $scope.temp.ddlWeek);
                formData.append("txtTimeFrom", $scope.txtTimeFrom);
                formData.append("txtTimeTo", $scope.txtTimeTo);
                formData.append("chkClosed", $scope.temp.chkClosed);
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
                $scope.getWorkingHours();
                // $scope.clear();
                $scope.temp.txtTimeFrom='';
                $scope.temp.txtTimeTo='';
                $scope.temp.whid='';
                $scope.temp.chkClosed =false;
                document.getElementById("txtTimeFrom").focus();
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


     /* ========== GET Working Hours =========== */
     $scope.getWorkingHours = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getWorkingHours'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getWorkingHours = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getWorkingHours(); --INIT
    

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




    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("ddlLocation").focus();

        $scope.temp = {
            whid:id.WHID,
            ddlLocation:(id.LOCID).toString(),
            ddlWeek: id.WDAY_NAME,
            txtTimeFrom: id.CLOSED == 0 ? $scope.SetTime(id.TIME_FROM) : '',
            txtTimeTo: id.CLOSED == 0 ? $scope.SetTime(id.TIME_TO) : '',
            chkClosed: id.CLOSED == 1 ? true : false,
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getWorkingHours.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        document.getElementById("ddlLocation").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.chkClosed =false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'whid': id.WHID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getWorkingHours.indexOf(id);
		            $scope.post.getWorkingHours.splice(index, 1);
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