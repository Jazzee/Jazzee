class jazzee::php {
  case $operatingsystem {
    centos, redhat: { 
      $php                  = 'php'
      $service_name         = 'php'
      $apache               = 'httpd'
      $conf_template        = 'php.ini.erb'
      $conf_path            = '/etc/php.ini'
    }
  }

  package { $php:
    ensure => latest,
    alias  => 'php'
  }

  file { 'php.ini':
    path    => $conf_path,
    ensure  => file,
    require => Package['php'],
    notify => Service[$apache],
    content  => template("jazzee/${conf_template}"),
  }

  class {'jazzee::php::mysql': }
  class {'jazzee::php::xdebug': }
  class {'jazzee::php::imagick': }
}