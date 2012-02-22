Ubuntu Server 11.04
=====================


Satisfying Dependencies
-----------------------------------

Install lamp stack::

  $sudo apt-get install tasksel
  $sudo tasksel install lamp-server 

Install required php extensions::

  #sudo apt-get install php-log php5-imagick php-apc

Install the Doctrine ORM::
  $sudo pear config-set auto_discover 1
  $sudo pear install pear.doctrine-project.org/DoctrineORM