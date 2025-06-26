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
  $scope.PageSub = "MEPITMASTER";
  $scope.PageSub1 = "MEPITDEPLOYMENT";

  var url = "code/MEP_IT_Deployment_code.php";

  $scope.setMyOrderBY = function (COL) {
    $scope.myOrderBY = COL == $scope.myOrderBY ? `-${COL}` : $scope.myOrderBY == `-${COL}` ? (myOrderBY = COL) : (myOrderBY = `-${COL}`);
    console.log($scope.myOrderBY);
};

  // GET DATA
  $scope.init = function () {
    // Check Session
    $http({
      method: "post",
      url: "code/checkSession.php",
      data: $.param({ type: "checkSession" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);

        if (data.data.success) {
          $scope.post.user = data.data.data;
          $scope.userid = data.data.userid;
          $scope.userFName = data.data.userFName;
          $scope.userLName = data.data.userLName;
          $scope.userrole = data.data.userrole;
          $scope.USER_LOCATION = data.data.LOCATION;

          if (
            $scope.userrole != "ADMINISTRATOR" &&
            $scope.userrole != "SUPERADMIN"
          ) {
            // alert($scope.userrole);
            window.location.assign("dashboard.html#!/dashboard");
          } else {
            $scope.getQuery();
          }
        } else {
          // window.location.assign('index.html#!/login')
          // alert
          $scope.logout();
        }
      },
      function (data, status, headers, config) {
        //console.log(data)
        console.log("Failed");
      }
    );
  };

  /* ========== Save Paymode =========== */
  $scope.save = function () {
    $(".btn-save").attr("disabled", "disabled");
    // $(".btn-save").text('Saving...');
    $(".btn-update").attr("disabled", "disabled");
    // $(".btn-update").text('Updating...');

    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("txtTechPlatformId", $scope.temp.txtTechPlatformId);

        formData.append("txtObjTypeId", $scope.temp.txtObjTypeId);
        formData.append("txtObjMasterId", $scope.temp.txtObjMasterId);
        formData.append("txtModificationDesc", $scope.temp.txtModificationDesc);

        formData.append("txtUser1", $scope.temp.txtUser1);
        // formData.append("txtUser2", $scope.temp.txtUser2);
        // formData.append("txtUser3", $scope.temp.txtUser3);

        formData.append("txtModificationDate", $scope.dateFormat($scope.temp.txtModificationDate));
        // formData.append("txtTestingDate",$scope.dateFormat($scope.temp.txtTestingDate));
        formData.append("txtDeploymentDate",$scope.dateFormat($scope.temp.txtDeploymentDate));
        formData.append("txtDeployedcd", $scope.temp.txtDeployedcd);
        formData.append("source_database_cd", $scope.temp.source_database_cd);
        formData.append("txtremarks", $scope.temp.txtremarks);
        
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      console.log(data.data);
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);

        $scope.getQuery();
        $scope.clear();
        document.getElementById("txtTechPlatformId").focus();
        // console.log(pmid);
      } else {
        $scope.messageFailure(data.data.message);
        // console.log(data.data)
      }
      $(".btn-save").removeAttr("disabled");
      $(".btn-save").text("SAVE");
      $(".btn-update").removeAttr("disabled");
      $(".btn-update").text("UPDATE");
    });
  };


  $scope.dateFormat = function (date) { 
    return (!date || date == '') ? '' : date.toLocaleDateString('sv-SE');
  }
  /* ========== GET USER BY LOCATION =========== */
  $scope.getUserByLoc = function () {
    $(".spinUser").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getUserByLoc",
        ddlLocation: $scope.temp.ddlLocation,
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          $scope.post.getUserByLoc = data.data.data;
        } else {
          $scope.post.getUserByLoc = [];
        }
        $(".spinUser").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };

  $scope.getUserByLoc();

  /* ========== GET Paymode =========== */
  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getQuery" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getQuery = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };

  //Get Object Type Master
  $scope.getObjectType = function () {
    $scope.post.getObjectType = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getObjectType",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getObjectType = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getObjectType();

  
  //Get Object Master
  $scope.getObjectMaster = function (id) {
    $scope.post.getObjectMaster = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        txtObjTypeId : $scope.temp.txtObjTypeId,
        type: "getObjectMaster",
        
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getObjectMaster = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getObjectMaster();

  //Get Tech Platform objects
  $scope.getTechPlatform = function () {
    $scope.post.getTechPlatform = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getTechPlatform",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getTechPlatform = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getTechPlatform();


//Get Deployment Status
 
$scope.getDeployedcd = function () {
$http({
    method: 'post',
    url: url,
    data: $.param({ 'type': 'getDeployedcd'}),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
}).
then(function (data, status, headers, config) {
   console.log(data.data.data);
   $scope.post.getDeployedcd = data.data.success ? data.data.data : [];
},
function (data, status, headers, config) {
   console.log('Failed');
})
}
$scope.getDeployedcd();


  //Get Source Database
 
$scope.getSourceDatabase = function () {
$http({
    method: 'post',
    url: url,
    data: $.param({ 'type': 'getSourceDatabase'}),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
}).
then(function (data, status, headers, config) {
   console.log(data.data.data);
   $scope.post.getSourceDatabase = data.data.success ? data.data.data : [];
},
function (data, status, headers, config) {
   console.log('Failed');
})
}
$scope.getSourceDatabase();

  
  
  /* ============ Edit Button ============= */
  $scope.edit = function (id) {
    
    document.getElementById("txtTechPlatformId").focus();

    $scope.temp = {
      pmid: id.DEPLOYMENTID,
      txtTechPlatformId: id.TECHPLATFORMID.toString(),
      txtObjTypeId: id.OBJTYPEID.toString(),
      // txtObjMasterId: id.OBJMASTER_ID.toString(),
      txtModificationDesc: id.MODIFICATION_DESC,
      txtUser1: id.MODIFICATION_USER_ID.toString(),
      txtModificationDate: id.MODIFICATION_DATE ? new Date(id.MODIFICATION_DATE) : '',
      // Need deployment date
      txtDeploymentDate: id.DEPLOYMENT_DATE ? new Date(id.DEPLOYMENT_DATE) : '',
      txtDeployedcd: id.DEPLOYED_CD.toString(),
      source_database_cd: id.SOURCE_DATABASE_CD.toString(),
      txtremarks: id.REMARKS,
    };

    $scope.getObjectMaster();
    $timeout(()=>{
      $scope.temp.txtObjMasterId=id.OBJMASTER_ID.toString();
    },500);

    $scope.editMode = true;
    $scope.getUserByLoc();
    $scope.index = $scope.post.getQuery.indexOf(id);
  };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("txtTechPlatformId").focus();
    $scope.temp = {};
    $scope.editMode = false;
  };

  /* ========== DELETE =========== */
  $scope.delete = function (id) {
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
      $http({
        method: "post",
        url: url,
        data: $.param({ pmid: id.DEPLOYMENTID, type: "delete" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        console.log(data.data);
        if (data.data.success) {
          var index = $scope.post.getQuery.indexOf(id);
          $scope.post.getQuery.splice(index, 1);
          // console.log(data.data.message)

          $scope.messageSuccess(data.data.message);
        } else {
          $scope.messageFailure(data.data.message);
        }
      });
    }
  };

  /* ========== Logout =========== */
  $scope.logout = function () {
    $http({
      method: "post",
      url: "code/logout.php",
      data: $.param({ type: "logout" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          window.location.assign("index.html#!/login");
        } else {
          //window.location.assign('backoffice/index#!/')
        }
      },
      function (data, status, headers, config) {
        console.log("Not login Failed");
      }
    );
  };

  $scope.messageSuccess = function (msg) {
    jQuery(".alert-success > span").html(msg);
    jQuery(".alert-success").show();
    jQuery(".alert-success")
      .delay(5000)
      .slideUp(function () {
        jQuery(".alert-success > span").html("");
      });
  };

  $scope.messageFailure = function (msg) {
    jQuery(".alert-danger > span").html(msg);
    jQuery(".alert-danger").show();
    jQuery(".alert-danger")
      .delay(5000)
      .slideUp(function () {
        jQuery(".alert-danger > span").html("");
      });
  };
});