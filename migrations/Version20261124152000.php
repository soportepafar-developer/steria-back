<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261124152000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea tablas de configuración (esquemas_regionales, esquema_region_estados, config_elementos) y vincula regiones a esquemas';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE esquemas_regionales (
            id INT AUTO_INCREMENT NOT NULL,
            clave VARCHAR(50) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            es_activo TINYINT(1) DEFAULT 0 NOT NULL,
            es_predeterminado TINYINT(1) DEFAULT 0 NOT NULL,
            UNIQUE INDEX UNIQ_ESQUEMAS_CLAVE (clave),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE regiones ADD esquema_id INT DEFAULT NULL, ADD clave VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE regiones ADD CONSTRAINT FK_REGIONES_ESQUEMA FOREIGN KEY (esquema_id) REFERENCES esquemas_regionales (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_REGIONES_ESQUEMA ON regiones (esquema_id)');

        $this->addSql('CREATE TABLE esquema_region_estados (
            id INT AUTO_INCREMENT NOT NULL,
            esquema_id INT NOT NULL,
            region_id INT NOT NULL,
            estado_id INT NOT NULL,
            INDEX IDX_ERE_ESQUEMA (esquema_id),
            INDEX IDX_ERE_REGION (region_id),
            INDEX IDX_ERE_ESTADO (estado_id),
            UNIQUE INDEX uniq_esquema_estado (esquema_id, estado_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE esquema_region_estados ADD CONSTRAINT FK_ERE_ESQUEMA FOREIGN KEY (esquema_id) REFERENCES esquemas_regionales (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE esquema_region_estados ADD CONSTRAINT FK_ERE_REGION FOREIGN KEY (region_id) REFERENCES regiones (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE esquema_region_estados ADD CONSTRAINT FK_ERE_ESTADO FOREIGN KEY (estado_id) REFERENCES estados (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE config_elementos (
            id INT AUTO_INCREMENT NOT NULL,
            clave VARCHAR(50) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            icono VARCHAR(100) NOT NULL,
            color VARCHAR(7) NOT NULL,
            visible TINYINT(1) DEFAULT 1 NOT NULL,
            orden SMALLINT DEFAULT 0 NOT NULL,
            UNIQUE INDEX UNIQ_CONFIG_ELEMENTOS_CLAVE (clave),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE config_elementos');
        $this->addSql('DROP TABLE esquema_region_estados');
        $this->addSql('ALTER TABLE regiones DROP FOREIGN KEY FK_REGIONES_ESQUEMA');
        $this->addSql('DROP INDEX IDX_REGIONES_ESQUEMA ON regiones');
        $this->addSql('ALTER TABLE regiones DROP esquema_id, DROP clave');
        $this->addSql('DROP TABLE esquemas_regionales');
    }
}
