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

### Keep Track of Smallest and Second Smallest

Example: Given an integer array nums, return true if there exists a triple of indices (i, j, k) such that i < j < k and nums[i] < nums[j] < nums[k]. If no such indices exists, return false.


- Input: nums = [6,7,5,8]
- Output: true .. as of [6,7,8]

```java

// [6,7,5] 8 ..
// if [6,7,5] have middle .. this means there was a smallest whatever it is
// if 8 is bigger than middle then we found a solution

// [1,7,5,4] .. minimum Middle = 4 because of 1,4 pair
    // int smallest = Integer.MAX_VALUE;
    // int middle = Integer.MAX_VALUE;
    // for(int x : nums){
    //     if(x <= smallest){
    //         smallest = x ;
    //     }
    //     else if(x <= middle){
    //         middle = x ;
    //     }
    // }

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


### Prefix Product

```java

//build
long[] prefix =  new long[n.length+1];
prefix[0] = 1
for i from 1 to n:
    prefix[i] = prefix[i - 1] + arr[i - 1]


// get product of sub array between left,right including
product(left, right) = prefix[right + 1] / prefix[left]

```

---

### Suffix Product (less common since in most cases prefix would do)

in suffix product: suffix[i] = nums[i] × nums[i+1] × ... × nums[n-1]

```java
// [1,2,3,4]
// [24,24,12,4,1] .. suffix
//build
long[] suffix =  new long[n.length+1];
suffix[n] = 1
for i from n-1 to 0:
    suffix[i] = suffix[i + 1] * arr[i]


// get product of sub array between left,right including
product(left, right) = suffix[left] / suffix[right + 1]

```

Example: Given an integer array nums, return an array answer such that answer[i] is equal to the product of all the elements of nums except nums[i].

- Input: nums = [1,2,3,4]
- Output: [24,12,8,6]
- You must write an algorithm that runs in O(n) time and without using the division operation.


```java

class Solution {
    public int[] productExceptSelf(int[] nums) {
        //build prefix product O(n)
        int[] prefix = new int[nums.length+1];
        prefix[0] = 1;
        for(int i=1;i<nums.length;i++){
            prefix[i] = prefix[i-1] * nums[i-1];
        }
        
        //build suffix product O(n)
        int[] suffix = new int[nums.length+1];
        suffix[nums.length] = 1;
        for(int i=nums.length-1;i>=0;i--){
            suffix[i] = suffix[i+1] * nums[i];
        }
        
        int[] output = new int[nums.length];
        for(int i=0;i<nums.length;i++){
            int productBefore = prefix[i];
            int productAfter = suffix[i+1];
            output[i] = productBefore * productAfter; 
        }
        
        return output;
    }
}

```


### Iterative with Boundary Tracking of 2d matrix

You maintain four variables: top, bottom, left, right .. and shrink them as you traverse each side.

You think of the matrix as concentric rectangles (“layers”) and traverse each layer in order: right → down → left → up.

```java

int left = 0, right = n-1, top = 0, bottom = m-1;
while(left <= right && top <= bottom){
    // Right
    for(int j=left; j<=right; j++) result.add(matrix[top][j]);
    top++;
    // Down
    for(int i=top; i<=bottom; i++) result.add(matrix[i][right]);
    right--;
    // Left
    for(int j=right; j>=left; j--) result.add(matrix[bottom][j]);
    bottom--;
    // Up
    for(int i=bottom; i>=top; i--) result.add(matrix[i][left]);
    left++;
}
```


Example: Given an m x n matrix, return all elements of the matrix in spiral order.

- Input: matrix = [
                   [1,2,3],
                   [4,5,6],
                   [7,8,9]
                ]
- Output: [1,2,3,6,9,8,7,4,5]

```java

public List<Integer> spiralOrder(int[][] matrix) {
    List<Integer> result = new ArrayList<>();
    if (matrix == null || matrix.length == 0) return result;

    int left = 0, right = matrix[0].length - 1;
    int top = 0, bottom = matrix.length - 1;

    while (left <= right && top <= bottom) {
        // Go Right
        for (int j = left; j <= right; j++) result.add(matrix[top][j]);
        top++;

        // Go Down
        for (int i = top; i <= bottom; i++) result.add(matrix[i][right]);
        right--;

        // Go Left
        if (top <= bottom) {
            for (int j = right; j >= left; j--) result.add(matrix[bottom][j]);
            bottom--;
        }

        // Go Up
        if (left <= right) {
            for (int i = bottom; i >= top; i--) result.add(matrix[i][left]);
            left++;
        }
    }

    return result;
}

