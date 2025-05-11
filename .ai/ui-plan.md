# Architektura UI dla 10xCards

## 1. Przegląd struktury UI

Cała architektura interfejsu użytkownika koncentruje się na wdrożeniu kluczowych widoków aplikacji: generowania fiszek przez AI, dashboardu z listą decków, szczegółów zestawu, edycji fiszek, logowania, rejestracji, zarządzania kontem oraz panelu administratora. Nawigacja odbywa się poprzez główne menu oraz breadcrumbs, umożliwiając płynne przechodzenie między widokami. UI jest budowane przy użyciu Tailwind CSS i Flowbite, co zapewnia spójny i responsywny design zgodny z WCAG, przy jednoczesnym uwzględnieniu mechanizmów bezpieczeństwa (walidacja danych, komunikaty o błędach, ochrona sesji). Komunikacja z API realizowana jest przez Fetch/Axios, z lokalnym zarządzaniem stanem umożliwiającym dalsze rozszerzenia, takie jak cache'owanie czy lazy loading.

## 2. Lista widoków

### Widok: Generowanie fiszek przez AI
- **Ścieżka**: `/ai/flashcards`
- **Główny cel**: Umożliwienie użytkownikowi generowania fiszek na podstawie wprowadzonego tekstu, przy użyciu API generującego fiszki.
- **Kluczowe informacje do wyświetlenia**: Instrukcje dotyczące limitu znaków, status generowania, lista wygenerowanych fiszek z możliwością akceptacji, edycji lub odrzucenia.
- **Kluczowe komponenty widoku**: Formularz wejściowy (textarea), przycisk "Generuj", lista wyników, modale potwierdzeń, flash messages (komunikaty o błędach i sukcesach). Nawigacja dla całej listy (zapisz wszystkie, odrzuć wszystkie, zapisz wybrane)
- **UX, dostępność i względy bezpieczeństwa**: Jasne komunikaty, walidacja długości tekstu, widoczny wskaźnik ładowania, zgodność z WCAG, informacja o limitach oraz zabezpieczenie przed nadużyciami.

### Widok: Dashboard / Lista decków
- **Ścieżka**: `/decks`
- **Główny cel**: Prezentacja wszystkich decków (zestawów fiszek) przypisanych do zalogowanego użytkownika.
- **Kluczowe informacje do wyświetlenia**: Nazwa i opis zestawu, data utworzenia, liczba fiszek, status powtórek.
- **Kluczowe komponenty widoku**: Karty podsumowujące decki, lista decków, przyciski nawigacyjne do przejścia do szczegółów, breadcrumbs.
- **UX, dostępność i względy bezpieczeństwa**: Intuicyjna nawigacja, czytelne formatowanie informacji, dostępność z klawiatury i czytników ekranu, ochrona danych przed nieautoryzowanym dostępem.

### Widok: Szczegóły zestawu
- **Ścieżka**: `/decks/{deckId}`
- **Główny cel**: Wyświetlenie szczegółowych informacji o wybranym zestawie wraz z listą fiszek.
- **Kluczowe informacje do wyświetlenia**: Lista fiszek, statystyki zestawu, opcje akcji bulk (np. zapis czy usunięcie fiszek).
- **Kluczowe komponenty widoku**: Tabela lub lista fiszek, przyciski bulk action, modal do edycji fiszki, flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Przejrzysta prezentacja danych, potwierdzenia dla operacji bulk, walidacja krytycznych akcji oraz dostępność dla użytkowników o ograniczeniach percepcyjnych.

### Widok: Edycja fiszki
- **Ścieżka**: `/cards/{cardId}/edit`
- **Główny cel**: Umożliwienie modyfikacji treści wybranej fiszki.
- **Kluczowe informacje do wyświetlenia**: Aktualna treść fiszki (front i back), komunikaty walidacyjne dotyczące limitów znaków.
- **Kluczowe komponenty widoku**: Formularz edycji z polami tekstowymi, przyciski "Zapisz" i "Anuluj", flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Natychmiastowa walidacja danych, czytelne informacje o błędach, zgodność z wytycznymi WCAG oraz ochrona przed nieautoryzowanymi zmianami.

### Widok: Logowanie
- **Ścieżka**: `/login`
- **Główny cel**: Autoryzacja użytkownika i rozpoczęcie sesji.
- **Kluczowe informacje do wyświetlenia**: Formularz logowania, komunikaty o błędach (np. błędne dane, blokada konta).
- **Kluczowe komponenty widoku**: Formularz z polami na e-mail i hasło, przycisk "Zaloguj", flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Odpowiednia walidacja pól, zabezpieczenia przed atakami, ochrona danych (np. mechanizm JWT), zgodność z WCAG.

### Widok: Rejestracja
- **Ścieżka**: `/register`
- **Główny cel**: Rejestracja nowych użytkowników w systemie.
- **Kluczowe informacje do wyświetlenia**: Formularz rejestracyjny, wymagania dotyczące hasła, komunikaty informujące o aktywacji konta.
- **Kluczowe komponenty widoku**: Formularz rejestracji, pola formularza (e-mail, hasło), przycisk "Zarejestruj się", flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Jasne instrukcje, walidacja danych, zabezpieczenia przed botami, zgodność z WCAG.

