/**
 * @file
 * Custom scripts to render file fields with Swagger UI.
 */

(function ($, window, Drupal, once) {

  Drupal.behaviors.swaggerUIFormatter = {
    attach: function (context) {
      once('swaggerUIFormatter', '.swagger-ui-formatter-element', context).forEach((element) => {
        const domId = element.id;
        const fieldElementInField = JSON.parse(element.getAttribute('data-swagger-settings'));
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
          dom_id: '#' + domId,
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

        window['swagger_ui_' + domId] = SwaggerUIBundle(options);
      });
    }
  };

}(jQuery, window, Drupal, once));
