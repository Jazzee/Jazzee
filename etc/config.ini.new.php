;This file was auto generated during the jazzee installation.
;The syntax of the file is extremely simple. Whitespace and Lines
;beginning with a semicolon are silently ignored (as you probably guessed).
;Section headers (e.g. [Foo]) are also silently ignored, even though
;they might mean something in the future.

;Directives are specified using the following syntax:
;directive = value
;Directive names are *case sensitive* - foo=bar is different from FOO=bar.

;The value can be a string, a number, a PHP constant (e.g. E_ALL or M_PI), one
;of the INI constants (On, Off, True, False, Yes, No and None) or an expression
;(e.g. E_ALL ~E_NOTICE), or a quoted string ("foo").

;Mode is used to controll access to a production jazzee installation
;There are three possible levels
;LIVE - No access Controll
;SOFT - GET Requests are redirected to the maintence page, but POST requests are saved before they are redirected
;DOWN - ALL requests are redirected to the maintence page. No database access
mode=LIVE

;There are three status levels available which controll things like log output
; and payment processing:
;PRODUCTION - Live jazzee site open to the public
;PREVIEW - Open to testers and internal users only
;DEVELOPMENT - Open only to developers. Logs and other dangerous information available on screen
status=PRODUCTION

;Force HTTPS connection and secure cookies. Should only be disabled on internal development sites when SSL is not available.
;On productions systems it is also a good idea to reidrect non-secure traffic at the webserver level to ensure no data is transmitted in the clear.
forceSSL=on

;Doctrine DSN
;eg mysql://user:pass@localhost/databasename
;Info at http://www.doctrine-project.org/documentation/manual/1_2/en/introduction-to-connections
dsn=

;Server/account to send email from
;Format: type://[username:password@]host:port
;eg smpt+ssl://user:123Pass$@mail.example.com:443
;leave null to use PHPs built in mail()
mailServer=

;The FROM email address to apply by default and to system messages (like logs), if blank mail server will probably apply a default
mailDefaultFrom=

;If an address was specified above you can also give it a nice name like Jazzee System which will be displayed in most mail clients
mailDefaultName=

;If an email addres is specified here all mail will go to that address. Usefull for testing and development environments
mailOverrideTo=

;Path to the var directory where sessions, caches, logs etc are stored
;This only needs to be changed in development environments where the applicaiton root is over written regularly
varPath=

;A comma seperated list of email addresses that critical system events should be sent to.
adminEmail=

;The timezone this machine is located in. A list of supported options can be found at http://php.net/manual/en/timezones.php
timezone=

;Call this bootstrap file to load local customizations.
localBootstrap=
