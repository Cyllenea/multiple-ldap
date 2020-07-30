Multiple LDAP
=============

Tato knihovna slouží pro postupnou autorizaci na více LDAP serverech, pokud na předchozím serveru nebyl pokus úspěšný.

```
extensions:
    ldap: cyllenea\multiple-ldap\LDAPExtension


ldap:
    attributes:
        # - memberOf
        - employeeNumber    # Employee ID
        - employeeID        # Cost center
        - mail              # Email address
        - cn                # Common name
        - sn                # Surname
        - givenName         # First name

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

authenticator:
    class: cyllenea\multiple-ldap\Authenticator
    setup:
        - setIdentityGenerator([@userManagemenent, 'createIdentity'])
```
            