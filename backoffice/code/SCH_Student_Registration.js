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
  $scope.temp.TEXT_DATE_OF_LEAVING = new Date();
  
 

  var url = "code/SCH_Student_Registration_code.php";

  
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
    // $(".btn-save").text('Saving...');
    $(".btn-update").attr("disabled", "disabled");
    // $(".btn-update").text('Updating...');
  

    $http({
      method: "POST",
      url: url,
      processData: false,
        transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_FY_YEAR_CD", $scope.temp.TEXT_FY_YEAR_CD);  
        formData.append("TEXT_STUDENT_FIRST_NAME", $scope.temp.TEXT_STUDENT_FIRST_NAME);
        formData.append("TEXT_STUDENT_LAST_NAME", $scope.temp.TEXT_STUDENT_LAST_NAME);
        formData.append("TEXT_DATE_OF_ADMISSION", $scope.temp.TEXT_DATE_OF_ADMISSION.toLocaleString('sv-SE'));  
        formData.append("TEXT_SCHOLAR_NO", $scope.temp.TEXT_SCHOLAR_NO);
        formData.append("TEXT_PEN", $scope.temp.TEXT_PEN);
        formData.append("TEXT_FATHER_NAME", $scope.temp.TEXT_FATHER_NAME);
        formData.append("TEXT_MOTHER_NAME", $scope.temp.TEXT_MOTHER_NAME);
        formData.append("TEXT_DOB", $scope.temp.TEXT_DOB.toLocaleString('sv-SE'));  
        formData.append("TEXT_GENDER_CD", $scope.temp.TEXT_GENDER_CD);
        formData.append("TEXT_CATEGORY_CD", $scope.temp.TEXT_CATEGORY_CD);
        formData.append("TEXT_CASTE_CD", $scope.temp.TEXT_CASTE_CD);
        formData.append("TEXT_RELIGION_CD", $scope.temp.TEXT_RELIGION_CD);
        formData.append("TEXT_CLASS_CD", $scope.temp.TEXT_CLASS_CD);
        formData.append("TEXT_ADDRESS1", $scope.temp.TEXT_ADDRESS1);
        formData.append("TEXT_ADDRESS2", $scope.temp.TEXT_ADDRESS2);
        formData.append("TEXT_CITY_ID", $scope.temp.TEXT_CITY_ID);
        formData.append("TEXT_STATE_ID", $scope.temp.TEXT_STATE_ID);
        formData.append("TEXT_COUNTRY_ID", $scope.temp.TEXT_COUNTRY_ID);
        formData.append("TEXT_ZIP_CD", $scope.temp.TEXT_ZIP_CD);
        formData.append("TEXT_STUDENT_MOBILE_NO", $scope.temp.TEXT_STUDENT_MOBILE_NO);
        formData.append("TEXT_FATHER_MOBILE_NO", $scope.temp.TEXT_FATHER_MOBILE_NO);
        formData.append("TEXT_SAMAGRA_ID", $scope.temp.TEXT_SAMAGRA_ID);
        formData.append("TEXT_RTE_CD", $scope.temp.TEXT_RTE_CD);
        formData.append("TEXT_BLOOD_GROUP", $scope.temp.TEXT_BLOOD_GROUP);
        formData.append("TEXT_HEIGHT", $scope.temp.TEXT_HEIGHT);
        formData.append("TEXT_WEIGHT", $scope.temp.TEXT_WEIGHT);
        formData.append("TEXT_STUDENT_EMAIL_ID", $scope.temp.TEXT_STUDENT_EMAIL_ID);
        formData.append("TEXT_PARENT_EMAIL_ID", $scope.temp.TEXT_PARENT_EMAIL_ID);
        formData.append("TEXT_DATE_OF_LEAVING", $scope.temp.TEXT_DATE_OF_LEAVING.toLocaleString('sv-SE'));
        formData.append("TEXT_UID", $scope.temp.TEXT_UID);
        formData.append("TEXT_BANK_ACCOUNT_NO", $scope.temp.TEXT_BANK_ACCOUNT_NO);  
        formData.append("txtremarks", $scope.temp.txtremarks);
        formData.append("TEXT_SECTION_ID", $scope.temp.TEXT_SECTION_ID);  

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
       
        console.log(data.data);
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
 


$scope.getSection = function () {
    $scope.post.getSection = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getSection",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getSection = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getSection();
  


  $scope.getQuery = function () {
   
     
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD_S : $scope.temp.TEXT_CLASS_CD_S,
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

$scope.getBloodgroup = function () {
    $scope.post.getBloodgroup = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getBloodgroup",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getBloodgroup = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getBloodgroup();
  

$scope.getRte = function () {
    $scope.post.getRte = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getRte",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getRte = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };
  $scope.getRte();



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
  // $scope.getCity();

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
  $scope.getState();


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




  $scope.getClass_S = function () {
    $scope.post.getClass_S = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
         TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getClass_S",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getClass_S = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
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



  $scope.getReligion = function () {
    $scope.post.getReligion = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getReligion",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getReligion = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  $scope.getReligion();


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


  
  $scope.getCategory = function () {
    $scope.post.getCategory = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getCategory",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getCategory = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  $scope.getCategory();


  //Get Back Up Location
  $scope.getCaste = function () {
    $scope.post.getCaste = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getCaste",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getCaste = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        // console.log("Failed");
      }
    );
  };
  $scope.getCaste();

  

  //Get Tech Platform objects
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
    pmid: id.STUDENT_ID,
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
    TEXT_FY_YEAR_CD: id.FY_YEAR_CD.toString(),
    TEXT_STUDENT_FIRST_NAME  : id.STUDENT_FIRST_NAME,
    TEXT_STUDENT_LAST_NAME : id.STUDENT_LAST_NAME,
		TEXT_SCHOLAR_NO  : id.SCHOLAR_NO,
    TEXT_DATE_OF_ADMISSION: id.DATE_OF_ADMISSION ? new Date(id.DATE_OF_ADMISSION) : '',
    TEXT_PEN   : id.PEN,
		TEXT_FATHER_NAME  : id.FATHER_NAME,
    TEXT_MOTHER_NAME: id.MOTHER_NAME,
    TEXT_DOB: id.DOB ? new Date(id.DOB) : '',
		TEXT_GENDER_CD  : id.GENDER_CD.toString(),
		TEXT_CATEGORY_CD  : id.CATEGORY_CD.toString(),
		TEXT_CASTE_CD  : id.CASTE_CD.toString(),
		TEXT_RELIGION_CD  : id.RELIGION_CD.toString(),
		TEXT_CLASS_CD  : id.CLASS_CD.toString(),
		TEXT_ADDRESS1  : id.ADDRESS1,
		TEXT_ADDRESS2  : id.ADDRESS2,
		
    // TEXT_CITY_ID: id.CITY_ID.toString(),
		TEXT_STATE_ID  : id.STATE_ID.toString(),
    TEXT_COUNTRY_ID: id.COUNTRY_ID.toString(),
    
		TEXT_ZIP_CD  : id.ZIP_CD,
		TEXT_STUDENT_MOBILE_NO  : id.STUDENT_MOBILE_NO,
		TEXT_FATHER_MOBILE_NO  : id.FATHER_MOBILE_NO,
		TEXT_SAMAGRA_ID  : id.SAMAGRA_ID,
		TEXT_RTE_CD  : id.RTE_CD.toString(),
		TEXT_UID  : id.UID,
		TEXT_BLOOD_GROUP  : id.BLOOD_GROUP_CD.toString(),
		TEXT_HEIGHT  : id.HEIGHT,
		TEXT_WEIGHT  : id.WEIGHT,
		TEXT_STUDENT_EMAIL_ID  : id.STUDENT_EMAIL_ID,
		TEXT_PARENT_EMAIL_ID  : id.PARENT_EMAIL_ID,
    TEXT_DATE_OF_LEAVING: id.DATE_OF_LEAVING ? new Date(id.DATE_OF_LEAVING) : '',
    TEXT_BANK_ACCOUNT_NO  : id.BANK_ACCOUNT_NO,
    txtremarks: id.REMARKS,
    TEXT_SECTION_ID: id.SECTION_ID.toString(),
    
    };

   $scope.getState();
    $timeout(()=>{
      $scope.temp.TEXT_STATE_ID=id.STATE_ID.toString();
    },100); 

    $scope.getCity();
    
    $timeout(() => {
     
      $scope.temp.TEXT_CITY_ID=id.CITY_ID.toString();
    },100);

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
        data: $.param({ pmid: id.STUDENT_ID, type: "delete" }),
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