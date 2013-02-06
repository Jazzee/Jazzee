class jazzee::php::imagick {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-domxml':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}