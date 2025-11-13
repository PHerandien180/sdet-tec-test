describe('Shopping Flow E2E Tests', () => {
  beforeEach(() => {
    cy.visit('/') //Resets state before each test
  })

  it('should allow user to browse and add products to cart', () => {
    cy.get('[data-testid="login-button"]').click()
    
    cy.get('input[type="email"]').type('test@example.com')
    cy.get('input[type="password"]').type('password')
    cy.get('button[type="submit"]').click()
    
    cy.url().should('include', '/products')
    cy.get('.product-card').should('have.length.greaterThan', 0)
    
    cy.get('.product-card').first().within(() => {
      cy.get('[data-testid="add-to-cart-button"]').click()
    })
    
    cy.get('[data-testid="cart-button"]').should('contain', '1')

    //ISSUE : Logic logic is repeated in many tests , Should use a customn command to avoid duplicates
  })

  it('should update cart total when items are added', () => {
    cy.login('test@example.com', 'password') // Like this one Custom Command
    
    cy.visit('/products')
    
    cy.get('.product-card').first().click()
    
    cy.get('[data-testid="cart-button"]').click()
    
    cy.get('.cart-total').should('contain', '$')

    //ISSUE: Does not assert total value , only expects presence of $
  })

  it('should show correct product details', () => {
    cy.visit('/products/1') 
    
    cy.contains('Wireless Headphones').should('be.visible') 
    cy.contains('$199.9900').should('be.visible') //ISSUe format price seems to be incorrect
    cy.contains('50').should('be.visible') 
  })

  it('should complete checkout process', () => {
    cy.login('test@example.com', 'password')
    cy.addProductToCart(1, 1)
    
    cy.get('[data-testid="cart-button"]').click()
    cy.get('[data-testid="checkout-button"]').click()
    
    cy.url().should('include', '/orders')
  })

  it('should filter products by category', () => {
    cy.visit('/products')
    
    cy.get('.v-select > .v-input__control > .v-input__slot').click()
    cy.get('.v-menu__content .v-list-item').contains('Electronics').click()
    
    cy.get('.product-card').should('exist')
  })

  it('should handle user registration', () => {
    cy.visit('/register')
    
    cy.get('input[name="firstName"]').type('John')
    cy.get('input[name="lastName"]').type('Doe')
    cy.get('input[name="email"]').type('john@test.com')
    cy.get('input[name="password"]').type('password123')
    
    cy.get('button[type="submit"]').click()
    
  })

  it('should handle concurrent cart updates', () => {
    cy.login('test@example.com', 'password')
    
    cy.addProductToCart(1, 1)
    cy.addProductToCart(2, 2)
    cy.addProductToCart(3, 1)
    
    cy.get('[data-testid="cart-button"]').should('contain', '4')
  })

  it('should add out of stock product to cart', () => {
    cy.login('test@example.com', 'password')
    cy.visit('/products')
    
    cy.get('.product-card').contains('Out of stock').parent().within(() => {
      cy.get('[data-testid="add-to-cart-button"]').click()
    })
    
  })

  it('should search for products', () => {
    cy.visit('/products')
    
    cy.get('[data-testid="search-input"]').type('laptop')
    
    cy.wait(2000) // ISSUE Hard coded wait should use cy.intercept
    
    cy.get('.product-card').should('contain', 'Laptop')
    
    cy.wait(1000) //Another Hard coded wait
  })

  it('should navigate through product pages', () => {
    cy.visit('/products')
    
    cy.get('.v-data-table__wrapper > table > tbody > tr:first-child > td:first-child > a').click()
    
    cy.url().should('match', /\/products\/\d+$/)
    
    cy.get('.v-toolbar > .v-toolbar__content > .v-btn:first-child').click()

    //ISSUE Navigation asserions is weak , should assert page content after navigating back
  })
})
