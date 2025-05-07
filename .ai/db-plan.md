# Database Schema Plan for 10xCards

## 1. Tables

### app.user
| Column                  | Type                         | Constraints                                                               |
|-------------------------|------------------------------|---------------------------------------------------------------------------|
| id                      | SERIAL                       | PRIMARY KEY                                                               |
| email                   | VARCHAR(255)                 | NOT NULL, UNIQUE                                                          |
| password_hash           | VARCHAR(255)                 | NOT NULL                                                                  |
| role                    | VARCHAR(50)                  | NOT NULL, DEFAULT 'user', CHECK(role IN ('user','admin'))                 |
| failed_login_attempts   | INT                          | NOT NULL, DEFAULT 0                                                       |
| locked_until            | TIMESTAMPTZ                  | NULL                                                                      |
| created_at              | TIMESTAMPTZ                  | NOT NULL, DEFAULT now()                                                   |
| deletion_requested_at   | TIMESTAMPTZ                  | NULL                                                                      |
| is_deleted              | BOOLEAN                      | NOT NULL, DEFAULT FALSE                                                   |
| deleted_at              | TIMESTAMPTZ                  | NULL                                                                      |

### app.deck
| Column        | Type             | Constraints                                                             |
|---------------|------------------|-------------------------------------------------------------------------|
| id            | SERIAL           | PRIMARY KEY                                                             |
| user_id       | INT              | NOT NULL, REFERENCES app.user(id) ON DELETE CASCADE                     |
| name          | VARCHAR(100)     | NOT NULL, CHECK(char_length(name) <= 100)                               |
| description   | TEXT             | NULL, CHECK(char_length(description) <= 1000)                           |
| created_at    | TIMESTAMPTZ      | NOT NULL, DEFAULT now()                                                 |
| is_deleted    | BOOLEAN          | NOT NULL, DEFAULT FALSE                                                 |
| deleted_at    | TIMESTAMPTZ      | NULL                                                                    |

**Constraints:**
- UNIQUE(user_id, name)

### app.card
| Column           | Type             | Constraints                                                                  |
|------------------|------------------|------------------------------------------------------------------------------|
| id               | SERIAL           | PRIMARY KEY                                                                  |
| deck_id          | INT              | NOT NULL, REFERENCES app.deck(id) ON DELETE CASCADE                          |
| front            | VARCHAR(200)     | NOT NULL, CHECK(char_length(front) <= 200)                                   |
| back             | TEXT             | NOT NULL, CHECK(char_length(back) <= 1000)                                   |
| source           | VARCHAR(50)      | NOT NULL, CHECK(source IN ('manual','ai'))                                   |
| interval         | INT              | NOT NULL, DEFAULT 1                                                          |
| repetition_count | INT              | NOT NULL, DEFAULT 0                                                          |
| due_date         | DATE             | NOT NULL, DEFAULT CURRENT_DATE                                               |
| created_at       | TIMESTAMPTZ      | NOT NULL, DEFAULT now()                                                      |
| is_deleted       | BOOLEAN          | NOT NULL, DEFAULT FALSE                                                      |
| deleted_at       | TIMESTAMPTZ      | NULL                                                                         |

### app.ai_jobs
| Column             | Type             | Constraints                                                            |
|--------------------|------------------|------------------------------------------------------------------------|
| id                 | SERIAL           | PRIMARY KEY                                                            |
| user_id            | INT              | NOT NULL, REFERENCES app.user(id) ON DELETE CASCADE                    |
| input_text_length  | INT              | NOT NULL                                                               |
| token_count        | INT              | NOT NULL                                                               |
| flashcards_count   | INT              | NOT NULL                                                               |
| duration_ms        | INT              | NOT NULL                                                               |
| status             | VARCHAR(50)      | NOT NULL, CHECK(status IN ('pending','completed','failed'))            |
| created_at         | TIMESTAMPTZ      | NOT NULL, DEFAULT now()                                                |

### app.ai_job_flashcards
| Column       | Type             | Constraints                                                                |
|--------------|------------------|----------------------------------------------------------------------------|
| id           | SERIAL           | PRIMARY KEY                                                                |
| ai_job_id    | INT              | NOT NULL, REFERENCES app.ai_jobs(id) ON DELETE CASCADE                     |
| front        | VARCHAR(200)     | NOT NULL, CHECK(char_length(front) <= 200)                                 |
| back         | TEXT             | NOT NULL, CHECK(char_length(back) <= 1000)                                 |
| status       | VARCHAR(50)      | NOT NULL, CHECK(status IN ('accepted','edited','rejected'))                |
| edited_front | TEXT             | NULL                                                                       |
| edited_back  | TEXT             | NULL                                                                       |
| created_at   | TIMESTAMPTZ      | NOT NULL, DEFAULT now()                                                    |

