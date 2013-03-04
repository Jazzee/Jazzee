class jazzee::mysql {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      $mysql              = 'mysql-server'
      $service_name     = 'mysqld'
      $conf_template    = 'my.cnf.erb'
      $conf_path        = '/etc/my.cnf'
    }
    default: {fail("$operatingsystem is not defined.")}
  }

  package { $mysql:
    ensure => latest,
    alias  => 'mysql'
  }

  service { $service_name:
    ensure    => running,
    require   => File['my.cnf'],
  }

  file { 'my.cnf':
    path      => $conf_path,
    ensure    => file,
    require   => Package['mysql'],
    content   => template("jazzee/${conf_template}"),
    notify    => Service[$service_name]
  }

  exec {'openaccess': 
    require  =>  Service[$service_name],
    command   => "mysql -u root -e 'GRANT ALL ON *.* TO 'vagrant'@localhost WITH GRANT OPTION; FLUSH PRIVILEGES;'",
    path      => [ "/usr/bin/", "/bin/" ],
  }
}