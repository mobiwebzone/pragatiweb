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

    var url = "code/SCH_Student_Attendance_code.php";

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
      $(".btn-save").prop("disabled", true).text("SAVING...");
      var attendanceData = $scope.post.getQuery.map(function (x) {
        return {
          STUDENT_ID: x.STUDENT_ID,
          ATTENDANCE_STATUS: x.ATTENDANCE_STATUS === 1, // true for Present, false for Absent
        };
      });

      if (
        !$scope.temp.TEXT_SCHOOL_ID ||
        !$scope.temp.TEXT_CLASS_CD ||
        !$scope.temp.TEXT_ATTENDANCE_DATE
      ) {
        $scope.messageFailure(
          "Please select School, Class, and Attendance Date."
        );
        $(".btn-save").prop("disabled", false).text("SAVE");
        return;
      }

      var formattedDate = moment($scope.temp.TEXT_ATTENDANCE_DATE).format(
        "YYYY-MM-DD"
      );
      console.log("Sending data:", {
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_ATTENDANCE_DATE: formattedDate,
        attendanceData: attendanceData,
      });

      $http({
        method: "POST",
        url: url,
        processData: false,
        transformRequest: function (data) {
          var formData = new FormData();
          formData.append("type", "save");
          formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
          formData.append("TEXT_CLASS_CD", $scope.temp.TEXT_CLASS_CD);
          formData.append("TEXT_ATTENDANCE_DATE", formattedDate);
          formData.append("TEXT_TEACHER_ID", $scope.temp.TEXT_TEACHER_ID);
          formData.append("attendanceData", JSON.stringify(attendanceData));
          return formData;
        },
        data: $scope.temp,
        headers: { "Content-Type": undefined },
      }).then(
        function (response) {
          if (response.data.success) {
            $scope.messageSuccess(response.data.message);
            $scope.getQuery();
            $scope.clear();
            document.getElementById("TEXT_SCHOOL_ID").focus();
          } else {
            console.error("Save error:", response.data);
            let errorMsg =
              response.data.message || "Failed to save attendance.";
            if (errorMsg.includes("Attendance already recorded")) {
              errorMsg =
                "Attendance already recorded for one or more students on this date.";
            }
            $scope.messageFailure(errorMsg);
          }
          $(".btn-save").prop("disabled", false).text("SAVE");
        },
        function (error) {
          console.error("HTTP error:", error);
          $scope.messageFailure(
            "Network error: " +
              (error.statusText || "Failed to save attendance.")
          );
          $(".btn-save").prop("disabled", false).text("SAVE");
        }
      );
    };

    $scope.getQuery = function () {
      var formattedDate = $scope.temp.TEXT_ATTENDANCE_DATE
        ? moment($scope.temp.TEXT_ATTENDANCE_DATE).format("YYYY-MM-DD")
        : null;

    //   console.log("getQuery params:", {
    //     TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
    //     TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
    //     TEXT_ATTENDANCE_DATE: formattedDate,
    //   });

      $http({
        method: "post",
        url: url,
        data: $.param({
          type: "getQuery",
          TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
          TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
          TEXT_ATTENDANCE_DATE: formattedDate,
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (response) {
          console.log("getQuery response:", response.data);
          if (response.data.success && Array.isArray(response.data.data)) {
            $scope.post.getQuery = response.data.data.map(function (item) {
              item.ATTENDANCE_STATUS = item.ATTENDANCE_STATUS === 1 ? 1 : 2; // Ensure 1 or 2
              console.log(
                "Student:",
                item.STUDENT_NAME,
                "Status:",
                item.ATTENDANCE_STATUS
              );
              return item;
            });
            $scope.$apply(); // Force UI update
          } else {
            console.error("Invalid response:", response.data);
            $scope.messageFailure(
              response.data.message || "Failed to load student data."
            );
            $scope.post.getQuery = [];
          }
        },
        function (error) {
          console.error("Query failed:", error);
          $scope.messageFailure(
            "Failed to load student data: " +
              (error.statusText || "Unknown error")
          );
          $scope.post.getQuery = [];
        }
      );
    };

    $scope.getClass = function () {
      $scope.post.getClass = [];
      $(".SpinBank").show();
      $http({
        method: "post",
        url: url,
        data: $.param({
          type: "getClass",
          TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (response) {
          $scope.post.getClass = response.data.success
            ? response.data.data
            : [];
          $(".SpinBank").hide();
        },
        function (error) {
          console.error("Failed to load classes:", error);
          $scope.messageFailure("Failed to load classes.");
          $(".SpinBank").hide();
        }
      );
    };

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

    $scope.getTeacher = function () {
      $scope.post.getTeacher = [];

      $(".SpinBank").show();
      $http({
        method: "post",
        url: url,
        data: $.param({
          TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
          type: "getTeacher",
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (data, status, headers, config) {
          console.log(data.data);
          $scope.post.getTeacher = data.data.success ? data.data.data : [];
          $(".SpinBank").hide();
        },
        function (data, status, headers, config) {
          console.log("Failed");
        }
      );
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
