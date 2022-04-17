<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220416074421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create « urls » table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE urls (id INT AUTO_INCREMENT NOT NULL, original VARCHAR(255) NOT NULL, shortened VARCHAR(6) NOT NULL, UNIQUE INDEX UNIQ_2A9437A12F727085 (original), UNIQUE INDEX UNIQ_2A9437A178B5DC1 (shortened), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE urls');
    }
}