class jazzee::php::pdflib {
  case $operatingsystem {
    centos, redhat, oraclelinux: {
      $modulePath = '/usr/lib64/php/modules/php_pdflib.so'
      file {$modulePath:
        source => 'puppet:///modules/jazzee/php_pdflib.so',
        ensure    => present
      }
      file {'/etc/php.d/pdflib.ini':
        content   => template("jazzee/pdflib.ini.erb"),
        ensure    => present
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}
