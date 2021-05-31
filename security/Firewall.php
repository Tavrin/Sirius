<?php


namespace Sirius\security;


use Sirius\http\Request;
use Sirius\http\Response;
use Sirius\utils\JsonParser;
use Sirius\utils\StringUtils;

class Firewall
{
    public const SECURITY_CONFIG = ROOT_DIR . '/config/security.json';
    private array $firewalls;
    private Security $security;
    public function __construct()
    {
        $this->firewalls = JsonParser::parseFile(self::SECURITY_CONFIG);
        $this->security = new Security();
    }

    public function checkFirewalls(Request $request)
    {
       $pathinfo = $request->getPathInfo();
       foreach ($this->firewalls['firewalls'] as $firewall)
       {
           [$pathinfo, $firewallPattern] = StringUtils::normalizeForComparison($pathinfo, $firewall['pattern']);
           if (false === strpos($pathinfo, $firewallPattern)) {
                continue;
           }

           foreach ($firewall['roles'] as $firewallRole) {
               if ( $this->security->hasRole($firewallRole)) {
                   return;
               }
           }

           if (isset($firewall['redirect'])) {
               header("Location:" . $firewall['redirect']);
               exit();
           }
           
           header("Location:/");
           exit();
       }
    }
}