<?php 

// if i have 50 api and i want to hit all of them.. 
// given that each api take 20 seconds to reply back.. how can i hit them with php?


$urls = []; // fill with 50 urls
for ($i = 1; $i <= 50; $i++) {
    $urls[] = "https://example.com/api/endpoint/{$i}";
}



// [1] ❌ Blocking api calls (Worse thing you can do)
foreach($urls as $url){
    file_get_contents($url);
}


// [2] ✅ use php curl multi handler functionality

$multi = curl_multi_init();
$handles = [];

// create individual easy handles
foreach ($urls as $i => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);            // total timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);      // connect timeout
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // set headers, auth etc if needed:
    // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    curl_multi_add_handle($multi, $ch);
    $handles[(int)$ch] = ['handle' => $ch, 'url' => $url];
}

// run the multi handle
$active = null;
do {
    $mrc = curl_multi_exec($multi, $active); //run the subconnections in event loop
    // Wait for activity on any curl-connection (efficient)
    if ($mrc === CURLM_OK) {
        curl_multi_select($multi, 1.0); //Block until reading or writing is possible for any cURL multi handle connection
    } else {
        break;
    }
} while ($active);


// reaching here, means all urls are visited and finished

// collect results
$results = [];
foreach ($handles as $key => $info) {
    $ch = $info['handle'];
    $content = curl_multi_getcontent($ch);
    $errNo = curl_errno($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    $results[] = [
        'url' => $info['url'],
        'http_code' => $httpCode,
        'error_no' => $errNo,
        'error' => $err,
        'body' => $content,
    ];

    curl_multi_remove_handle($multi, $ch);
    curl_close($ch);
}

curl_multi_close($multi);



// [3] ✅ do theading manually for every url

function fetchBlocking($url) {
    // simple curl blocking request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    return ['url' => $url, 'code' => $code, 'err' => $err, 'body_len' => $body ? strlen($body) : 0];
}

$maxWorkers = 10;   // concurrency level
$children = [];

for ($i = 0; $i < count($urls); $i++) {
    // wait for a free slot
    while (count($children) >= $maxWorkers) {
        $pid = pcntl_wait($status, WNOHANG);
        if ($pid > 0) {
            unset($children[$pid]);
        } else {
            usleep(100000); // 100 ms
        }
    }

    $pid = pcntl_fork();
    if ($pid == -1) {
        // fork failed
        die("Could not fork\n");
    } elseif ($pid) {
        // parent
        $children[$pid] = $urls[$i];
    } else {
        // child
        $res = fetchBlocking($urls[$i]);
        // Print result to stdout (parent can capture or just let it out)
        echo json_encode($res) . PHP_EOL;
        exit(0);
    }
}

// parent to wait for all children to finish
while (count($children) > 0) {
    $pid = pcntl_wait($status);
    if ($pid > 0) {
        unset($children[$pid]);
    }
}
