/**
 * @file
 * Custom scripts to render file fields with Swagger UI.
 */

(function ($, window, Drupal, drupalSettings) {

  Drupal.behaviors.swaggerUIFormatter = {
    attach: function(context) {
      // Iterate over fields and render each field item with Swagger UI.
      for (var fieldName in drupalSettings.swaggerUIFormatter) {
        if (drupalSettings.swaggerUIFormatter.hasOwnProperty(fieldName)) {
          var field = drupalSettings.swaggerUIFormatter[fieldName];
          for (var fieldDelta = 0; fieldDelta < field.swaggerFiles.length; fieldDelta++) {
            // Do not instantiate/re-render Swagger UI if it has been done
            // before (avoid re-rendering on AJAX requests for example).
            if ('swagger_ui_' + fieldName + '_' + fieldDelta in window) {
              continue;
            }

            // Add SVG definition to the DOM (old Swagger UI requirement).
            if (field.svgDefinition) {
              $('body', context).once('swagger-ui-svg-definition').prepend(field.svgDefinition);
            }

            var validatorUrl = undefined;
            switch (field.validator) {
              case 'custom':
                validatorUrl = field.validatorUrl;
                break;

              case 'none':
                validatorUrl = null;
                break;
            }

            var options = {
              url: field.swaggerFiles[fieldDelta],
              dom_id: '#swagger-ui-' + fieldName + '-' + fieldDelta,
              deepLinking: true,
              presets: [
                SwaggerUIBundle.presets.apis,
                // This is a dirty hack but it works out of the box.
                // See https://github.com/swagger-api/swagger-ui/issues/3229.
                field.showTopBar ? SwaggerUIStandalonePreset : SwaggerUIStandalonePreset.slice(1)
              ],
              plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
              ],
              validatorUrl: validatorUrl,
              docExpansion: field.docExpansion,
              layout: "StandaloneLayout",
              tagsSorter: field.sortTagsByName ? 'alpha' : '',
              supportedSubmitMethods: field.supportedSubmitMethods
            };

            // Allow altering the options.
            $(window).trigger('swaggerUIFormatterOptionsAlter', options);

            window['swagger_ui_' + fieldName + '_' + fieldDelta] = SwaggerUIBundle(options);
          }
        }
      }
    }
  };

}(jQuery, window, Drupal, drupalSettings));
