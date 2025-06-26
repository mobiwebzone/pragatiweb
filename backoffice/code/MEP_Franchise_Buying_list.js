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
    $scope.Page = "L&A";
    $scope.PageSub = "FRANCHISEMANAGEMENT";
    $scope.PageSub2 = "MEPFRANCHISEBUYINGLIST";
   
    
    var url = 'code/MEP_Franchise_Buying_list_code.php';




    
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
                    // $scope.getBankAccountsDetails();
                    // $scope.getBankID();
                    $scope.getfLocations();
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
                formData.append("BUYING_LIST_ID", $scope.temp.BUYING_LIST_ID);
                formData.append("ddlfLocation", $scope.temp.ddlfLocation);
                formData.append("ddlItemctgy", $scope.temp.ddlItemctgy);
                 formData.append("txtItemname", $scope.temp.txtItemname);
                formData.append("txtItemqnty", $scope.temp.txtItemqnty);
                formData.append("txtItemPrice", $scope.temp.txtItemPrice);
                formData.append("ddlCurrency", $scope.temp.ddlCurrency);
                formData.append("txtitemmodel", $scope.temp.txtitemmodel);
                formData.append("ddlItemvendor", $scope.temp.ddlItemvendor);
                formData.append("txtPlink1", $scope.temp.txtPlink1);
                formData.append("txtPlink2", $scope.temp.txtPlink2);
                formData.append("ddlMastertask", $scope.temp.ddlMastertask);
                formData.append("txtRemark", $scope.temp.txtRemark);
                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getItemData();
                $scope.clear();
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
    
    
    
    
    $scope.getItemData = function () {
        $(".SpinMain").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getItemData"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getItemData = data.data.success ? data.data.data : [];
            $(".SpinMain").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
    };
    $scope.getItemData();
    
     
    //Get Module Name
    $scope.getModule = function () {
    $scope.post.getModule = [];
    
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getModule"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getModule = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
    };
    $scope.getModule();
    
    /* ========== GET ITEM CATEGORY  =========== */
    $scope.getitemcategory = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getitemcategory'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
       console.log(data.data.data);
       $scope.post.getitemcategory = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getitemcategory();
    
    
    /* ========== GET CURRENCY =========== */
    $scope.getCurrency = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getCurrency'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getCurrency = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getCurrency();
        
    
    /* ========== GET ITEM VENDOR =========== */
    $scope.getItemvendor = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getItemvendor'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getItemvendor = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
    }
    $scope.getItemvendor();
     
    
    
        /* ========== GET Master Task =========== */
        $scope.getMastertask = function () {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'type': 'getMastertask'}),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
               console.log(data.data.data);
               $scope.post.getMastertask = data.data.success ? data.data.data : [];
            },
            function (data, status, headers, config) {
               console.log('Failed');
            })
            }
            $scope.getMastertask();
    
        
   
     /* ========== GET Location =========== */
     $scope.getfLocations = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getfLocations'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
           console.log(data.data.data);
           $scope.post.getfLocations = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getfLocations();
    
    // $scope.getfLocations(); --INIT
    /* ========== GET Location =========== */
    
    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        console.log(id)
        $scope.temp = {
            BUYING_LIST_ID:id.BUYING_LIST_ID,
            ddlfLocation:id.FRANCHISE_LOCATION_ID.toString(),
            ddlItemctgy:id.ITEM_CATG_ID.toString(),
            txtItemname:id.ITEM,
            txtItemqnty:id.ITEM_QUANTITY,
            txtItemPrice:id.ITEM_UNIT_PRICE,
            ddlCurrency:id.PRICE_CURRENCY_ID.toString(),
            txtitemmodel:id.ITEM_MODEL,
            ddlItemvendor:id.ITEM_VENDOR_ID.toString(),
            txtPlink1:id.PURCHASE_LINK1,
            txtPlink2:id.PURCHASE_LINK2,
            ddlMastertask:!id.MASTER_TASK_CD ? '' : id.MASTER_TASK_CD.toString(),
            txtRemark:id.REMARKS
        };
    
        $scope.editMode = true;
        $scope.index = $scope.post.getItemData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp = {};
        $scope.editMode = false;
    }
    
    
    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'BUYING_LIST_ID': id.BUYING_LIST_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
            then(function (data, status, headers, config) {
                 console.log(data.data)
                if (data.data.success) {
                    var index = $scope.post.getItemData.indexOf(id);
                    $scope.post.getItemData.splice(index, 1);
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

});