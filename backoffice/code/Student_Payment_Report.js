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
  $scope.PageSub = "FEESPAYMENT";
  $scope.PageSub1 = "SCHFEESPAYMENT";
 
  var url = "code/Student_Payment_Report.php";

  
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

 
  // $scope.save = function () {
  //   $(".btn-save").attr("disabled", "disabled");
  //   $(".btn-update").attr("disabled", "disabled");
  

  //   $http({
  //     method: "POST",
  //     url: url,
  //     processData: false,
  //       transformRequest: function (data) {
  //       var formData = new FormData();
  //       formData.append("type", 'save');
  //               formData.append("feespaymentid", $scope.temp.feespaymentid);
  //               formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
  //               formData.append("TEXT_CLASS_CD", $scope.temp.TEXT_CLASS_CD);
  //               formData.append("TEXT_STUDENT_ID", $scope.temp.TEXT_STUDENT_ID);
  //               formData.append("TEXT_FEES_FY_YEAR_CD", $scope.temp.TEXT_FEES_FY_YEAR_CD);
  //               formData.append("TEXT_FEES_PAID", $scope.temp.TEXT_FEES_PAID);
  //               formData.append("TEXT_PAYMENT_DATE", $scope.temp.TEXT_PAYMENT_DATE.toLocaleString('sv-SE'));
  //               formData.append("txtremarks", $scope.temp.txtremarks);
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
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD : $scope.temp.TEXT_CLASS_CD,
        TEXT_FEES_FY_YEAR_CD: $scope.temp.TEXT_FEES_FY_YEAR_CD,
        TEXT_STUDENT_ID: $scope.temp.TEXT_STUDENT_ID,
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


   $scope.getStudent = function () {
    $scope.post.getStudent = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID : $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_FEES_FY_YEAR_CD: $scope.temp.TEXT_FEES_FY_YEAR_CD,
        type: "getStudent",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getStudent = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };


//  $scope.getFeesDue = function () {
//     $scope.post.getFeesDue = [];

//     $(".SpinBank").show();
//     $http({
//       method: "post",
//       url: url,
//       data: $.param({
//         TEXT_SCHOOL_ID        : $scope.temp.TEXT_SCHOOL_ID,
//         TEXT_CLASS_CD         : $scope.temp.TEXT_CLASS_CD,
//         TEXT_STUDENT_ID       : $scope.temp.TEXT_STUDENT_ID,
//         TEXT_FEES_FY_YEAR_CD  : $scope.temp.TEXT_FEES_FY_YEAR_CD,
//         type: "getFeesDue",
//       }),
//       headers: { "Content-Type": "application/x-www-form-urlencoded" },
//     }).then(
//       function (data, status, headers, config) {
//         console.log(data.data);
//         $scope.post.getFeesDue = data.data.success ? data.data.data : [];
//         $(".SpinBank").hide();
//       },
//       function (data, status, headers, config) {
//         // console.log("Failed");
//       }
//     );
//   };
 

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
  


  // $scope.getStudent = function () {
  //   $scope.post.getStudent = [];

  //   $(".SpinBank").show();
  //   $http({
  //     method: "post",
  //     url: url,
  //     data: $.param({
  //       TEXT_SCHOOL_ID : $scope.temp.TEXT_SCHOOL_ID,
  //       TEXT_CLASS_CD:  $scope.temp.TEXT_CLASS_CD,
  //       type: "getStudent",
  //     }),
  //     headers: { "Content-Type": "application/x-www-form-urlencoded" },
  //   }).then(
  //     function (data, status, headers, config) {
  //       // console.log(data.data);
  //       $scope.post.getStudent = data.data.success ? data.data.data : [];
  //       $(".SpinBank").hide();
  //     },
  //     function (data, status, headers, config) {
  //       // console.log("Failed");
  //     }
  //   );
  // };


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


  // $scope.edit = function (id) {
   
  //   document.getElementById("TEXT_SCHOOL_ID").focus();

  //   $scope.temp = {
  //   feespaymentid: id.FEES_PAYMENT_ID,
  //   TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
  //   TEXT_CLASS_CD: id.CLASS_CD.toString(),
  //   TEXT_STUDENT_ID: id.STUDENT_ID.toString(),
  //   TEXT_FEES_FY_YEAR_CD: id.FEES_FY_YEAR_CD.toString(),
  //   TEXT_PAYMENT_DATE: id.PAYMENT_DATE ? new Date(id.PAYMENT_DATE) : '',
  //   TEXT_FEES_PAID: id.FEES_PAID,
  //   TEXT_STUDENT_ID : id.STUDENT_ID.toString(),
  //   txtremarks: id.REMARKS
  //   };

    

  //   $scope.editMode = true;
  //   $scope.index = $scope.post.getQuery.indexOf(id);
  // };

  $scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_ID").focus();
    $scope.temp = {};
    $scope.editMode = false;
  };

  // $scope.delete = function (id) {
  //   var r = confirm("Are you sure want to delete this record!");
  //   if (r == true) {
  //     $http({
  //       method: "post",
  //       url: url,
  //       data: $.param({
  //         feespaymentid: id.FEES_PAYMENT_ID,
  //         TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
  //         TEXT_CLASS_CD: id.CLASS_CD.toString(),
  //         TEXT_STUDENT_ID: id.STUDENT_ID.toString(),
  //         TEXT_FEES_PAID: id.FEES_PAID,
  //         TEXT_FEES_FY_YEAR_CD: id.FEES_FY_YEAR_CD.toString(),
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