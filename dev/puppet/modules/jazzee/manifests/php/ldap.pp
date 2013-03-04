class jazzee::php::ldap {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-ldap':
        ensure => latest,
        notify => Service['httpd']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}