<?php declare(strict_types=1);

namespace Sumedia\Wbo\Tests\Config;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Sumedia\Wbo\Config\WboConfig;

class ConfigTest extends TestCase
{
    use KernelTestBehaviour;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testConfigClass(): void
    {
        global $bootstrap;
        /** @var WboConfig $config */
        $config = $bootstrap->getWboConfig($this->getKernel());
        $systemConfigService = $this->getKernel()->getContainer()->get('Shopware\Core\System\SystemConfig\SystemConfigService');

        $configDomain = WboConfig::CONFIG_DOMAIN;
        $reflection = new \ReflectionClass($config);
        foreach ($reflection->getConstants() as $name => $key) {
            if ($name == 'CONFIG_DOMAIN') {
                continue;
            }
            $value = $systemConfigService->get($configDomain . '.config.' . $key);
            $this->assertEquals($value, $config->get($key));
        }
    }
}
