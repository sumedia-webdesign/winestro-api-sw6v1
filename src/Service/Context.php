<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service;

use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\ContextSource;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;

class Context extends \Shopware\Core\Framework\Context
{
    public function __construct(
        ContextSource $source = null,
        array $ruleIds = [],
        string $currencyId = Defaults::CURRENCY,
        array $languageIdChain = [Defaults::LANGUAGE_SYSTEM],
        string $versionId = Defaults::LIVE_VERSION,
        float $currencyFactor = 1.0,
        bool $considerInheritance = false,
        string $taxState = CartPrice::TAX_STATE_GROSS,
        ?CashRoundingConfig $rounding = null
    ) {
        $source = $source ?: new SystemSource();
        parent::__construct($source);
    }
}
