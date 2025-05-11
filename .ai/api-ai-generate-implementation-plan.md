# API Endpoint Implementation Plan: Generate Flashcards via AI

## 1. Przegląd punktu końcowego
Endpoint służy do generowania fiszek z wykorzystaniem silnika AI w oparciu o tekst wprowadzony przez użytkownika. Po otrzymaniu żądania, system waliduje długość tekstu, sprawdza ograniczenia szybkości (max 5 żądań na minutę) oraz wywołuje usługę AI, która generuje fiszki. Wygenerowany rezultat jest rejestrowany w bazie danych (tabele `app.ai_jobs` oraz `app.ai_job_flashcards`) i zwracany jako odpowiedź.

## 2. Szczegóły żądania
- **Metoda HTTP:** POST  
- **Struktura URL:** `/api/ai/generate`  
- **Parametry:**
  - **Wymagane:**
    - `input_text` (string): Tekst wejściowy do generacji fiszek (maksymalnie 10 000 znaków oraz minimalnie 1000 znaków).
  - **Opcjonalne:** Brak  
- **Request Body (przykład):**
  ```json
  { "input_text": "Your long text here..." }
  ```

## 3. Wykorzystywane typy
- **DTO Request:**  
  Utworzyć typ `GenerateFlashcardsRequest` w folderze `/src/DTO/Request` do mapowania danych przychodzących.
- **DTO Response:**  
  Utworzyć typ `GenerateFlashcardsResponse` w folderze `/src/DTO/Response`, który będzie zawierać szczegóły zadania AI i tablicę wygenerowanych fiszek.
- **Command Model:**  
  Rozważyć utworzenie `GenerateFlashcardsCommand` jako obiektu komendowego, który przekazuje wszystkie niezbędne dane do warstwy biznesowej.
- **Dodatkowy DTO:**  
  `FlashcardDTO` do reprezentacji pojedynczej fiszki (pola: `front` i `back`).

## 4. Szczegóły odpowiedzi
- **Sukces (200 OK):**
  ```json
  {
    "ai_job": {
      "id": 123,
      "input_text_length": 5678,
      "token_count": 100,
      "flashcards_count": 5,
      "duration_ms": 250,
      "created_at": "2023-10-20T12:34:56Z"
    },
    "flashcards": [
      { "front": "Question text", "back": "Answer text" },
      ...
    ]
  }
  ```
- **Błędy:**
  - **400 Bad Request:** W przypadku przekroczenia limitu długości tekstu lub naruszenia limitów szybkości.
  - **401 Unauthorized:** Brak lub niewłaściwe dane autoryzacyjne (JWT).
  - **500 Internal Server Error:** Błąd po stronie serwera lub nieoczekiwany wyjątek.

## 5. Przepływ danych
1. **Przyjęcie żądania:**  
   - Kontroler odbiera żądanie POST na endpoint `/api/ai/generate`.
   - Żądanie jest mapowane do obiektu `GenerateFlashcardsRequest`.
2. **Walidacja:**  
   - Walidacja długości `input_text` (min. 1000 znaków, maks. 10 000 znaków) przy użyciu Symfony Validator.
   - Sprawdzenie ograniczeń szybkości (5 żądań/min) za pomocą wbudowanego komponentu RateLimiter.
3. **Wywołanie usługi AI:**  
   - Po pomyślnej walidacji, dane są przekazywane do usługi (np. `AiFlashcardsGenerator`), która korzysta z Symfony HttpClient do komunikacji z zewnętrznym silnikiem AI (np. OpenAI/Openrouter.ai).
4. **Zapis do bazy danych:**  
   - Utworzenie wpisu w tabeli `app.ai_jobs` z danymi dotyczącymi zadania.
   - Wygenerowane fiszki są zapisywane w tabeli `app.ai_job_flashcards` z powiązaniem do danego zadania.
5. **Odpowiedź do klienta:**  
   - Usługa zwraca odpowiedni DTO (`GenerateFlashcardsResponse`), który kontroler serializuje i odsyła w formacie JSON.

## 6. Względy bezpieczeństwa
- **Autoryzacja:**  
  Endpoint powinien być zabezpieczony przy użyciu JWT i weryfikacji przez Symfony Security.  
