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
  $scope.PageSub2 = "MEPCODEMASTER"

  var url = "code/SCH_School_User_Mapping_code.php";

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
       
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_LOC_ID", $scope.temp.TEXT_LOC_ID);
        formData.append("TEXT_USER_ID", $scope.temp.TEXT_USER_ID);
        formData.append("txtremarks", $scope.temp.txtremarks);
       
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);

        $scope.getQuery();
        $scope.clear();
        document.getElementById("TEXT_SCHOOL_ID").focus();
        console.log(data.data)
      } else {
        $scope.messageFailure(data.data.message);
         console.log(data.data)
      }
      $(".btn-save").removeAttr("disabled");
      $(".btn-save").text("SAVE");
      $(".btn-update").removeAttr("disabled");
      $(".btn-update").text("UPDATE");
    });
  };

 
  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getQuery"
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getQuery = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };

  $scope.getSchool = function () {
    $scope.post.getSchool = [];
  
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getSchool"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getSchool = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
$scope.getSchool();

$scope.getLocations = function (id) {
  $scope.post.getLocations = [];

  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({
       TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,     
      type: "getLocations",
      
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (data, status, headers, config) {
      console.log(data.data);
      $scope.post.getLocations = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    },
    function (data, status, headers, config) {
      console.log("Failed");
    }
  );
};

$scope.getLocations();


 
  $scope.getOrgUser = function () {
    $scope.post.getOrgUser = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
         TEXT_LOC_ID: $scope.temp.TEXT_LOC_ID,    
        type: "getOrgUser",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getOrgUser = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
};
$scope.getOrgUser();   


  // /* ============ Edit Button ============= */
  $scope.edit = function (id) {
    // console.log(id);
   document.getElementById("TEXT_SCHOOL_ID").focus();

    $scope.temp = {
      pmid       : id.SCHOOL_USER_ID,
      TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
      TEXT_LOC_ID: id.LOC_ID.toString(),
      TEXT_USER_ID: id.USER_ID.toString(),
      txtremarks: id.REMARKS,
      
    };

    $scope.editMode = true;
    $scope.index = $scope.post.getQuery.indexOf(id);
  };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_ID").focus();
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
        data: $.param({ pmid: id.SCHOOL_USER_ID, type: "delete" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        // console.log(data.data);
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