class jazzee::php::pear($packageName, $confPath = false, $template = false, $creates = null) {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      $pear       = 'php-pear'
      $php_devel  = 'php-devel'
      $exec       = '/usr/bin/pear'
      $apache     = 'httpd'
      $modulePath = '/usr/lib64/php/modules'
    }
    default: {fail("$operatingsystem is not defined.")}
  }

  package { $pear:
    ensure => latest,
    alias  => 'pear'
  }

  package { $php_devel:
    ensure => latest,
    alias  => 'php-devel'
  }
  
  exec {$packageName:
    require => [Package['pear'],Package['php-devel'],Exec['pear-auto-discover'],Exec['pear-update-channels']],
    command => "${exec} install ${$packageName}",
    creates => $creates
  }
  
  exec { "pear-auto-discover" :
    command => "${exec} config-set auto_discover 1",
    require => Package[$pear]
  }

  exec { "pear-update-channels" :
    command => "${exec} update-channels",
    require => Package[$pear]
  }
  
  if ($template and $confPath) {
    file {$confPath: 
      notify    => Service[$apache],
      require   => Exec[$packageName],
      content  =>  template($template)
    }
  }
}