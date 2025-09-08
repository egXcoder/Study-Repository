<?php

// ❌ Bad
interface StorageInterface {
    public function saveToDisk(string $file);
    public function saveToS3(string $file);
    public function saveToFtp(string $file);
}

// ✅ Good
interface LocalStorageInterface {
    public function saveToDisk(string $file);
}

interface CloudStorageInterface {
    public function saveToCloud(string $file);
}

class S3Storage implements CloudStorageInterface {
    public function saveToCloud(string $file) {
        Storage::disk('s3')->put($file, file_get_contents($file));
    }
}

class LocalStorage implements LocalStorageInterface {
    public function saveToDisk(string $file) {
        Storage::disk('local')->put($file, file_get_contents($file));
    }
}

// 👉 In Laravel, contracts should stay small and specific.
// If a service only needs SmsNotificationInterface, it doesn’t get polluted with unused Email or Push methods.