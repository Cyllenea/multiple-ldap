<?php

namespace cyllenea\ldap;

use Nette;
use Nette\Schema\Expect;

class LDAPExtension extends Nette\DI\CompilerExtension
{

    public function getConfigSchema(): Nette\Schema\Schema
    {
        return Expect::structure([
            'controllers' => Expect::array(),
            'attributes' => Expect::array()->default([
                "employeeNumber",
                "employeeID",
                "mail",
                "cn",
            ]),
        ]);
    }

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('articles'))
            ->setFactory(LDAP::class, [
                $this->config->controllers,
                $this->config->attributes,
            ]);
    }

}
