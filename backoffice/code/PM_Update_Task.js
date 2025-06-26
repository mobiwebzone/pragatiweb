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
    $scope.temp_new = {};
    $scope.editMode = false;
    $scope.Page = "L&A";
    $scope.PageSub = "TSK_MANG";
    $scope.PageSub1 = "TASK_USERS";
    
    var url = 'code/PM_Update_Task_code.php';
    // var masterUrl = 'code/MASTER_API.php';

    $scope.setMyOrderBY = function(COL){
        $scope.myOrderBY = COL==$scope.myOrderBY ? `-${COL}` : ($scope.myOrderBY == `-${COL}` ? myOrderBY = COL : myOrderBY = `-${COL}`);
        console.log($scope.myOrderBY);
    }


    
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN" && $scope.userrole != "LA_MASTER")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                 
                  $scope.getOrganization();
                    $scope.getQuery();
                   
                }
               
            }
            else {
               
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.save = function(){
        $(".btn-save").attr('disabled', 'disabled').text('Saving...');
        $(".btn-update").attr('disabled', 'disabled').text('Updating...');
       
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'save');
                formData.append("pmid", $scope.temp.pmid);
                formData.append("TEXT_ORG_ID", $scope.temp.TEXT_ORG_ID);
                formData.append("TEXT_PROJECT_ID", $scope.temp.TEXT_PROJECT_ID); 
                
                formData.append("TEXT_ASSIGNED_TO_ID", $scope.temp.TEXT_ASSIGNED_TO_ID);
                
                formData.append("remarks", $scope.temp.remarks);
              
                if ($scope.temp.txtStartDT) {
                  formData.append("txtStartDT", $scope.temp.txtStartDT.toLocaleDateString('sv-SE'));
              } else {
                  // Handle the case where txtEndDT is empty or undefined (optional)
                  formData.append("txtStartDT", ""); // or you can skip appending this field entirely
              }
              
              if ($scope.temp.txtEndDT) {
                  formData.append("txtEndDT", $scope.temp.txtEndDT.toLocaleDateString('sv-SE'));
              } else {
                  // Handle the case where txtEndDT is empty or undefined (optional)
                  formData.append("txtEndDT", ""); // or you can skip appending this field entirely
              }
              


                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getQuery();
                $scope.clear();
                document.getElementById("TEXT_ORG_ID").focus();
            }
            else {
                $scope.messageFailure(data.data.message);
                console.log(data.data)
            }
            // $('.btn-save').removeAttr('disabled').text('SAVE');
          // $('.btn-update').removeAttr('disabled').text('UPDATE');
          
          $(".btn-save").removeAttr("disabled");
          $(".btn-save").text("SAVE");
          $(".btn-update").removeAttr("disabled");
          $(".btn-update").text("UPDATE");

        });
    }

  
    // $scope.sendMail = function () {
    //   $scope.post.sendMail = [];
    
    //   $(".SpinBank").show();
    //   $http({
    //     method: "post",
    //     url: url,
    //     data: $.param({
    //       TEXT_PROJECT_ID: $scope.temp.TEXT_PROJECT_ID,
    //       TEXT_ASSIGNED_TO_ID: $scope.temp.TEXT_ASSIGNED_TO_ID,
    //       type: "sendMail"

    //     }),
    //     headers: { "Content-Type": "application/x-www-form-urlencoded" },
    //   }).then(
    //     function (data, status, headers, config) {
    //       console.log(data.data);
    //       $scope.post.sendMail = data.data.success ? data.data.data : [];
    //       $(".SpinBank").hide();
    //     },
    //     function (data, status, headers, config) {
    //       console.log("Failed");
    //     }
    //   );
    // };

  
    $scope.sendMail = function() {
      console.log($scope.temp.TEXT_PROJECT_ID);  // Check if the value is set correctly
  
      $http({
          method: 'POST',
          url: url,
          processData: false,
          transformRequest: function(data) {
              var formData = new FormData();
              formData.append("type", $TYPE);
              formData.append("TEXT_ASSIGNED_TO_ID", $scope.temp.TEXT_ASSIGNED_TO_ID);
              formData.append("TEXT_PROJECT_ID", $scope.temp.TEXT_PROJECT_ID);  // Ensure this is being appended correctly
              return formData;
          },
          data: $scope.temp,
          headers: { 'Content-Type': undefined }
      }).then(function (data, status, headers, config) {
          console.log(data.data);
        console.log($scope.temp.TEXT_PROJECT_ID);
          if (data.data.success) {
              $scope.messageSuccess(data.data.message);
          } else {
              $scope.messageFailure(data.data.message);
              console.log(data.data);
          }
      });
  }
  
  
  
  

    $scope.getQuery = function () {
        $http({
          method: "post",
          url: url,
            data: $.param({
                TEXT_ORG_ID: $scope.temp.TEXT_ORG_ID,
                TEXT_PROJECT_ID: $scope.temp.TEXT_PROJECT_ID,
                TEXT_TASK_CAT_ID_S: $scope.temp.TEXT_TASK_CAT_ID_S,
                TEXT_TASK_SUB_CAT_ID_S: $scope.temp.TEXT_TASK_SUB_CAT_ID_S,
                TEXT_TASK_STATUS_CD_S: $scope.temp.TEXT_TASK_STATUS_CD_S,
                type: "getQuery"
            }),
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

   
   
    $scope.getProjects = function () {
        $scope.post.getProjects = [];
      
        $(".SpinBank").show();
        $http({
          method: "post",
          url: url,
            data: $.param(
                {
                    TEXT_ORG_ID: $scope.temp.TEXT_ORG_ID,
                    type: "getProjects"
                }),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getProjects = data.data.success ? data.data.data : [];
            $(".SpinBank").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
      };

    
   
       
    $scope.getAssignedToUser = function () {
      $scope.post.getAssignedToUser = [];
  
      $(".SpinBank").show();
      $http({
        method: "post",
        url: url,
        data: $.param({
            TEXT_ORG_ID: $scope.temp.TEXT_ORG_ID,
            TEXT_PROJECT_ID: $scope.temp.TEXT_PROJECT_ID,
          type: "getAssignedToUser",
        }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      }).then(
        function (data, status, headers, config) {
          //console.log(data.data);
          $scope.post.getAssignedToUser = data.data.success ? data.data.data : [];
          $(".SpinBank").hide();
        },
        function (data, status, headers, config) {
          //console.log("Failed");
        }
      );
  };
  $scope.getAssignedToUser();   
  
 
  $scope.getTaskMainCategory = function () {
    $scope.post.getTaskMainCategory = [];
  
    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({
            TEXT_ORG_ID: $scope.temp.TEXT_ORG_ID,
            TEXT_PROJECT_ID: $scope.temp.TEXT_PROJECT_ID,
            TEXT_TASK_CAT_ID_S: $scope.temp.TEXT_TASK_CAT_ID_S,
            type: "getTaskMainCategory"
        }),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getTaskMainCategory = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };


  $scope.getTaskstatus = function () {
        $scope.post.getTaskstatus = [];
      
        $(".SpinBank").show();
        $http({
          method: "post",
          url: url,
          data: $.param({type: "getTaskstatus"}),
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        }).then(
          function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getTaskstatus = data.data.success ? data.data.data : [];
            $(".SpinBank").hide();
          },
          function (data, status, headers, config) {
            console.log("Failed");
          }
        );
      };
    $scope.getTaskstatus();

    
    /* ============ Edit Form =========== */ 
  $scope.edit = function (id) {
    document.getElementById("TEXT_ORG_ID").focus();
            
    $scope.temp = {
      pmid: id.TASK_ID,
      TEXT_ORG_ID: id.ORG_ID.toString(),
      TEXT_PROJECT_ID: id.PROJECT_ID.toString(),
      TEXT_ASSIGNED_TO_ID: id.ASSIGNED_TO_ID.toString(),
     
      txtStartDT :(id.ACT_STARTDATE && id.ACT_STARTDATE.trim() !== '') ? new Date(id.ACT_STARTDATE) : null,
      txtEndDT :(id.ACT_ENDDATE && id.ACT_ENDDATE.trim() !== '') ? new Date(id.ACT_ENDDATE) : null,
     
      remarks: id.REMARKS,
    };

  //   $scope.temp.TEXT_TASK_CAT_ID = id.TASK_CAT_ID.toString();
    
  //   if ($scope.temp.TEXT_TASK_CAT_ID > 0) {
  //     $scope.getTaskCategory();
  //     $timeout(()=>{
  //         $scope.temp.TEXT_TASK_SUB_CAT_ID=id.TASK_SUB_CAT_ID.toString();
  //     },100);
  // }
    

    $scope.editMode = true;
    $scope.index = $scope.post.getQuery.indexOf(id);  
    document.getElementById("TEXT_ORG_ID").focus();
  };     

    /* ============ Clear Form =========== */ 
    $scope.clear = function () {
      document.getElementById("TEXT_ORG_ID").focus();
     
      $scope.temp.TEXT_ASSIGNED_TO_ID= null;
      $scope.temp.txtStartDT= null;
      $scope.temp.txtEndDT= null;
     
      $scope.temp.remarks= null;
      
      $scope.editMode = false;
  };
  

 

    /* ========== DELETE =========== */
    $scope.delete = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'pmid': id.TASK_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getQuery.indexOf(id);
		            $scope.post.getQuery.splice(index, 1);
                      $scope.clear();
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


    $scope.eyepass = function(For,index) {
        if(For == 'MPASS'){
            var input = $("#txtMeetingPasscode");
        }else if(For == 'EPASS'){
            var input = $("#txtEmailPassword");
        }
        
        // var input = $("#txtMeetingPasscode");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        if(input.attr('type') === 'password'){
            $('.Eyeicon'+index).removeClass('fa-eye');
            $('.Eyeicon'+index).addClass('fa-eye-slash');
        }else{
            $('.Eyeicon'+index).removeClass('fa-eye-slash');
            $('.Eyeicon'+index).addClass('fa-eye');
        }
    };

});