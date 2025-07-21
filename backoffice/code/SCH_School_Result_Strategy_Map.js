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
  $scope.PageSub1 = "SUBJECTSMASTER";
  $scope.temp.TEXT_EXAM_DATE = new Date();
  
 

  var url = "code/SCH_School_Result_Strategy_Map_code.php";

  
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
            $scope.getResultStrategy();
            $scope.getClassFrom();
            $scope.getClassTo();
            $scope.getEffectiveYear();
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
                formData.append("mapid", $scope.temp.mapid);
                formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
                formData.append("TEXT_CLASS_CD_FROM", $scope.temp.TEXT_CLASS_CD_FROM);
                formData.append("TEXT_CLASS_CD_TO", $scope.temp.TEXT_CLASS_CD_TO);
                formData.append("TEXT_STRATEGY_ID", $scope.temp.TEXT_STRATEGY_ID);
                formData.append("TEXT_YEAR", $scope.temp.TEXT_YEAR);
                return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
          
      if (data.data.success) {
        
        $scope.messageSuccess(data.data.message);

        $scope.getQuery();
        $scope.clear();
        document.getElementById("TEXT_SCHOOL_ID").focus();
       
        // console.log(data.data);
      } else {
       
        console.log('Érror Ocurred! Please check');
        console.log(data.data);
        $scope.messageFailure(data.data.message);
        
      }
      $(".btn-save").removeAttr("disabled");
      $(".btn-save").text("SAVE");
      $(".btn-update").removeAttr("disabled");
      $(".btn-update").text("UPDATE");
    });
  };

  


  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
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



  $scope.getResultStrategy = function (callback) {
  $scope.post.getResultStrategy = [];

  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({
      type: "getResultStrategy",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      $scope.post.getResultStrategy = response.data.success ? response.data.data : [];
      $(".SpinBank").hide();
      if (callback) callback();  // ✅ Call the callback after data is loaded
    },
    function () {
      console.log("Failed to fetch Result Strategy");
    }
  );
};

  
$scope.getResultStrategy();

  
$scope.getClassFrom = function (callback) {
  $scope.post.getClassFrom = [];
  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      type: "getClassFrom",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      $scope.post.getClassFrom = response.data.success ? response.data.data : [];
      $(".SpinBank").hide();
      if (callback) callback();  // ✅ Trigger callback once ready
    },
    function () {
      console.log("Failed");
    }
  );
};

$scope.getClassTo = function (callback) {
  $scope.post.getClassTo = [];
  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({
      TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
      type: "getClassTo",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      $scope.post.getClassTo = response.data.success ? response.data.data : [];
      $(".SpinBank").hide();
      if (callback) callback();  // ✅ Trigger callback once ready
    },
    function () {
      console.log("Failed");
    }
  );
};


  $scope.getEffectiveYear = function (callback) {
  $scope.post.getEffectiveYear = [];

  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({
      type: "getEffectiveYear",
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (response) {
      $scope.post.getEffectiveYear = response.data.success ? response.data.data : [];
      $(".SpinBank").hide();
      if (callback) callback();  // ✅ Execute callback after data loads
    },
    function () {
      console.log("Failed to fetch Effective Year");
    }
  );
};

  $scope.getEffectiveYear();
 


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

  // Set school ID first
  $scope.temp = {
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString()
  };

  // Call dependent dropdowns and populate after they are fetched
  $scope.getClassFrom(function () {
    $scope.temp.TEXT_CLASS_CD_FROM = id.CLASS_CD_FROM.toString();
  });

  $scope.getClassTo(function () {
    $scope.temp.TEXT_CLASS_CD_TO = id.CLASS_CD_TO.toString();
  });

  // These do not depend on CLASS LOVs so can be set directly
  $scope.getResultStrategy(function () {
    $scope.temp.TEXT_STRATEGY_ID = id.STRATEGY_ID.toString();
  });

  $scope.getEffectiveYear(function () {
    $scope.temp.TEXT_YEAR = id.YEAR_ID.toString();
  });

  $scope.temp.mapid = id.MAP_ID;
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
          mapid: id.MAP_ID,
          type: "delete"
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