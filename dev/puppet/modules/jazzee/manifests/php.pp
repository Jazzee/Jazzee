class jazzee::php {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      $php                  = 'php'
      $service_name         = 'php'
      $apache               = 'httpd'
      $conf_template        = 'php.ini.erb'
      $conf_path            = '/etc/php.ini'
    }
    default: {fail("$operatingsystem is not defined.")}
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

  class {'jazzee::php::mysql': require => Package['php']}
  class {'jazzee::php::xdebug': require => Package['php']}
  class {'jazzee::php::imagick': require => Package['php']}
  class {'jazzee::php::xml': require => Package['php']}
  class {'jazzee::php::ldap': require => Package['php']}
  class {'jazzee::php::mbstring': require => Package['php']}
  class {'jazzee::php::soap': require => Package['php']}
  class {'jazzee::php::apc': require => Package['php']}
  class {'jazzee::php::pdflib': require => Package['php']}
}