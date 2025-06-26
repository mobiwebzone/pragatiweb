$postModule = angular.module("myApp", ["angularjs-dropdown-multiselect","angularUtils.directives.dirPagination", "ngSanitize"]);
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
    $scope.FormName = 'Show Entry Form';
    $scope.Page = "PRODUCTS";
    $scope.PageSub = "PRO_DIS_MASTER";
    $scope.ddlPlans=[];
    $scope.post.getPlan=[];
    $scope.sequence_arr=[];
    // $scope.post.getPDMID_Plan=[];
    $scope.plan_array=[];
    var url = 'code/ProductDisplayMaster_code.php';
    $scope.temp.txtDisplayColor ='#000000';
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }

    $scope.PLANS_settings = {enableSearch: true,scrollable: true, scrollableHeight:'400px',scrollableWidth:'200px'};

    /* =============== DATE CONVERT ============== */
    $scope.dateFormat=function(datetime){
        return datetime.getFullYear()+'-'+("0"+(datetime.getMonth()+1)).slice(-2)+'-'+("0"+datetime.getDate()).slice(-2);
    } 
    
    
    /* =============== Time CONVERT ============== */
    $scope.TimeFormat=function(datetime){
        return datetime.getHours()+':'+datetime.getMinutes();
        
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
                // window.location.assign("dashboard.html");

                if($scope.userrole != "TSEC_USER")
                {
                    $scope.getProductDisplay();
                    $scope.getPlans();
                }
                else{
                    window.location.assign("dashboard.html#!/dashboard");
                }
            }
            else {
                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

    }

    $scope.FormShowHide=function (){
        
        $scope.FormName ='';
        var isMobileVersion = document.getElementsByClassName('collapsed');
        if (isMobileVersion.length > 0) {
            $scope.FormName = 'Hide Entry Form';
        }else{
            $scope.FormName = 'Show Entry Form';
        }

        $('.ShowHideIcon').toggleClass("fa-plus-circle fa-minus-circle");
    }

    $scope.selectPlanNo = {
        onSelectAll :onPlanSelect,
        onItemSelect: onPlanSelect,
        onItemDeselect: onPlanSelect,
        // onDeselectAll: clearPlanSelect,
    };
    function onPlanSelect() {
        // console.log($scope.ddlPlans);
        // clearPlanSelect();
        
        // $timeout(()=>{
        //     for(i=0; i<($scope.ddlPlans).length; i++)
        //     {
        //         $scope.plan_array.push($scope.ddlPlans[i]['id']);
        //     }
        //     console.log($scope.plan_array);
        // },1000);

    }

    // function clearPlanSelect(){
    //     $scope.plan_array=[];
    // }


    $scope.saveProductDisplay = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');
        // console.log($scope.ddlPlans);
        $scope.plan_array = []
        const uniqueIdsSet = new Set($scope.ddlPlans.map(x => x.id));
        $scope.plan_array = [...uniqueIdsSet];
        // console.log($scope.plan_array);

        // $scope.plan_array =$scope.ddlPlans.map(x=>x.id);
        // alert(JSON.stringify($scope.temp.ddlPlans));
        // return;
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveProductDisplay');
                formData.append("pdmid", $scope.temp.pdmid);
                formData.append("txtProductDisplay", $scope.temp.txtProductDisplay);
                formData.append("ddlPlans", $scope.plan_array);
                formData.append("txtProductOrder", $scope.temp.txtProductOrder);
                formData.append("txtDisplayColor", $scope.temp.txtDisplayColor);
                formData.append("isHeader", $scope.temp.isHeader);
                formData.append("txtHeader", $scope.temp.txtHeader);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getProductDisplay();


                document.getElementById("txtProductDisplay").focus();
                // $scope.temp={};
                $scope.clearForm();
                $scope.editMode = false;
                
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
    

    /* ========== UPDATE DETAILS SEQNO =========== */
    $scope.updateSeqno = function(id,idx){
        $("#txtSequence"+idx).attr('disabled', true);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'updateSeqno');
                formData.append("PDDID", id.PDDID);
                formData.append("SEQNO", $scope.sequence_arr[idx]);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                $scope.getDetails($scope.GET_PDMID);
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $("#txtSequence"+idx).attr('disabled', false);
        });
    }

    /* ========== GET Plans =========== */
    $scope.getPlans = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT



    /* ========== GET Products Display =========== */
    $scope.getProductDisplay = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getProductDisplay'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            console.log(data.data);
            $scope.post.getProductDisplays = data.data.data;
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProductDisplay(); --INIT
    




    $scope.getPDMID_Plans = function () {
        // alert($scope.temp.pdmid);
        // $scope.post.getPlan=[];
        $scope.ddlPlans=[];
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPDMID_Plans','PDMID':$scope.temp.pdmid}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);  
            // $scope.plan_array= data.data.data;
            // alert(data.data.data);
            if(data.data.success){
                // var json = JSON.parse(angular.toJson(data.data.data));
                $scope.ddlPlans=data.data.data;
                // console.log( $scope.ddlPlans);
            }

        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }


    /* ========== GET Products Display Details =========== */
    $scope.getDetails = function (PDMID) {
        $scope.GET_PDMID = PDMID;
        $scope.SpinDetailsData=true;
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getDetails','PDMID':$scope.GET_PDMID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getDetails = data.data.data;
            $scope.SpinDetailsData=false;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getDetails(); --INIT
    


    /* ============ Edit Button ============= */ 
    $scope.editProductDisplay = function (id) {
        $('.collapse').collapse('show');
        $timeout(function() {
            $scope.FormShowHide();
            $scope.FormName = 'Hide Entry Form';
        },500);

        document.getElementById("txtProductDisplay").focus();
        $scope.temp = {
            pdmid:id.PDMID,
            txtProductDisplay: id.DISPLAY_PRODUCT,
            txtProductOrder: Number(id.ORDER),
            txtDisplayColor: id.COLORCODE,
            isHeader: id.ISHEADER,
            txtHeader: id.HEADER
        };

        $scope.getPDMID_Plans();


        $scope.editMode = true;
        $scope.index = $scope.post.getProductDisplays.indexOf(id);
    }
    
    /* ============ Clear Form =========== */ 
    $scope.clearForm = function(){
        document.getElementById("txtProductDisplay").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.temp.txtDisplayColor ='#000000';
        $scope.temp.isHeader ='0';
        // $scope.temp.ddlPlans=[];
        // $scope.post.getPlan=[];
        // $scope.plan_array=[];
        $scope.ddlPlans=[];
        $scope.plan_array = []
    }

    /* ========== DELETE =========== */
    $scope.deleteProductDisplay = function (id) {
        var r = confirm("Are you sure want to delete this Plan? This will delete all information permanently.");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'pdmid': id.PDMID, 'type': 'deleteProductDisplay' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getProductDisplays.indexOf(id);
		            $scope.post.getProductDisplays.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearForm();
                    
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



    // Plans
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