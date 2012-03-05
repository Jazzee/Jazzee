Authentication
===============

Jazzee applicants create accounts in the system using thier email address and a 
self selected password.  For administrative users there is pluggable authentication 
system which allows different campuses to use thier own prefered method.  If your
campus has a single sign on or campus wide authentication system that is not already
available in Jazzee it should be fairly straight forward to implement it yourself.  If
your solution can be used by other campuses please contribute it back.

.. toctree::

  shibboleth
  openid
  simplesaml
  noauthentication