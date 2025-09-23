/**
 * @file Exposes the Swagger UI bundle initialization function on the global window object.
 * @internal
 */

import 'swagger-ui-dist/swagger-ui.css';
import { SwaggerUIBundle } from 'swagger-ui-dist';

window.SwaggerUIBundle = SwaggerUIBundle;
