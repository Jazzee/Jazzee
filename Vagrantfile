# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "Jazzee_CentOS63"
  config.vm.host_name = "web"
  config.vm.network :hostonly, "10.10.10.10"
  config.vm.forward_port 8080, 8080
  config.vm.provision  :puppet do  |puppet|
    puppet.manifests_path = "dev/puppet/manifests"
    puppet.manifest_file = "web.pp"
    puppet.module_path  = "dev/puppet/modules"
  end
end