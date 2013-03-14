class jazzee::mail {
  case $operatingsystem {
    centos, redhat: { 
      $package      = ['sendmail', 'mutt']
      $service_name = 'sendmail'
    }
  }

  package { $package:
    ensure => latest,
  }

  service { $service_name:
    ensure    => running,
    require   => Package[$package]
  }
}