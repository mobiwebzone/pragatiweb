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
    $scope.Page = "COLLEGE_APP";
    $scope.PageSub = "CA_MASTER";
    $scope.PageSub1 = "COLLEGE_MASTER";
    $scope.editMode = false;
    
    var url = 'code/College_Master_code.php';



    

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
                    $scope.getColleges();
                    $scope.getUniversity();
                    $scope.getCountries();
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

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("clid", $scope.temp.clid);
                formData.append("ddlUniversity", $scope.temp.ddlUniversity);
                formData.append("txtCollegeName", $scope.temp.txtCollegeName);
                formData.append("txtAddLine1", $scope.temp.txtAddLine1);
                formData.append("txtAddLine2", $scope.temp.txtAddLine2);
                formData.append("txtCity", $scope.temp.txtCity);
                formData.append("txtState", $scope.temp.txtState);
                formData.append("txtZipcode", $scope.temp.txtZipcode);
                formData.append("ddlCountry", $scope.temp.ddlCountry);
                formData.append("txtPhone", $scope.temp.txtPhone);
                formData.append("txtEmail", $scope.temp.txtEmail);
                formData.append("txtWebsite", $scope.temp.txtWebsite);
                formData.append("txtCountyRank", $scope.temp.txtCountyRank);
                formData.append("txtInternationalRank", $scope.temp.txtInternationalRank);
                formData.append("ddlCollegeType", $scope.temp.ddlCollegeType);
                formData.append("txtCollegeStrength", $scope.temp.txtCollegeStrength);
                formData.append("txtAnuuTuiInState", $scope.temp.txtAnuuTuiInState);
                formData.append("txtAnuuTuiOutOfState", $scope.temp.txtAnuuTuiOutOfState);
                formData.append("txtAnuuTuiInternational", $scope.temp.txtAnuuTuiInternational);
                formData.append("txtLodging", $scope.temp.txtLodging);
                formData.append("txtFood", $scope.temp.txtFood);
                formData.append("txtPerOfStInState", $scope.temp.txtPerOfStInState);
                formData.append("txtPerOfStOutOfState", $scope.temp.txtPerOfStOutOfState);
                formData.append("txtPerOfStInternational", $scope.temp.txtPerOfStInternational);
                formData.append("txtRemark", $scope.temp.txtRemark);
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
                $scope.getColleges();
                $scope.clearForm();
                
                $("#ddlUniversity").focus();
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



    /* ========== GET COLLEGES =========== */
     $scope.getColleges = function () {
         $('#SpinColleges').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getColleges'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getColleges = data.data.data;
            }else{
                $scope.post.getColleges = [];
            }
            $('#SpinColleges').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getColleges(); --INIT
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


    /* ========== GET Countries =========== */
    $scope.getCountries = function () {
        $('.spinCountry').show();
        $http({
            method: 'post',
            url: 'code/Countries_code.php',
            data: $.param({ 'type': 'getCountries'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCountry = data.data.data;
            $('.spinCountry').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCountries(); --INIT
    /* ========== GET Countries =========== */


    /* ============ Edit Button ============= */ 
    $scope.editDate = function (id) {
        $("#ddlUniversity").focus();
        $scope.temp = {
            clid:id.CLID,
            ddlUniversity: (id.UNIVERSITYID).toString(),
            txtCollegeName : id.COLLEGE,
            txtAddLine1 : id.ADDRESSLINE1,
            txtAddLine2 : id.ADDRESSLINE2,
            txtCity : id.CITY,
            txtState : id.STATE,
            txtZipcode : id.ZIPCODE,
            ddlCountry : id.COUNTRYID>0?(id.COUNTRYID).toString():'',
            txtPhone : id.PHONE>0?Number(id.PHONE):'',
            txtEmail : id.EMAILID,
            txtWebsite : id.WEBSITE,
            txtCountyRank : Number(id.COUNTRY_RANK),
            txtInternationalRank : Number(id.INTERNATIONAL_RANK),
            ddlCollegeType : (id.COLLEGE_TYPE && id.COLLEGE_TYPE!='')?id.COLLEGE_TYPE:'',
            txtCollegeStrength : id.STRENGTH>0?Number(id.STRENGTH):'',
            txtAnuuTuiInState : Number(id.ANNUAL_TUITION_INSTATE),
            txtAnuuTuiOutOfState : Number(id.ANNUAL_TUITION_OUTSTATE),
            txtAnuuTuiInternational : Number(id.ANNUAL_TUITION_INTERNATIONAL),
            txtLodging : Number(id.LODGING),
            txtFood : Number(id.FOOD),
            txtPerOfStInState : Number(id.PEROFSTUDENT_INSTATE),
            txtPerOfStOutOfState : Number(id.PEROFSTUDENT_OUTSTATE),
            txtPerOfStInternational : Number(id.PEROFSTUDENT_INTERNATIONAL),
            txtRemark : id.REMARKS,
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getColleges.indexOf(id);
    }
    /* ============ Edit Button ============= */ 

    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlUniversity").focus();
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
                data: $.param({ 'CLID': id.CLID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getColleges.indexOf(id);
		            $scope.post.getColleges.splice(index, 1);
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