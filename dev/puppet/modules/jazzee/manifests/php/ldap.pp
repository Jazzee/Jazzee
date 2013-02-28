class jazzee::php::ldap {
  case $operatingsystem {
    centos, redhat: { 
      package { 'php-ldap':
        ensure => latest,
        notify => Service['httpd']
      }
    }
  }
}