class web {
  class {'jazzee': }
  case $operatingsystem {
    centos, redhat: { 
      $enhancers = [ "screen", "emacs", "vim-enhanced" ]
      $firewall = ["iptables", "ip6tables"]
      file { "/var/log/httpd":
        ensure => directory,
        group => 'apache',
        mode => '0755',
        require => [Group['apache']]
      }
      group { 'apache':
        ensure => present,
        require => Class['jazzee']
      }
      user { 'vagrant':
        ensure  => present,
        groups => ["apache"],
        require => [Group['apache']]
      }
    }
  }

  package { $enhancers: ensure => "latest" }

  service {$firewall: ensure => stopped }
}

class {'web': }
