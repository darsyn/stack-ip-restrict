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
            #array(''),
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
            #array(''),
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
     * @test
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
