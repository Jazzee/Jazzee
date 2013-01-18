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

Once you have those installed you can run::

  cd JAZZEE_SRC/dev
  vagrant basebox build Jazzee_CentOS63
  vagrant basebox export Jazzee_CentOS63
  vagrant box add Jazzee_CentOS63 Jazzee_CentOS63.box

A lot of stuff will happen there - ISO files for Centos will be downloaded, a new installation will be built 
and then packaged for export.  Depending on your connection speed it can take anywhere from a few minutes to a few hours.

Once that is complete you can go to JAZZEE_SRC and use vagrant to start a new VM run::

  vagrant up

and Jazzee will be availalbe at http://localhost:8080

You will need to run some intiial setup for Jazzee to get the database 
initialized.  Those directions are at :ref:`installation_initial-setup`.  You 
should run them from the virtual machine by doing::

  cd JAZZEE_SRC
  vagrant up
  vagrant ssh

This will log you into the vagrant virtual machine where you can find jazzee at::

  cd /vagrant