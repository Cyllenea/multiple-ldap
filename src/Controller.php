<?php

namespace cyllenea\ldap;

class Controller
{

    protected string $host;
    protected int $port = 389;
    protected string $dn;
    protected string $domain;

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }

    public function getDn(): string
    {
        return $this->dn;
    }

    public function setDn(string $dn)
    {
        $this->dn = $dn;
    }

    public function getDomain(): string
    {
        return sprintf($this->domain, '');
    }

    public function getUserDomain(string $username): string
    {
        return sprintf($this->domain, $username);
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

}
