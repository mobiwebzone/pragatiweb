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
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,$sce) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "QUESTION_SECTION";
    $scope.PageSub = "MAP_SET_TO_LA";
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/MAP_SECTION_TOPIC_TO_LA_TOPIC.php';




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

                $scope.getSectionMaster();
                $scope.getGrades();
                $scope.getSubjects();
                // $scope.getTestMaster();
                // $scope.getRubericData();
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
    $scope.saveLaTopics = function(){
        // console.log($scope.selectedLATopics.map(x=>x.TOPICID))
        // return;
        $(".btnSave").attr('disabled', 'disabled').text('Saving...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveLaTopics');
                formData.append("ddlTopic", $scope.temp.ddlTopic);
                formData.append("selectedLATopics", !$scope.selectedLATopics ? [] : $scope.selectedLATopics.map(x=>x.TOPICID));
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);

                $scope.selectedLATopics=[];
                $scope.temp.ddlTopicLA = '';
                $scope.temp.ddlSubject = '';
                $scope.getMappedData();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }

            $(".btnSave").removeAttr('disabled').text('SAVE');
        });
    }
    // =========== SAVE DATA ==============



    /* ========== GET MAPPED DATA =========== */
    $scope.getMappedData = function () {
        $scope.post.getMappedData=[];
        $scope.selectedSectionTopic = '';
        if(!$scope.temp.ddlSubCategory || $scope.temp.ddlSubCategory<=0 || !$scope.temp.ddlTopic || $scope.temp.ddlTopic<=0) return;
        $scope.selectedSectionTopic = $('#ddlTopic option:selected').text();
        $('.spinMainData').show();
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getMappedData');
                formData.append("SECID", $scope.temp.ddlSection);
                formData.append("CATID", $scope.temp.ddlCategory);
                formData.append("SUBCATID", $scope.temp.ddlSubCategory);
                formData.append("TOPICID", $scope.temp.ddlTopic);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getMappedData = data.data.success ? data.data.data : [];
            // if(!data.data.success) $scope.messageFailure(data.data.message);
            $('.spinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    /* ========== GET MAPPED DATA =========== */



    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'STLATID': id.STLATID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getMappedData.indexOf(id);
		            $scope.post.getMappedData.splice(index, 1);
		            // console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

    // ##############################################
    // ################################# OTHER
    // ##############################################


    /* ========== GET SECTION MASTER =========== */
    $scope.getSectionMaster = function () {
        $('.SectionSpin').show();
        $http({
            method: 'post',
            url: 'code/Question_Master_code.php',
            data: $.param({ 'type': 'getSectionMaster'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSectionMaster = data.data.data;

            $('.SectionSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSectionMaster(); --INIT
    /* ========== GET SECTION MASTER =========== */





    /* ========== GET CATEGORIES =========== */
    $scope.getCategories = function (secid) {
        $('.CategorySpin').show();
        $scope.post.getCategories=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getCategories', 'secid' : secid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getCategories = data.data.data;
            $('.CategorySpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getCategories();
    /* ========== GET CATEGORIES =========== */




    /* ========== GET SUB CATEGORIES =========== */
    $scope.getSubCategories = function (catid) {
        $('.SubCatSpin').show();
        $scope.post.getSubCategories=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getSubCategories', 'catid' : catid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getSubCategories = data.data.data;
            $('.SubCatSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getSubCategories();
    /* ========== GET SUB CATEGORIES =========== */




    /* ========== GET TOPICS =========== */
    $scope.getTopic = function (subcatid) {
        $('.TopicSpin').show();
        $scope.post.getTopic=[];
        $http({
            method: 'post',
            url: 'code/Question_Categories_code.php',
            data: $.param({ 'type': 'getTopic', 'subcatid' : subcatid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getTopic = data.data.data;
            $('.TopicSpin').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getTopic();
    /* ========== GET TOPICS =========== */
    


    /* ========== GET GRADES =========== */
    $scope.getGrades = function () {
        $scope.post.getGrades=[];
        $('.spinGrade').show();
        $http({
            method: 'POST',
            url: 'code/LA-GRADE-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getGrades');
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
    //  $scope.getGrades();
    /* ========== GET GRADES =========== */

    /* ========== GET SUBJECT =========== */
    $scope.getSubjects = function () {
        $scope.post.getSubjects=[];
        $('.spinSubject').show();
        $http({
            method: 'POST',
            url: 'code/LA-SUBJECT-MASTER.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getSubjects');
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
    //  $scope.getSubjects();
    /* ========== GET SUBJECT =========== */

    /* ========== GET LA TOPICS =========== */
    $scope.getLaTopics = function () {
        $scope.temp.ddlTopicLA='';
        $scope.post.getLATopics=[];
        $scope.selectDesign = '';
        $scope.post.getSlides=[];
        $scope.SLIDE_model = [];
        if(!$scope.temp.ddlGrade || $scope.temp.ddlGrade<=0 || !$scope.temp.ddlSubject || $scope.temp.ddlSubject<=0)return;
        $('.spinTopic').show();
        $http({
            method: 'POST',
            url: 'code/LA-TOPICS-COVERED.php',
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getTopicsByLoc_Grade_Subject');
                formData.append("LOCID", 1);
                formData.append("GRADEID", $scope.temp.ddlGrade);
                formData.append("SUBID", $scope.temp.ddlSubject);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLATopics = data.data.success ? data.data.data : [];
            $scope.selectDesign = data.data.success ? $sce.trustAsHtml(data.data.finalData): '';
            $('.spinTopic').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }

    $scope.selectedLATopics = [];
    $scope.setTopicId=function(ADD_DELETE,optionElement){
        if(ADD_DELETE=='ADD'){
            var TOPICID=optionElement.value;
            $scope.temp.ddlTopicLA = optionElement.value.toString();
            var selectedTopic = $scope.post.getLATopics.filter(x=>x.TOPICID==TOPICID).map(x=>x.TOPIC).toString();
    
            if(TOPICID && TOPICID>0){
                var newData = {"TOPICID":TOPICID,"TOPICNAME":selectedTopic};
                var chkExist = $scope.selectedLATopics.some(x=>x.TOPICID==TOPICID);
                (chkExist) ? $scope.messageFailure(`"${selectedTopic}" Topic already selected.`) : $scope.selectedLATopics.push(newData);
            }
        }
        else if(ADD_DELETE=='REMOVE') {
            var indexOf = $scope.selectedLATopics.indexOf(optionElement);
            $scope.selectedLATopics.splice(indexOf,1);
        }
        // console.log($scope.selectedLATopics);
    }


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