# parted command

`parted /dev/sda` .. parted on a specific disk

`print` .. show partitions on this disk

`print free` .. show partitions + free space

`mkpart mydata ext4 32.2GB 100%` .. make partition with name = mydata and fs = ext4 from 32.2GB and take rest 100% space

`rm 2` .. remove partition 2

Tip:
- partitions and parted is different from file system.. parted is used to manage partition sizes.. but not really file system
    - making a new partition ext4 .. is just some hint added to partition meta dat that partition is intended to be ext4
    - print command will show you file system hinted in partition meta data
    - when you run mkfs command it will put a hint into partition meta data that partition is ext4 now.

## File System

`mkfs.ext4 /dev/sda2` .. format partition sda2 to be ext4

## Mount and Unmount

`mount /dev/sda2 /mnt/data` .. mount partition to a directory
`umount /dev/sda2` or `umount /mnt/data` .. unmount parition

`df -h` .. test partition is mounted correctly

Mount Permanently:
- `blkid` Get the UUID of parition
- `nano /etc/fstab` edit file system table (fstab)
- `UUID=abcd-1234   /mnt/data   ext4   defaults   0   2` .. add this entry to fstab
- `mount -a` .. if no errors then fstab is okay

    - UUID=abcd-1234 → which partition
    - /mnt/data → where to mount
    - ext4 → filesystem type
    - defaults → mount options
    - 0 → skip dump
    - 2 → run filesystem check after boot

## Resize Partition

`resizepart <partition_number> <end_position>`

Q: can i shrink partition while its being used?

yes, you can do whatever you want .. no restrictions or stopping just alerts .. resizing partition dont care what is the file system doing. is partition mounted? what is the size of file system now, is it big is it small? resizing partition dont care. linux assume you know what you are doing.. this can cause data loss if you are not careful .. you always need to make sure file system allows you especially on shrinking before messing with parititions


## Resize File System

`resize2fs <partition_number> [new_size]`

Q: can i shrink fs while its being mounted?

no, you can't .. fs is more strict and does care about file system .. to shrink fs it has to be not mounted anywhere



## incrase disk space
`lsblk` .. the disk is 100 GB but the root partition (sda2) is still 50 GB.

sda     8:0   0   100G  0 disk
└─sda2  8:2   0    50G  0 part / 

Parted Command
    - `parted /dev/sda` .. open parted software on required disk 
    - `resizepart 2 100%` .. resize partition 2 to take remaining disk space

Resize File System
    - `resize2fs /dev/sda1` .. make file system to take partition space by default

Q: what if we didnt grow file system?
 - in this situation block will be bigger than file system, and you cant put data into the extra space unless file system grows

Q: can i grow partition while its being used?
 - yes, you can. it will just give you alert but you can..

Q: what is difference between partition and file system
 - you can think of it partition is the box and fs is another box inside .. fs is the actual box you can put data into it.. ideally parition must 100% match file system


## Reduce Space

Resize File System
    - `resize2fs /dev/sda2 50G`
 
Parted Command
    - `parted /dev/sda` .. open parted software on required disk
    - `resizepart 2 50GB` .. resize partition 2 to be 50GB

Q: what if i tried to shrink block directly?
 - this can cause irrecoverable corruption and permanent data loss as you will have file system thinking its still big while block is already shrinked, so file system will try to read and write on places that doesnt exist

Q: okay, but shouldnt linux stop that?
 - linux doesnt stop you, you have to know what you are doing

Q: if i shrink file system while data is using all space, would that may cause data loss?
 - yes, of course it will