# Plan implementacji widoku Generowanie fiszek przez AI

## 1. Przegląd
Widok "Generowanie fiszek przez AI" umożliwia użytkownikom wprowadzanie tekstu, na podstawie którego sztuczna inteligencja generuje propozycje fiszek. Użytkownik może następnie przeglądać wygenerowane fiszki, akceptować je, edytować lub odrzucać pojedynczo, a także wykonywać akcje zbiorcze takie jak zapisz wszystkie zaakceptowane lub odrzuć wszystkie. Widok ten jest kluczowy dla głównej funkcjonalności aplikacji 10xCards, jaką jest szybkie tworzenie materiałów do nauki.

## 2. Routing widoku
- **Ścieżka**: `/ai/flashcards` (dla wyświetlenia formularza)
- **Ścieżka**: `/ai/generate` (dla przetwarzania formularza i wyświetlania wyników)
- **Ścieżka**: `/ai/jobs/{jobId}` (dla wyświetlenia szczegółów zadania i fiszek - *może być częścią tego samego widoku lub osobnym widokiem, do którego przekierowujemy po wygenerowaniu*)
- **Ścieżka**: `/ai/flashcards/{flashcardId}/edit` (dla edycji pojedynczej fiszki - modal lub osobna strona)
- **Ścieżka**: `/ai/jobs/{jobId}/bulk-save` (dla zbiorczego zapisu zaakceptowanych fiszek)

## 2.1 Kontroler i formularz
- **Kontrolery**: `AIController` (web) oraz `AIGenerateController` + `FlashcardController` (api)
- **Akcje kontrolerów**:
    - `generateAction(Request $request)`: Obsługuje GET dla `/ai/flashcards` (wyświetlenie formularza) oraz POST dla `/api/ai/generate` (przetwarzanie danych wejściowych, wywołanie serwisu AI, obsługa odpowiedzi i wyświetlenie wyników).
    - `viewJobAction(Request $request, string $jobId)`: Obsługuje GET dla `/ai/jobs/{jobId}` (pobranie i wyświetlenie fiszek dla danego zadania).
    - `editFlashcardAction(Request $request, string $flashcardId)`: Obsługuje GET dla `/ai/flashcards/{flashcardId}/edit` (wyświetlenie formularza edycji) oraz POST (zapis zmian).
    - `updateFlashcardStatusAction(Request $request, string $flashcardId)`: Obsługuje PATCH dla `/api/ai/flashcards/{flashcardId}` (aktualizacja statusu fiszki - Accept/Reject). *Może być obsługiwane przez JS + API bezpośrednio lub przez dedykowaną akcję kontrolera.*
    - `bulkSaveAction(Request $request, string $jobId)`: Obsługuje POST dla `/api/ai/jobs/{jobId}/bulk-save` (zapis zaakceptowanych fiszek).
- **Klasa FormType**:
    - `AIGenerationType`:
        - `input_text` (TextareaType): Pole do wprowadzenia tekstu przez użytkownika.
        - `submit` (SubmitType): Przycisk "Generuj".
    - `FlashcardEditType` (dla modala/strony edycji):
        - `front` (TextType): Pole na przód fiszki.
        - `back` (TextareaType): Pole na tył fiszki.
        - `save` (SubmitType): Przycisk "Zapisz".
- **Constraints walidacji**:
    - `AIGenerationType`:
        - `input_text`:
            - `NotBlank`: Pole nie może być puste.
            - `Length`: min 1000, max 10000 znaków.
    - `FlashcardEditType`:
        - `front`:
            - `NotBlank`
            - `Length`: max 200 znaków.
        - `back`:
            - `NotBlank`
            - `Length`: max 1000 znaków.
- **FlashBag**: Używany do wyświetlania komunikatów o sukcesie (np. fiszki wygenerowane, zapisane) lub błędach (np. przekroczono limit znaków, błąd API, błąd walidacji).

## 3. Struktura szablonów i części wspólnych (Twig)
- `base.html.twig`: Główny layout aplikacji.
- `ai/generate.html.twig`: Główny szablon widoku generowania fiszek.
    - Rozszerza `base.html.twig`.
    - Zawiera bloki: `title`, `stylesheets`, `javascripts`, `body`.
    - `body`:
        - Formularz (`AIGenerationType`) do wprowadzania tekstu.
        - Sekcja wyświetlania instrukcji i limitów.
        - Sekcja wyświetlania statusu generowania (wskaźnik ładowania).
        - Sekcja listy wygenerowanych fiszek (po otrzymaniu odpowiedzi od API).
            - `_flashcard_item.html.twig`: Szablon częściowy dla pojedynczej fiszki na liście.
        - Przyciski akcji zbiorczych (Zapisz wszystkie, Odrzuć wszystkie, Zapisz wybrane).
        - Flash messages.
