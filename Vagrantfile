# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.hostname = "schezzo.vagrant.net"
  config.vm.box = "ptwlt/centos7-php7-mysql57-x86_64.box"

  #pwapi
  config.vm.define :pwapi do |pwapi|

    pwapi.vm.network :forwarded_port, guest: 22, host: 3334, id: "ssh", auto_correct: false
    pwapi.vm.network :private_network, ip: "172.16.22.20"
    # Mac
    pwapi.vm.synced_folder Dir::pwd, "/var/www/schezzo", :owner => 'vagrant', :group => 'vagrant', mount_options: ["dmode=775", "fmode=775"]

    pwapi.vm.provider "virtualbox" do |v|
      v.customize ["modifyvm", :id, "--natdnsproxy1", "off"]
      v.customize ["modifyvm", :id, "--natdnshostresolver1", "off"]

      v.customize ["modifyvm", :id, "--memory", 1024]
      v.gui = false
    end

  end

  config.vm.provision "ansible" do |ansible|
    ansible.limit = "default"
    ansible.inventory_path = "provision/hosts"
    ansible.playbook = "provision/playbook.yml"
  end

end