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
  $scope.PageSub2 = "MEPOBJECTMAPPING";

  var url = "code/MEP_IT_Object_Mapping_code.php";

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
        formData.append("txtObjTypeIdSource", $scope.temp.txtObjTypeIdSource);
        formData.append("txtObjMasterIdSource", $scope.temp.txtObjMasterIdSource);
        formData.append("txtObjTypeIdTarget", $scope.temp.txtObjTypeIdTarget);
        formData.append("txtObjMasterIdTarget", $scope.temp.txtObjMasterIdTarget);
        formData.append("txtremarks", $scope.temp.txtremarks);
        formData.append("txtDevelopmentStatus", $scope.temp.txtDevelopmentStatus);

        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      // console.log(data.data);
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
  
  /* ========== GET Query =========== */
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

  //Get Object Type Master- Source
  $scope.getObjectTypeSource = function () {
    $scope.post.getObjectTypeSource = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getObjectTypeSource",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getObjectTypeSource = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) { 
        console.log("Failed");
      }
    );
  };
  $scope.getObjectTypeSource();


    //Get Object Type Master- Target
  $scope.getObjectTypeTarget = function () {
    $scope.post.getObjectTypeTarget = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getObjectTypeTarget",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getObjectTypeTarget = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) { 
        console.log("Failed");
      }
    );
  };
  $scope.getObjectTypeTarget();



  //Get Object Master - Source
  $scope.getObjectMasterSource = function () {
    $scope.post.getObjectMasterSource = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getObjectMasterSource",
        
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getObjectMasterSource = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getObjectMasterSource();


  //Get Object Master - Target
  $scope.getObjectMasterTarget = function () {
    $scope.post.getObjectMasterTarget = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getObjectMasterTarget",
        
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getObjectMasterTarget = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getObjectMasterTarget();



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

//Get Development Status
  $scope.getDevelopmentStatus = function () {
    $scope.post.getDevelopmentStatus = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getDevelopmentStatus",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getDevelopmentStatus = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getDevelopmentStatus();



  /* ============ Edit Button ============= */
  $scope.edit = function (id) {
    // console.log(id);
    document.getElementById("txtTechPlatformId").focus();

    $scope.temp = {
      pmid: id.OBJMAPPINGID,
      txtTechPlatformId: id.TECHPLATFORMID.toString(),
      txtObjTypeIdSource: id.OBJTYPEID_SOURCE.toString(),
      txtObjMasterIdSource: id.OBJMASTER_ID_SOURCE.toString(),
      txtObjTypeIdTarget: id.OBJTYPEID_TARGET.toString(),
      txtObjMasterIdTarget: id.OBJMASTER_ID_TARGET.toString(),
      txtremarks: id.REMARKS,
      txtDevelopmentStatus: id.DEVELOPMENT_STATUS_CD.toString(),
    };

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
        data: $.param({ pmid: id.OBJMAPPINGID, type: "delete" }),
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