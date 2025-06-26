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
  $scope.PageSub2 = "MEPCODEDETILS";

  var url = "code/MEP_Code_Details_code.php";

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
    console.log('begin save js');
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("txtObject", $scope.temp.txtObject);
        formData.append("txtCodeId", $scope.temp.txtCodeId);
        formData.append("txtremarks", $scope.temp.txtremarks);  
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      console.log('Inside ');
      console.log(data.data);

      if (data.data.success) {
        
        $scope.messageSuccess(data.data.message);

        $scope.getQuery();
        $scope.clear();
        document.getElementById("txtCodeId").focus();
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
      data: $.param({ ddlMCode: $scope.temp.ddlMCode,
        type: "getQuery"
      }),
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
  $scope.getQuery();
  
/* ========== GET INSTITUTION TYPE =========== */
$scope.getMastercode = function () {
  $scope.post.getMastercode = [];

  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({type: "getMastercode"}),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (data, status, headers, config) {
      console.log(data.data);
      $scope.post.getMastercode = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    },
    function (data, status, headers, config) {
      console.log("Failed");
    }
  );
};
$scope.getMastercode();
  

  $scope.getCodeId = function () {
    $scope.post.getCodeId = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getCodeId",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getCodeId = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getCodeId();

  /* ============ Edit Button ============= */
  $scope.edit = function (id) {
    // console.log(id);
   document.getElementById("txtCodeId").focus();

    $scope.temp = {
      pmid: id.CODE_DETAIL_ID.toString(),
      txtObject: id.CODE_DETAIL_DESC,
      txtCodeId: id.CODE_ID.toString(),
      txtremarks: id.REMARKS,
    };

    $scope.editMode = true;
    $scope.index = $scope.post.getQuery.indexOf(id);
  };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("txtCodeId").focus();
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
        data: $.param({ pmid: id.CODE_DETAIL_ID, type: "delete" }),
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