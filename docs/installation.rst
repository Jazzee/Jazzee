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
Read :doc:`configuration` for information on all the available configuration directives.  To get started you
must configure your database, authentication, and directory settings.

VAR directory permissions
--------------------------
Modify the permissions of JAZZEESRC/var to be writable by the webserver

Initial Setup
---------------

Setting up Jazzee requires using the PHP command line.  Don't Panic!

change to the JAZZEESRC/setup directory and run these.

First build the database and set everything up::

  $./setup install
  Creating database schema and installing default components...
  Database schema created successfully
  Default Page types added
  Default Element types added
  Administrator role created

Now do a quick check to make sure everything is working::

  $./setup preflight
  Preflight Check Passed

The next step depends on the directory service you are using.  If you are using the 
Local directory which does not connect to any other service you will need to log
into Jazzee first with the account you want to enable.  Just visit https://YOURJAZZEESERVER/admin/login
Once you have logged in you can continue and just search for your newly created account.

If you have LDAP enabled you can lookup and add your first user account automatically.  
Just replace FIRST and LAST with the name you are searching for::

  $./setup find-user -fFIRST -lLAST
  Search returned 1 results
  Johnson, Jonathan (jon.johnson@ucsf.edu) has user name 581234@ucsf.edu

The important information here is the username which will display in yellow.  
Copy that name and add it to the Administrators role::

  $./setup user-role 581234@ucsf.edu Administrator
  Johnson, Jonathan added to Administrator role