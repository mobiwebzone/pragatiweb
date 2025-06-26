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
  $scope.Page = "QUESTION_SECTION";
  $scope.PageSub = "ST_TEST_VIEW_ANS";

  // ========= PEGINATION =============
  $scope.serial = 1;
  $scope.indexCount = function (newPageNumber) {
    $scope.serial = newPageNumber * 25 - 24;
  };
  // ========= PEGINATION =============

  var url = "code/Student_Test_View_Answer_code.php";

  // =============== Check Session =============
  $scope.init = function () {
    // Check Session
    $http({
      method: "post",
      url: "code/checkSession.php",
      data: $.param({ type: "checkSession" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);

        if (data.data.success) {
          $scope.post.user = data.data.data;
          $scope.userid = data.data.userid;
          $scope.userFName = data.data.userFName;
          $scope.userLName = data.data.userLName;
          $scope.userrole = data.data.userrole;
          $scope.USER_LOCATION = data.data.LOCATION;
          $scope.locid = data.data.locid;
          // window.location.assign("dashboard.html");

          // $scope.getTestMaster();

          $scope.getPlans();
          //Changes for addition of location
          if ($scope.userrole != "TSEC_USER") {
            $scope.getLocations();
          } else {
            window.location.assign("dashboard.html#!/dashboard");
          }
        } else {
          // window.location.assign('index.html#!/login')
          // alert
          $scope.logout();
        }
      },
      function (data, status, headers, config) {
        //console.log(data)
        console.log("Failed");
      }
    );
  };
  // =============== Check Session =============
  
    
//Changes for addition of location

    $scope.getLocations = function () {
    $http({
      method: "post",
      url: "code/Users_code.php",
      data: $.param({ type: "getLocations" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getLocations = data.data.data;
        $scope.temp.ddlLocation = $scope.post.getLocations ? $scope.locid.toString() : "";
        // if ($scope.temp.ddlLocation > 0) $scope.getStudentTest();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };

    
    
  /* ========== GET PLANS =========== */
  $scope.getPlans = function () {
    $(".spinPlans").show();
    // $scope.post.getTestSection = [];
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getPlans" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          $scope.post.getPlans = data.data.data;
        } else {
          console.info(data.data.message);
        }
        $(".spinPlans").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getPlans(); --INIT
  /* ========== GET PLANS =========== */

  /*============ GET STUDENT BY PLAN =============*/
  $scope.getStudentByPlan = function () {
      $(".spinStudent").show();
       $scope.post.getStudentByPlan = "";
    $scope.post.getTestByRegid = [];
    $scope.post.getTestSection = [];
    $scope.post.getStudentAnswer = [];
    $scope.post.getTestSectionAttempts = [];
    $scope.SectionName = "";
    $scope.ATTEMPT = 0;
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getStudentByPlan",
        ddlPlan: $scope.temp.ddlPlan,
        ddlLocation: $scope.temp.ddlLocation,
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          $scope.post.getStudentByPlan = data.data.data;
        } else {
          console.info(data.data.message);
        }
        $(".spinStudent").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getStudentByPlan();
  /*============ GET STUDENT BY PLAN =============*/

  /*============ GET STUDENT TEST BY REGID =============*/
  $scope.getTestByRegid = function () {
    $(".spinTest").show();
    $scope.temp.ddlTest = "";
    $scope.post.getTestSection = [];
    $scope.post.getStudentAnswer = [];
    $scope.post.getTestSectionAttempts = [];
    $scope.SectionName = "";
    $scope.ATTEMPT = 0;
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getTestByRegid",
        ddlStudent: $scope.temp.ddlStudent,
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getTestByRegid = data.data.data;
        $(".spinTest").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getTestByRegid(); --INIT
  /*============ GET STUDENT TEST BY REGID =============*/

  /* ========== GET TEST SECTION BY TEST =========== */
  $scope.getTestSection = function () {
    $scope.post.getTestSection = [];
    $scope.post.getStudentAnswer = [];
    $scope.post.getTestSectionAttempts = [];
    $scope.SectionName = "";
    $scope.ATTEMPT = 0;
    $(".spinTestSec").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getTestSection",
        ddlTest: $scope.temp.ddlTest,
        ddlStudent: $scope.temp.ddlStudent,
        txtTestDate: (!$scope.temp.txtTestDate || $scope.temp.txtTestDate == "") ? "" : $scope.temp.txtTestDate.toLocaleString("sv-SE"),
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          $scope.post.getTestSection = data.data.data;
        } else {
          $scope.post.getTestSection = [];
          // console.info(data.data.message);
        }
        $(".spinTestSec").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getTestSection();
  /* ========== GET TEST SECTION BY TEST =========== */

  /* ========== GET TEST SECTION ATTEMPTS =========== */
  $scope.SectionName = "";
  $scope.TESTID = 0;
  $scope.TSECID = 0;
  $scope.REGID = 0;
  $scope.TSAttID = [];
  $scope.getTestSectionAttempts = function (id) {
    $scope.SectionName = id.SECTION;
    $scope.TSAttID = id;
    $scope.TESTID = id.TESTID;
    $scope.TSECID = id.TSECID;
    $scope.REGID = id.REGID;
    $scope.post.getStudentAnswer = [];
    $scope.STID = 0;

    $scope.post.getTestSectionAttempts = [];
    $scope.ATTEMPT = 0;
    $(".spinTestAns").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getTestSectionAttempts",
        TESTID: $scope.temp.ddlTest,
        TSECID: id.TSECID,
        REGID: $scope.temp.ddlStudent,
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          $scope.post.getTestSectionAttempts = data.data.data;
          $scope.getStudentAnswer(data.data.data[0]);
        } else {
          $scope.post.getTestSectionAttempts = [];
          console.info(data.data.message);
        }
        $(".spinTestAns").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getTestSectionAttempts();
  /* ========== GET TEST SECTION ATTEMPTS =========== */

  /* ========== GET STUDENT ANSWERS =========== */
  $scope.myid = [];
  $scope.ATTEMPT = 0;
  $scope.STID = 0;
  $scope.getStudentAnswer = function (id) {
    $(".btnDelAtt").attr("disabled", "disabled");
    $(".spinTestAns").show();
    $scope.myid = id;
    $scope.ATTEMPT = id.ATTEMPT;
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getStudentAnswer",
        TESTID: $scope.temp.ddlTest,
        TSECID: $scope.TSECID,
        REGID: $scope.temp.ddlStudent,
        ATTEMPT: id.ATTEMPT,
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        if (data.data.success) {
          $scope.post.getStudentAnswer = data.data.data;
          $scope.STID = data.data.data[0]["STID"];
        } else {
          $scope.post.getStudentAnswer = [];
          $scope.STID = 0;
          console.info(data.data.message);
        }
        $(".spinTestAns").hide();
        $(".btnDelAtt").removeAttr("disabled");
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getStudentAnswer();
  /* ========== GET STUDENT ANSWERS =========== */

  /* ============ UPDATE ANS MODAL ============= */
  $scope.updateAnswerModal = (id) => {
    $scope.temp.ddlAns = "";
    $scope.temp.txtAns = "";
    $scope.RID = id.RID;
    $scope.QUESTION = id.QUESTION;
    $scope.QUETYPE = id.QUETYPE;
    $scope.CORRECTANS = id.CORRECTANS.trim();
    $scope.STUDENTANS = id.STUDENTANS != "" ? id.STUDENTANS.trim() : "-";
    if ($scope.QUETYPE === "MCQ") {
      // console.log(id.QUE_OPTIONS);
      $scope.QUE_OPTIONS = id.QUE_OPTIONS.split(",").map(item => item.trim());
      $scope.temp.ddlAns = !id.STUDENTANS ? id.STUDENTANS.trim() : id.STUDENTANS;
    } else if ($scope.QUETYPE === "TYPE-IN") {
      $scope.temp.txtAns = !id.STUDENTANS ? id.STUDENTANS.trim() : id.STUDENTANS.trim();
    }
  };
  /* ============ UPDATE AND MODAL ============= */
  /* ============ CLEAR MODAL DATA ============= */
  $scope.clearModalData = () => {
    $("#AnsModal").trigger({ type: "click" });
    $scope.temp.ddlAns = "";
    $scope.temp.txtAns = "";
    $scope.RID = "";
    $scope.QUESTION = "";
    $scope.QUETYPE = "";
    $scope.CORRECTANS = "";
    $scope.STUDENTANS = "";
    $scope.QUE_OPTIONS = "";
  };
  /* ============ CLEAR MODAL DATA ============= */

  // =========== UPDATE ANSWER ==============
  $scope.UpdateAnswerFinal = function () {
    $(".btnupdate").attr("disabled", "disabled");
    $(".btnupdate").text("Updating...");
    $(".spinUpdateAns").show();

    // console.log($scope.myid);
    // alert($scope.temp.ddlCollege);
    $scope.ansFinal =
      $scope.QUETYPE === "MCQ" ? $scope.temp.ddlAns : $scope.temp.txtAns;
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "UpdateAnswerFinal");
        formData.append("RID", $scope.RID);
        formData.append("ansFinal", $scope.ansFinal);
        formData.append("CORRECTANS", $scope.CORRECTANS);
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      // console.log(data.data);
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);
        $scope.getStudentAnswer($scope.myid);
        $("#AnsModal").trigger({ type: "click" });
        $scope.clearModalData();
      } else {
        $scope.messageFailure(data.data.message);
        // console.log(data.data)
      }
      $(".spinUpdateAns").hide();
      $(".btnupdate").removeAttr("disabled");
      $(".btnupdate").text("UPDATE");
    });
  };
  // =========== UPDATE ANSWER ==============

  /* ============ Clear Form =========== */
  $scope.clearForm = function () {
    $("#ddlPlan").focus();
    $scope.temp = {};
    $scope.editMode = false;
    $scope.post.getTestSection = [];
    $scope.SectionName = "";
    $scope.post.getTestByRegid = [];
    $scope.post.getStudentByPlan = [];
    $scope.post.getStudentAnswer = [];
    $scope.post.getTestSectionAttempts = [];
    $scope.clearModalData();

    $scope.SectionName = "";
    $scope.TESTID = 0;
    $scope.TSECID = 0;
    $scope.REGID = 0;
    $scope.TSAttID = [];
    $scope.myid = [];
    $scope.ATTEMPT = 0;
    $scope.STID = 0;
  };
  /* ============ Clear Form =========== */

  /* ============ DELETE ATTEMPT =========== */
  $scope.DeleteAttempt = function () {
    // console.log($scope.STID);
    var r = confirm("Are you sure want to delete this record!");
    if (r == true) {
      $http({
        method: "post",
        url: url,
        data: $.param({ STID: $scope.STID, type: "DeleteAttempt" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        // console.log(data.data)
        if (data.data.success) {
          $scope.getTestSectionAttempts($scope.TSAttID);

          console.log(data.data.message);

          $scope.messageSuccess(data.data.message);
        } else {
          $scope.messageFailure(data.data.message);
        }
      });
    }
  };
  /* ============ DELETE ATTEMPT =========== */

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
  /* ========== Logout =========== */

  /* ========== MESSAGE =========== */
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
  /* ========== MESSAGE =========== */
});