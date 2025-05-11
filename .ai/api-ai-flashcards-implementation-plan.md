# Plan wdrożenia punktu końcowego API: Zarządzanie generowanymi fiszkami

## 1. Przegląd punktu końcowego
Ten punkt końcowy służy do zarządzania fiszkami generowanymi przez system AI. Obejmuje on trzy główne funkcjonalności:
- Pobieranie wygenerowanych fiszek powiązanych z określonym zadaniem AI.
- Aktualizacja statusu (oraz opcjonalnie treści) pojedynczej fiszki.
- Zbiorczy zapis lub odrzucenie fiszek do wybranej talii.

Dzięki temu rozwiązaniu użytkownicy mogą efektywnie przeglądać i zarządzać wynikami generowania fiszek przez system AI.

## 2. Szczegóły żądania
- **Metoda HTTP:**
  - GET: Pobranie wygenerowanych fiszek dla zadanego zadania AI.
  - PATCH: Aktualizacja statusu oraz opcjonalnie treści konkretnej fiszki.
  - POST: Zbiorczy zapis zaakceptowanych fiszek do wskazanej talii.
- **Struktura URL:**
  - GET `/api/ai/jobs/{jobId}/flashcards`
  - PATCH `/api/ai/flashcards/{flashcardId}`
  - POST `/api/ai/jobs/{jobId}/bulk-save`
- **Parametry:**
  - **Wymagane:**
    - `{jobId}`: Unikalny identyfikator zadania AI.
    - `{flashcardId}` (dla PATCH): Unikalny identyfikator fiszki.
    - W ciele żądania POST: `deck_id` (ID talii, do której mają zostać zapisane zaakceptowane fiszki).
  - **Opcjonalne:**
    - Dla żądania PATCH:
      - `edited_front`: Opcjonalna, zmodyfikowana treść frontu (może być null).
      - `edited_back`: Opcjonalna, zmodyfikowana treść tyłu (może być null).
- **Treść żądania:**
  - Dla PATCH: Przykładowy obiekt JSON:
    ```json
    { 
      "status": "accepted", 
      "edited_front": null, 
      "edited_back": null 
    }
    ```
  - Dla POST (bulk-save): Przykładowy obiekt JSON:
    ```json
    {
      "action": "save",  // ustaw 'save' dla zapisu lub 'reject' dla odrzucenia; przy 'save' wymagany jest parametr deck_id
      "deck_id": 1
    }
    ```

## 3. Wykorzystywane typy
- **DTO:**
  - `UpdateFlashcardDTO` – służy do walidacji i mapowania danych przy aktualizacji statusu fiszki.
  - `BulkSaveFlashcardsDTO` – służy do walidacji danych przy operacji zbiorczego zapisu.
  - `FlashcardResponseDTO` – reprezentuje pojedynczą fiszkę w odpowiedzi.
  - `AIJobFlashcardsResponseDTO` – zawiera listę fiszek oraz metadane związane z zadaniem AI.
- **Modele poleceń (Command Modele):**
  - `UpdateFlashcardCommand` – enkapsuluje logikę aktualizacji fiszki.
  - `BulkSaveFlashcardsCommand` – enkapsuluje logikę zbiorczego zapisu fiszek do talii.

## 4. Szczegóły odpowiedzi
- **GET** `/api/ai/jobs/{jobId}/flashcards`
  - Odpowiedź: 200 OK
  - Zawartość: Lista fiszek w formacie `FlashcardResponseDTO` lub struktura zawierająca metadane zadania AI.
- **PATCH** `/api/ai/flashcards/{flashcardId}`
  - Odpowiedź: 200 OK
  - Zawartość: Zaktualizowana fiszka z nowym statusem oraz ewentualnymi zmianami treści.
- **POST** `/api/ai/jobs/{jobId}/bulk-save`
  - Odpowiedź: 200 OK
  - Zawartość: Potwierdzenie wykonania zbiorczego zapisu fiszek do wybranej talii.

## 5. Przepływ danych
1. Klient wysyła żądanie GET do `/api/ai/jobs/{jobId}/flashcards`.
   - Kontroler autoryzuje użytkownika, weryfikuje `jobId` oraz pobiera powiązane fiszki z bazy danych (tabela `app.ai_job_flashcards`).
   - Dane są mapowane do `AIJobFlashcardsResponseDTO` i zwracane klientowi.
2. Przy żądaniu PATCH, klient wysyła nowe dane dla fiszki do `/api/ai/flashcards/{flashcardId}`.
   - Kontroler waliduje dane przy użyciu DTO oraz Symfony Validator.
   - Następnie wywołuje `UpdateFlashcardCommand` poprzez warstwę serwisową (np. `FlashcardService`), która aktualizuje rekord w bazie danych.
   - Zaktualizowana fiszka jest mapowana do `FlashcardResponseDTO` i zwracana klientowi.
