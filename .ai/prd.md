# Dokument wymagań produktu (PRD) - 10xCards

## 1. Przegląd produktu
10xCards to aplikacja webowa umożliwiająca szybkie tworzenie i zarządzanie fiszkami edukacyjnymi wspieranymi przez sztuczną inteligencję oraz integrację z algorytmem spaced repetition jako zewnętrznym mikroserwisem. Użytkownicy mogą generować fiszki na bazie wklejonego tekstu, tworzyć je ręcznie, organizować w zestawy (decks) i przeprowadzać sesje powtórek. W MVP dostępny jest też panel administratora służący do zarządzania użytkownikami, limitami oraz monitorowania KPI.

## 2. Problem użytkownika
Manualne tworzenie wysokiej jakości fiszek edukacyjnych jest czasochłonne i wymaga od użytkownika wielokrotnego przekształcania treści, co zniechęca do korzystania z efektywnej metody nauki opartej na spaced repetition.

## 3. Wymagania funkcjonalne
- Automatyczne generowanie fiszek przez AI na podstawie wklejonego tekstu (limit 10 000 znaków na zapytanie, 5 zapytań/min, reset dzienny o 00:00 UTC).
- Akceptacja, edycja lub odrzucenie wygenerowanych fiszek przed zapisaniem w postaci jednej listy.
- Manualne tworzenie pojedynczych fiszek z polami front (≤ 200 znaków) i back (≤ 1000 znaków).
- Podsumowanie wygenerowanych fiszek z opcjami akcji grupowych: bulk save oraz bulk delete.
- Przeglądanie, wyszukiwanie, filtrowanie oraz usuwanie pojedynczych fiszek.
- Zarządzanie zestawami (deckami): tworzenie, nazwa, opis, data utworzenia, reset progresu.
- Integracja z zewnętrznym mikroserwisem algorytmu spaced repetition (karty due today + zaległe; manualne odkładanie; reset progresu per zestaw).
- System kont użytkowników: rejestracja (e-mail, hasło), logowanie, reset hasła, limity (500 fiszek/dzień).
- Panel administratora: przegląd listy użytkowników, blokada/odblokowanie konta, usuwanie konta, reset limitów, podgląd KPI globalnych i per user.
- Zabezpieczenia: polityka haseł, blokada po 5 nieudanych próbach logowania, brak trenowania modeli na danych użytkowników.
- Zgodność z RODO: brak udostępniania cudzych zestawów, pełne usunięcie konta po 48 h, backupy 30 dni.
- Skalowalne i bezpieczne przechowywanie danych użytkowników i fiszek.
- Zbieranie statystyk: liczba fiszek wygenerowanych ręcznie, przez AI, oraz liczba zaakceptowanych.

## 4. Granice produktu
- brak własnego zaawansowanego algorytmu powtórek (korzystanie z mikroserwisu open-source);
- brak importu wielu formatów (PDF, DOCX, itp.);
- brak współdzielenia zestawów między użytkownikami;
- brak integracji z zewnętrznymi platformami edukacyjnymi;
- brak aplikacji mobilnych;
- brak importu/eksportu CSV;
- brak powiadomień e-mail w MVP;
- brak zaawansowanego testowania UX, WCAG i rozbudowanych statystyk.

## 5. Historyjki użytkowników
- ID: US-001  
  Tytuł: Generowanie fiszek przez AI  
  Opis: Jako użytkownik wklejam tekst (≤ 10 000 znaków), klikam "Generuj" i otrzymuję listę proponowanych fiszek.  
  Kryteria akceptacji:  
    - system wysyła zapytanie do AI,  
    - użytkownik otrzymuje maksymalnie 500 fiszek (limit dzienny),  
    - każda fiszka ma front ≤ 200 i back ≤ 1000 znaków,  
    - w razie przekroczenia limitu znaków lub błędu API wyświetlany jest komunikat o błędzie.

- ID: US-002  
  Tytuł: Akceptacja lub odrzucenie wygenerowanych fiszek  
  Opis: Jako użytkownik przeglądam wygenerowane fiszki i oznaczam każdą akcję: Akceptuj, Edytuj, Odrzuć.  
  Kryteria akceptacji:  
    - widzę listę fiszek z przyciskami Akceptuj, Edytuj, Odrzuć,  
    - akcja Akceptuj zaznacza fiszkę do zapisu,  
    - akcja Odrzuć usuwa fiszkę z listy,  
    - akcja Edytuj otwiera modal do zmiany front/back i waliduje długości.

