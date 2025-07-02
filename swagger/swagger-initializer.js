window.onload = function() {
  // Obter o domínio atual do navegador
  const domain = `${window.location.protocol}//${window.location.host}`;

  // Configuração do Swagger
  window.ui = SwaggerUIBundle({
    url: `${domain}/api/openapi.json`,
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });
};
