class jazzee::php::apc {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-pecl-apc':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}