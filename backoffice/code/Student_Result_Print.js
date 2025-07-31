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

$postModule.controller("myCtrl", function ($scope, $http) {
  $scope.post = {};
  $scope.temp = {};
  $scope.editMode = false;

  $scope.showTables = false;
  $scope.showStudentMarks = false;

  var url = "code/Student_Result_Print_code.php";

  $scope.init = function () {
    $scope.getschoolname();
    $scope.getFinancialYear();
    $scope.checkSession();
  };

  $scope.checkSession = function () {
    $http.post("code/checkSession.php", $.param({ type: "checkSession" }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      if (res.data.success) {
        $scope.post.user = res.data.data;
        $scope.userid = res.data.userid;
        $scope.userrole = res.data.userrole;
        if ($scope.userrole !== "ADMINISTRATOR" && $scope.userrole !== "SUPERADMIN") {
          window.location.assign("dashboard.html#!/dashboard");
        }
      } else {
        $scope.logout();
      }
    });
  };

  $scope.logout = function () {
    $http.post("code/logout.php", $.param({ type: "logout" }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      if (res.data.success) {
        window.location.assign("index.html#!/login");
      }
    });
  };

  $scope.getschoolname = function () {
    $http.post(url, $.param({ type: "getschoolname" }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.getschoolname = res.data.success ? res.data.data : [];
    });
  };

  $scope.getClass = function () {
    $http.post(url, $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      type: "getClass"
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.getClass = res.data.success ? res.data.data : [];
    });
  };

  $scope.getFinancialYear = function () {
    $http.post(url, $.param({ type: "getFinancialYear" }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      if (res.data.success && res.data.data.length > 0) {
        $scope.post.getFinancialYear = res.data.data;
        $scope.temp.TEXT_FY_YEAR_CD = res.data.data[0].CODE_DETAIL_ID.toString();
      }
    });
  };

  $scope.getStudent = function () {
    $http.post(url, $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
      type: "getStudent"
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.getStudent = res.data.success ? res.data.data : [];
    });
  };

  $scope.getQuery = function () {
    $http.post(url, $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
      TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
      TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
      type: "getQuery"
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.getQuery = Array.isArray(res.data.data) ? res.data.data : [];

      if ($scope.post.getQuery.length > 0) {
        const student = $scope.post.getQuery[0];
        $scope.dob = student.DOB;
        $scope.fatherName = student.FATHER_NAME;
        $scope.motherName = student.MOTHER_NAME;
        $scope.scholarNo = student.SCHOLAR_NO;
        $scope.samagraid = student.SAMAGRA_ID;
        $scope.section = student.SECTION;
        $scope.uid = student.UID;
        $scope.adhar = student.ADHAR_NO;
        $scope.doa = student.DATE_OF_ADMISSION;
         $scope.pen = student.PEN;

        $scope.totalMarks = $scope.post.getQuery.reduce((sum, s) => sum + Number(s.TOTAL_MARKS || 0), 0);
        $scope.totalMarksObtained = $scope.post.getQuery.reduce((sum, s) => sum + Number(s.MARKS_OBTAINED || 0), 0);
        $scope.finalPercentage = (($scope.totalMarksObtained / $scope.totalMarks) * 100).toFixed(2);
        $scope.finalGrade = student.FINAL_GRADE;
      }

      $scope.checkIfEmptyResults();
    }, function () {
      $scope.post.getQuery = [];
      $scope.checkIfEmptyResults();
    });
  };

  $scope.getQueryFinal = function () {
    $http.post(url, $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
      TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
      TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
      type: "getQueryFinal"
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.getQueryFinal = Array.isArray(res.data.data) ? res.data.data : [];
      $scope.checkIfEmptyResults();
    }, function () {
      $scope.post.getQueryFinal = [];
      $scope.checkIfEmptyResults();
    });
  };

  $scope.getQuery_student = function () {
    $http.post(url, $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
      TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
      TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
      type: "getQuery_student"
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.getQuery_student = Array.isArray(res.data.data) ? res.data.data : [];
    });
  };
  
  $scope.schoolTemplate = '';

  $scope.onStudentChange = function () {
    if (!$scope.temp.TEXT_STUDENT_ID) return;
    $scope.currentDateTime = moment().format("DD-MM-YYYY hh:mm A");
    const selectedSchool = $scope.post.getschoolname.find(s => s.SCHOOL_ID == $scope.temp.TEXT_SCHOOL_ID);
    $scope.schoolTemplate = selectedSchool?.RESULT_TEMPLATE || 'default';

    $scope.getQuery();
    $scope.getQueryFinal();
    $scope.showTables = true;
    $scope.showStudentMarks = false;
  };

  $scope.showResult = function () {
    if (!$scope.temp.TEXT_SCHOOL_ID || !$scope.temp.TEXT_CLASS_CD || !$scope.temp.TEXT_FY_YEAR_CD || !$scope.temp.TEXT_STUDENT_ID) {
      $scope.messageFailure("Please select all required fields.");
      return;
    }

    $scope.getQuery_student();
    $scope.showStudentMarks = true;
    $scope.currentDateTime = moment().format("DD-MM-YYYY hh:mm A");
  };

  $scope.hideResult = function () {
    $scope.showStudentMarks = false;
  };

  $scope.onClassChange = function () {
    $scope.temp.TEXT_STUDENT_ID = null;
    $scope.showTables = false;
    $scope.showStudentMarks = false;
  };

  $scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_ID").focus();
    $scope.temp = {};
    $scope.editMode = false;
    $scope.showTables = false;
    $scope.showStudentMarks = false;
  };

  $scope.getStudentName = function (id) {
    const obj = $scope.post.getStudent.find(x => x.STUDENT_ID == id);
    return obj ? obj.STUDENT_NAME : '';
  };

  $scope.getClassName = function (id) {
    const obj = $scope.post.getClass.find(x => x.CLASS_CD == id);
    return obj ? obj.CLASS : '';
  };

  $scope.getSchoolName = function (id) {
    const obj = $scope.post.getschoolname.find(x => x.SCHOOL_ID == id);
    return obj ? obj.SCHOOL_NAME : '';
  };

  $scope.getSchoolAddress = function (id) {
    const obj = $scope.post.getschoolname.find(x => x.SCHOOL_ID == id);
    return obj ? obj.ADDRESS : '';
  };

  $scope.getAcademicYearDesc = function (id) {
    const obj = $scope.post.getFinancialYear.find(x => x.CODE_DETAIL_ID == id);
    return obj ? obj.CODE_DETAIL_DESC : '';
  };

  $scope.messageSuccess = function (msg) {
    $(".alert-success > span").html(msg);
    $(".alert-success").show().delay(5000).slideUp(() => {
      $(".alert-success > span").html("");
    });
  };

  $scope.messageFailure = function (msg) {
    $(".alert-danger > span").html(msg);
    $(".alert-danger").show().delay(5000).slideUp(() => {
      $(".alert-danger > span").html("");
    });
  };

  $scope.checkIfEmptyResults = function () {
    if (
      Array.isArray($scope.post.getQuery) &&
      Array.isArray($scope.post.getQueryFinal) &&
      $scope.post.getQuery.length === 0 &&
      $scope.post.getQueryFinal.length === 0
    ) {
      $scope.messageFailure("Result is not Prepared! Please prepare Final Result.");
    }
  };

  // Init call
  $scope.init();
});
