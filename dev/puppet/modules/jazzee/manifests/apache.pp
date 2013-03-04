class jazzee::apache {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      $httpd            = 'httpd'
      $service_name     = 'httpd'
      $conf_template    = 'httpd.conf.erb'
      $conf_path        = '/etc/httpd/conf/httpd.conf'
    }
    default: {fail("$operatingsystem is not defined.")}
  }

  package { $httpd:
    ensure => latest,
    alias  => 'apache'
  }

  service { 'apache':
    name      => $service_name,
    ensure    => running,
    enable    => true,
    subscribe => File['httpd.conf'],
    require   => Package['apache'],
  }

  file { 'httpd.conf':
    path    => $conf_path,
    ensure  => file,
    require => Package['apache'],
    content  => template("jazzee/${conf_template}"),
  }
}