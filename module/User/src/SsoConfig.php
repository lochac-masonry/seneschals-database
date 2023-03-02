<?php

declare(strict_types=1);

namespace User;

use Laminas\Config\Config;

/**
 * Config object for Single Sign-On functionality of AuthController.
 */
class SsoConfig
{
    /** @var String */
    public $key;
    /** @var String */
    public $algorithm;
    /** @var String */
    public $issuer;
    /** @var String */
    public $audience;

    public function __construct(array $config)
    {
        if (!isset($config['sso']) || !is_array($config['sso'])) {
            throw new \InvalidArgumentException('Config section [sso] must be set');
        }
        $ssoConfig = $config['sso'];

        if (!isset($ssoConfig['key']) || !is_string($ssoConfig['key'])) {
            throw new \InvalidArgumentException('Config value [sso.key] must be set to a string');
        }
        $this->key = $ssoConfig['key'];

        if (!isset($ssoConfig['algorithm']) || !is_string($ssoConfig['algorithm'])) {
            throw new \InvalidArgumentException('Config value [sso.algorithm] must be set to a string');
        }
        $this->algorithm = $ssoConfig['algorithm'];

        if (!isset($ssoConfig['issuer']) || !is_string($ssoConfig['issuer'])) {
            throw new \InvalidArgumentException('Config value [sso.issuer] must be set to a string');
        }
        $this->issuer = $ssoConfig['issuer'];

        if (!isset($ssoConfig['audience']) || !is_string($ssoConfig['audience'])) {
            throw new \InvalidArgumentException('Config value [sso.audience] must be set to a string');
        }
        $this->audience = $ssoConfig['audience'];
    }
}
