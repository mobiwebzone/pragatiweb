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
 

  var url = "code/SCH_Employee_Salary_Process_code.php";

  
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
             $scope.getQuery();
             $scope.getMonth();
            $scope.getSalaryComponents();
            $scope.loadSalaryComponents(); 
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

$scope.addTempComponent = function () {
  const compId = $scope.temp.newComponentId;
  const amount = parseFloat($scope.temp.newComponentAmount);

  if (!compId || isNaN(amount) || amount <= 0) {
    $scope.messageFailure("Please select a component and enter a valid amount.");
    return;
  }

  // Check for duplicates
  const exists = $scope.post.tempComponents.some(item => item.COMPONENT_ID === compId);
  if (exists) {
    $scope.messageFailure("This component is already added.");
    return;
  }

  // Send to server
  $http.post(url, $.param({
    type: "addTempComponent",
    EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
    MONTH_ID: $scope.temp.TEXT_MONTH_ID,
    COMPONENT_ID: compId,
    FIXED_AMOUNT: amount
  }), {
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
  }).then(function (res) {
    if (res.data.success) {
      // Get name from component master list
      const compObj = $scope.post.salaryComponents.find(c => c.COMPONENT_ID === compId);
      $scope.post.tempComponents.push({
        COMPONENT_ID: compId,
        COMPONENT_NAME: compObj ? compObj.COMPONENT_NAME : '',
        FIXED_AMOUNT: amount
      });

       $scope.getQuery(); 
      $scope.temp.newComponentId = null;
      $scope.temp.newComponentAmount = null;
      $scope.messageSuccess(res.data.message);
    } else {
      $scope.messageFailure(res.data.message);
    }
  });
};



// Delete from temp list
$scope.deleteTempComponent = function (item) {
  const index = $scope.post.tempComponents.indexOf(item);
  if (index !== -1) {
    $scope.post.tempComponents.splice(index, 1);
  }
};


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



$scope.addTempComponent = function () {
  if (!$scope.temp.newComponentId || !$scope.temp.newComponentAmount || !$scope.temp.newHeadMonthId) {
    $scope.messageFailure("Please select component, amount, and month.");
    return;
  }

  $http.post(url, $.param({
    type: "addTempComponent",
    EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
    MONTH_ID: $scope.temp.newHeadMonthId,
    COMPONENT_ID: $scope.temp.newComponentId,
    FIXED_AMOUNT: $scope.temp.newComponentAmount
  }), {
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
  }).then(function(res) {
    if (res.data.success) {
      $scope.messageSuccess(res.data.message);
      // Refresh if same month as selected
      if ($scope.temp.newHeadMonthId == $scope.temp.TEXT_MONTH_ID) {
        $scope.getQuery();
        $scope.loadTempComponents();
      }
      $scope.temp.newComponentId = null;
      $scope.temp.newComponentAmount = null;
      $scope.temp.newHeadMonthId = null;
    } else {
      $scope.messageFailure(res.data.message);
    }
  });
};



$scope.deleteTempComponent = function (item) {
  $http.post(url, $.param({
    type: "deleteTempComponent",
    EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
    MONTH_ID: $scope.temp.TEXT_MONTH_ID,
    COMPONENT_ID: item.COMPONENT_ID
  }), {
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
  }).then(function(res) {
    if (res.data.success) {
      $scope.messageSuccess(res.data.message);
      $scope.loadTempComponents();
      $scope.getQuery(); 
    } else {
      $scope.messageFailure(res.data.message);
    }
  });
};


  
 $scope.save = function () {
    if (!$scope.temp.TEXT_EMPLOYEE_ID || !$scope.temp.TEXT_MONTH_ID) {
      $scope.messageFailure("Select Employee and Month.");
      return;
    }

    $http.post(url, $.param({
      type: "checkIfAlreadyPaid",
      EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
      MONTH_ID: $scope.temp.TEXT_MONTH_ID
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      if (res.data.alreadyPaid) {
        $scope.messageFailure("Salary is already paid for this employee and month.");
      } else {
        actuallyProcessSalary();
      }
    });
  };

  function actuallyProcessSalary() {
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function () {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("salaryid", $scope.temp.salaryid);
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_EMPLOYEE_ID", $scope.temp.TEXT_EMPLOYEE_ID);
        formData.append("TEXT_MONTH_ID", $scope.temp.TEXT_MONTH_ID);
        formData.append("TEXT_PAYMENT_DATE", $scope.temp.TEXT_PAYMENT_DATE.toLocaleString("sv-SE"));
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data) {
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);
        $scope.getQuery();
        $scope.clear();
      } else {
        $scope.messageFailure(data.data.message);
      }
    });
  }

  $scope.processAllEmployees = function () {
    if (!$scope.temp.TEXT_SCHOOL_ID || !$scope.temp.TEXT_MONTH_ID || !$scope.temp.TEXT_PAYMENT_DATE) {
      $scope.messageFailure("Please select School, Month, and Payment Date.");
      return;
    }

    $http.post(url, $.param({
      type: "checkIfAlreadyPaidForSchool",
      SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      MONTH_ID: $scope.temp.TEXT_MONTH_ID
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      if (res.data.anyPaid) {
        $scope.messageFailure("Some salaries already paid. Aborting.");
      } else {
        actuallyProcessAll();
      }
    });
  };

  function actuallyProcessAll() {
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function () {
        var formData = new FormData();
        formData.append("type", "processAll");
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_MONTH_ID", $scope.temp.TEXT_MONTH_ID);
        formData.append("TEXT_PAYMENT_DATE", $scope.temp.TEXT_PAYMENT_DATE.toLocaleString("sv-SE"));
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data) {
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);
        $scope.getQuery();
      } else {
        $scope.messageFailure(data.data.message);
      }
    });
  }




  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
        TEXT_MONTH_ID: $scope.temp.TEXT_MONTH_ID,
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
$scope.getQuery();


$scope.onInputChange = function () {
  if ($scope.temp.TEXT_SCHOOL_ID && $scope.temp.TEXT_EMPLOYEE_ID && $scope.temp.TEXT_MONTH_ID) {
    $scope.getQuery();            // Populate main salary block
    $scope.loadTempComponents();  // Populate temporary head block
  }
};

$scope.onNewHeadMonthChange = function () {
  if ($scope.temp.TEXT_EMPLOYEE_ID && $scope.temp.newHeadMonthId) {
    // Only update the Add Head block (2nd block)
    $http.post(url, $.param({
      type: "getTempComponents",
      EMPLOYEE_ID: $scope.temp.TEXT_EMPLOYEE_ID,
      MONTH_ID: $scope.temp.newHeadMonthId
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (res) {
      $scope.post.tempComponents = res.data.success ? res.data.data : [];
    });
  }
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