describe('Flujo completo de usuario', () => {
  it('Inicia sesión, agrega al carrito y a favoritos', () => {
    cy.visit('/')

    cy.visit('/login')

    cy.get('input[name="email"]').type('andrescarranza8281@gmail.com')
    cy.get('input[name="password"]').type('12345678')
    cy.get('button[type="submit"]').first().click()

    cy.url().should('not.include', '/login')

    cy.visit('/catalog')

    cy.get('a.catalog-card').first().click()

    cy.get('h1.product-title').should('be.visible')
    cy.get('span.current-price').should('be.visible')

    cy.get('.product-actions .btn-primary').click()
    cy.get('.aroma-notification').should('be.visible')

    cy.visit('/cart')
    cy.get('#cartItemsContainer .cart-item-row').should('have.length.at.least', 1)

    cy.go('back')

    cy.get('#wishlistBtn').click()

    cy.visit('/favorites')
    cy.get('#favoritesGrid .catalog-card').should('have.length.at.least', 1)
  })
})
