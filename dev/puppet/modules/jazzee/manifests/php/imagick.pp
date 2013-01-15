class jazzee::php::imagick {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-pecl-imagick':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}