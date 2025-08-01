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
  
  $scope.temp.TEXT_PAYMENT_DATE = new Date();
 

  var url = "code/SCH_Employee_Salary_Summary_code.php";

  
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


$scope.togglePaidStatus = function (row) {
    var confirmMsg = row.IS_PAID ? 'Mark as Paid?' : 'Mark as Unpaid?';
    if (confirm(confirmMsg)) {
        $http({
            method: "post",
            url: url,
            data: $.param({
                type: "updatePaidStatus",
                SALARY_PROCESS_ID: row.SALARY_PROCESS_ID,
                IS_PAID: row.IS_PAID,
                PAID_BY: $scope.userid,
                PAYMENT_CONFIRMED_DATE: new Date().toISOString().split('T')[0]
            }),
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(function (res) {
            if (!res.data.success) {
                alert("Failed to update paid status");
            }
        });
    } else {
        row.IS_PAID = row.IS_PAID ? 0 : 1; // revert
    }
};


$scope.markAllPaid = function() {
  if (!$scope.List || $scope.List.length === 0) {
    alert("No records to mark.");
    return;
  }

  if (!confirm("Are you sure you want to mark all employees as PAID?")) {
    return;
  }

  const formData = $.param({
    type: 'markAllPaid',
    TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
    TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
    PAID_BY: $scope.userid,
    PAYMENT_CONFIRMED_DATE: new Date().toISOString().split('T')[0]
  });

  $http({
    method: 'post',
    url: url,
    data: formData,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
  }).then(
    function(response) {
      if (response.data.success) {
        $scope.messageSuccess("All salaries marked as PAID successfully.");
        $scope.getQuery();  // ✅ Refresh table data
      } else {
        $scope.messageFailure("Failed to mark all as paid.");
      }
    },
    function() {
      $scope.messageFailure("Error occurred while marking all as paid.");
    }
  );
};


$scope.togglePayment = function(row) {
  const message = row.IS_PAID ? "Are you sure you want to mark this salary as PAID?" : "Are you sure you want to UNMARK this salary as paid?";
  
  if (!confirm(message)) {
    row.IS_PAID = !row.IS_PAID; // revert checkbox
    return;
  }

  const formData = $.param({
    type: 'togglePaymentStatus',
    SALARY_PROCESS_ID: row.SALARY_PROCESS_ID,
    IS_PAID: row.IS_PAID ? 1 : 0
  });

  $http({
    method: 'post',
    url: url,
    data: formData,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
  }).then(
    function(response) {
      if (!response.data.success) {
        $scope.messageFailure("Update failed");
        row.IS_PAID = !row.IS_PAID; // revert checkbox
      } else {
        $scope.messageSuccess("Updated successfully");
      }
    },
    function() {
      $scope.messageFailure("Update error");
      row.IS_PAID = !row.IS_PAID; // revert checkbox
    }
  );
};


$scope.markAllUnpaid = function () {
  if (!$scope.List || $scope.List.length === 0) {
    alert("No records to mark.");
    return;
  }

  if (!confirm("Are you sure you want to mark all employees as UNPAID?")) {
    return;
  }

  const formData = $.param({
    type: 'markAllUnpaid',
    TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
    TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
    PAID_BY: $scope.userid
  });

  $http({
    method: 'post',
    url: url,
    data: formData,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
  }).then(
    function (response) {
      if (response.data.success) {
        $scope.messageSuccess("All salaries marked as UNPAID successfully.");
        $scope.getQuery(); // Refresh table data
      } else {
        $scope.messageFailure("Failed to mark all as unpaid.");
      }
    },
    function () {
      $scope.messageFailure("Error occurred while marking all as unpaid.");
    }
  );
};


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




  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
        TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
         type: "getQuery"
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        
        $scope.post.getQuery = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed during query");
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


  $scope.edit = function (id) {
   
    

    $scope.temp = {
    salaryid: id.SALARY_PROCESS_ID,
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
  
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
          salaryid: id.SALARY_PROCESS_ID,
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