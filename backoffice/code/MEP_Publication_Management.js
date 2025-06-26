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
    $scope.PageSub = "PUBLICATION";
    $scope.PageSub2 = "PUBLICATIONMASTER";
   
    
    var url = 'code/MEP_Publication_Management_code.php';




    
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
                formData.append("PUBLICATION_ID", $scope.temp.PUBLICATION_ID);
                formData.append("txtPublicationName", $scope.temp.txtPublicationName);
                formData.append("ddlGradefrom", $scope.temp.ddlGradefrom);
                formData.append("ddlGradeto", $scope.temp.ddlGradeto);
                formData.append("Sdate", (!$scope.temp.Sdate || $scope.temp.Sdate=='') ? '' : $scope.temp.Sdate.toLocaleDateString('sv-SE'));
                formData.append("Edate", (!$scope.temp.Edate || $scope.temp.Edate=='') ? '' : $scope.temp.Edate.toLocaleDateString('sv-SE'));
                formData.append("txtHourSpent", $scope.temp.txtHourSpent);
                formData.append("txtBookcost", $scope.temp.txtBookcost);
                formData.append("ddlPublicationStatus", $scope.temp.ddlPublicationStatus);
                formData.append("txtMainContri", $scope.temp.txtMainContri);
                formData.append("ddlDocutype", $scope.temp.ddlDocutype);
                formData.append("txtLastdraft", $scope.temp.txtLastdraft);
                formData.append("txtLDfileattached", $scope.temp.txtLDfileattached);
                formData.append("txtLDraftdate",(!$scope.temp.txtLDraftdate || $scope.temp.txtLDraftdate=='') ? '' : $scope.temp.txtLDraftdate.toLocaleDateString('sv-SE'));
                formData.append("txtLDraftwho", $scope.temp.txtLDraftwho);
                formData.append("txtFinalLink", $scope.temp.txtFinalLink);
                formData.append("txtFileattached", $scope.temp.txtFileattached);
                formData.append("txtFinaldate", (!$scope.temp.txtFinaldate || $scope.temp.txtFinaldate=='') ? '' : $scope.temp.txtFinaldate.toLocaleDateString('sv-SE'));
                formData.append("txtFinalwho", $scope.temp.txtFinalwho);
                formData.append("txtFinalRemark", $scope.temp.txtFinalRemark);
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
                $scope.getPublicationmanagementData();
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



 /* ========== GET GRADE FROM =========== */
  $scope.getGradefrom = function () {
    $scope.post.getGradefrom = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getGradefrom"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getGradefrom = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getGradefrom();



 /* ========== GET GRADE TO =========== */
 $scope.getGradeto = function () {
    $scope.post.getGradeto = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getGradeto"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getGradeto = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getGradeto();

  
/* ========== GET PUBLICATION STATUS =========== */
  $scope.getPublicStatus = function () {
    $scope.post.getPublicStatus = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getPublicStatus"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getPublicStatus = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getPublicStatus();

  /* ========== GET DOCUMNET TYPE =========== */
  $scope.getDocutype = function () {
    $scope.post.getDocutype = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getDocutype"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getDocutype = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getDocutype();


     
 /* ========== GET PUBLICATION MANAGEMENT DATA =========== */
  $scope.getPublicationmanagementData = function () {
    $scope.post.getPublicationmanagementData = [];

    $(".SpinBank").show();
    $http({
      method: "post",
      url: url,
      data: $.param({type: "getPublicationmanagementData"}),
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
    }).then(
      function (data, status, headers, config) {
        console.log(data.data);
        $scope.post.getPublicationmanagementData = data.data.success ? data.data.data : [];
        $(".SpinBank").hide();
      },
      function (data, status, headers, config) {
        console.log("Failed");
      }
    );
  };
  $scope.getPublicationmanagementData();



    /* ============ Edit Button ============= */ 
    $scope.edit = function (id) {
        $scope.temp = {
            PUBLICATION_ID:id.PUBLICATION_ID,
            txtPublicationName:id.PUBLICATION_NAME,
            ddlGradefrom:id.GRADE_FROM_CD.toString(),
            ddlGradeto:id.GRADE_TO_CD.toString(),
            Sdate:new Date(id.START_DATE),
            Edate:new Date(id.END_DATE),
            txtHourSpent:id.HOURS_SPENT,
            txtBookcost:id.BOOK_COST,
            ddlPublicationStatus:id.PUBLICATION_STATUS_CD.toString(),
            txtMainContri:id.MAIN_CONTRIBUTOR,
            ddlDocutype:id.DOCUMENT_TYPE_CD.toString(),
            txtLastdraft:id.LAST_DRAFT_LINK,
            txtLDfileattached:id.LAST_DRAFT_FILE_ATTACHED,
            txtLDraftdate:new Date(id.LAST_DRAFT_DATE),
            txtLDraftwho:id.LAST_DRAFT_WHO,
            txtFinalLink:id.FINAL_LINK,
            txtFileattached:id.FINAL_FILE_ATTACHED,
            txtFinaldate:new Date(id.FINAL_DATE),
            txtFinalwho:id.FINAL_WHO,
            txtFinalRemark:id.FINAL_REMARKS,
            txtRemark:id.REMARKS
        };

        $scope.editMode = true;
        $scope.index = $scope.post.getPublicationmanagementData.indexOf(id);
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
                data: $.param({ 'PUBLICATION_ID': id.PUBLICATION_ID, 'type': 'delete' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                //  console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getPublicationmanagementData.indexOf(id);
		            $scope.post.getPublicationmanagementData.splice(index, 1);
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