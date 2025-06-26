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
  $scope.PageSub = "MEPITMANAGEMENT";
  $scope.PageSub1 = "MEPITMASTER";
  $scope.PageSub2 = "MEPOBJECTTYPES";

  var url = "code/MEP_Object_Master_Types_code.php";

  //For sorting
  $scope.setMyOrderBY = function (COL) {
    $scope.myOrderBY =
      COL == $scope.myOrderBY
        ? `-${COL}`
        : $scope.myOrderBY == `-${COL}`
        ? (myOrderBY = COL)
        : (myOrderBY = `-${COL}`);
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
            $scope.getObjectType();
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
    $(".btn-save").text("Saving...");
    $(".btn-update").attr("disabled", "disabled");
    $(".btn-update").text("Updating...");

    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("objid", $scope.temp.objid);
        formData.append("txtObjType", $scope.temp.txtObjType);
        formData.append("txTechPlatformId", $scope.temp.txTechPlatformId);
        formData.append("txtremarks", $scope.temp.txtremarks);
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);

        $scope.getObjectType();
        $scope.clear();
        document.getElementById("txTechPlatformId").focus();
      } else {
        $scope.messageFailure(data.data.message);
        console.log(data.data);
      }
      $(".btn-save").removeAttr("disabled");
      $(".btn-save").text("SAVE");
      $(".btn-update").removeAttr("disabled");
      $(".btn-update").text("UPDATE");
    });
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

  /* ========== GET Paymode =========== */
  $scope.getObjectType = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getObjectType" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getObjectType = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };

  /* ============ Edit Button ============= */
  $scope.edit = function (id) {
    console.log(id);
    document.getElementById("txtObjType").focus();

    $scope.temp = {
      objid: id.OBJTYPEID,
      txtObjType: id.OBJECT_TYPE_DESC,
      txTechPlatformId: id.TECHPLATFORMID,
      txtremarks: id.REMARKS,
    };

    $scope.editMode = true;
    $scope.index = $scope.post.getObjectType.indexOf(id);
  };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("txtObjType").focus();
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
        data: $.param({ objid: id.OBJTYPEID, type: "delete" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        console.log(data.data);
        if (data.data.success) {
          var index = $scope.post.getObjectType.indexOf(id);
          $scope.post.getObjectType.splice(index, 1);
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