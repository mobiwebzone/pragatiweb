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
  $scope.temp.TEXT_DATE_OF_LEAVING = '';
  
 

  var url = "code/SCH_Student_Fees_Master_code.php";

  
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

    $http({
        method: "POST",
        url: url,
        processData: false,
        transformRequest: function (data) {
            var formData = new FormData();
            formData.append("type", 'save');
            formData.append("detailid", $scope.temp.detailid || 0);
            formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID || 0);
            formData.append("TEXT_CLASS_CD", $scope.temp.TEXT_CLASS_CD || 0);
            formData.append("TEXT_STUDENT_ID", $scope.temp.TEXT_STUDENT_ID || 0);
            formData.append("TEXT_FEES_FY_YEAR_CD", $scope.temp.TEXT_FEES_FY_YEAR_CD || 0);
            formData.append("TEXT_FEES_HEAD_CD", $scope.temp.TEXT_FEES_HEAD_CD || 0);
            formData.append("TEXT_FEES_DUE_INSERT", $scope.temp.TEXT_FEES_DUE_INSERT || 0);
            return formData;
        },
        data: $scope.temp,
        headers: { "Content-Type": undefined },
    }).then(function (response) {
        console.log("Raw response:", response);
        console.log("Response data:", response.data);

        if (response.data && response.data.success === true) {
            $scope.messageSuccess(response.data.message || "Record saved successfully.");
            $scope.getQuery();
            $scope.clear();
            document.getElementById("TEXT_SCHOOL_ID").focus();
        } else {
            console.log("Error occurred! Response data:", response.data);
            $scope.messageFailure(response.data && response.data.message ? response.data.message : "Unknown error occurred.");
        }
        $(".btn-save").removeAttr("disabled");
        $(".btn-save").text("SAVE");
        $(".btn-update").removeAttr("disabled");
        $(".btn-update").text("UPDATE");
    }, function (error) {
        console.log("HTTP error:", error);
        $scope.messageFailure("Failed to communicate with the server: " + (error.statusText || "Unknown error"));
        $(".btn-save").removeAttr("disabled");
        $(".btn-save").text("SAVE");
        $(".btn-update").removeAttr("disabled");
        $(".btn-update").text("UPDATE");
    });
};

$scope.updateRowAmountDue = function (row) {
  $http({
    method: "POST",
    url: url,
    processData: false,
    transformRequest: function (data) {
      var formData = new FormData();
      formData.append("type", 'updateRowAmountDue');
      formData.append("DETAIL_ID", row.DETAIL_ID);
      formData.append("STUDENT_ID", row.STUDENT_ID);
      formData.append("SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
      formData.append("CLASS_CD", $scope.temp.TEXT_CLASS_CD);
      formData.append("FEES_YEAR_CD", row.FEES_YEAR_CD);
      formData.append("AMOUNT_DUE", row.AMOUNT_DUE);
      formData.append("FEES_HEAD_CD", row.FEES_HEAD_CD);
      return formData;
    },
    data: row,
    headers: { "Content-Type": undefined },
  }).then(function (response) {
    if (response.data.success) {
      $scope.messageSuccess("Updated successfully.");
      $scope.getQuery(); // refresh
    } else {
      $scope.messageFailure(response.data.message || "Update failed.");
    }
  });
};


$scope.getFeesDue = function () {
  if (!$scope.temp.TEXT_SCHOOL_ID || !$scope.temp.TEXT_CLASS_CD || !$scope.temp.TEXT_STUDENT_ID || !$scope.temp.TEXT_FEES_FY_YEAR_CD) {
    return;
  }

  $http({
    method: "post",
    url: url,
    data: $.param({
      type: "getFeesDue",
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
      TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
      TEXT_FEES_FY_YEAR_CD: $scope.temp.TEXT_FEES_FY_YEAR_CD
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
  }).then(
    function (response) {
      if (response.data.success) {
        $scope.temp.TEXT_FEES_DUE = response.data.FEE_DUE;
        $scope.temp.TEXT_FEES_PAID = response.data.FEE_PAID;
        $scope.temp.TEXT_FEES_BALANCE = response.data.FEE_BALANCE;
      } else {
        $scope.temp.TEXT_FEES_DUE = 0;
        $scope.temp.TEXT_FEES_PAID = 0;
        $scope.temp.TEXT_FEES_BALANCE = 0;
      }
    },
    function () {
      console.log("Failed to fetch fees due");
    }
  );
};




  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_FEES_FY_YEAR_CD: $scope.temp.TEXT_FEES_FY_YEAR_CD,
        TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
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


$scope.getFeesHead = function () {
    $scope.post.getFeesHead = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getFeesHead",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getFeesHead = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getFeesHead();



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
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getFinancialYear();
  



$scope.getStudent = function (callback) {
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
  }).then(
    function (response) {
      $scope.post.getStudent = response.data.success ? response.data.data : [];
      $(".SpinBank").hide();

      if (callback) callback(); // invoke the callback if provided
    },
    function () {
      $(".SpinBank").hide();
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
  // $scope.getClass();


 
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
  document.getElementById("TEXT_SCHOOL_ID").focus();

  // Set basic fields first
  $scope.temp = {
    feesid: id.DETAIL_ID,
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
    TEXT_CLASS_CD: id.CLASS_CD.toString(),
    TEXT_FEES_FY_YEAR_CD: id.FEES_FY_YEAR_CD.toString(),
    TEXT_FEES_DUE: id.FEES_DUE,
    txtremarks: id.REMARKS
  };

  // Call getStudent AFTER setting school and class, then set student ID when list is loaded
  $scope.getStudent(function () {
    $scope.temp.TEXT_STUDENT_ID = id.STUDENT_ID.toString();
  });

 

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
        schoolid : id.SCHOOL_ID,
        classcd  : id.CLASS_CD,
        feesheadcd : id.FEES_HEAD_CD,
        feesid: id.DETAIL_ID,
        studentid:id.STUDENT_ID,
        amountdue: id.AMOUNT_DUE,
        feeyearcd : id.FEES_YEAR_CD,
        type: "delete"
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
      
        console.log(data.data.message);
        
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