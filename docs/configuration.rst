Configuration
==============

Jazzee does its best to be very configurable so it can be deployed in different places
without any changes to the source code.  This is a list of all the available configuration options.
In many cases you will not need to change any of these from the default.
They are separated into contextual areas in this documentation for readability,
but there are no actual distinctions of configuration options.

System
-------

mode
^^^^^
The mode allows you to limit access to the application easily. Possible values are:

* LIVE the default everything running mode
* APPLY_MAINTENANCE which does not allow applicants or recommenders to access the system
* MAINTENANCE which prevents everyone from accessing the system

maintenanceModeMessage
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Message displayed to anyone who cannot access the system becuase of a mode setting

broadcastMessage
^^^^^^^^^^^^^^^^^^^^^^^^^
Message displayed to everyone on every page.  Useful for advertising future downtime or any other significant system wide events.

status
^^^^^^^^^^^^^^^^^^^^^^^^^
Provides information  to JAZZEE components about the current system state.  Possible values are:

* PRODUCTION the default live application status
* PREVIEW limits some functionality in a draft installation.  Useful for QA where something like payments shouldn't work - but caching should still work and email should still go out
* DEVELOPMENT If you're working on Jazzee this is the status for you.  If redirects outgoing email and limits caching

sessionName
^^^^^^^^^^^^^^^^^^^^^^^^^
What to name the PHP session.  Defaults to 'JAZZEE'

applicantSessionLifetime
^^^^^^^^^^^^^^^^^^^^^^^^^
The maximum session lifetime for an applicant in seconds.  Defaults to '0' which means applicants stay logged in until they close their browser or logout manually

adminSessionLifetime
^^^^^^^^^^^^^^^^^^^^^^^^^
The maximum session lifetime for administrators.  Defaults to 7200 or two hours.

varPath
^^^^^^^^^^^^^^^^^^^^^^^^^
The system path to the VAR directory.  Defaults to JAZZEESOURCE/var.  This directory must be writable be the webserver.  It is where session data, temporary files, uploads, and logs will get writtend to.

maximumApplicantFileUploadSize
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The maximum size for applicant file uploads.  Programs will not be able to override this setting.
Defaults to the value of PHP's builtin upload_max_filesize which is generally pretty large so you should set this to something sensible like 1M

defaultApplicantFileUploadSize
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The default size for applicant file uploads.  This will be initially set for any File Upload element and will
often not be overridden so it has a normative effect on file upload sizes.
Defaults to the value of PHP's builtin upload_max_filesize which is generally pretty large so you should set this to something sensible like 1M

virusScanUploads
^^^^^^^^^^^^^^^^^^^^^^^^^
Should uploaded files be scanned for viruses.  User ClamAV which must be installed.  This is a pretty
substantial performance hit so if you're experiencing problems with load this should be deactivated.
Defaults to true.

allowApplicantNameChange
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Are applicant allowed to change their own name.  Defaults to false.

allowApplicantEmailChange
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Are applicant allowed to change their own email address.  Defaults to false.


allowApplicantPasswordChange
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Are applicant allowed to change their own password.  Defaults to false.

allowApplicantPrintApplication
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Are applicant allowed to print their application.  Defaults to false.

maximumAdminFileUploadSize
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
The maximum size for administrator file uploads.
Defaults to the value of PHP's builtin upload_max_filesize which is generally pretty large so you should set this to something sensible like 5M

publicKeyCertificatePath
^^^^^^^^^^^^^^^^^^^^^^^^^
The SSL public key certificate to use in encrypting data.  Only the public key should reside on the Jazzee server as Jazzee has no method for decrypting data.

recaptchaPrivateKey
^^^^^^^^^^^^^^^^^^^^^^^^^
Your reCaptch private key to use for new applicant accounts.  More information at http://www.google.com/recaptcha

recaptchaPublicKey
^^^^^^^^^^^^^^^^^^^^^^^^^
Your reCaptch public key to use for new applicant accounts.  More information at http://www.google.com/recaptcha

pdflibLicenseKey
^^^^^^^^^^^^^^^^^^^^^^^^^
If you want some advanced PDF functions you will have to purchase a PDFlib+PDI license
and enter your license key here. http://www.pdflib.com/

adminCronAllowed
^^^^^^^^^^^^^^^^^^^^^^^^^
Hostnames or IP addresses which are allowed to hit the cron page and trigger
a run.  If you're using links to trigger cron from the webserver jazzee is on then leaving this as the default 'localhost' is fine.

Database
---------

dbHost
^^^^^^^^^^^^^^^^^^^^^^^^^
The Database host name.  Defaults to 'localhost'

dbPort
^^^^^^^^^^^^^^^^^^^^^^^^^
The database port

dbName
^^^^^^^^^^^^^^^^^^^^^^^^^
The database name

dbUser
^^^^^^^^^^^^^^^^^^^^^^^^^
The database user

dbPassword
^^^^^^^^^^^^^^^^^^^^^^^^^
The database password

dbDriver
^^^^^^^^^^^^^^^^^^^^^^^^^
The database driver.  The allowed types can be found at the `Doctrine Project website <http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver>`_

dbCharset
^^^^^^^^^^^^^^^^^^^^^^^^^
The database character set.  This differers by DB vendor, but should be set to the equivalent of UTF8 for best results.
Defaults to 'utf8' which works for MySQL.

Email
-------

mailServerType
^^^^^^^^^^^^^^^^^^^^^^^^^
The type of outgoing mail server we will be using defaults to php. Possible values are:

