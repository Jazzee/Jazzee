class jazzee::php::soap {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-soap':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}