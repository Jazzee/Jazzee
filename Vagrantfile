# -*- mode: ruby -*-
# vi: set ft=ruby :
VAGRANTFILE_API_VERSION = "2"
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "Jazzee_CentOS64-1"
  config.vm.box_url = "https://ucsf.box.com/shared/static/fmab3tdifq36iv3uz4zf.box"
  config.vm.hostname = "jazzeedev"
  config.vm.network :private_network, ip: "10.10.10.10"
  config.vm.synced_folder ".", "/vagrant", :nfs => true, id: "vagrant-root"
  config.vm.network :forwarded_port, guest: 8080, host: 8080
  config.vm.provision  :puppet do  |puppet|
    puppet.manifests_path = "dev/puppet/manifests"
    puppet.manifest_file = "web.pp"
    puppet.module_path  = "dev/puppet/modules"
  end
end