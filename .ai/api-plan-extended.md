# Extended API and Web Documentation for 10xCards

## Architectural Overview
The application is split into two main parts:

- **API Controllers**: Located in `/src/Controller/Api/`, these controllers expose RESTful endpoints that return JSON responses. They are designed for programmatic consumption by client applications.

- **Web Controllers**: Located in `/src/Controller/Web/`, these controllers render HTML views using Twig templates. They provide traditional web pages for listing, viewing details, creating, editing, and deleting resources, including form handling and redirections.

This separation ensures a clear division between the API (backend data access) and the Web (user interface) layers, following best practices in Symfony development.

---

## API Endpoints (REST, JSON)

### A. Authentication and User Management
1. **User Registration**
   - **Method:** POST
   - **URL:** `/api/auth/register`
   - **Description:** Register a new user with email and password.
   - **Request Payload:**
     ```json
     {
       "email": "user@example.com",
       "password": "SecurePassword123"
     }
     ```
   - **Response:** 201 Created with user details (excluding password).
   - **Errors:** 400 Bad Request if validation fails.

2. **User Login**
   - **Method:** POST
   - **URL:** `/api/auth/login`
   - **Description:** Authenticate and return a JWT token.
   - **Request Payload:**
     ```json
     {
       "email": "user@example.com",
       "password": "SecurePassword123"
     }
     ```
   - **Response:** 200 OK with JWT token.
   - **Errors:** 401 Unauthorized if credentials are invalid.

3. **Password Reset**
   - **Method:** POST
   - **URL:** `/api/auth/forgot-password`
   - **Description:** Initiate a password reset procedure by sending a reset link to the user's email.
   - **Request Payload:**
     ```json
     { "email": "user@example.com" }
     ```
   - **Response:** 200 OK with a message indicating the reset link has been sent.

### B. Deck Management
1. **List Decks**
   - **Method:** GET
   - **URL:** `/api/decks`
   - **Description:** Retrieve a paginated list of decks belonging to the authenticated user.
   - **Response Example:**
     ```json
     {
       "data": [{ "id": 1, "name": "Deck Name", "description": "...", "created_at": "..." }],
       "pagination": { "page": 1, "limit": 10, "total": 25 }
     }
     ```

2. **Create Deck**
   - **Method:** POST
   - **URL:** `/api/decks`
   - **Description:** Create a new deck for the user.
   - **Request Payload:**
     ```json
     {
       "name": "New Deck",
       "description": "Deck description"
     }
     ```
   - **Response:** 201 Created with deck details.
   - **Errors:** 400 Bad Request if name exceeds 100 characters or description exceeds 1000 characters.

3. **Update Deck**
   - **Method:** PUT/PATCH
   - **URL:** `/api/decks/{deckId}`
   - **Description:** Update deck details (name, description).
   - **Request Payload:**
     ```json
     { "name": "Updated Deck Name", "description": "Updated description" }
     ```
   - **Response:** 200 OK with updated deck details.

4. **Delete Deck**
   - **Method:** DELETE
   - **URL:** `/api/decks/{deckId}`
   - **Description:** Soft-delete a deck (sets is_deleted flag).
   - **Response:** 204 No Content.

5. **Reset Deck Progress**
   - **Method:** POST
   - **URL:** `/api/decks/{deckId}/reset-progress`
   - **Description:** Reset progress for all cards within the deck.
   - **Response:** 200 OK with confirmation message.

### C. Card Management
1. **List Cards**
   - **Method:** GET
   - **URL:** `/api/cards`
   - **Description:** Retrieve a paginated list of cards, with optional filtering (e.g., due today, overdue).
   - **Response Example:**
     ```json
     {
       "data": [{ "id": 10, "deck_id": 1, "front": "Front text", "back": "Back text", "due_date": "..." }],
       "pagination": { "page": 1, "limit": 10, "total": 50 }
     }
     ```

2. **Create Card (Manual)**
   - **Method:** POST
   - **URL:** `/api/cards`
   - **Description:** Create a single card manually with front and back texts.
   - **Request Payload:**
     ```json
     {
       "deck_id": 1,
       "front": "Question?",
       "back": "Answer.",
       "source": "manual"
     }
     ```
   - **Response:** 201 Created with card details.
   - **Errors:** 400 Bad Request if front > 200 characters or back > 1000 characters.

