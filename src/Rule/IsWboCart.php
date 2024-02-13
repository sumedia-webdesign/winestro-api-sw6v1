<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Rule;

use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Shopware\Core\Framework\Struct\Struct;
use Symfony\Component\Validator\Constraints\Type;

class IsWboCart extends Rule
{
    /** @var bool */
    protected $isWboCart;

    /** @var bool */
    protected $freeShippingOnlyWboCart;

    public function __construct(bool $freeShippingOnlyWboCart = false)
    {
        $this->freeShippingOnlyWboCart = $freeShippingOnlyWboCart;
        $this->isWboCart = false;
        parent::__construct();
    }

    public function getName(): string
    {
        return 'isWboCart';
    }

    public function match(RuleScope $scope): bool
    {
        if (!($scope instanceof CartRuleScope)) {
            return false;
        }

        if (!$this->freeShippingOnlyWboCart) {
            return true;
        }

        $lineItems = $scope->getCart()->getLineItems();
        /** @var \Shopware\Core\Checkout\Cart\LineItem\LineItem $lineItem */
        foreach ($lineItems as $lineItem) {
            /** @var Struct $isWboArticle */
            $isWboArticle = $lineItem->getExtension('is_wbo_article');
            if (!isset($isWboArticle->getVars()['is_wbo_article'])) {
                return false;
            }
        }

        return true;
    }

    public function getConstraints(): array
    {
        return [
            'isWboCart' => [ new Type('bool') ]
        ];
    }
}
