<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command;

use Shopware\Core\Framework\Context;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;
use Sumedia\Wbo\Service\Wbo\Request\RequestInterface;

class SetWineGroups extends AbstractCommand implements CommandInterface
{
    /** @var EntityRepository */
    protected $wineGroupsRepository;

    /** @var RequestInterface */
    protected $getWineGroups;

    /** @var WboConfig */
    protected $wboConfig;

    /** @var ConnectorInterface */
    protected $connector;

    public function __construct(
        LoggerInterface $debugLogger,
        LoggerInterface $errorLogger,
        WboConfig $wboConfig,
        EntityRepository $wineGroupsRepository,
        RequestInterface $getWineGroups,
        ConnectorInterface $connector
    ) {
        parent::__construct($errorLogger, $debugLogger, $wboConfig);
        $this->wineGroupsRepository = $wineGroupsRepository;
        $this->getWineGroups = $getWineGroups;
        $this->connector = $connector;
    }

    public function execute(): void
    {
        $this->debug('wbo: setting wine groups');

        if (!$this->isActive()) {
            $this->debug('wbo is not active');
            return;
        }

        try {
            /** @var \Sumedia\Wbo\Service\Wbo\Response\GetWineGroups $response */
            $response = $this->connector->execute($this->getWineGroups);
            if (!$response->isSuccessful()) {
                throw new \RuntimeException('couldn\'t fetch GetWineGroups: ' . $response->getError());
            }
            $groups = $response->getGroups();
            $this->setGroups($groups);
        } catch (\Throwable $e) {
            $this->logException($e);
        }
    }

    protected function setGroups(array $groups): void
    {
        $groupsCollection = $this->wineGroupsRepository->search(
            new Criteria(),
            Context::createDefaultContext()
        );
        $allGroups = $groupsCollection->getElements();

        foreach ($groups as $group) {

            $groupId = Uuid::randomHex();
            foreach ($allGroups as $key => $oneGroup) {
                if ($oneGroup->getGroupId() == $group->getGroupId()) {
                    $groupId = $oneGroup->getId();
                    unset($allGroups[$key]);
                    break;
                }
            }

            $data = [
                'id' => $groupId,
                'groupId' => $group->getGroupId(),
                'name' => $group->getName(),
                'description' => $group->getDescription() ?: ' ',
                'created_at' => date('d-m-Y H:i:s')
            ];

            $this->wineGroupsRepository->upsert([$data], Context::createDefaultContext());
        }

        // delete unused groups
        foreach ($allGroups as $group) {
            $this->wineGroupsRepository->delete([['id' => $group->getId()]], Context::createDefaultContext());
        }
    }
}
