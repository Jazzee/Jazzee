Ubuntu Server 11.04
=====================


Satisfying Dependencies
-----------------------------------

Install lamp stack::

  $sudo apt-get install tasksel
  $sudo tasksel install lamp-server

Enable apache modrewrite::
  $sudo a2enmod rewrite

You will need to edit /etc/httpd/conf.d/sites-available/default to allow .htaccess to work.
Add AllowOverride FileInfo to the Jazzee Directory

Install required php extensions::

  $sudo apt-get install php-log php5-imagick php-apc php-curl php-uuid php-htmlpurifier