* php to use the builtin php mail() function
* sendmail to use the sendmail binary on the server
* smtp to use a remote smtp server
* smtp+ssl for a secure connection to a remote smtp server

mailServerHost
^^^^^^^^^^^^^^^^^^^^^^^^^
The hostname for the mailserver - only required for external smtp mailServerTypes

mailServerPort
^^^^^^^^^^^^^^^^^^^^^^^^^
The port for the mailserver - only required for external smtp mailServerTypes

mailServerUsername
^^^^^^^^^^^^^^^^^^^^^^^^^
The username for the mailserver - only required for external smtp mailServerTypes

mailServerPassword
^^^^^^^^^^^^^^^^^^^^^^^^^
The password for the mailserver - only required for external smtp mailServerTypes

mailSubjectPrefix
^^^^^^^^^^^^^^^^^^^^^^^^^
If set all outgoing mail subject lines will be prefixed with this string

mailDefaultFromAddress
^^^^^^^^^^^^^^^^^^^^^^^^^
If no address is set for the outgoing message it will default to this address.  You should set this otherwise a system default like postmaster@local.nothing could get sent.

mailDefaultFromName
^^^^^^^^^^^^^^^^^^^^^^^^^
If no address is set for the outgoing message it will use this name.

mailOverrideToAddress
^^^^^^^^^^^^^^^^^^^^^^^^^
This should only be used in DEVELOPMENT environments.  It will send ALL outoing mail to this address.  NOT the intended recipient.

Authentication
---------------

.. _configuration-adminAuthenticationClass:

adminAuthenticationClass
^^^^^^^^^^^^^^^^^^^^^^^^^
Authentication for administrators can be handled by several different methods.  Builtin options are:

* Shibboleth - for schools which have shibboleth IDPs.
* SimpleSAML - an easier to configure shibboleth SP.  If the webserver you are  using doesn't have shibboleth installed this may be the right choice for you.
* OpenID - This will allow anyone with a google, yahoo or other internet account to log in.
* NoAuthentication - only if Jazzee is in DEVELOPER status.  This allows the user to pick ANY user account and login as them.

See :doc:`authentication/index` for more information on the different authentication systems.

shibbolethUsernameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If Shibboleth is set as the adminAuthenticationClass this is the attribute name we will use as the userName.  This is almost always the default of 'eppn'

shibbolethFirstNameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If Shibboleth is set as the adminAuthenticationClass this is the attribute name we will use as the first name.  This is almost always the default of 'givenName'

shibbolethLastNameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If Shibboleth is set as the adminAuthenticationClass this is the attribute name we will use as the last name.  This is almost always the default of 'sn'

shibbolethEmailAddressAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If Shibboleth is set as the adminAuthenticationClass this is the attribute name we will use as the email address.  This is almost always the default of 'mail'

shibbolethLoginUrl
^^^^^^^^^^^^^^^^^^^^^^^^^
If Shibboleth is set as the adminAuthenticationClass this is the url applicants will be directed to in order to login.  This is almost always the default of '/Shibboleth.sso/Login'

shibbolethLogoutUrl
^^^^^^^^^^^^^^^^^^^^^^^^^
If Shibboleth is set as the adminAuthenticationClass this is the url applicants will be directed to in order to logout.  This is almost always the default of '/Shibboleth.sso/Logout'

noAuthIpAddresses
^^^^^^^^^^^^^^^^^^^^^^^^^
if NoAuthentication is set as the adminAuthenticationClass this restricts what ip addresses can be used to authenticate.  Defaults to 127.0.0.1 (the localhost)

simpleSAMLIncludePath
^^^^^^^^^^^^^^^^^^^^^^^^^
If SimpleSAML is set as the adminAuthenticationClass this is the path to the autoloader so it can be included when needed.

simpleSAMLAuthenticationSource
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If SimpleSAML is set as th adminAuthenticationClass this is the IDP

simpleSAMLUsernameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
we will use as the userName.  This is almost always the default of 'eduPersonPrincipalName'

simpleSAMLFirstNameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
we will use as the first name.  This is almost always the default of 'givenName'

simpleSAMLLastNameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If SimpleSAML is set as the adminAuthenticationClass this is the attribute name
we will use as the last name.  This is almost always the default of 'sn'

simpleSAMLEmailAddressAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If SimpleSAML is set as the adminAuthenticationClass this is the attribute name we will use as the email address.  This is almost always the default of 'mail'

Directory
----------

adminDirectoryClass
^^^^^^^^^^^^^^^^^^^^^^^^^
The class to use when looking up users.  If your campus has an LDAP directory you
should use Ldap so you can search for new users there.  Otherwise Local looks up users
who alrady have Jazzee accounts.  If your using OpenID for you adminAuthenticationClass
then Local is the only way to go.

ldapHostname
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the host name for you server

ldapPort
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the port for you server

ldapBindRdn
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the bind RDN for you server

ldapBindPassword
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the bind password for you server

ldapUsernameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the attribute name
we will use as the username.  This is should match what will be returend in  for the shibbolethUserName

ldapFirstNameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the attribute name
we will use as the first name.  This is almost always the default of 'givenName'

ldapLastNameAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the attribute name
we will use as the last name.  This is almost always the default of 'sn'

ldapEmailAddressAttribute
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the attribute name
we will use as the email address.  This is almost always the default of 'mail'

ldapSearchBase
^^^^^^^^^^^^^^^^^^^^^^^^^
If Ldap is set as your adminDirectoryClass then this is the search base for
your directory.  Usually something like 'ou=people, dc=ucsf, dc=edu'
