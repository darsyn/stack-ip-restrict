<?php
namespace Darsyn\Stack\IpRestrict;

use Darsyn\IP\IP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * IP Checker
 *
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
abstract class IpChecker implements HttpKernelInterface
{
    /**
     * @access private
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $app;

    /**
     * @access private
     * @var array
     */
    private $ipAddresses;

    /**
     * @access private
     * @var string
     */
    private $message;
    /**
     * @access private
     * @var integer
     */
    private $code;

    /**
     * @access private
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $accessDeniedResponse;

    /**
     * Constructor
     *
     * @access public
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $app
     * @param array $ipAddresses
     * @param string $message
     * @param integer $code
     */
    public function __construct(
        HttpKernelInterface $app,
        array $ipAddresses,
        $message = 'Your IP address is not allowed.',
        $code = 403
    ) {
        $this->app = $app;
        $this->ipAddresses = $ipAddresses;
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * Handle Request
     *
     * @access public
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $type
     * @param boolean $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return $this->app->handle($request, $type, $catch);
    }

    /**
     * Get: Access Denied Response
     *
     * @access public
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAccessDeniedResponse()
    {
        if ($this->accessDeniedResponse instanceof Response) {
            return $this->accessDeniedResponse;
        }
        return new Response($this->message, $this->code);
    }

    /**
     * Set: Access Denied Response
     *
     * @access public
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    public function setAccessDeniedResponse(Response $response)
    {
        $this->accessDeniedResponse = $response;
    }

    /**
     * Does IP Address Match?
     *
     * @access protected
     * @param  \Darsyn\IP\IP $clientIp
     * @return boolean
     */
    protected function doesIpAddressMatch(IP $clientIp)
    {
        foreach ($this->ipAddresses as $ipAddress) {
            try {
                $cidr = 128;
                if (preg_match('#^(.+)/([1-9]\d{0,2})$#', $ipAddress, $matches)) {
                    $ipAddress = $matches[1];
                    $cidr = (int) $matches[2];
                }
                $ipAddress = new IP($ipAddress);
                if ($ipAddress->inRange(
                    $clientIp,
                    max($ipAddress->isVersion(IP::VERSION_4) ? $cidr + 96 : $cidr, 128)
                )) {
                    return true;
                }
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }
        return false;
    }
}