- `ai/_flashcard_item.html.twig`: Szablon dla pojedynczej wygenerowanej fiszki.
    - Wyświetla front i back fiszki.
    - Przyciski: Akceptuj, Edytuj, Odrzuć.
- `ai/_edit_flashcard_modal.html.twig` (lub osobna strona `ai/edit_flashcard.html.twig`): Szablon dla modala/formularza edycji fiszki.
    - Formularz (`FlashcardEditType`).
    - Przyciski: Zapisz, Anuluj.
- `_flash_messages.html.twig`: Szablon częściowy do wyświetlania komunikatów z FlashBag.

## 4. Szczegóły szablonów i formularzy

### 4.1. `ai/generate.html.twig`
- **Opis**: Główny widok do generowania fiszek. Użytkownik wprowadza tekst, inicjuje generowanie, a następnie zarządza wynikami.
- **Główne elementy HTML i komponenty dzieci**:
    - Formularz z `textarea` (`form.input_text`) i przyciskiem "Generuj" (`form.submit`).
    - Kontener na wyświetlanie informacji o limitach (np. `{{ user_limits.remaining_chars }}`, `{{ user_limits.remaining_requests }}`).
    - Wskaźnik ładowania (np. spinner, ukrywany/pokazywany przez JS).
    - Lista wygenerowanych fiszek (`<ul>` lub `<div>`), gdzie każdy element listy to `include('ai/_flashcard_item.html.twig', { 'flashcard': flashcard_data })`.
    - Przyciski akcji zbiorczych (np. `<button id="bulk-save">Zapisz wybrane</button>`).
- **Obsługiwane zdarzenia**:
    - Submit formularza generowania.
    - Kliknięcie przycisków Akceptuj/Edytuj/Odrzuć na pojedynczej fiszce (może wywoływać JS do zmiany statusu lokalnie i/lub wysłania żądania PATCH do API).
    - Kliknięcie przycisków akcji zbiorczych.
- **Warunki walidacji (na formularzu `AIGenerationType`)**:
    - `input_text`: niepusty, długość między 1000 a 10000 znaków.
- **Parametry kontrolera (zmienne Twig)**:
    - `form`: Instancja `AIGenerationType`.
    - `jobId` (opcjonalnie, jeśli wyświetlamy wyniki istniejącego zadania).
    - `generated_flashcards`: Lista obiektów/tablic reprezentujących wygenerowane fiszki (po POST).
    - `user_limits`: Obiekt/tablica z informacjami o limitach użytkownika.
    - `error_message` (opcjonalnie, jeśli wystąpił błąd API).
    - `isLoading` (boolean, do kontrolowania wskaźnika ładowania).
- **Klasa FormType**: `AIGenerationType`.
- **Constraints walidacji (Symfony Validator)**: `NotBlank`, `Length` dla `input_text`.
- **Bloki i include'y**: `{% extends 'base.html.twig' %}`, `{% block title %}`, `{% block body %}`, `{% include '_flash_messages.html.twig' %}`, `{% include 'ai/_flashcard_item.html.twig' %}` (w pętli).
- **FlashBag**: Do wyświetlania komunikatów o powodzeniu/błędzie operacji generowania, zapisu.
- **CSRF token**: Automatycznie dodawany przez `form_start(form)`.

### 4.2. `ai/_flashcard_item.html.twig`
- **Opis**: Szablon częściowy dla pojedynczej fiszki na liście wyników.
- **Główne elementy HTML**:
    - Divy/paragrafy do wyświetlania `flashcard.front` i `flashcard.back`.
    - Przyciski: "Akceptuj" (`data-action="accept"`), "Edytuj" (`data-action="edit"`), "Odrzuć" (`data-action="reject"`). Każdy z `data-flashcard-id="{{ flashcard.id }}"`.
    - Checkbox do zaznaczenia fiszki dla akcji zbiorczych.
- **Obsługiwane zdarzenia (przez JS)**:
    - Kliknięcie przycisków Akceptuj/Edytuj/Odrzuć.
