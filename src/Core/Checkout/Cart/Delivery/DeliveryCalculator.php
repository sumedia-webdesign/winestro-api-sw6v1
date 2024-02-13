<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\Wbo\Core\Checkout\Cart\Delivery;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryCalculator as DeliveryCalculatorSW;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor;
use Shopware\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryPosition;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use Shopware\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use Shopware\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceEntity;
use Shopware\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Shopware\Core\Checkout\Shipping\Exception\ShippingMethodNotFoundException;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Util\FloatComparator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Sumedia\Wbo\Entity\WboArticlesEntity;

#[Package('checkout')]
class DeliveryCalculator extends DeliveryCalculatorSW
{
    public const CALCULATION_BY_BOTTLE = 5;

    private DeliveryCalculatorSW $deliveryCalculator;
    private QuantityPriceCalculator $priceCalculator;
    private PercentageTaxRuleBuilder $percentageTaxRuleBuilder;
    private EntityRepository $wboArticlesRepository;

    /**
     * @internal
     */
    public function __construct(
        DeliveryCalculatorSW $deliveryCalculator,
        QuantityPriceCalculator $priceCalculator,
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder,
        EntityRepository $wboArticlesRepository
    ) {
        $this->deliveryCalculator = $deliveryCalculator;
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
        $this->wboArticlesRepository = $wboArticlesRepository;
    }

    public function calculate(CartDataCollection $data, Cart $cart, DeliveryCollection $deliveries, SalesChannelContext $context): void
    {
        foreach ($deliveries as $delivery) {
            $this->calculateDelivery($data, $cart, $delivery, $context);
        }
    }

    private function calculateDelivery(CartDataCollection $data, Cart $cart, Delivery $delivery, SalesChannelContext $context): void
    {
        $costs = null;

        if ($delivery->getShippingCosts()->getUnitPrice() > 0 || $cart->hasExtension(DeliveryProcessor::MANUAL_SHIPPING_COSTS)) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                new PriceCollection([
                    new Price(
                        Defaults::CURRENCY,
                        $delivery->getShippingCosts()->getTotalPrice(),
                        $delivery->getShippingCosts()->getTotalPrice(),
                        false
                    ),
                ]),
                $delivery->getPositions()->getLineItems(),
                $context
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        if (
            $this->hasDeliveryPriceRecalculationSkipWithZeroUnitPrice($cart->getBehavior(), $delivery->getShippingCosts()->getUnitPrice())
            || $this->hasDeliveryWithOnlyShippingFreeItems($delivery)
        ) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                new PriceCollection([new Price(Defaults::CURRENCY, 0, 0, false)]),
                $delivery->getPositions()->getLineItems(),
                $context
            );
            $delivery->setShippingCosts($costs);

