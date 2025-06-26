<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Setup;

use Shopware\Core\Framework\Context;
use Sumedia\Wbo\Setting\Exception\WboSettingsInvalidException;
use Sumedia\Wbo\Setting\Service\SettingsService;
use Sumedia\Wbo\Setting\SumediaWboSettingStruct;
use Sumedia\Wbo\Setting\SumediaWboSettingStructValidator;

class Install extends SetupAbstract
{
    public function install(Context $context): void
    {
        $this->addDefaultConfiguration();
        $this->addWineGroupsTable();
        $this->addArticlesTable();
    }

    protected function addDefaultConfiguration(): void
    {
        if ($this->validSettingsExists()) {
            return;
        }

        foreach ((new SumediaWboSettingStruct())->jsonSerialize() as $key => $value) {
            if ($value === null || $value === []) {
                continue;
            }
            $this->systemConfig->set(SettingsService::SYSTEM_CONFIG_DOMAIN . $key, $value);
        }
    }

    protected function validSettingsExists(): bool
    {
        $keyValuePairs = $this->systemConfig->getDomain(SettingsService::SYSTEM_CONFIG_DOMAIN);

        $structData = [];
        foreach ($keyValuePairs as $key => $value) {
            $identifier = (string) \mb_substr($key, \mb_strlen(SettingsService::SYSTEM_CONFIG_DOMAIN));
            if ($identifier === '') {
                continue;
            }
            $structData[$identifier] = $value;
        }

        $settings = (new SumediaWboSettingStruct())->assign($structData);

        try {
            SumediaWboSettingStructValidator::validate($settings);
        } catch (WboSettingsInvalidException $e) {
            return false;
        }

        return true;
    }

    protected function addWineGroupsTable(): void
    {
        $wboWineGroupsExists = $this->ifTableExists('wbo_wine-groups');

        if ($wboWineGroupsExists) {
            return;
        }

        $this->connection->exec('
            CREATE TABLE IF NOT EXISTS wbo_wine_groups
            (
                id BINARY(16) NOT NULL,
                group_id INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (`id`)        
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    protected function addArticlesTable(): void
    {
        $wboArticlesExists = $this->ifTableExists('wbo_articles');

        if ($wboArticlesExists) {
            return;
        }

        $this->connection->exec('
            CREATE TABLE wbo_articles
            (
                id BINARY(16) NOT NULL,
                article_number VARCHAR(64) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                description TEXT,
                weight DECIMAL(13,4),
                price DECIMAL(13,4) NOT NULL,
                tax_percent DECIMAL(13,4) NOT NULL,
                is_free_shipping TINYINT(1),
                notice VARCHAR(255),
                group_id BINARY(16),
                is_wine TINYINT(1),
                shopnotice VARCHAR(255),
                kiloprice DECIMAL(13,4),
                waregroup VARCHAR(255),
                stock INT,
                
                allergens VARCHAR(255),
                apnr VARCHAR(64),
                awards VARCHAR(255),
                caloric_value DECIMAL(13,4),
                cultivation VARCHAR(255),
                location VARCHAR(255),
                development VARCHAR(255),
                drinking_temperature DECIMAL(13,4),
                expertise TEXT,
                grounds VARCHAR(255),
                has_sulfite TINYINT(1),
                is_drunken TINYINT(1),
                is_storable TINYINT(1),
                kind VARCHAR(255),
                alcohol DECIMAL(13,4),
                litre DECIMAL(13,4),
                litre_price DECIMAL(13,4),
                nuances VARCHAR(255),
                protein DECIMAL(13,4),
                quality VARCHAR(128),
                sugar DECIMAL(13,4),
                taste VARCHAR(128),
                `year` VARCHAR(64),
                acid DECIMAL(13,4),
                
                image_1 VARCHAR(255),
                image_2 VARCHAR(255),
                image_3 VARCHAR(255),
                image_4 VARCHAR(255),
                big_image_1 VARCHAR(255),
                big_image_2 VARCHAR(255),
                big_image_3 VARCHAR(255),
                big_image_4 VARCHAR(255),
                
                created_at DATETIME NOT NULL,
                updated_at DATETIME,
                imported_at DATETIME,
                
                PRIMARY KEY (`id`)
                        
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $this->connection->exec('
            ALTER TABLE wbo_articles
                ADD CONSTRAINT `fk.wbo_articles.group_id` 
                    FOREIGN KEY (group_id) 
                    REFERENCES wbo_wine_groups(`id`) 
                    ON DELETE CASCADE ON UPDATE CASCADE
        ');
    }

    private function ifTableExists(string $table): bool
    {
        /** @var \Doctrine\DBAL\Driver\PDO\Statement $res */
        $res = $this->connection->query('SHOW TABLES');
        foreach ($res->fetchAllAssociative() as $_table) {
            $_table = current($_table);
            if ($_table === $table) {
                return true;
            }
        }
        return false;
    }
}
