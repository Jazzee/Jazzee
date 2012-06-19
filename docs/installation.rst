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
* PECL/Imagick
* PECL/apc
* ldap <http://www.php.net/manual/en/book.ldap.php>
* `PHP ClamAV <http://php-clamav.sourceforge.net/>`_
* `PDFLib <http://www.pdflib.com/>`_ is used to generate PDFs of applications.  It isn't free and we want to replace
  it but there does not seem to be a better solution available.  If you want to be able
  to create PDFs of applications you will need to purchase and install PDFLib.

Platform Specific Instructions
-------------------------------
:doc:`platforms/rhel6`

:doc:`platforms/centos6`

:doc:`platforms/ubuntu1104`

Download Jazzee
----------------
You can download the latest version of jazzee from http://jazzee.org

There is only one directory in Jazzee that should be open to the public JAZZEESRC/webroot.  
The safest way to install Jazzee is to create a link to that directory from your webservers
root.

Jazzee doesn't care whether you run it in a subdirectory, but you will have to make
one important change for that work work properly.  If you planning to install jazzee in 
a subdirectory of your webserver like https://example.com/ourjazzee then you will need to edit
JAZZEESRC/webroot/.htaccess to put that as the RewriteBase::

 RewriteBase /ourjazzee

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

First Steps in a new Install
-----------------------------

Once you have configured and successfully logged into Jazzee you will need to create
a Cycle and Program to really start seeing what Jazzee can do.  If you were already
logged into Jazzee before you added yourself as an administrator you will need to logout
and back in again.

If you only see the Home and My Account menus at the top of the screen then you 
are not in the correct role.  Go back and review the instructions for adding yourself
to the Administrator role above.

Create a new cycle by choosing Cycles from the Manage menu.  Call it something like
test2012 and give it a start and end date.  

Create a new program by choosing Programs from the Manage menu.  You can name it
anything you like.  The Short name is used when creating custom URLs for each program
so keep it short and descriptive.

Now that you have a cycle and program setup you can start building your first application.
Choose Application from the Setup menu.  For now just fill out the required fields and save.  
You can come back for the rest later.  Once you have done that the Setup Menu will include the Pages 
option for creating the structure of your application and you should be off to the races.
