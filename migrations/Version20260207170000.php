<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Insert sample doctors for the appointment form.
 */
final class Version20260207170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert sample doctors into medecin table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO medecin (first_name, last_name, specialite, email, phone) VALUES
            ('Jennifer', 'Morgan', 'Cardiology', 'j.morgan@medinest.com', '+1 555 100 2001'),
            ('Robert', 'Kim', 'Neurology', 'r.kim@medinest.com', '+1 555 100 2002'),
            ('Sarah', 'Thompson', 'Pediatrics', 's.thompson@medinest.com', '+1 555 100 2003'),
            ('Michael', 'Rivera', 'Orthopedics', 'm.rivera@medinest.com', '+1 555 100 2004'),
            ('Emily', 'Chen', 'General Medicine', 'e.chen@medinest.com', '+1 555 100 2005'),
            ('David', 'Wilson', 'Emergency', 'd.wilson@medinest.com', '+1 555 100 2006')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM medecin WHERE email IN (
            'j.morgan@medinest.com',
            'r.kim@medinest.com',
            's.thompson@medinest.com',
            'm.rivera@medinest.com',
            'e.chen@medinest.com',
            'd.wilson@medinest.com'
        )");
    }
}
