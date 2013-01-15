class jazzee::php::pear($packageName, $confPath = false, $template = false) {
  case $operatingsystem {
    centos, redhat: { 
      $pear       = 'php-pear'
      $php_devel  = 'php-devel'
      $exec       = '/usr/bin/pear'
      $apache     = 'httpd'
      $modulePath = '/usr/lib64/php/modules'
    }
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
    require => [Package['pear'],Package['php-devel']],
    command => "${exec} install ${$packageName}"
  }
  
  if ($template and $confPath) {
    file {$confPath: 
      notify    => Service[$apache],
      require   => Exec[$packageName],
      content  =>  template($template)
    }
  }
}