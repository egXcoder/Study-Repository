# Array

### Subarrays/substrings Vs subsequences Vs and subsets

Given n = [1, 2, 3, 4, 5]

- SubArray [2, 3, 4]
- SubSequence [1, 3, 4]
- Subset [1, 4, 3]

---


### Two Pointers [first at start and second at end]

```java

function fn(arr):
    left = 0
    right = arr.length - 1

    while left < right:
        Do some logic here depending on the problem
        Do some more logic here to decide on one of the following:
            1. left++
            2. right--
            3. Both left++ and right--

```

Example: is a string is a palindrome? such as "ahmha" or "ahha"

---


### Two Pointers [on two different arrays in same time arr1, arr2]

```java

function fn(arr1, arr2):
    i = j = 0
    while i < arr1.length AND j < arr2.length:
        Do some logic here depending on the problem
        Do some more logic here to decide on one of the following:
            1. i++
            2. j++
            3. Both i++ and j++

    // Step 4: make sure both iterables are exhausted
    // Note that only one of these loops would run
    while i < arr1.length:
        Do some logic here depending on the problem
        i++

    while j < arr2.length:
        Do some logic here depending on the problem
        j++

```

Example: Given two sorted integer arrays arr1 and arr2, return a new array that combines both of them and is also sorted.

---

### Sliding Window (Variable Window)


```java

function fn(arr):
    left = 0
    for (int right = 0; right < arr.length; right++):
        Do some logic to "add" element at arr[right] to window

        while WINDOW_IS_INVALID:
            Do some logic to "remove" element at arr[left] from window
            left++

        Do some logic to update the answer

```

Example: find the length of the longest subarray whose sum is less than or equal to k

---

### Sliding Window (Fixed Window)


```java

function fn(arr, k):
    curr = some data to track the window

    // build the first window
    for (int i = 0; i < k; i++)
        Do something with curr or other variables to build first window

    ans = answer variable, probably equal to curr here depending on the problem
    for (int i = k; i < arr.length; i++)
        Add arr[i] to window
        Remove arr[i - k] from window
        Update ans

    return ans

```

Example: find the sum of the subarray with the largest sum whose length is k

---


### Prefix Sum

Tips:
- its better to have prefix array as `long` to handle if numbers sum gets big
- its better to follow the structure of prefix = new long[n.length + 1] and prefix[0] = 0

```java

//build
// nums = [ 5 , 2 , 1 , 6 , 3  ,  8 ]
// psum = [ 0 , 5 , 7 , 8 , 14 , 17, 24 ]
long[] prefix =  new long[n.length+1];
prefix[0] = 0
for i from 1 to n:
    prefix[i] = prefix[i - 1] + arr[i - 1]


// usage to get sum of sub array between left,right including
sum(left, right) = prefix[right + 1] - prefix[left]

```

Example: find the number of ways to split the array into two parts so that the first section has a sum greater than or equal to the sum of the second section. The second section should have at least one number.

Solution
```java

class Solution {
    public int waysToSplitArray(int[] nums) {
        //build prefix
        long[] prefix = new long[nums.length+1];
        for(int i=1;i<=nums.length;i++){
            prefix[i] = prefix[i-1] + nums[i-1];
        }


        //loop and check on each position if left section >= right section
        int noOfWays = 0;
        for(int i=0;i<nums.length-1;i++){
            int leftSectionLeft = 0;
            int leftSectionRight = i;
            long leftSum = prefix[leftSectionRight + 1] - prefix[leftSectionLeft];


            int rightSectionLeft = i+1;
            int rightSectionRight = nums.length -1;
            long rightSum = prefix[rightSectionRight + 1] - prefix[rightSectionLeft];

            if(leftSum>=rightSum){
                noOfWays++;
            }
        }

        return noOfWays;
    }
}

```

--- 

### in-place index marking (Flip Sign To Mark Presence)

Example: Given an array of integers from 1->n, find all elements that appear twice.

- Input:  [4,3,2,7,8,2,3,1]
- Output: [2,3]

