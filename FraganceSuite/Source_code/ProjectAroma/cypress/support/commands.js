Cypress.Commands.add('login', (
  email = 'andrescarranza8281@gmail.com',
  password = '12345678'
) => {
  cy.session([email, password], () => {
    // GET /login para obtener el CSRF token antes de hacer POST
    cy.request('/login').then(({ body }) => {
      const match = body.match(/name="_token"\s+value="([^"]+)"/)
      const token = match ? match[1] : ''

      cy.request({
        method: 'POST',
        url: '/login',
        form: true,
        body: { email, password, _token: token },
        followRedirect: true,
        failOnStatusCode: false
      })
    })
  }, {
    cacheAcrossSpecs: true,
    validate () {
      // Si la sesión expiró en el servidor, esto falla y fuerza re-login
      cy.request({ url: '/api/cart', failOnStatusCode: false })
        .its('status').should('eq', 200)
    }
  })
})
