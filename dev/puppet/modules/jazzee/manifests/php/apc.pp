class jazzee::php::apc {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-pecl-apc':
        ensure => latest,
        notify => Service['httpd']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}