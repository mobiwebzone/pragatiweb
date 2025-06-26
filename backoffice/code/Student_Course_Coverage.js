$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect","angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.editModeStudent = false;
    $scope.Page = "STUDENT";
    $scope.PageSub = "ST_COURSE_COVERAGE";
    $scope.temp.txtCoverageDT = new Date();
    $scope.INV_model = [];
    $scope.INV_CHAPTERS_model = [];
    $scope.files = [];
    $scope.filesST = [];
    $scope.temp.txtFromDT_ST=$scope.temp.txtToDT_ST = new Date();

    $scope.INV_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};
    $scope.INV_CHAPTERS_settings = {enableSearch: true,
                                    scrollable: true, 
                                    scrollableHeight:'400px',
                                    groupBy: 'INVENTORY',
                                    };
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Student_Course_Coverage_code.php';


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
        // console.log(fileType+'/'+$scope.FILE_EXTENTION);
        if(fileType != 'image') $scope.logo_src=$scope.FileTypeImage(fileType,ext);
    }
    /*========= Image Preview =========*/ 
    // $scope.FileTypeImage = function (FType,EXT) {
    //     if(['xlsx','xlsm','xlsb','xltx','xltm','xls','xlt','xls','csv','xml','xlam','xla','xlw','xlr'].includes(EXT)){
    //         $scope.logo_src = '../images/FileEx/xls.png';
    //     } 
    //     else if(['pdf'].includes(EXT)){$scope.logo_src = '../images/FileEx/pdf.png';} 
    //     else if(['doc','docx'].includes(EXT)){$scope.logo_src = '../images/FileEx/doc.png';} 
    //     else if(['pptx','pptm','ppt'].includes(EXT)){$scope.logo_src = '../images/FileEx/ppt.png';} 
    //     else if(['txt'].includes(EXT)){$scope.logo_src = '../images/FileEx/txt.png';}
    //     else{$scope.logo_src = '../images/FileEx/document.png';}
    // }

    /*========= Image Preview ST =========*/ 
    $scope.FILE_EXTENTION_ST = '';
    $scope.UploadImageST = function (element) {
        
        if (!element || !element.files[0] || element.files[0].length === 0){return;}
        
        const fileType = element.files[0]['type'].split('/')[0];
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {

            if(fileType == 'image'){$scope.logo_srcST = event.target.result}
            $scope.$apply(function ($scope) {
                $scope.filesST = element.files;
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

        $scope.FILE_EXTENTION_ST = ext;
        // console.log(fileType+'/'+$scope.FILE_EXTENTION);
        if(fileType != 'image') $scope.logo_srcST = $scope.FileTypeImage(fileType,ext);
    }
    /*========= Image Preview ST =========*/ 
    $scope.FileTypeImage = function (FType,EXT) {
        if(['xlsx','xlsm','xlsb','xltx','xltm','xls','xlt','xls','csv','xml','xlam','xla','xlw','xlr'].includes(EXT)){
            var src = '../images/FileEx/xls.png';
        } 
        else if(['pdf'].includes(EXT)){var src = '../images/FileEx/pdf.png';} 
        else if(['doc','docx'].includes(EXT)){var src = '../images/FileEx/doc.png';} 
        else if(['pptx','pptm','ppt'].includes(EXT)){var src = '../images/FileEx/ppt.png';} 
        else if(['txt'].includes(EXT)){var src = '../images/FileEx/txt.png';}
        else{var src = '../images/FileEx/document.png';}

        return src;
    }
    $scope.clearLogo_src=(FOR)=>{
        if(FOR=='WORK'){
            $scope.logo_src='';
            $scope.files = [];
        }else{
            $scope.logo_srcST='';
            $scope.filesST = [];
        }
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
                $scope.locid=data.data.locid;
                // window.location.assign("dashboard.html");

                $scope.getLocations();
                $scope.getPlans();
                // $scope.getInventory();
                // $scope.getStudentCourseCoverage();
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



// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENT COURSE COVERAGE SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    


    // =========== SAVE DATA ==============
    $scope.saveData = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');

        $scope.temp.DocsUpload = $scope.files[0];
        // console.log($scope.SEMYAER_model);
        $scope.FinalInventory = [];
        $scope.FinalInventory = $scope.INV_model.map((x)=>{return x.id;});
        $scope.FinalChapters = [];
        $scope.FinalChapters = $scope.INV_CHAPTERS_model.map((x)=>{return {'CHAPID':x.id,'INVID':x.INVID};});
        // console.log($scope.FinalChapters);
        // return;


        // if(!$scope.editMode) $scope.GET_ALL_SCCID=[];
        // console.log($scope.FinalChapters);
        // alert(JSON.stringify($scope.GET_ALL_SCCID));
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("sccid", $scope.temp.sccid);
                formData.append("sccid_ALL", $scope.GET_ALL_SCCID);
                formData.append("txtCoverageDT", $scope.temp.txtCoverageDT.toLocaleString('sv-SE'));
                formData.append("ddlPlan", $scope.temp.ddlPlan);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                // formData.append("ddlInventory", $scope.temp.ddlInventory);
                formData.append("ddlInventory", $scope.FinalInventory);
                // formData.append("ddlInvChapter", $scope.temp.ddlInvChapter);
                // formData.append("txtPageFrom", $scope.temp.txtPageFrom);
                // formData.append("txtPageTo", $scope.temp.txtPageTo);
                formData.append("txtRemarkMain", $scope.temp.txtRemarkMain);
                formData.append("txtHomeWork", $scope.temp.txtHomeWork);
                formData.append("FinalChapters", JSON.stringify($scope.FinalChapters));
                formData.append("selectedStudentData", JSON.stringify($scope.selectedStudentData));
                formData.append("DocsUpload", $scope.temp.DocsUpload);
                formData.append("existingDocsUpload", $scope.temp.existingDocsUpload);
                formData.append("chkRemoveImgOnUpdate", ((!$scope.logo_src || $scope.logo_src.length<=0) && $scope.editMode)?1:0);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.temp.sccid=data.data.GET_SCCID;
                if(!$scope.editMode){
                    $scope.GET_ALL_SCCID = data.data.GET_ALL_SCCID;
                }
                // $('#ddlInventory').attr('disabled','disabled');
                // $('.dropdown-toggle').attr('disabled','disabled');
                $('#INV_CHAPTERS').find('.dropdown-toggle').attr('disabled','disabled');
                // $scope.clearForm();


                $scope.FinalInventory = [];
                $scope.FinalChapters = [];
                $scope.post.selectedStudentData = []
                $scope.getStudentCourseCoverage();
                if($scope.temp.sccid > 0){
                    $scope.getSelectedStudents();
                }
                // $timeout(()=>{$("#ddlPlanStudent").focus();},500);
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






    /* ========== GET STUDENT COURSE COVERAGE =========== */
    $scope.getStudentCourseCoverage = function () {
        $scope.post.getTestSection = [];
        $scope.post.getStudentCourseCoverage=[];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('#SpinMainData').show();
        $('.btnGetDT').attr('disabled','disabled');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getStudentCourseCoverage');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("txtFromDT_ST", !$scope.temp.txtFromDT_ST || $scope.temp.txtFromDT_ST==''? '' : $scope.temp.txtFromDT_ST.toLocaleDateString('sv-SE'));
                formData.append("txtToDT_ST", !$scope.temp.txtToDT_ST || $scope.temp.txtToDT_ST==''? '' : $scope.temp.txtToDT_ST.toLocaleDateString('sv-SE'));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentCourseCoverage = data.data.data;
            }else{
                $scope.post.getStudentCourseCoverage=[];
                // console.info(data.data.message);
            }
            $('.btnGetDT').removeAttr('disabled');
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getStudentCourseCoverage(); --INIT
    /* ========== GET STUDENT COURSE COVERAGE =========== */



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
            $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            if($scope.temp.ddlLocation > 0) $scope.getInventory();
            if($scope.temp.ddlLocation > 0) $scope.getStudentCourseCoverage();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */





    /* ========== GET PLANS =========== */
    $scope.getPlans = function () {
        $('.spinPlan').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.data;
            $('.spinPlan').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT
    /* ========== GET PLANS =========== */




    
    /* ========== GET PRODUCTS BY PLANID =========== */
    $scope.getProductByPlanID = function () {
        $('.spinPlanProduct').show();
        $http({
            method: 'post',
            url: 'code/SellingPlans_code.php',
            data: $.param({ 'type': 'getPlanProducts','planid':$scope.temp.ddlPlan}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getPlanProduct = data.data.data;
            }else{
                $scope.post.getPlanProduct = [];
            }
            $('.spinPlanProduct').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlanProducts();
    /* ========== GET PRODUCTS BY PLANID =========== */





    /* ========== GET INVENTORY =========== */
    $scope.getInventory = function () {
        $scope.post.getInventory = [];
        $scope.post.getInvChapters = [];
        // $('.dropdown-toggle').attr('disabled','disabled');
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0) return;
        $('.SpinINV').show();
        $http({
            method: 'post',
           url: 'code/Inventory_Master_code.php',
           data: $.param({ 'type': 'getInventories','ddlLocation':$scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
           if(data.data.success){
               $scope.post.getInventory = data.data.data;
               if($scope.post.getInventory.length>0){
                   $scope.post.getInventory = angular.copy(data.data.data.map(x=>({
                    ...x,
                    id:x.INVID,
                    label:x.TITLE
                   })));
               }
           }else{
               $scope.post.getInventory = [];
           }
           $('.SpinINV').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getInventory(); --INIT
   /* ========== GET INVENTORY =========== */





    /* ========== GET INVENTORY CHAPTERS =========== */
    $scope.getInvChapters = function () {
        $FINAL_INVID = $scope.INV_model.map(x=>x.id);
        // console.log($FINAL_INVID);
        $('.SpinINVChap').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'getInvChapters','INVID':$FINAL_INVID}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
        //    console.log(data.data);
           if(data.data.success){
               $scope.post.getInvChapters = data.data.data;
               $('#INV_CHAPTERS').find('.dropdown-toggle').removeAttr('disabled');
           }else{
               $scope.post.getInvChapters = [];
               $('#INV_CHAPTERS').find('.dropdown-toggle').attr('disabled','disabled');
           }
           $('.SpinINVChap').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
   }
   // $scope.getInvChapters();
   /* ========== GET INVENTORY CHAPTERS =========== */





    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        // $("#txtChannel").focus();
        $scope.GET_ALL_SCCID = [];
        $scope.temp.sccid = id.SCCID;
        $scope.GET_ALL_SCCID.push(id.SCCID);
        $scope.temp.txtCoverageDT = new Date(id.CDATE);
        $scope.temp.ddlPlan = (id.PLANID).toString();
        if($scope.temp.ddlPlan > 0){
            $scope.getProductByPlanID();
            $timeout(()=>{$scope.temp.ddlProduct=(id.PRODUCTID).toString()},500);
            $scope.getStudentByPlan();
        }
        // $scope.temp.ddlInventory = (id.INVID).toString();
        $scope.INV_model = [{'id':id.INVID}];
        if($scope.INV_model.length > 0){
            $scope.getInvChapters();
                $timeout(()=>{
                    const indexOfChap = $scope.post.getInvChapters.findIndex((x)=>x.id==id.CHAPID);
                    $scope.INV_CHAPTERS_model = [$scope.post.getInvChapters[indexOfChap]];
                    // console.log($scope.INV_CHAPTERS_model);
                    $('#ddlInventory, .dropdown-toggle').attr('disabled','disabled');
                    $('#INV_CHAPTERS').find('.dropdown-toggle').attr('disabled','disabled');
                },3000);
            
        }
        // $scope.temp.txtPageFrom = Number(id.PAGEFROM);
        // $scope.temp.txtPageTo = Number(id.PAGETO);
        $scope.temp.txtRemarkMain = id.REMARK;
        $scope.temp.txtHomeWork = id.HOMEWORK;
        $scope.temp.existingDocsUpload = id.HOMEWORK_DOC,

        $timeout(()=>{
            if($scope.temp.sccid > 0){
                $scope.getSelectedStudents();
            }
        },1000);

        $('#txtCoverageDT').focus();

        /*########### IMG #############*/
        if(id.HOMEWORK_DOC != ''){
            const name_edit = id.HOMEWORK_DOC;
            const lastDot_edit = name_edit.lastIndexOf('.');
            const ext_edit = name_edit.substring(lastDot_edit + 1);

            // alert(name_edit+'....'+ext_edit);

            if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
                $scope.logo_src='images/course_coverage_hw/'+id.HOMEWORK_DOC;
            }else{
                $scope.logo_src = $scope.FileTypeImage('',ext_edit);
            }
        }else{
            $scope.logo_src='';
        }
        
        $scope.editMode = true;
        $scope.index = $scope.post.getStudentCourseCoverage.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#txtChannel").focus();
        $scope.temp={};
        $scope.post.getInvChapters = [];
        $scope.editMode = false;
        $scope.temp.txtCoverageDT = new Date();
        $scope.FinalInventory = [];
        $scope.FinalChapters = [];
        // $scope.selectedStudentData = [];
        $scope.post.selectedStudentData = [];
        $scope.INV_model = [];
        $scope.INV_CHAPTERS_model = [];
        $scope.logo_src = '';
        $scope.files = [];
        $scope.GET_ALL_SCCID=[];
        angular.element('#DocsUpload').val(null);
        // $('#ddlInventory').removeAttr('disabled');
        // $('.dropdown-toggle').attr('disabled','disabled');
        $('#ddlInventory').find('.dropdown-toggle').removeAttr('disabled');
        $('#INV_CHAPTERS').find('.dropdown-toggle').attr('disabled','disabled');

        $scope.clearFormStudents();
        $scope.temp.txtFromDT_ST=$scope.temp.txtToDT_ST = new Date();
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
                data: $.param({ 'SCCID': id.SCCID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getStudentCourseCoverage.indexOf(id);
		            $scope.post.getStudentCourseCoverage.splice(index, 1);
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
    




// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% STUDENTS SECTION %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $scope.selectedStudentData=[];
    // =========== ADD STUDENTS ==============
    $scope.addRemoveStudents=function(id,FOR){
        $('#alert-AddStudent').hide();
        
        if(FOR === 'ADD'){
            $('.btn-save-Students').attr('disabled','disabled');
            if(!$scope.temp.ddlStudent || $scope.temp.ddlStuden<=0) return
            if($scope.selectedStudentData.find((x)=>x.studentid == $scope.temp.ddlStudent) != undefined){
                $('#alert-AddStudent').show();
                $timeout(()=>$('#alert-AddStudent').hide(),5000);
            }else{
                // console.log($scope.selectedStudentData.find((x)=>x.studentid == $scope.temp.ddlStudent));
                $scope.selectedStudentData.push({
                    studentid : Number($scope.temp.ddlStudent),
                    student : $scope.post.getStudentByPlan.filter((x)=>x.REGID==$scope.temp.ddlStudent).map((x)=>x.STUDENT).toString(), 
                    remark : (!$scope.temp.txtRemark || $scope.temp.txtRemark == '') ? '' : $scope.temp.txtRemark,
                    homework_done : 0,
                    studentwork : '',
                    homework_img : ''
                });
            }
            $('.btn-save-Students').removeAttr('disabled');
            $scope.temp.ddlStudent = '';
            $scope.temp.txtRemark = '';
        }else{
            var ss = $scope.selectedStudentData.indexOf(id);
            $scope.selectedStudentData.splice(ss,1);
        }
    }
    // =========== ADD STUDENTS ==============


    // =========== VIEW STUDENT HOME WORK IMAGE ==============
    $scope.viewHomeWorkImages=function(id){
        // console.log(id);
        $scope.HOMEWORK_IMAGE_SET = [];

        if(id.HOMEWORK_IMG !='' && id.HOMEWORK_IMG.length>0) $scope.HOMEWORK_IMAGE_SET.push({src: `../student_zone/images/homework/${id.HOMEWORK_IMG}`,title: id.HOMEWORK_IMG})
        
        // define options (if needed)
        var options = {
            // optionName: 'option value'
            // for example:
            index: 0, // this option means you will start at first image
            keyboard:true,
            title:true,
            fixedModalSize:true,
            modalWidth: 500,
            modalHeight: 500,
            fixedModalPos:true,
            footerToolbar: ['zoomIn','zoomOut','prev','fullscreen','next','actualSize','rotateRight','myCustomButton'],
            customButtons: {
                myCustomButton: {
                  text: '',
                  title: 'Click To Download',
                  click: function (context, e) {
                    // alert('clicked the custom button!');
                    var link = document.createElement('a');
                    link.href = `../student_zone/images/homework/${id.HOMEWORK_IMG}`;
                    link.download = id.HOMEWORK_IMG;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                  }
                }
              }
        };
        
        // Initialize the plugin
        var photoviewer = new PhotoViewer($scope.HOMEWORK_IMAGE_SET, options);    
        // $('.photoviewer-button-myCustomButton').addClass('bg-success rounded border brder-success mt-2').css({"height": "34px"});            
        $('.photoviewer-button-myCustomButton').html('<i class="fa fa-download" aria-hidden="true"></i>');            
        
    }
    // =========== VIEW STUDENT HOME WORK IMAGE ==============


    // =========== SAVE DATA ==============
    $scope.saveDataStudents = function(){
        $(".btn-save-Students").attr('disabled', 'disabled');
        $(".btn-save-Students").text('Adding...');
        $(".btn-update-Students").attr('disabled', 'disabled');
        $(".btn-update-Students").text('Updating...');
        $scope.temp.DocsUploadST = $scope.filesST[0];
        if(!$scope.GET_ALL_SCCID && $scope.GET_ALL_SCCID.length==0) $scope.GET_ALL_SCCID.push($scope.temp.sccid);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveDataStudents');
                formData.append("sccdid", $scope.temp.sccdid);
                formData.append("sccid_ALL", $scope.GET_ALL_SCCID);
                formData.append("sccid", $scope.temp.sccid);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("DocsUploadST", $scope.temp.DocsUploadST);
                formData.append("existingDocsUpload", $scope.temp.existingDocsUploadST);
                formData.append("chkRemoveImgOnUpdate", ((!$scope.logo_srcST || $scope.logo_srcST.length<=0) && $scope.editModeStudent)?1:0);
                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.clearFormStudents();
                $scope.getSelectedStudents();
                $scope.messageSuccess(data.data.message);
                $scope.getStudentCourseCoverage();
            }
            else {
                $scope.messageFailure(data.data.message);
            }
            $('.btn-save-Students').removeAttr('disabled');
            $(".btn-save-Students").text('ADD');
            $('.btn-update-Students').removeAttr('disabled');
            $(".btn-update-Students").text('UPDATE');
        });
    }
    // =========== SAVE DATA ==============




    
    
    /* ========== GET ST_CC_STUDENTS =========== */
    $scope.getSelectedStudents = function () {
        $('#spinSTUDENTSMAIN').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getSelectedStudents', 'sccid' : $scope.temp.sccid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.selectedStudentData = data.data.data;
                // if($scope.editModeStudent) $scope.clearFormStudents()
            }else{
                // $scope.selectedStudentData = [];
                $scope.post.selectedStudentData = [];
            }
            $('#spinSTUDENTSMAIN').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSelectedStudents();
    /* ========== GET ST_CC_STUDENTS =========== */
    



   /*============ GET STUDENT BY PLAN =============*/ 
    $scope.getStudentByPlan = function () {
        $scope.post.getStudentByPlan = [];
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlPlan || $scope.temp.ddlPlan<=0) return;
        $('.spinStudent').show();
        // $scope.post.getTestSection = [];
        $http({
            method: 'post',
            url: 'code/Student_Test_View_Answer_code.php',
            data: $.param({ 'type': 'getStudentByPlan', 'ddlPlan' : $scope.temp.ddlPlan,'ddlLocation':$scope.temp.ddlLocation}),
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
    /*============ GET STUDENT BY PLAN =============*/ 





    /* ============ Edit Button ============= */ 
    $scope.editFormStudents = function (id) {
        // console.log(id);
        $("#txtRemark").focus();
        // $("#ddlPlanStudent").attr('disabled','disabled');
        $("#ddlStudent").attr('disabled','disabled');
        $scope.GET_ALL_SCCID=[];
        $scope.temp.sccdid = id.SCCDID;
        // $scope.temp.ddlPlanStudent = '';
        $scope.temp.ddlStudent = id.REGID.toString();
        $scope.temp.txtRemark = id.REMARK;
        $scope.temp.existingDocsUploadST = id.DOC;
        $scope.editModeStudent = true;

        /*########### IMG #############*/
        if(id.DOC != ''){
            const name_edit = id.DOC;
            const lastDot_edit = name_edit.lastIndexOf('.');
            const ext_edit = name_edit.substring(lastDot_edit + 1);

            // alert(name_edit+'....'+ext_edit);

            if(['jpg','jpeg','jfif' ,'pjpeg','pjp','png','svg','webp','gif'].includes(ext_edit)){
                $scope.logo_srcST='images/course_coverage_hw/'+id.DOC;
            }else{
                $scope.logo_srcST = $scope.FileTypeImage('',ext_edit);
            }
        }else{
            $scope.logo_srcST='';
        }

        $scope.index = $scope.post.selectedStudentData.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearFormStudents = function(){
        $("#ddlStudent").focus();
        $scope.temp.sccdid = '';
        // $scope.temp.ddlPlanStudent = '';
        $scope.temp.ddlStudent = '';
        $scope.temp.txtRemark = '';
        $scope.logo_srcST = '';
        $scope.filesST = [];
        angular.element('#DocsUploadST').val(null);
        $scope.editModeStudent = false;

        // $("#ddlPlanStudent").removeAttr('disabled');
        $("#ddlStudent").removeAttr('disabled');
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.deleteStudents = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SCCDID': id.SCCDID, 'type': 'deleteStudents' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.selectedStudentData.indexOf(id);
		            $scope.post.selectedStudentData.splice(index, 1);
		            // console.log(data.data.message)
                    $scope.clearFormStudents();
                    $scope.getStudentCourseCoverage();
                    
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