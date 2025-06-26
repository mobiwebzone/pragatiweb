$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize","textAngular"]);
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
// taOptions.toolbar = [
//     ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
//     ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
//     ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent'],
//     ['html', 'insertImage','insertLink', 'insertVideo', 'wordcount', 'charcount']
// ];
$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout,taOptions) {
  $scope.post = {};
  $scope.temp = {};
  $scope.editMode = false;
  $scope.Page = "SETTING";
  $scope.PageSub = "PP_COOKIE";
  // ========= TEXT EDITOR =========
  taOptions.toolbar = [
    ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre', 'quote'],
    ['bold', 'italics', 'underline', 'strikeThrough', 'ul', 'ol', 'redo', 'undo', 'clear'],
    ['justifyLeft', 'justifyCenter', 'justifyRight', 'indent', 'outdent']
  ];
  // ========= TEXT EDITOR =========

  var url = "code/Privacy_Cookie_Policy_Master.php";




  // GET DATA
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
          $scope.LOCID = data.data.locid;

          if (
            $scope.userrole != "ADMINISTRATOR" &&
            $scope.userrole != "SUPERADMIN"
          ) {
            window.location.assign("dashboard.html#!/dashboard");
          } else {
            // $scope.getTerm();
            // $scope.getLocations();
          }
        } else {
          // window.location.assign('index.html#!/login')
          $scope.logout();
        }
      },
      function (data, status, headers, config) {
        //console.log(data)
        console.log("Failed");
      }
    );
  };

  $scope.save = function () {
    $(".btn-update").attr("disabled", true).text("Updating...");
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pid", $scope.temp.pid);
        formData.append("ddlPolicyType", $scope.temp.ddlPolicyType);
        formData.append("txtPolicy", $scope.temp.txtPolicy);
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      // console.log(data.data);
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);
        $scope.getPolicy();
        document.getElementById("txtPolicy").focus();
      } else {
        $scope.messageFailure(data.data.message);
        // console.log(data.data)
      }
      $(".btn-update").attr("disabled",false).text("UPDATE");
    });
  };

  /* ========== GET POLICY =========== */
  $scope.getPolicy = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({ type: "getPolicy","ddlPolicyType":$scope.temp.ddlPolicyType}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.temp.txtPolicy = data.data.success ? data.data.data['POLICY'] : '';
        $scope.temp.pid = data.data.success ? data.data.data['PID'] : 0;
        // $scope.post.getPolicys = data.data.success ? data.data.data : [];
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
      );
    };
    // $scope.getPolicy(); --INIT
    /* ========== GET POLICY =========== */


    
    /* ========== GET Location =========== */
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
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  // $scope.getLocations(); --INIT



  /* ============ Clear Form =========== */
  $scope.clearForm = function () {
    document.getElementById("txtPolicy").focus();
    //document.getElementById("txtTerm").focus();
      $scope.temp = {};
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

/* ========== MESSAGE =========== */
$scope.messageSuccess = function (msg) {
  jQuery('#myToast > .toast-body > samp').html(msg);
  jQuery('#myToast').toast('show');
  jQuery('#myToast > .toast-header').removeClass('bg-danger').addClass('bg-success');
  jQuery('#myToastMain').animate({bottom: '50px'});
  setTimeout(function() {
      jQuery('#myToastMain').animate({bottom: "-80px"});
  }, 5000 );
}

$scope.messageFailure = function (msg) {
  jQuery('#myToast > .toast-body > samp').html(msg);
  jQuery('#myToast').toast('show');
  jQuery('#myToast > .toast-header').removeClass('bg-success').addClass('bg-danger');
  jQuery('#myToastMain').animate({bottom: '50px'});
  setTimeout(function() {
      jQuery('#myToastMain').animate({bottom: "-80px"});
  }, 5000 );
}
/* ========== MESSAGE =========== */
});