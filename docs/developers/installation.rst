Installation
============

Composer to install dependancies
---------------------------------

Using composer it is easy to download all jazzee libraries.  Installation instructions
are at `Composer <http://getcomposer.org/download/>`_ we generally install composer in our ~/bin directory for easy access just run::
  
  curl -s https://getcomposer.org/installer | php -- --install-dir=~/bin

Then you can install all dependancies with::

  cd JAZZEE_SRC
  composer.phar install

Vagrant VM Environment
-----------------------

The fastest way to get started developing is to use `Vagrant <http://www.vagrantup.com/>`_ to create a development environment.  
You will need to install Virtualbox, Vagrant and Veewee on your system and you can build the 
Jazzee environment from scratch with a few commands.

============ =======================================
Install       Instructions
============ =======================================
Virtualbox    https://www.virtualbox.org/
Vagrant       http://www.vagrantup.com
Veewee        https://github.com/jedi4ever/veewee
============ =======================================

There are currently two types of VM supported, most development is done on the centOS based box, 
however there is also an Oracle Linux option for testing and working with an Oracle Database.

CentOS
^^^^^^^
With Virtualbox, Vagrant, and Veewee installed run::

  cd JAZZEE_SRC/dev
  vagrant basebox build -f Jazzee_CentOS64
  vagrant basebox export -f Jazzee_CentOS64
  vagrant box add -f Jazzee_CentOS64 Jazzee_CentOS64.box
  cd JAZZEE_SRC
  cp dev/Vagrantfile.centos Vagrantfile
  vagrant up

Oracle
^^^^^^^
With Virtualbox, Vagrant, and Veewee installed run::

  cd JAZZEE_SRC/dev
  vagrant basebox build -f Jazzee_Oracle63
  vagrant basebox export -f Jazzee_Oracle63
  vagrant box add -f Jazzee_Oracle63 Jazzee_Oracle63.box
  cd JAZZEE_SRC
  cp dev/Vagrantfile.oracle Vagrantfile
  vagrant up


Final Setup
^^^^^^^^^^^^

A lot of stuff will happen there - ISO files will be downloaded, a new installation will be built
and then packaged for export.  Packages will be installed and a new VM setup.  
Depending on your connection speed it can take anywhere from a few minutes to a few hours.

Jazzee will be available at http://localhost:8080

You will need to run some initial setup for Jazzee to get the database 
initialized.  Those directions are at :ref:`installation_initial-setup`.  You 
should run them from the virtual machine by doing::

  cd JAZZEE_SRC
  vagrant up
  vagrant ssh

This will log you into the vagrant virtual machine where you can setup the database with::

  cd /vagrant/setup
  ./setup update
  ./setup defaults
  ./setup create-demo