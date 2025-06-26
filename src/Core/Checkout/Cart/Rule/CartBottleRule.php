<?php declare(strict_types=1);

namespace Sumedia\Wbo\Core\Checkout\Cart\Rule;

use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleComparison;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

class CartBottleRule extends Rule
{
    protected string $operator;
    public const RULE_NAME = 'cartBottle';

    protected int $bottles;

    public function __construct(
        string $operator = self::OPERATOR_EQ,
        ?int $bottles = null
    ) {
        parent::__construct();
        $this->operator = $operator;
        $this->bottles = (int) $bottles;
    }

    public function getName(): string
    {
        return self::RULE_NAME;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        return RuleComparison::numeric($this->calculateCartBottles($scope->getCart()), $this->bottles, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'bottles' => RuleConstraints::int(),
            'operator' => RuleConstraints::numericOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER)
            ->numberField('bottles', ['unit' => 'bottles']);
    }

    private function calculateCartBottles(Cart $cart): float
    {
        $bottles = 0;

        /** @var OrderLineItemEntity $lineItem */
        foreach ($cart->getLineItems()->filterGoodsFlat() as $lineItem) {
            $bottles += (int) $lineItem->getExtension('wbo_article')['bottles'];
        }

        return $bottles;
    }
}
