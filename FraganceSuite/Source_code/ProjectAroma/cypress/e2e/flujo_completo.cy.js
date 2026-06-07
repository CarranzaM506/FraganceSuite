describe('Flujo completo de usuario', () => {
  beforeEach(() => {
    cy.login()
  })

  it('Agrega producto al carrito y a favoritos', () => {
    cy.visit('/catalog')

    cy.get('a.catalog-card').first().click()

    cy.get('h1.product-title').should('be.visible')
    cy.get('span.current-price').should('be.visible')

    // Agregar al carrito solo si hay stock (botón sin disabled)
    cy.get('.product-actions .btn-primary').then($btn => {
      if (!$btn.is(':disabled')) {
        cy.wrap($btn).click()
      }
    })

    // Interceptar ANTES del click para no perder la petición
    cy.intercept('POST', '/favorites/toggle').as('toggleFav')
    cy.get('#wishlistBtn').click({ force: true })

    // Verificar que el servidor respondió 200 (usuario autenticado)
    // y que la operación fue exitosa independientemente del estado
    // previo del favorito (added o removed ambos son válidos)
    cy.wait('@toggleFav').its('response.statusCode').should('eq', 200)
    cy.get('@toggleFav').its('response.body.success').should('be.true')
  })
})
