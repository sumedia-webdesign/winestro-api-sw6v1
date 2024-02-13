<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Response\GetWineGroups;

class Item
{
    protected $groupId = 0;
    protected $name = '';
    protected $description = '';

    public function __construct(array $groupData)
    {
        $description = is_array($groupData['grp_beschreibung'])
            ? current($groupData['grp_beschreibung'])
            : $groupData['grp_beschreibung'];
        if (is_bool($description)) {
            $description = '';
        }

        $this->setGroupId((int)$groupData['id_grp']);
        $this->setName($groupData['grp_name']);
        $this->setDescription($description);
    }

    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
