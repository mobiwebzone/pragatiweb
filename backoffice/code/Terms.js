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
  $scope.Page = "SETTING";
  $scope.PageSub = "TERMS";

  var url = "code/Terms_code.php";

  /* =============== DATE CONVERT ============== */
  $scope.dateFormat = function (datetime) {
    return (
      datetime.getFullYear() +
      "-" +
      ("0" + (datetime.getMonth() + 1)).slice(-2) +
      "-" +
      ("0" + datetime.getDate()).slice(-2)
    );
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
          $scope.LOCID = data.data.locid;

          if (
            $scope.userrole != "ADMINISTRATOR" &&
            $scope.userrole != "SUPERADMIN"
          ) {
            window.location.assign("dashboard.html#!/dashboard");
          } else {
            $scope.getTerm();
            $scope.getLocations();
          }
        } else {
          // window.location.assign('index.html#!/login')
          $scope.logout();
        }
      },
      function (data, status, headers, config) {
        //console.log(data)
        console.log("Failed");
      }
    );
  };

  $scope.saveTerm = function () {
    $(".btn-save").attr("disabled", "disabled");
    $(".btn-save").text("Saving...");
    $(".btn-update").attr("disabled", "disabled");
    $(".btn-update").text("Updating...");
    // alert($scope.temp.ddlCollege);

    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "saveTerm");
        formData.append("termid", $scope.temp.termid);
        formData.append("txtTerm", $scope.temp.txtTerm);
        formData.append("ddlLocation", $scope.temp.ddlLocation);
          
        // formData.append("txtFromDate", $scope.dateFormat($scope.temp.txtFromDate));
        // formData.append("txtToDate", $scope.dateFormat($scope.temp.txtToDate));
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      // console.log(data.data);
      if (data.data.success) {
        if ($scope.editMode) {
          $scope.messageSuccess(data.data.message);
        } else {
          $scope.messageSuccess(data.data.message);
        }
        $scope.getTerm();
        $scope.clearForm();

        document.getElementById("txtTerm").focus();
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

  /* ========== GET Terms =========== */
  $scope.getTerm = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getTerm","LOCID":$scope.temp.ddlLocation}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getTerms = data.data.success ? data.data.data : [];
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getTerm(); --INIT

  /* ========== GET Location =========== */
  $scope.getLocations = function () {
    $http({
      method: "post",
      url: "code/Users_code.php",
      data: $.param({ type: "getLocations" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getLocations = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getLocations(); --INIT

  /* ============ Edit Button ============= */
  $scope.editTerm = function (id) {
    document.getElementById("ddlLocation").focus();
    //document.getElementById("txtTerm").focus();
    $scope.temp = {
      termid: id.TERMID,
      txtTerm: id.TERM,
      ddlLocation: id.LOC_ID>0?id.LOC_ID.toString():'',
      // txtFromDate: new Date(id.FROMDATE),
      // txtToDate: new Date(id.TODATE),
      };
      

    $scope.editMode = true;
    $scope.index = $scope.post.getTerms.indexOf(id);
  };

  /* ============ Clear Form =========== */
  $scope.clearForm = function () {
    document.getElementById("ddlLocation").focus();
    //document.getElementById("txtTerm").focus();
      $scope.temp = {};
      $scope.temp.ddlLocation = $scope.LOCID.toString();
    $scope.editMode = false;
  };

  /* ========== DELETE =========== */
  $scope.deleteTerm = function (id) {
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
      $http({
        method: "post",
        url: url,
        data: $.param({ termid: id.TERMID, type: "deleteTerm" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        // console.log(data.data)
        if (data.data.success) {
          var index = $scope.post.getTerms.indexOf(id);
          $scope.post.getTerms.splice(index, 1);
          console.log(data.data.message);
          $scope.clearForm();

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