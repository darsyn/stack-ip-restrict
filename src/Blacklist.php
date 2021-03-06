<?php

namespace Darsyn\Stack\IpRestrict;

use Darsyn\IP\IP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Blacklist
 *
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
final class Blacklist extends IpChecker
{
    /**
     * Handle Request
     *
     * @access public
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  integer $type
     * @param  boolean $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $clientIp = new IP($request->getClientIp());
        if ($this->doesIpAddressMatch($clientIp)) {
            return $this->getAccessDeniedResponse();
        }
        return parent::handle($request, $type, $catch);
    }
}
