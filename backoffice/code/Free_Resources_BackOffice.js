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
$postModule.directive('ngFile', ['$parse', function ($parse) {
    return {
     restrict: 'A',
     link: function(scope, element, attrs) {
       element.bind('change', function(){
  
       $parse(attrs.ngFile).assign(scope,element[0].files)
       scope.$apply();
     });
    }
   };
  }]);
$postModule.filter('trustThisUrl', ["$sce", function ($sce) {
    return function (val) {
        return $sce.trustAsResourceUrl(val);
    };
}]);
$postModule.controller("myCtrl", function ($scope, $http,$timeout,$sce,taOptions) {
    $scope.trustAsHtml = $sce.trustAsHtml;
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "FREERES";
    $scope.PageSub = "FREE_RES";
    $scope.FormName = 'Show Entry Form';
    $scope.serial = 1;
    $scope.temp.txtChkHTML = false;
    $scope.temp.txtChkBlink = false;
    $scope.files = [];
    $scope.logo_src = [];
    // $scope.txtSerarch='Undergrad College Essay Prompts';

    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        // ['p'],
        ['p','bold', 'italics', 'underline', 'strikeThrough', 'redo', 'undo', 'clear'],
        // ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Free_Resources_BackOffice_code.php';

    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.logo_src=[];
        $scope.files=[];
        console.log(element.files);
        if (element.files && element.files[0]) {
            var filesAmount = element.files.length;
            for (let i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();
       
                    reader.onload = (event) => {
                    //   console.log(event.target.result);
                    //    this.images.push(event.target.result); 
                        // var base64 = event.target.result;
                        // $scope.logo_src.push(base64);                
                        $scope.logo_src[i]=event.target.result;                
                        $scope.$apply(function ($scope) {
                            $scope.files[i] = element.files;
                        });
                    }
                    reader.readAsDataURL(element.files[i]);
            }
        }

        
        // $scope.currentFile = element.files[0];
        // var reader = new FileReader();
        // reader.onload = function (event) {
        //     $scope.logo_src = event.target.result
        //     $scope.$apply(function ($scope) {
        //         $scope.files = element.files;
        //     });
        // }
        // reader.readAsDataURL(element.files[0]);
    }

    /*========= Image Preview =========*/ 


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
                // window.location.assign("dashboard.html");

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getCategory();
                    $scope.getFreeResource();
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

    $scope.FormShowHide=function (){
        $scope.FormName ='';
        var isMobileVersion = document.getElementsByClassName('collapsed');
        if (isMobileVersion.length > 0) {
            $scope.FormName = 'Hide Entry Form';
        }else{
            $scope.FormName = 'Show Entry Form';
        }

        $('.ShowHideIcon').toggleClass("fa-plus-circle fa-minus-circle");
    }



    $scope.saveFreeResource = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // $scope.temp.txtResImage=$scope.files;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveFreeResource');
                formData.append("id", $scope.temp.id);
                formData.append("ddlResCat", $scope.temp.ddlResCat);
                formData.append("txtRecCatName", $scope.temp.txtRecCatName);
                formData.append("ddlUnderResource", $scope.temp.ddlUnderResource);
                formData.append("txtChkBlink", $scope.temp.txtChkBlink);
                formData.append("txtCatColor", $scope.temp.txtCatColor);
                formData.append("txtChkHTML", $scope.temp.txtChkHTML);
                formData.append("txtResourceLinkLabel", $scope.temp.txtResourceLinkLabel);
                formData.append("txtResourceLink", $scope.temp.txtResourceLink);
                formData.append("txtResImage", $scope.temp.txtResImage);
                formData.append("existingResImage", $scope.temp.existingResImage);
                formData.append("chkRemoveImgOnUpdate", ($scope.logo_src.length<=0 && $scope.editMode)?1:0);
                // MULTIPLE IMAGE
                angular.forEach($scope.uploadfiles,function(file){
                    formData.append('file[]',file);
                    });
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                $scope.temp.id = data.data.GET_ID;
                if($scope.temp.ddlResCat == 'Resource' && $scope.temp.ddlUnderResource > 0) $scope.getCategoryFeatures('UNDER');
                $scope.getFreeResource();
                $scope.getCategory();
                // $scope.clearForm();
                document.getElementById("ddlResCat").focus();
                
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
    
    
    /* ========== GET Free Resource =========== */
    $scope.getFreeResource = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getFreeResource'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getFreeResources = data.data.data;
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getFreeResource(); --INIT
     


     /* ========== GET Category =========== */
     $scope.getCategory = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCategory'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategory(); --INIT







    /* ============ Edit Button ============= */ 
    $scope.editFreeResource = function (id) {
        // CLEAR SUB FORMS
        $scope.clearCFForm();
        $scope.post.getCategoryFeatures=[];
        $scope.clearCFValForm();
        $scope.post.getCategoryFeaturesVal = [];
        $scope.post.FEATURE_VAL= []
        // CLEAR SUB FORMS

        


        $('.collapse').collapse('show');
        $timeout(function() {
            $scope.FormShowHide();
            $scope.FormName = 'Hide Entry Form';
        },500);
        document.getElementById("ddlResCat").focus();
        $scope.temp = {
            id:id.ID,
            ddlResCat: id.RESOURCE_CATEGORY,
            txtRecCatName: id.RESOURCE_CATEGORY_TEXT,
            // ddlUnderResource: (id.UNDER_ID).toString(),
            txtContactPerson: id.LOC_PERSON,
            txtChkBlink: id.BLINK>0?true:false,
            txtCatColor:id.COLOR,
            txtChkHTML: id.HTML>0?true:false,
            txtResourceLinkLabel: id.RESOURCE_LINK_LABEL,
            txtResourceLink: id.RESOURCE_LINK,
            existingResImage: id.RESOURCE_IMAGES
        };

        if(id.UNDER_ID != 0){
            $scope.temp.ddlUnderResource=(id.UNDER_ID).toString();
            if(id.RESOURCE_CATEGORY=='Resource') $scope.getCategoryFeatures('UNDER');
            // if(id.RESOURCE_CATEGORY=='Resource') $scope.getCategoryFeaturesVal();
        }
        if(id.RESOURCE_CATEGORY=='Category') $scope.getCategoryFeatures('CAT');

        // console.log(id.RESOURCE_IMAGES_ARRAY);
        if(id.RESOURCE_IMAGES_ARRAY.length>0){
            for($i=0; $i<id.RESOURCE_IMAGES_ARRAY.length; $i++){
                $scope.logo_src.push(`images/free_resources/${id.RESOURCE_IMAGES_ARRAY[$i]}`);
            }
        }
        

        $scope.editMode = true;
        $scope.index = $scope.post.getFreeResources.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("ddlResCat").focus();
        // $scope.FormName = 'Show Entry Form';
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.IsmainET = false;

        $scope.clearCFForm();
        $scope.post.getCategoryFeatures=[];
        $scope.clearCFValForm();
        $scope.post.getCategoryFeaturesVal = [];
        $scope.post.FEATURE_VAL= []

        $scope.clearFormCategoryChange();
    }

    $scope.clearFormCategoryChange = function(){
        $scope.temp.txtChkBlink = false;
        $scope.temp.txtCatColor = '#000000';
        $scope.temp.txtChkHTML = false;
        $scope.temp.txtResourceLinkLabel = '';
        $scope.temp.txtResourceLink = '';
        $scope.clearFormHTML();
    }
    
    $scope.clearFormHTML = function(){
        if(!$scope.editMode){
            $scope.logo_src = [];
            $scope.files = [];
            angular.element('#txtResImage').val(null);
        }
    }


    /* ========== DELETE =========== */
    $scope.deleteFreeResource = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'ID': id.ID, 'type': 'deleteFreeResource','RESOURCE_CATEGORY':id.RESOURCE_CATEGORY }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getFreeResources.indexOf(id);
		            $scope.post.getFreeResources.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearForm();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    


    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $scope.saveCatFeatures = function(){
        $(".btn-saveCF").attr('disabled', 'disabled');
        $(".btn-saveCF").text('Adding...');
        $(".btn-updateCF").attr('disabled', 'disabled');
        $(".btn-updateCF").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCatFeatures');
                formData.append("cfid", $scope.temp.cfid);
                formData.append("id", $scope.temp.id);
                formData.append("txtFeatureName", $scope.temp.txtFeatureName);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getCategoryFeatures('CAT');
                $("#txtFeatureName").focus();
                $scope.clearCFForm();
                $scope.getFreeResource();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveCF').removeAttr('disabled');
            $(".btn-saveCF").text('ADD');
            $('.btn-updateCF').removeAttr('disabled');
            $(".btn-updateCF").text('UPDATE');
        });
    }
    
    
    /* ========== GET Category Features =========== */
    $scope.CFID_ARRAY =[];
    $scope.getCategoryFeatures = function (TYPE) {
        $ID = TYPE == 'CAT' ? $scope.temp.id : $scope.temp.ddlUnderResource;
        $('#SpinCFData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCategoryFeatures','ID':$ID,'ddlResCat':$scope.temp.ddlResCat}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategoryFeatures = data.data.success ? data.data.data : [];
            $scope.CFID_ARRAY = data.data.success ? data.data.CFID_ARRAY : [];
            if(TYPE == 'UNDER') $scope.getCategoryFeaturesVal();
            $('#SpinCFData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategoryFeatures();
     


    /* ============ Edit Button ============= */ 
    $scope.editCatFeature = function (id) {
        if($scope.temp.ddlResCat!='Category') return;
        $("#txtFeatureName").focus();
        $scope.temp.cfid=id.CFID;
        $scope.temp.txtFeatureName=id.FEATURES;
        $scope.index = $scope.post.getCategoryFeatures.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearCFForm = function(){
        $("#txtFeatureName").focus();
        $scope.temp.cfid='';
        $scope.temp.txtFeatureName='';
    }



    /* ========== DELETE =========== */
    $scope.deleteCatFeature = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'CFID': id.CFID, 'type': 'deleteCatFeature' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCategoryFeatures.indexOf(id);
		            $scope.post.getCategoryFeatures.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearCFForm();
                    $scope.getFreeResource();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    
    
    
    
    


    
    
    
    
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES VALUES START %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    /* ========== Save Category Features Val =========== */
    $scope.saveCatFeaturesVal = function(){
        // console.log($scope.temp.txtFeature);
        $(".btn-saveCFVal").attr('disabled', 'disabled');
        $(".btn-saveCFVal").text('Adding...');
        $(".btn-updateCFVal").attr('disabled', 'disabled');
        $(".btn-updateCFVal").text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveCatFeaturesVal');
                formData.append("rfvid", $scope.temp.rfvid);
                formData.append("ID", $scope.temp.id);
                formData.append("Feature", JSON.stringify($scope.temp.txtFeature));
                formData.append("RFVID_UPD", $scope.RFVID_UPD);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getCategoryFeaturesVal();
                $("#txtFeature0").focus();
                $scope.clearCFValForm();
                $scope.getFreeResource();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveCFVal').removeAttr('disabled');
            $(".btn-saveCFVal").text('ADD');
            $('.btn-updateCFVal').removeAttr('disabled');
            $(".btn-updateCFVal").text('UPDATE');
        });
    }
    
    
    /* ========== GET Category Features Val =========== */
    $scope.getCategoryFeaturesVal = function () {
        $('#SpinCFValData').show();
        // console.log($scope.CFID_ARRAY);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 
                            'type': 'getCategoryFeaturesVal',
                            'ID':$scope.temp.id,
                            'CFID_ARRAY':$scope.CFID_ARRAY,
                        }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getCategoryFeaturesVal = data.data.success ? data.data.data : [];
            $scope.post.FEATURE_VAL = data.data.success ? data.data.FEATURE_VAL : [];
            $('#SpinCFValData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategoryFeaturesVal();
     


    /* ============ Edit Button ============= */ 
    $scope.RFVID_UPD = [];
    $scope.editCatFeatureVal = function (id) {
        if($scope.temp.ddlResCat!='Resource') return;
        $scope.RFVID_UPD = [];
        for(i=0;i<id.length;i++){
            $scope.temp.txtFeature[i]['VAL']=id[i].VALUE;
            $scope.temp.rfvid=id[0].RFVID;
            $scope.RFVID_UPD.push(id[i].RFVID);
        }
        $("#txtFeature_0").focus();
        $scope.index = $scope.post.getCategoryFeaturesVal.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearCFValForm = function(){
        if($scope.temp.txtFeature){
            var ss = Object.keys($scope.temp.txtFeature).map((key) => $scope.temp.txtFeature[key]);
            for(i=0;i<ss.length;i++){
                $scope.temp.txtFeature[i]['VAL']="";
            }
            $scope.RFVID_UPD = [];
            // $scope.temp.txtFeature['VAL'] = new Array($scope.temp.txtFeature.length).fill("");
            $scope.temp.rfvid='';
            $("#txtFeature_0").focus();
        }
    }



    /* ========== DELETE =========== */
    $scope.deleteCatFeatureVal = function (id) {
        $scope.RFVID_UPD = [];
        for(i=0;i<id.length;i++){
            $scope.RFVID_UPD.push(id[i].RFVID);
        }
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'RFVID': $scope.RFVID_UPD.toString(), 'type': 'deleteCatFeatureVal' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getCategoryFeaturesVal.indexOf(id);
		            $scope.post.getCategoryFeaturesVal.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearCFValForm();
                    $scope.getCategoryFeaturesVal();
                    $scope.getFreeResource();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% CATEGORY FEATURES VALUES END %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


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