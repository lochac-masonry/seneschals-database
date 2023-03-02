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

    public function __construct(Config $config)
    {
        if (!isset($config->key) || !is_string($config->key)) {
            throw new \InvalidArgumentException('Config value [sso.key] must be set to a string');
        }
        $this->key = $config->key;

        if (!isset($config->algorithm) || !is_string($config->algorithm)) {
            throw new \InvalidArgumentException('Config value [sso.algorithm] must be set to a string');
        }
        $this->algorithm = $config->algorithm;

        if (!isset($config->issuer) || !is_string($config->issuer)) {
            throw new \InvalidArgumentException('Config value [sso.issuer] must be set to a string');
        }
        $this->issuer = $config->issuer;

        if (!isset($config->audience) || !is_string($config->audience)) {
            throw new \InvalidArgumentException('Config value [sso.audience] must be set to a string');
        }
        $this->audience = $config->audience;
    }
}
