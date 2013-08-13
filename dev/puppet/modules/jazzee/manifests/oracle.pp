#Class for installing oracle xe database
#adapted from https://github.com/bpholt/puppet-oracle-xe
class jazzee::oracle {
  case $operatingsystem {
    oraclelinux: { 
      $httpPort = 9090
      $dbPort = 1521
      $password = 'root'
      $serverRpm = 'oracle-xe-11.2.0-1.0.x86_64.rpm'
      $clientRpm = 'oracle-instantclient11.2-basic-11.2.0.3.0-1.x86_64.rpm'
      $clientDevRpm = 'oracle-instantclient11.2-devel-11.2.0.3.0-1.x86_64.rpm'
      $sqlClientRpm = 'oracle-instantclient11.2-sqlplus-11.2.0.3.0-1.x86_64.rpm'
      $rpmDir = "/opt/oracle-rpms"
      $tmpDir = "/tmp"
      $configurationFile = "$tmpDir/oracleConfigure"

      file { 'oracle-xe-configure':
        path   => "$configurationFile",
        ensure => file,
        content  => template("jazzee/oracle-server-response.erb"),
        mode   => 0444,
        owner  => root,
        group  => root,
      }

      exec { 'oracle-xe-conf':
        creates => "/etc/sysconfig/oracle-xe",
        command => "/etc/init.d/oracle-xe configure responseFile=$configurationFile",
        user => root,
        require => [Package['oracle-xe'],File['oracle-xe-configure']]
      }

      package { 'oracle-rdbms-server-11gR2-preinstall':
        ensure => latest,
        alias  => 'oracle-preinstall'
      }
      package { 'oracle-xe':
        ensure   => present,
        source   => "$rpmDir/$serverRpm",
        provider => 'rpm',
        require => Package['oracle-preinstall']
      }
      service { 'oracle-xe':
        ensure => running,
        enable => true,
        require   => [Package['oracle-xe'],Exec['oracle-xe-conf']]
      }
      package { 'oracle-instantclient11.2-basic':
        ensure   => present,
        source   => "$rpmDir/$clientRpm",
        provider => 'rpm',
        require => Package['oracle-preinstall']
      }
      package { 'oracle-instantclient11.2-devel':
        ensure   => present,
        source   => "$rpmDir/$clientDevRpm",
        provider => 'rpm',
        require => Package['oracle-instantclient11.2-basic']
      }
      package { 'oracle-instantclient11.2-sqlplus':
        ensure   => present,
        source   => "$rpmDir/$sqlClientRpm",
        provider => 'rpm',
        require => Package['oracle-instantclient11.2-basic']
      }

      $addVagrantUserSql = "DECLARE\nu_count number;\nBEGIN u_count :=0;\nSELECT COUNT (1) INTO u_count FROM dba_users WHERE username = 'VAGRANT';\nIF u_count = 0 THEN\nEXECUTE IMMEDIATE 'CREATE USER vagrant identified by vagrant';\nEXECUTE IMMEDIATE 'GRANT create session to vagrant';\nEXECUTE IMMEDIATE 'GRANT all privileges to vagrant';\nELSE\nEXECUTE IMMEDIATE 'GRANT create session to vagrant';\nEXECUTE IMMEDIATE 'GRANT all privileges to vagrant';\nEND IF;\nEND;\n/\nEXIT"
      file { 'vagrant-user':
        path   => "$tmpDir/addvagrantuser.sql",
        ensure => file,
        content  => $addVagrantUserSql,
        mode   => 0444,
        owner  => root,
        group  => vagrant,
      }
      exec {'openaccess': 
        require  =>  [Service['oracle-xe'],Package['oracle-instantclient11.2-basic'],File['vagrant-user']],
        environment => 'LD_LIBRARY_PATH=/usr/lib/oracle/11.2/client64/lib:$LD_LIBRARY_PATH',
        command   => "sqlplus64  SYSTEM/$password@//localhost:$dbPort/xe @$tmpDIr/addvagrantuser.sql",
        path      => [ "/usr/bin/", "/bin/" ],
      }

      file { ["/etc/profile.d/oracle.sh","/etc/profile.d/oracle.csh"]:
        ensure => present,
        content => "export LD_LIBRARY_PATH=/usr/lib/oracle/11.2/client64/lib:$LD_LIBRARY_PATH\nexport ORACLE_HOME=/usr/lib/oracle/11.2/client64\nexport ORACLE_SID=xe\nexport TNS_ADMIN=/usr/lib/oracle/11.2/client64/etc\nalias sqlplus=sqlplus64",
        mode => 0644,
        owner => 'root',
        group => 'root'
      }
    }
    default: {fail("$operatingsystem is not supported for oracle DB install.")}
  }
}