3. **Update Card**
   - **Method:** PUT/PATCH
   - **URL:** `/api/cards/{cardId}`
   - **Description:** Update the content of a card.
   - **Request Payload:**
     ```json
     { "front": "Updated front", "back": "Updated back" }
     ```
   - **Response:** 200 OK with updated card details.

4. **Delete Card**
   - **Method:** DELETE
   - **URL:** `/api/cards/{cardId}`
   - **Description:** Soft-delete a card.
   - **Response:** 204 No Content.

5. **Bulk Delete Cards**
   - **Method:** DELETE
   - **URL:** `/api/cards`
   - **Description:** Delete multiple cards by providing an array of card IDs.
   - **Request Payload:**
     ```json
     { "ids": [10, 11, 12] }
     ```
   - **Response:** 204 No Content.

### D. AI Flashcards Generation
1. **Generate Flashcards via AI**
   - **Method:** POST
   - **URL:** `/api/ai/generate`
   - **Description:** Generate flashcards based on pasted text (maximum 10,000 characters; max 5 requests/min).
   - **Request Payload:**
     ```json
     { "input_text": "Your long text here..." }
     ```
   - **Response:** 200 OK with a list of generated flashcards, AI job details, and statistics.
   - **Errors:** 400 Bad Request if text exceeds limit or rate limit is reached.

2. **Manage Generated Flashcards**
   - **a. View Generated Flashcards**
     - **Method:** GET
     - **URL:** `/api/ai/jobs/{jobId}/flashcards`
     - **Description:** Retrieve flashcards generated for a specific AI job.
   - **b. Update Flashcard Status (Accept/Edit/Reject)**
     - **Method:** PATCH
     - **URL:** `/api/ai/flashcards/{flashcardId}`
     - **Description:** Update the status of a generated flashcard. Optionally modify content if editing.
     - **Request Payload:**
       ```json
       { "status": "accepted", "edited_front": null, "edited_back": null }
       ```
     - **Response:** 200 OK with updated flashcard details.
   - **c. Bulk Save Accepted Flashcards to a Deck**
     - **Method:** POST
     - **URL:** `/api/ai/jobs/{jobId}/bulk-save`
     - **Description:** Persist all flashcards marked as accepted into a designated deck.
     - **Request Payload:**
       ```json
       { "deck_id": 1 }
       ```

### E. Spaced Repetition (SR) Session Endpoints
1. **Start SR Session**
   - **Method:** GET
   - **URL:** `/api/srs/session`
   - **Description:** Retrieve the next due flashcard for a spaced repetition session, optionally filtered by deck.
   - **Response Example:**
     ```json
     { "card": { "id": 20, "front": "...", "back": "...", "due_date": "..." } }
     ```

2. **Submit SR Answer**
   - **Method:** POST
   - **URL:** `/api/srs/session/answer`
   - **Description:** Submit answer result (e.g., known, unknown, postpone) to update card scheduling details.
   - **Request Payload:**
     ```json
     { "card_id": 20, "result": "known" }
     ```
   - **Response:** 200 OK with updated scheduling details.

### F. Admin Endpoints (Protected: Admin Role Only)
1. **Manage Users**
   - **Method:** GET
   - **URL:** `/api/admin/users`
   - **Description:** Retrieve a list of users along with account statuses and KPIs.

2. **Lock/Unlock or Delete User Account**
   - **Method:** PATCH/DELETE
   - **URL:** `/api/admin/users/{userId}`
   - **Description:** Modify user account status (e.g., lock after failed logins, delete account).
   - **Response:** 200 OK with status message.

3. **Reset User Limits**
   - **Method:** POST
   - **URL:** `/api/admin/users/{userId}/reset-limits`
   - **Description:** Manually reset the rate limits and flashcard counters for a user.
   - **Response:** 200 OK with confirmation.

4. **View KPI Dashboard**
   - **Method:** GET
   - **URL:** `/api/admin/kpi`
   - **Description:** Retrieve global and per-user KPIs.

---

## Web Endpoints (HTML Views)

For each resource, Web endpoints correspond functionally to the API endpoints, providing HTML interfaces rendered via Twig templates. These controllers reside in `/src/Controller/Web/`.

