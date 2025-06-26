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
    $scope.editMode = false;
    $scope.Page = "STUDENT";
    $scope.PageSub = "STUDENT_TEACHER_DOCS";
    $scope.serial = 1;
    $scope.files = [];

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
        var objtype = !$scope.temp.ddlDocType?'':$scope.temp.ddlDocType;
        $scope.objectAccept = objtype == 'IMAGE' ? '.jpg, .jpeg, .png' : objtype == 'VIDEO' ? 'video/*' : objtype == 'PDF' ? 'application/pdf' : '';
        $scope.maxSize = objtype == 'IMAGE' ? '(Max : 1mb)' : objtype == 'VIDEO' ? '' : objtype == 'PDF' ? '(Max : 2mb)' : '';
    }


    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Student_Teacher_Docs.php';

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
                // window.location.assign("dashboard.html");
                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getLocations();
                    // $scope.getProduct();
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

    $scope.save = function(){
        $(".btn-save").attr('disabled', true).text('Saving...');
        $(".btn-update").attr('disabled', true).text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.pictureUpload = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("stdid", $scope.temp.stdid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlDocFor", $scope.temp.ddlDocFor);
                formData.append("ddlDocForID", $scope.temp.ddlDocForID);
                formData.append("ddlDocType", $scope.temp.ddlDocType);
                formData.append("txtDocDesc", $scope.temp.txtDocDesc);
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
                $scope.messageSuccess(data.data.message);
                $scope.getStudentTeacherDocs();
                $scope.clearForm();
                
                document.getElementById("ddlDocType").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').attr('disabled', false).text('SAVE');
            $('.btn-update').attr('disabled', false).text('UPDATE');
        });
    }


     /* ========== GET DATA =========== */
     $scope.getStudentTeacherDocs = function () {
         $('#SpinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getStudentTeacherDocs');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlDocFor", $scope.temp.ddlDocFor);
                formData.append("ddlDocForID", $scope.temp.ddlDocForID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
            then(function (data, status, headers, config) {
                console.log(data.data);
                $scope.post.getStudentTeacherDocs = data.data.data;
                $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    


    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("ddlDocType").focus();
        $scope.temp.stdid = id.STDID;
        // $scope.temp.ddlLocation = id.LOCID;
        $scope.temp.ddlDocFor = id.DOCFOR;
        $scope.temp.ddlDocType = id.DOCTYPE;
        $scope.temp.txtDocDesc = id.DOCDESC;
        $scope.temp.pictureUpload = id.DOCUMENT;
        $scope.temp.existingPictureUpload = id.DOCUMENT;

        if($scope.temp.ddlLocation>0 && $scope.temp.ddlDocFor!==''){
            $scope.getTeacherStudent();
            $timeout(()=>{
                $scope.temp.ddlDocForID = id.DOCFORID.toString();
            },1000);
        }

        $scope.objectTypeChange();

        $scope.Img_src= id.DOCUMENT != '' ? 'images/student_teacher_docs/'+id.DOCUMENT : '';
        $scope.editMode = true;
        $scope.index = $scope.post.getStudentTeacherDocs.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlDocType").focus();
        // $scope.temp={};
        $scope.temp.ddlDocType='';
        $scope.temp.txtDocDesc='';
        $scope.temp.stdid=0;
        $scope.editMode = false;
        $scope.clearImg_src();
    }



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STDID': id.STDID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentTeacherDocs.indexOf(id);
		            $scope.post.getStudentTeacherDocs.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    

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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.post.user[0]['LOCID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getStudentTeacherDocs();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


    /* ========== GET STUDENT TEACHERS =========== */
    $scope.getTeacherStudent = function () {
        $scope.post.getTeacherByLocation = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlDocFor) return;
        $scope.spinTeacher =  true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTeacherStudent','LOCID':$scope.temp.ddlLocation,'ddlDocFor':$scope.temp.ddlDocFor}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTeacherByLocation = data.data.success ? data.data.data : [];
            $scope.spinTeacher =  false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ========== GET STUDENT TEACHERS =========== */


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