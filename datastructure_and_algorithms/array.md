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