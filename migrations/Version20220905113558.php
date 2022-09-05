<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220905113558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `author_to_book` (author_id BIGINT NOT NULL, book_id BIGINT NOT NULL) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE author_to_book ADD CONSTRAINT author_to_book_author_id_fk FOREIGN KEY (author_id) REFERENCES author (id)');
        $this->addSql('ALTER TABLE author_to_book ADD CONSTRAINT author_to_book_book_id_fk FOREIGN KEY (book_id) REFERENCES book (id)');

        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331F675F31B;');
        $this->addSql('ALTER TABLE book DROP COLUMN author_id;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
