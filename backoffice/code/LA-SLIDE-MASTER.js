$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","textAngular"]);
// taOptions.toolbar = [
//     ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
//     ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
//     ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
//     ['html', 'insertImage','insertLink', 'insertVideo', 'wordcount', 'charcount']
// ];
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
$postModule.directive('tooltip', function () {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        // Initialize Bootstrap Tooltip
        $(element).tooltip({
            placement: attrs.tooltip || 'top'
        });
      }
    };
});
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "L&A";
    $scope.PageSub = "LA_MASTER";
    $scope.PageSub1 = "LA_SLIDE_M";
    $scope.txtFinalizedDT = [];
    $scope.txtConfiguredDT = [];
    $scope.txtSlideReadyDT = [];
    $scope.txtConfigReadyDT = [];
    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote',
        'bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear','insertLink','justifyLeft', 'justifyCenter', 'justifyRight'],
        // ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
    ];
    // ========= TEXT EDITOR =========
    
    var url = 'code/LA-SLIDE-MASTER.php';
    var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        // console.log($scope.myOrderBY);
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
                $scope.locid = data.data.locid;
                $scope.IS_ET = data.data.IS_ET;

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN" && $scope.userrole != "LA_MASTER")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getUserLocationsWithMainLocation();
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


    /* ========== GET UNDER TOPIC =========== */
    // $scope.post.getUnderTopics="<h2 class='text-center mb-0' id=''>No Record</h2>";
    $scope.TOPIC_LIST_EXIST=false;
    $scope.getUnderTopics = function () {
        if(!$scope.temp.ddlLocation || $scope.temp.ddlLocation<=0 || !$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0) return;
        // $scope.post.getUnderTopics="<h2 class='text-center' id='wait'>Please wait...</h2>";
        $scope.TOPIC_LIST_EXIST=false;
        $scope.post.getSlideHeads = [];
        $scope.SELECTED_TOPICID=0;
        $scope.clearSlideHeading();
        $('.spinTopics').slideToggle();
        $('#topicNoRecord').hide();
        $('#slideNoRec').text('-').show();
        // $('#topicNoRecord').text('Please Wait...');
        $http({
            method: 'POST',
            url: 'code/LA-SLIDE-TOPIC-LIST.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getUnderTopics');
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                // formData.append("ddlLocation", 1);
                formData.append("ddlGrade", $scope.temp.ddlGrade);
                formData.append("ddlSubject", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getUnderTopics = data.data.data['data'];
            $scope.TOPIC_LIST_EXIST = data.data.data['success'];
            // if($scope.TOPIC_LIST_EXIST){
            //     $('.underTopic').hide();
            // }else{
            //     // $scope.post.getUnderTopics="<h2 class='text-center mb-0' id=''>No Record</h2>";
            // }
            // alert(data.data['data']);
            $('.spinTopics').slideToggle();
            if(!$scope.TOPIC_LIST_EXIST)$('#topicNoRecord').delay(2000).text('No Record').show();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    //  $scope.getUnderTopics();
    /* ========== GET UNDER TOPIC =========== */


    // ######################################################
    //                    HEADING START
    // ######################################################
    $scope.editModeSlideHead = false;
    /* ========== GET SLIDE HEADS =========== */
    $scope.SELECTED_TOPICID = 0;
    $scope.SELECTED_TOPIC = '';
    $scope.getSlideHeads = function(TOPICID,TOPIC){
        // $scope.txtFinalizedDT = [];
        // $scope.txtConfiguredDT = [];
        $scope.temp.slideid='';
        $scope.temp.txtHeadingName='';
        $scope.SELECTED_TOPICID = TOPICID;
        $scope.SELECTED_TOPIC = TOPIC;
        $scope.SLIDEID_IS = 0;
        // $scope.issueText = '';
        $('.spinSlide').slideToggle();
        $('.spinSlideHead').show();
        $('#slideNoRec').hide();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSlideHeads');
                formData.append("TOPICID", TOPICID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if(data.data.success){
                $scope.post.getSlideHeads = data.data.data;
                $scope.openIssue = data.data.data.map(x=>x.OPEN_ISSUE);
                $scope.openIssueStudent = data.data.data.map(x=>x.OPEN_ISSUE_STUDENT);
                $scope.openIssueTeacher = data.data.data.map(x=>x.OPEN_ISSUE_TEACHER);
                // console.log(data.data.data.map(x=>x.OPEN_ISSUE));

                $scope.post.getSlideHeads.forEach((x,i) => {
                    // console.log(i);
                    $scope.txtFinalizedDT[i] = (!x.FINALIZED_DT || x.FINALIZED_DT == '') ? '' : new Date(x.FINALIZED_DT);
                    $scope.txtConfiguredDT[i] = (!x.CONFIGURED_DT || x.CONFIGURED_DT == '') ? '' : new Date(x.CONFIGURED_DT);
                    $scope.txtSlideReadyDT[i] = (!x.SLIDEREADY_DT || x.SLIDEREADY_DT == '') ? '' : new Date(x.SLIDEREADY_DT);
                    $scope.txtConfigReadyDT[i] = (!x.CONFIGREADY_DT || x.CONFIGREADY_DT == '') ? '' : new Date(x.CONFIGREADY_DT);
                });
            }else{
                $scope.post.getSlideHeads = [];
                $scope.openIssue = [];
                $scope.openIssueStudent = [];
                $scope.openIssueTeacher = [];
                $('.slideContentNoRecord').show();
                $('#slideNoRec').text('No Record Found.').show();
            }
            // alert(data.data['data']);
            $('.spinSlideHead').hide();
            $('.spinSlide').slideToggle();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
        // alert(TOPICID+'////'+TOPIC)
    }
    $scope.clearSlideHeadForm=function(){
        $scope.temp.slideid='';
        $scope.temp.txtHeadingName='';
    }

    /* ========== SAVE SLIDE HEADS =========== */
    $scope.saveSlideHeading = function(){
        $(".btnSlideHeadSave").attr('disabled', 'disabled').text('Saving...');
        $(".btnSlideHeadUpd").attr('disabled', 'disabled').text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveSlideHeading');
                formData.append("slideid", $scope.temp.slideid);
                formData.append("topicid", $scope.SELECTED_TOPICID);
                formData.append("txtHeadingName", $scope.temp.txtHeadingName);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.clearSlideHeading();
                $scope.getSlideHeads($scope.SELECTED_TOPICID,$scope.SELECTED_TOPIC);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnSlideHeadSave').removeAttr('disabled').text('SAVE');
            $('.btnSlideHeadUpd').removeAttr('disabled').text('UPDATE');
        });
    }

    /* ============ Edit Button ============= */ 
    $scope.editSlideHeading = function (id) {
        // $('#headingModal').modal('show');
        $('#txtHeadingName').focus();
        $scope.temp.slideid = id.SLIDEID;
        $scope.temp.txtHeadingName = id.SLIDEHEADING;
        $scope.index = $scope.post.getSlideHeads.indexOf(id);
        $scope.editModeSlideHead = true;
    }
    
    $scope.clearSlideHeading = function(){
        $('#txtHeadingName').focus();
        $scope.temp.slideid='';
        $scope.temp.txtHeadingName ='';
        $scope.editModeSlideHead=false;
    }

    /* ========== DELETE =========== */
    $scope.deleteSlideHeading = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SLIDEID': id.SLIDEID, 'type': 'deleteSlideHeading' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                //  console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getSlideHeads.indexOf(id);
                    $scope.post.getSlideHeads.splice(index, 1);
                    // console.log(data.data.message)
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }
        

    /* ========== OPEN / CLOSE ISSUE =========== */
    $scope.openIssue = [];
    $scope.openCloseIssue=function(id,val,index,BO_ST){
        // console.log(id);
        if(val == 1){
            if(BO_ST=='BO'){
                $(".issueInput"+index).attr('disabled', 'disabled');
                $(".spinOpenIssue"+index).html('<i class="fa fa-spin fa-spinner"></i>');
            }
            else if(BO_ST=='ST'){
                $(".issueInputStudent"+index).attr('disabled', 'disabled');
                $(".spinopenIssueStudent"+index).html('<i class="fa fa-spin fa-spinner"></i>');
            }
            else if(BO_ST=='TH'){
                $(".issueInputTeacher"+index).attr('disabled', 'disabled');
                $(".spinopenIssueTeacher"+index).html('<i class="fa fa-spin fa-spinner"></i>');
            }
            // alert($scope.temp.ddlCollege);
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'openCloseIssue');
                    formData.append("FOR", 'OPEN');
                    formData.append("BO_ST", BO_ST);
                    formData.append("SLIDEID", id.SLIDEID);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.messageSuccess(data.data.message);
                    $scope.getSlideHeads($scope.SELECTED_TOPICID,$scope.SELECTED_TOPIC);
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
                if(BO_ST=='BO'){
                    $(".issueInput"+index).removeAttr('disabled');
                    $(".spinOpenIssue"+index).html('OPENED');
                }else if(BO_ST=='ST'){
                    $(".issueInputStudent"+index).removeAttr('disabled');
                    $(".spinopenIssueStudent"+index).html('OPENED');
                }else if(BO_ST=='TH'){
                    $(".issueInputTeacher"+index).removeAttr('disabled');
                    $(".spinopenIssueTeacher"+index).html('OPENED');
                }
            });
        }else if(val == 0){
            var r = confirm("Are you sure want to close this Issue!");
            if(r == true) {
                if(BO_ST=='BO') $(".issueInput"+index).attr('disabled', 'disabled');
                if(BO_ST=='ST') $(".issueInputStudent"+index).attr('disabled', 'disabled');
                if(BO_ST=='TH') $(".issueInputTeacher"+index).attr('disabled', 'disabled');
                $http({
                    method: 'POST',
                    url: url,
                    processData: false,
                    transformRequest: function (data) {
                        var formData = new FormData();
                        formData.append("type", 'openCloseIssue');
                        formData.append("FOR", 'CLOSE');
                        formData.append("BO_ST", BO_ST);
                        formData.append("SLIDEID", id.SLIDEID);
                        return formData;
                    },
                    data: $scope.temp,
                    headers: { 'Content-Type': undefined }
                }).
                then(function (data, status, headers, config) {
                    // console.log(data.data);
                    if (data.data.success) {
                        $scope.messageSuccess(data.data.message);
                        $scope.getSlideHeads($scope.SELECTED_TOPICID,$scope.SELECTED_TOPIC);
                    }
                    else {
                        $scope.messageFailure(data.data.message);
                        // console.log(data.data)
                    }
                    if(BO_ST=='BO') $(".issueInput"+index).removeAttr('disabled');
                    if(BO_ST=='ST') $(".issueInputStudent"+index).removeAttr('disabled');
                    if(BO_ST=='TH') $(".issueInputTeacher"+index).removeAttr('disabled');
                });
            }else{
                $scope.openIssue[index] = val==1?'0':'1';
                // console.log($scope.openIssue);
            }
        }else{
            $scope.messageFailure('Error : Open Issue invalid.');
        }
    }

    /* ========== UPDATE ISSUE =========== */
    $scope.updateIssue=function(){
        $(".btnIssueUpd").attr('disabled', 'disabled').text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateIssue');
                formData.append("SLIDEID", $scope.SLIDEID_IS);
                formData.append("issueText", $scope.issueText);
                formData.append("IssueFor", $scope.IssueFor);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $('#IssueModal').modal('hide');
                $scope.messageSuccess(data.data.message);
                $scope.getSlideHeads($scope.SELECTED_TOPICID,$scope.SELECTED_TOPIC);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btnIssueUpd").removeAttr('disabled').text('Update');
        });
    }
    $scope.getIsuuesDet = function(id,FOR){
        $scope.IssueFor = FOR;
        $scope.SLIDEID_IS = id.SLIDEID;
        if(FOR=='BO'){
            $scope.issueText = id.ISSUE_REMARKS;
        }else if(FOR=='ST'){
            $scope.issueText = id.ISSUE_REMARKS_STUDENT;
        }else if(FOR=='TH'){
            $scope.issueText = id.ISSUE_REMARKS_TEACHER;
        }else{
            $scope.issueText = '';
        }
        $('#issueText').focus();
    }



    /* ========== FINALIZED OR CONFIGURED DATE =========== */
    $scope.updateFinalConfigDate = function(VAL,DATE_TYPE,id){
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateFinalConfigDate');
                formData.append("SLIDEID", id.SLIDEID);
                formData.append("DATE", !VAL || VAL=='' ? '' : VAL.toLocaleString('sv-SE'));
                formData.append("DATE_TYPE", DATE_TYPE);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getSlideHeads($scope.SELECTED_TOPICID,$scope.SELECTED_TOPIC);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            // $(".btnIssueUpd").removeAttr('disabled').text('Update');
        });
    }
    // ######################################################
    //                    HEADING END
    // ######################################################










    // ######################################################
    //                      SLIDE START
    // ######################################################
    $scope.files=[];
    /*========= Image Preview =========*/ 
    $scope.UploadImage = function (element) {
        $scope.currentFile = element.files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            $scope.slide_src = event.target.result
            $scope.$apply(function ($scope) {
                $scope.files = element.files;
            });
        }
        reader.readAsDataURL(element.files[0]);
    }
    /*========= Image Preview =========*/ 

    $scope.openSlideModal = function(id){
        $('#txtSeqNo').focus();
        $scope.SELECTED_SLIDEID = id.SLIDEID;
        $scope.HEADNAME = id.SLIDEHEADING;
        $scope.clearSlideForm();
        $scope.getSlides();
    }
    $scope.clearSlideForm=()=>{
        $('#txtSeqNo').focus();
        $scope.temp.slidedetid='';
        $scope.temp.txtSeqNo='';
        $scope.temp.ddlContentType='';
        $scope.clearSlideContent();
    }
    $scope.clearSlideContent =() =>{
        $scope.temp.txtSlidTextContent = '';
        $scope.clearSlideImage();
    }
    $scope.clearSlideImage=()=>{
        angular.element('#txtContentImage').val(null);
        $scope.slide_src = '';
        $scope.files=[];
        $scope.temp.existingContentImage='';
    };

    /* ========== SAVE SLIDE =========== */
    $scope.saveSlide = function(){
        $(".btnSlideSave").attr('disabled', 'disabled').text('Saving...');
        $(".btnSlideUpd").attr('disabled', 'disabled').text('Updating...');
        $scope.temp.txtContentImage = $scope.files[0];
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveSlide');
                formData.append("slidedetid", $scope.temp.slidedetid);
                formData.append("slideid", $scope.SELECTED_SLIDEID);
                formData.append("txtSeqNo", $scope.temp.txtSeqNo);
                formData.append("ddlContentType", $scope.temp.ddlContentType);
                formData.append("txtSlidTextContent", $scope.temp.txtSlidTextContent);
                formData.append("txtContentImage", $scope.temp.txtContentImage);
                formData.append("existingContentImage", $scope.temp.existingContentImage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.clearSlideForm();
                $scope.getSlides();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btnSlideSave').removeAttr('disabled').text('SAVE');
            $('.btnSlideUpd').removeAttr('disabled').text('UPDATE');
        });
    }
    
    /* ========== GET SLIDE =========== */
    $scope.getSlides = function(){
        $('.spinSlides').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSlides');
                formData.append("slideid", $scope.SELECTED_SLIDEID);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if(data.data.success){
                $scope.post.getSlidesDATA = data.data.data;
            }else{
                $scope.post.getSlidesDATA = [];
            }
            $('.spinSlides').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    /* ============ Edit Button ============= */ 
    $scope.editSlide = function (id) {
        // $('#headingModal').modal('show');
        $('#txtSeqNo').focus();
        $scope.temp.slidedetid = id.SLIDEDETID;
        $scope.temp.txtSeqNo = Number(id.SEQNO);
        $scope.temp.ddlContentType = id.CONTENT_TYPE;
        $scope.temp.txtSlidTextContent = id.CONTENT_TYPE=='TEXT' ? id.CONTENT : '';
        /*########### IMG #############*/
        if(id.CONTENT_TYPE=='IMAGE' || id.CONTENT_TYPE=='PDF'){
            $scope.temp.existingContentImage = id.CONTENTFILE;
            if(id.CONTENTFILE != ''){
                $scope.slide_src='images/slides/'+id.CONTENTFILE;
            }else{
                $scope.slide_src='images/slides/default.png';
            }
        }else{
            $scope.clearSlideImage();
        }

        $scope.index = $scope.post.getSlidesDATA.indexOf(id);
        $scope.editModeSlideHead = true;
    }


    /* ========== DELETE =========== */
    $scope.deleteSlide = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'SLIDEDETID': id.SLIDEDETID, 'type': 'deleteSlide' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                //  console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getSlidesDATA.indexOf(id);
                    $scope.post.getSlidesDATA.splice(index, 1);
                    // console.log(data.data.message)
                    
                    $scope.messageSuccess(data.data.message);
                } else {
                    $scope.messageFailure(data.data.message);
                }
            })
        }
    }


    $scope.openSlideViewModal = function(id){
        $scope.HEADNAME = id.SLIDEHEADING;
        $scope.SELECTED_SLIDEID = id.SLIDEID;
        $scope.getSlides();


    }
    // ######################################################
    //                      SLIDE END
    // ######################################################

    






    /* ========== GET Location =========== */
    $scope.getUserLocationsWithMainLocation = function () {
        $http({
            method: 'POST',
            url: masterUrl,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getUserLocationsWithMainLocation');
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if(data.data.success){
                $scope.post.getLocations = data.data.data;
                $scope.temp.ddlLocation = ($scope.post.getLocations) ? $scope.locid.toString():'';
            }else{
                $scope.messageFailure('Location Not found.');
            }
            if($scope.temp.ddlLocation > 0) $scope.getTopics();
            // if($scope.temp.ddlLocation > 0) $scope.getGrades();
            // if($scope.temp.ddlLocation > 0) $scope.getSubjects();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */

    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $('.spinGrade').show();
        $http({
            method: 'POST',
            url: 'code/LA-GRADE-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
                // formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlLocation", 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getGrades = data.data.success ? data.data.data : [];
            $('.spinGrade').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $('.spinSubject').show();
        $http({
            method: 'POST',
            url: 'code/LA-SUBJECT-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjects');
                // formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlLocation", 1);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubjects = data.data.success ? data.data.data : [];
            $('.spinSubject').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
     $scope.getSubjects();
    /* ========== GET SUBJECT =========== */






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


    $scope.eyepass = function(For,index) {
        if(For == 'MPASS'){
            var input = $("#txtMeetingPasscode");
        }else if(For == 'EPASS'){
            var input = $("#txtEmailPassword");
        }
        
        // var input = $("#txtMeetingPasscode");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon'+index).removeClass('fa-eye');
            $('.Eyeicon'+index).addClass('fa-eye-slash');
        }else{
            $('.Eyeicon'+index).removeClass('fa-eye-slash');
            $('.Eyeicon'+index).addClass('fa-eye');
        }
    };

});