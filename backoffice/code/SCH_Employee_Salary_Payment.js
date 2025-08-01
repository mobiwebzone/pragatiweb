$postModule = angular.module("myApp", [
  "angularUtils.directives.dirPagination",
  "ngSanitize",
]);
$postModule.directive("bindHtmlCompile", [
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
]);
$postModule.controller("myCtrl", function ($scope, $http, $interval, $timeout) {
  $scope.post = {};
  $scope.temp = {};
  $scope.editMode = false;
  $scope.Page = "STUDENT";
  $scope.PageSub = "FEESPAYMENT";
  $scope.PageSub1 = "SCHFEESPAYMENT";
  $scope.temp.TEXT_PAYMENT_DATE = new Date();
  $scope.temp.TEXT_CHEQUE_DATE = new Date();
  $scope.temp.TEXT_OTHER_FEES_AMOUNT = 0;
  $scope.temp.TEXT_TOTAL_FEES_AMOUNT = 0;
  $scope.temp.TEXT_FEES_DUE = "";

  var url = "code/SCH_Employee_Salary_Payment_code.php";

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
            //$scope.getQuery();
            $scope.getschoolname();
            $scope.getFinancialYear();
            $scope.getMonth();
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
  $(".btn-save").attr("disabled", "disabled");
  $(".btn-update").attr("disabled", "disabled");

  // Step 1: Validate total payments
  const netSalaryObj = ($scope.post.getNetSalary || []).find(
    x => x.SALARY_PROCESS_ID == $scope.temp.TEXT_SALARY_PROCESS_ID
  );
  const currentPaidAmount = parseFloat($scope.temp.TEXT_PAID_AMOUNT || 0);
  const allPayments = $scope.post.getQuery || [];

  const totalPaidSoFar = allPayments
    .filter(x => x.SALARY_PROCESS_ID == $scope.temp.TEXT_SALARY_PROCESS_ID)
    .reduce((sum, item) => sum + parseFloat(item.PAID_AMOUNT || 0), 0);

  const totalWithNewPayment = totalPaidSoFar + currentPaidAmount;

  if (netSalaryObj && totalWithNewPayment > netSalaryObj.NET_SALARY) {
    $scope.messageFailure("Total salary paid exceeds NET SALARY for this month!");
    $(".btn-save").removeAttr("disabled");
    $(".btn-update").removeAttr("disabled");
    return;
  }

  // Step 2: Proceed to save
  $http({
    method: "POST",
    url: url,
    processData: false,
    transformRequest: function (data) {
      var formData = new FormData();
      formData.append("type", "save");
      formData.append("paymentid", $scope.temp.paymentid);
      formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
      formData.append("TEXT_MONTH_ID", $scope.temp.TEXT_MONTH_ID);
      formData.append("TEXT_EMPLOYEE_ID", $scope.temp.TEXT_EMPLOYEE_ID);
      formData.append("TEXT_FY_YEAR_CD", $scope.temp.TEXT_FY_YEAR_CD);
      formData.append("TEXT_SALARY_PROCESS_ID", $scope.temp.TEXT_SALARY_PROCESS_ID);
      formData.append("TEXT_PAID_AMOUNT", $scope.temp.TEXT_PAID_AMOUNT);
      formData.append("TEXT_PAYMENT_DATE", $scope.temp.TEXT_PAYMENT_DATE.toLocaleString("sv-SE"));
      formData.append("TEXT_PAYMENT_MODE_CD", $scope.temp.TEXT_PAYMENT_MODE_CD);
      formData.append("TEXT_CHEQUE_NO", $scope.temp.TEXT_CHEQUE_NO);
      formData.append("TEXT_CHEQUE_DATE", $scope.temp.TEXT_CHEQUE_DATE ? $scope.temp.TEXT_CHEQUE_DATE.toLocaleString("sv-SE") : '');
      formData.append("TEXT_BANK_CD", $scope.temp.TEXT_BANK_CD);
      formData.append("TEXT_UPI_ID", $scope.temp.TEXT_UPI_ID);
      formData.append("TEXT_UPI_PLATFORM", $scope.temp.TEXT_UPI_PLATFORM);
      formData.append("TEXT_MOBILE_NO", $scope.temp.TEXT_MOBILE_NO);
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
    $(".btn-save").removeAttr("disabled");
    $(".btn-update").removeAttr("disabled");
  });
};


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
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getMonth();

 
 $scope.getBank = function () {
    $scope.post.getBank = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getBank",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getBank = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };
  // $scope.getBank();

 
  $scope.getEmployeeNameDetails = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
        TEXT_EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
        type: "getEmployeeNameDetails",
      }),
      headers: { "Content-Type": " application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);

        $scope.post.getEmployeeNameDetails = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed during query");
      }
    );
  }; 
  
  
  
  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
        TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
        TEXT_EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
        type: "getQuery",
      }),
      headers: { "Content-Type": " application/x-www-form-urlencoded" },
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

  $scope.getOtherFees = function () {
    $scope.post.getOtherFees = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getOtherFees",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        $scope.post.getOtherFees = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {}
    );
  };
  $scope.getOtherFees();

  $scope.getPaymentMode = function () {
    $scope.post.getPaymentMode = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getPaymentMode",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        $scope.post.getPaymentMode = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {}
    );
  };
  $scope.getPaymentMode();


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
        // console.log(data.data);
        $scope.post.getEmployeeName = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  // $scope.getEmployeeName();

  $scope.getNetSalary = function () {
    $scope.post.getNetSalary = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
     
        TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
        TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
        TEXT_EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
        type: "getNetSalary",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getNetSalary = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  // $scope.getNetSalary();

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



  // $scope.edit = function (id) {
  //   document.getElementById("TEXT_SCHOOL_ID").focus();

  //   $scope.temp = {
  //     paymentid: id.PAYMENT_ID,
  //     TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
  //     TEXT_MONTH_ID: id.CLASS_CD.toString(),
  //     TEXT_FY_YEAR_CD: id.FY_YEAR_CD.toString(),
  //     TEXT_PAYMENT_DATE: id.PAYMENT_DATE ? new Date(id.PAYMENT_DATE) : "",
  //     TEXT_PAID_AMOUNT: id.FEES_PAID,
  //     TEXT_RECEIPT_NO: id.RECEIPT_NO,
  //     TEXT_PAYMENT_MODE_CD: id.PAYMENT_MODE_CD.toString(),
  //     txtremarks: id.REMARKS,
  //   };

  //   $scope.getEmployeeName();
  //   $timeout(() => {
  //     $scope.temp.TEXT_EMPLOYEE_ID = id.STUDENT_ID.toString();
  //   }, 500);

  //   $scope.editMode = true;
  //   $scope.index = $scope.post.getQuery.indexOf(id);
  // };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    $scope.temp = {};

    $scope.temp.TEXT_FEES_DUE = "";

    $scope.temp.paymentid = "";

    $scope.editMode = false;
    document.getElementById("TEXT_SCHOOL_ID").focus();
  };

  /* ========== DELETE =========== */
  $scope.delete = function (id) {
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
      $http({
        method: "post",
        url: url,
        data: $.param({
        schoolid :id.SCHOOL_ID,
        paymentid: id.PAYMENT_ID,
        monthid: id.MONTH_ID,
        empid: id.EMPLOYEE_ID,
        salaryprocessid : id.SALARY_PROCESS_ID,
          type: "delete",
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        if (data.data.success) {
          var index = $scope.post.getQuery.indexOf(id);
          $scope.post.getQuery.splice(index, 1);

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
