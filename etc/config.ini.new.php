;PHP ini file for Jazzee Configuration
;Many of these items are optional and have been commented out
;In places where a default is usefull it is selected


;### DATABASE CONNECTION ###;
;#specifics and allowed options at http://www.doctrine-project.org/docs/dbal/2.0/en/reference/configuration.html
dbHost=
;dbPort=
dbName=
dbUser=
dbPassword=
dbDriver=


;### ADMIN AUTHENTICATION ###;
;#admin authentication is replacable by any class which implements AdminAuthInterface
;#there are buit in classes for Shibboleth and simplesaml 
;#there is also a dangerous developement type called NoAuthentication that can be used for development
;#Builtin options: '\Jazzee\AdminAuthentication\SimpleSAML', '\Jazzee\AdminAuthentication\Shibboleth', '\Jazzee\AdminAuthentication\NoAuthentication'
adminAuthenticationClass=

;#Uncomment and set the options for the authentication type you have selected

;##Shibboleth##;
;#username attribute name
;#firstname attribute name
;#last anme attribute name
;#email address attribute name
;ShibbolethUsernameAttribute='eppn'
;ShibbolethFirstNameAttribute='givenName'
;ShibbolethLastNameAttribute='sn'
;ShibbolethEmailAddressAttribute='mail'

;##NoAuthentication##;
;noAuthUserId=0
;noAuthIpAddresses='127.0.0.1'

;##SimpleSAML##;
;#include path for simplesamle autoloader
;#authentication source
;#username attribute name
;#firstname attribute name
;#last anme attribute name
;#email address attribute name
;simpleSAMLIncludePath=
;simpleSAMLAuthenticationSource='default-sp'
;simpleSAMLUsernameAttribute='eduPersonPrincipalName'
;simpleSAMLFirstNameAttribute='givenName'
;simpleSAMLLastNameAttribute='sn'
;simpleSAMLEmailAddressAttribute='mail'

;### ADMIN DIRECTORY ###;
;#admin directory is replacable by any class which implements AdminDirectory interface
;There is a builtin for LDAP
;#Builtin options: '\Jazzee\AdminDirectory\Ldap'
adminDirectoryClass=

;LdapHostname=
;LdapPort=
;LdapBindRdn=
;LdapBindPassword=
;LdapUsernameAttribute='eppn'
;LdapFirstNameAttribute='givenName'
;LdapLastNameAttribute='sn'
;LdapEmailAddressAttribute='mail'

;### MAIL SERVER ###;
;#Server/account to send email from
;#type must be one of 'php','sendmail','smtp','smtp+ssl'
mailServerType=php
;mailServerHost=
;mailServerPort=
;mailServerUsername=
;mailServerPassword=

;#The FROM email address to apply by default and to messages without a sender (like logs)
;#if blank mail server will probably apply a default based on your hostname
;#if you speicify an address you can also specify a nice name like 'Jazzee System'
;mailDefaultFromAddress=
;mailDefaultFromName=

;#Email address for system events and logs to be sent to
;adminEmail=

;### MISC OPTIONS ###;
;#Recaptch keys are free - go to http://www.google.com/recaptcha
;recaptchaPrivateKey=
;recaptchaPublicKey=

;PDFlib License Key - purchase at http://www.pdflib.com/
;There is an education discount
pdflibLicenseKey=

maximumApplicantFileUploadSize=1m
maximumAdminFileUploadSize=5m

;#Public OPENSSL PKI certificate is used to encrypt applicant input for especially sensitive data like SSNs
;#For security only the public key should be stored on the Jazzee System and decryption should happen offline
;publicKeyCertificatePath=

;#Mode is used to controll access to a production jazzee installation
;#There are three possible levels
;#LIVE - No access Controll
;#MAINTENANCE - ALL requests are redirected to the maintence page. No database access
;#APPLY_MAINTENANCE - No access to apply controllers - admin area is available
mode=LIVE
;maintenanceModeMessage=

;broadcast messagesa are displayed on every screen to announce something critical
;broadcastMessage=

;### DEVELOPMENT OPTIONS ###;
;#There are three status levels available which controll things like log output and payment processing:
;#PRODUCTION - Live jazzee site open to the public
;#PREVIEW - Open to testers and internal users only - some things like recommendation emails get routed differently and payments are in testing mode
;#DEVELOPMENT - Open only to developers. Logs and other dangerous information available on screen
status=PRODUCTION

;#Path to the var directory where sessions, caches, logs etc are stored
;#This only needs to be changed in development environments where the applicaiton root is over written regularly
;varPath=

;#You can override the TO address of all mail in order to catch it in development
;mailOverrideToAddress=