- **Parametry kontrolera (zmienne Twig)**:
    - `flashcard`: Obiekt/tablica z danymi pojedynczej fiszki (np. `id`, `front`, `back`, `status`).
- **FlashBag**: Niebezpośrednio; komunikaty z akcji na fiszce mogą być dodawane globalnie.

### 4.3. `ai/_edit_flashcard_modal.html.twig` (lub osobna strona)
- **Opis**: Modal lub strona do edycji treści pojedynczej fiszki.
- **Główne elementy HTML**:
    - Formularz (`FlashcardEditType`) z polami `front`, `back` i przyciskiem "Zapisz".
- **Obsługiwane zdarzenia**:
    - Submit formularza edycji.
- **Warunki walidacji (na formularzu `FlashcardEditType`)**:
    - `front`: niepusty, max 200 znaków.
    - `back`: niepusty, max 1000 znaków.
- **Parametry kontrolera (zmienne Twig)**:
    - `edit_form`: Instancja `FlashcardEditType`.
    - `flashcard_id`: ID edytowanej fiszki.
- **Klasa FormType**: `FlashcardEditType`.
- **Constraints walidacji (Symfony Validator)**: `NotBlank`, `Length`.
- **FlashBag**: Komunikaty o sukcesie/błędzie zapisu edycji.
- **CSRF token**: Automatycznie dodawany przez `form_start(edit_form)`.

## 5. Typy
Nowe DTOs (Data Transfer Objects) mogą być potrzebne do obsługi danych:

- **`AIGenerationRequestDTO`**:
    - `inputText: string` (dla danych z formularza `AIGenerationType`)

- **`FlashcardDTO`** (reprezentacja fiszki na froncie, może być mapowana z odpowiedzi API):
    - `id: string` (lub `int`, zależnie od API)
    - `jobId: string` (lub `int`)
    - `front: string`
    - `back: string`
    - `status: string` ('pending', 'accepted', 'rejected', 'edited') - status na froncie, przed wysłaniem do API.
    - `originalFront?: string` (do edycji)
    - `originalBack?: string` (do edycji)

- **`FlashcardUpdateDTO`** (dla żądania PATCH do `/api/ai/flashcards/{flashcardId}`):
    - `status: string` ('accepted', 'rejected')
    - `edited_front?: string` (opcjonalnie, jeśli edytowana)
    - `edited_back?: string` (opcjonalnie, jeśli edytowana)

- **`UserLimitsDTO`**:
    - `remainingChars: int`
    - `remainingRequestsPerMin: int`
    - `remainingFlashcardsToday: int`

- **`AIJobResponseDTO`** (dla odpowiedzi z `/api/ai/generate`):
    - `jobId: string`
    - `status: string`
    - `flashcards: FlashcardDTO[]`
    - `statistics: object` (np. liczba wygenerowanych)
    - `message?: string`

## 6. Formularze i walidacja (Symfony Form + Validator)
- **`AIGenerationType`**:
    - Pole `input_text` typu `TextareaType`.
    - Constraint `NotBlank` i `Length(min=1000, max=10000)` dla `input_text`.
    - Przycisk "Generuj" typu `SubmitType`.
- **`FlashcardEditType`**:
    - Pole `front` typu `TextType`. Constraint `NotBlank` i `Length(max=200)`.
    - Pole `back` typu `TextareaType`. Constraint `NotBlank` i `Length(max=1000)`.
    - Przycisk "Zapisz" typu `SubmitType`.
- Walidacja będzie przeprowadzana po stronie serwera przy użyciu komponentu Symfony Validator. Błędy walidacji będą wyświetlane obok odpowiednich pól formularza oraz poprzez FlashBag.
- Każdy formularz będzie zabezpieczony tokenem CSRF.

## 7. Integracja API (Integracja w kontrolerze)
- **Akcja `generateAction` (POST na `/ai/flashcards/generate`)**:
    1. Walidacja formularza `AIGenerationType`.
    2. Jeśli walidacja poprawna:
        - Pobranie `input_text` z formularza.
        - Wywołanie serwisu aplikacyjnego (np. `AIFlashcardGeneratorService`).
        - Serwis użyje `HttpClient` (Symfony HttpClient) do wysłania żądania POST na `/api/ai/generate` z payloadem `{"input_text": "..."}`.
        - Obsługa odpowiedzi z API:
            - **200 OK**: Odpowiedź zawiera `AIJobResponseDTO` (listę fiszek, `jobId`, statystyki). Przekazanie tych danych do szablonu `ai/generate.html.twig` w celu wyświetlenia listy fiszek.
            - **400 Bad Request**: Błąd (np. przekroczony limit znaków, błąd rate limit). Wyświetlenie komunikatu o błędzie użytkownikowi poprzez FlashBag i/lub zmienną w szablonie.
            - Inne błędy HTTP: Obsługa i wyświetlenie generycznego komunikatu błędu.
    3. Jeśli walidacja niepoprawna, formularz zostanie ponownie wyświetlony z błędami.

