
$postModule = angular.module("myApp", [ "angularUtils.directives.dirPagination", "ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.PageSub = "MEPITMANAGEMENT";
    $scope.PageSub1 = "MEPITMASTER";
    $scope.PageSub2 = "MEPCHANGES";
   
    
    var url = 'code/MEP_Object_Changes_Tracker_code.php';




    
    // GET DATA
    $scope.init = function () {
        // Check Session
        $http({
            method: 'post',
            url: 'code/checkSession.php',
            data: $.param({ 'type': 'checkSession' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);   
            if (data.data.success) {
                $scope.post.user = data.data.data;
                $scope.userid=data.data.userid;
                $scope.userFName=data.data.userFName;
                $scope.userLName=data.data.userLName;
                $scope.userrole=data.data.userrole;
                $scope.USER_LOCATION=data.data.LOCATION;

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                    // $scope.getBankAccountsDetails();
                    // $scope.getBankID();
                    // $scope.getLocations();
                }
                // window.location.assign("dashboard.html");
            }
            else {
                // window.location.assign('index.html#!/login')
                // alert
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // alert($scope.temp.ddlCollege);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("OBJECT_CHANGE_ID", $scope.temp.OBJECT_CHANGE_ID);
                formData.append("ddlTechPlatform", $scope.temp.ddlTechPlatform);
                formData.append("ddlObjectType", $scope.temp.ddlObjectType);
                formData.append("ddlObject", $scope.temp.ddlObject);
                formData.append("ddlRelatedObjectType", $scope.temp.ddlRelatedObjectType);
                formData.append("ddlRelatedObject", $scope.temp.ddlRelatedObject);
                formData.append("TxtModificaton", $scope.temp.TxtModificaton);
                formData.append("ddlDevelopedBY", $scope.temp.ddlDevelopedBY);
                formData.append("Deploydate", (!$scope.temp.Deploydate || $scope.temp.Deploydate=='') ? '' : $scope.temp.Deploydate.toLocaleDateString('sv-SE'));
                formData.append("txtRemark", $scope.temp.txtRemark);
                
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getTrackingData();
                $scope.clear();
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-save').removeAttr('disabled');
            $(".btn-save").text('SAVE');
            $('.btn-update').removeAttr('disabled');
            $(".btn-update").text('UPDATE');
        });
    }

    
    /* ========== GET TECH PLATEFORM =========== */
  $scope.getTechPlatform = function () {
    $scope.post.getTechPlatform = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getTechPlatform"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getTechPlatform = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getTechPlatform();



 /* ========== GET OBJECT TYPE =========== */
  $scope.getObjectType = function () {
    $scope.post.getObjectType = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getObjectType"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getObjectType = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getObjectType();

      /* ========== GET OBJECT =========== */
      $scope.getObject = function () {
        $scope.post.getObject = [];
    
        $(".SpinBank").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getObject"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getObject = data.data.success ? data.data.data : [];
            $(".SpinBank").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
      };
      $scope.getObject();
    
    
    
     /* ========== GET RELATED OBJECT TYPE =========== */
      $scope.getRelatedObjectType = function () {
        $scope.post.getRelatedObjectType = [];
    
        $(".SpinBank").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getRelatedObjectType"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getRelatedObjectType = data.data.success ? data.data.data : [];
            $(".SpinBank").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
      };
      $scope.getRelatedObjectType();

          /* ========== GET RELATED OBJECT =========== */
  $scope.getRelatedObject = function () {
    $scope.post.getRelatedObject = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getRelatedObject"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getRelatedObject = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getRelatedObject();



 /* ========== GET DEVELOPED BY =========== */
  $scope.getDevelpedBY = function () {
    $scope.post.getDevelpedBY = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getDevelpedBY"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getDevelpedBY = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getDevelpedBY();



 
     
 /* ========== GET OBJECT TRACKING DATA =========== */
  $scope.getTrackingData = function () {
    $scope.post.getTrackingData = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getTrackingData"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getTrackingData = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getTrackingData();

    

  
    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            OBJECT_CHANGE_ID:id.OBJECT_CHANGE_ID,
            ddlTechPlatform:id.TECHPLATFORMID.toString(),
            ddlObjectType:id.OBJTYPEID.toString(),
            ddlObject:id.OBJMASTER_ID.toString(),
            ddlRelatedObjectType:id.OTHER_OBJTYPEID.toString(),
            ddlPublicddlRelatedObjectationname:id.OTHER_OBJMASTER_ID.toString(),
            TxtModificaton:id.MODIFICATION_DESC,
            ddlDevelopedBY:id.DEVELOPER_ID.toString(),
            Deploydate:new Date(id.DEPLOYMENT_DATE),
            txtRemark:id.REMARKS
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getTrackingData.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clear = function(){
        $scope.temp = {};
        $scope.editMode = false;
    }
    


    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'OBJECT_CHANGE_ID': id.OBJECT_CHANGE_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getTrackingData.indexOf(id);
		            $scope.post.getTrackingData.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    


    /* ========== Logout =========== */
    $scope.logout = function () {
        $http({
            method: 'post',
            url: 'code/logout.php',
            data: $.param({ 'type': 'logout' }),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    window.location.assign('index.html#!/login')
                }
                else {
                    //window.location.assign('backoffice/index#!/')
                }
            },
            function (data, status, headers, config) {
                console.log('Not login Failed');
            })
    }



    $scope.messageSuccess = function (msg) {
        jQuery('.alert-success > span').html(msg);
        jQuery('.alert-success').show();
        jQuery('.alert-success').delay(5000).slideUp(function () {
            jQuery('.alert-success > span').html('');
        });
    }

    $scope.messageFailure = function (msg) {
        jQuery('.alert-danger > span').html(msg);
        jQuery('.alert-danger').show();
        jQuery('.alert-danger').delay(5000).slideUp(function () {
            jQuery('.alert-danger > span').html('');
        });
    }


});