<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo;

use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\PaymentConfigMapping;
use Sumedia\Wbo\Service\WboPayments;

class PaymentMatcher
{
    const WBO_PAYMENT_FALLBACK_ID = WboPayments::PAYMENT_PREPAYED;

    /** @var WboConfig */
    protected $wboConfig;

    /** @var array */
    protected $paymentConfigMappingConstants;

    public function __construct(WboConfig $wboConfig)
    {
        $this->wboConfig = $wboConfig;

        $reflection = new \ReflectionClass(PaymentConfigMapping::class);
        /** @var \ReflectionClassConstant $constant */
        $this->paymentConfigMappingConstants = $reflection->getConstants();
    }

    public function getPaymentIds(): array
    {
        $paymentIds = [];
        foreach ($this->paymentConfigMappingConstants as $constant) {
            $paymentId = $this->wboConfig->get($constant);
            if (!$paymentId) {
                continue;
            }
            $camelCaseToWord = preg_split('/(?=[A-Z])/', $constant);
            $camelCaseToWord = array_map(function($a) {
                return strtoupper($a);
            }, $camelCaseToWord);
            $identifier = str_replace('_MAPPING', '', implode('_', $camelCaseToWord));
            $paymentIds[$paymentId] = constant(WboPayments::class . '::' . $identifier);
        }
        return $paymentIds;
    }
}