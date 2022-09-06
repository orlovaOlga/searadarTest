<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220906083520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `author` (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `book` (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE author_to_book (book_id BIGINT NOT NULL, author_id BIGINT NOT NULL, INDEX IDX_69312DBD16A2B381 (book_id), INDEX IDX_69312DBDF675F31B (author_id), PRIMARY KEY(book_id, author_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE author_to_book ADD CONSTRAINT FK_69312DBD16A2B381 FOREIGN KEY (book_id) REFERENCES `book` (id)');
        $this->addSql('ALTER TABLE author_to_book ADD CONSTRAINT FK_69312DBDF675F31B FOREIGN KEY (author_id) REFERENCES `author` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE author_to_book DROP FOREIGN KEY FK_69312DBD16A2B381');
        $this->addSql('ALTER TABLE author_to_book DROP FOREIGN KEY FK_69312DBDF675F31B');
        $this->addSql('DROP TABLE `author`');
        $this->addSql('DROP TABLE `book`');
        $this->addSql('DROP TABLE author_to_book');
    }
}
