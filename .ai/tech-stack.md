# Tech Stack dla 10xCards (PHP/Symfony + PostgreSQL)

1. Backend
   • PHP 8.2 + Symfony 6.4 LTS  
   • Doctrine ORM + Migrations (PostgreSQL)  
   • API-first via API Platform – automatycznie REST/GraphQL dla encji (User, Deck, Card, AIJob)  
   • Symfony Security + LexikJWTAuthenticationBundle – JWT dla API, RateLimiter na logowanie, rejestrację, endpoint AI  
   • Symfony Mailer + Messenger – wysyłka maili (aktywacja, reset hasła), kolejka asynchroniczna  

2. Frontend (server-side renderowane)
   • Twig + fetch/Axios do własnego API  
   • Symfony UX (Stimulus + Turbo) – modale, filtry, bulk‐akcje  
   • Tailwind CSS 4 (Webpack Encore lub Vite)  

3. Integracja AI
   • Symfony HttpClient (PSR-18) + Openrouter.ai/OpenAI-PHP  
   • Serwis domenowy: walidacja limitów (10 000 znaków/5 zapytań/min, 500 kart/dzień), retry, cache, statystyki AI vs. ręczne  

4. Baza i RODO
   • PostgreSQL (Managed DB lub własny serwer)  
   • RLS w bazie lub Symfony Voters – dostęp tylko do własnych decków/fiszek  
   • Usunięcie konta: komenda Messenger uruchamiana przez cron po 48 h  
   • Backupy: cron + `pg_dump` → DigitalOcean Spaces/S3 (retencja 30 dni)  

5. Zadania cykliczne
   • Symfony Commands (`app:delete-user`, `app:backup-db`) wywoływane przez cron  
   • Opcjonalnie: Symfony Messenger Scheduler  

6. CI/CD i hosting
   • Docker Compose (PHP-FPM, Nginx, PostgreSQL, Redis) – dev  
   • GitHub Actions: PHPStan + Psalm + CS-Fixer, PHPUnit, build assets, deploy (Droplet/App Platform)  
   • HTTPS (Let's Encrypt), env vars / Secret Manager  

7. Bezpieczeństwo i skalowalność
   • Symfony Security policies + RateLimiter + JWT + RLS  
   • Regularne aktualizacje (Dependabot, Symfony CLI)  
   • Skalowanie: więcej workerów Messenger, load-balancer, rozdzielenie usług 