angular
  .module("myApp", ["angularUtils.directives.dirPagination", "ngSanitize"])
  .directive("bindHtmlCompile", [
    "$compile",
    function ($compile) {
      return {
        restrict: "A",
        link: function (scope, element, attrs) {
          scope.$watch(
            function () {
              return scope.$eval(attrs.bindHtmlCompile);
            },
            function (value) {
              element.html(value);
              $compile(element.contents())(scope);
            }
          );
        },
      };
    },
  ])
  .controller("myCtrl", function ($scope, $http) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "STUDENT";
    $scope.PageSub = "ATTENDANCE";
    $scope.PageSub1 = "SCHATTENDANCE";
    $scope.temp.TEXT_ATTENDANCE_DATE = null;

    var url = "code/SCH_Master_Field_Visibility_code.php";

    $scope.init = function () {
      $http({
        method: "post",
        url: "code/checkSession.php",
        data: $.param({ type: "checkSession" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (response) {
          console.log("Session response:", response.data);
          if (response.data.success) {
            $scope.post.user = response.data.data;
            $scope.userId = response.data.userId;
            $scope.userFName = response.data.userFName;
            $scope.userLName = response.data.userLName;
            $scope.userrole = response.data.userrole;
            $scope.USER_LOCATION = response.data.LOCATION;

            if (
              $scope.userrole !== "ADMINISTRATOR" &&
              $scope.userrole !== "SUPERADMIN"
            ) {
              window.location.assign("dashboard.html#!/dashboard");
            }
            $scope.getschoolname();
            $scope.getForm();
            $scope.getIsvisible();
            
          } else {
            console.error("Session check failed:", response.data);
            $scope.messageFailure("Session expired. Please log in.");
            $scope.logout();
          }
        },
        function (error) {
          console.error("Init failed:", error);
          $scope.messageFailure("Failed to initialize session.");
        }
      );
    };

    
$scope.save = function () {
  $(".btn-save").attr("disabled", "disabled");
  $(".btn-update").attr("disabled", "disabled");

  $http({
    method: "POST",
    url: url,
    processData: false,
    transformRequest: function (data) {
      var formData = new FormData();
      formData.append("type", "save");
      formData.append("pmid", $scope.temp.pmid);
      formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
      formData.append("TEXT_FORM_ID", $scope.temp.TEXT_FORM_ID);
      formData.append("TEXT_FIELD_NAME_DESC", $scope.temp.TEXT_FIELD_NAME_DESC);
      formData.append("TEXT_FIELD_NAME", $scope.temp.TEXT_FIELD_NAME);
      formData.append("TEXT_IS_VISIBLE", $scope.temp.TEXT_IS_VISIBLE);
      return formData;
    },
    data: $scope.temp,
    headers: { "Content-Type": undefined },
  }).then(function (response) {
    if (response.data.success) {
      $scope.messageSuccess(response.data.message);
      $scope.getQuery();
      $scope.clear();
      document.getElementById("TEXT_SCHOOL_ID").focus();
      console.log(response.data);
    } else {
      console.warn("Error occurred! Please check");
      console.warn(response.data);
      $scope.messageFailure(response.data.message);
    }

    $(".btn-save").removeAttr("disabled").text("SAVE");
    $(".btn-update").removeAttr("disabled").text("UPDATE");
  }, function (error) {
    console.error("Server error occurred", error);
    $scope.messageFailure("Server error occurred.");
    $(".btn-save").removeAttr("disabled").text("SAVE");
    $(".btn-update").removeAttr("disabled").text("UPDATE");
  });
};



    $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_FORM_ID: $scope.temp.TEXT_FORM_ID,
        type: "getQuery"
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        
        $scope.post.getQuery = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed during query");
      }
    );
  };

  


$scope.getForm = function () {
    $scope.post.getForm = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getForm",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getForm = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };

$scope.getForm();
  
  
  
    $scope.getschoolname = function () {
      $scope.post.schoolname = [];

      $(".SpinBank").show();
      $http({
        method: "post",
        url: url,
        data: $.param({
          type: "getschoolname",
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (data, status, headers, config) {
          //console.log(data.data);
          $scope.post.getschoolname = data.data.success ? data.data.data : [];
          $(".SpinBank").hide();
        },
        function (data, status, headers, config) {
          // console.log("Failed");
        }
      );
    };
    $scope.getschoolname();

    $scope.clear = function () {
      $scope.temp = {};
      $scope.temp.TEXT_ATTENDANCE_DATE = new Date();
      $scope.editMode = false;
      document.getElementById("TEXT_SCHOOL_ID").focus();
    };

    $scope.getIsvisible = function () {
      $scope.post.getIsvisible = [];

      $(".SpinBank").show();
      $http({
        method: "post",
        url: url,
        data: $.param({
          type: "getIsvisible",
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (data, status, headers, config) {
          console.log(data.data);
          $scope.post.getIsvisible = data.data.success ? data.data.data : [];
          $(".SpinBank").hide();
        },
        function (data, status, headers, config) {
          console.log("Failed");
        }
      );
    };

 $scope.getIsvisible();


$scope.edit = function (id) {
   
    document.getElementById("TEXT_SCHOOL_ID").focus();

    $scope.temp = {
    pmid: id.FIELD_VISIBILITY_ID,
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
    TEXT_FORM_ID: id.FORM_ID.toString(),
    TEXT_FIELD_NAME_DESC: id.FIELD_NAME_DESC,
    TEXT_FIELD_NAME: id.FIELD_NAME,
    TEXT_IS_VISIBLE  : id.IS_VISIBLE_CD.toString(),
		
    };

       
    $scope.editMode = true;
    $scope.index = $scope.post.getQuery.indexOf(id);
  };




 $scope.delete = function (id) {
  var r = confirm("Are you sure want to delete this record!");
  if (r == true) {
    $http({
      method: "post",
      url: url,
      data: $.param({
        pmid: id.FIELD_VISIBILITY_ID,
        type: "delete"
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data, status, headers, config) {
      console.log(data.data);

      if (data.data.success) {
        var index = $scope.post.getQuery.indexOf(id);
        $scope.post.getQuery.splice(index, 1);
        $scope.messageSuccess(data.data.message);
      } else {
        $scope.messageFailure(data.data.message);
      }
    }, function (err) {
      console.log("Error in deleting:", err);
    });
  }
};


    $scope.logout = function () {
      $http({
        method: "post",
        url: "code/logout.php",
        data: $.param({ type: "logout" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (response) {
          if (response.data.success) {
            window.location.assign("index.html#!/login");
          }
        },
        function (error) {
          console.error("Logout failed:", error);
          $scope.messageFailure("Failed to logout.");
        }
      );
    };

    $scope.messageSuccess = function (msg) {
      jQuery(".alert-success > span").html(msg);
      jQuery(".alert-success")
        .show()
        .delay(5000)
        .slideUp(function () {
          jQuery(".alert-success > span").html("");
        });
    };

    $scope.messageFailure = function (msg) {
      jQuery(".alert-danger > span").html(msg);
      jQuery(".alert-danger")
        .show()
        .delay(5000)
        .slideUp(function () {
          jQuery(".alert-danger > span").html("");
        });
    };
  });
