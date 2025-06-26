<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service;

class PaymentConfigMapping
{
    const PAYMENT_MAPPING_CASH                  = 'paymentMappingCash';
    const PAYMENT_MAPPING_PREPAYED              = 'paymentMappingPrepayed';
    const PAYMENT_MAPPING_PREPAYED_PAYED        = 'paymentMappingPrepayedPayed';
    const PAYMENT_MAPPING_CASHPREPAYED          = 'paymentMappingCashprepayed';
    const PAYMENT_MAPPING_CASHONDELIVERY        = 'paymentMappingCashOnDelivery';
    const PAYMENT_MAPPING_DEBIT                 = 'paymentMappingDebit';
    const PAYMENT_MAPPING_CREDITCARD            = 'paymentMappingCreditcard';
    const PAYMENT_MAPPING_INVOICE               = 'paymentMappingInvoice';
    const PAYMENT_MAPPING_PAYPAL                = 'paymentMappingPaypal';
    const PAYMENT_MAPPING_EC                    = 'paymentMappingEc';
    const PAYMENT_MAPPING_GIROPAY               = 'paymentMappingGiropay';
    const PAYMENT_MAPPING_SOFORT                = 'paymentMappingSofort';
    const PAYMENT_MAPPING_AMAZON_PAY            = 'paymentMappingAmazonPay';
    const PAYMENT_MAPPING_GOOGLE_PAY            = 'paymentMappingGooglePay';
    const PAYMENT_MAPPING_APPLE_PAY             = 'paymentMappingApplePay';
    const PAYMENT_MAPPING_SHOPIFY_PAYMENTS      = 'paymentMappingShopifyPayments';
    const PAYMENT_MAPPING_WINEESTRO_PREPAYED    = 'paymentMappingWineestroPrepayed';
    const PAYMENT_MAPPING_EPS                   = 'paymentMappingEps';
    const PAYMENT_MAPPING_IDEAL                 = 'paymentMappingIdeal';
    const PAYMENT_MAPPING_PRZELEWY24            = 'paymentMappingPrzelewy24';
    const PAYMENT_MAPPING_ALLPAY                = 'paymentMappingAllpay';
    const PAYMENT_MAPPING_MULTIBANCO            = 'paymentMappingMultibanco';
    const PAYMENT_MAPPING_BANCONTACT            = 'paymentMappingBancontact';
    const PAYMENT_MAPPING_ELOPAGE               = 'paymentMappingElopage';
}