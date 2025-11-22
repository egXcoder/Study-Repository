# Android Studio 

Android Studio is the official Integrated Development Environment (IDE)

## Versions
Uses animal-themed codenames for major releases, starting around 2023‑2024. Each major stable version gets a codename like canary, Chipmunk, Dolphin, Electric Eel, Flamingo, Giraffe, and now Otter 2025.


## Android SDK (Software Development Kit)

is a set of tools and libraries that developers use to build Android applications.

- Libraries
    - Pre-written code that helps you do common tasks:
    - Display UI (View, Button, TextView)
    - Access hardware (camera, sensors, GPS)
    - Handle storage and networking

- Tools
    - SDK Manager → downloads updates for SDK components, platform tools, and emulator images.
    - ADB (Android Debug Bridge) → lets you control devices and emulators from your computer.
    - Fastboot → for flashing devices or installing system images.

- Platform Tools
    - Necessary files to build apps for a specific Android version (API level).
    - Each Android version (like Android 13, API 33) has its own platform libraries.

- Build Tools
    - Convert your code into an APK or AAB (Android app package).

- Emulator Images
    - Virtual Android devices to test your app without a real phone.

Tip: Android Studio uses the SDK to compile and build apps.

Tip: Android Studio bundles the SDK, but you can also install or update it separately.

Tip: Without the SDK, you cannot run or test an Android app, even if you have Android Studio.


## Intent

An Intent is a message that Android uses to request an action.

Intent = “I want something done.”
- Explicit → “Do it in this activity/class.”
- Implicit → “Do it in anyone that can handle it.”

#### Explicit Intent

```java
Intent intent = new Intent(MainActivity.this, SecondActivity.class);
// Optional: pass data to SecondActivity
intent.putExtra("message", "Hello from MainActivity");
// Start the activity
startActivity(intent);
```

#### Implicit intent

```java
Intent intent = new Intent(Intent.ACTION_VIEW);
intent.setData(Uri.parse("https://example.com"));
startActivity(intent);
```

Here, you don’t know which app will open the URL. Any app that declares it can handle VIEW actions for URLs can respond.


#### Intent Filter

Intent filter = “I am an activity that can do certain things.”

You list actions (what you do), categories (context, like DEFAULT or BROWSABLE), and data (what kind of content you accept).


```xml
<activity
    android:name=".MainActivity"
    android:exported="true"
    android:theme="@style/Theme.YellowLedger">
    <intent-filter>
        <action android:name="android.intent.action.MAIN" />
        <category android:name="android.intent.category.LAUNCHER" />
    </intent-filter>
</activity>
```
- this action Means this is the entry point of the app.
- this category Means this Activity should appear in the launcher (app icon).

```xml
<activity android:name=".PdfViewerActivity" android:exported="true">
    <intent-filter>
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <data android:mimeType="application/pdf" />
    </intent-filter>
</activity>
```
This tells Android:
- “I can view (action.VIEW) files with application/pdf MIME type.”
- Now, when a user clicks a PDF in another app, your app can appear as an option to open it.



## Manifest

The AndroidManifest.xml is a configuration file that every Android app must have. It tells the Android system about your app, including:

- App metadata — name, icon, theme, version, etc.
- Components — activities, services, broadcast receivers, content providers.
- Permissions — what the app can access (internet, camera, location).
- Intent filters — how other apps or the system can start your app components.
- Minimum Android version your app supports.

Think of it as a configuration used by Android OS to know how to interact with your app.

```xml

#### Example from android studio

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android" xmlns:tools="http://schemas.android.com/tools">
    <application
        android:allowBackup="false"
        android:dataExtractionRules="@xml/data_extraction_rules"
        android:fullBackupContent="@xml/backup_rules"
        android:icon="@mipmap/ic_launcher"
        android:label="@string/app_name"
        android:roundIcon="@mipmap/ic_launcher_round"
        android:supportsRtl="false"
        android:theme="@style/Theme.YellowLedger">
        <activity
            android:name=".MainActivity"
            android:exported="true"
            android:theme="@style/Theme.YellowLedger">
            <intent-filter>
                <action android:name="android.intent.action.MAIN" />

                <category android:name="android.intent.category.LAUNCHER" />
            </intent-filter>
        </activity>
    </application>
</manifest>
```
#### Manifest Activity Export
- android:exported="true" → The activity can be launched by other apps or the system directly
- android:exported="false" → The activity can only be used internally within your app.

Tip: if exported true then this activity must have intent filter then outside can reach your activity

#### Manifest Namespace
- xmlns:android="..." → namespace declaration (to avoid conflicts)
- xmlns:tools="..." → namespace declaration (to avoid conflicts)

Tip: android namespace is targetting the android OS to read from it, while tools is targeting android studio design to show data while designing


## Paths

- app/ → main app module (usually all your code and resources go here)
- gradle/ → Gradle wrapper files
- build.gradle (project level)
- settings.gradle
- gradlew, gradlew.bat → scripts to build from terminal


### app path
app/
 ├─ src/
 │   ├─ main/
 │   │   ├─ java/           → your Java or Kotlin source code
 │   │   │   └─ com/example/myapp/ 
 │   │   ├─ res/            → resources (layouts, drawables, strings, etc.)
 │   │   │   ├─ layout/      → XML layout files
 │   │   │   ├─ drawable/    → images, icons
 │   │   │   ├─ values/      → colors.xml, strings.xml, dimens.xml
 │   │   │   └─ mipmap/      → app icons
 │   │   ├─ AndroidManifest.xml → app configuration, activities, permissions
 │   │   └─ assets/         → raw files you want to ship (optional)
 │   └─ test/                → unit tests
 └─ build.gradle             → app/module-specific build settings
