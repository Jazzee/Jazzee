class jazzee::php::mbstring {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-mbstring':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}