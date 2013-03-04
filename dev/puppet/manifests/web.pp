class web {
  class {'base': , require => Class['jazzee']}
  class {'jazzee': databaseType=> 'mysql'}
}

class {'web': }
