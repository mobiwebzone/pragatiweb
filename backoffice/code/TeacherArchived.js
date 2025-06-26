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
    $scope.temp.txtDisplayColor="#000000";
    
    var url = 'code/TeacherArchived.php';

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
                $scope.locid=data.data.locid;
                // window.location.assign("dashboard.html");

                if($scope.userrole != "TSEC_USER")
                {
                    // $scope.getPlans();
                    $scope.getschoolname();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
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


$scope.getschoolname = function () {
    $scope.post.schoolname = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getschoolname",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getschoolname = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
    };
    
  $scope.getschoolname();



    $scope.getTeacherid = function (x, FOR) {
        $scope.FOR = FOR;
        $scope.TEACHER_ID = x.TEACHER_ID;
        if(FOR=='UNARCHIVED'){
            var r = confirm("Are you sure want to unarchive this teacher!");
            if (r == true) {
                $scope.archiveUnarchiveTeacher()        
            }
        }
    }

    $scope.archiveUnarchiveTeacher = function(){
        console.log($scope.TEACHER_ID);
        console.log($scope.FOR);
        // return;
        $(".btn-save").attr('disabled', true).text('Saving...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'archiveUnarchiveTeacher');
                formData.append("TEACHER_ID", $scope.TEACHER_ID);
                formData.append("txtArcRemark", $scope.txtArcRemark);
                formData.append("FOR", $scope.FOR);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $('#archivedModal').modal('hide');
                $scope.txtArcRemark = '';
                $scope.messageSuccess(data.data.message);
                $scope.getTeacherByName();
                $scope.getArchivedTeacher();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-save").attr('disabled', false).text('SAVE');
        });
    }

     /* ========== GET TEACHER =========== */
     $scope.getTeacherByName = function () {
        // console.log($scope.temp.txtSearchTeacher)
        // if(!$scope.temp.txtSearchTeacher || $scope.temp.txtSearchTeacher=='') return;
         $scope.SpinSearchST = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getTeacherByName',
             'TEXT_SCHOOL_ID':$scope.temp.TEXT_SCHOOL_ID,
             'txtSearchTeacher':$scope.temp.txtSearchTeacher}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getTeacherByName = data.data.success ? data.data.data : [];
                $scope.SpinSearchST = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTeacherByName();

    
    /* ========== GET ARCHIVED TEACHER =========== */
    $scope.getArchivedTeacher = function () {
         $scope.SpinArchived_T = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getArchivedTeacher',
             'TEXT_SCHOOL_ID':$scope.temp.TEXT_SCHOOL_ID}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getArchivedTeacher = data.data.success ? data.data.data : [];
                $scope.SpinArchived_T = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getArchivedTeacher();



    /* ========== DELETE =========== */
    // $scope.deleteProduct = function (id) {
    //     var r = confirm("Are you sure want to delete this record!");
    //     if (r == true) {
    //         $http({
    //             method: 'post',
    //             url: url,
    //             data: $.param({ 'productid': id.PRODUCT_ID, 'type': 'deleteProduct' }),
    //             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //         }).
	// 	    then(function (data, status, headers, config) {
    //             // console.log(data.data)
	// 	        if (data.data.success) {
	// 	            var index = $scope.post.getProduct.indexOf(id);
	// 	            $scope.post.getProduct.splice(index, 1);
	// 	            console.log(data.data.message)
                    
	// 	            $scope.messageSuccess(data.data.message);
	// 	        } else {
	// 	            $scope.messageFailure(data.data.message);
	// 	        }
	// 	    })
    //     }
    // }


    /* ========== GET Location =========== */
    // $scope.getLocations = function () {
    //     $http({
    //         method: 'post',
    //         url: 'code/Users_code.php',
    //         data: $.param({ 'type': 'getLocations'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getLocations = data.data.data;
    //         if($scope.post.getLocations.length>0){
    //             $scope.temp.ddlLocation = $scope.locid.toString();
    //             if($scope.temp.ddlLocation>0) $scope.getArchivedTeacher();
    //             if($scope.temp.ddlLocation>0) $scope.getTeacherByName();
    //         }
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // $scope.getLocations(); --INIT
    
    
    /* ========== GET Plan =========== */
    // $scope.getPlans = function () {
    //     $http({
    //         method: 'post',
    //         url: url,
    //         data: $.param({ 'type': 'getPlans'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getPlans = data.data.data;
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // $scope.getPlans(); --INIT



   
    


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