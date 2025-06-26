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
    $scope.Page = "LA";
    $scope.PageSub = "FRANCHISEMANAGEMENT";
    $scope.PageSub2 = "FRANCHISEBACKOFFICE";
    
    $scope.editMode = false;
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/Franchise_BackOffice_code.php';

    $scope.dateFormat=function(datetime){
        if(datetime!=undefined){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+datetime.getDate();        
        }
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

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getFranchise();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
                // window.location.assign("dashboard.html");
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

    $scope.saveFranchise = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveFranchise');
                formData.append("faid", $scope.temp.faid);
                formData.append("txtfirstname", $scope.temp.txtfirstname);
                formData.append("txtmiddlename", $scope.temp.txtmiddlename);
                formData.append("txtlastname", $scope.temp.txtlastname);
                formData.append("txtdob", $scope.dateFormat($scope.temp.txtdob));                
                //formData.append("txtdob", $scope.temp.txtdob.getFullYear() + "/" + $scope.temp.txtdob.getMonth() + "/" + $scope.temp.txtdob.getDate());                
                formData.append("txtcellphone", $scope.temp.txtcellphone);
                formData.append("txtemail", $scope.temp.txtemail);
                formData.append("txtaddress1", $scope.temp.txtaddress1);
                formData.append("txtaddress2", $scope.temp.txtaddress2);
                formData.append("txtcity", $scope.temp.txtcity);
                formData.append("txtstate", $scope.temp.txtstate);
                formData.append("txtzip", $scope.temp.txtzip);
                formData.append("txtcitizen", $scope.temp.txtcitizen);
                formData.append("txteducatBack", $scope.temp.txteducatBack);
                formData.append("txtjobexp", $scope.temp.txtjobexp);
                formData.append("txtbusiness", $scope.temp.txtbusiness);
                formData.append("txttutoringexp", $scope.temp.txttutoringexp);
                formData.append("txtliquidfin", $scope.temp.txtliquidfin);
                formData.append("txtlistallfel", $scope.temp.txtlistallfel);
                formData.append("txtlistallpast", $scope.temp.txtlistallpast);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getFranchise();
                $scope.clearForm();
                
                document.getElementById("txtfirstname").focus();
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


     /* ========== GET Countries =========== */
     $scope.getFranchise = function () {
        $scope.spinMain = true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getFranchise'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getFranchise = data.data.success ? data.data.data : [];
            $scope.spinMain = false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getFranchise(); --INIT
    
   
    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        document.getElementById("txtfirstname").focus();
        $scope.temp = {
            faid : id.FAID,
            txtfirstname : id.FIRSTNAME,
            txtmiddlename : id.MIDDLENAME,
            txtlastname : id.LASTNAME,
            txtdob : !id.BIRTHDATE ? '' : new Date(id.BIRTHDATE),
            txtcellphone : id.PHONE,
            txtemail : id.EMAILID,
            txtaddress1 : id.ADDRESS1,
            txtaddress2 : id.ADDRESS2,
            txtcity : id.CITY,
            txtstate : id.STATE,
            txtzip : id.ZIPCODE,
            txtcitizen : id.CITIZEN,
            txteducatBack : id.EDUCATION,
            txtjobexp : id.JOBEXP,
            txtbusiness : id.BUSIEXP,
            txttutoringexp : id.TUTEXP,
            txtliquidfin : id.LIQFINRESOURCE,
            txtlistallfel : id.FELONY,
            txtlistallpast : id.PASTPERSONAL
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getFranchise.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("txtfirstname").focus();
        $scope.temp={};
        $scope.editMode = false;
    }



    /* ========== DELETE =========== */
    $scope.deleteFranchise = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'faid': id.FAID, 'type': 'deleteFranchise' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getFranchise.indexOf(id);
		            $scope.post.getFranchise.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearForm();
                    
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