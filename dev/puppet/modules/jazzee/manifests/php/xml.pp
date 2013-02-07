class jazzee::php::xml {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-xml':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}