/**
 * @file
 * Custom scripts to render file fields with Swagger UI.
 */

(function ($, window, Drupal, drupalSettings, once) {

  Drupal.behaviors.swaggerUIFormatter = {
    attach: function (context) {
      once('swaggerUIFormatter', '[id^="swagger-ui-"]', context).forEach(() => {
        // Iterate over field values and render each field value with Swagger UI.
        for (const fieldNamePlusDelta of Object.keys(drupalSettings.swaggerUIFormatter)) {
          const fieldElementInField = drupalSettings.swaggerUIFormatter[fieldNamePlusDelta];
          // Do not instantiate/re-render Swagger UI if it has been done
          // before (avoid re-rendering on AJAX requests for example).
          if ('swagger_ui_' + fieldNamePlusDelta in window) {
            continue;
          }

          let validatorUrl = undefined;
          switch (fieldElementInField.validator) {
            case 'custom':
              validatorUrl = fieldElementInField.validatorUrl;
              break;

            case 'none':
              validatorUrl = null;
              break;
          }
          const options = {
            url: fieldElementInField.swaggerFile,
            dom_id: '#swagger-ui-' + fieldNamePlusDelta,
            deepLinking: true,
            plugins: [
              SwaggerUIBundle.plugins.DownloadUrl
            ],
            presets: [
              SwaggerUIBundle.presets.apis,
              fieldElementInField.showTopBar ? SwaggerUIStandalonePreset : SwaggerUIStandalonePreset.slice(1)
            ],
            layout: "StandaloneLayout",
            validatorUrl: validatorUrl,
            docExpansion: fieldElementInField.docExpansion,
            tagsSorter: fieldElementInField.sortTagsByName ? 'alpha' : null,
            supportedSubmitMethods: fieldElementInField.supportedSubmitMethods,
            oauth2RedirectUrl: fieldElementInField.oauth2RedirectUrl
          };

          // Allow altering the options.
          $(window).trigger('swaggerUIFormatterOptionsAlter', options);

          window['swagger_ui_' + fieldNamePlusDelta] = SwaggerUIBundle(options);
        }
      });
    }
  };

}(jQuery, window, Drupal, drupalSettings, once));
