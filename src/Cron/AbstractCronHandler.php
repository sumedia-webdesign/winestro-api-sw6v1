<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Sumedia\Wbo\Cron\v6_4\AbstractCronHandler as BaseAbstractCronHandler6_4;
use Sumedia\Wbo\Cron\v6_7\AbstractCronHandler as BaseAbstractCronHandler6_7;

$shopwareVersion = \Composer\InstalledVersions::getVersion('shopware/core');

if (version_compare($shopwareVersion, '6.7.0', '>=')) {
    abstract class AbstractCronHandler extends BaseAbstractCronHandler6_7 {}
} else {
    abstract class AbstractCronHandler extends BaseAbstractCronHandler6_4 {}
}