            return;
        }

        $key = DeliveryProcessor::buildKey($delivery->getShippingMethod()->getId());

        if (!$data->has($key)) {
            throw new ShippingMethodNotFoundException($delivery->getShippingMethod()->getId());
        }

        /** @var ShippingMethodEntity $shippingMethod */
        $shippingMethod = $data->get($key);

        foreach ($context->getRuleIds() as $ruleId) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', $ruleId);

            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
            if ($costs !== null) {
                break;
            }
        }

        // Fetch default price if no rule matched
        if ($costs === null) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', null);
            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
        }

        if (!$costs) {
            $cart->addErrors(
                new ShippingMethodBlockedError((string) $shippingMethod->getTranslation('name'))
            );

            return;
        }

        $delivery->setShippingCosts($costs);
    }

    private function hasDeliveryWithOnlyShippingFreeItems(Delivery $delivery): bool
    {
        foreach ($delivery->getPositions()->getLineItems()->getIterator() as $lineItem) {
            if ($lineItem->getDeliveryInformation() && !$lineItem->getDeliveryInformation()->getFreeDelivery()) {
                return false;
            }
        }

        return true;
    }

    private function matches(Delivery $delivery, ShippingMethodPriceEntity $shippingMethodPrice, SalesChannelContext $context): bool
    {
        if ($shippingMethodPrice->getCalculationRuleId()) {
            return \in_array($shippingMethodPrice->getCalculationRuleId(), $context->getRuleIds(), true);
        }

        $start = $shippingMethodPrice->getQuantityStart();
        $end = $shippingMethodPrice->getQuantityEnd();

        switch ($shippingMethodPrice->getCalculation()) {
            case self::CALCULATION_BY_PRICE: $value = $delivery->getPositions()->getWithoutDeliveryFree()->getPrices()->sum()->getTotalPrice(); break;
            case self::CALCULATION_BY_LINE_ITEM_COUNT: $value = $delivery->getPositions()->getWithoutDeliveryFree()->getQuantity(); break;
            case self::CALCULATION_BY_WEIGHT: $value = $delivery->getPositions()->getWithoutDeliveryFree()->getWeight(); break;
            case self::CALCULATION_BY_VOLUME: $value = $delivery->getPositions()->getWithoutDeliveryFree()->getVolume(); break;
            default: $value = $delivery->getPositions()->getWithoutDeliveryFree()->getLineItems()->getPrices()->sum()->getTotalPrice() / 100;
        }

        if (self::CALCULATION_BY_BOTTLE == $shippingMethodPrice->getCalculation()) {
            $bottles = 0;
            /** @var DeliveryPosition $deliveryPosition */
            foreach ($delivery->getPositions()->getWithoutDeliveryFree()->getIterator() as $deliveryPosition) {
                $productNumber = $deliveryPosition->getLineItem()->getPayloadValue('productNumber');
                /** @var WboArticlesEntity $wboArticle */
                $wboArticle = $this->wboArticlesRepository->search((new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber)), $context->getContext())->first();
                $bottles += ((int) $wboArticle->getBottles()) * $deliveryPosition->getQuantity();
            }
            $value = $bottles;
        }

        // $end (optional) exclusive
        return (!$start || FloatComparator::greaterThanOrEquals($value, $start)) && (!$end || FloatComparator::lessThanOrEquals($value, $end));
    }

    private function calculateShippingCosts(ShippingMethodEntity $shippingMethod, PriceCollection $priceCollection, LineItemCollection $calculatedLineItems, SalesChannelContext $context): CalculatedPrice
    {
        switch ($shippingMethod->getTaxType()) {
            case ShippingMethodEntity::TAX_TYPE_HIGHEST:
                $rules = $calculatedLineItems->getPrices()->getHighestTaxRule();

                break;

            case ShippingMethodEntity::TAX_TYPE_FIXED:
                $tax = $shippingMethod->getTax();

                if ($tax !== null) {
                    $rules = $context->buildTaxRules($tax->getId());

                    break;
                }

            // no break
            default:
                $rules = $this->percentageTaxRuleBuilder->buildRules(
                    $calculatedLineItems->getPrices()->sum()
                );
        }

        $price = $this->getCurrencyPrice($priceCollection, $context);

        $definition = new QuantityPriceDefinition($price, $rules, 1);

        return $this->priceCalculator->calculate($definition, $context);
    }

    private function getCurrencyPrice(PriceCollection $priceCollection, SalesChannelContext $context): float
    {
        /** @var Price $price */
        $price = $priceCollection->getCurrencyPrice($context->getCurrency()->getId());

        $value = $this->getPriceForTaxState($price, $context);

        if ($price->getCurrencyId() === Defaults::CURRENCY) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }

    private function getMatchingPriceOfRule(Delivery $delivery, SalesChannelContext $context, ShippingMethodPriceCollection $shippingPrices): ?CalculatedPrice
    {
        $shippingPrices->sort(
            function (ShippingMethodPriceEntity $priceEntityA, ShippingMethodPriceEntity $priceEntityB) use ($context) {
                /** @var PriceCollection $priceCollectionA */
                $priceCollectionA = $priceEntityA->getCurrencyPrice();
                $priceA = $this->getCurrencyPrice($priceCollectionA, $context);

                /** @var PriceCollection $priceCollectionB */
                $priceCollectionB = $priceEntityB->getCurrencyPrice();
                $priceB = $this->getCurrencyPrice($priceCollectionB, $context);

                return $priceA <=> $priceB;
            }
        );

        $costs = null;
        foreach ($shippingPrices as $shippingPrice) {
            if (!$this->matches($delivery, $shippingPrice, $context)) {
                continue;
            }
            $price = $shippingPrice->getCurrencyPrice();
            if (!$price) {
                continue;
            }
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                $price,
                $delivery->getPositions()->getLineItems(),
                $context
            );

            break;
        }

        return $costs;
    }

    private function hasDeliveryPriceRecalculationSkipWithZeroUnitPrice(?CartBehavior $behavior, float $unitPrice): bool
    {
        return $behavior
            && $behavior->hasPermission(DeliveryProcessor::SKIP_DELIVERY_PRICE_RECALCULATION)
            && $unitPrice === 0.0;
    }
}