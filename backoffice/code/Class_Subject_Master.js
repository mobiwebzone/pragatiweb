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
    $scope.PageSub1 = "CLASS_SUBJECT_MASTER";
    $scope.editMode = false;
    
    var url = 'code/Class_Subject_Master.php';

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
                    $scope.getClassTypes();
                    $scope.getClassSubject();
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
                formData.append("csubid", $scope.temp.csubid);
                formData.append("ddlClassType", $scope.temp.ddlClassType);
                formData.append("txtClassTypeOther", $scope.temp.txtClassTypeOther);
                formData.append("txtCredit", $scope.temp.txtCredit);
                formData.append("ddlSemesterClass", $scope.temp.ddlSemesterClass);
                formData.append("ddlVirtualClass", $scope.temp.ddlVirtualClass);
                formData.append("txtShortDesc", $scope.temp.txtShortDesc);
                formData.append("txtLongDesc", $scope.temp.txtLongDesc);
                formData.append("ddlPreReq1", $scope.temp.ddlPreReq1);
                formData.append("ddlPreReq2", $scope.temp.ddlPreReq2);
                formData.append("ddlPreReq3", $scope.temp.ddlPreReq3);
                formData.append("ddlNextClass1", $scope.temp.ddlNextClass1);
                formData.append("ddlNextClass2", $scope.temp.ddlNextClass2);
                formData.append("ddlNextClass3", $scope.temp.ddlNextClass3);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }         
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.getClassSubject();
                $scope.clearForm();
                $scope.messageSuccess(data.data.message);
                
                $("#ddlClassType").focus();
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
    /* ============ SAVE DATA ============= */ 





     /* ========== GET CLASS/SUBJECT =========== */
     $scope.getClassSubject = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
             url: url,
            data: $.param({ 'type': 'getClassSubject'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getClassSubject = data.data.success ? data.data.data : [];
             $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getClassSubject(); --INIT
    /* ========== GET CLASS/SUBJECT =========== */
    


    /* ============ Edit Button ============= */ 
    $scope.editData = function (id) {
        $("#ddlClassType").focus();
        $scope.temp = {
            csubid:id.CSUBID,
            ddlClassType:(id.CLASSTYPEID).toString(),
            txtClassTypeOther:id.CLASSTYPEID_OTHER,
            txtCredit:Number(id.CREDIT),
            ddlSemesterClass:(id.SEMESTER_CLASS && id.SEMESTER_CLASS!='') ? id.SEMESTER_CLASS : '',
            ddlVirtualClass:(id.VIRTUAL_CLASS && id.VIRTUAL_CLASS!='') ? id.VIRTUAL_CLASS : '',
            txtShortDesc:id.SHORT_DESC,
            txtLongDesc:id.LONG_DESC,
            ddlPreReq1:(id.PREREQ1 && id.PREREQ1>0) ? (id.PREREQ1).toString() : '',
            ddlPreReq2:(id.PREREQ2 && id.PREREQ2>0) ? (id.PREREQ2).toString() : '',
            ddlPreReq3:(id.PREREQ3 && id.PREREQ3>0) ? (id.PREREQ3).toString() : '',
            ddlNextClass1:(id.NEXTCLASS1 && id.NEXTCLASS1>0) ? (id.NEXTCLASS1).toString() : '',
            ddlNextClass2:(id.NEXTCLASS2 && id.NEXTCLASS2>0) ? (id.NEXTCLASS2).toString() : '',
            ddlNextClass3:(id.NEXTCLASS3 && id.NEXTCLASS3>0) ? (id.NEXTCLASS3).toString() : '',
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getClassSubject.indexOf(id);
    }
    /* ============ Edit Button ============= */ 


    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlClassType").focus();
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
                data: $.param({ 'CSUBID': id.CSUBID, 'type': 'deleteData' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getClassSubject.indexOf(id);
		            $scope.post.getClassSubject.splice(index, 1);
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


/* ######################################################################################################################### */
/*                                          GET EXTRA DATA START                                                             */
/* ######################################################################################################################### */


    /* ========== SET CLASS TYPE OTHER =========== */
    $scope.setClassTypeOther=()=>{
        var ClassTypeName = $scope.post.getClassTypes.filter((x)=>Number(x.CLASSTYPEID)==Number($scope.temp.ddlClassType)).map((x)=>x.CLASS_TYPE).toString();
        $scope.temp.txtClassTypeOther = ClassTypeName == 'Other' ? '' : ClassTypeName;
    }
    /* ========== SET CLASS TYPE OTHER =========== */



    /* ========== GET CLASS TYPES =========== */
    $scope.getClassTypes = function () {
        $('.spinClassType').show();
        $http({
            method: 'post',
            url: 'code/Class_Type_Master.php',
        data: $.param({ 'type': 'getClassTypes'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getClassTypes = data.data.success ? data.data.data : [];
            $('.spinClassType').hide();
    },
    function (data, status, headers, config) {
        console.log('Failed');
    })
    }
    // $scope.getClassTypes(); --INIT
    /* ========== GET CLASS TYPES =========== */



/* ######################################################################################################################### */
/*                                           GET EXTRA DATA END                                                              */
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
    /* ========== MESSAGE =========== */




});