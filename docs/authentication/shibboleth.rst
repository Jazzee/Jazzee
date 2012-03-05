Shibboleth Authentication
==========================

Shibboleth is a free, open-source web single sign-on system with rich attribute-exchange 
based on open standards, principally SAML. It is a federated system, 
supporting secure access to resources across security domains. 
Information about a user is sent from a home identity provider (IdP) to a 
service provider (SP) which prepares the information for protection of sensitive 
content and use by applications. So-called federations, while not a purely technical construct, 
can often be used to help providers trust each other in a scalable way.  Information on shibboleth can
be found at http://shibboleth.internet2.edu/

Once you have configured your web server to act as an SP you can enable shibboleth for
user authentication be setting :ref:`configuration-adminAuthenticationClass` to Shibboleth.