### Widok: Reset hasła
- **Ścieżka**: `/forgot-password`
- **Główny cel**: Umożliwienie użytkownikowi rozpoczęcia procedury resetowania hasła.
- **Kluczowe informacje do wyświetlenia**: Formularz wprowadzania adresu e-mail, komunikat o wysłaniu linku resetującego.
- **Kluczowe komponenty widoku**: Formularz, przycisk "Resetuj hasło", flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Jasne kroki resetowania, walidacja e-maila, zabezpieczenie przed nadużyciami.

### Widok: Zarządzanie kontem użytkownika
- **Ścieżka**: `/account` lub `/settings`
- **Główny cel**: Zarządzanie danymi osobowymi, ustawieniami konta oraz konfiguracją powiadomień.
- **Kluczowe informacje do wyświetlenia**: Dane użytkownika, opcje zmiany hasła, ustawienia profilu, przyciski wylogowania.
- **Kluczowe komponenty widoku**: Formularze edycji, przyciski zapisu, flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Intuicyjna edycja danych, potwierdzenia zmian, zabezpieczenia przed nieautoryzowanym dostępem.

### Widok: Panel administratora
- **Ścieżka**: `/admin`
- **Główny cel**: Zarządzanie użytkownikami, kontrola limitów i monitorowanie KPI systemu.
- **Kluczowe informacje do wyświetlenia**: Lista użytkowników, statusy kont, opcje blokowania/odblokowania, reset limitów, prezentacja KPI.
- **Kluczowe komponenty widoku**: Tabele z danymi, przyciski administracyjne, filtry, modale potwierdzeń, flash messages.
- **UX, dostępność i względy bezpieczeństwa**: Zaawansowane narzędzia zarządzania, potwierdzenia krytycznych operacji, dodatkowa autoryzacja dla administratorów, zgodność z WCAG.

### Widok: Strony błędów (np. 404, 503)
- **Ścieżka**: `/error/404`, `/error/503`
- **Główny cel**: Informowanie użytkownika o błędach systemowych lub niedostępności zasobów.
- **Kluczowe informacje do wyświetlenia**: Komunikat o błędzie, sugestia powrotu do strony głównej.
- **Kluczowe komponenty widoku**: Informacyjne komunikaty błędów, przyciski nawigacyjne.
- **UX, dostępność i względy bezpieczeństwa**: Jasny przekaz błędu, łatwa nawigacja do poprawnych stron, zgodność z WCAG.

## 3. Mapa podróży użytkownika

1. Użytkownik trafia na stronę logowania/rejestracji.
2. Po pomyślnym zalogowaniu, użytkownik zostaje przekierowany do dashboardu, gdzie widzi listę swoich decków.
3. Użytkownik wybiera konkretny deck, przechodząc do widoku szczegółów zestawu, gdzie może przeglądać fiszki i wykonywać akcje bulk (np. usuwanie, zapisywanie).
4. Z poziomu dashboardu lub szczegółów zestawu, użytkownik wybiera opcję generowania fiszek przez AI, przechodząc do widoku dedykowanego do tego celu.
5. Po wygenerowaniu fiszek, użytkownik przegląda rezultaty, akceptuje lub edytuje pojedyncze fiszki, a także wykonuje akcje bulk.
6. W dowolnym momencie użytkownik może przejść do widoku edycji wybranej fiszki lub zarządzania kontem, aby zmienić ustawienia swojego profilu.
7. Administrator, logując się, uzyskuje dostęp do panelu administracyjnego, gdzie zarządza użytkownikami, resetuje limity i monitoruje KPI.
8. W przypadku błędów operacyjnych, użytkownik otrzymuje komunikaty poprzez flash messages lub zostaje przekierowany na dedykowane strony błędów (np. 404, 503).
9. Nawigacja między widokami odbywa się za pomocą głównego menu oraz breadcrumbs, ułatwiających powrót do poprzednich sekcji.

## 4. Układ i struktura nawigacji

- Główne menu (header) zawiera linki: Lista decków, Generowanie fiszek przez AI, Zarządzanie kontem, a (dla administratorów) Panel administratora.
- Komponent breadcrumbs umieszczony poniżej headera, umożliwiający śledzenie ścieżki nawigacji i szybki powrót do poprzednich widoków.
- Stopka zawiera linki do pomocy, regulaminu oraz informacji o produkcie.
- Dodatkowo, na widokach mobilnych (w przyszłych iteracjach) zastosowany zostanie menu boczne lub rozwijane.

## 5. Kluczowe komponenty

- **Komponent formularzy**: Współdzielony zestaw elementów (Input, Button, Checkbox) używanych we wszystkich formularzach (logowanie, rejestracja, edycja, reset hasła).
- **Komponent flash messages**: Globalny system komunikatów informacyjnych, ostrzeżeń i błędów.
- **Komponent modal dialog**: Używany przy krytycznych operacjach (potwierdzenie usunięcia, reset)
- **Komponent nawigacji**: Breadcrumbs, menu główne oraz ewentualnie menu boczne, zapewniający spójne doświadczenie nawigacyjne.
- **Komponent karty/działu**: Stosowany do prezentacji decków, fiszek oraz podsumowań.
- **Komponent ładowania (Loader/Spinner)**: Informowanie użytkownika o przetwarzaniu operacji czy pobieraniu danych. 