<?php
declare(strict_types=1);

namespace Kod\BootstrapSlim\Tests;

use Kod\BootstrapSlim\Exceptions\ContainerException;
use Kod\BootstrapSlim\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use Kod\BootstrapSlim\Container;
use Error;

class ContainerTest extends TestCase
{
    /**
     * @testdox Should throw ContainerException
     */
    public function testContainerException()
    {
        $msg = 'Bad thing';
        $code = 100;
        $container = new Container([
            'error' => function() use($msg, $code){
                throw new Error($msg, $code);
            }
        ]);

        try{
            $container->get('error');
            $this->fail('ContainerException must be thrown');
        } catch(ContainerException $exception) {
            $this->assertEquals($msg, $exception->getMessage());
            $this->assertEquals($code, $exception->getCode());
        }
    }

    /**
     * @testdox Should throw NotFoundException
     */
    public function testNotFoundException()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->get('xxx');
    }
    /**
     * @testdox Should return TRUE if key exists in container
     */
    public function testKeyExists()
    {
        $container = new Container([
            'key' => 'value'
        ]);
        $this->assertTrue($container->has('key'));
    }
    /**
     * @testdox Should return FALSE if key does not exist in container
     */
    public function testKeyDoesNotExist()
    {
        $container = new Container();
        $this->assertFalse($container->has('key'));
    }

    /**
     * @testdox Test __get() method
     */
    public function testGetter()
    {
        $container = new Container([
            'key' => 'value'
        ]);
        $this->assertEquals('value', $container->key);
    }

    /**
     * @testdox Test __isset() method
     */
    public function testIsset()
    {
        $container = new Container([
            'key' => 'value'
        ]);
        $this->assertFalse(isset($container->xxx));
        $this->assertTrue(isset($container->key));
    }
}
