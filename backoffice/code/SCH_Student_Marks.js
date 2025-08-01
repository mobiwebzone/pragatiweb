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
  
  
 

  var url = "code/SCH_Student_Marks_code.php";

  
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



// Save Temp Data
$scope.saveTemp = function () {
  
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "saveTemp");
        formData.append("TEXT_SCHOOL_ID", $scope.temp.TEXT_SCHOOL_ID);
        formData.append("TEXT_CLASS_CD", $scope.temp.TEXT_CLASS_CD);
        formData.append("TEXT_EXAM_TYPE_CD", $scope.temp.TEXT_EXAM_TYPE_CD);
        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      if (data.data.success) {
        
        document.getElementById("TEXT_SCHOOL_ID").focus();

        console.log(data.data);
      } else {
        console.log("Érror Ocurred! Please check");
        console.log(data.data);
        $scope.messageFailure(data.data.message);
      }
     
    });
  };

  
$scope.saveAll = function () {
  if (!$scope.post.getQuery || $scope.post.getQuery.length === 0) return;

  for (let i = 0; i < $scope.post.getQuery.length; i++) {
    const mark = $scope.post.getQuery[i];
    const subject = mark.SUBJECT || "Unknown";
    const obtainedRaw = mark.MARKS_OBTAINED;
    const max = Number(mark.MAX_MARKS);

    // Step 1: Check if empty
    if (obtainedRaw === null || obtainedRaw === undefined || obtainedRaw === '') {
      alert(`Please enter marks for subject: "${subject}"`);
      return;
    }

    // Step 2: Check if it's a number using regex and typeof
    if (!/^\d+(\.\d+)?$/.test(obtainedRaw.toString())) {
      alert(`Please enter a numeric value for subject: "${subject}"`);
      return;
    }

    // Step 3: Convert to number and check range
    const obtained = Number(obtainedRaw);
    if (obtained < 0 || obtained > max) {
      alert(`Marks for subject "${subject}" must be between 0 and ${max}`);
      return;
    }
  }

  $(".btn-save").attr("disabled", "disabled").text("Saving...");

  const marksList = $scope.post.getQuery.map(mark => ({
    MARKS_ID: mark.MARKS_ID || 0,
    STUDENT_ID: mark.STUDENT_ID,
    SCHOOL_ID: mark.SCHOOL_ID,
    CLASS_CD: mark.CLASS_CD,
    FY_YEAR_CD: mark.FY_YEAR_CD,
    SCHOOL_SUBJECT_ID: mark.SCHOOL_SUBJECT_ID,
    EXAM_ID: mark.EXAM_ID,
    MAX_MARKS: mark.MAX_MARKS,
    MARKS_OBTAINED: Number(mark.MARKS_OBTAINED)
  }));

  $http({
    method: "POST",
    url: url,
    data: $.param({
      type: "saveAll",
      marksArray: JSON.stringify(marksList),
    }),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(function (response) {
    if (response.data.success) {
      $scope.messageSuccess(response.data.message);
      $scope.getQuery();
    } else {
      $scope.messageFailure(response.data.message);
    }

    $(".btn-save").removeAttr("disabled").text("SAVE ALL");
  });
};


$scope.isFormValid = function () {
  if (!$scope.post.getQuery || $scope.post.getQuery.length === 0) return false;

  for (let i = 0; i < $scope.post.getQuery.length; i++) {
    let mark = $scope.post.getQuery[i];
    let obtained = parseFloat(mark.MARKS_OBTAINED);
    let max = parseFloat(mark.MAX_MARKS);

    if (isNaN(obtained) || obtained < 0 || obtained > max) {
      return false;
    }
  }

  return true;
};

  $scope.getQuery = function () {
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD: $scope.temp.TEXT_CLASS_CD,
        TEXT_EXAM_TYPE_CD: $scope.temp.TEXT_EXAM_TYPE_CD,
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



$scope.getSubjects = function () {
    $scope.post.getSubjects = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getSubjects",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getSubjects = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
      }
    );
  };

  $scope.getSubjects();


  $scope.getExaminationType = function () {
    $scope.post.getExaminationType = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
     
       TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        type: "getExaminationType",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        
        $scope.post.getExaminationType = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
       
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
        TEXT_SCHOOL_ID: $scope.temp.TEXT_SCHOOL_ID,
        TEXT_CLASS_CD : $scope.temp.TEXT_CLASS_CD,
        type: "getStudent",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        //console.log(data.data);
        $scope.post.getStudent = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        //console.log("Failed");
      }
    );
  };
  // $scope.getStudent();


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

    $scope.temp = {
    marksid: id.MARKS_ID,
    TEXT_SCHOOL_ID: id.SCHOOL_ID.toString(),
    TEXT_CLASS_CD: id.CLASS_CD.toString(),
   
		TEXT_EXAM_TYPE_CD: id.EXAM_ID.toString(),
    TEXT_SUBJECT_CD: id.SCHOOL_SUBJECT_ID.toString(),
   
    txtremarks: id.REMARKS
    };

   $scope.getStudent();
    $timeout(()=>{
      $scope.temp.TEXT_STUDENT_ID=id.STUDENT_ID.toString();
    },500);
   
    $scope.getTotalmarks();
    $timeout(()=>{
      $scope.temp.TEXT_MARKS_MASTER_ID=id.SUBJECT_MAX_MARKS_ID.toString();
    },500);    

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
          marksid: id.MARKS_ID,
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