# Status implementacji widoku AI Flashcard Generator

## Zrealizowane kroki

### Iteracja 1-3
1. Utworzenie podstawowej struktury kontrolerów:
   - `AIController` (Web)
   - `AIFlashcardController` (API)
2. Implementacja serwisów:
   - `UserLimitsService`
   - `AIFlashcardGeneratorService`
3. Utworzenie szablonów Twig:
   - `ai/generate.html.twig`
   - `ai/_flashcard_item.html.twig`
   - `ai/_edit_flashcard_modal.html.twig`
4. Implementacja kontrolera Stimulus:
   - `flashcard_controller.js`

### Iteracja 4
1. Dostosowanie serwisów do istniejącej struktury:
   - Migracja do `AiJobFlashcardRepository` i `AiJobRepository`
   - Aktualizacja `AIFlashcardGeneratorService` do używania `AiJob` i `AiJobFlashcard`
2. Implementacja metod w repozytoriach:
   - `countTodayFlashcardsForUser`
   - `countRecentRequestsForUser`
   - `findPaginatedByJob`
   - `countByJob`
   - `findByJobAndStatus`
3. Aktualizacja kontrolerów do używania enumów:
   - `FlashcardStatus`
   - `JobStatus`
   - `BulkAction`
4. Dodanie dokumentacji OpenAPI do kontrolerów API
5. Implementacja obsługi błędów i komunikatów:
   - Toasty dla sukcesu/błędu w JS
   - Obsługa wyjątków w kontrolerach
6. Aktualizacja endpointów API:
   - `/api/ai/flashcards/{flashcardId}` (PATCH)
   - `/api/ai/jobs/{jobId}/bulk-save` (POST)

## Kolejne kroki

### Testy
1. Implementacja testów jednostkowych:
   - Serwisy
   - Repozytoria
   - Kontrolery
2. Implementacja testów funkcjonalnych:
   - Scenariusze generowania fiszek
   - Scenariusze edycji/akceptacji/odrzucania
   - Scenariusze operacji zbiorczych

### Walidacja
1. Dodanie walidacji formularzy:
   - `AIGenerationType`
   - `UpdateFlashcardDTO`
   - `BulkSaveFlashcardsDTO`
2. Implementacja walidacji po stronie klienta (JavaScript)

### UI/UX
1. Dodanie wskaźników limitów użytkownika:
   - Pozostałe znaki
   - Pozostałe żądania/minutę
   - Pozostałe fiszki/dzień
2. Implementacja wskaźników ładowania
3. Ulepszenie komunikatów o błędach
4. Dodanie potwierdzenia dla operacji zbiorczych

### Dokumentacja
1. Aktualizacja dokumentacji API
2. Dodanie dokumentacji dla developerów
3. Utworzenie dokumentacji użytkownika 