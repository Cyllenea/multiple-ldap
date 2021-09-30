<?php

namespace cyllenea\ldap;

use cyllenea\ldap\Exception\LDAPErrorException;

use function ldap_bind;
use function ldap_unbind;
use function ldap_set_option;
use function ldap_connect;
use function ldap_search;
use function ldap_get_entries;
use function ldap_get_attributes;
use function ldap_first_entry;

class LDAP
{

    protected array $controllers = [];
    protected string $filter = "(sAMAccountName=%s)";
    protected array $attributes = [];

    /** LDAP */
    protected $ldap;

    protected ?Controller $loggedIn = null;

    public function __construct($controllers = [], $attributes = [])
    {
        // Create controllers
        foreach ($controllers as $_) {
            $controller = new Controller();
            $controller->setHost($_["host"]);
            $controller->setPort($_["port"]);
            $controller->setDomain($_["domain"]);
            $controller->setDn($_["dn"]);
            $this->addController($controller);
        }

        $this->setAttributes($attributes);

    }

    /**
     * @param $username
     * @param $password
     * @return bool
     * @throws LDAPErrorException
     */
    public function login(string $username, string $password)
    {
        $i = 1;
        
        /** @var Controller $controller */
        foreach ($this->controllers as $controller) {

            if ($this->ldap = @ldap_connect($controller->getHost(), $controller->getPort())) {
                // Configure ldap params
                ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);

                // Try to bind like user
                if ($bind = @ldap_bind($this->ldap, $controller->getUserDomain($username), $password)) {
                    $this->loggedIn = $controller;
                    break;
                } else {
                    $this->disconnect();
                }
            }
            
            // Wait between requests
            if ($i !== count($this->controllers)) {
                usleep(250000);
            }
            $i++;
            
        }

        if (!$this->loggedIn) {
            throw new LDAPErrorException("Unable to connect to any server!");
        }

        return true;
    }

    public function isLoggedIn(): bool
    {
        return $this->loggedIn !== null;
    }


    public function getLoggedInController(): ?Controller
    {
        return $this->loggedIn ?? null;
    }

    /**
     * @param $username
     * @return array|null
     * @throws LDAPErrorException
     */
    public function search(string $username): ?array
    {
        if (!$this->isLoggedIn()) {
            throw new LDAPErrorException("Not connected to any server!");
        }

        $filter = sprintf($this->filter, $username);
        if ($result = ldap_search($this->ldap, $this->loggedIn->getDn(), $filter, $this->attributes)) {

            // Get entries
            $entries = ldap_get_entries($this->ldap, $result);

            // Check number of entries
            if ($entries["count"] > 1) {
                throw new LDAPErrorException("Founded more than one record");
            }

            if ($entries["count"] === 0) {
                return null;
            }

            // Send result
            return ldap_get_attributes($this->ldap, ldap_first_entry($this->ldap, $result));

        } else {
            throw new LDAPErrorException("Unable to search LDAP server");
        }
    }

    public function parseAttributes(array $attributes = []): array
    {
        $output = [];

        foreach ($attributes as $key => $attribute) {
            if (in_array($key, $this->attributes)) {
                if ($key === "memberOf") {
                    // Remove number of groups from attributes
                    unset($attribute["count"]);
                    $output[$key] = $attribute;
                } elseif (is_array($attribute)
                    && array_key_exists("count", $attribute)
                    && $attribute["count"] !== 0
                    && array_key_exists(0, $attribute)) {
                    $output[$key] = $attribute[0];
                }
            }
        }

        return $output;
    }

    public function disconnect(): void
    {
        if($this->ldap) {
            @ldap_unbind($this->ldap);
            $this->ldap = null;
        }
        $this->loggedIn = null;
    }

    public function addController(Controller $controller): int
    {
        return array_push($this->controllers, $controller);
    }

    public function getControllers(): array
    {
        return $this->controllers;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

}