- **Akcja `updateFlashcardStatusAction` (obsługa akcji Akceptuj/Odrzuć/Edytuj - np. przez JS + AJAX)**:
    - Frontendowy JS wysyła żądanie PATCH na `/api/ai/flashcards/{flashcardId}`.
    - **Payload**: `{"status": "accepted" | "rejected", "edited_front": "...", "edited_back": "..."}`.
    - **Odpowiedź API**: 200 OK z zaktualizowanymi danymi fiszki. JS aktualizuje UI.
    - Jeśli edycja odbywa się przez osobny formularz Symfony (np. modal), to po submicie tego formularza, kontroler wyśle żądanie PATCH.

- **Akcja `bulkSaveAction` (POST na `/ai/jobs/{jobId}/bulk-save`)**:
    1. Frontend przesyła listę ID zaakceptowanych fiszek lub kontroler pobiera fiszki ze statusem 'accepted' dla danego `jobId`.
    2. Kontroler wysyła żądanie POST na `/api/ai/jobs/{jobId}/bulk-save`. API backendowe zajmuje się zapisem do decka.
    3. **Odpowiedź API**: 200 OK. Przekierowanie do widoku decka z komunikatem sukcesu przez FlashBag.

## 8. Interakcje użytkownika
1.  **Wprowadzanie tekstu**: Użytkownik wpisuje lub wkleja tekst do `textarea`.
    - *Wynik*: Tekst jest widoczny w polu.
2.  **Kliknięcie "Generuj"**: Użytkownik klika przycisk "Generuj".
    - *Wynik*: Formularz jest wysyłany. Wskaźnik ładowania jest wyświetlany. Po otrzymaniu odpowiedzi od API, lista wygenerowanych fiszek pojawia się poniżej formularza, lub wyświetlany jest komunikat błędu. Limity użytkownika są widoczne.
3.  **Akceptacja fiszki**: Użytkownik klika przycisk "Akceptuj" przy fiszce.
    - *Wynik*: Fiszka jest oznaczana jako "zaakceptowana" (wizualnie i w stanie JS). Opcjonalnie, od razu wysyłane jest żądanie PATCH do API.
4.  **Odrzucenie fiszki**: Użytkownik klika przycisk "Odrzuć" przy fiszce.
    - *Wynik*: Fiszka jest oznaczana jako "odrzucona" lub usuwana z listy (wizualnie i w stanie JS). Opcjonalnie, od razu wysyłane jest żądanie PATCH do API lub fiszka jest po prostu ignorowana przy zapisie zbiorczym.
5.  **Edycja fiszki**: Użytkownik klika przycisk "Edytuj" przy fiszce.
    - *Wynik*: Otwiera się modal lub nowa strona z formularzem edycji, wypełnionym aktualną treścią fiszki. Użytkownik modyfikuje treść i klika "Zapisz". Po zapisie (i sukcesie od API), modal się zamyka, a fiszka na liście jest zaktualizowana.
6.  **Zapisz wszystkie zaakceptowane**: Użytkownik klika przycisk "Zapisz wszystkie zaakceptowane" (lub podobny, np. "Zapisz wybrane" jeśli są checkboxy).
    - *Wynik*: Wysyłane jest żądanie do API w celu zapisania zaakceptowanych fiszek. Użytkownik jest przekierowywany do widoku decka z komunikatem o sukcesie lub błędzie.
7.  **Odrzuć wszystkie**: Użytkownik klika przycisk "Odrzuć wszystkie".
    - *Wynik*: Wszystkie fiszki na liście są oznaczane jako odrzucone lub usuwane z listy.

