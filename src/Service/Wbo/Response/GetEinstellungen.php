<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Response;

use Sumedia\Wbo\Service\Wbo\Command\SetAbroadDiscounts;
use Sumedia\Wbo\Service\Wbo\Command\SetInlandDiscounts;

class GetEinstellungen extends ResponseAbstract
{
    public function getInlandFreeShippingType(): string
    {
        $freeShippingType = $this->get('versandkosten');
        return (int) (is_numeric($freeShippingType) ? $freeShippingType : SetInlandDiscounts::FREE_SHIPPING_TYPE_NONE);
    }

    public function getInlandFreeShippingAmount(): float
    {
        return (float) ($this->get('freiab') ?: 0);
    }

    public function getAbroadFreeShippingType(): string
    {
        $freeShippingType = $this->get('versandfrei_ausland');
        return (int) (is_numeric($freeShippingType) ? $freeShippingType : SetAbroadDiscounts::FREE_SHIPPING_TYPE_NONE);
    }

    public function getAbroadFreeShippingAmount(): float
    {
        return (float) ($this->get('freiab_ausland') ?: 0);
    }

    public function getInlandCode(): string
    {
        return $this->get('land') ?: 'DE';
    }

    public function getInlandDeliveries(): array
    {
        $deliveries = [];
        if ($this->get('aktiv6')) {
            $deliveries[6] = $this->get('sechs');
        }
        if ($this->get('aktiv12')) {
            $deliveries[12] = $this->get('zwoelf');
        }
        if ($this->get('aktiv15')) {
            $deliveries[15] = $this->get('fuenfzehn');
        }
        if ($this->get('aktiv16')) {
            $deliveries[16] = $this->get('sechtzehn');
        }
        if ($this->get('aktiv18')) {
            $deliveries[18] = $this->get('achtzehn');
        }
        if ($this->get('aktiv21')) {
            $deliveries[21] = $this->get('einundzwanzig');
        }
        return $deliveries;
    }

    public function getEuDeliveries(): array
    {
        $deliveries = [];
        if ($this->get('aktiv6_eu')) {
            $deliveries[6] = $this->get('sechs_eu');
        }
        if ($this->get('aktiv12_eu')) {
            $deliveries[12] = $this->get('zwoelf_eu');
        }
        if ($this->get('aktiv15_eu')) {
            $deliveries[15] = $this->get('fuenfzehn_eu');
        }
        if ($this->get('aktiv16_eu')) {
            $deliveries[16] = $this->get('sechtzehn_eu');
        }
        if ($this->get('aktiv18_eu')) {
            $deliveries[18] = $this->get('achtzehn_eu');
        }
        if ($this->get('aktiv21_eu')) {
            $deliveries[21] = $this->get('einundzwanzig_eu');
        }
        return $deliveries;
    }

    public function getWorldDeliveries(): array
    {
        $deliveries = [];
        if ($this->get('aktiv6_ww')) {
            $deliveries[6] = $this->get('sechs_ww');
        }
        if ($this->get('aktiv12_ww')) {
            $deliveries[12] = $this->get('zwoelf_ww');
        }
        if ($this->get('aktiv15_ww')) {
            $deliveries[15] = $this->get('fuenfzehn_ww');
        }
        if ($this->get('aktiv16_ww')) {
            $deliveries[16] = $this->get('sechtzehn_ww');
        }
        if ($this->get('aktiv18_ww')) {
            $deliveries[18] = $this->get('achtzehn_ww');
        }
        if ($this->get('aktiv21_ww')) {
            $deliveries[21] = $this->get('einundzwanzig_ww');
        }
        return $deliveries;
    }
}