### A. Authentication and User Management
1. **User Registration**
   - **Display Registration Form**
     - **Method:** GET
     - **URL:** `/register`
     - **Description:** Render the registration form for new users.
   - **Process Registration**
     - **Method:** POST
     - **URL:** `/register`
     - **Description:** Handle registration submission, create user, and redirect upon success.

2. **User Login**
   - **Display Login Form**
     - **Method:** GET
     - **URL:** `/login`
     - **Description:** Render the login form.
   - **Process Login**
     - **Method:** POST
     - **URL:** `/login`
     - **Description:** Authenticate user credentials and redirect to the dashboard.

3. **Password Reset**
   - **Display Password Reset Request Form**
     - **Method:** GET
     - **URL:** `/forgot-password`
     - **Description:** Render the form to request a password reset.
   - **Process Password Reset Request**
     - **Method:** POST
     - **URL:** `/forgot-password`
     - **Description:** Handle the password reset request and send a reset link.

### B. Deck Management
1. **List Decks**
   - **Method:** GET
   - **URL:** `/decks`
   - **Description:** Render a list of the user's decks.

2. **Create Deck**
   - **Display New Deck Form**
     - **Method:** GET
     - **URL:** `/decks/new`
     - **Description:** Render the form to create a new deck.
   - **Process Deck Creation**
     - **Method:** POST
     - **URL:** `/decks/new`
     - **Description:** Handle new deck submission and redirect to the deck details view.

3. **View Deck Details**
   - **Method:** GET
   - **URL:** `/decks/{deckId}`
   - **Description:** Render detailed view of a deck, including its cards.

4. **Edit Deck**
   - **Display Edit Form**
     - **Method:** GET
     - **URL:** `/decks/{deckId}/edit`
     - **Description:** Render the edit form for a deck.
   - **Process Deck Update**
     - **Method:** POST
     - **URL:** `/decks/{deckId}/edit`
     - **Description:** Handle the update submission and redirect to the deck details.

5. **Delete Deck**
   - **Method:** POST
   - **URL:** `/decks/{deckId}/delete`
   - **Description:** Process deck deletion and redirect appropriately.

6. **Reset Deck Progress**
   - **Method:** POST
   - **URL:** `/decks/{deckId}/reset-progress`
   - **Description:** Process deck progress reset and refresh the deck view.

### C. Card Management
1. **List Cards**
   - **Method:** GET
   - **URL:** `/cards`
   - **Description:** Render a list of all cards, optionally filtered by deck.

2. **Create Card**
   - **Display New Card Form**
     - **Method:** GET
     - **URL:** `/cards/new`
     - **Description:** Render the form to create a new card.
   - **Process Card Creation**
     - **Method:** POST
     - **URL:** `/cards/new`
     - **Description:** Handle the card creation and redirect to the card details view.

3. **View Card Details**
   - **Method:** GET
   - **URL:** `/cards/{cardId}`
   - **Description:** Render detailed view of a card.

4. **Edit Card**
   - **Display Edit Form**
     - **Method:** GET
     - **URL:** `/cards/{cardId}/edit`
     - **Description:** Render the edit form for a card.
   - **Process Card Update**
     - **Method:** POST
     - **URL:** `/cards/{cardId}/edit`
     - **Description:** Handle the update and redirect to the card details view.

5. **Delete Card**
   - **Method:** POST
   - **URL:** `/cards/{cardId}/delete`
   - **Description:** Process card deletion and redirect to the card list.

6. **Bulk Delete Cards**
   - **Method:** POST
   - **URL:** `/cards/bulk-delete`
   - **Description:** Process bulk deletion of selected cards and refresh the list.

### D. AI Flashcards Generation
1. **Generate Flashcards**
   - **Display Generation Form**
     - **Method:** GET
     - **URL:** `/ai/flashcards`
     - **Description:** Render the form to input text for AI flashcard generation.
   - **Process Flashcards Generation**
     - **Method:** POST
     - **URL:** `/ai/flashcards/generate`
     - **Description:** Handle submission of text, generate flashcards, and display them for review.

