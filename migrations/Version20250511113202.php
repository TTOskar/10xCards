<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511113202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make user_id nullable in ai_jobs table to support anonymous users';
    }

    public function up(Schema $schema): void
    {
        // Drop the foreign key constraint first
        $this->addSql('ALTER TABLE app.ai_jobs DROP CONSTRAINT ai_jobs_user_id_fkey');
        
        // Modify the column to allow NULL values
        $this->addSql('ALTER TABLE app.ai_jobs ALTER COLUMN user_id DROP NOT NULL');
        
        // Add the foreign key constraint back with ON DELETE SET NULL
        $this->addSql('ALTER TABLE app.ai_jobs ADD CONSTRAINT ai_jobs_user_id_fkey 
            FOREIGN KEY (user_id) REFERENCES app.user(id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove NULL values if any exist (convert to 0 or another default)
        $this->addSql('UPDATE app.ai_jobs SET user_id = 0 WHERE user_id IS NULL');
        
        // Drop the foreign key constraint
        $this->addSql('ALTER TABLE app.ai_jobs DROP CONSTRAINT ai_jobs_user_id_fkey');
        
        // Make the column NOT NULL again
        $this->addSql('ALTER TABLE app.ai_jobs ALTER COLUMN user_id SET NOT NULL');
        
        // Add the original foreign key constraint back
        $this->addSql('ALTER TABLE app.ai_jobs ADD CONSTRAINT ai_jobs_user_id_fkey 
            FOREIGN KEY (user_id) REFERENCES app.user(id)');
    }
}
