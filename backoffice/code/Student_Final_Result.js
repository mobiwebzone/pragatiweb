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
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_FINAL_RESULT";
    $scope.editMode = false;
    
    var url = 'code/Student_Final_Result_code.php';



    

    /* ========== CHECK SESSION =========== */
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
                
                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getPlans();
                    $scope.getProduct();
                    $scope.getUniversity();
                    $scope.getCollegeMajor();
                    $scope.getStudentFinalResult();
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
    /* ========== CHECK SESSION =========== */


    
    

    /* ========== SAVE DATA =========== */
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        
        $scope.txtStudiedFromDT = ($scope.temp.txtStudiedFromDT == '' || $scope.temp.txtStudiedFromDT == undefined) ? '' : $scope.temp.txtStudiedFromDT.toLocaleString('sv-SE');
        $scope.txtStudiedToDT = ($scope.temp.txtStudiedToDT == '' || $scope.temp.txtStudiedToDT == undefined) ? '' : $scope.temp.txtStudiedToDT.toLocaleString('sv-SE');
        
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("sfrid", $scope.temp.sfrid);
                formData.append("ddlStudentType", $scope.temp.ddlStudentType);
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("txtFirstName", $scope.temp.txtFirstName);
                formData.append("txtLastName", $scope.temp.txtLastName);
                formData.append("ddlProduct1", $scope.temp.ddlProduct1);
                formData.append("ddlProduct2", $scope.temp.ddlProduct2);
                formData.append("txtStudiedFromDT", $scope.txtStudiedFromDT);
                formData.append("txtStudiedToDT", $scope.txtStudiedToDT);
                formData.append("ddlSelected", $scope.temp.ddlSelected);
                formData.append("txtSuperScore", $scope.temp.txtSuperScore);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("ddlCollege", $scope.temp.ddlCollege);
                formData.append("ddlCollegeMajor", $scope.temp.ddlCollegeMajor);
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("ShowInHome", $scope.temp.ShowInHome ? 1 : 0);
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
                $scope.getStudentFinalResult();
                $scope.clearForm();
                
                $("#ddlStudentType").focus();
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
    /* ========== SAVE DATA =========== */



    /* ========== GET STUDENT FINAL RESULT =========== */
     $scope.getStudentFinalResult = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentFinalResult'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentFinalResult = data.data.data;
            }else{
                $scope.post.getStudentFinalResult = [];
            }
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentFinalResult() --INIT
    /* ========== GET STUDENT FINAL RESULT =========== */



    /* ========== GET COLLEGES =========== */
    $scope.getCollegeByUniversity = function () {
         $('.spinCollege').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getCollegeByUniversity','UNIVERSITYID':$scope.temp.ddlUniversity}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getCollegeByUniversity = data.data.data;
            }else{
                $scope.post.getCollegeByUniversity = [];
            }
            $('.spinCollege').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCollegeByUniversity()
    /* ========== GET COLLEGES =========== */
    


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
            if(data.data.success){
                $scope.post.getUniversity = data.data.data;
            }else{
                $scope.post.getUniversity = [];
            }
            $('.spinUniversity').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getUniversity(); --INIT
    /* ========== GET UNIVERSITY =========== */


    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getPlan = data.data.data;
            }else{
                $scope.post.getPlan = [];
            }
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */


    /* ========== GET STUDENT BY PLAN =========== */
    $scope.getStudentByPlan = function () {
        $('.spinStudent').show();
        $scope.temp.txtFirstName='';
        $scope.temp.txtLastName='';
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByPlan','PLANID':$scope.temp.ddlPlan}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentByPlan = data.data.data;
            }else{
                $scope.post.getStudentByPlan = [];
            }
            $('.spinStudent').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentByPlan();
    /* ========== GET STUDENT BY PLAN =========== */



    /* ========== GET PRODUCT =========== */
    $scope.getProduct = function () {
        $('.spinProduct').show();
        $http({
            method: 'post',
            url: 'code/Products_code.php',
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
            $('.spinProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT
    /* ========== GET PRODUCT =========== */


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
           if(data.data.success){
               $scope.post.getCollegeMajor = data.data.data;
           }else{
               $scope.post.getCollegeMajor = [];
           }
           $('.spinCollegeMajor').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getCollegeMajor(); --INIT
   /* ========== GET COLLEGE MAJOR =========== */
    
    
    
    /* ========== SET STUDENT NAME =========== */
    $scope.setStudentName = ()=>{
        $scope.temp.txtFirstName='';
        $scope.temp.txtLastName='';
        var fname = $scope.post.getStudentByPlan.filter((x)=> x.REGID == Number($scope.temp.ddlStudent)).map((x)=> x.FIRSTNAME);
        var lname = $scope.post.getStudentByPlan.filter((x)=> x.REGID == Number($scope.temp.ddlStudent)).map((x)=> x.LASTNAME);
        // console.log(fname);
        $scope.temp.txtFirstName = fname.length>0?fname:'';
        $scope.temp.txtLastName = lname.length>0?lname:'';
    }
    /* ========== SET STUDENT NAME =========== */



    /* ============ Edit Button ============= */ 
    $scope.editDate = function (id) {
        $scope.post.getCollegeByUniversity = [];
        $("#ddlStudentType").focus();
        $scope.temp = {
            sfrid : id.SFRID,
            ddlStudentType : id.STUDENT_TYPE,
            ddlPlan : id.STUDENT_TYPE === 'REGISTERED' ? (id.PLANID).toString() : '',
            ddlProduct1 : (id.PRODUCTID1).toString(),
            ddlProduct2 : id.PRODUCTID2>0?(id.PRODUCTID2).toString():'',
            txtStudiedFromDT : id.STUDIED_FROM == '' ? '' : new Date(id.STUDIED_FROM),
            txtStudiedToDT : id.STUDIED_UPTO == '' ? '' : new Date(id.STUDIED_UPTO),
            ddlSelected : id.SELECTED==''?'':id.SELECTED,
            txtSuperScore : Number(id.SUPERSCORE),
            ddlUniversity : id.UNIVERSITYID>0?(id.UNIVERSITYID).toString():'',
            ddlCollegeMajor : id.MAJORID>0?(id.MAJORID).toString():'',
        };
        $scope.getStudentByPlan();
        $timeout(()=>{$scope.temp.ddlStudent=(id.REGID).toString();},700);

        $scope.getCollegeByUniversity();
        $timeout(()=>{$scope.temp.ddlCollege=id.CLID>0?(id.CLID).toString():'';},1000);
        $scope.temp.txtFirstName = id.FIRSTNAME;
        $scope.temp.txtLastName = id.LASTNAME;
        $scope.temp.txtRemark = id.REMARK;
        $scope.temp.ShowInHome = id.SHOW_IN_HOMEPAGE==0?false:true;

        $scope.editMode = true;
        $scope.index = $scope.post.getStudentFinalResult.indexOf(id);
    }
    /* ============ Edit Button ============= */ 

    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlStudentType").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getCollegeByUniversity = [];
        $scope.temp.ShowInHome = false;
    } 
    /* ============ Clear Form =========== */ 



    /* ========== DELETE =========== */
    $scope.deleteData = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SFRID': id.SFRID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentFinalResult.indexOf(id);
		            $scope.post.getStudentFinalResult.splice(index, 1);
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