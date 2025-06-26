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
  $scope.PageSub2 = "MEPOBJECTMASTER";

  var url = "code/MEP_OBJECT_Master_code.php";

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
     $(".btn-save").text('Saving...');
    $(".btn-update").attr("disabled", "disabled");
     $(".btn-update").text('Updating...');

    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("txtOBJMASTER_DESC", $scope.temp.txtOBJMASTER_DESC);
        formData.append("txtddlObjTypeId", $scope.temp.ddlObjTypeId);
        formData.append("txtddlfolderid", $scope.temp.ddlfolderid);
        formData.append("txtddlfunctionid", $scope.temp.ddlfunctionid);
        formData.append("txTechPlatformId", $scope.temp.txTechPlatformId);
        formData.append("chkEmail", $scope.temp.chkEmail);
        formData.append("chkStudentParent", $scope.temp.chkStudentParent);
        formData.append("archiveflag", $scope.temp.archiveflag);
        formData.append("txtremarks", $scope.temp.txtremarks);
        formData.append("txtDevelopmentStatus", $scope.temp.txtDevelopmentStatus);
        
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

 
 
  /* ========== GET Paymode =========== */
  $scope.getObjMaster = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getObjMaster" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getObjMaster = data.data.success ? data.data.data : [];
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getObjMaster();

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

  /* ============ Edit Button ============= */
  $scope.edit = function (id) {
   
    document.getElementById("txTechPlatformId").focus();
    
    $scope.temp = {
      pmid: id.OBJMASTER_ID,
      txTechPlatformId: id.TECHPLATFORMID.toString(),
      txtOBJMASTER_DESC: id.OBJMASTER_DESC,
      ddlObjTypeId: id.OBJTYPEID.toString(),
      ddlfunctionid: id.FUNCTIONID.toString(),
      ddlfolderid: id.FOLDERID.toString(),
      chkEmail: (!id.E_MAIL_FLAG || id.E_MAIL_FLAG==0) ? '0' : '1',
      chkStudentParent: (!id.STUDENT_TEACHER_FLAG || id.STUDENT_TEACHER_FLAG == 0) ? '0' : '1',
      archiveflag: (!id.ARCHIVE_FLAG || id.ARCHIVE_FLAG == 0) ? '0' : '1',
      txtremarks: !id.REMARKS ? '' : id.REMARKS,
      txtDevelopmentStatus: id.DEVELOPMENT_STATUS_CD.toString(),
    };

    $scope.editMode = true;
    $scope.index = $scope.post.getQuery.indexOf(id);
  };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("txTechPlatformId").focus();
    $scope.temp = {};
    $scope.temp.chkEmail = '0';
    $scope.temp.chkStudentParent = '0';
    $scope.temp.archiveflag = '0';
    $scope.editMode = false;
  };

  /* ========== DELETE =========== */
  $scope.delete = function (id) {
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
      $http({
        method: "post",
        url: url,
        data: $.param({ pmid: id.OBJMASTER_ID, type: "delete" }),
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