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
  $scope.PageSub = "REGISTRATION";
  $scope.PageSub1 = "SCHREGISTRATION";
  $scope.temp.TEXT_DATE_OF_JOINING = new Date();
  $scope.temp.TEXT_DOB = new Date();
  $scope.temp.TEXT_DATE_OF_LEAVING = '';
 

  var url = "code/SCH_Employee_Master_code.php";

  
$scope.visibleFields = [];

  // Load visible fields based on school ID
  $scope.loadVisibleFields = function () {
    const schoolId = $scope.temp.TEXT_SCHOOL_ID;
    // console.log("Loading visible fields for school ID:", schoolId);

    $http.post(url, $.param({
      type: "getVisibleFields",
      TEXT_SCHOOL_ID: schoolId
    }), {
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(function (response) {
      if (response.data.success && Array.isArray(response.data.visibleFields)) {
        $scope.visibleFields = response.data.visibleFields;
        // console.log("Visible fields loaded:", $scope.visibleFields);
      } else {
        console.warn("Visible fields not loaded properly.", response.data);
      }
    }, function (error) {
      console.error("Error loading visible fields", error);
    });
  };

  // Visibility check function
  $scope.isFieldVisible = function (fieldKey) {
    // console.log("Checking visibility for", fieldKey, "in", $scope.visibleFields);
    return $scope.visibleFields.includes(fieldKey);
  };

  // Watch for changes in selected school ID
  $scope.$watch('temp.TEXT_SCHOOL_ID', function (newVal) {
    if (newVal) {
      $scope.loadVisibleFields();
    }
  });





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

  /* ========== Save Paymode =========== */
  $scope.save = function () {
    $(".btn-save").attr("disabled", "disabled");
     $(".btn-save").text('Saving...');
    $(".btn-update").attr("disabled", "disabled");
    $(".btn-update").text('Updating...');
  

    $http({
      method: "POST",
      url: url,
      processData: false,
        transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_EMPLOYEE_NAME", $scope.temp.TEXT_EMPLOYEE_NAME);
         formData.append("TEXT_EMPLOYEE_CODE", $scope.temp.TEXT_EMPLOYEE_CODE);
        formData.append("TEXT_DATE_OF_JOINING", $scope.temp.TEXT_DATE_OF_JOINING.toLocaleString('sv-SE'));  
        formData.append("TEXT_FATHER_HUSBAND_NAME", $scope.temp.TEXT_FATHER_HUSBAND_NAME);
        formData.append("TEXT_DOB", $scope.temp.TEXT_DOB.toLocaleString('sv-SE'));  
        formData.append("TEXT_GENDER_CD", $scope.temp.TEXT_GENDER_CD);
        formData.append("TEXT_NATIONALITY_CD", $scope.temp.TEXT_NATIONALITY_CD);
        formData.append("TEXT_ADDRESS1", $scope.temp.TEXT_ADDRESS1);
        formData.append("TEXT_ADDRESS2", $scope.temp.TEXT_ADDRESS2);
        formData.append("TEXT_CITY_ID", $scope.temp.TEXT_CITY_ID);
        formData.append("TEXT_STATE_ID", $scope.temp.TEXT_STATE_ID);
        formData.append("TEXT_COUNTRY_ID", $scope.temp.TEXT_COUNTRY_ID);
        formData.append("TEXT_ZIP_CD", $scope.temp.TEXT_ZIP_CD);
        formData.append("TEXT_EMPLOYEE_MOBILE_NO", $scope.temp.TEXT_EMPLOYEE_MOBILE_NO);
        formData.append("TEXT_EMPLOYEE_EMAIL_ID", $scope.temp.TEXT_EMPLOYEE_EMAIL_ID);
      
        formData.append("TEXT_DATE_OF_LEAVING", $scope.temp.TEXT_DATE_OF_LEAVING ? new Date($scope.temp.TEXT_DATE_OF_LEAVING).toLocaleString('sv-SE'): '');  
        formData.append("TEXT_UID", $scope.temp.TEXT_UID);
        formData.append("TEXT_BANK_CD", $scope.temp.TEXT_BANK_CD);
        formData.append("TEXT_BANK_BRANCH", $scope.temp.TEXT_BANK_BRANCH);
        formData.append("TEXT_BANK_ACCOUNT_NO", $scope.temp.TEXT_BANK_ACCOUNT_NO);
        formData.append("TEXT_BANK_IFSC_CODE", $scope.temp.TEXT_BANK_IFSC_CODE);  
     
        formData.append("TEXT_MOTHER_NAME", $scope.temp.TEXT_MOTHER_NAME);

        formData.append("TEXT_NATIONALITY_CD", $scope.temp.TEXT_NATIONALITY_CD);
        formData.append("TEXT_DESIGNATION_CD", $scope.temp.TEXT_DESIGNATION_CD);
        formData.append("TEXT_DEPARTMENT_CD", $scope.temp.TEXT_DEPARTMENT_CD);

        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      console.log(data.data);
     
      if (data.data.success) {
        
        $scope.messageSuccess(data.data.message);

        $scope.getQuery();
        $scope.clear();
        document.getElementById("TEXT_SCHOOL_ID").focus();
       
        // console.log(data.data);
      } else {
       
        console.log('Érror Ocurred! Please check');
        // console.log(data.data);
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
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
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



  $scope.getNationality = function () {
    $scope.post.getNationality = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getNationality",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getNationality = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
  };
  $scope.getNationality();
  


 $scope.getDesignation = function () {
    $scope.post.getDesignation = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getDesignation",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getDesignation = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
  };
  $scope.getDesignation();
 


   $scope.getDepartment = function () {
    $scope.post.getDepartment = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getDepartment",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getDepartment = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
  };
  $scope.getDepartment();
 

$scope.getBank = function () {
    $scope.post.getBank = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
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
  $scope.getBank();



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
        //console.log(data.data);
        $scope.post.getCountry = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  $scope.getCountry();


  

  $scope.getGender = function () {
    $scope.post.getGender = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getGender",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getGender = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  $scope.getGender();


  

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


  /* ============ Edit Button ============= */
  $scope.edit = function (id) {
   
    document.getElementById("TEXT_SCHOOL_ID").focus();

    $scope.temp = {
    pmid: id.EMPLOYEE_ID,
    TEXT_SCHOOL_ID  : id.SCHOOL_ID.toString(),
    TEXT_EMPLOYEE_NAME  : id.EMPLOYEE_NAME,
    TEXT_EMPLOYEE_CODE  : id.EMPLOYEE_CODE,
		TEXT_DATE_OF_JOINING: id.DATE_OF_JOINING ? new Date(id.DATE_OF_JOINING) : '',
    TEXT_FATHER_HUSBAND_NAME  : id.FATHER_HUSBAND_NAME,
    TEXT_DOB: id.DOB ? new Date(id.DOB) : '',
    TEXT_GENDER_CD: id.GENDER_CD.toString(),
    TEXT_NATIONALITY_CD  : id.NATIONALITY_CD.toString(),
		TEXT_ADDRESS1  : id.ADDRESS1,
		TEXT_ADDRESS2  : id.ADDRESS2,
		TEXT_ZIP_CD  : id.ZIP_CD,
		TEXT_UID  : id.UID,
		TEXT_EMPLOYEE_MOBILE_NO  : id.EMPLOYEE_MOBILE_NO,
    TEXT_EMPLOYEE_EMAIL_ID  : id.EMPLOYEE_EMAIL_ID,
    TEXT_DATE_OF_LEAVING: id.DATE_OF_LEAVING ? new Date(id.DATE_OF_LEAVING) : '',
    TEXT_BANK_CD: id.BANK_CD,
    TEXT_BANK_ACCOUNT_NO: id.BANK_ACCOUNT_NO,
    TEXT_BANK_IFSC_CODE: id.BANK_IFSC_CODE,
    TEXT_BANK_BRANCH  : id.BANK_BRANCH,
     TEXT_MOTHER_NAME: id.MOTHER_NAME,
    TEXT_DESIGNATION_CD: id.DESIGNATION_CD.toString(),
    TEXT_DEPARTMENT_CD: id.DEPARTMENT_CD.toString(),
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
   
    $scope.temp = {};

    $scope.temp.pmid = "";
    $scope.temp.TEXT_DATE_OF_JOINING = new Date();
    $scope.temp.TEXT_DOB = new Date();
    $scope.temp.TEXT_DATE_OF_LEAVING = '';

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
        data: $.param({ pmid: id.EMPLOYEE_ID, type: "delete" }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(function (data, status, headers, config) {
        // console.log(data.data);
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
          //window.location.assign('backoffice/index#!/')
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