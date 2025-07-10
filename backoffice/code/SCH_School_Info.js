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
  $scope.Page = "L&A";
  $scope.PageSub = "MEPITMANAGEMENT";
  $scope.PageSub1 = "MEPITMASTER";
  $scope.PageSub2 = "MEPCODEMASTER"

  $scope.temp.TEXT_LICENSE_START_DATE = new Date();
  
  $scope.temp.TEXT_LICENSE_END_DATE = '';

  var url = "code/SCH_School_info_code.php";

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
                 

          if (
            $scope.userrole != "ADMINISTRATOR" &&
            $scope.userrole != "SUPERADMIN"
          ) {
            // alert($scope.userrole);
            window.location.assign("dashboard.html#!/dashboard");
          } else {
            $scope.getQuery();
            $scope.getschoolname();
            
          }
        } else {
         
          $scope.logout();
        }
      },
      function (data, status, headers, config) {
        //console.log(data)
        console.log("Failed");
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
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("TEXT_SCHOOL_NAME", $scope.temp.TEXT_SCHOOL_NAME);
        formData.append("ddlLocation", $scope.temp.ddlLocation);

        formData.append("TEXT_ADDRESS", $scope.temp.TEXT_ADDRESS);
        formData.append("TEXT_CITY_ID", $scope.temp.TEXT_CITY_ID);
        formData.append("TEXT_STATE_ID", $scope.temp.TEXT_STATE_ID);
        formData.append("TEXT_COUNTRY_ID", $scope.temp.TEXT_COUNTRY_ID);
        formData.append("TEXT_PINCODE", $scope.temp.TEXT_PINCODE);
        formData.append("TEXT_CO_ORDINATOR", $scope.temp.TEXT_CO_ORDINATOR);
        formData.append("TEXT_MOBILE_NO", $scope.temp.TEXT_MOBILE_NO);
        formData.append("TEXT_EMAIL_ID", $scope.temp.TEXT_EMAIL_ID);
        formData.append("TEXT_LICENSE_START_DATE", $scope.temp.TEXT_LICENSE_START_DATE.toLocaleString('sv-SE'));
        formData.append("TEXT_LICENSE_END_DATE", $scope.temp.TEXT_LICENSE_END_DATE ? new Date($scope.temp.TEXT_LICENSE_END_DATE).toLocaleString('sv-SE'): '');  
        formData.append("txtremarks", $scope.temp.txtremarks);  
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);
        console.log(data.data);
        $scope.getQuery();
        $scope.getCountry();
        $scope.clear();
        document.getElementById("TEXT_SCHOOL_NAME").focus();
        
      } else {
        $scope.messageFailure(data.data.message);
        console.log(data.data);
      }
      $(".btn-save").removeAttr("disabled");
      $(".btn-save").text("SAVE");
      $(".btn-update").removeAttr("disabled");
      $(".btn-update").text("UPDATE");
    });
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


  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({ 
         TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,   
        type: "getQuery" }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getQuery = data.data.data;
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };


  $scope.getLocations = function (id) {
    $scope.post.getLocations = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
              type: "getLocations",
        
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getLocations = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getLocations();

$scope.getCity = function () {
    $scope.post.getCity = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_STATE_ID: $scope.temp.TEXT_STATE_ID,
        type: "getCity",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getCity = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
  };


$scope.getState = function () {
    $scope.post.getState = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
         TEXT_COUNTRY_ID: $scope.temp.TEXT_COUNTRY_ID,
        type: "getState",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getState = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
  };
  // $scope.getState();


$scope.getCountry = function () {
    $scope.post.getCountry = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getCountry",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getCountry = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getCountry();


$scope.edit = function (id) {

  $scope.temp = {
    pmid: id.SCHOOL_ID,
    TEXT_SCHOOL_NAME: id.SCHOOL_NAME,
    ddlLocation: id.LOC_ID.toString(),
    TEXT_ADDRESS: id.ADDRESS,
    TEXT_PINCODE: id.PINCODE,
    TEXT_CO_ORDINATOR: id.CO_ORDINATOR,
    TEXT_MOBILE_NO: id.PHONE_NO,
    TEXT_EMAIL_ID: id.EMAIL_ID,
    TEXT_LICENSE_START_DATE: id.LICENSE_START_DATE ? new Date(id.LICENSE_START_DATE) : '',
    TEXT_LICENSE_END_DATE: id.LICENSE_END_DATE ? new Date(id.LICENSE_END_DATE) : '',
    txtremarks: id.REMARKS
  };

  // Step 1: Set Country, then load States
  $scope.getCountry(); // Make sure this populates $scope.countryList
  $timeout(() => {
    $scope.temp.TEXT_COUNTRY_ID = id.COUNTRY_ID.toString();
    
    // Step 2: After country is set, load States
    $scope.getState();

    $timeout(() => {
      $scope.temp.TEXT_STATE_ID = id.STATE_ID.toString();

      // Step 3: After state is set, load Cities
      $scope.getCity();

      $timeout(() => {
        $scope.temp.TEXT_CITY_ID = id.CITY_ID.toString();
      }, 100);

    }, 100);

  }, 100);

  $scope.editMode = true;
  $scope.index = $scope.post.getQuery.indexOf(id);
};


   

  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("TEXT_SCHOOL_NAME").focus();
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
        data: $.param({ pmid: id.SCHOOL_ID, type: "delete" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        console.log(data.data);
        if (data.data.success) {
          var index = $scope.post.getQuery.indexOf(id);
          $scope.post.getQuery.splice(index, 1);
          // console.log(data.data.message)

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
        // console.log(data.data);
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