<?php


// idea: lets you split classes into two separate hierarchies which can be developed independently of each other.


//Family Of Remotes ----------Bridge -------- Family Of Devices


abstract class RemoteControl {
    protected Device $device;

    public function __construct(Device $device) {
        $this->device = $device;
    }

    abstract public function turnOn(): void;
    abstract public function turnOff(): void;
}

class BasicRemote extends RemoteControl {
    public function turnOn(): void {
        $this->device->turnOn();
    }

    public function turnOff(): void {
        $this->device->turnOff();
    }
}

class AdvancedRemote extends RemoteControl {
    public function turnOn(): void {
        $this->device->turnOn();
        $this->device->setVolume(50); // default volume
    }

    public function turnOff(): void {
        $this->device->turnOff();
    }

    public function mute(): void {
        $this->device->setVolume(0);
    }
}

interface Device {
    public function turnOn(): void;
    public function turnOff(): void;
    public function setVolume(int $percent): void;
}

class TV implements Device {
    public function turnOn(): void {
        echo "TV is ON\n";
    }
    public function turnOff(): void {
        echo "TV is OFF\n";
    }
    public function setVolume(int $percent): void {
        echo "TV volume set to {$percent}%\n";
    }
}

class Radio implements Device {
    public function turnOn(): void {
        echo "Radio is ON\n";
    }
    public function turnOff(): void {
        echo "Radio is OFF\n";
    }
    public function setVolume(int $percent): void {
        echo "Radio volume set to {$percent}%\n";
    }
}


// Basic Remote with TV
$remote = new BasicRemote(new TV());
$remote->turnOn();
$remote->turnOff();