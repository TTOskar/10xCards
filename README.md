# 10xCards

[![Project Status](https://img.shields.io/badge/status-MVP-blue.svg)](#project-status)  [![PHP Version](https://img.shields.io/badge/php-8.2-brightgreen.svg)](#tech-stack)  [![Symfony Version](https://img.shields.io/badge/symfony-7.2-lightgrey.svg)](#tech-stack)  [![License](https://img.shields.io/badge/license-Proprietary-lightgrey.svg)](#license)

## Table of Contents

- [Project Description](#project-description)
- [Tech Stack](#tech-stack)
- [Getting Started Locally](#getting-started-locally)
- [Available Scripts](#available-scripts)
- [Project Scope](#project-scope)
- [Project Status](#project-status)
- [License](#license)

## Project Description

10xCards is a web application that enables the rapid creation and management of educational flashcards powered by artificial intelligence, integrated with an external open-source spaced repetition microservice. It is designed to significantly accelerate and simplify the process of learning by transforming user-provided text into high-quality flashcards.

Key features include:
- AI-powered card generation from pasted text (10,000 characters/request, 5 requests/min, daily reset at 00:00 UTC)
- Three-step card approval workflow (Accept/Edit/Reject) with bulk actions
- Manual card creation with validation (front ≤200 chars, back ≤1000 chars)
- Deck management with progress tracking and reset capabilities
- Integration with external spaced repetition service (due today/overdue cards, manual postponing)
- User accounts with daily limits (500 cards/day) and secure authentication
- Admin panel for user management and KPI monitoring
- GDPR compliance with automatic data deletion (48h) and 30-day backups

## Tech Stack

### Backend
- PHP 8.2
- Symfony 7.2 LTS
- Doctrine ORM 3.x + Migrations 3.x (PostgreSQL)
- API Platform 3.x (REST & GraphQL)
- LexikJWTAuthenticationBundle 3.x
- Symfony Components: HttpClient, Messenger, Mailer, RateLimiter, Security
- OpenAI PHP Client 0.12.x (AI integration)

### Frontend
- Twig 3.x templates
- Symfony UX (Stimulus 3.x + Turbo 7.x)
- Tailwind CSS 4.x + Webpack Encore

### Infrastructure
- PostgreSQL 15+ with RLS (Row-Level Security)
- Docker Compose (PHP-FPM 8.2, Nginx, PostgreSQL, Redis)
- GitHub Actions CI/CD (PHPStan, Psalm, PHPUnit)
- Symfony Voters for access control

## Getting Started Locally

### Prerequisites

- Docker 24.0+
- PHP >= 8.2
- Composer 2.6+
- PostgreSQL 15+

### Installation

1. Clone and configure:
```bash
git clone https://github.com/<your-org>/10xCards.git
cd 10xCards
cp .env .env.local  # Configure DB, JWT_SECRET, OPENAI_KEY
```

2. Install dependencies:
```bash
composer install
```

3. Start services:
```bash
docker-compose up -d
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

4. Access at `http://localhost:8000`

## Available Scripts

### Composer
```bash
composer install  # Install PHP dependencies
composer update   # Update dependencies
composer test     # Run PHPUnit tests
```

### Symfony Console
```bash
php bin/console doctrine:migrations:migrate  # Run migrations
php bin/console app:backup-db                # Create DB backup
php bin/console app:delete-user              # GDPR user deletion
```


## Project Scope

### In-Scope (MVP)
✅ AI Card Generation:  
- 10k characters/request, 5 reqs/min, daily limits  
- Card validation (front ≤200, back ≤1000 chars)  
- Bulk accept/edit/reject workflow  

✅ Spaced Repetition:  
- Integration with external OSS microservice  
- Due today/overdue cards handling  
- Manual postponing and progress reset  

✅ Security:  
- JWT authentication  
- Password policy (8+ chars, special chars)  
- Account lockout after 5 failed attempts  

✅ GDPR Compliance:  
- Full account deletion within 48h  
- 30-day encrypted backups  
- Data isolation between users  

### Out-of-Scope
❌ Custom SRS algorithm development  
❌ File imports (PDF/DOCX/CSV)  
❌ Social/sharing features  
❌ Mobile applications  
❌ Advanced analytics  

## Project Status
This project is currently in active development as an MVP. Core features are implemented and under testing. 
Contributions, issue reports, and feedback are welcome.
**Current Version:** MVP (1.0.0-beta)  
**Active Development:** Core features complete, testing phase  
**Key Metrics:**  
- 95% PHPStan/Psalm coverage  
- 87% test coverage (PHPUnit)  
- <2s average API response time  

## License

Proprietary software © 2025 10xCards Team. All rights reserved. 