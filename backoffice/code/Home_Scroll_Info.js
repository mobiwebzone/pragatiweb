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
    $scope.Page = "SETTING";
    $scope.PageSub = "ANNOUNCE";
    $scope.TDate = new Date();
    $scope.temp.txtAMDate = new Date();
    $scope.temp.txtTillDate = new Date();
    $scope.temp.chkShowInDB =false;
    $scope.serial = 1;
    $scope.temp.txtColor = '#000000';
    $scope.temp.txtPassage = '';

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }

    // ========= TEXT EDITOR =========
    taOptions.toolbar = [
        ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre','bold', 'italics', 'underline', 'strikeThrough', 'redo', 'undo', 'clear']
    ];
    // ========= TEXT EDITOR =========
    
    var url = 'code/Home_Scroll_Info.php';


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
                // window.location.assign("dashboard.html");

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getScrolls();
                }
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
        $(".btn-save").attr('disabled', true).text('Saving...');
        $(".btn-update").attr('disabled', true).text('Updating...');
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("infoid", $scope.temp.infoid);
                formData.append("ddlDivNumber", $scope.ddlDivNumber);
                formData.append("txtInfo", $scope.temp.txtInfo);
                formData.append("txtInfoLink", $scope.temp.txtInfoLink);
                formData.append("txtFromDT", $scope.temp.txtFromDT.toLocaleDateString('sv-SE'));
                formData.append("txtToDT", $scope.temp.txtToDT.toLocaleDateString('sv-SE'));
                formData.append("txtSEQNo", $scope.temp.txtSEQNo);
                formData.append("txtColor", $scope.temp.txtColor);
                formData.append("isHeader", $scope.temp.isHeader);
                formData.append("txtHeader", $scope.temp.txtHeader);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getScrolls();
                $scope.clear();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $(".btn-save").attr('disabled', true).text('SAVE');
            $(".btn-update").attr('disabled', true).text('UPDATE');
        });
    }


     /* ========== GET SCROLLS =========== */
     $scope.getScrolls = function () {
        $scope.spinScrolls = true;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'getScrolls');
                formData.append("ddlDivNumber", $scope.ddlDivNumber);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getScrolls = data.data.success ? data.data.data : [];
            $scope.spinScrolls = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getScrolls(); --INIT



    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("txtInfo").focus();
        $scope.ddlDivNumber = id.DIV_NO.toString();
        $scope.temp.infoid = id.INFOID;
        $scope.temp.txtInfo = id.INFO;
        $scope.temp.txtInfoLink = id.INFO_LINK;
        $scope.temp.txtFromDT = new Date(id.DISPLAY_FROM);
        $scope.temp.txtToDT = new Date(id.DISPLAY_TO);
        $scope.temp.txtSEQNo = Number(id.SEQNO);
        $scope.temp.txtColor = id.COLOR
        $scope.temp.isHeader= id.ISHEADER;
        $scope.temp.txtHeader= id.HEADER;

        $scope.editMode = true;
        $scope.index = $scope.post.getScrolls.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        document.getElementById("txtInfo").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.txtColor = '#000000';
        $scope.temp.isHeader='0';
        $scope.getScrolls();
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'INFOID': id.INFOID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getScrolls.indexOf(id);
		            $scope.post.getScrolls.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clear();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
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