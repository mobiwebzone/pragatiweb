$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","textAngular"]);
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
// taOptions.toolbar = [
//     ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
//     ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
//     ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
//     ['html', 'insertImage','insertLink', 'insertVideo', 'wordcount', 'charcount']
// ];
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "MISC";
    $scope.PageSub = "STD_TEXTEMAIL";
    $scope.temp.txtETADate = new Date();
    $scope.files = [];
    $scope.txtCommunicationLongDesc = '';
    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
        ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========

    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Standard_TextEmail_code.php';




    // =============== Check Session =============
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

                // $scope.getTDCategory();
                $scope.getLocations();
                // $scope.getStandardTextEmails();
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
    // =============== Check Session =============



    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $scope.temp.txtCatImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("steid", $scope.temp.steid);
                formData.append("ddlSSubCategory", $scope.temp.ddlSSubCategory);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtCommunicationID", $scope.temp.txtCommunicationID);
                formData.append("txtCommunicationShortDesc", $scope.temp.txtCommunicationShortDesc);
                formData.append("txtCommunicationLongDesc", $scope.txtCommunicationLongDesc);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $scope.clearForm();

                $scope.temp.steid='';
                $scope.temp.ddlCategory='';
                $scope.temp.ddlSubCategory='';
                $scope.temp.ddlSSubCategory='';
                $scope.temp.txtCommunicationID='';
                $scope.temp.txtCommunicationShortDesc='';
                $scope.txtCommunicationLongDesc='';
                $scope.editMode = false;
                $scope.post.getTDSubCategory = [];
                $scope.post.getTDSSubCategory = [];
                
                $scope.getStandardTextEmails();
                $("#ddlCategory").focus();
                $scope.messageSuccess(data.data.message);
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
    // =========== SAVE DATA ==============






    /* ========== GET STD TEXT/EMAIL =========== */
    $scope.getStandardTextEmails = function () {
        $scope.post.getStandardTextEmails=[];
        $scope.temp.txtSerarch = undefined;
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#ddlSearchCategory').attr('disabled','disabled');
        $('#SpinnerMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getStandardTextEmails',
                            'ddlSearchCategory': $scope.temp.ddlSearchCategory,
                            'ddlLocation': $scope.temp.ddlLocation
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getStandardTextEmails = data.data.success ? data.data.data : [];

            $scope.refreshData = !$scope.refreshData;
            $('#SpinnerMainData').hide();
            $('#ddlSearchCategory').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStandardTextEmails(); --INIT
    /* ========== GET STD TEXT/EMAIL =========== */




    

    /* ========== GET CATEGORY =========== */
    $scope.getTDCategory = function () {
        $scope.post.getTDCategory = [];
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.spinCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getCategories','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDCategory = data.data.data;
            }else{
                $scope.post.getTDCategory = [];
            }
            $('.spinCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDCategory(); --INIT
    /* ========== GET CATEGORY =========== */




    

    /* ========== GET SUB CATEGORY =========== */
    $scope.getTDSubCategory = function () {
        $scope.post.getTDSSubCategory = [];
        $('.spinSubCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'tdcatid' : $scope.temp.ddlCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDSubCategory = data.data.data;
            }else{
                $scope.post.getTDSubCategory = [];
            }
            $('.spinSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDSubCategory();
    /* ========== GET SUB CATEGORY =========== */




    

    /* ========== GET SUB SUBCATEGORY =========== */
    $scope.getTDSSubCategory = function () {
        $('.spinSSubCat').show();
        $http({
            method: 'post',
            url: 'code/ToDoCategories_code.php',
            data: $.param({ 'type': 'getSSubCategories', 'tdsubcatid' : $scope.temp.ddlSubCategory}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTDSSubCategory = data.data.data;
            }else{
                $scope.post.getTDSSubCategory = [];
            }
            $('.spinSSubCat').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTDSSubCategory();
    /* ========== GET SUB SUBCATEGORY =========== */




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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getTDCategory();
            if($scope.temp.ddlLocation > 0) $scope.getStandardTextEmails();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */







    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#ddlCategory").focus();
        
        $scope.temp.steid = id.STEID;
        $scope.temp.ddlCategory = (id.TDCATID).toString();
        $scope.getTDSubCategory();
        $timeout( () => {
            $scope.temp.ddlSubCategory = (id.TDSUBCATID).toString();
            $scope.getTDSSubCategory();
            $timeout( () => {$scope.temp.ddlSSubCategory = (id.TDSSUBCATID).toString();},500);
        },700);
        
        $scope.temp.ddlLocation = (id.LOCID).toString();
        $scope.temp.txtCommunicationShortDesc = id.COMMUNICATION_SHORTDESC;
        $scope.txtCommunicationLongDesc = id.COMMUNICATION_LONGDESC;
        
        $scope.editMode = true;
        $scope.index = $scope.post.getStandardTextEmails.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlCategory").focus();
        $scope.temp={};
        $scope.txtCommunicationLongDesc = '';
        $scope.editMode = false;
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $scope.getLocations();
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STEID': id.STEID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStandardTextEmails.indexOf(id);
		            $scope.post.getStandardTextEmails.splice(index, 1);
		            // console.log(data.data.message)
                    
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