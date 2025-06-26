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
    $scope.PageSub2 = "MEPFRANCHISELAUNCH";
   
    
    var url = 'code/MEP_Franchise_launch_Tasks_code.php';




    
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
                $scope.getfranchisedataData();
                $scope.filterfranchiseList();
                // $scope.getBankAccountsDetails();
                // $scope.getBankID();
                // $scope.getLocations();
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
            formData.append("TASK_ID", $scope.temp.TASK_ID);
            formData.append("ddlLocation", $scope.temp.ddlLocation);
            formData.append("ddlfranchiseID", $scope.temp.ddlfranchiseID);
             formData.append("txtTask", $scope.temp.txtTask);
            formData.append("ddlTaskctgy", $scope.temp.ddlTaskctgy);
            formData.append("txtTaskname", $scope.temp.txtTaskname);
            formData.append("ddluser", $scope.temp.ddluser);
            formData.append("Sdate", (!$scope.temp.Sdate || $scope.temp.Sdate=='') ? '' : $scope.temp.Sdate.toLocaleDateString('sv-SE'));
            formData.append("Edate", (!$scope.temp.Edate || $scope.temp.Edate=='') ? '' : $scope.temp.Edate.toLocaleDateString('sv-SE'));
            formData.append("ddlPriority", $scope.temp.ddlPriority);
            formData.append("ddltaststatus", $scope.temp.ddltaststatus);
            formData.append("QUESTIONS_FRANCHISE", $scope.temp.QUESTIONS_FRANCHISE);
            formData.append("QUESTIONS_HQ", $scope.temp.QUESTIONS_HQ);
            formData.append("ddlMastertask", $scope.temp.ddlMastertask);
            formData.append("txtRemark", $scope.temp.txtRemark);
            
            return formData;
        },
        data: $scope.temp,
        headers: { 'Content-Type': undefined }
    }).
    then(function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
            $scope.messageSuccess(data.data.message);
            $scope.getfranchisedataData();
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




$scope.getfranchisedataData = function () {
    $(".SpinMain").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getfranchisedataData"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
            $scope.post.getfranchisedataData = data.data.success ? data.data.data : [];
            $scope.filterfranchiseList();
        $(".SpinMain").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
};
$scope.getfranchisedataData();

 
 $scope.filterfranchiseList = function () { 
        if (!$scope.temp.ddlfranchiseID|| $scope.temp.ddlfranchiseID == '') {
            $scope.post.filteredData = angular.copy($scope.post.getfranchisedataData);
        }
        else { 
            
            if ($scope.post.getfranchisedataData && $scope.temp.ddlfranchiseID != null)
                {
                 $scope.post.filteredData = $scope.post.getfranchisedataData.filter(x => x.FRANCHISE_ID == $scope.temp.ddlfranchiseID);
                }
            else
            {
                console.error('getfranchisedataData or ddlfranchiseID is not defined');
            }
        }
     
       }   
    
    
    
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
    // console.log(data.data);
    $scope.post.getModule = data.data.success ? data.data.data : [];
    $(".SpinBank").hide();
  },
  function (data, status, headers, config) {
    console.log("Failed");
  }
);
};
$scope.getModule();

/* ========== GET FRANCHISE NAME =========== */
$scope.getFranchisename = function () {
$http({
    method: 'post',
    url: url,
    data: $.param({ 'type': 'getFranchisename'}),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
}).
then(function (data, status, headers, config) {
//    console.log(data.data.data);
   $scope.post.getFranchisename = data.data.success ? data.data.data : [];
},
function (data, status, headers, config) {
   console.log('Failed');
})
}
$scope.getFranchisename();


/* ========== GET priority =========== */
$scope.getpriority = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getpriority'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
    //    console.log(data.data.data);
       $scope.post.getpriority = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getpriority();
    

/* ========== GET Task Category =========== */
$scope.getTaskCategoryData = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getTaskCategoryData'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
    //    console.log(data.data.data);
       $scope.post.getTaskCategoryData = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
}
$scope.getTaskCategoryData();
    
    
/* ========== GET Who user =========== */
$scope.getuserData = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getuserData'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
    //    console.log(data.data.data);
       $scope.post.getuserData = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getuserData();

    /* ========== GET Task Status =========== */
    $scope.getTaskstatusData = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getTaskstatusData'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data.data);
           $scope.post.getTaskstatusData = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getTaskstatusData();


        /* ========== GET Master Task =========== */
    $scope.getMastertask = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getMastertask'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
        //    console.log(data.data.data);
           $scope.post.getMastertask = data.data.success ? data.data.data : [];
        },
        function (data, status, headers, config) {
           console.log('Failed');
        })
        }
        $scope.getMastertask();


 /* ========== GET Location =========== */
 $scope.getLocations = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getLocations'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
    //    console.log(data.data.data);
       $scope.post.getLocations = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
       console.log('Failed');
    })
    }
    $scope.getLocations();

// $scope.getLocations(); --INIT
/* ========== GET Location =========== */

/* ============ Edit Button ============= */ 
$scope.edit = function (id) {
    // console.log(id)
    $scope.temp = {
        TASK_ID:id.TASK_ID,
        ddlLocation:id.LOC_ID.toString(),
        ddlfranchiseID:id.FRANCHISE_ID.toString(),
        txtTask:id.TASK_DESC,
        ddlTaskctgy:id.TASK_CATG_ID.toString(),
        txtTaskname:id.TASK_NAME,
        ddluser:id.WHO_ID.toString(),
        Sdate:new Date(id.START_DATE),
        Edate:new Date(id.END_DATE),
        ddlPriority:id.PRIORTY_ID.toString(),
        ddltaststatus:id.TASK_STATUS_ID.toString(),
        QUESTIONS_FRANCHISE:id.QUESTIONS_FRANCHISE,
        QUESTIONS_HQ:id.QUESTIONS_HQ,
        ddlMastertask:!id.MASTER_TASK_CD ? '' : id.MASTER_TASK_CD.toString(),
        txtRemark:id.REMARKS
    };

    $scope.editMode = true;
    $scope.index = $scope.post.getfranchisedataData.indexOf(id);
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
            data: $.param({ 'TASK_ID': id.TASK_ID, 'type': 'delete' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            //  console.log(data.data)
            if (data.data.success) {
                var index = $scope.post.getfranchisedataData.indexOf(id);
                $scope.post.getfranchisedataData.splice(index, 1);
                // console.log(data.data.message)
                
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