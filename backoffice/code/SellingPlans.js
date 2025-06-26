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
    $scope.FormName = 'Show Entry Form';
    $scope.Page = "PRODUCTS";
    $scope.PageSub = "SELLING_PLAN";
    $scope.GetPlanId='-';
    $scope.temp.txtDisplayColor="#000000";
    $scope.serial = 1;

    $scope.indexCount = function(newPageNumber){
        $scope.serial = newPageNumber * 25 - 24;
    }
    
    var url = 'code/SellingPlans_code.php';

    $scope.Days=['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

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
                    $scope.getLocation();
                    $scope.getProduct();
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


    $scope.savePlan = function(){
        $(".btn-save").attr('disabled', 'disabled');
        $(".btn-save").text('Saving...');
        $(".btn-update").attr('disabled', 'disabled');
        $(".btn-update").text('Updating...');

        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'savePlan');
                formData.append("planid", $scope.temp.planid);
                formData.append("ISCombo", true);
                formData.append("txtPlan", $scope.temp.txtPlan);
                formData.append("txtStartDT", $scope.dateFormat($scope.temp.txtStartDT));
                formData.append("txtEndDT", $scope.dateFormat($scope.temp.txtEndDT));
                formData.append("txtPrice", $scope.temp.txtPrice);
                formData.append("txtPriceInstall", $scope.temp.txtPriceInstall);
                formData.append("ddlFrequency", $scope.temp.ddlFrequency);
                formData.append("txtNO_Install", $scope.temp.txtNO_Install);
                formData.append("txtDisplayFrom", $scope.dateFormat($scope.temp.txtDisplayFrom));
                formData.append("txtDisplayTo", $scope.dateFormat($scope.temp.txtDisplayTo));
                formData.append("txtDisplayColor", $scope.temp.txtDisplayColor);
                formData.append("chkActive", $scope.temp.chkActive);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                // alert(data.data.PLANID);
                $scope.GetPlanId=data.data.PLANID;
                $scope.getPlans();

                $scope.getPlanProducts();
                $scope.getPlanLocation();
                $scope.getShedule();

                document.getElementById("txtPlan").focus();
                // $scope.temp={};
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
    


    /* ========== GET Plans =========== */
    $scope.getPlans = function () {
        $('#SpinMainData').show();
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlans'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlan = data.data.data;
            $('#SpinMainData').hide();
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlans(); --INIT

    /* ========== GET Products =========== */
    $scope.getProduct = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getProduct'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getProduct(); --INIT
    



    /* ============ Edit Button ============= */ 
    $scope.editPlan = function (id) {
        $scope.post.getShedules=[];
        $scope.GET_LOCID=0;
        $scope.clearSechedule();
        
        $('.collapse').collapse('show');
        $timeout(function() {
            $scope.FormShowHide();
            $scope.FormName = 'Hide Entry Form';
        },500);

        document.getElementById("txtPlan").focus();
        $scope.temp = {
            planid:id.PLANID,
            txtPlan: id.PLANNAME,
            txtStartDT: new Date(id.STARTDATE),
            txtEndDT: new Date(id.ENDDATE),
            txtPrice: Number(id.PRICE),
            txtPriceInstall: Number(id.INST_AMOUNT),
            ddlFrequency: id.INST_FREQ,
            txtNO_Install: Number(id.INST_NO),
            txtDisplayFrom: new Date(id.DISPLAYFROMDATE),
            txtDisplayTo: new Date(id.DISPLAYTODATE),
            txtDisplayColor: id.DISPLAYCOLOR,
            chkActive:id.ACTIVE === 1 ? '1' : '0',
        };
        $scope.GetPlanId = id.PLANID;

        $scope.getPlanProducts();
        $scope.getPlanLocation();
        // $scope.getShedule();


        $scope.editMode = true;
        $scope.index = $scope.post.getProduct.indexOf(id);
    }
    
    
    /* ============ Clear Form =========== */ 
    $scope.clearFormPlans = function(){
        document.getElementById("txtPlan").focus();
        $scope.temp={};
        $scope.editMode = false;
        $scope.GetPlanId='';
        $scope.temp.txtDisplayColor="#000000";
        $scope.temp.chkActive="1";

        $scope.post.getShedules=[];
        $scope.GET_LOCID=0;
        $scope.clearSechedule();
    }

    /* ========== DELETE =========== */
    $scope.deletePlans = function (id) {
        var r = confirm("Are you sure want to delete this Plan? This will delete all information permanently.");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'planid': id.PLANID, 'type': 'deletePlans' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getPlan.indexOf(id);
		            $scope.post.getPlan.splice(index, 1);
		            console.log(data.data.message)
                    $scope.clearFormPlans();
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    
    

    







      /* ============================================ Products START ================================= */
      $scope.saveProducts = function(){
        $(".btn-saveProduct").attr('disabled', 'disabled');
        // $(".btn-saveProduct").text('Saving...');
        $(".btn-updateProduct").attr('disabled', 'disabled');
        // $(".btn-update").text('Updating...');

        // alert($scope.GetPlanId);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveProducts');
                formData.append("planpid", $scope.temp.planpid);
                formData.append("planid", $scope.GetPlanId);
                formData.append("ddlProduct", $scope.temp.ddlProduct);
                formData.append("txtProductPrice", $scope.temp.txtProductPrice);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                if ($scope.editMode) {
                    $scope.messageSuccess(data.data.message);
                }
                else {
                    $scope.messageSuccess(data.data.message);
                }
                // alert(data.data.PLANID);
                $scope.getPlanProducts();
                document.getElementById("txtPlan").focus();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveProduct').removeAttr('disabled');
            // $(".btn-saveProduct").text('SAVE');
            $('.btn-updateProduct').removeAttr('disabled');
            // $(".btn-update").text('UPDATE');
        });
    }


    /* ========== GET Plan Products =========== */
    $scope.getPlanProducts = function () {
        // alert($scope.GetPlanId);
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlanProducts','planid':$scope.GetPlanId}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlanProduct = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlanProducts();

    /* ============ Edit Button ============= */ 
    $scope.editPlanProduct = function (id) {
        document.getElementById("ddlProduct").focus();
         
        $scope.temp.planpid=id.PLANPID;
        $scope.temp.ddlProduct= (id.PRODUCTID).toString();
        $scope.temp.txtProductPrice= Number(id.PRICE);
        
        // $scope.GetPlanId = id.PLANID;


        $scope.editMode = true;
        $scope.index = $scope.post.getPlanProduct.indexOf(id);
    }

    /* ============ Clear Product Form =========== */ 
    $scope.clearProduct = function(){
        document.getElementById("ddlProduct").focus();
        $scope.temp.ddlProduct = '';
        $scope.temp.txtProductPrice = '';
        $scope.temp.planpid = 0;
        // $scope.editMode = false;
    }

    /* ========== DELETE Product =========== */
    $scope.deletePlanProduct = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'planpid': id.PLANPID, 'type': 'deletePlanProduct' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getPlanProduct.indexOf(id);
		            $scope.post.getPlanProduct.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

    /* ============================================ Products END ================================= */











    /* ============================================ Location START ================================= */
      $scope.saveLocation = function(){
        $(".btn-saveLocation").attr('disabled', 'disabled');
        // $(".btn-saveProduct").text('Saving...');
        $(".btn-updateLocation").attr('disabled', 'disabled');
        // $(".btn-update").text('Updating...');

        // alert($scope.GetPlanId);
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveLocation');
                formData.append("planlid", $scope.temp.planlid);
                formData.append("planid", $scope.GetPlanId);
                formData.append("ddlLocation", $scope.temp.ddlLocation);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.GET_LOCID=$scope.temp.ddlLocation;
                $scope.temp.planlid = data.data.GET_PLANLID;
                $scope.messageSuccess(data.data.message);
                // alert(data.data.PLANID);
                document.getElementById("txtPlan").focus();
                $scope.getPlanLocation();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveLocation').removeAttr('disabled');
            // $(".btn-saveProduct").text('SAVE');
            $('.btn-updateLocation').removeAttr('disabled');
            // $(".btn-update").text('UPDATE');
        });
    }


    /* ========== GET Location =========== */
    $scope.getLocation = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getLocation'}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getLocations = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getLocation(); --INIT
   
   
   
    /* ========== GET Plan Location =========== */
    $scope.getPlanLocation = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getPlanLocation','planid':$scope.GetPlanId}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getPlanLocations = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getPlanLocation();

    /* ============ Edit Button ============= */ 
    $scope.editPlanLocation = function (id) {
        $scope.GET_LOCID=0;
        $scope.post.getShedules=[];
        $scope.clearSechedule();
        
        document.getElementById("ddlLocation").focus();
        
        $scope.temp.planlid=id.PLANLID;
        $scope.temp.ddlLocation= (id.LOCATIONID).toString();
        $scope.GET_LOCID = id.LOCATIONID;
        

        $scope.editMode = true;
        $scope.index = $scope.post.getPlanLocations.indexOf(id);
        if($scope.GET_LOCID>0)$scope.getShedule();
    }
   

    /* ============ Clear Location Form =========== */ 
    $scope.clearLocation = function(){
        document.getElementById("ddlLocation").focus();
        $scope.temp.ddlLocation = '';
        $scope.temp.planlid = 0;

        $scope.post.getShedules=[];
        $scope.GET_LOCID=0;
        $scope.clearSechedule();
    }
    

    /* ========== DELETE Location =========== */
    $scope.deletePlanLocation = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'planlid': id.PLANLID, 'type': 'deletePlanLocation' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getPlanLocations.indexOf(id);
		            $scope.post.getPlanLocations.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }

    /* ============================================ Location END ================================= */


    






    /* ============================================ Schedule START ================================= */
    $scope.saveSchedule = function(){
        $(".btn-saveSchedule").attr('disabled', 'disabled');
        // $(".btn-saveProduct").text('Saving...');
        $(".btn-updateSchedule").attr('disabled', 'disabled');
        // $(".btn-update").text('Updating...');

        // alert($scope.TimeFormat($scope.temp.txtFromTime));
        $http({
            method: 'POST',
            url: url,
            processData: false,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("type", 'saveSchedule');
                formData.append("plansid", $scope.temp.plansid);
                formData.append("planid", $scope.GetPlanId);
                formData.append("locid", $scope.GET_LOCID);
                formData.append("ddlDay", $scope.temp.ddlDay);
                formData.append("txtFromTime", $scope.TimeFormat($scope.temp.txtFromTime));
                formData.append("txtToTime", $scope.TimeFormat($scope.temp.txtToTime));
                formData.append("txtFromDate", $scope.dateFormat($scope.temp.txtFromDate));
                formData.append("txtToDate", $scope.dateFormat($scope.temp.txtToDate));
                formData.append("txtRemark", $scope.temp.txtRemark);
                return formData;
            },
            data: $scope.temp,
            headers: { 'Content-Type': undefined }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            if (data.data.success) {
                $scope.messageSuccess(data.data.message);
                // alert(data.data.PLANID);
                document.getElementById("ddlDay").focus();
                $scope.getShedule();
                
            }
            else {
                $scope.messageFailure(data.data.message);
                // console.log(data.data)
            }
            $('.btn-saveSchedule').removeAttr('disabled');
            // $(".btn-saveProduct").text('SAVE');
            $('.btn-updateSchedule').removeAttr('disabled');
            // $(".btn-update").text('UPDATE');
        });
    }


    /* ========== GET Plan Schedule =========== */
    $scope.getShedule = function () {
        $http({
            method: 'post',
            url: url,
            data: $.param({ 'type': 'getShedule','planid':$scope.GetPlanId,'locid':$scope.GET_LOCID}),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).
        then(function (data, status, headers, config) {
            // console.log(data.data);
            $scope.post.getShedules = data.data.data;
        },
        function (data, status, headers, config) {
            console.log('Failed');
        })
    }
    // $scope.getShedule();

    // var today=new Date();
    // alert(today)
    // s=today.getHours()+':'+today.getMinutes();
    
    // // $scope.temp.txtFromTime=new Date(2015, 10, 10, 14, 57, 0);
    // $scope.temp.txtFromTime=new Date(0, 0, 0, today.getHours(),today.getMinutes());
     /* ============ Edit Button ============= */ 
     tarr=[];
     $scope.editPlanSchedule = function (id) {
         tarr=id.TIMEFROM_ET.split(':');
         tatr=id.TIMETO_ET.split(':');

        document.getElementById("ddlDay").focus();
        // $scope.temp = {
            $scope.temp.plansid=id.PLANSID;
            $scope.temp.ddlDay= id.WEEKDAYNAME;
            $scope.temp.txtFromTime= new Date(0,0,0,tarr[0],tarr[1]);
            $scope.temp.txtToTime= new Date(0,0,0,tatr[0],tatr[1]);
            $scope.temp.txtRemark= id.REMARKS;
        // };
        // alert(id.FROMDATE);
        if(id.FROMDATE_S != '01-01-1900'){
            $scope.temp.txtFromDate = new Date(id.FROMDATE);
        }else{$scope.temp.txtFromDate =''}

        if(id.TODATE_S != '01-01-1900'){
            $scope.temp.txtToDate = new Date(id.TODATE);
        }else{$scope.temp.txtToDate =''}

        $scope.editMode = true;
        $scope.index = $scope.post.getShedules.indexOf(id);
    }

    /* ============ Clear Sechedule Form =========== */ 
    $scope.clearSechedule = function(){
        document.getElementById("ddlDay").focus();
        $scope.temp.ddlDay = '';
        $scope.temp.txtFromTime = '';
        $scope.temp.txtToTime = '';
        // $scope.temp.txtFromDate = '';
        // $scope.temp.txtToDate = '';
        $scope.temp.txtRemark = '';
        $scope.temp.plansid = 0;
        tarr=[];
    }


    /* ========== DELETE Sechedule =========== */
    $scope.deletePlanSchedule = function (id) {
        var r = confirm("Are you sure want to delete this record!");
        if (r == true) {
            $http({
                method: 'post',
                url: url,
                data: $.param({ 'plansid': id.PLANSID, 'type': 'deletePlanSchedule' }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).
		    then(function (data, status, headers, config) {
                // console.log(data.data)
		        if (data.data.success) {
		            var index = $scope.post.getShedules.indexOf(id);
		            $scope.post.getShedules.splice(index, 1);
		            console.log(data.data.message)
                    
		            $scope.messageSuccess(data.data.message);
		        } else {
		            $scope.messageFailure(data.data.message);
		        }
		    })
        }
    }
    /* ============================================ Location END ================================= */


    /* ============================================ COPY PLAN START ================================= */
    $scope.copyPlans = function(id){
        var r = confirm("Are you sure want to copy this plan!");
        if (r == true) {
            $(".btn-copy").attr('disabled', 'disabled');
            $http({
                method: 'POST',
                url: url,
                processData: false,
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("type", 'copyPlans');
                    formData.append("PLANID", id.PLANID);
                    return formData;
                },
                data: $scope.temp,
                headers: { 'Content-Type': undefined }
            }).
            then(function (data, status, headers, config) {
                // console.log(data.data);
                if (data.data.success) {
                    $scope.messageSuccess(data.data.message);
                    $scope.getPlans();
                }
                else {
                    $scope.messageFailure(data.data.message);
                    // console.log(data.data)
                }
                $('.btn-copy').removeAttr('disabled');
            });
        }
    }
    /* ============================================ COPY PLAN END ================================= */

    
    


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

    // Plans
    // $scope.messageSuccess = function (msg) {
    //     jQuery('.alert-plan-s > span').html(msg);
    //     jQuery('.alert-plan-s').show();
    //     jQuery('.alert-plan-s').delay(5000).slideUp(function () {
    //         jQuery('.alert-plan-s > span').html('');
    //     });
    // }

    // $scope.messageFailure = function (msg) {
    //     jQuery('.alert-plan-d > span').html(msg);
    //     jQuery('.alert-plan-d').show();
    //     jQuery('.alert-plan-d').delay(5000).slideUp(function () {
    //         jQuery('.alert-plan-d > span').html('');
    //     });
    // }
    
    
    // Products
    // $scope.messageSuccess_Product = function (msg) {
    //     jQuery('.alert-product-s > span').html(msg);
    //     jQuery('.alert-product-s').show();
    //     jQuery('.alert-product-s').delay(5000).slideUp(function () {
    //         jQuery('.alert-product-s > span').html('');
    //     });
    // }

    // $scope.messageFailure_Product = function (msg) {
    //     jQuery('.alert-product-d > span').html(msg);
    //     jQuery('.alert-product-d').show();
    //     jQuery('.alert-product-d').delay(5000).slideUp(function () {
    //         jQuery('.alert-product-d > span').html('');
    //     });
    // }
    
    // Locations
    // $scope.messageSuccess_Locations = function (msg) {
    //     jQuery('.alert-location-s > span').html(msg);
    //     jQuery('.alert-location-s').show();
    //     jQuery('.alert-location-s').delay(5000).slideUp(function () {
    //         jQuery('.alert-location-s > span').html('');
    //     });
    // }

    // $scope.messageFailure_Locations = function (msg) {
    //     jQuery('.alert-location-d > span').html(msg);
    //     jQuery('.alert-location-d').show();
    //     jQuery('.alert-location-d').delay(5000).slideUp(function () {
    //         jQuery('.alert-location-d > span').html('');
    //     });
    // }
    
    
    
    // Schedules
    // $scope.messageSuccess_Schedules = function (msg) {
    //     jQuery('.alert-schedule-s > span').html(msg);
    //     jQuery('.alert-schedule-s').show();
    //     jQuery('.alert-schedule-s').delay(5000).slideUp(function () {
    //         jQuery('.alert-schedule-s > span').html('');
    //     });
    // }

    // $scope.messageFailure_Schedules = function (msg) {
    //     jQuery('.alert-schedule-d > span').html(msg);
    //     jQuery('.alert-schedule-d').show();
    //     jQuery('.alert-schedule-d').delay(5000).slideUp(function () {
    //         jQuery('.alert-schedule-d > span').html('');
    //     });
    // }




});