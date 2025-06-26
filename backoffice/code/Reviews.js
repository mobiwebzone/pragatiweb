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
    $scope.Page = "MISC";
    $scope.PageSub = "REVIEW";
    $scope.temp.txtETADate = new Date();
    $scope.files = [];
    $scope.Status = ['Open','Called','Texted','Called & Texted','Closed - Did not write','Review Written'],
    
    // ========= PEGINATION =============
    $scope.serial = 1;
    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    // ========= PEGINATION =============
    
    var url = 'code/Reviews_code.php';



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

                $scope.getLocations();
                $scope.getReviews();
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
        // alert($scope.temp.ShowInHome);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveData');
                formData.append("rvid", $scope.temp.rvid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlSTCategory", $scope.temp.ddlSTCategory);
                formData.append("ddlStudent", $scope.temp.ddlStudent);
                formData.append("txtFirstName", $scope.temp.txtFirstName);
                formData.append("txtLastName", $scope.temp.txtLastName);
                formData.append("ddlReviewBy", $scope.temp.ddlReviewBy);
                formData.append("txtReviewByName", $scope.temp.txtReviewByName);
                formData.append("txtPhone", $scope.temp.txtPhone);
                formData.append("txtEmail", $scope.temp.txtEmail);
                formData.append("ddlLocReview", $scope.temp.ddlLocReview);
                formData.append("txtComment", $scope.temp.txtComment);
                formData.append("ddlStatus", $scope.temp.ddlStatus);
                formData.append("ShowInHome", $scope.temp.ShowInHome ? 1 : 0);
                formData.append("txtReviewDT", ($scope.temp.txtReviewDT && $scope.temp.txtReviewDT!='') ? $scope.temp.txtReviewDT.toLocaleString('sv-SE'):'');
                formData.append("txtReview", $scope.temp.txtReview);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                // $scope.clearForm();
                $scope.getReviews();
                $("#ddlLocation").focus();
                $scope.messageSuccess(data.data.message);

                $scope.temp.rvid = '';
                $scope.temp.ddlStudent = '';
                $scope.temp.txtFirstName = '';
                $scope.temp.txtLastName = '';
                $scope.temp.ddlReviewBy = '';
                $scope.temp.txtReviewByName = '';
                $scope.temp.txtPhone = '';
                $scope.temp.txtEmail = '';
                $scope.temp.ddlLocReview = '';
                $scope.temp.txtComment = '';
                $scope.temp.ddlStatus = '';
                $scope.temp.ShowInHome = false;
                $scope.temp.txtReviewDT = '';
                $scope.temp.txtReview = '';
                
                
                $scope.DETAILS = [];
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







    /* ========== GET REVIEWS =========== */
    $scope.getReviews = function () {
        $('#SpinnerReviews').show();
        $scope.post.getTestSection = [];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getReviews','ddlSearchStatus':$scope.temp.ddlSearchStatus}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getReviews = data.data.data;
            }else{
                $scope.post.getReviews=[];
                // console.info(data.data.message);
            }
            $('#SpinnerReviews').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getReviews(); --INIT
    /* ========== GET REVIEWS =========== */


    

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
            $('.spinLoc').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */




    
    
    /* ========== GET STUDENT BY LOCATION =========== */
    $scope.getStudentByLoc = function () {
        $('.spinUser').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getStudentByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getStudentByLoc = data.data.data;
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




    
    
    /* ========== SET STUDENT DETAILS =========== */
    $scope.DETAILS = [];
    $scope.setStudentDetails = () => {
        $scope.temp.txtFirstName = '';
        $scope.temp.txtLastName = '';
        $scope.DETAILS = [];
        $scope.temp.txtReviewByName = '';
        $scope.temp.txtPhone = '';
        $scope.temp.txtEmail = '';
        $scope.temp.ddlReviewBy = '';

        if($scope.temp.ddlStudent > 0){
            $scope.DETAILS = $scope.post.getStudentByLoc.filter((x)=> x.REGID === Number($scope.temp.ddlStudent));  
            // console.log($scope.DETAILS);

            $scope.temp.txtFirstName = $scope.DETAILS[0]['FIRSTNAME'];
            $scope.temp.txtLastName = $scope.DETAILS[0]['LASTNAME'];
        }else{
            $scope.temp.txtFirstName = '';
            $scope.temp.txtLastName = '';
            $scope.DETAILS = [];
        }
    }
    /* ========== SET STUDENT DETAILS =========== */




    
    
    /* ========== SET REVIEW BY DETAILS =========== */
    $scope.setReviewByDetails = () => {
        $scope.temp.txtReviewByName = '';
        $scope.temp.txtPhone = '';
        $scope.temp.txtEmail = '';
        if($scope.temp.ddlStudent > 0){
            if($scope.temp.ddlReviewBy === 'SELF'){
                $scope.temp.txtReviewByName = `${$scope.DETAILS[0]['FIRSTNAME']} ${$scope.DETAILS[0]['LASTNAME']}`;
                $scope.temp.txtPhone = $scope.DETAILS[0]['PHONE'] == 'null' ? '' : $scope.DETAILS[0]['PHONE'];
                $scope.temp.txtEmail = $scope.DETAILS[0]['EMAIL'] == 'null' ? '' : $scope.DETAILS[0]['EMAIL'];
            }else if($scope.temp.ddlReviewBy === 'PARENT1'){
                $scope.temp.txtReviewByName = `${$scope.DETAILS[0]['P1_FIRSTNAME']} ${$scope.DETAILS[0]['P1_LASTNAME']}`;
                $scope.temp.txtPhone = $scope.DETAILS[0]['P1_PHONE'] == 'null' ? '' : $scope.DETAILS[0]['P1_PHONE'];
                $scope.temp.txtEmail = $scope.DETAILS[0]['P1_EMAIL'] == 'null' ? '' : $scope.DETAILS[0]['P1_EMAIL'];
            }else{
                $scope.temp.txtReviewByName = `${$scope.DETAILS[0]['P2_FIRSTNAME']} ${$scope.DETAILS[0]['P2_LASTNAME']}`;
                $scope.temp.txtPhone = $scope.DETAILS[0]['P2_PHONE'] == 'null' ? '' : $scope.DETAILS[0]['P2_PHONE'];
                $scope.temp.txtEmail = $scope.DETAILS[0]['P2_EMAIL'] == 'null' ? '' : $scope.DETAILS[0]['P2_EMAIL'];
            }
        }
        else{
            $scope.temp.txtReviewByName = '';
            $scope.temp.txtPhone = '';
            $scope.temp.txtEmail = '';
        }
    }
    /* ========== SET REVIEW BY DETAILS =========== */




    
    
    /* ========== GET LOC REVIEW BY LOCATION =========== */
    $scope.getLocReviewByLoc = function () {
        $('.spinLocRevi').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLocReviewByLoc', 'ddlLocation' : $scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if(data.data.success){
                $scope.post.getLocReviewByLoc = data.data.data;
            }else{
                $scope.post.getLocReviewByLoc = [];
            }
            $('.spinLocRevi').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocReviewByLoc();
    /* ========== GET LOC REVIEW BY LOCATION =========== */




    // $query = "SELECT RVID,LOCID,(SELECT [LOCATION] FROM LOCATIONS WHERE LOC_ID=R.LOCID)[LOCATION],
    // STUDENT_CATEGORY,REGID,FIRSTNAME,LASTNAME,REVIEW_BY,REVIEWBY_NAME,PHONE,EMAILID,REVID,
    // (SELECT REVIEWMEDIA FROM LOCATION_REVIEWS WHERE REVID=R.REVID)REVIEWMEDIA,
    // (SELECT REVIEWLINK FROM LOCATION_REVIEWS WHERE REVID=R.REVID)REVIEWLINK,
    // COMMENTS_GIVEN 
    // FROM REVIEWS R WHERE ISDELETED=0 ORDER BY [LOCATION]";

    /* ============ Edit Button ============= */ 
    $scope.editForm = function (id) {
        $("#ddlLocation").focus();
        
        $scope.temp.rvid = id.RVID;
        $scope.temp.ddlLocation = (id.LOCID).toString();
        if($scope.temp.ddlLocation > 0){
            $scope.getStudentByLoc();
            $scope.getLocReviewByLoc();
            $scope.temp.ddlSTCategory = id.STUDENT_CATEGORY;
            $timeout(()=>{
                $scope.temp.ddlStudent = id.STUDENT_CATEGORY === 'REGISTERED' ? id.REGID.toString() : '';
            },1000);
        }
        $scope.temp.txtFirstName = id.FIRSTNAME;
        $scope.temp.txtLastName = id.LASTNAME;
        $scope.temp.ddlReviewBy = id.REVIEW_BY;
        $scope.temp.txtReviewByName = id.REVIEWBY_NAME;
        $scope.temp.txtPhone = id.PHONE;
        $scope.temp.txtEmail = id.EMAILID;
        $scope.temp.ddlLocReview = id.REVID.toString();
        $scope.temp.txtComment = id.COMMENTS_GIVEN;
        $scope.temp.ddlStatus = id.STATUS;
        $scope.temp.ShowInHome = id.SHOW_IN_HOMEPAGE==0?false:true;
        $scope.temp.txtReviewDT = id.REVIEW_DATE!=''?new Date(id.REVIEW_DATE):'';
        $scope.temp.txtReview = id.REVIEW;
        
        $scope.editMode = true;
        $scope.index = $scope.post.getReviews.indexOf(id);
    }
    /* ============ Edit Button ============= */ 
    
    


    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        $("#ddlLocation").focus();
        $scope.temp={};
        $scope.DETAILS = [];
        $scope.editMode = false;
        $scope.temp.ShowInHome = false;
    }
    /* ============ Clear Form =========== */ 




    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'rvid': id.RVID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getReviews.indexOf(id);
		            $scope.post.getReviews.splice(index, 1);
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