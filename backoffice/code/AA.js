
$postModule = angular.module("myApp", ["ngSanitize"]);

$postModule.controller("myCtrl", function ($scope, $http,$interval,$timeout) {
    $scope.post = {};
    $scope.temp = {};
    $scope.editMode = false;
    $scope.Page = "SETTING";
    $scope.PageSub = "TERMS";
    // $scope.correctAnswer='';
    $scope.datalist = [
        {
            "TSQID": 3839,
            "QUEID": 896,
            "QUEIMAGE": null,
            "QUESTION": "SAT 1 - R - Q1",
            "QUETYPE": "MCQ",
            "QUEOPTIONS": "A;#;,B;#;,C;#;,D",
            "CORRECTANSWER": "B",
            "ALLOWEDCALC": 0,
            "STID": 6579,
            "STUDENTANS": "",
            "QUEOPTIONS_LIST": ["A","B","C","D"],
        },
        {
            "TSQID": 3840,
            "QUEID": 897,
            "QUEIMAGE": null,
            "QUESTION": "SAT 1 - R - Q2",
            "QUETYPE": "MCQ",
            "QUEOPTIONS": "A;#;,B;#;,C;#;,D",
            "CORRECTANSWER": "B",
            "ALLOWEDCALC": 0,
            "STID": 6579,
            "STUDENTANS": "",
            "QUEOPTIONS_LIST": ["A","B","C","D"],
        },
        {
            "TSQID": 3841,
            "QUEID": 898,
            "QUEIMAGE": null,
            "QUESTION": "SAT 1 - R - Q3",
            "QUETYPE": "MCQ",
            "QUEOPTIONS": "A;#;,B;#;,C;#;,D ",
            "CORRECTANSWER": "C",
            "ALLOWEDCALC": 0,
            "STID": 6579,
            "STUDENTANS": "",
            "QUEOPTIONS_LIST": ["A","B","C","D"],
        },
        {
            "TSQID": 3842,
            "QUEID": 899,
            "QUEIMAGE": null,
            "QUESTION": "SAT 1 - R - Q4",
            "QUETYPE": "MCQ",
            "QUEOPTIONS": "A;#;,B;#;,C;#;,D",
            "CORRECTANSWER": "A",
            "ALLOWEDCALC": 0,
            "STID": 6579,
            "STUDENTANS": "",
            "QUEOPTIONS_LIST": ["A","B","C","D"],
        },
    ]
    

    $scope.crosQuestion = function(pidx,idx){
        // console.log(pidx+'/'+idx);
        $('#optLabel'+pidx+''+idx).removeClass('active').toggleClass('line-through');
        var prevLineThrough = $('#optLabel'+pidx+''+idx).hasClass('line-through');
        if (prevLineThrough) {
          $('.cros'+pidx+''+idx).removeClass('fa-times text-dark').addClass('fa-undo text-danger');
          $('.cros'+pidx+''+idx).prev().children().attr('disabled','disabled');
        }else{
          $('.cros'+pidx+''+idx).removeClass('fa-undo text-danger').addClass('fa-times text-dark');
          $('.cros'+pidx+''+idx).prev().children().removeAttr('disabled');
        }
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

                if($scope.userrole != "ADMINISTRATOR" && $scope.userrole != "SUPERADMIN")
                {
                    window.location.assign("dashboard.html#!/dashboard");
                }
                else{
                   
                }
                
            }else{

                // window.location.assign('index.html#!/login')
                $scope.logout();
            }
            
        },
        function (data, status, headers, config) {
            
            //console.log(data)
            console.log('Failed');
        })

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