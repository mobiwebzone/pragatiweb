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
    $scope.Page = "MEPITMANAGEMENT";
    $scope.PageSub1 = "MEPITREPORT";
    $scope.PageSub2 = "MEPBUGSCLOSEDREPORT";
    $scope.temp.txtETADate = new Date();
    $scope.TodayDate = new Date().toLocaleDateString('es-US');
    $scope.files = [];

    // console.log(angular.element(document.body).scope());
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/MEP_IT_closed_Bugs_report_code.php';



    /*========= Image Preview =========*/ 
    $scope.FILE_EXTENTION = '';
    $scope.UploadImage = function (element) {
        
        if (!element || !element.files[0] || element.files[0].length === 0){return;}
        
        const fileType = element.files[0]['type'].split('/')[0];
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {

            if(fileType == 'image'){$scope.logo_src = event.target.result}
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);



        /////////////////////
        // GET FILE EXTENTION
        /////////////////////
        const name = element.files[0].name;
        const lastDot = name.lastIndexOf('.');
        // const fileName = name.substring(0, lastDot);
        const ext = name.substring(lastDot + 1);

        $scope.FILE_EXTENTION = ext;
        console.log(fileType+'/'+$scope.FILE_EXTENTION);
        if(fileType != 'image') $scope.FileTypeImage(fileType,ext);
    }
    /*========= Image Preview =========*/ 

    $scope.FileTypeImage = function (FType,EXT) {
        if(['xlsx','xlsm','xlsb','xltx','xltm','xls','xlt','xls','csv','xml','xlam','xla','xlw','xlr'].includes(EXT)){
            $scope.logo_src = '../images/FileEx/xls.png';
        } 
        else if(['pdf'].includes(EXT)){$scope.logo_src = '../images/FileEx/pdf.png';} 
        else if(['doc','docx'].includes(EXT)){$scope.logo_src = '../images/FileEx/doc.png';} 
        else if(['pptx','pptm','ppt'].includes(EXT)){$scope.logo_src = '../images/FileEx/ppt.png';} 
        else if(['txt'].includes(EXT)){$scope.logo_src = '../images/FileEx/txt.png';}
        else{$scope.logo_src = '../images/FileEx/document.png';}
    }
    

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
                // $scope.getTODO();
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



    /* =========== SAVE DATA ==============*/
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
                formData.append("tdlid", $scope.temp.tdlid);
                formData.append("ddlSSubCategory", $scope.temp.ddlSSubCategory);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlUser", $scope.temp.ddlUser);
                formData.append("ddlPriority", $scope.temp.ddlPriority);
                formData.append("txtToDo", $scope.temp.txtToDo);
                formData.append("txtETADate", $scope.temp.txtETADate.toLocaleString('sv-SE'));
                formData.append("txtCatImage", $scope.temp.txtCatImage);
                formData.append("existingCatImage", $scope.temp.existingCatImage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearForm();
                $scope.getTODO();
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





    // =========== UPDATE TODO ==============
    $scope.UpdateTodo = function(id,index,val){
        $(".btnUpdTodo"+index+"").attr('disabled', 'disabled');
        $(".btnUpdTodo"+index+"").text('Updating...');
        // alert($scope.temp.ddlCollege);
        // alert(`${id.TDLID} // ${val} // ${index}`);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'UpdateTodo');
                formData.append("TDLID", id.TDLID);
                formData.append("txtTodoUpd", val);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnUpdTodo'+index+'').removeAttr('disabled');
            $(".btnUpdTodo"+index+"").text('UPDATE');
        });
    }
    // =========== UPDATE TODO ==============*/




