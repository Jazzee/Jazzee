class jazzee::php::soap {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-soap':
        ensure => latest,
        notify => Service['httpd']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}