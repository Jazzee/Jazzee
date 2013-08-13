# Installing the virtualbox guest additions
yum -y install kernel-devel kernel-uek-devel
VBOX_VERSION=$(cat /home/veewee/.vbox_version)
cd /tmp
mount -o loop /home/veewee/VBoxGuestAdditions_$VBOX_VERSION.iso /mnt
sh /mnt/VBoxLinuxAdditions.run
umount /mnt
#rm -rf /home/veewee/VBoxGuestAdditions_*.iso