- ID: US-003  
  Tytuł: Zapis zaakceptowanych fiszek zbiorczo  
  Opis: Jako użytkownik po selekcji akcji grupowych klikam "Zapisz wszystkie zaakceptowane" i tworzę karty w wybranym zestawie.  
  Kryteria akceptacji:  
    - przycisk bulk save zapisuje tylko fiszki oznaczone jako zaakceptowane,  
    - karty pojawiają się w wybranym decku,  
    - liczba zapisanych kart nie przekracza limitu 500/dzień.

- ID: US-004  
  Tytuł: Manualne tworzenie pojedynczej fiszki  
  Opis: Jako użytkownik wybieram opcję "Dodaj fiszkę", wypełniam pola front i back, zapisuję do decku.  
  Kryteria akceptacji:  
    - walidacja front ≤ 200 znaków i back ≤ 1000 znaków,  
    - możliwość wyboru istniejącego decku,  
    - nowa fiszka pojawia się w liście w decku.

- ID: US-005  
  Tytuł: Przeglądanie i zarządzanie fiszkami  
  Opis: Jako użytkownik otwieram deck, widzę listę wszystkich fiszek, mogę filtrować lub wyszukiwać.  
  Kryteria akceptacji:  
    - lista wyświetla front, status powtórki i datę,  
    - istnieje pole wyszukiwania i filtr "due today" i "overdue".

- ID: US-006  
  Tytuł: Edycja pojedynczej fiszki  
  Opis: Jako użytkownik przy wybranej fiszce klikam "Edytuj", modyfikuję front/back i zapisuję zmiany.  
  Kryteria akceptacji:  
    - modal lub strona edycji otwiera się z aktualnymi danymi,  
    - walidacja długości front/back,  
    - po zapisie zmiany widoczne w liście.

- ID: US-007  
  Tytuł: Usunięcie pojedynczej fiszki  
  Opis: Jako użytkownik przy wybranej fiszce klikam "Usuń" i potwierdzam operację.  
  Kryteria akceptacji:  
    - pojawia się okno potwierdzenia,  
    - po potwierdzeniu fiszka znika z listy i baza jest aktualizowana.

- ID: US-008  
  Tytuł: Usuwanie wielu fiszek  
  Opis: Jako użytkownik zaznaczam kilka fiszek i klikam "Usuń wszystkie", aby szybko wyczyścić deck.  
  Kryteria akceptacji:  
    - zaznaczone fiszki są usuwane zbiorczo,  
    - potwierdzenie przed usunięciem.

- ID: US-009  
  Tytuł: Zarządzanie deckami  
  Opis: Jako użytkownik tworzę, edytuję nazwę i opis nowego decku oraz usuwam nieużywane decki.  
  Kryteria akceptacji:  
    - walidacja nazwy i opisu,  
    - data utworzenia automatyczna,  
    - reset progresu decku usuwa historię powtórek.

- ID: US-010  
  Tytuł: Reset progresu decku  
  Opis: Jako użytkownik na stronie decku klikam "Reset progresu", aby zacząć naukę od początku.  
  Kryteria akceptacji:  
    - pojawia się potwierdzenie,  
    - wszystkie karty oznaczone jako due i zaległe,  
    - sesje SR zaczynają się od pierwszej fiszki.

- ID: US-011  
  Tytuł: Sesja powtórek SR  
  Opis: Jako użytkownik rozpoczynam sesję powtórek na kartach due today i zaległych, oznaczam statusy.  
  Kryteria akceptacji:  
    - widzę jedną fiszkę na ekranie z opcjami "Znam", "Nie znam", "Odkładam",  
    - wybór opcji aktualizuje harmonogram w mikroserwisie SR,  
    - po zakończeniu sesji wyświetla się podsumowanie.

- ID: US-012  
  Tytuł: Odraczanie fiszki w sesji  
  Opis: Jako użytkownik wybieram "Odkładam", aby odłożyć kartę na dalszą powtórkę.  
  Kryteria akceptacji:  
    - karta zostaje przeniesiona na koniec listy sesji,  
    - liczniki powtórek aktualizowane.

