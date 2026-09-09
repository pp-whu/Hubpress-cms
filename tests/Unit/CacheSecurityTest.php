<?php

declare(strict_types=1);

namespace HuberCMS\Tests\Unit;

use HuberCMS\Core\Cache;
use HuberCMS\Core\Config;
use PHPUnit\Framework\TestCase;

final class CacheSecurityTest extends TestCase
{
    public function testFileCacheRejectsSerializedObjectsAndReturnsDefault(): void
    {
        $base = dirname(__DIR__, 2);
        $tmpDir = sys_get_temp_dir() . '/hubercms_security_test_' . uniqid('', true);
        mkdir($tmpDir, 0777, true);

        $config = new Config($base);
        $config->set('cache.driver', 'file');
        $config->set('cache.prefix', 'test_');
        $config->set('cache.stores.file.path', $tmpDir);

        $cache = new Cache($config);

        $filePath = (new \ReflectionClass(Cache::class))
            ->getMethod('filePath')
            ->invoke($cache, 'malicious_key');

        file_put_contents(
            $filePath,
            serialize([
                'expires' => time() + 60,
                'data' => (object) ['danger' => 'value'],
            ]),
            LOCK_EX
        );

        $this->assertSame('fallback', $cache->get('malicious_key', 'fallback'));

        @unlink($filePath);
        @rmdir($tmpDir);
    }
}
