<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial database schema setup for 10xCards
 * - Creates app schema
 * - Sets up user management tables
 * - Creates deck and card management system
 * - Implements AI job tracking
 * - Adds rate limiting table
 * - Enables Row Level Security (RLS) on all tables
 */
final class Version20240319143000_initial_schema extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database schema setup with user management, decks, cards, AI jobs, and rate limiting';
    }

    public function up(Schema $schema): void
    {
        // Create app schema
        $this->addSql('CREATE SCHEMA IF NOT EXISTS app');

        // Create user table
        $this->addSql('CREATE TABLE app.user (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT \'user\' CHECK(role IN (\'user\',\'admin\')),
            failed_login_attempts INT NOT NULL DEFAULT 0,
            locked_until TIMESTAMPTZ NULL,
            created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
            deletion_requested_at TIMESTAMPTZ NULL,
            is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
            deleted_at TIMESTAMPTZ NULL
        )');

        // Create deck table
        $this->addSql('CREATE TABLE app.deck (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL REFERENCES app.user(id) ON DELETE CASCADE,
            name VARCHAR(100) NOT NULL CHECK(char_length(name) <= 100),
            description TEXT NULL CHECK(char_length(description) <= 1000),
            created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
            is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
            deleted_at TIMESTAMPTZ NULL,
            UNIQUE(user_id, name)
        )');

        // Create card table
        $this->addSql('CREATE TABLE app.card (
            id SERIAL PRIMARY KEY,
            deck_id INT NOT NULL REFERENCES app.deck(id) ON DELETE CASCADE,
            front VARCHAR(200) NOT NULL CHECK(char_length(front) <= 200),
            back TEXT NOT NULL CHECK(char_length(back) <= 1000),
            source VARCHAR(50) NOT NULL CHECK(source IN (\'manual\',\'ai\')),
            interval INT NOT NULL DEFAULT 1,
            repetition_count INT NOT NULL DEFAULT 0,
            due_date DATE NOT NULL DEFAULT CURRENT_DATE,
            created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
            is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
            deleted_at TIMESTAMPTZ NULL
        )');

        // Create AI jobs table
        $this->addSql('CREATE TABLE app.ai_jobs (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL REFERENCES app.user(id) ON DELETE CASCADE,
            input_text_length INT NOT NULL,
            token_count INT NOT NULL,
            flashcards_count INT NOT NULL,
            duration_ms INT NOT NULL,
            status VARCHAR(50) NOT NULL CHECK(status IN (\'pending\',\'completed\',\'failed\')),
            created_at TIMESTAMPTZ NOT NULL DEFAULT now()
        )');

        // Create AI job flashcards table
        $this->addSql('CREATE TABLE app.ai_job_flashcards (
            id SERIAL PRIMARY KEY,
            ai_job_id INT NOT NULL REFERENCES app.ai_jobs(id) ON DELETE CASCADE,
            front VARCHAR(200) NOT NULL CHECK(char_length(front) <= 200),
            back TEXT NOT NULL CHECK(char_length(back) <= 1000),
            status VARCHAR(50) NOT NULL CHECK(status IN (\'accepted\',\'edited\',\'rejected\')),
            edited_front TEXT NULL,
            edited_back TEXT NULL,
            created_at TIMESTAMPTZ NOT NULL DEFAULT now()
        )');

        // Create rate limit table
        $this->addSql('CREATE TABLE app.rate_limit (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL REFERENCES app.user(id) ON DELETE CASCADE,
            window_start TIMESTAMPTZ NOT NULL,
            request_count INT NOT NULL DEFAULT 0,
            text_characters INT NOT NULL DEFAULT 0,
            flashcard_count INT NOT NULL DEFAULT 0
        )');

        // Create indexes
        $this->addSql('CREATE INDEX idx_deck_user_id ON app.deck(user_id)');
        $this->addSql('CREATE INDEX idx_card_deck_id ON app.card(deck_id)');
        $this->addSql('CREATE INDEX idx_card_created_at ON app.card(created_at)');
        $this->addSql('CREATE INDEX idx_card_due_date ON app.card(due_date)');
        $this->addSql('CREATE INDEX idx_ai_jobs_user_created ON app.ai_jobs(user_id, created_at)');
        $this->addSql('CREATE INDEX idx_ai_flashcards_job ON app.ai_job_flashcards(ai_job_id)');
        $this->addSql('CREATE INDEX idx_rate_limit_user_window ON app.rate_limit(user_id, window_start)');

        // Enable RLS on all tables
        $this->addSql('ALTER TABLE app.user ENABLE ROW LEVEL SECURITY');
        $this->addSql('ALTER TABLE app.deck ENABLE ROW LEVEL SECURITY');
        $this->addSql('ALTER TABLE app.card ENABLE ROW LEVEL SECURITY');
        $this->addSql('ALTER TABLE app.ai_jobs ENABLE ROW LEVEL SECURITY');
        $this->addSql('ALTER TABLE app.ai_job_flashcards ENABLE ROW LEVEL SECURITY');
        $this->addSql('ALTER TABLE app.rate_limit ENABLE ROW LEVEL SECURITY');

        // Create RLS policies for user table
        $this->addSql('CREATE POLICY user_select ON app.user FOR SELECT USING (id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY user_insert ON app.user FOR INSERT WITH CHECK (id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY user_update ON app.user FOR UPDATE USING (id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY user_delete ON app.user FOR DELETE USING (id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');

        // Create RLS policies for deck table
        $this->addSql('CREATE POLICY deck_select ON app.deck FOR SELECT USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY deck_insert ON app.deck FOR INSERT WITH CHECK (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY deck_update ON app.deck FOR UPDATE USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY deck_delete ON app.deck FOR DELETE USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');

        // Create RLS policies for card table
        $this->addSql('CREATE POLICY card_select ON app.card FOR SELECT USING (EXISTS (SELECT 1 FROM app.deck d WHERE d.id = deck_id AND (d.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');
        $this->addSql('CREATE POLICY card_insert ON app.card FOR INSERT WITH CHECK (EXISTS (SELECT 1 FROM app.deck d WHERE d.id = deck_id AND (d.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');
        $this->addSql('CREATE POLICY card_update ON app.card FOR UPDATE USING (EXISTS (SELECT 1 FROM app.deck d WHERE d.id = deck_id AND (d.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');
        $this->addSql('CREATE POLICY card_delete ON app.card FOR DELETE USING (EXISTS (SELECT 1 FROM app.deck d WHERE d.id = deck_id AND (d.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');

        // Create RLS policies for ai_jobs table
        $this->addSql('CREATE POLICY ai_jobs_select ON app.ai_jobs FOR SELECT USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY ai_jobs_insert ON app.ai_jobs FOR INSERT WITH CHECK (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY ai_jobs_update ON app.ai_jobs FOR UPDATE USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY ai_jobs_delete ON app.ai_jobs FOR DELETE USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');

        // Create RLS policies for ai_job_flashcards table
        $this->addSql('CREATE POLICY ai_flashcards_select ON app.ai_job_flashcards FOR SELECT USING (EXISTS (SELECT 1 FROM app.ai_jobs j WHERE j.id = ai_job_id AND (j.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');
        $this->addSql('CREATE POLICY ai_flashcards_insert ON app.ai_job_flashcards FOR INSERT WITH CHECK (EXISTS (SELECT 1 FROM app.ai_jobs j WHERE j.id = ai_job_id AND (j.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');
        $this->addSql('CREATE POLICY ai_flashcards_update ON app.ai_job_flashcards FOR UPDATE USING (EXISTS (SELECT 1 FROM app.ai_jobs j WHERE j.id = ai_job_id AND (j.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');
        $this->addSql('CREATE POLICY ai_flashcards_delete ON app.ai_job_flashcards FOR DELETE USING (EXISTS (SELECT 1 FROM app.ai_jobs j WHERE j.id = ai_job_id AND (j.user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')))');

        // Create RLS policies for rate_limit table
        $this->addSql('CREATE POLICY rate_limit_select ON app.rate_limit FOR SELECT USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY rate_limit_insert ON app.rate_limit FOR INSERT WITH CHECK (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY rate_limit_update ON app.rate_limit FOR UPDATE USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
        $this->addSql('CREATE POLICY rate_limit_delete ON app.rate_limit FOR DELETE USING (user_id = current_setting(\'app.user_id\')::int OR current_setting(\'app.role\') = \'admin\')');
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order to respect foreign key constraints
        $this->addSql('DROP TABLE IF EXISTS app.rate_limit');
        $this->addSql('DROP TABLE IF EXISTS app.ai_job_flashcards');
        $this->addSql('DROP TABLE IF EXISTS app.ai_jobs');
        $this->addSql('DROP TABLE IF EXISTS app.card');
        $this->addSql('DROP TABLE IF EXISTS app.deck');
        $this->addSql('DROP TABLE IF EXISTS app.user');
        $this->addSql('DROP SCHEMA IF EXISTS app');
    }
} 