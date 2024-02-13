<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Response;

class GetBestand extends ResponseAbstract
{
    public function getStock(): int
    {
        foreach ($this->get('item') as $data) {
            if (isset($data['artikel_bestand'])) {
                return (int) $data['artikel_bestand'];
            }
        }
        return 0;
    }
}