3. Przy żądaniu POST bulk-save, klient wysyła żądanie do `/api/ai/jobs/{jobId}/bulk-save` zawierające parametr `action`, który określa żądaną operację ("save" dla zapisu lub "reject" dla odrzucenia), oraz, jeśli operacja to zapisu, wymaganą wartość `deck_id` w ciele żądania.
   - Kontroler weryfikuje, czy fiszki o statusie "accepted" są powiązane z danym `jobId`.
   - W zależności od wartości parametru `action`, warstwa serwisowa wykonuje operację zapisu fiszek do tabeli `app.card` lub oznacza fiszki jako odrzucone.
   - Zwracane jest potwierdzenie wykonania operacji.

## 6. Względy bezpieczeństwa
- **Uwierzytelnianie i autoryzacja:**
  - Wszystkie operacje wymagają uwierzytelnienia za pomocą tokena JWT.
  - Dostęp do zasobów (zadanie AI oraz fiszki) jest weryfikowany, aby upewnić się, że należą do aktualnie zalogowanego użytkownika.
- **Walidacja:**
  - Walidacja danych wejściowych odbywa się przy użyciu Symfony Validator oraz atrybutów PHP 8 w DTO.
  - Sprawdzane jest, czy użytkownik ma prawo do wykonania operacji na danym zasobie.
- **Ochrona przed atakami:**
  - Stosowane są mechanizmy RLS oraz odpowiednie polityki w Symfony Security w celu ochrony dostępu do danych.
  - Ograniczenie możliwości mass assignment poprzez stosowanie ściśle typowanych DTO.

## 7. Obsługa błędów
- **400 Bad Request:** W przypadku niepoprawnych danych wejściowych (np. błędny format JSON, brak wymaganych pól, nieprawidłowy status).
- **401 Unauthorized:** Brak autoryzacji lub nieprawidłowy token JWT.
- **404 Not Found:** Nie znaleziono danego `jobId` lub `flashcardId`.
- **500 Internal Server Error:** Błąd serwera (np. problemy z bazą danych lub niespodziewane wyjątki).
- Wszystkie błędy powinny być logowane (np. przy użyciu Monolog) i zwracane w ustandaryzowanym formacie (np. jako ErrorDTO).

## 8. Rozważania dotyczące wydajności
- Optymalizacja zapytań do bazy danych dzięki wykorzystaniu indeksów (np. na kolumnach `ai_job_id` oraz `id` w tabeli `app.ai_job_flashcards`).
- Implementacja paginacji przy pobieraniu listy fiszek, aby uniknąć przeciążenia serwera.
- Rozważenie cache'owania często pobieranych danych w przyszłych iteracjach.
- Asynchroniczne przetwarzanie operacji zbiorczego zapisu, jeśli operacja staje się czasochłonna.

## 9. Etapy wdrożenia
1. Zdefiniowanie oraz implementacja modeli DTO i poleceń (Command):
   - `UpdateFlashcardDTO`, `BulkSaveFlashcardsDTO` (rozszerzony o pole `action`), `UpdateFlashcardCommand`, `BulkSaveFlashcardsCommand` (z logiką obsługi akcji "save" i "reject").
2. Utworzenie lub rozbudowa warstwy serwisowej (np. `FlashcardService`), która:
   - Pobierze fiszki dla określonego `jobId`.
   - Zaktualizuje pojedyncze fiszki na podstawie danych zawartych w DTO.
   - Wykona zbiorczy zapis zaakceptowanych fiszek do wybranej talii.
3. Rozszerzenie repository o metody obsługujące operacje na tabeli `app.ai_job_flashcards` oraz integrację z tabelą `app.card`.
4. Implementacja kontrolerów API w katalogu `/src/Controller/Api/`:
   - Punkt końcowy GET do pobierania fiszek.
   - Punkt końcowy PATCH do aktualizacji statusu fiszki.
   - Punkt końcowy POST do operacji bulk-save fiszek.
5. Implementacja walidacji danych wejściowych przy użyciu Symfony Validator i mapowania do DTO.
6. Dodanie zabezpieczeń autoryzacyjnych przy użyciu JWT, mechanizmów RLS oraz polityk Symfony Security.
7. Przeprowadzenie testów jednostkowych i integracyjnych dla wszystkich operacji.
8. Dokumentacja punktów końcowych w formacie OpenAPI/Swagger.
9. Przeprowadzenie przeglądu kodu zgodnie z zasadami clean-code, codequality oraz symfony-package-coding-standards.
10. Wdrożenie na środowisku testowym. 