### app.rate_limit
| Column          | Type             | Constraints                                                           |
|-----------------|------------------|-----------------------------------------------------------------------|
| id              | SERIAL           | PRIMARY KEY                                                           |
| user_id         | INT              | NOT NULL, REFERENCES app.user(id) ON DELETE CASCADE                   |
| window_start    | TIMESTAMPTZ      | NOT NULL                                                              |
| request_count   | INT              | NOT NULL, DEFAULT 0                                                   |
| text_characters | INT              | NOT NULL, DEFAULT 0                                                   |
| flashcard_count | INT              | NOT NULL, DEFAULT 0                                                   |

## 2. Relationships
- **User → Deck**: one-to-many via `deck.user_id`
- **Deck → Card**: one-to-many via `card.deck_id`
- **User → AIJob**: one-to-many via `ai_jobs.user_id`
- **AIJob → AIJobFlashcards**: one-to-many via `ai_job_flashcards.ai_job_id`
- **User → RateLimit**: one-to-many via `rate_limit.user_id`

## 3. Indexes
```sql
CREATE INDEX idx_deck_user_id           ON app.deck(user_id);
CREATE INDEX idx_card_deck_id           ON app.card(deck_id);
CREATE INDEX idx_card_created_at        ON app.card(created_at);
CREATE INDEX idx_card_due_date          ON app.card(due_date);
CREATE INDEX idx_ai_jobs_user_created   ON app.ai_jobs(user_id, created_at);
CREATE INDEX idx_ai_flashcards_job      ON app.ai_job_flashcards(ai_job_id);
CREATE INDEX idx_rate_limit_user_window ON app.rate_limit(user_id, window_start);
```

## 4. Row-Level Security (RLS)
Enable RLS on every table in the `app` schema and restrict access to the owner's rows or admin:
```sql
-- Users
ALTER TABLE app.user ENABLE ROW LEVEL SECURITY;
CREATE POLICY user_access ON app.user
  FOR ALL
  USING (id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
  WITH CHECK (id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin');

-- Decks
ALTER TABLE app.deck ENABLE ROW LEVEL SECURITY;
CREATE POLICY deck_access ON app.deck
  FOR ALL
  USING (user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
  WITH CHECK (user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin');

-- Cards
ALTER TABLE app.card ENABLE ROW LEVEL SECURITY;
CREATE POLICY card_access ON app.card
  FOR ALL
  USING (
    EXISTS (
      SELECT 1 FROM app.deck d
      WHERE d.id = app.card.deck_id
        AND (d.user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
    )
  )
  WITH CHECK (
    EXISTS (
      SELECT 1 FROM app.deck d
      WHERE d.id = app.card.deck_id
        AND (d.user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
    )
  );

-- AI Jobs
ALTER TABLE app.ai_jobs ENABLE ROW LEVEL SECURITY;
CREATE POLICY ai_jobs_access ON app.ai_jobs
  FOR ALL
  USING (user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
  WITH CHECK (user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin');

-- AI Job Flashcards
ALTER TABLE app.ai_job_flashcards ENABLE ROW LEVEL SECURITY;
CREATE POLICY ai_flashcards_access ON app.ai_job_flashcards
  FOR ALL
  USING (
    EXISTS (
      SELECT 1 FROM app.ai_jobs j
      WHERE j.id = app.ai_job_flashcards.ai_job_id
        AND (j.user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
    )
  )
  WITH CHECK (
    EXISTS (
      SELECT 1 FROM app.ai_jobs j
      WHERE j.id = app.ai_job_flashcards.ai_job_id
        AND (j.user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
    )
  );

-- Rate Limit
ALTER TABLE app.rate_limit ENABLE ROW LEVEL SECURITY;
CREATE POLICY rate_limit_access ON app.rate_limit
  FOR ALL
  USING (user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin')
  WITH CHECK (user_id = current_setting('app.user_id')::int OR current_setting('app.role') = 'admin');
```  

## 5. Additional Notes
- **Soft-delete** is implemented via `is_deleted` and `deleted_at`; a periodic cron job physically removes records older than 48 hours.
- The schema is normalized to 3NF; denormalization and partitioning/full-text search are deferred to future iterations. 