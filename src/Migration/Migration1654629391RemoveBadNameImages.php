<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Lcobucci\JWT\Exception;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Validation\ConstraintViolationExceptionInterface;

class Migration1654629391RemoveBadNameImages extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1654629391;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
        $this->removeBadImageNames($connection);
    }

    private function removeBadImageNames(Connection $connection): void
    {
        $deleteMediaIds = [];
        $result = $connection->executeQuery("SELECT id, file_name FROM media WHERE file_name REGEXP '^wbo_'");
        foreach ($result->fetchAllAssociative() as $media) {
            if (strlen($media['file_name']) === 25) {
                $deleteMediaIds[] = $media['id'];
            }
        }
        if (count($deleteMediaIds)) {
            foreach ($deleteMediaIds as $mediaId) {
                try {
                    $connection->executeStatement('DELETE FROM product_media WHERE media_id = ?', [$mediaId]);
                    $connection->executeStatement('DELETE FROM media WHERE id = ?', [$mediaId]);
                } catch (ConstraintViolationException $e) {}
            }
        }
    }
}
