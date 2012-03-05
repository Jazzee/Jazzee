Installation
============

A basic Jazzee installation with no customizations should be fairly straightforward to install.  The
biggest hurdle is ensuring that you have all of the required dependencies on your system.
Jazzee requires a minimum of PHP 5.3.0. For greatly improved performance it is 
also recommended that you use APC with PHP.

Required Apache Modules
------------------------
* mod_rewrite

Required PHP extensions
------------------------
* pear/Log
* PECL/Imagick
* htmlpurifier.org/HTMLPurifier
* pear.doctrine-project.org/DoctrineORM
* pear/apc
* pear/ldap
* PECL/UUID
* `PHP ClamAV <http://php-clamav.sourceforge.net/>`_
* `PDFLib <http://www.pdflib.com/>`_ is used to generate PDFs of applications.  It isn't free and we want to replace
  it but there does not seem to be a better solution available.  If you want to be able
  to create PDFs of applications you will need to purchase and install PDFLib.

Platform Specific Instructions
-------------------------------
:doc:`platforms/rhel6`

:doc:`platforms/ubuntu1104`

Download Jazzee
----------------
You can download the latest version of jazzee from http://jazzee.org

Initial Configuration
----------------------
Copy JAZZEESRC/etc/config.ini.new.php to JAZZEESRC/etc/config.ini.php and edit it for your environment.

VAR directory permissions
--------------------------
Modify the permissions of JAZZEESRC/var to be writable by the webserver

Initial Setup
---------------

Install the databse and the first user.  This is currently done with the setup 
command, but that needs to go away and get replaced with a nice web interface.