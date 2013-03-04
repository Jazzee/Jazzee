class jazzee::php::mbstring {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-mbstring':
        ensure => latest,
        notify => Service['httpd']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}