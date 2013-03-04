class jazzee::php::xdebug {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      package { 'php-pecl-xdebug':
        ensure => latest,
        notify => Service['httpd']
      }
      
      file {'/etc/php.d/xdebug.ini':
        content   => template("jazzee/xdebug.ini.erb"),
        ensure    => present,
        require   => Package['php-pecl-xdebug']
      }
    }
    default: {fail("$operatingsystem is not defined.")}
  }
}