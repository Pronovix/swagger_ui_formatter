/**
 * @file
 * Custom scripts to render file fields with Swagger UI.
 */

(function ($, window, Drupal, drupalSettings) {

  Drupal.behaviors.swaggerUIFormatter = {
    attach: function (context) {
      // Iterate over field values and render each field value with Swagger UI.
      for (var fieldNamePlusDelta in drupalSettings.swaggerUIFormatter) {
        if (drupalSettings.swaggerUIFormatter.hasOwnProperty(fieldNamePlusDelta)) {
          var fieldElementInField = drupalSettings.swaggerUIFormatter[fieldNamePlusDelta];
          // Do not instantiate/re-render Swagger UI if it has been done
          // before (avoid re-rendering on AJAX requests for example).
          if ('swagger_ui_' + fieldNamePlusDelta in window) {
            continue;
          }

          // Add SVG definition to the DOM (old Swagger UI requirement).
          if (fieldElementInField.svgDefinition) {
            $('body', context).once('swagger-ui-svg-definition').prepend(fieldElementInField.svgDefinition);
          }

          var validatorUrl = undefined;
          switch (fieldElementInField.validator) {
            case 'custom':
              validatorUrl = fieldElementInField.validatorUrl;
              break;

            case 'none':
              validatorUrl = null;
              break;
          }

          var options = {
            // For BC, we kept the array instead of a single value.
            url: fieldElementInField.swaggerFiles[0],
            dom_id: '#swagger-ui-' + fieldNamePlusDelta,
            deepLinking: true,
            presets: [
              SwaggerUIBundle.presets.apis,
              // This is a dirty hack but it works out of the box.
              // See https://github.com/swagger-api/swagger-ui/issues/3229.
              fieldElementInField.showTopBar ? SwaggerUIStandalonePreset : SwaggerUIStandalonePreset.slice(1)
            ],
            plugins: [
              SwaggerUIBundle.plugins.DownloadUrl
            ],
            validatorUrl: validatorUrl,
            docExpansion: fieldElementInField.docExpansion,
            layout: "StandaloneLayout",
            tagsSorter: fieldElementInField.sortTagsByName ? 'alpha' : '',
            supportedSubmitMethods: fieldElementInField.supportedSubmitMethods
          };

          if (fieldElementInField.oauth2RedirectUrl) {
            options.oauth2RedirectUrl = fieldElementInField.oauth2RedirectUrl;
          }

          // Allow altering the options.
          $(window).trigger('swaggerUIFormatterOptionsAlter', options);

          window['swagger_ui_' + fieldNamePlusDelta] = SwaggerUIBundle(options);
        }
      }
    }
  };

}(jQuery, window, Drupal, drupalSettings));
