Multiple LDAP
=============

Tato jednoduchá knihovna slouží pro autoarizaci na více LDAP/AD serverech.

Pokud přihlášení na jednom serveru nebylo úspěšné, pokusí se přihlásit na druhém serveru.

Běžné použití je v globální aplikaci ve firemním prostředí, kde existuje několik různých lokalit s vlastním AD serverem.



**Registrace knihovny**
```
extensions:
    ldap: cyllenea\multiple-ldap\LDAPExtension
```
  
**Nastavení atributů, které mají být získány z Active Directory záznamu**
```
ldap:
    attributes:
        - employeeNumber    # Employee ID
        - employeeID        # Cost center
        - mail              # Email address
        - cn                # Common name
        - sn                # Surname
        - givenName         # First name
```
  
**Nastavení ověřovacích serverů**
```
ldap:
    controllers:
        wnc:
            host: wnc.local
            port: 389
            domain: "%s@wnc.local"
            dn: "OU=COMPANY,DC=wnc,DC=local"

        wv:
            host: wvdc01.wv.local
            port: 389
            domain: "%s@wv.local"
            dn: "OU=COMPANY,DC=wv,DC=local"
```
  
**Registrace vlastní autorizační služby**
```
services:
    authenticator:
        class: cyllenea\multiple-ldap\Authenticator
        setup:
            - setIdentityGenerator([@userManagemenent, 'createIdentity'])
```
  
**Ukázka implementace vlastní autorizační služby**
```
<?php declare(strict_types = 1);

namespace App\Model\Security\Authenticator;

use cyllenea\ldap\Exception\LDAPErrorException;
use cyllenea\ldap\LDAP;
use Nette;

final class UserAuthenticator implements Nette\Security\IAuthenticator
{

    private LDAP $ldap;

    public function __construct(LDAP $ldap)
    {
        $this->ldap = $ldap;
    }

    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$username, $password] = $credentials;

        $user = null;

        $attributes = [];

        try {

            // Login to LDAP
            $this->ldap->login($username, $password);

            // Search user
            $obtainedAttributes = $this->ldap->search($username);

            // Get attributes
            $attributes = Nette\Utils\ArrayHash::from($this->ldap->parseAttributes($obtainedAttributes));

        } catch (LDAPErrorException | \Exception $e) {

            throw new Nette\Security\AuthenticationException('Authentication failed. Please check your username/password.');

        } finally {

            // Disconnect
            $this->ldap->disconnect();

        }

        return new Nette\Security\Identity($username, [], $attributes);
    }

}
```
  
**Přihlášení**
```
try {
    $this->user->login("USERNAME", "PASSWORD");
    return true;
} catch (AuthenticationException $e) {
    // Něco se pokazilo, pop.ř. - dump($e->getMessage());
}
```