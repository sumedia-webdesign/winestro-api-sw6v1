<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service;

class WboPayments
{
    const PAYMENT_INVOICE               = 1;
    const PAYMENT_CASHPREPAYED          = 2;
    const PAYMENT_DEBIT                 = 3;
    const PAYMENT_PAYPAL                = 4;
    const PAYMENT_CASH                  = 5;
    const PAYMENT_PREPAYED              = 6;
    const PAYMENT_EC                    = 7;
    const PAYMENT_SOFORT                = 8;
    const PAYMENT_CREDITCARD            = 9;
    const PAYMENT_AMAZON_PAY            = 11;
    const PAYMENT_PAYPAL_INVOICE        = 12;
    CONST PAYMENT_GIROPAY               = 13;
    const PAYMENT_EPS                   = 14;
    const PAYMENT_IDEAL                 = 15;
    const PAYMENT_PRZELEWY24            = 16;
    const PAYMENT_ALLPAY                = 17;
    const PAYMENT_MULTIBANCO            = 18;
    const PAYMENT_GOOGLE_PAY            = 19;
    const PAYMENT_APPLE_PAY             = 20;
    const PAYMENT_CASH_ON_DELIVERY      = 21;
    const PAYMENT_BANCONTACT            = 22;
    const PAYMENT_PREPAYED_PAYED        = 23;
    const PAYMENT_SHOPIFY_PAYMENTS      = 24;
    const PAYMENT_WINEESTRAO_PREPAYED   = 25;
    const PAYMENT_ELOPAGE               = 26;
}
