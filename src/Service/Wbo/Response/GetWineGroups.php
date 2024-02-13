<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Response;

use Sumedia\Wbo\Service\Wbo\Response\GetWineGroups\Item;

class GetWineGroups extends ResponseAbstract
{
    public function getGroups(): array
    {
        $items = $this->get('item');
        if (!is_array($items)) {
            return [];
        }

        $groups = [];

        foreach ($items as $groupData) {
            if (!isset($groupData['grp_beschreibung'])) {
                continue;
            }
            $groups[] = new Item($groupData);
        }

        return $groups;
    }
}