```

---

### Hash map + pair-sum reduction (meet-in-the-middle)

Example: Given four integer arrays nums1, nums2, nums3, and nums4 all of length n, return the number of tuples (i, j, k, l) such that: 0 <= i, j, k, l < n and nums1[i] + nums2[j] + nums3[k] + nums4[l] == 0

- Input: nums1 = [1,2], nums2 = [-2,-1], nums3 = [-1,2], nums4 = [0,2]
- Output: 2
-Explanation:
    The two tuples are:
    1. (0, 0, 0, 1) -> nums1[0] + nums2[0] + nums3[0] + nums4[1] = 1 + (-2) + (-1) + 2 = 0
    2. (1, 1, 0, 0) -> nums1[1] + nums2[1] + nums3[0] + nums4[0] = 2 + (-1) + (-1) + 0 = 0


Solution:
- Compute sums of nums1 + nums2
- For nums3 + nums4, look for complement
- Uses 2 nested loops instead of 4
- Complexity is O(n^2) instead of O(n^4)
- if its 5 arrays, you would split it for 2 arrays and 3 arrays so it would be O(n^2) and O(n^3)


```java
class Solution {
    public int fourSumCount(int[] nums1, int[] nums2, int[] nums3, int[] nums4) {
        // Map to store sum of pairs from nums1 and nums2 -> frequency
        Map<Integer, Integer> sumCount = new HashMap<>();

        // Step 1: Compute all sums of nums1 and nums2
        for (int a : nums1) {
            for (int b : nums2) {
                int sum = a + b;
                sumCount.put(sum, sumCount.getOrDefault(sum, 0) + 1);
            }
        }

        int count = 0;

        // Step 2: For each sum of nums3 and nums4, find complement
        for (int c : nums3) {
            for (int d : nums4) {
                int target = -(c + d);
                count += sumCount.getOrDefault(target, 0);
            }
        }

        return count;
    }
}

```

--- 

### Greedy + Monotonic Reasoning (Two Pointers)

Example: Array represent vertical lines height, take two lines to form a container, such that the container contains the most water.

- Input: height = [1,8,6,2,5,4,8,3,7]
- Output: 49
- Explanation: max area of water falls between i=1 and i=8 = (8-1) * min(8,7) = 7 * 7 = 49

Solution:
- Goal: Maximize the area.
    - Area = (right - left) * min(height[left], height[right])

- Challenge: Area depends on two dimensions:
    - Width = (right - left)
    - Height = min(height[left], height[right])
    - Considering both together directly is hard.

- Strategy: Fix one dimension first.
    - Place left and right pointers at the start and end of the array.
    - This guarantees the width (right-left) is maximal.
    - Now the problem reduces to finding the maximum possible height for this width.

- Iteration:
    - Compute the area with current left and right.
    - Move the pointer corresponding to the shorter line, shrinking the width by one.
    - Recompute the maximum height for the new width.

- Repeat:
    - Continue shrinking the width while always trying to increase the height.
    - Track the largest area found along the way.

Steps:
- if we put left and right at begining and end... Area = (8-0) * min(1,7) = 8 * 1 = 8
- current minimum height is 1, more area means bigger minimum height
- keep right at 7 and see if we moved left pointer is that can increase the minimum height and hence bigger area?


```java
class Solution {
    public int maxArea(int[] height) {
        int left = 0;
        int right = height.length -1;
        int maxArea = 0;
        while(left<right){
            int area = (right - left) * Math.min(height[left],height[right]);
            
            if(area>maxArea){
                maxArea = area;
            }
            
            
            //potential to find bigger area only exist if we could find bigger minimum height
            if(height[left]<height[right]){
                left++;
            }else{
                right--;
            }
        }
        
        return maxArea;
    }
}

```
---


### In-place update using encoded states
Example: Game of Life .. Given the current state of the board, update the board to reflect its next state. that you do not need to return anything. 

The board is made up of an m x n grid of cells, where each cell live = 1 or dead = 0. Each cell interacts with its eight neighbors (horizontal, vertical, diagonal) using the following four rules:
- Any live cell with fewer than two live neighbors dies as if caused by under-population.
- Any live cell with two or three live neighbors lives on to the next generation.
- Any live cell with more than three live neighbors dies, as if by over-population.
- Any dead cell with exactly three live neighbors becomes a live cell, as if by reproduction.

- Input: board = [[0,1,0],
                [0,0,1],
                [1,1,1],
                [0,0,0]]
- Output:  [[0,0,0],
            [1,0,1],
            [0,1,1],
            [0,1,0]]


- Below Solution is using Space of O(m * n) because we need to capture new state for output and in same time keep copy of old state for checking...

- to reduce space usage we can store old state and new state in same cell using two bits instead of one bit 
    - x x = new_state old_state
    - board[ni][nj] & 1 .. comparing last bit .. LSB (least significant bit) .. for example 4 & 1 .. 10(0) & 00(1) = 0
    - board[i][j] >>= 1 .. shifting one bit to right .. 4>>1 .. 100 >> 1 = 010 = 2
    - (board[i][j] & 1) << 1; .. extract first bit and shift it to left
    - board[i][j] |= (board[i][j] & 1) << 1; write second bit on left given original second bit was 0
    - board[i][j] |= 2 (10); .. explicit write second bit with 1
    - board[i][j] &= 1 (01); .. explicit write second bit with 0
    - board[i][j] &= ~2 is anding with 2 complement .. 2 is 000010 while ~2 is 111101

```java
class Solution {
    public void gameOfLife(int[][] board) {
        int[][] output = new int[board.length][board[0].length];
        
        int[][] dirs = {
            {1,0},
            {-1,0},
            {0,1},
            {0,-1},
            {1,1},
            {1,-1},
            {-1,1},
            {-1,-1}
        };
        
        for(int i=0;i<board.length;i++){
            for(int j=0;j<board[0].length;j++){
                int neighbourLiveCount = 0;
            
                for(int[] dir : dirs){
                    int ni = i + dir[0];
                    int nj = j + dir[1];
                    
                    if(ni>=0 && nj>=0 && ni<board.length&&nj<board[0].length){
                        if(board[ni][nj] == 1){
                            neighbourLiveCount++;
                        }
                    }
                }
                
                output[i][j] = board[i][j];
                
                if(output[i][j] == 1 && neighbourLiveCount<2){
                    output[i][j] = 0;
                }
                else if(output[i][j] == 1 && neighbourLiveCount>3){
                    output[i][j] = 0;
                }
                else if(output[i][j] == 0 && neighbourLiveCount==3){
                    output[i][j] = 1;
                }
            }
        }
        
        
        //copy output into board
        for(int i=0;i<board.length;i++){
            for(int j=0;j<board[0].length;j++){
                board[i][j] = output[i][j];
            }
        }
    }
}

