class jazzee::php::pdflib {
  case $operatingsystem {
    centos, redhat: {
      $modulePath = '/usr/lib64/php/modules/libpdf_php.so'
      file {$modulePath:
        source => 'puppet:///modules/jazzee/libpdf_php.so',
        ensure    => present
      }
      file {'/etc/php.d/pdflib.ini':
        content   => template("jazzee/pdflib.ini.erb"),
        ensure    => present
      }
    }
  }
}