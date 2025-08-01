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
  $scope.Page = "TEACHER";
  $scope.PageSub = "EDUCATION";
  $scope.PageSub1 = "SCHREGISTRATION";
 
  $scope.temp.TEXT_DATE_OF_JOINING = new Date();
  $scope.temp.TEXT_DATE_OF_LEAVING = new Date();
  $scope.temp.TEXT_PAYMENT_DATE = new Date();
 

  var url = "code/Teacher_Payroll_Summary_Report.php";

  
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

  /* ========== Save Paymode =========== */
  // $scope.save = function () {
  //   $(".btn-save").attr("disabled", "disabled");
  //   // $(".btn-save").text('Saving...');
  //   $(".btn-update").attr("disabled", "disabled");
  //   // $(".btn-update").text('Updating...');
  

  //   $http({
  //     method: "POST",
  //     url: url,
  //     processData: false,
  //       transformRequest: function (data) {
  //       var formData = new FormData();
  //       formData.append("type", 'save');
  //               formData.append("feesid", $scope.temp.feesid);
  //               formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
  //               formData.append("TEXT_TEACHER_ID", $scope.temp.TEXT_TEACHER_ID);
  //               formData.append("TEXT_FY_YEAR_CD", $scope.temp.TEXT_FY_YEAR_CD);
  //               formData.append("TEXT_MONTH_CD", $scope.temp.TEXT_MONTH_CD);
  //               formData.append("TEXT_SALARY_DUE", $scope.temp.TEXT_SALARY_DUE);
  //               formData.append("TEXT_SALARY_DEDUCTED", $scope.temp.TEXT_SALARY_DEDUCTED);  
  //               formData.append("TEXT_SALARY_PAID", $scope.temp.TEXT_SALARY_PAID);
  //               formData.append("TEXT_DEDUCTION_REASON", $scope.temp.TEXT_DEDUCTION_REASON);
  //               formData.append("txtremarks", $scope.temp.txtremarks);
  //               formData.append("TEXT_PAYMENT_MODE_CD", $scope.temp.TEXT_PAYMENT_MODE_CD);
  //               formData.append("TEXT_PAYMENT_DATE", $scope.temp.TEXT_PAYMENT_DATE.toLocaleString('sv-SE'));
  //               return formData;
  //     },
  //     data: $scope.temp,
  //     headers: { "Content-Type": undefined },
  //   }).then(function (data, status, headers, config) {
          
  //     if (data.data.success) {
        
  //       $scope.messageSuccess(data.data.message);

  //       $scope.getQuery();
  //       $scope.clear();
  //       document.getElementById("TEXT_SCHOOL_ID").focus();
       
  //       console.log(data.data);
  //     } else {
       
  //       console.log('Érror Ocurred! Please check');
  //       console.log(data.data);
  //       $scope.messageFailure(data.data.message);
        
  //     }
  //     $(".btn-save").removeAttr("disabled");
  //     $(".btn-save").text("SAVE");
  //     $(".btn-update").removeAttr("disabled");
  //     $(".btn-update").text("UPDATE");
  //   });
  // };

  


  $scope.getQuery = function () {
    
    // console.log("Sending request with the following data:", {
    //     TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
    //     TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
    //     TEXT_TEACHER_ID: $scope.temp.TEXT_TEACHER_ID,
    //     type: "getQuery"
    // });
    
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
        TEXT_TEACHER_ID: $scope.temp.TEXT_TEACHER_ID,
        TEXT_MONTH_CD: $scope.temp.TEXT_MONTH_CD,
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


// $scope.getGrossSalary = function () {
//     $scope.post.getGrossSalary = [];

//     $(".SpinBank").show();
//     $http({
//       method: "post",
//       url: url,
//       data: $.param({
//         TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
//         TEXT_FY_YEAR_CD: $scope.temp.TEXT_FY_YEAR_CD,
//         TEXT_TEACHER_ID: $scope.temp.TEXT_TEACHER_ID,
//         type: "getGrossSalary",
//       }),
//       headers: { "Content-Type": "application/x-www-form-urlencoded" },
//     }).then(
//       function (data, status, headers, config) {
        
//         $scope.post.getGrossSalary = data.data.success ? data.data.data : [];
//         $(".SpinBank").hide();
//       },
//       function (data, status, headers, config) {
       
//       }
//     );
//   };



// $scope.getPaymentMode = function () {
//     $scope.post.getPaymentMode = [];

//     $(".SpinBank").show();
//     $http({
//       method: "post",
//       url: url,
//       data: $.param({
//         type: "getPaymentMode",
//       }),
//       headers: { "Content-Type": "application/x-www-form-urlencoded" },
//     }).then(
//       function (data, status, headers, config) {
        
//         $scope.post.getPaymentMode = data.data.success ? data.data.data : [];
//         $(".SpinBank").hide();
//       },
//       function (data, status, headers, config) {
       
//       }
//     );
//   };
//   $scope.getPaymentMode();


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
  $scope.getTeacher();


 
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
  //   feesid: id.TEACHER_SALARY_ID,
  //   TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
  //   TEXT_TEACHER_ID: id.TEACHER_ID.toString(),
  //   TEXT_FY_YEAR_CD: id.FY_YEAR_CD.toString(),
  //   // TEXT_MONTH_CD  : id.MONTH_CD.toString(),
  //   TEXT_SALARY_DUE: id.SALARY_DUE,
  //   TEXT_SALARY_DEDUCTED: id.SALARY_DEDUCTED,
  //   TEXT_SALARY_PAID: id.SALARY_PAID,
  //   TEXT_DEDUCTION_REASON: id.DEDUCTION_REASON,
  //   TEXT_PAYMENT_DATE: id.PAYMENT_DATE ? new Date(id.PAYMENT_DATE) : '',
  //   TEXT_PAYMENT_MODE_CD: id.PAYMENT_MODE_CD.toString(),
  //     txtremarks: id.REMARKS
    
  //   };

    

  //   $scope.editMode = true;
  //   $scope.index = $scope.post.getQuery.indexOf(id);
  // };

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_ID").focus();
    $scope.temp = {};
    $scope.editMode = false;
  };

  /* ========== DELETE =========== */
  // $scope.delete = function (id) {
  //   var r = confirm("Are you sure want to delete this record!");
  //   if (r == true) {
  //     $http({
  //       method: "post",
  //       url: url,
  //       data: $.param({
  //         feesid: id.TEACHER_SALARY_ID,
  //         type: "delete"
  //       }),
  //       headers: { "Content-Type": "application/x-www-form-urlencoded" },
  //     }).then(function (data, status, headers, config) {
      

  //       if (data.data.success) {
  //         var index = $scope.post.getQuery.indexOf(id);
  //         $scope.post.getQuery.splice(index, 1);
         
  //         $scope.messageSuccess(data.data.message);
  //       } else {
  //         $scope.messageFailure(data.data.message);
  //       }
  //     });
  //   }
  // };

  // /* ========== Logout =========== */
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