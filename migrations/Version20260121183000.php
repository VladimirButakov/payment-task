<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Миграция с тестовыми данными
 */
final class Version20260121183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление тестовых данных: продуктов, купонов и налогов';
    }

    public function up(Schema $schema): void
    {
        // Добавляем продукты
        $this->addSql("INSERT INTO product (name, price) VALUES 
            ('Iphone', 100.00),
            ('Наушники', 20.00),
            ('Чехол', 10.00)
        ");

        // Добавляем купоны
        $this->addSql("INSERT INTO coupon (code, type, value) VALUES 
            ('D15', 'percent', 15.00),
            ('F10', 'fixed', 10.00),
            ('P10', 'percent', 10.00),
            ('P100', 'percent', 100.00)
        ");

        // Добавляем налоги с паттернами валидации
        $this->addSql("INSERT INTO tax (country_code, rate, tax_number_pattern) VALUES 
            ('DE', 19.00, '^DE[0-9]{9}\$'),
            ('IT', 22.00, '^IT[0-9]{11}\$'),
            ('FR', 20.00, '^FR[A-Z]{2}[0-9]{9}\$'),
            ('GR', 24.00, '^GR[0-9]{9}\$')
        ");
    }

    public function down(Schema $schema): void
    {
        // Удаляем тестовые данные в обратном порядке
        $this->addSql("DELETE FROM tax WHERE country_code IN ('DE', 'IT', 'FR', 'GR')");
        $this->addSql("DELETE FROM coupon WHERE code IN ('D15', 'F10', 'P10', 'P100')");
        $this->addSql("DELETE FROM product WHERE name IN ('Iphone', 'Наушники', 'Чехол')");
    }
}

