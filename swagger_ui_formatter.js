/**
 * @file
 * Swagger UI custom javascript.
 */

    (function($) {
      Drupal.behaviors.swagger_ui_formatter = {
        attach: function(context, settings) {
          var swagger_files = Drupal.settings.swagger_ui_formatter.swagger_files;
          var swagger_ui = [];
          for (var i = 0; i < swagger_files.length; i++) {
            swagger_ui[i] = new SwaggerUi({
              url: swagger_files[i].url,
              dom_id: "swagger-ui-container-" + i
            });
          }
          for (var i = 0; i < swagger_ui.length; i++) {
            swagger_ui[i].load();
          }
        }
      }

    })(jQuery);
