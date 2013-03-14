class jazzee {
  class {'jazzee::apache': }
  class {'jazzee::php': }
  class {'jazzee::mysql': }
  class {'jazzee::mail': }
  $dbName     = 'jazzee'
  $dbUser     = 'jazzee'
  $dbPassword = 'jazzee'
  $dbHost     = 'localhost'
  $varPath    = '/var/tmp/jazzee'
  $safeIps    = '10.0.0.0/8,127.0.0.1'
  $sslCert    = "${varPath}/snakeoil.crt"

  file {'/vagrant/etc/config.ini.php':
      ensure  => file,
      content => template('jazzee/jazzee.ini.erb'),
  }

  file {$varPath:
      ensure => directory,
      owner => root, 
      group => root, 
      mode => 777,
  }

  file {$sslCert:
      ensure  => file,
      require => File[$varPath],
      source => 'puppet:///modules/jazzee/snakeoil.crt',
  }

  exec {'createdb': 
    require  => [Class['jazzee::mysql'], Class['jazzee::php']],
    command   => "mysql -e 'CREATE DATABASE IF NOT EXISTS ${dbName}; GRANT ALL ON ${dbName}.* TO ${dbUser}@localhost IDENTIFIED BY \"${dbPassword}\";'",
    path      => [ "/usr/bin/", "/bin/" ],
  }
}