$postModule = angular.module("myApp", [ "angularjs-dropdown-multiselect", "angularUtils.directives.dirPagination", "ngSanitize"]);
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
  $scope.PageSub = "MEPITMASTER";
  $scope.PageSub1 = "MEPITDEPLOYMENT";
  $scope.LOCS_model = [];

  

  $scope.LOCS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px'};

  var url = "code/PM_New_Projects_code.php";

  $scope.setMyOrderBY = function (COL) {
    $scope.myOrderBY = COL == $scope.myOrderBY ? `-${COL}` : $scope.myOrderBY == `-${COL}` ? (myOrderBY = COL) : (myOrderBY = `-${COL}`);
    console.log($scope.myOrderBY);
};

  
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

          if ($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN") {
            // alert($scope.userrole);
            window.location.assign("dashboard.html#!/dashboard");
          } else {
            $scope.getLocations();
            $scope.getQuery();
            $scope.getProjecttype();
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

  /* ========== Save Paymode =========== */
  $scope.save = function () {
    $(".btn-save").attr("disabled", "disabled");
    // $(".btn-save").text('Saving...');
    $(".btn-update").attr("disabled", "disabled");
    // $(".btn-update").text('Updating...');
    $scope.LOCATIONIDS = [];
    if($scope.LOCS_model.length>0){
      $scope.LOCATIONIDS = $scope.LOCS_model.map((x)=>x.id);
    }
    $http({
      method: "POST",
      url: url,
      processData: false,
      transformRequest: function (data) {
        var formData = new FormData();
        formData.append("type", "save");
        formData.append("pmid", $scope.temp.pmid);
        formData.append("TEXT_ORG_ID", $scope.temp.TEXT_ORG_ID);
        formData.append("TEXT_LOC_ID", $scope.temp.TEXT_LOC_ID);
        formData.append("TEXT_PROJECT_NAME", $scope.temp.TEXT_PROJECT_NAME);
        formData.append("TEXT_PM_NAME", $scope.temp.TEXT_PM_NAME);
        formData.append("TEXT_PROJECT_TYPE_CD", $scope.temp.TEXT_PROJECT_TYPE_CD);
        formData.append("TEXT_PROJECT_START_DATE", $scope.dateFormat($scope.temp.TEXT_PROJECT_START_DATE));
        formData.append("TEXT_PROJECT_END_DATE",$scope.dateFormat($scope.temp.TEXT_PROJECT_END_DATE));
        formData.append("TEXT_PROJECT_CAPACITY", $scope.temp.TEXT_PROJECT_CAPACITY);
        formData.append("TEXT_PROJECT_STATUS_CD", $scope.temp.TEXT_PROJECT_STATUS_CD);
        formData.append("TEXT_PROJECT_CAPACITY_UNIT_CD", $scope.temp.TEXT_PROJECT_CAPACITY_UNIT_CD);
        formData.append("txtremarks", $scope.temp.txtremarks);
        

        return formData;
      },
      data: $scope.temp,
      headers: { "Content-Type": undefined },
    }).then(function (data, status, headers, config) {
      console.log(data.data);
      if (data.data.success) {
        $scope.messageSuccess(data.data.message);

        $scope.getQuery();
        // $scope.getLocations();
        $scope.clear();
        document.getElementById("TEXT_ORG_ID").focus();
        // console.log(pmid);
      } else {
        $scope.messageFailure(data.data.message);
        // console.log(data.data)
      }
      $(".btn-save").removeAttr("disabled");
      $(".btn-save").text("SAVE");
      $(".btn-update").removeAttr("disabled");
      $(".btn-update").text("UPDATE");
    });
  };


  $scope.dateFormat = function (date) { 
    return (!date || date == '') ? '' : date.toLocaleDateString('sv-SE');
  }
  
  
  $scope.getQuery = function () {
    
    console.log( $scope.temp.txtStatus);

    $http({
      method: "post",
      url: url,
      data: $.param({ 
        TEXT_ORG_ID: $scope.temp.TEXT_ORG_ID,
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

  $scope.getOrganization = function () {
    $scope.post.getOrganization = [];
  
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getOrganization"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getOrganization = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
$scope.getOrganization();



$scope.getProjecttype = function () {
  $scope.post.getProjecttype = [];

  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({type: "getProjecttype"}),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (data, status, headers, config) {
      console.log(data.data);
      $scope.post.getProjecttype = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    },
    function (data, status, headers, config) {
      console.log("Failed");
    }
  );
};
$scope.getProjecttype();

 
$scope.getcapacityunit = function () {
  $scope.post.getcapacityunit = [];

  $(".SpinBank").show();
  $http({
    method: "post",
    url: url,
    data: $.param({type: "getcapacityunit"}),
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
  }).then(
    function (data, status, headers, config) {
      console.log(data.data);
      $scope.post.getcapacityunit = data.data.success ? data.data.data : [];
      $(".SpinBank").hide();
    },
    function (data, status, headers, config) {
      console.log("Failed");
    }
  );
};
$scope.getcapacityunit();

  
  
  
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

  
 
  
  $scope.getProjectstatus = function () {
    $http({
        method: 'post',
        url: url,
        data: $.param({ 'type': 'getProjectstatus'}),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).
    then(function (data, status, headers, config) {
      // console.log(data.data.data);
      $scope.post.getProjectstatus = data.data.success ? data.data.data : [];
    },
    function (data, status, headers, config) {
      console.log('Failed');
    })
    }
    $scope.getProjectstatus();
  
  
  
  $scope.edit = function (id) {
    
    document.getElementById("TEXT_ORG_ID").focus();

    $scope.temp = {
      pmid: id.PROJECT_ID,
      TEXT_ORG_ID: id.ORG_ID.toString(),
      TEXT_LOC_ID : id.LOC_ID.toString(),
      TEXT_PROJECT_TYPE_CD: id.PROJECT_TYPE_CD.toString(),
      TEXT_PROJECT_NAME : id.PROJECT_NAME,
      TEXT_PM_NAME  : id.PM_NAME,
      TEXT_PROJECT_START_DATE : id.PROJECT_START_DATE ? new Date(id.PROJECT_START_DATE) : '',
      TEXT_PROJECT_END_DATE   : id.PROJECT_END_DATE ? new Date(id.PROJECT_END_DATE) : '',
      TEXT_PROJECT_CAPACITY : id.PROJECT_CAPACITY,
      TEXT_PROJECT_CAPACITY_UNIT_CD : id.PROJECT_CAPACITY_UNIT_CD.toString(),
      TEXT_PROJECT_STATUS_CD  : id.PROJECT_STATUS_CD.toString(),
      TEXT_REMARKS : id.REMARKS,
      
      
    };

    
    $scope.editMode = true;
    // $scope.getUserByLoc();
    
    $scope.index = $scope.post.getQuery.indexOf(id);
  };



  /* ============ Clear Form =========== */
  $scope.clear = function () {
    document.getElementById("TEXT_ORG_ID").focus();
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
        data: $.param({ pmid: id.PROJECT_ID, type: "delete" }),
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

  //Get Status
  $scope.getStatus = function () {
    $scope.post.getStatus = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
        type: "getStatus",
      }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        // console.log(data.data);
        $scope.post.getStatus = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getStatus();  


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