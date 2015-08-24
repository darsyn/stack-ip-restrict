<?php
namespace Darsyn\Stack\IpRestrict\Tests;

use Darsyn\Stack\IpRestrict\Blacklist;
use Mockery as m;

class BlacklistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @access private
     * @var HttpKernelInterface
     */
    private $stackedApp;

    /**
     * Test Setup
     *
     * @access public
     * @return void
     */
    public function setUp()
    {
        $successfulResponse = m::mock('Symfony\Component\HttpFoundation\Response', function ($mock) {
            $mock->shouldReceive('getStatusCode')->once()->andReturn(200);
        });
        $kernel = m::mock('Symfony\Component\HttpKernel\HttpKernelInterface', function ($mock) use ($successfulResponse) {
            $mock->shouldReceive('handle')->once()->andReturn($successfulResponse);
        });
        $this->stackedApp = new Blacklist($kernel, $this->getListedIpAddresses());

    }

    /**
     * Data Provider: Listed IP Addresses
     *
     * @access public
     * @return array
     */
    public function getListedIpAddresses()
    {
        return array(
            // IPv4
            '192.168.0.1/16',
            '153.254.102.145',
            '162.158.82.85/10',
            '253.236.138.172/23',
            // IPv6
            'c35b:64f6:5005:426e:7a18:e743:9f6d:df49/100',
            '7658:924f:200b:024c:39d4:f70f:c8f4:afe9',
            '10de:d82a:b44b:4c83:99b1:234d:9f1a:4986/45',
            '9869:cd0f:6ba3:78cf:4d72:41ee:d6d5:39c4/73',
        );
    }

    /**
     * Data Provider: Client IP Addresses
     *
     * @access public
     * @return array
     */
    public function getClientIpAddresses()
    {
        return array(
            // IPv4
            array('62.232.95.18'),
            array('245.240.202.151'),
            array('118.192.17.224'),
            array('153.254.102.145'),
            array('5.242.38.165'),
            array('247.132.107.43'),
            array('147.248.222.75'),
            array('210.66.65.106'),
            array('56.148.47.46'),
            array('134.249.197.251'),
            array('186.213.220.85'),
            array('213.68.229.217'),
            array('56.12.127.48'),
            // IPv6
            array('e68f:b206:e432:5c43:0a28:ac2e:7720:7af9'),
            array('8861:8825:3835:d1ff:e327:4875:0fef:26c2'),
            array('d789:c67f:6a5c:3b5a:ca74:8f3b:50b2:fadd'),
            array('34f6:ed98:10be:d265:17ff:f062:fd70:a976'),
            array('d7c8:9855:70ac:756e:2691:9cbb:0dc9:6856'),
            array('43f7:5937:c939:30ef:d710:185a:2c8d:c055'),
            array('3cf8:a9a4:99bb:2688:ec8d:fde9:25bb:70f2'),
            array('0197:9d87:df58:866e:1d3c:bc5d:29dd:110e'),
            array('1b2f:f066:53d9:f87c:6552:d959:ee19:f184'),
            array('8f49:1034:e156:1b6d:924b:8485:0798:b7c8'),
            array('e327:4875:0fef:26c2:ec8d:fde9:25bb:70f2'),
            array('e68f:b206:e432:5c43:d710:185a:2c8d:c055'),
        );
    }

    /**
     * Test: It Returns 403 for Listed IP Addresses
     *
     * @test
     * @dataProvider getClientIpAddresses
     * @access public
     * @param string $clientIp
     * @return void
     */
    public function itReturns403ForListedIpAddresses($clientIp)
    {
        $request = m::mock('Symfony\Component\HttpFoundation\Request', function ($mock) use ($clientIp) {
            $mock->shouldReceive('getClientIp')->once()->andReturn($clientIp);
        });
        $response = $this->stackedApp->handle($request);
        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * Test: It Returns 403 for Non-listed IP Addresses
     *
     * @dataProvider getClientIpAddresses
     * @access public
     * @param string $clientIp
     * @return void
     */
    public function itReturns200ForNonListedIpAddresses($clientIp)
    {
        $request = m::mock('Symfony\Component\HttpFoundation\Request', function ($mock) use ($clientIp) {
            $mock->shouldReceive('getClientIp')->once()->andReturn($clientIp);
        });
        $response = $this->stackedApp->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }
}
