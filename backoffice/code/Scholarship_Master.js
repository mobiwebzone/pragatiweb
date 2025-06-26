$postModule = angular.module("myApp", ["ngSanitize"]);
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
    $scope.Page = "COLLEGE_APP";
    $scope.PageSub = "CA_MASTER";
    $scope.PageSub1 = "SCHOLARSHIP_MASTER";
    $scope.editMode = false;
    
    var url = 'code/Scholarship_Master.php';

    /* ============ CHECK SESSION ============= */ 
    $scope.init = function () {
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
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getUniversity();
                    $scope.getCollegeMajor(); 
                    $scope.getScholarships();
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }
    /* ============ CHECK SESSION ============= */ 






    /* ============ SAVE DATA ============= */ 
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("schmid", $scope.temp.schmid);
                formData.append("txtScholarship", $scope.temp.txtScholarship);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("ddlCollege", $scope.temp.ddlCollege);
                formData.append("ddlCollegeMajor", $scope.temp.ddlCollegeMajor);
                formData.append("txtLink", $scope.temp.txtLink);
                formData.append("txtComments", $scope.temp.txtComments);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }        
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getScholarships();
                $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#txtScholarship").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ============ SAVE DATA ============= */ 





     /* ========== GET SCHOLARSHIPS =========== */
     $scope.getScholarships = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getScholarships'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getScholarships = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getScholarships(); --INIT
    /* ========== GET SCHOLARSHIPS =========== */

   




   /* ========== GET UNIVERSITY =========== */
   $scope.getUniversity = function () {
       $('.spinUniversity').show();
       $http({
           method: 'post',
           url: 'code/University_Master_code.php',
           data: $.param({ 'type': 'getUniversity'}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
            $scope.post.getUniversity = data.data.success ? data.data.data : [];
           $('.spinUniversity').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getUniversity(); --INIT
   /* ========== GET UNIVERSITY =========== */


    


    /* ========== GET COLLEGES =========== */
    $scope.getCollegeByUniversity = function () {
        $('.spinCollege').show();
         $http({
             method: 'post',
            url: 'code/Student_Final_Result_code.php',
            data: $.param({ 'type': 'getCollegeByUniversity','UNIVERSITYID':$scope.temp.ddlUniversity}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCollegeByUniversity = data.data.success ? data.data.data : [];
            $('.spinCollege').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
   }
   // $scope.getCollegeByUniversity();
   /* ========== GET COLLEGES =========== */





   /* ========== GET COLLEGE MAJOR =========== */
   $scope.getCollegeMajor = function () {
    $('.spinCollegeMajor').show();
    $http({
        method: 'post',
        url: 'code/College_Major_Master_code.php',
        data: $.param({ 'type': 'getCollegeMajor'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCollegeMajor = data.data.success?data.data.data:[];
        $('.spinCollegeMajor').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getCollegeMajor(); --INIT
    /* ========== GET COLLEGE MAJOR =========== */
    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#txtScholarship").focus();
    
        $scope.temp = {
            schmid:id.SCHMID,
            txtScholarship:id.SCHOLARSHIP,
            ddlUniversity:(id.UNIVERSITYID && id.UNIVERSITYID>0)?id.UNIVERSITYID.toString():'',
            // ddlCollege:(id.CLID && id.CLID>0)?id.CLID.toString():'',
            ddlCollegeMajor:(id.MAJORID && id.MAJORID>0)?id.MAJORID.toString():'',
            txtLink:id.SCHOLARSHIPLINK,
            txtComments:id.COMMENTS
        };

        if($scope.temp.ddlUniversity > 0 && id.UNIVERSITYID>0){
            $scope.getCollegeByUniversity();
            $timeout(()=>{$scope.temp.ddlCollege=(id.CLID && id.CLID>0)?id.CLID.toString():'';},500);
        }

        $scope.editMode = true;
        $scope.index = $scope.post.getScholarships.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtScholarship").focus();
        $scope.temp={};
        $scope.editMode = false;
    }
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SCHMID': id.SCHMID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getScholarships.indexOf(id);
		            $scope.post.getScholarships.splice(index, 1);
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



    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-success').show();
        });
        jQuery('.alert-success').delay(1000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').slideDown(700,'easeOutBounce',function () {
            jQuery('.alert-danger').show();
        });
        jQuery('.alert-danger').delay(5000).slideUp(700,'easeOutBounce',function () {
            jQuery('.alert-danger > span').html('');
        });
    }




});