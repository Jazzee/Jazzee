class jazzee::php::xdebug {
  case $operatingsystem {
    centos, redhat: { 
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
  }
}