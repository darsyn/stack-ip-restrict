<?php
namespace Darsyn\Stack\IpRestrict\Tests;

use Darsyn\Stack\IpRestrict\Whitelist;
use Mockery as m;

class WhitelistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @access private
     * @var HttpKernelInterface
     */
    private $kernel;

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
        $this->kernel = m::mock(
            'Symfony\Component\HttpKernel\HttpKernelInterface',
            function ($mock) use ($successfulResponse) {
                $mock->shouldReceive('handle')->once()->andReturn($successfulResponse);
            }
        );

    }

    /**
     * Data Provider: Matching IP Addresses
     *
     * @access public
     * @return array
     */
    public function getMatchingIpAddresses()
    {
        return array(
            array('192.168.33.10', array(
                '192.168.0.1/30',
                '253.236.138.172',
                '192.168.10.99/12',
            )),
            array('10.0.0.1', array(
                '255.255.255.255/17',
                '128.0.0.1/9',
                '10.0.0.1',
            )),
            array('d7c8:9855:70ac:756e::dc9:6856', array(
                'd789:c67f:6a5c:3b5a:ca74:8f3b:50b2:fadd/45',
                '34f6:ed98:10be:d265:17ff:f062:fd70:a976',
                'd7c8:9855:70ac:756e:2691:9cbb:dc9:6856/82',
            )),
            array('43f7:5937:c939:30ef:d710:185a:2c8d:c055', array(
                '0197:9d87:df58:866e:1d3c:bc5d:29dd:110e/12',
                '1b2f:f066:53d9:f87c:6552:d959:ee19:f184',
                '8f49:1034:e156:6d:924b:8485::/101',
            )),
        );
    }

    /**
     * Data Provider: Non-matching IP Addresses
     *
     * @access public
     * @return array
     */
    public function getNonMatchingIpAddresses()
    {
        return array(
            array('192.168.33.10', array(
                '192.168.0.1/30',
                '253.236.138.172',
                '192.168.10.99/31',
            )),
            array('10.0.0.1', array(
                '255.255.255.255/17',
                '128.0.0.1/9',
                '10.0.0.2',
            )),
            array('d7c8:9855:70ac:756e::dc9:6856', array(
                'd789:c67f:6a5c:3b5a:ca74:8f3b:50b2:fadd/45',
                '34f6:ed98:10be:d265:17ff:f062:fd70:a976',
                'd7c8:9855:70ac:756e:2691:9cbb:dc9:6856/82',
            )),
            array('43f7:5937:c939:30ef:d710:185a:2c8d:c055', array(
                '0197:9d87:df58:866e:1d3c:bc5d:29dd:110e/12',
                '1b2f:f066:53d9:f87c:6552:d959:ee19:f184',
                '8f49:1034:e156:6d:924b:8485::/101',
            )),
        );
    }

    /**
     * Test: It Returns 200 for Listed IP Addresses
     *
     * @test
     * @dataProvider getMatchingIpAddresses
     * @access public
     * @param string $clientIp
     * @param array $ipAddresses
     * @return void
     */
    public function itReturns200ForListedIpAddresses($clientIp, array $ipAddresses)
    {
        $stackedApp = new Whitelist($this->kernel, $ipAddresses);
        $request = m::mock('Symfony\Component\HttpFoundation\Request', function ($mock) use ($clientIp) {
            $mock->shouldReceive('getClientIp')->once()->andReturn($clientIp);
        });
        $response = $stackedApp->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test: It Returns 403 for Non-listed IP Addresses
     *
     * @test
     * @dataProvider getNonMatchingIpAddresses
     * @access public
     * @param string $clientIp
     * @param array $ipAddresses
     * @return void
     */
    public function itReturns403ForNonListedIpAddresses($clientIp, array $ipAddresses)
    {
        $stackedApp = new Whitelist($this->kernel, $ipAddresses);
        $request = m::mock('Symfony\Component\HttpFoundation\Request', function ($mock) use ($clientIp) {
            $mock->shouldReceive('getClientIp')->once()->andReturn($clientIp);
        });
        $response = $stackedApp->handle($request);
        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * Test: It Returns 200 When Invalid IP Addresses Are Listed
     *
     * @test
     * @access public
     * @return void
     */
    public function itReturns403WhenInvalidIpAddressesAreListed()
    {
        $stackedApp = new Whitelist($this->kernel, array('list', 'of', 'invalid', 'ip', 'addresses'));
        $request = m::mock('Symfony\Component\HttpFoundation\Request', function ($mock) {
            $mock->shouldReceive('getClientIp')->once()->andReturn('12.34.56.78');
        });
        $response = $stackedApp->handle($request);
        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * Test: It Returns A Custom Response When Set and IP Addresses Do Not Match
     *
     * @test
     * @access public
     * @return void
     */
    public function itReturnsACustomResponseWhenSetAndIpAddressesDoNotMatch()
    {
        $statusCode = 402;
        $customResponse = m::mock('Symfony\Component\HttpFoundation\Response', function ($mock) use ($statusCode) {
            $mock->shouldReceive('getStatusCode')->once()->andReturn($statusCode);
        });

        $stackedApp = new Whitelist($this->kernel, array('12.34.56.78'));
        $stackedApp->setAccessDeniedResponse($customResponse);
        $request = m::mock('Symfony\Component\HttpFoundation\Request', function ($mock) {
            $mock->shouldReceive('getClientIp')->once()->andReturn('87.65.43.21');
        });
        $response = $stackedApp->handle($request);
        $this->assertSame($statusCode, $response->getStatusCode());
    }
}
