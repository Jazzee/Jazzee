class jazzee::php::xml {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-xml':
        ensure => latest,
        notify => Service['httpd']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}