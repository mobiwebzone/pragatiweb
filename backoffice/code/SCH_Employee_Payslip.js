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
  $scope.Page = "MARKS";
  $scope.PageSub = "MASTER";
  $scope.PageSub1 = "MARKSMASTER";
   $scope.showPrintSection = false;
 
 

  var url = "code/SCH_Employee_Payslip_code.php";

  
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
            //  $scope.getQuery();
             $scope.getMonth();
             $scope.getFinancialYear();
             $scope.getSalaryComponents();
             $scope.loadSalaryComponents();
             $scope.getschoolname();
              
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


$scope.getPayslipHeader = function () {
    $http.post(url, $.param({
        type: "getPayslipHeader",
        EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
        SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID
    }), {
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
        if (res.data.success) {
            $scope.post.payslipHeader = res.data.data;
        } else {
            $scope.messageFailure("Header not found");
        }
    });
};


$scope.getMonthName = function (monthId) {
  let match = $scope.post.getMonth.find(m => m.MONTH_ID == monthId);
  return match ? match.MONTH : '';
};
$scope.getYearText = function (yearCd) {
  let match = $scope.post.getFinancialYear.find(y => y.CODE_DETAIL_ID == yearCd);
  return match ? match.CODE_DETAIL_DESC : '';
};




$scope.getSalaryComponents = function () {
  $scope.post.salaryComponents = [];

  $http({
    method: "post",
    url: url,
    data: $.param({ type: "getSalaryComponents" }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      if (response.data.success) {
        $scope.post.salaryComponents = response.data.data;
      }
    },
    function () {
      console.error("Failed to load salary components.");
    }
  );
};

// Initialize list to hold temporary salary heads
$scope.post.tempComponents = [];


$scope.loadSalaryComponents = function () {
  $http.post(url, $.param({ type: "getSalaryComponents" }), {
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(function (response) {
    $scope.post.salaryComponents = response.data.success ? response.data.data : [];
  });
};
$scope.loadSalaryComponents(); // Call once at init



$scope.loadTempComponents = function() {
  if (!$scope.temp.TEXT_EMPLOYEE_ID || !$scope.temp.TEXT_MONTH_ID) return;

  $http.post(url, $.param({
    type: "getTempComponents",
    EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
    MONTH_ID: $scope.temp.TEXT_MONTH_ID
  }), { headers: { "Content-Type": "application/x-www-form-urlencoded" } }).then(function(res) {
    $scope.post.tempComponents = res.data.success ? res.data.data : [];
  });
};


$scope.getQuery = function () {
  // Validate inputs before proceeding
  if (
    !$scope.temp.TEXT_SCHOOL_ID ||
    !$scope.temp.TEXT_EMPLOYEE_ID ||
    !$scope.temp.TEXT_MONTH_ID ||
    !$scope.temp.TEXT_FY_YEAR_CD
  ) {
    $scope.showPrintSection = false;
    return;
  }

  // Fetch payslip data; backend will validate salary processing
  $http({
    method: "post",
    url: url,
    data: $.param({
      type: "getQuery",
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
      TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
      TEXT_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
  }).then(function (response) {
    if (response.data.success) {
      $scope.post.getQuery = response.data.data;
      $scope.getPayslipHeader();
      $scope.showPrintSection = $scope.post.getQuery.length > 0;
    } else {
      $scope.messageFailure(response.data.message || "Salary is not processed or an error occurred.");
      $scope.showPrintSection = false;
    }
  }, function () {
    $scope.messageFailure("Server error while fetching payslip.");
    $scope.showPrintSection = false;
  });
};



$scope.onInputChange = function () {
  // console.log("onInputChange triggered New");

  if (
    !$scope.temp.TEXT_SCHOOL_ID ||
    !$scope.temp.TEXT_EMPLOYEE_ID ||
    !$scope.temp.TEXT_MONTH_ID ||
    !$scope.temp.TEXT_FY_YEAR_CD
  ) {
    $scope.showPrintSection = false;
    return;
  }

  // Just call getQuery; backend will handle salary processed check
  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
      TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
      TEXT_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
      type: "getQuery"
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
  }).then(function (response) {
    if (response.data.success) {
      $scope.post.getQuery = response.data.data;
      $scope.getPayslipHeader();
      $scope.showPrintSection = $scope.post.getQuery.length > 0;
    } else {
      $scope.messageFailure(response.data.message || "Error fetching payslip.");
      $scope.showPrintSection = false;
    }
  }, function () {
    $scope.messageFailure("Failed to fetch data from server.");
    $scope.showPrintSection = false;
  });
};



$scope.getEmployeeName = function () {
    $scope.post.getEmployeeName = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getEmployeeName",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        $scope.post.getEmployeeName = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {}
    );
  };
  $scope.getEmployeeName();

$scope.getMonth = function () {
    $scope.post.getMonth = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getMonth",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        $scope.post.getMonth = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {}
    );
  };
  $scope.getMonth();


$scope.getFinancialYear = function () {
    $scope.post.getFinancialYear = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getFinancialYear",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        $scope.post.getFinancialYear = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {}
    );
  };
  $scope.getFinancialYear();


   
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

function printPayslip() {
  var content = document.getElementById("print-area").innerHTML;
  var w = window.open();
  w.document.write('<html><head><title>Payslip</title>');
  w.document.write('<link rel="stylesheet" href="../css/bootstrap.min.css">'); // Bootstrap for styling
  w.document.write('</head><body>');
  w.document.write(content);
  w.document.write('</body></html>');
  w.document.close();
  w.focus();
  setTimeout(function () {
    w.print();
    w.close();
  }, 500);
}




  $scope.edit = function (id) {
   
    

    $scope.temp = {
    salaryid: id.SALARY_ID,
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
    TEXT_EMPLOYEE_ID: id.EMPLOYEE_ID.toString(),
    TEXT_MONTH_ID : id.COMPONENT_ID.toString(),
    
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
        data: $.param({
          salaryid: id.SALARY_ID,
          type: "delete"
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
      

        if (data.data.success) {
          var index = $scope.post.getQuery.indexOf(id);
          $scope.post.getQuery.splice(index, 1);
         
          $scope.messageSuccess(data.data.message);
        } else {
          //  console.error('Error occurred! Backend response:', data);
          //  alert("Error: " + (data.data.message || "Unknown backend error"));
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
       
        if (data.data.success) {
          window.location.assign("index.html#!/login");
        } else {
         
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