class jazzee::php::mysql {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      $phpmysql                  = 'php-mysql'
      $apache               = 'httpd'
    }
    default: {fail("$operatingsystem is not defined.")}
  }

  package { $phpmysql:
    ensure => latest,
    alias  => 'phpmysql',
    notify => Service[$apache]
  }
}