- ID: US-013  
  Tytuł: Rejestracja użytkownika  
  Opis: Jako nowy użytkownik podaję e-mail i hasło, odbieram link aktywacyjny/resetu w przyszłości.  
  Kryteria akceptacji:  
    - walidacja formatu e-mail i siły hasła,  
    - po rejestracji e-mail potwierdzający,  
    - konto nieaktywne do potwierdzenia.

- ID: US-014  
  Tytuł: Logowanie użytkownika  
  Opis: Jako zarejestrowany użytkownik loguję się e-mailem i hasłem.  
  Kryteria akceptacji:  
    - walidacja poświadczeń,  
    - przekierowanie do pulpitu z deckami,  
    - informacja o błędnych danych.

- ID: US-015  
  Tytuł: Reset hasła  
  Opis: Jako użytkownik zapomniałem hasła, proszę o link resetujący na e-mail.  
  Kryteria akceptacji:  
    - formularz prośby o reset,  
    - wysłanie maila z tokenem,  
    - możliwość ustawienia nowego hasła.

- ID: US-016  
  Tytuł: Blokada konta po nieudanych próbach  
  Opis: Jako system blokuję konto po 5 nieudanych próbach logowania, wyświetlam info o blokadzie.  
  Kryteria akceptacji:  
    - licznik nieudanych prób się zwiększa,  
    - po 5 próbach login zablokowany na określony czas lub do resetu przez admina.

- ID: US-017  
  Tytuł: Wyświetlanie limitów AI  
  Opis: Jako użytkownik widzę pozostałą liczbę znaków i zapytań AI oraz fiszek do stworzenia.  
  Kryteria akceptacji:  
    - licznik zapytań/min i dzienny odliczany,  
    - gdy limit osiągnięty, przyciski generowania i zapytań są nieaktywne.

- ID: US-018  
  Tytuł: Panel administratora – zarządzanie użytkownikami  
  Opis: Jako administrator przeglądam listę użytkowników, mogę blokować/usuń konta.  
  Kryteria akceptacji:  
    - widzę tabelę z e-mailem, statusem, limitami, KPI,  
    - akcje blokuj/usuń/reset mają potwierdzenie.

- ID: US-019  
  Tytuł: Panel administratora – reset limitów  
  Opis: Jako administrator resetuję limity wybranego użytkownika manualnie.  
  Kryteria akceptacji:  
    - akcja resetuje liczniki znaków, fiszek i zapytań,  
    - użytkownik może ponownie korzystać z AI natychmiast.

- ID: US-020  
  Tytuł: Panel administratora – podgląd KPI  
  Opis: Jako administrator przeglądam globalne i per-user KPI-A i KPI-B.  
  Kryteria akceptacji:  
    - KPI liczone jako (Akceptacje + 0,5×Edycje)/Wszystkie i (Akceptacje)/Wszystkie,  
    - wartości aktualizowane realtime.

- ID: US-021  
  Tytuł: Usunięcie konta przez użytkownika (GDPR)  
  Opis: Jako użytkownik inicjuję pełne usunięcie konta, dane usuwane są po 48 h, backupy 30 dni.  
  Kryteria akceptacji:  
    - po potwierdzeniu użytkownik otrzymuje info o terminie usunięcia,  
    - po 48 h konto i dane są irrewersybilnie usunięte.

- ID: US-022  
  Tytuł: Ochrona prywatności fiszek  
  Opis: Jako zalogowany użytkownik chcę mieć pewność, że moje fiszki są widoczne tylko dla mnie i nie mogę podglądać fiszek innych użytkowników.  
  Kryteria akceptacji:  
    - widzę wyłącznie swoje decki i fiszki,  
    - próba dostępu do fiszek innych użytkowników wyświetla komunikat o braku dostępu,  
    - UI nie pokazuje zestawów ani fiszek innych użytkowników.

## 6. Metryki sukcesu
- KPI-A = (Akceptacje + 0,5 × Edycje) / Wszystkie ≥ 75 % (globalnie i per user).  
- KPI-B = (Akceptacje) / Wszystkie ≥ 75 % (globalnie i per user).  
- Użytkownicy generują co najmniej 75 % fiszek przez AI.  
- Brak krytycznych błędów w ścieżkach: rejestracja, generowanie, sesja powtórek, SR-logika.  
- Wykorzystanie AI nie przekracza założonego budżetu (np. 20 USD/mies.).
