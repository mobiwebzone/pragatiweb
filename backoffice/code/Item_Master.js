$postModule = angular.module("myApp", [ "ngSanitize"]);
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
    $scope.Page = "INVENTORY";
    $scope.PageSub = "VEN_MASTER";
    $scope.PageSub1="ITEM_MASTER";
   
    var url = 'code/ITEM_MASTER.php';
    
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    $scope.getLocations();
                    // $scope.getVendorDetails();
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

    $scope.save = function(){
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
                formData.append("type", 'save');
                formData.append("itemid", $scope.temp.itemid);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                formData.append("ddlItemCategory", $scope.temp.ddlItemCategory);
                formData.append("txtItem", $scope.temp.txtItem);
                formData.append("txtDesc", $scope.temp.txtDesc);
                formData.append("ddlPubliManu", $scope.temp.ddlPubliManu);
                formData.append("txtIsbn_Model", $scope.temp.txtIsbn_Model);
                formData.append("txtRemark", $scope.temp.txtRemark);
                formData.append("ddlItemMasterStorage", $scope.temp.ddlItemMasterStorage);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            //  console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getItemmaster();
                $scope.clear();
                // document.getElementById("ddlPlan").focus();
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


    /* ========== GET Item Master =========== */
    $scope.getItemmaster = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getItemmaster','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getItemmaster = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getItemmaster();
    /* ========== GET Item Master =========== */

    

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
            if($scope.temp.ddlLocation > 0) $scope.getItemmaster();
            if($scope.temp.ddlLocation > 0) $scope.getItemStorageMaster();
            if($scope.temp.ddlLocation > 0) $scope.getPubliManu();
            if($scope.temp.ddlLocation > 0) $scope.getItemcategory();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocations(); --INIT
    /* ========== GET Location =========== */


        
    /* ========== GET Item Storage Master STMID  Details =========== */
    $scope.getItemStorageMaster = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
           url: url,
           data: $.param({ 'type': 'getItemStorageMaster','ddlLocation':$scope.temp.ddlLocation}),
           headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
       }).
       then(function (data, status, headers, config) {
           // console.log(data.data);
           $scope.post.getItemStorageMaster = data.data.success ? data.data.data : [];
           $('#SpinMainData').hide();
       },
       function (data, status, headers, config) {
           console.log('Failed');
       })
    }
    // $scope.getItemStorageMaster();






    /* ========== GET Publishere Manufacturer  Details =========== */
    $scope.getPubliManu = function () {
         $('#SpinMainData').show();
         $http({
             method: 'post',
            url: url,
            data: $.param({ 'type': 'getPubliManu','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPubliManu = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPubliManu();



    /* ========== GET Item Category =========== */
    $scope.getItemcategory = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getItemcategory','ddlLocation':$scope.temp.ddlLocation}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getItemcategory = data.data.success ? data.data.data : [];
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getItemcategory();





    /* ============ Edit ITEM MASTER Details ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            itemid:id.ITEMID,
            ddlLocation: (id.LOCID).toString(),
            ddlItemCategory: (id.ICATID).toString(),
            txtItem: id.ITEM,
            txtDesc: id.ITEMDESC,
            ddlPubliManu: (id.PUBMANID).toString(),
            txtIsbn_Model: id.ISBN_MODEL_NO,
            txtRemark: id.REMARKS,
            ddlItemMasterStorage:(id.STMID).toString(),
           
        };
        $scope.editMode = true;
        $scope.index = $scope.post.getItemmaster.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        // $scope.temp={};
        $scope.temp.itemid='';
        $scope.temp.ddlItemCategory='';
        $scope.temp.txtItem='';
        $scope.temp.txtDesc='';
        $scope.temp.ddlPubliManu='';
        $scope.temp.txtIsbn_Model='';
        $scope.temp.txtRemark='';
        $scope.temp.ddlItemMasterStorage='';
        $scope.editMode = false;
    }


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'itemid': id.ITEMID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getItemmaster.indexOf(id);
		            $scope.post.getItemmaster.splice(index, 1);
		            console.log(data.data.message)
                    
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



    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }


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