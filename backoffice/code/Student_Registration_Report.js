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
  $scope.Page = "STUDENT";
  $scope.PageSub = "REGISTRATION";
  $scope.PageSub1 = "SCHREGISTRATION";
  $scope.temp.TEXT_DATE_OF_ADMISSION = new Date();
  $scope.temp.TEXT_DOB = new Date();
  $scope.temp.TEXT_DATE_OF_LEAVING = new Date();
  
 

  var url = "code/Student_Registration_Report_code.php";

$scope.showDetailsBlock = true;

$scope.showStudentBlocks = false;

$scope.visibleFields = [];

  // Load visible fields based on school ID
  $scope.loadVisibleFields = function () {
    const schoolId = $scope.temp.TEXT_SCHOOL_ID;
    // console.log("Loading visible fields for school ID:", schoolId);

    $http.post(url, $.param({
      type: "getVisibleFields",
      TEXT_SCHOOL_ID: schoolId
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (response) {
      if (response.data.success && Array.isArray(response.data.visibleFields)) {
        $scope.visibleFields = response.data.visibleFields;
        // console.log("Visible fields loaded:", $scope.visibleFields);
      } else {
        console.warn("Visible fields not loaded properly.", response.data);
      }
    }, function (error) {
      console.error("Error loading visible fields", error);
    });
  };

  // Visibility check function
  $scope.isFieldVisible = function (fieldKey) {
    // console.log("Checking visibility for", fieldKey, "in", $scope.visibleFields);
    return $scope.visibleFields.includes(fieldKey);
  };

  // Watch for changes in selected school ID
  $scope.$watch('temp.TEXT_SCHOOL_ID', function (newVal) {
    if (newVal) {
      $scope.loadVisibleFields();
    }
  });



  
  $scope.init = function () {
    // Check Session
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
            $scope.getFinancialYear();
            // $scope.showDetailsBlock = true;
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

 $scope.loadStudentDetails = function(studentId, classCd, fyYearCd) {
    $scope.getFees(studentId, classCd, fyYearCd);
    $scope.getSecurity(studentId);
    $scope.getStudentDetails(studentId);
    $scope.getFeesMaster(studentId, classCd, fyYearCd);
    $scope.showDetailsBlock = false;
    $scope.showStudentBlocks = true; 
};


  $scope.getFinancialYear = function () {
  $http({
    method: "post",
    url: url,
    data: $.param({ type: "getFinancialYear" }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(function (response) {
    if (response.data.success && Array.isArray(response.data.data)) {
      $scope.post.getFinancialYear = response.data.data;

      // ✅ Set the first financial year as default
      if ($scope.post.getFinancialYear.length > 0) {
        $scope.temp.TEXT_FEES_FY_YEAR_CD = $scope.post.getFinancialYear[0].CODE_DETAIL_ID;

        // Optionally load data after setting year
        $scope.getQuery();
      }
    }
    $(".SpinBank").hide();
  });
};

 $scope.getFinancialYear();


$scope.getFeesMaster = function (studentId, classCd, fyYearCd) {
  $scope.temp.TEXT_STUDENT_ID = studentId;
  $scope.temp.TEXT_CLASS_CD = classCd;
  $scope.temp.TEXT_FEES_FY_YEAR_CD = fyYearCd;
  $scope.temp.pmid = studentId; // Optional: highlight the selected row

  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_FEES_FY_YEAR_CD: fyYearCd,
      TEXT_CLASS_CD: classCd,
      TEXT_STUDENT_ID: studentId,
      type: "getFeesMaster",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      $scope.post.getFeesMaster = response.data.data;
    },
    function () {
      console.log("Failed during getFees()");
    }
  );
};



$scope.getFees = function (studentId, classCd, fyYearCd) {
  $scope.temp.TEXT_STUDENT_ID = studentId;
  $scope.temp.TEXT_CLASS_CD = classCd;
  $scope.temp.TEXT_FEES_FY_YEAR_CD = fyYearCd;
  $scope.temp.pmid = studentId; // Optional: highlight the selected row

  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_FEES_FY_YEAR_CD: fyYearCd,
      TEXT_CLASS_CD: classCd,
      TEXT_STUDENT_ID: studentId,
      type: "getFees",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      $scope.post.getFees = response.data.data;
    },
    function () {
      console.log("Failed during getFees()");
    }
  );
};


$scope.getSecurity = function (studentId) {
  $scope.temp.TEXT_STUDENT_ID = studentId;

  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_STUDENT_ID: studentId,
      type: "getSecurity",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      console.log("Response from getSecurity():", response.data);
      
      // If response.data.data is an object, wrap in array
      const result = Array.isArray(response.data.data) ? response.data.data : [response.data.data];
      
      $scope.post.getSecurity = result;
      console.log("post.getSecurity (final):", $scope.post.getSecurity);
    },
    function () {
      console.log("Failed during getSecurity()");
    }
  );
};



$scope.getStudentDetails = function (studentId) {
  $scope.temp.TEXT_STUDENT_ID = studentId;

  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_STUDENT_ID: studentId,
      type: "getStudentDetails",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      console.log("Response from getStudentDetails():", response.data);
      
      // If response.data.data is an object, wrap in array
      const result = Array.isArray(response.data.data) ? response.data.data : [response.data.data];
      
      $scope.post.getStudentDetails = result;
      console.log("post.getSecurity (final):", $scope.post.getSecurity);
    },
    function () {
      console.log("Failed during getSecurity()");
    }
  );
};



  $scope.getRte = function () {
    $scope.post.getRte = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getRte",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getRte = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getRte();


  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_RTE_CD : $scope.temp.TEXT_RTE_CD,
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
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getClass = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  $scope.getClass();

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

  /* ============ Clear Form =========== */
  // $scope.clear = function () {
  //   document.getElementById("TEXT_SCHOOL_ID").focus();
  //   $scope.temp = {};
  //   $scope.editMode = false;
  // };
$scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_ID").focus();
    $scope.temp = {};
    $scope.showDetailsBlock = true;
    $scope.showStudentBlocks = false;
    $scope.post.getFees = [];
    $scope.post.getFeesMaster = [];
    $scope.post.getSecurity = [];
    $scope.editMode = false;
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