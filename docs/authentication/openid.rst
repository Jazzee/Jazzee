OpenID Authentication
======================

From Wikipedia - OpenID is an open standard that describes how users can be authenticated in a decentralized manner, 
eliminating the need for services to provide their own ad hoc systems and allowing users to consolidate 
their digital identities.[1] Users may create accounts with their preferred OpenID 
identity providers, and then use those accounts as the basis for signing on 
to any website which accepts OpenID authentication. The OpenID standard provides 
a framework for the communication that must take place between the identity provider 
and the OpenID acceptor (the ‘relying party’). An extension to the standard 
(the OpenID Attribute Exchange) facilitates the transfer of user attributes, such 
as name and gender, from the OpenID identity provider to the relying party (each 
relying party may request a different set of attributes, depending on its requirements).

**In Jazzee** openId is usefull if you do not have a campus identity solution or
if you want to preview Jazzee without integrating with your campuses solution.

Dependencies
-------------
Pear/OpenID must be installed and the Curl extension must be available in PHP.
Because OpenID does not have a central directory users should sign in to Jazzee
once before they are put in any roles.  Enable OpenID for user authentication by 
setting :ref:`configuration-adminAuthenticationClass` to OpenID.