- **Row-Level Security (RLS):**  
  Baza danych (tabele `app.ai_jobs` oraz `app.ai_job_flashcards`) jest zabezpieczona politykami RLS, zapewniającymi dostęp tylko właścicielowi zasobu lub administratorowi.
- **Walidacja wejściowa:**  
  Aby zapobiec atakom typu injection, dane wejściowe muszą być dokładnie walidowane.
- **Rate Limiting:**  
  Implementacja rate limitera chroniącego przed nadużyciami i atakami DDoS.
- **Api Timeout:**
  Timeout zapytania do zewnętrznego silnika API wynosi 60s.

## 7. Obsługa błędów
- **Błąd walidacji:**  
  - W przypadku przekroczenia limitu długości dla `input_text` lub naruszenia limitów szybkości, zwrócić błąd 400 z czytelnym komunikatem.
- **Błąd autoryzacji:**  
  - Jeśli użytkownik nie jest poprawnie uwierzytelniony, zwrócić 401 Unauthorized.
- **Błąd serwera:**  
  - Wystąpienie nieprzewidzianych wyjątków powinno skutkować odpowiedzią 500 Internal Server Error oraz rejestrowaniem szczegółów błędu (logowanie).
- **Logowanie:**  
  - Rejestrowanie krytycznych zdarzeń i błędów w centralnym systemie logów.

## 8. Rozważania dotyczące wydajności
- **Rate Limiting:**  
  Zastosowanie komponentu RateLimiter zapewnia, że żądania przekraczające 5/min są blokowane, co chroni system przed przeciążeniem.
- **Integracja z AI:**  
  - Użycie Symfony HttpClient z odpowiednimi timeoutami i obsługą błędów.
  - Ewentualnie rozważenie asynchronicznego przetwarzania z użyciem Symfony Messenger dla długotrwałych operacji.
- **Cache'owanie wyników:**  
  Możliwość cache'owania wyników dla identycznych zapytań w krótkim okresie, aby zminimalizować liczbę wywołań do silnika AI.

## 9. Etapy wdrożenia
1. **Utworzenie kontrolera i konfiguracja routingu:**  
   - Utworzenie dedykowanego kontrolera (np. `AiGenerateController`) oraz konfiguracja endpointu `/api/ai/generate`.
2. **Implementacja DTO:**  
   - Stworzenie `GenerateFlashcardsRequest` w folderze `/src/DTO/Request` oraz `GenerateFlashcardsResponse` (i ewentualnie `FlashcardDTO`) w folderze `/src/DTO/Response`.
3. **Walidacja danych wejściowych:**  
   - Dodanie walidacji długości `input_text` i integracja z Symfony Validator.
4. **Implementacja logiki biznesowej:**  
   - Utworzenie serwisu (np. `AiFlashcardsGenerator`), który:
     - Sprawdza warunki wejściowe.
     - Komunikuje się z zewnętrznym silnikiem AI przy użyciu Symfony HttpClient.
     - Sprwadza czas odpowiedzi zewnętrznego silnika AI.
     - Rejestruje wynik w bazie danych (`app.ai_jobs` oraz `app.ai_job_flashcards`).
5. **Implementacja ograniczenia szybkości:**  
   - Konfiguracja i integracja RateLimiter w celu ścisłego egzekwowania limitów 5 żądań/min.
6. **Integracja mechanizmów bezpieczeństwa:**  
   - Wdrożenie zabezpieczeń przy użyciu JWT oraz konfiguracja polityk RLS dla bazy danych.
7. **Obsługa wyjątków i logowanie błędów:**  
   - Dodanie try-catch w krytycznych miejscach i wykorzystanie systemu logowania błędów.
8. **Testy:**  
   - Utworzenie testów jednostkowych i integracyjnych, aby sprawdzić poprawność walidacji, ograniczenia szybkości, integrację z serwisem AI oraz zapisywanie do bazy.
9. **Dokumentacja i Code Review:**  
   - Aktualizacja dokumentacji API oraz przegląd kodu zgodnie z zasadami clean code i Symfony Package Coding Standards.
