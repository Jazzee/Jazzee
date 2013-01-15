class jazzee::php::mysql {
  case $operatingsystem {
    centos, redhat: { 
      $phpmysql                  = 'php-mysql'
      $apache               = 'httpd'
    }
  }

  package { $phpmysql:
    ensure => latest,
    alias  => 'phpmysql',
    notify => Service[$apache]
  }
}