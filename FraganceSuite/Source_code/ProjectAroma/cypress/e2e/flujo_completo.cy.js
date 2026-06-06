describe('Flujo completo de usuario', () => {
  beforeEach(() => {
    cy.session('user-session', () => {
      cy.visit('/login')
      cy.get('input[name="email"]').type('andrescarranza8281@gmail.com')
      cy.get('input[name="password"]').type('12345678')
      cy.get('button[type="submit"]').first().click({ force: true })
      cy.url().should('not.include', '/login')
    })
  })

  it('Agrega al carrito y a favoritos', () => {
    cy.visit('/catalog')

    cy.get('a.catalog-card').first().click()

    cy.get('h1.product-title').should('be.visible')
    cy.get('span.current-price').should('be.visible')

    // Agregar al carrito solo si hay stock
    cy.get('.product-actions .btn-primary').then($btn => {
      if (!$btn.is(':disabled')) {
        cy.wrap($btn).click()
      }
    })

    // Agregar a favoritos e interceptar respuesta
    cy.intercept('POST', '/favorites/toggle').as('toggleFav')
    cy.get('#wishlistBtn').click({ force: true })
    cy.wait('@toggleFav').its('response.statusCode').should('eq', 200)
    cy.get('@toggleFav').its('response.body.status').should('eq', 'added')
  })
})
