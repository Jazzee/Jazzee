class jazzee::mail {
  case $operatingsystem {
    centos, redhat, oraclelinux: { 
      $package      = ['sendmail', 'mutt']
      $service_name = 'sendmail'
    }
    default: {fail("$operatingsystem is not supperted.")}
  }

  package { $package:
    ensure => latest,
  }

  service { $service_name:
    ensure    => running,
    require   => Package[$package]
  }
}