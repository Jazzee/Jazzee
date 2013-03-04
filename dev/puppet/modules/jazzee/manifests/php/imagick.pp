class jazzee::php::imagick {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-pecl-imagick':
        ensure => latest,
        notify => Service['httpd']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}