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
  $scope.Page = "MEPITMANAGEMENT";
    $scope.PageSub1 = "MEPITREPORT";
    $scope.PageSub2 = "MEPDEVELOPMENTREPORT";
  
  

  var url = "code/MEP_IT_Objects_Under_Development_Report_code.php";

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
/*
  /* ========== Save Paymode =========== 
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
        formData.append("txTechPlatformId", $scope.temp.txTechPlatformId);
        formData.append("txtModuleId", $scope.temp.txtModuleId);
        formData.append("txtObjMasterDesc", $scope.temp.txtObjMasterDesc);
        formData.append("txtObjTypeId", $scope.temp.txtObjTypeId);
        formData.append("txtfunctionId", $scope.temp.txtfunctionId);
        formData.append("txtfolderId", $scope.temp.txtfolderId);

        formData.append("txtUser1", $scope.temp.txtUser1);
        formData.append("txtdevelopmentDate", $scope.temp.txtdevelopmentDate.toLocaleString('sv-SE'));
        formData.append("txtDevServerLocId", $scope.temp.txtDevServerLocId);

        formData.append("txtUser2", $scope.temp.txtUser2);
        formData.append("txtTestingDate", $scope.temp.txtTestingDate.toLocaleString('sv-SE'));

        formData.append("txtUser3", $scope.temp.txtUser3);
        formData.append("txtDeploymentDate", $scope.temp.txtDeploymentDate.toLocaleString('sv-SE'));
        formData.append("txtMainServerLocationId", $scope.temp.txtMainServerLocationId);

        formData.append("txtStatusCode", $scope.temp.txtStatusCode);
        formData.append("txtremarks", $scope.temp.txtremarks);
        
        formData.append("txtObjMasterId", $scope.temp.txtObjMasterId);
        
        
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
        document.getElementById("txTechPlatformId").focus();
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
*/
 
  //Get Module Name
  $scope.getModule = function () {
    $scope.post.getModule = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getModule",
      }),
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

 
  //Get Status
  $scope.getStatus = function () {
    $scope.post.getStatus = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getStatus",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getStatus = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getStatus();

 
  /* ========== GET USER  =========== */
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

  // /* ========== GET Paymode =========== */
  // $scope.getObjMaster = function () {
  //   $http({
  //     method: "post",
  //     url: url,
  //     data: $.param({ type: "getObjMaster" }),
  //     headers: { "Content-Type": "application/x-www-form-urlencoded" },
  //   }).then(
  //     function (data, status, headers, config) {
  //       //console.log(data.data);
  //       $scope.post.getObjMaster = data.data.success ? data.data.data : [];
  //     },
  //     function (data, status, headers, config) {
  //       console.log("Failed");
  //     }
  //   );
  // };
  // $scope.getObjMaster();

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
        // console.log(data.data);
        $scope.post.getObjectMaster = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getObjectMaster();




  $scope.getFolder = function () {
    $scope.post.getFolder = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getFolder",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //   console.log(data.data);
        $scope.post.getFolder = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getFolder();

  // Get Functions
  $scope.getFunction = function () {
    $scope.post.getFunction = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getFunction",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //   console.log(data.data);
        $scope.post.getFunction = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getFunction();


//Get Main Server Location
  $scope.getMainServerLocation = function () {
    $scope.post.getMainServerLocation = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getMainServerLocation",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getMainServerLocation = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getMainServerLocation();


  //Get Back Up Location
  $scope.getBackupLocation = function () {
    $scope.post.getBackupLocation = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getBackupLocation",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getBackupLocation = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getBackupLocation();


  /* ============ Edit Button ============= 
  $scope.edit = function (id) {
    // console.log(id);
    document.getElementById("txTechPlatformId").focus();

    $scope.temp = {
        pmid: id.OBJMASTER_NEW_ID,
        txtModuleId :id.MODULE_ID.toString(),
        txTechPlatformId :id.TECHPLATFORMID.toString(),
        txtObjMasterDesc :id.OBJMASTER_DESC,
        txtObjTypeId :id.OBJTYPEID.toString(),
        // txtfunctionId :id.FUNCTIONID.toString(),
        // txtfolderId :id.FOLDERID.toString(),
        txtUser1 :id.DEVELOPER_USER_ID.toString(),
        txtdevelopmentDate :id.DEVELOPMENT_DATE ? new Date(id.DEVELOPMENT_DATE) : '',
        txtDevServerLocId :id.DEVELOPMENT_SERVER_LOCATION_ID.toString(),
        txtUser2 :id.TESTING_USER_ID.toString(),
        txtTestingDate :id.TESTING_DATE ? new Date(id.TESTING_DATE) : '',
        txtUser3 :id.DEPLOYMENT_USER_ID.toString(),
        txtDeploymentDate  :id.DEPLOYMENT_DATE ? new Date(id.DEPLOYMENT_DATE) : '',
        txtMainServerLocationId :id.MAIN_SERVER_LOCATION_ID.toString(),
        txtStatusCode :id.STATUS_CODE.toString(),
        txtremarks: id.REMARKS,
        txtObjMasterId : id.OBJMASTER_ID.toString(),
        
    };

 
    $scope.editMode = true;
    $scope.getUserByLoc();
    $scope.index = $scope.post.getQuery.indexOf(id);
  };
  */
  /* ============ Clear Form =========== 
  $scope.clear = function () {
    document.getElementById("txTechPlatformId").focus();
    $scope.temp = {};
    $scope.editMode = false;
  };

  */
  /* ========== DELETE =========== 
  $scope.delete = function (id) {
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
      $http({
        method: "post",
        url: url,
        data: $.param({ pmid: id.OBJMASTER_NEW_ID, type: "delete" }),
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
  */
  /* ========== Deploy =========== */
  $scope.deploy = function (id) {
    var r = confirm("Are you sure want to deploy this record!");
    if (r == true) {
      
      $http({
        method: "post",
        url: url,
        data: $.param({ pmid: id.OBJMASTER_NEW_ID, type: "deploy",txtObjMasterId:id.OBJMASTER_ID }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        console.log(data.data);
        if (data.data.success) {
          var index = $scope.post.getQuery.indexOf(id);
          $scope.post.getQuery.splice(index, 1);
          console.log(data.data.message)

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