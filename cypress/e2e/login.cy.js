describe('Login Flow', () => {
  it('should display login page', () => {
    cy.visit('/login');
    cy.contains('Sign in to your account');
  });

  it('should show error on invalid credentials', () => {
    cy.visit('/login');
    
    cy.get('input[name="email"]').type('invalid@example.com');
    cy.get('input[name="password"]').type('wrongpassword');
    cy.get('button[type="submit"]').click();

    cy.contains('Invalid credentials').should('be.visible');
  });

  it('should login successfully with valid credentials', () => {
    // Ideally, we should seed the database before this test
    // For now, we assume a user exists or we can mock the API response
    
    // Mocking the API response
    cy.intercept('POST', '/api/auth/login', {
      statusCode: 200,
      body: {
        success: true,
        data: {
          token: 'fake-token',
          user: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
            role: { role_name: 'User' }
          }
        }
      }
    }).as('loginRequest');

    cy.intercept('GET', '/api/auth/me', {
      statusCode: 200,
      body: {
        success: true,
        data: {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
           role: { role_name: 'User' }
        }
      }
    }).as('meRequest');

    cy.visit('/login');
    
    cy.get('input[name="email"]').type('test@example.com');
    cy.get('input[name="password"]').type('password');
    cy.get('button[type="submit"]').click();

    cy.wait('@loginRequest');
    
    // Should redirect to dashboard
    cy.url().should('include', '/dashboard');
  });
});