/*
    // =========== UPDATE STATUS ==============
    $scope.updateStatus = function(id,val,index){
        $(".btnStatusUpdate"+index+"").attr('disabled', 'disabled');
        $(".btnStatusUpdate"+index+"").text('Updating...');
        // alert($scope.temp.ddlCollege);
        // alert(`${id.TDLID} // ${val} // ${index}`);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateStatus');
                formData.append("TDLID", id.TDLID);
                formData.append("ddlStatus", val);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                
                $scope.messageSuccess(data.data.message);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnStatusUpdate'+index+'').removeAttr('disabled');
            $(".btnStatusUpdate"+index+"").text('UPDATE');
        });
    }
    // =========== UPDATE STATUS ==============*/






    /* ========== GET TODO =========== */
    $scope.refreshData = false;
    $scope.getTODO = function () {
        $scope.post.getTODO=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $scope.refreshData = !$scope.refreshData;
        $scope.temp.txtSerarch = undefined;
        // $scope.temp.ddlSearchCategory = undefined;
        // $scope.temp.ddlSearchPriority = undefined;
        // $scope.temp.ddlSearchStatus = undefined;
        $('#ddlSearchCategory').attr('disabled','disabled');
        $('#ddlSearchPriority').attr('disabled','disabled');
        $('#ddlSearchStatus').attr('disabled','disabled');
        $('#SpinnerTodo').show();
        $scope.post.getTestSection = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getTODO',
                            'ddlLocation': $scope.temp.ddlLocation,
                            'ddlSearchCategory': $scope.temp.ddlSearchCategory,
                            'ddlSearchPriority': $scope.temp.ddlSearchPriority,
                            'ddlSearchStatus': $scope.temp.ddlSearchStatus,
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getTODO = data.data.data;
            }else{
                $scope.post.getTODO=[];
                // console.info(data.data.message);
            }
            $scope.refreshData = !$scope.refreshData;
            $('#SpinnerTodo').hide();
            $('#ddlSearchCategory').removeAttr('disabled');
            $('#ddlSearchPriority').removeAttr('disabled');
            $('#ddlSearchStatus').removeAttr('disabled');
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTODO(); --INIT
    /* ========== GET TODO =========== */




    

    /* ========== GET CATEGORY =========== */
    $scope.getTDCategory = function () {
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
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




    

    // /* ========== GET Location =========== */
    // $scope.getLocations = function () {
    //     $scope.post.getUserByLoc = [];
    //     $('.spinLoc').show();
    //     $http({
    //         method: 'post',
    //         url: 'code/Users_code.php',
    //         data: $.param({ 'type': 'getLocations'}),
    //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    //     }).
    //     then(function (data, status, headers, config) {
    //         // console.log(data.data);
    //         $scope.post.getLocations = data.data.data;
    //         $('.spinLoc').hide();
    //     },
    //     function (data, status, headers, config) {
    //         console.log('Failed');
    //     })
    // }
    // // $scope.getLocations(); --INIT
    // /* ========== GET Location =========== */
    /* ========== GET Location =========== */
    $scope.getLocations = function () {
        $scope.post.getUserByLoc = [];
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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getUserByLoc();
            if($scope.temp.ddlLocation > 0) $scope.getTDCategory();
            if($scope.temp.ddlLocation > 0) $scope.getTODO();

            // $scope.temp.ddlLocation = ($scope.post.getLocations) ? data.data.data[0]['LOC_ID'].toString():'';
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    
    
    /* ========== GET USER BY LOCATION =========== */
    $scope.getUserByLoc = function () {
        $('.spinUser').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getUserByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getUserByLoc = data.data.data;
            }else{
                $scope.post.getUserByLoc = [];
            }
            $('.spinUser').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getUserByLoc();
    /* ========== GET USER BY LOCATION =========== */






    /* ============ Edit Button ============= 
    $scope.editForm = function (id) {
        $("#ddlCategory").focus();
        
        $scope.temp.tdlid = id.TDLID;
        $scope.temp.ddlCategory = (id.TDCATID).toString();
        $scope.getTDSubCategory();
        $timeout( () => {
            $scope.temp.ddlSubCategory = (id.TDSUBCATID).toString();
            $scope.getTDSSubCategory();
            $timeout( () => {$scope.temp.ddlSSubCategory = (id.TDSSUBCATID).toString();},500);
        },700);

        $scope.temp.ddlLocation = (id.LOCID).toString();
        $scope.getUserByLoc();
        $timeout( () => {$scope.temp.ddlUser = (id.TOUSER).toString();},700);
        $scope.temp.ddlPriority = id.PRIORITY;
        $scope.temp.txtToDo = id.TODO;
        $scope.temp.txtETADate = new Date(id.ETA);
        $scope.temp.existingCatImage = id.IMAGE;
        /*########### IMG #############
        if(id.IMAGE != ''){

            const name_edit = id.IMAGE;
            const lastDot_edit = name_edit.lastIndexOf('.');
            const ext_edit = name_edit.substring(lastDot_edit + 1);

            // alert(name_edit+'....'+ext_edit);

            if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
                $scope.logo_src='todo_images/'+id.IMAGE;
            }else{
                $scope.FileTypeImage('',ext_edit);
            }
        }else{
            // $scope.logo_src='todo_images/default.png';
            $scope.logo_src='';
        }

        
        $scope.editMode = true;
        $scope.index = $scope.post.getTODO.indexOf(id);
    }
    /* ============ Edit Button =============
    
    

    /* ============ Clear Form ===========  
    $scope.clearForm = function(){
        $("#ddlCategory").focus();
        // $scope.temp={};
        $scope.temp.tdlid = '';
        $scope.temp.ddlCategory = '';
        $scope.temp.ddlSubCategory = '';
        $scope.temp.ddlSSubCategory = '';
        $scope.temp.ddlLocation = '';
        $scope.temp.ddlUser = '';
        $scope.temp.ddlPriority = '';
        $scope.temp.txtToDo = '';
        $scope.editMode = false;
        $scope.post.getUserByLoc = [];
        $scope.post.getTDSubCategory = [];
        $scope.post.getTDSSubCategory = [];
        $scope.temp.txtETADate = new Date();

        $scope.temp.existingCatImage = '';
        $scope.logo_src = '';
        $scope.files = [];
        angular.element('#txtCatImage').val(null);

        $scope.getLocations();
    }
    /* ============ Clear Form ===========  




    /* ========== DELETE =========== 
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'tdlid': id.TDLID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTODO.indexOf(id);
		            $scope.post.getTODO.splice(index, 1);
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


    /* ========== MESSAGE =========== */
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-success > span').html(msg);
    //     jQuery('.alert-success').show();
    //     jQuery('.alert-success').delay(5000).slideUp(function () {
    //         jQuery('.alert-success > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-danger > span').html(msg);
    //     jQuery('.alert-danger').show();
    //     jQuery('.alert-danger').delay(5000).slideUp(function () {
    //         jQuery('.alert-danger > span').html('');
    //     });
    // }
    /* ========== MESSAGE =========== */




});