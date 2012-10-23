class host {
  include jazzee
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
	package { "screen":
		ensure => present,
	}
	package { "mysql-client":
		ensure => present,
	}
	
	package { "nfs-common":
		ensure => present,
	}
	
	class { 'apache':
		serveradmin => 'testadmin@localhost',
		default_mods => false
	}
	
	apache::vhost { 'localhost':
			priority        => '1',
                        override        => ['FileInfo'],
			vhost_name      => '*',
			port            => '80',
			docroot         => '/vagrant/webroot/',
	}
	
	a2mod { "enable dir module":
			name => "dir",
			ensure => "present"
	}
	
	a2mod { "enable authz_host module":
			name => "authz_host",
			ensure => "present"
	}
	
	a2mod { "enable rewrite module":
			name => "rewrite",
			ensure => "present"
	}	
	
	class {'apache::mod::php': } 
	package { "php5-mysql": 
		ensure => "installed",
	}
	package { "php5-xdebug": 
		ensure => "installed",
	}
	package { "php-apc": 
		ensure => "installed",
	}
	package { "php5-ldap": 
		ensure => "installed",
	}
	package { "php5-curl": 
		ensure => "installed",
	}
	package { "php5-imagick": 
		ensure => "installed",
	}
}

class {'host': }