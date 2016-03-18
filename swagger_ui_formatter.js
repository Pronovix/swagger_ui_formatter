/**
 * @file
 * Swagger UI custom javascript.
 */

window.SwaggerUiList = [];
    (function($) {
      Drupal.behaviors.swagger_ui_formatter = {
        attach: function(context, settings) {
          var swagger_files = Drupal.settings.swagger_ui_formatter.swagger_files;
          var docExpansion = Drupal.settings.swagger_ui_formatter.doc_expansion;
          var validatorUrl = Drupal.settings.swagger_ui_formatter.validator_url;
          var showRequestHeaders = Drupal.settings.swagger_ui_formatter.show_request_headers;
          var options = {
            docExpansion: docExpansion,
            showRequestHeaders: showRequestHeaders
          };
          if (validatorUrl !== false) {
            options.validatorUrl = validatorUrl;
          }
          var swagger_ui = [];
          for (var i = 0; i < swagger_files.length; i++) {
            options.url = swagger_files[i].url;
            options.dom_id = "swagger-ui-container-" + i;
            swagger_ui[i] = new SwaggerUi(options);
            window.SwaggerUiList[i] = swagger_ui[i];
          }
          for (var i = 0; i < swagger_ui.length; i++) {
            swagger_ui[i].load();
          }
        }
      }

    })(jQuery);
