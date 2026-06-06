describe('Flujo completo de usuario', () => {
  it('Inicia sesión y verifica un producto del catálogo', () => {
    cy.visit('/')

    cy.visit('/login')

    cy.get('input[name="email"]').type('andrescarranza8281@gmail.com')
    cy.get('input[name="password"]').type('12345678')
    cy.get('button[type="submit"]').click()

    cy.url().should('not.include', '/login')

    cy.contains('a', /catálogo|catalogo|productos/i).click()

    cy.get('a').filter(':visible').first().click()

    cy.get('h1, h2').should('be.visible')
    cy.contains(/\$|₡|precio/i).should('be.visible')
  })
})
