<?php

namespace Bitendian\TBP\Tests\Utils\Config;

use Bitendian\TBP\TBPException;
use Bitendian\TBP\Utils\Config;
use PHPUnit\Framework\TestCase;
use stdClass;

class ConfigTest extends TestCase
{
    /**
     * Test if config folder does not exists, config reader throws an exception.
     */
    public function testFolderNotFound()
    {
        try {
            $folder = __DIR__ . DIRECTORY_SEPARATOR . 'not-found' . DIRECTORY_SEPARATOR;
            $configReader = new Config($folder);
            $this->fail('expected config "folder not found exception" not triggered');
        } catch (TBPException $e) {
            $this->assertEquals(-1, $e->getCode());
        }
    }

    /**
     * Test if config file does not exists, config reader returns an empty object.
     */
    public function testConfigFileNotFound()
    {
        try {
            $folder = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
            $configReader = new Config($folder);

            $file = 'not-found';
            $config = $configReader->getConfig($file);
            $this->assertTrue(empty($config), "unexpected config file '$file' found");

        } catch (TBPException $e) {
            $this->fail("expected config folder '$folder' does not exist");
        }
    }

    /**
     * Test reading attributes and values.
     */
    public function testBase()
    {
        $folder = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
        $file = 'base';

        $config = $this->loadConfig($folder, $file);

        $this->assertTrue(isset($config->foo1), "expected foo1 attribute not found");
        $this->assertFalse(isset($config->foo2), "unexpected foo2 attribute found");
        $this->assertEquals('hello world', $config->foo1);
    }

    /**
     * Test trimming attributes and values.
     */
    public function testTrim()
    {
        $folder = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
        $file = 'trim';

        $config = $this->loadConfig($folder, $file);

        for ($i = 1; $i <= 4; $i++) {
            $this->assertTrue(isset($config->{'foo' . $i}), "expected foo$i attribute not found");
            $this->assertEquals("$i", $config->{'foo' . $i});
        }
    }

    /**
     * @param string $folder Absolute path
     * @param string $file Filename
     * @return stdClass
     */
    private function loadConfig($folder, $file)
    {
        $config = new stdClass();
        try {
            $configReader = new Config($folder);

            $config = $configReader->getConfig($file);
            $this->assertFalse(empty($config), "expected config file '$file' not found");

        } catch (TBPException $e) {
            $this->fail("expected config folder '$folder' does not exist");
        }
        return $config;
    }
}
