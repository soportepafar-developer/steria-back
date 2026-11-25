<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea las tablas proyectos y proyecto_figuras';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS proyectos (
            id INT AUTO_INCREMENT NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            descripcion LONGTEXT DEFAULT NULL,
            usuario_id INT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS proyecto_figuras (
            id INT AUTO_INCREMENT NOT NULL,
            proyecto_id INT NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            tipo VARCHAR(50) NOT NULL,
            coordenadas LONGTEXT NOT NULL,
            radio DOUBLE PRECISION DEFAULT NULL,
            color VARCHAR(7) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS proyecto_figuras');
        $this->addSql('DROP TABLE IF EXISTS proyectos');
    }
}
