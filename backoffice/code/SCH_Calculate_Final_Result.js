// AngularJS Controller for Prepare Final Result

var $postModule = angular.module("myApp", ["angularUtils.directives.dirPagination", "ngSanitize"]);

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

$postModule.controller("myCtrl", function ($scope, $http, $interval, $timeout) {
  $scope.post = {};
  $scope.temp = {};
  $scope.editMode = false;
  $scope.showTables = false;
  $scope.Page = "MARKS";
  $scope.PageSub = "MASTER";
  $scope.PageSub1 = "SUBJECTSMASTER";

  var url = "code/SCH_Calculate_Final_Result_code.php";

  $scope.classChangeTriggered = false;
  $scope.queryFinalLoaded = false;

  
$scope.init = function () {
    $scope.getschoolname();
    $scope.getFinancialYear();
   
    $http({
      method: "post",
      url: "code/checkSession.php",
      data: $.param({ type: "checkSession" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
       

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
           
            window.location.assign("dashboard.html#!/dashboard");
          } else {
            // $scope.getQuery();
          }
        } else {
          
          $scope.logout();
        }
      },
      function (data, status, headers, config) {
        //console.log(data)
        console.log("Failed during Init");
      }
    );
  };

  $scope.save = function () {
    $(".btn-save, .btn-update").attr("disabled", "disabled");

    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function () {
        var formData = new FormData();
        formData.append("type", 'save');
        formData.append("weightageid", $scope.temp.weightageid);
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_CLASS_CD", $scope.temp.TEXT_CLASS_CD);
        formData.append("TEXT_FY_YEAR_CD", $scope.temp.TEXT_FY_YEAR_CD);
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data) {
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);
        $scope.getQuery();
        $scope.clear();
        document.getElementById("TEXT_SCHOOL_ID").focus();
      } else {
        $scope.messageFailure(data.data.message);
      }
      $(".btn-save, .btn-update").removeAttr("disabled");
    });
  };

  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
        TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
        type: "getQuery"
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      $scope.post.getQuery = data.data.data;
      $scope.showTables = true;
    }, function () {
      console.log("Failed during query");
      $scope.showTables = false;
    });
  };

  $scope.getQueryFinal = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
        TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
        type: "getQueryFinal"
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      $scope.post.getQueryFinal = data.data.data || [];
      $scope.showTables = true;

      if ($scope.classChangeTriggered && Array.isArray($scope.post.getQueryFinal) && $scope.post.getQueryFinal.length === 0) {
        $scope.messageFailure("Result is not Prepared! Please prepare Final Result !");
      }
      $scope.classChangeTriggered = false;
    }, function () {
      console.log("Failed during queryFinal");
      $scope.classChangeTriggered = false;
    });
  };

  $scope.onClassChange = function () {
    $scope.temp.TEXT_STUDENT_ID = null;
    $scope.classChangeTriggered = true;
    $scope.getQuery();
    $scope.getQueryFinal();
  };


$scope.onStudentChange = function () {
  if (!$scope.temp.TEXT_STUDENT_ID) return;

  $scope.getQuery();
  $scope.getQueryFinal();

  // Wait for both query results to load, then check for data
  $timeout(function () {
    var subjectData = Array.isArray($scope.post.getQuery) ? $scope.post.getQuery : [];
    var overallData = Array.isArray($scope.post.getQueryFinal) ? $scope.post.getQueryFinal : [];

    if (subjectData.length === 0 && overallData.length === 0) {
      $scope.messageFailure("Marks are not entered for this Student!");
    }
  }, 500);  // Delay allows the HTTP responses to complete
};



$scope.showResult = function () {
  if (!$scope.temp.TEXT_SCHOOL_ID) {
    $scope.messageFailure("Please select School.");
    return;
  }
  if (!$scope.temp.TEXT_CLASS_CD) {
    $scope.messageFailure("Please select Class.");
    return;
  }
  if (!$scope.temp.TEXT_FY_YEAR_CD) {
    $scope.messageFailure("Please select Academic Year.");
    return;
  }

  $scope.temp.TEXT_STUDENT_ID = null;  // Reset student selection
  $scope.classChangeTriggered = false;
  $scope.getQuery();
  $scope.getQueryFinal();
};


  $scope.getStudent = function () {
    $scope.post.getStudent = [];
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        type: "getStudent",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      $scope.post.getStudent = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    });
  };

  $scope.getFinancialYear = function () {
    $scope.post.getFinancialYear = [];
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getFinancialYear" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      if (data.data.success && data.data.data.length > 0) {
        $scope.post.getFinancialYear = data.data.data;
        $scope.temp.TEXT_FY_YEAR_CD = data.data.data[0].CODE_DETAIL_ID.toString();
      }
      $(".SpinBank").hide();
    });
  };

  $scope.getClass = function () {
    $scope.post.getClass = [];
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getClass",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      $scope.post.getClass = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    });
  };

  $scope.getschoolname = function () {
    $scope.post.getschoolname = [];
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getschoolname" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      $scope.post.getschoolname = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    });
  };
$scope.getschoolname();



  $scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_ID").focus();
    $scope.temp = {};
    $scope.editMode = false;
    $scope.showTables = false;
  };

  $scope.edit = function (id) {
    document.getElementById("TEXT_SCHOOL_ID").focus();
    $scope.temp = {
      weightageid: id.WEIGHTAGE_ID,
      TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
      TEXT_CLASS_CD: id.CLASS_CD.toString(),
      TEXT_FY_YEAR_CD: id.FY_YEAR_CD.toString(),
    };
    $scope.editMode = true;
    $scope.index = $scope.post.getQuery.indexOf(id);
  };

  $scope.logout = function () {
    $http({
      method: "post",
      url: "code/logout.php",
      data: $.param({ type: "logout" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(function (data) {
      if (data.data.success) {
        window.location.assign("index.html#!/login");
      }
    }, function () {
      console.log("Logout failed");
    });
  };

  $scope.messageSuccess = function (msg) {
    jQuery(".alert-success > span").html(msg);
    jQuery(".alert-success").show().delay(5000).slideUp(function () {
      jQuery(".alert-success > span").html("");
    });
  };

  $scope.messageFailure = function (msg) {
    jQuery(".alert-danger > span").html(msg);
    jQuery(".alert-danger").show().delay(5000).slideUp(function () {
      jQuery(".alert-danger > span").html("");
    });
  };
});