```


```java
class Solution {
    public void gameOfLife(int[][] board) {
        int[][] dirs = {
            {1,0},
            {-1,0},
            {0,1},
            {0,-1},
            {1,1},
            {1,-1},
            {-1,1},
            {-1,-1}
        };
        
        for(int i=0;i<board.length;i++){
            for(int j=0;j<board[0].length;j++){
                int neighbourLiveCount = 0;
            
                for(int[] dir : dirs){
                    int ni = i + dir[0];
                    int nj = j + dir[1];
                    
                    if(ni>=0 && nj>=0 && ni<board.length&&nj<board[0].length){
                        if((board[ni][nj] & 1) == 1){
                            neighbourLiveCount++;
                        }
                    }
                }
                
                //board[i][j] & 1 .. get old state
                //shift old state to bit to left
                //or second bit with board cell to write new state bit
                board[i][j] |= (board[i][j] & 1) << 1;
                
                if((board[i][j] & 1) == 1 && neighbourLiveCount<2){
                    board[i][j] &= 1; //write new state bit with 0
                }
                else if((board[i][j] & 1) == 1 && neighbourLiveCount>3){
                    board[i][j] &= 1; //write new state bit with 0
                }
                else if((board[i][j] & 1) == 0 && neighbourLiveCount==3){
                    board[i][j] |= 2; //write new state bit with 1
                }
            }
        }
        
        
        //prepare board to be finalized
        for(int i=0;i<board.length;i++){
            for(int j=0;j<board[0].length;j++){
                board[i][j] >>= 1; //shift one bit to right
            }
        }
    }
}

```

---

### Sequence Start Detection

Example: Given an unsorted array of integers nums, return the length of the longest consecutive elements sequence.

- Input: nums = [100,4,200,1,3,2]
- Output: 4
- Explanation: The longest consecutive elements sequence is [1, 2, 3, 4]. Therefore its length is 4.


```java

public int longestConsecutive(int[] nums) {
    Set<Integer> set = new HashSet<>();
    for (int num : nums) {
        set.add(num);
    }

    int longest = 0;

    for (int x : set) {
        // only start counting if this is the beginning
        if (!set.contains(num - 1)) {
            int count = 1;

            while (set.contains(x + 1)) {
                x++;
                count++;
            }

            longest = Math.max(longest, count);
        }
    }

    return longest;
}

```

---

### Detect Cycle (Floyd’s Tortoise and Hare (Cycle Detection))

Floyd’s cycle detection:
    - slow = tortoise (1 step)
    - fast = hare (2 steps)
    - if there is a cycle, its guranteed tortoise will catch hare at some point in the cycle
    - if no cycle..hare will beat the slow in the contest and it will end being slow!=fast
    - it can work for linkedlist and array to detect cycle

```java

do {
    slow = nums[slow]; //Tortoise (1 step)
    fast = nums[nums[fast]]; //hare (2 steps)
} while (slow != fast);

```

Find Beginning of cycle (part of floyd logic)
- if we reset slow at head and move tortoise and hare each one step we will reach cycle entry
- its logic is mathemtically proved by floyd

```java

// Phase 2: find cycle entry
slow = nums[0];
while (slow != fast) {
    slow = nums[slow];
    fast = nums[fast];
}

```


Example: Given an array of integers nums containing n + 1 integers where each integer is in the range [1, n] inclusive. There is only one repeated number in nums, return this repeated number.

- Input: nums = [1,3,4,2,2]
- Output: 2


```java
class Solution {
    public int findDuplicate(int[] nums) {
        int slow = nums[0];
        int fast = nums[0];

        // Phase 1: detect cycle
        do {
            slow = nums[slow];
            fast = nums[nums[fast]];
        } while (slow != fast);

        // Phase 2: find cycle entry
        slow = nums[0];
        while (slow != fast) {
            slow = nums[slow];
            fast = nums[fast];
        }

        return slow;
    }
}

```