```java

for (int i=0;i<n;i++):
    val = abs(arr[i])
    if arr[val - 1] < 0:
        # already negative → duplicate found
        output.append(val)
    else:
        # flip sign to mark presence
        arr[val - 1] = -arr[val - 1]

```

---

### Character Counting Signature

Given String = "eat" .. you can represent this word as character counting signature so that "eat","ate","eta","tae" has same signature

```java
String s = "eat";

int[] ascii = new int[26];
for(char c:s.toCharArray()){
    ascii[c - 'a']++;
}

StringBuilder sb = new StringBuilder();
for(int v:ascii){
    sb.append(v + '_');
}

String signature = sb.toString(); //signature here



//Another Way Faster but limited
// Faster:
//  as no object creation is needed
//  no need to convert from integer to character as ascii is char[] already so converting it to string is straight forward
// Limited:
    // hard to debug if you try to print signature as it will show non-human readable characters rather than counting frequency
    // can't have count of duplicate characters more than 65k as one chacater is maximum of 65,535 as of 16 bit 
    // If somehow a single letter occurred > 65535 times: char would overflow modulo 65536, causing two different counts to map to the same char which can cause collision
char[] ascii = new char[26];

for(char c:s.toCharArray()){
    ascii[c - 'a']++;
}

String signature = String.valueOf(ascii);

```

Example: Given an array of strings strs, group the anagrams together. You can return the answer in any order.

- Input: strs = ["eat","tea","tan","ate","nat","bat"]
- Output: [["bat"],["nat","tan"],["ate","eat","tea"]]

Solution
```java

class Solution {
    public List<List<String>> groupAnagrams(String[] strs) {
        if(strs == null || strs.length == 0){
            return new ArrayList<>();
        }

        HashMap<String,ArrayList<String>> map = new HashMap<>();
        
        for(String s : strs){
            char[] ascii = new char[26];

            for(char c:s.toCharArray()){
                ascii[c - 'a']++;
            }
            
            String key = String.valueOf(ascii);
            
            map.computeIfAbsent(key, k -> new ArrayList<>()).add(s);
        }
        
        return new ArrayList<>(map.values());
    }
}

```


---

### Explore Around Center

Example: Given a string s, return the longest palindromic substring in s.

- Input: s = "babad"
- Output: "bab"

```java

class Solution {
    public String longestPalindrome(String s) {
        if(s == null || s.isEmpty()){
            return s;
        }
        
        int maxLength = 0;
        int start = 0;
        int end = 0;
        for(int i=0;i<s.length();i++){
            //odd length palindrome
            int len1 = exploreAroundCenter(s,i,i);
        
            //even length palindrome
            int len2 = exploreAroundCenter(s,i,i+1);
        
            int len = Math.max(len1,len2);
            
            if(len > maxLength){
                maxLength = len;
                
                start = i - ((len-1)/2);
                end = i + (len/2);
            }
        }
        
        return s.substring(start,end+1);
    }
    
    protected int exploreAroundCenter(String s,int left, int right){
        while(left>=0 && right<s.length() && s.charAt(left) == s.charAt(right)){
            left--;
            right++;
        }        
        
        return right - left - 1;
    }
}

```

### State Maintenance while looping

Example: Given an integer array nums, return true if there exists a triple of indices (i, j, k) such that i < j < k and nums[i] < nums[j] < nums[k]. If no such indices exists, return false.


- Input: nums = [6,7,5,8]
- Output: true .. as of [6,7,8]

```java

// [6,7,5] 8 ..
// if [6,7,5] have middle .. this means there was a smallest whatever it is
// if 8 is bigger than middle then we found a solution

class Solution {
    public boolean increasingTriplet(int[] nums) {
        int smallest = Integer.MAX_VALUE;
        int middle = Integer.MAX_VALUE;

        for(int x : nums){
            //if number more than middle, then we found a solution
            if(x > middle){
                return true;
            }

            //maintain middle value and always try to minimize it
            if(x <= smallest){
                smallest = x ;
            }
            else if(x <= middle){
                middle = x ;
            }
        }

        return false;
    }
}

```