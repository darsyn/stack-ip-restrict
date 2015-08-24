<?php
namespace Darsyn\Stack\IpRestrict;

use Darsyn\IP\IP;
use Symfony\Component\HttpFoundation\Request;

/**
 * Blacklist
 *
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
final class Whitelist extends IpChecker
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
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $clientIp = new IP($request->getClientIp());
        if (!$this->doesIpAddressMatch($clientIp)) {
            return $this->getAccessDeniedResponse();
        }
        return parent::handle($request, $type, $catch);
    }
}
