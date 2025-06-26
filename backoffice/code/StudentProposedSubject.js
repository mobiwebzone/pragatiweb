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
    $scope.PageSub = "ST_PROPOSED_SUB";
    $scope.temp.txtDate = new Date();
    $scope.temp.txtForYear = Number(new Date().getFullYear());
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/StudentProposedSubject.php';


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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    $scope.getClassSubject();
                }
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

    $scope.save = function(){
        if(!$scope.ADDMODE_ON)$(".btn-save").attr('disabled', 'disabled').text('Saving...');
        if($scope.ADDMODE_ON)$(".btnAdd").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.grade = (!$scope.ADDMODE_ON) ? $scope.temp.txtGrade : $scope.GRADE_ADD;
        $scope.year = (!$scope.ADDMODE_ON) ? new Date().getFullYear() : $scope.YEAR_ADD;
        $scope.ClassSubject = (!$scope.ADDMODE_ON) ? $scope.temp.ddlClassSubject : $scope.temp.ddlClassSubjectADD;
        $scope.Draf_Final = (!$scope.ADDMODE_ON) ? $scope.temp.chkDraf_Final : $scope.temp.chkDraf_FinalADD;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("gsid", $scope.temp.gsid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("txtGrade", $scope.grade);
                formData.append("txtClassOf", $scope.temp.txtClassOf);
                formData.append("year", $scope.year);
                formData.append("ddlClassSubject", $scope.ClassSubject);
                formData.append("chkDraf_Final", $scope.Draf_Final);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getGradeSubject();

                if($scope.ADDMODE_ON){
                    $scope.temp.ddlClassSubjectADD='';
                    $scope.temp.chkDraf_FinalADD='';
                }
                // $scope.clear();
                $("#ddlStudent").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            if(!$scope.ADDMODE_ON)$('.btn-save').removeAttr('disabled').text('SAVE');
            if($scope.ADDMODE_ON)$('.btnAdd').removeAttr('disabled').text('SAVE');
            $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }


    /* ========== Insert Next Year =========== */
    $scope.InsertNextYear = function(id,index){
        $("#btnnextYear"+index+"").attr('disabled', 'disabled').text('Next Year...');
        // $(".btn-update").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'InsertNextYear123');
                formData.append("MAIN_DATA", JSON.stringify(id.MAIN_DATA));
                formData.append("GRADE", id.GRADE);
                formData.append("YEAR", id.YEAR);
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
                $scope.getGradeSubject();
                // $scope.clear();
                document.getElementById("ddlStudent").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $("#btnnextYear"+index+"").removeAttr('disabled').text('Next Year >>');
            // $('.btn-update').removeAttr('disabled').text('UPDATE');
        });
    }
    /* ========== Insert Next Year =========== */


    /* ============ UPDATE DRAFT/FINAL =========== */
    $D = 0;
    $F = 0;
    $scope.changeDraftFinal = function(id,FOR){
        console.log(id);
        console.log(FOR);
        $(".btn-DF").attr('disabled', 'disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'changeDraftFinal');
                formData.append("GSID", id.GSID);
                formData.append("REGID", id.REGID);
                formData.append("DRAFT", id.DRAFT);
                formData.append("FINAL", id.FINAL);
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
                $scope.getGradeSubject();
                // $scope.clear();
                // document.getElementById("ddlStudent").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-DF").removeAttr('disabled');
        });
    }
    /* ============ UPDATE DRAFT/FINAL =========== */


     /* ========== GET GRADE SUBJECT =========== */
     $scope.getGradeSubject = function () {
        $('.spinGradeSub').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getGradeSubject','REGID':$scope.temp.ddlStudent}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGradeSubject = data.data.success ? data.data.FINAL : [];
            $('.spinGradeSub').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getGradeSubject(); --INIT
    /* ========== GET GRADE SUBJECT =========== */
    
    
    
    
    /* ========== OPEN ADD SUBJECT MODAL =========== */
    $scope.ADDMODE_ON = false;
    $scope.openAddSubModal=function(id){    
        $scope.AddModalClear();
        $scope.ADDMODE_ON = true;
        $scope.GRADE_ADD = id.GRADE;
        $scope.YEAR_ADD = id.YEAR;
    }
    $scope.AddModalClear=function(){
        $scope.ADDMODE_ON = false;
        $scope.GRADE_ADD = '';
        $scope.YEAR_ADD = '';
        $scope.temp.ddlClassSubjectADD = '';
        $scope.temp.chkDraf_FinalADD = '';
    }
    /* ========== OPEN ADD SUBJECT MODAL =========== */
    






    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("txtForYear").focus();

        $scope.temp = {
            gsid:id.GSID,
            ddlLocation:id.LOCID.toString(),
            txtGrade:Number(id.GRADE),
            txtClassOf:Number(id.GRYEAR),
            ddlClassSubject:id.CSUBID.toString(),
            chkDraf_Final:id.DRAFT>0?'DRAFT':(id.FINAL>0?'FINAL':'')
        };
        if($scope.temp.ddlLocation > 0){
            $scope.temp.ddlStudent=id.REGID.toString();
            if($scope.temp.ddlStudent > 0)$scope.getGradeSubject();
        }
        

        $scope.editMode = true;
        $scope.index = $scope.post.getHolidays.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $('#ddlStudent').focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.post.getGradeSubject=[];
        $scope.AddModalClear();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'GSID': id.GSID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            // var index = $scope.post.getHolidays.indexOf(id);
		            // $scope.post.getHolidays.splice(index, 1);
                    $scope.getGradeSubject();
                    // $scope.clear();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    




    /* ######################################################################################################################### */
    /*                                          GET EXTRA DATA START                                                             */
    /* ######################################################################################################################### */
        
    
        
    
        /* ========== GET STUDENT BY LOCATION =========== */
        $scope.getStudentByLoc = function () {
            $('.spinUser').show();
            $http({
                method: 'post',
                url: 'code/StudentApplication.php',
                data: $.param({ 'type': 'getStudentByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if(data.data.success){
                    $scope.post.getStudentByLoc = data.data.data;
                    // if($scope.editMode)$timeout(()=>{if($scope.temp.ddlStudent>0)$scope.setStudentDetails()},500);
                }else{
                    $scope.post.getStudentByLoc = [];
                }
                $('.spinUser').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        // $scope.getStudentByLoc();
        /* ========== GET STUDENT BY LOCATION =========== */

        

        /* ========== GET Location =========== */
        $scope.getLocations = function () {
            $scope.post.getStudentByLoc = [];
            $scope.post.getLocReviewByLoc = [];
            $('.spinLoc').show();
            $http({
                method: 'post',
                url: 'code/Users_code.php',
                data: $.param({ 'type': 'getLocations'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getLocations = data.data.data;
                if($scope.post.getLocations.length>0){
                    $timeout(()=>{
                        $scope.temp.ddlLocation = ($scope.post.getLocations.length==1) ? $scope.post.getLocations[0]['LOC_ID'].toString() : '';
                        if($scope.post.getLocations.length==1){
                            $scope.getStudentByLoc(); 
                            // $scope.getLocReviewByLoc();
                        }
                    },1000);
                }
                $('.spinLoc').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        // $scope.getLocations(); --INIT
        /* ========== GET Location =========== */

        
        
        
        
        /* ========== SET STUDENT DETAILS =========== */
        $scope.GRADE_CLASS_EXIST = false;
        $scope.setStudentDetails = function(){
            let details=[];
            if($scope.temp.ddlStudent || $scope.temp.ddlStudent > 0){
                details = $scope.post.getStudentByLoc.filter((x)=>x.REGID == $scope.temp.ddlStudent);
                $scope.temp.txtGrade = details[0]['GRADE'];
                $scope.temp.txtClassOf = Number(details[0]['CLASSOF']);
            }else{
                $scope.temp.txtGrade = '';
                $scope.temp.txtClassOf = '';
                $scope.GRADE_CLASS_EXIST = false;
            }

            $scope.GRADE_CLASS_EXIST = (details[0]['GRADE']) == '' ? false : true;
            if(!$scope.GRADE_CLASS_EXIST){
                $scope.messageFailure('Please Update Grade of Student.');
                $('#txtGrade').addClass('bg-danger text-light');
                $timeout(()=>{$('#txtGrade').removeClass('bg-danger text-light');},2000);
                return;
            }
            $scope.GRADE_CLASS_EXIST = Number(details[0]['CLASSOF']) <= 0 ? false : true;
            if(!$scope.GRADE_CLASS_EXIST){
                $scope.messageFailure('Please Update Class of Student.');
                $('#txtClassOf').addClass('bg-danger text-light');
                $timeout(()=>{$('#txtClassOf').removeClass('bg-danger text-light');},2000);
                return;
            }
            if((details[0]['GRADE']) != '' && Number(details[0]['CLASSOF']) > 0) {
                $scope.getGradeSubject();
            }else{
                $scope.post.getGradeSubject=[];
            }
            // $scope.GRADE_CLASS_EXIST = (details[0]['GRADE'] == '' || Number(details[0]['CLASSOF']) <= 0) ? false : true;
            // if(!$scope.GRADE_CLASS_EXIST) $scope.messageFailure('Please Update Grade or Class of Student.');
        }
        /* ========== SET STUDENT DETAILS =========== */


    
        
    
        /* ========== GET CLASS/SUBJECT =========== */
        $scope.getClassSubject = function () {
            $('.spinCS').show();
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getClassSubject'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                $scope.post.getClassSubject = data.data.success?data.data.data:[];
                $('.spinCS').hide();
            },
            function (data, status, headers, config) {
                console.log('Failed');
            })
        }
        // $scope.getClassSubject();
        /* ========== GET CLASS/SUBJECT =========== */

    /* ######################################################################################################################### */
    /*                                          GET EXTRA DATA END                                                             */
    /* ######################################################################################################################### */


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



});