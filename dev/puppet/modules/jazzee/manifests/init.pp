class jazzee {
  include jazzee::data
  file {'/vagrant/etc/config.ini.php':
      ensure  => file,
      content => template('jazzee/jazzee.ini.erb'),
  }
  file {'/etc/php5/conf.d/jazzee-defaults.ini':
      ensure => present,
      owner => root, 
      group => root, 
      mode => 655,
      content => "post_max_size = 20M \nupload_max_filesize = 20M \nfile_uploads = On\ndisplay_errors = On\nhtml_errors = On\n",
      require => Class['apache::mod::php'],
      notify => Service['httpd'],
  }

  file {'/var/tmp/jazzee':
      ensure => directory,
      owner => root, 
      group => root, 
      mode => 777,
  }
}

class jazzee::data {
  $var_path = '/var/tmp/jazzee'
  $mail_override = 'noone@example.com'
  $db_host = '10.10.11.5'
  $db_name = 'webapp'
  $db_user = 'webapp'
  $db_password = 'webapp'
  
  $web_host = '10.10.11.4'
  
  $host_address = '10.10.11.1'
}