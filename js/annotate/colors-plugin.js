Annotator.Plugin.StoreLogger = function (element) {
    return {
      pluginInit: function () {
        this.annotator
            .subscribe("annotationCreated", function (annotation) {
              console.info("The annotation: %o has just been created!", annotation)

              // var randomHue = Math.floor(Math.random() * 360); // Generate a random hue value between 0 and 360
              // $('.annotator-hl').last().css('background-color', 'hsl(' + randomHue + ', 70%, 80%)');
              // $(this).find('.annotator-hl').addClass('text-danger');
            })
            .subscribe("annotationUpdated", function (annotation) {
              console.info("The annotation: %o has just been updated!", annotation)
            })
            .subscribe("annotationDeleted", function (annotation) {
              console.info("The annotation: %o has just been deleted!", annotation)
            }).
            subscribe("annotationEditorShown",function (viewer,annotation){
              var self = this;
              var colors = ["#ffff0063", "#00ff1a6b", "#fbaf8778","#ff7bc878","#7bf6ff78","#9b7bff78","#97d5ff78","#ffc10778"];
              // var listItem = '<li class="annotator-item d-flex">';
              if($('.annotator-listing').find('.color-option0').length==0){
                var list = $('<li>').addClass('annotator-item d-flex colorList px-2 py-3 justify-content-center');
                $('.annotator-listing').last().append(list);
              }
              colors.forEach(function(color,key) {
                // console.log(key);
                listItem = $("<div>")
                  .addClass("border border-secondary color-option"+key)
                  .data("color", color)
                  .css({"background-color": color,"width":"30px","height":"20px","gap":"5px"})
                  .click(function() {
                    
                    // Event handler for when a color option is clicked
                    var selectedColor = $(this).data("color");
                    console.log("Selected color:", selectedColor);
                    $(self).find('.annotator-hl-temporary').css('background-color', selectedColor);
                  });
              // var newItem = $("<li>").text("New Item");
                
                console.log($('.annotator-listing').find('.color-option'+key).length);
                if($('.annotator-listing').find('.color-option'+key).length==0){
                  $('.annotator-listing li').last().append(listItem);

                }
                
              });
          
            });
      }
    }
  }