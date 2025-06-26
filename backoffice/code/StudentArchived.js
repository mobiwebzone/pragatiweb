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
    $scope.formTitle = '';
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_LOGINA";
    $scope.temp.txtDisplayColor="#000000";
    
    var url = 'code/StudentArchived.php';



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
            console.log(data.data);
            if (data.data.success) {
                $scope.post.user = data.data.data;
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;
                $scope.locid=data.data.locid;
               

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getPlans();
                  
                    $scope.getschoolname();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
            }
            else {
               
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



    $scope.getStudentid = function(x,FOR){
        $scope.FOR = FOR;
        $scope.STUDENT_ID = x.STUDENT_ID;
        if(FOR=='UNARCHIVED'){
            var r = confirm("Are you sure want to unarchive this student!");
            if (r == true) {
                $scope.archiveUnarchiveStudent()        
            }
        }
    }

    $scope.archiveUnarchiveStudent = function(){
        console.log($scope.STUDENT_ID);
        console.log($scope.FOR);
        // return;
        $(".btn-save").attr('disabled', true).text('Saving...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'archiveUnarchiveStudent');
                formData.append("STUDENT_ID", $scope.STUDENT_ID);
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
                $scope.getStudentByName();
                $scope.getArchivedStuent();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-save").attr('disabled', false).text('SAVE');
        });
    }

     /* ========== GET STUDENT =========== */
     $scope.getStudentByName = function () {
        
         $scope.SpinSearchST = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getStudentByName',
             'TEXT_SCHOOL_ID':$scope.temp.TEXT_SCHOOL_ID,
             'txtSearchStudent':$scope.temp.txtSearchStudent}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getStudentByName = data.data.success ? data.data.data : [];
                $scope.SpinSearchST = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
   

    
    /* ========== GET ARCHIVED STUDENT =========== */
    $scope.getArchivedStuent = function () {
         $scope.SpinArchivedST = true;
         $http({
             method: 'post',
             url: url,
             data: $.param({ 'type': 'getArchivedStuent',
             'TEXT_SCHOOL_ID':$scope.temp.TEXT_SCHOOL_ID}),
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                $scope.post.getArchivedStuent = data.data.success ? data.data.data : [];
                $scope.SpinArchivedST = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getArchivedStuent();



    /* ========== DELETE =========== */
    $scope.deleteProduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'productid': id.PRODUCT_ID, 'type': 'deleteProduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getProduct.indexOf(id);
		            $scope.post.getProduct.splice(index, 1);
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