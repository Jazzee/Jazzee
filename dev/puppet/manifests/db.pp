class host {
	include jazzee::data
	exec { 'apt-get update':
	  command => '/usr/bin/apt-get update'
  }
  Package { 
	  require => Exec["apt-get update"],
  }
  group { "puppet": 
		ensure => "present", 
	}
	package { "vim":
		ensure => present,
	}
	
	package { "nfs-common":
		ensure => present,
	}
	
	class { 'mysql::server': 
		config_hash => { 'bind_address' => '0.0.0.0' }
	}
	
	mysql::db { $jazzee::data::db_name:
		user     => $jazzee::data::db_user,
		password => $jazzee::data::db_password,
		host     => '%',
		grant    => ['all'],
		charset => 'utf8',
  }
}

class {'host': }