## 9. Warunki i walidacja
- **Limit znaków w `textarea`**: 1000-10000 znaków. Weryfikowane przez Symfony Validator na serwerze. Komunikat błędu wyświetlany przy polu i/lub przez FlashBag. Przycisk "Generuj" może być nieaktywny (JS) jeśli warunek nie jest spełniony po stronie klienta.
- **Limit zapytań AI**: 5 zapytań/min. Weryfikowane przez API backendowe. Jeśli przekroczone, API zwróci błąd 400. Frontend wyświetli komunikat błędu. Przycisk "Generuj" może być tymczasowo nieaktywny.
- **Dzienny limit fiszek**: 500 fiszek/dzień. Weryfikowane przez API backendowe i serwis domenowy. Jeśli przekroczone, API zwróci błąd lub odpowiednią informację. Frontend wyświetli komunikat.
- **Długość pól fiszki (front/back) przy edycji**: Front ≤ 200, Back ≤ 1000 znaków. Weryfikowane przez Symfony Validator w formularzu edycji. Komunikaty błędów przy polach.
- **Obecność tekstu w `textarea`**: Pole nie może być puste (NotBlank). Weryfikowane przez Symfony Validator.
- **Instrukcje**: Muszą być jasno wyświetlone, informując o limitach.
- **Status generowania**: Widoczny wskaźnik ładowania podczas komunikacji z API.
- **Zabezpieczenie przed nadużyciami**: Rate limiting po stronie API. CSRF tokeny w formularzach Symfony.

## 10. Obsługa błędów
- **Błędy walidacji formularza (Symfony)**:
    - Wyświetlane przy odpowiednich polach formularza.
    - Ogólny komunikat przez FlashBag, jeśli to konieczne.
- **Błędy API (`/api/ai/generate`)**:
    - **400 Bad Request (przekroczony limit znaków, rate limit)**: Komunikat z API wyświetlany użytkownikowi przez FlashBag. Formularz pozostaje wypełniony.
    - **Inne błędy serwera (5xx)**: Generyczny komunikat "Wystąpił błąd podczas generowania fiszek. Spróbuj ponownie później." przez FlashBag.
- **Błędy API (`/api/ai/flashcards/{flashcardId}` PATCH)**:
    - Komunikat o niepowodzeniu aktualizacji wyświetlany blisko danej fiszki lub przez globalny FlashBag.
- **Błędy API (`/api/ai/jobs/{jobId}/bulk-save` POST)**:
    - Komunikat o niepowodzeniu zapisu przez FlashBag. Użytkownik pozostaje na stronie generowania.
- **Brak odpowiedzi od API / Timeout**:
    - Komunikat "Serwer nie odpowiada. Sprawdź połączenie internetowe i spróbuj ponownie."
- **Niezalogowany użytkownik**: Przekierowanie na stronę logowania (obsługiwane przez Symfony Security).
- **Brak uprawnień**: Komunikat o braku dostępu (jeśli dotyczy, choć ten widok powinien być dostępny dla zalogowanych).

## 11. Kroki implementacji
1.  **Backend Setup (jeśli nie istnieje)**:
    *   Upewnij się, że endpointy API (`/api/ai/generate`, `/api/ai/flashcards/{flashcardId}`, `/api/ai/jobs/{jobId}/bulk-save`) są zaimplementowane i działają zgodnie z opisem.
    *   Stwórz niezbędne serwisy po stronie backendu (np. `AIFlashcardGeneratorService`) do komunikacji z właściwym API AI i obsługi logiki biznesowej (limity, statystyki).
2.  **Routing**:
    *   Zdefiniuj ścieżki przez anotacje w kontrolerze:
        *   `/ai/flashcards` (GET) -> `AIController::generateAction`
        *   `/ai/generate` (POST) -> `AIController::generateAction`
        *   (Opcjonalnie) `/ai/jobs/{jobId}` (GET) -> `AIController::viewJobAction`
        *   (Opcjonalnie, jeśli strona) `/ai/flashcards/{flashcardId}/edit` (GET, POST) -> `AIController::editFlashcardAction`
        *   `/ai/jobs/{jobId}/bulk-save` (POST) -> `AIController::bulkSaveAction`
