class oracle {
  class {'base': , require => Class['jazzee']}
  class {'jazzee': databaseType=> 'oracle'}
}

class {'oracle': }
