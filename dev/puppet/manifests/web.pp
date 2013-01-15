class web {
  class {'jazzee': }
  case $operatingsystem {
    centos, redhat: { 
      $enhancers = [ "screen", "emacs", "vim-enhanced" ]
      $firewall = ["iptables", "ip6tables"]
    }
  }
  
  package { $enhancers: ensure => "latest" }

  service {$firewall: ensure => stopped }
}

class {'web': }
