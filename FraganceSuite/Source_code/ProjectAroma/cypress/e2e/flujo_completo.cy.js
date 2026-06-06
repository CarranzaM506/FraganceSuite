describe('Flujo completo de usuario', () => {
  function handleProduct(index) {
    cy.get('a.catalog-card').eq(index).click()
    cy.get('h1.product-title').should('be.visible')
    cy.get('span.current-price').should('be.visible')

    cy.get('.product-actions .btn-primary').then($btn => {
      if ($btn.is(':disabled')) {
        cy.go('back')
        handleProduct(index + 1)
      } else {
        cy.url().as('productUrl')

        cy.wrap($btn).click()
        cy.get('.aroma-notification').should('be.visible')

        cy.visit('/cart')
        cy.get('#cartItemsContainer .cart-item-row', { timeout: 8000 }).should('have.length.at.least', 1)

        cy.get('@productUrl').then(url => cy.visit(url))
        cy.get('#wishlistBtn').click()

        cy.visit('/favorites')
        cy.get('#favoritesGrid .catalog-card').should('have.length.at.least', 1)
      }
    })
  }

  it('Inicia sesión, agrega al carrito y a favoritos', () => {
    cy.visit('/')

    cy.visit('/login')

    cy.get('input[name="email"]').type('andrescarranza8281@gmail.com')
    cy.get('input[name="password"]').type('12345678')
    cy.get('button[type="submit"]').first().click({ force: true })

    cy.url().should('not.include', '/login')

    cy.visit('/catalog')
    handleProduct(0)
  })
})