3.  **Kontroler (`AIController`)**:
    *   Stwórz klasę `AIController`.
    *   Zaimplementuj akcję `generateAction(Request $request, AIFlashcardGeneratorService $generatorService, UserLimitsService $limitsService)`:
        *   Obsługa GET: Utwórz instancję `AIGenerationType`, pobierz limity użytkownika, przekaż do szablonu.
        *   Obsługa POST: Zbinduj dane z `Request` do formularza. Jeśli `isValid()`, wywołaj `$generatorService->generateFromText()`. Obsłuż odpowiedź (sukces/błąd), dodaj komunikaty do FlashBag, przekaż dane (fiszki lub błąd) do szablonu.
    *   Zaimplementuj (jeśli potrzebne) `editFlashcardAction`, `viewJobAction`, `bulkSaveAction`.
4.  **Formularze Symfony**:
    *   Stwórz `AIGenerationType.php`:
        *   `input_text` (TextareaType) z odpowiednimi `label`, `attr` (np. `rows`).
        *   `submit` (SubmitType).
        *   Skonfiguruj `constraints` w `configureOptions`.
    *   Stwórz `FlashcardEditType.php` (jeśli edycja przez formularz Symfony):
        *   `front` (TextType), `back` (TextareaType), `save` (SubmitType).
        *   Skonfiguruj `constraints`.
5.  **DTOs**:
    *   Zdefiniuj klasy DTO: `AIGenerationRequestDTO`, `FlashcardDTO`, `FlashcardUpdateDTO`, `UserLimitsDTO`, `AIJobResponseDTO` w odpowiednim namespace (np. `App\DTO\AI`).
6.  **Szablony Twig**:
    *   Stwórz `ai/generate.html.twig`:
        *   Rozszerz `base.html.twig`.
        *   Dodaj `form_start(form)`, `form_widget(form.input_text)`, `form_widget(form.submit)`, `form_end(form)`.
        *   Implementuj wyświetlanie instrukcji i limitów (`{{ user_limits.remainingChars }}`).
        *   Dodaj sekcję na listę fiszek (pętla `for flashcard in generated_flashcards`), używając `include('ai/_flashcard_item.html.twig')`.
        *   Dodaj wskaźnik ładowania.
        *   Dodaj przyciski akcji zbiorczych.
        *   Implementuj wyświetlanie FlashMessages.
    *   Stwórz `ai/_flashcard_item.html.twig`:
        *   Wyświetl `flashcard.front`, `flashcard.back`.
        *   Dodaj przyciski Akceptuj/Edytuj/Odrzuć z odpowiednimi `data-` atrybutami dla JS.
        *   Dodaj checkbox dla zaznaczania.
    *   Stwórz `ai/_edit_flashcard_modal.html.twig` (lub stronę):
        *   Dodaj formularz edycji (`edit_form`).
7.  **JavaScript (Stimulus/Fetch/Axios)**:
    *   Implementacja logiki po stronie klienta (plik np. `assets/controllers/ai_flashcards_controller.js` jeśli używany jest Stimulus).
    *   Obsługa wyświetlania/ukrywania wskaźnika ładowania.
    *   Obsługa kliknięć przycisków Akceptuj/Edytuj/Odrzuć na fiszkach:
        *   Aktualizacja UI.
        *   Wysłanie żądania PATCH do `/api/ai/flashcards/{flashcardId}` (jeśli nie jest to pełne przeładowanie strony).
        *   Obsługa otwierania modala edycji i dynamicznego ładowania/submisji formularza edycji.
    *   Obsługa przycisków akcji zbiorczych (np. zebranie ID zaznaczonych fiszek i wysłanie ich w żądaniu POST do `/ai/jobs/{jobId}/bulk-save`).
    *   Opcjonalna walidacja po stronie klienta (np. długość tekstu w textarea) dla lepszego UX.
8.  **CSS (Tailwind CSS / Flowbite)**:
    *   Stylizuj formularz, listę fiszek, przyciski, komunikaty, wskaźnik ładowania, modal edycji zgodnie z UI planem i tech stackiem.
9.  **Testowanie**:
    *   Przetestuj generowanie fiszek z poprawnym tekstem.
    *   Przetestuj z tekstem za krótkim/za długim (walidacja).
    *   Przetestuj osiągnięcie limitu zapytań/dziennego limitu fiszek.
    *   Przetestuj akcje Akceptuj/Edytuj/Odrzuć.
    *   Przetestuj edycję fiszki (walidacja długości pól).
    *   Przetestuj akcje zbiorcze.
    *   Sprawdź wyświetlanie komunikatów o błędach i sukcesach.
    *   Sprawdź responsywność i dostępność (WCAG).
10. **Dokumentacja**: Upewnij się, że nowe komponenty/serwisy są odpowiednio udokumentowane. 