CentOS 6
===========================


Satisfying Dependencies
-----------------------------------
Start with the minimal ISO server install

Enable the Extra Packages for Enterprise Linux (EPEL) repository.  Download the rpm
from http://download.fedoraproject.org/pub/epel/6/i386/repoview/epel-release.html

Install it with::

  #rpm -Uvh epel-release*.noarch.rpm

Install dependencies::

  #yum install php php-pecl-apc php-dom php-mbstring php-pecl-imagick clamav clamav-devel

Download the php clam-av extension from http://php-clamav.sourceforge.net/
At the time of this writing the current version was 0.15.6.
Install php-clamav::

  #tar xvzf php-clamav-0.XX.tar.gz
  #cd php-clamav-0.XX
  #phpize
  #./configure --with-clamav
  #make
  #mkdir -p /usr/local/lib/php/extensions/
  #cp modules/clamav.so /usr/local/lib/php/extensions/

Enable the module::

  #echo 'extension=/usr/local/lib/php/extensions/clamav.so
  [clamav]
  clamav.dbpath="/var/lib/clamav"
  clamav.maxreclevel=16
  clamav.maxfiles=10000
  clamav.maxfilesize=26214400
  clamav.maxscansize=104857600
  clamav.keeptmp=0
  clamav.tmpdir="/tmp"' > /etc/php.d/clamav.ini

PDFLib is used to generate PDFs of applications.  It isn't free and we want to replace
it but there does not seem to be a better solution available.  If you want to be able
to create PDFs of applications you will need to purchase and install PDFLib from http://www.pdflib.com/

Edit your apache configuration to allow .htaccess to work by adding AllowOverride FileInto
to the Jazzee Directory

Set your correct timezon in /etc/php.ini.  eg date.timezon=America/Los_Angelas