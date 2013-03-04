class jazzee ($databaseType) {
  class {'jazzee::apache': }
  class {'jazzee::php': }
  class {'jazzee::mail': }
  case $databaseType {
    mysql: {
      class {'jazzee::mysql': }
      $dbName     = 'jazzee'
      $dbUser     = 'jazzee'
      $dbPassword = 'jazzee'
      $dbHost     = 'localhost'
      $dbDriver   = 'pdo_mysql'
      exec {'createdb': 
        require  => [Class['jazzee::mysql'], Class['jazzee::php']],
        command   => "mysql -e 'CREATE DATABASE IF NOT EXISTS ${dbName}; GRANT ALL ON ${dbName}.* TO ${dbUser}@localhost IDENTIFIED BY \"${dbPassword}\";'",
        path      => [ "/usr/bin/", "/bin/" ],
      }
    }
    oracle: {
      class {'jazzee::oracle': }
      $dbName     = 'xe'
      $dbUser     = 'JAZZEE'
      $dbPassword = 'jazzee'
      $dbHost     = 'localhost'
      $dbDriver   = 'oci8'
      file { 'jazzee-user':
        path   => "/tmp/addjazzeeuser.sql",
        ensure => file,
        content  => "DECLARE u_count number; BEGIN u_count :=0; SELECT COUNT (1) INTO u_count FROM dba_users WHERE username = 'JAZZEE'; IF u_count = 0 THEN EXECUTE IMMEDIATE 'CREATE USER jazzee identified by jazzee'; EXECUTE IMMEDIATE 'GRANT create session to jazzee'; EXECUTE IMMEDIATE 'GRANT all privileges to jazzee'; ELSE EXECUTE IMMEDIATE 'GRANT create session to jazzee'; EXECUTE IMMEDIATE 'GRANT all privileges to jazzee'; END IF; END;\n/\nEXIT",
        mode   => 0444,
        owner  => root,
        group  => vagrant,
      }
      exec {'addjazzeeuser': 
        require  =>  [Class['jazzee::oracle'],File['jazzee-user']],
        environment => 'LD_LIBRARY_PATH=/usr/lib/oracle/11.2/client64/lib:$LD_LIBRARY_PATH',
        command   => "sqlplus64  VAGRANT/vagrant@//localhost:1521/xe @/tmp/addjazzeeuser.sql",
        path      => [ "/usr/bin/", "/bin/" ],
      }
      class {'jazzee::php::pear': 
        packageName => 'pecl/oci8',
        template => 'jazzee/oci8.ini.erb',
        confPath => '/etc/php.d/oci8.ini',
        creates => '/usr/lib64/php/modules/oci8.so',
        require => Class['jazzee::php']
      }
    }
    default: {fail("$databaseType is not valid.  Correct values are mysql, oracle")}
  }
  
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
}
