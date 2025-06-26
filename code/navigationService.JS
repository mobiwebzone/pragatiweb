angular.module('myApp')
   .service('NavigationService', function ($http, $q) {
      var service = this;
      service.navigationItems = [];
      // Use a flag to check if data has been fetched
      var dataFetched = false;
    
      // Define a function to fetch navigation data from PHP
      service.getNavigationData   = function () {
         if (!dataFetched) {
            return $http({
               method: 'POST',
               url: 'code/index1.php',
               processData: false,
               transformRequest: function (data) {
                  var formData = new FormData();
                  formData.append("type", 'getHomeMenu');
                  return formData;
               },
               headers: { 'Content-Type': undefined }
            }).then(function (response) {
                console.log(response.data);
                service.navigationItems = response.data.success ? response.data.data : [];
                dataFetched = true;
            });
            // return $http.post('index1.php').then(function (response) {
            //     // Assuming the response contains an array of navigation items
            //     service.navigationItems = response.data.data;
            //     dataFetched = true; // Set the flag to true after fetching data
            //  });
         }else {
            // If data is already fetched, resolve with the existing data
            return $q.resolve(service.navigationItems);
         }
      };

   });
