<?php declare(strict_types=1);

namespace Wbm\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1715011084ProductReviewVote extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1715011084;
    }

    public function update(Connection $connection): void
    {
        $query = <<<'SQL'
            CREATE TABLE product_review_vote (
                id BINARY(16) NOT NULL, 
                product_review_id BINARY(16) NOT NULL, 
                customer_id BINARY(16) DEFAULT NULL, 
                sales_channel_id BINARY(16) NOT NULL, 
                positive_review TINYINT(1) DEFAULT 0, 
                created_at DATETIME NOT NULL, 
                updated_at DATETIME DEFAULT NULL, 
                PRIMARY KEY(id)) 
            DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        $query = <<<'SQL'
            DROP TABLE product_review_vote;
SQL;

        $connection->executeStatement($query);
    }
}