2. **Review and Manage Generated Flashcards**
   - **View Job Details and Flashcards**
     - **Method:** GET
     - **URL:** `/ai/jobs/{jobId}`
     - **Description:** Render job details along with the list of generated flashcards.
   - **Edit Flashcard**
     - **Method:** GET
     - **URL:** `/ai/flashcards/{flashcardId}/edit`
     - **Description:** Render the form to edit a generated flashcard.
   - **Process Flashcard Update**
     - **Method:** POST
     - **URL:** `/ai/flashcards/{flashcardId}/edit`
     - **Description:** Handle the update of a flashcard's status or content.
   - **Bulk Save Flashcards**
     - **Method:** POST
     - **URL:** `/ai/jobs/{jobId}/bulk-save`
     - **Description:** Save accepted flashcards to a deck and redirect to the deck view.

### E. Spaced Repetition (SR) Session Endpoints
1. **Start SR Session**
   - **Method:** GET
   - **URL:** `/srs/session`
   - **Description:** Render the study session interface with the next due flashcard.

2. **Submit SR Answer**
   - **Method:** POST
   - **URL:** `/srs/session/answer`
   - **Description:** Process the answer submission and refresh the session view with the next card.

### F. Admin Endpoints (Protected)
1. **Manage Users**
   - **Method:** GET
   - **URL:** `/admin/users`
   - **Description:** Render the admin panel with a list of users and management options.

2. **View User Details and Manage Account**
   - **Method:** GET
   - **URL:** `/admin/users/{userId}`
   - **Description:** Render detailed information for a user with options to lock, unlock, or delete the account.

3. **Process Account Updates**
   - **Method:** POST
   - **URL:** `/admin/users/{userId}/update`
   - **Description:** Handle updates for a user's account (e.g., locking or deleting) and redirect appropriately.

4. **Reset User Limits**
   - **Method:** POST
   - **URL:** `/admin/users/{userId}/reset-limits`
   - **Description:** Process the reset of a user's rate limits and flashcard counters.

5. **View KPI Dashboard**
   - **Method:** GET
   - **URL:** `/admin/kpi`
   - **Description:** Render the KPI dashboard for overall statistics. 

## Core Directory Structure
```
src/
├── Controller/
│   ├── Api/          # API controllers
│   └── Web/          # Web controllers
├── DTO/              # All DTOs
│   ├── Request/      # Input DTOs
│   │   ├── Auth/     # Authentication DTOs
│   │   ├── Card/     # Card management DTOs
│   │   ├── Deck/     # Deck management DTOs
│   │   └── AI/       # AI generation DTOs
│   └── Response/     # Output DTOs
│       ├── Auth/     # Auth responses
│       ├── Card/     # Card responses
│       ├── Deck/     # Deck responses
│       └── Common/   # Shared DTOs
├── Entity/           # Database entities
├── Form/             # Form types for web forms
│   ├── Auth/         # Authentication forms
│   ├── Card/         # Card management forms
│   └── Deck/         # Deck management forms
├── Repository/       # Database queries
└── Service/          # Business logic
    ├── DTO/          # DTO mapping services
    └── Validator/    # Custom validation services
```

## DTO Layer Recommendations

### A. DTO Structure
- All DTOs should be immutable (readonly properties)
- Use PHP 8.0+ constructor property promotion
- Separate Request and Response DTOs
- Use validation attributes for request validation
- Follow naming convention: Create<Entity>DTO, Update<Entity>DTO, etc.

### B. Common Base Classes
- ApiResponse - standardized wrapper for all API responses
- PaginationDTO - reusable pagination structure
- ErrorDTO - standardized error response format

### C. Validation Strategy
- Use PHP 8 attributes for validation
- Group validations by context (create/update)
- Validate at controller level using Symfony Validator
- Custom constraints for business rules

### D. Mapping Layer
- DTOMapper service for entity-DTO conversion
- Use Symfony Serializer for complex mappings
- Handle nested relationships appropriately
- Keep mapping logic centralized

### E. OpenAPI/Swagger Integration
- Add OpenAPI annotations to DTOs
- Generate API documentation from DTO structure
- Include validation rules in documentation
- Document response formats

### F. Form Integration
- Create FormType classes matching DTO structure
- Reuse validation rules between API and forms
- Handle file uploads consistently
- Support CSRF protection for web forms

*Note: These recommendations ensure consistency across the application while maintaining clean separation between layers.* 