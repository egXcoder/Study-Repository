# Binary Search

is searching for a value existence within sorted array by discarding left and right at each step which gives O(log(n))

---

### Standard Finding Exact Match Or Insertion Point

```java
public int binarySearch(int[] arr, int target) {
    int left = 0; 
    int right = arr.length - 1;
 
    while (left <= right) {
        int mid = left + (right - left) / 2;

        if (arr[mid] == target) {
            //exact match found
            return mid;
        }

        if (target<arr[mid]) {
            right = mid - 1; //go left and discard mid .. shrink
        } else {
            left = mid + 1; //go right and discard mid .. shrink
        }
    }

    return left; //insertion point
}
```

Tip: insertion point refer to the index where you put the target into then shift indexes to right ... such as nums = [1,3,5]
- target = 1  .. insertion point = 0 .. new Array = [1,1,3,5]
- target = -1 .. insertion point = 0 .. new Array = [-1,1,3,5]
- target = 2  .. insertion point = 1 .. new Array = [1,2,3,5]
- target = 7  .. insertion point = 3 .. new Array = [1,3,5,7]

Tip: Above Implementation have the last insepcted interval will be one element since left<=right,so even if left==right it will still check such as nums = [3]
- target = 1 ... left = 0 valid insertion point
- target = 3 ... left = 0 exact match point
- target = 4 ... left = 1 valid insertion point

---


### Left-Most and Right-most match without considering insertion point if not exist

```java

protected int findIndex(int[] nums,int target,String dir){
    int left = 0;
    int right = nums.length -1;
    int ans = -1;

    while(left<=right){
        int mid = (right-left)/2 + left;

        if(target == nums[mid]){
            ans = mid;

            if(dir.equals("leftMost")){
                right = mid - 1;
            }else{
                left = mid + 1;
            }
        }
        else if(target>nums[mid]){
            left = mid + 1;
        }else{
            right = mid - 1;
        }
    }

    return ans;
}
```
---

### Find Left-Most Index

```java

public int binarySearch(int[] arr, int target) {
    int left = 0;
    int right = arr.length - 1;

    while (left < right) {
        int mid = left + (right - left) / 2;
        if (target <= arr[mid]) {
            right = mid; //go left and keep middle
        } else {
            left = mid + 1; //go right and discard mid
        }
    }

    //left is either the matched index or the insertion point
    return left;
}

```

Tip: i always like to use left=0, right= arr.length-1,i.e [left,right] as i can comprehend it better than right = arr.length i.e [left,right) exclusive

Tip: its preferable on this approach to use while(left < right) instead of <= .. and it means the last inspected interval will be only two elements such as [3,5].. and you either pick its left index or its right index whatever target is and terminate

Tip: since we want left-most, then always pick left side if (target==arr[mid])

Tip: normal middle always tends to be the left side [3,5] .. because of that its safe that 
- when picking left side you would set right = mid, which gives [3]
- when picking right side you would set left = mid+1, which gives [5]
- if you reversed them, this can lead to infinite loop

Tip: if target not the in array [3,5] .. it will give you insertion point
- target = 1 ... then left = 0
- target = 10 .. then left = 1
- target = 4 ... then left = 1

---

### Find Right-Most Index

```java

public int binarySearch(int[] arr, int target) {
    int left = 0;
    int right = arr.length - 1;

    while (left < right) {
        int mid = left + (right - left + 1) / 2; //upper middle .. critical otherwise infinite loop
        if (target >= arr[mid]) {
            left = mid; //go right and keep middle
        } else {
            right = mid - 1; //go left and discard mid
        }
    }

    //left is either the matched index or the insertion point
    return left;
}

```

Tip: since we want right-most, then always pick right side if (target==arr[mid])

Tip: if we didnt use upper middle, this will lead to infinite loop.. array [3,5]
- target = 1 .. infinite loop

---



Example: 

Given a sorted array of distinct integers and a target value, return the index if the target is found. If not, return the index where it would be if it were inserted in order.

- Input: nums = [1,3,5,6], target = 5
- Output: 2

```java
class Solution {
    public int searchInsert(int[] nums, int target) {
        int left = 0;
        int right = nums.length - 1;

        while(left<=right){
            int mid = (right-left)/2 + left;

            if(target==nums[mid]){
                return mid;
            }

            if(target>nums[mid]){
                left = mid + 1;
            }else{
                right = mid - 1;
            }
        }

        return left;
    }
}
```
---

Example: 

Find First Bad Version. given all the versions after a bad version are also bad.

- Input: n = 5, bad = 4
- Output: 4


```java
public class Solution extends VersionControl {
    public int firstBadVersion(int n) {
        int left = 1;
        int right = n;

        //if left == right dont loop again.
        while(left<right){
            int mid = (right-left) / 2 + left;

            if(this.isBadVersion(mid)){
                right = mid; //pick left side which contains [left,mid] so mid is potential solution
            }else{
                left = mid+1; // pick right side [mid+1,right] so discard mid
            }
        }

        //reaching here means left == right = the desired bad version
        return left;
    }
}

```
---

Example: 

Find First and Last Position of Element in Sorted Array

- Input: nums = [5,7,7,8,8,10], target = 8
- Output: [3,4]


```java

class Solution {
    public int[] searchRange(int[] nums, int target) {
        if(nums.length == 0){
            return new int[]{-1,-1};
        }

        int[] ans = new int[2];
        ans[0] = findLeftMost(nums,target);
        ans[1] = findRightMost(nums,target);
        return ans;
    }

    protected int findLeftMost(int[] nums,int target){
        //closed interval
        int left = 0;
        int right = nums.length-1;

        //if left == right then terminate, i.e reached one element then terminate
        while(left<right){
            int mid = (right-left)/2 + left;

            //since its left most, then if target=nums[mid] then go left
            if(target<=nums[mid]){
                right = mid; //go left and keep mid
            }else{
                left = mid+1; //go right and discard mid
            }
        }

        //if left == right, make sure its valid answer
        return nums[left] == target ? left : -1;
    }

    protected int findRightMost(int[] nums,int target){
        //closed interval
        int left = 0;
        int right = nums.length-1;

        //if left == right then terminate, i.e reached one element then terminate 
        while(left<right){
            int mid = left + (right - left + 1) / 2; //upper middle is critical to avoid infinite loop

            //since its rightmost, then if target=nums[mid] then go right and keep level
            if(target>=nums[mid]){
                left = mid; //go right and keep mid
            }else{
                right = mid-1; //go left and discard mid
            }
        }

        //if left == right, make sure its valid answer
        return nums[left] == target ? left : -1;
    }
}

```

---

Example: 

Find Any Peak Element

- Input: nums = [1,2,1,3,5,6,4]
- Output: 5
- Explanation: Your function can return either index number 1 where the peak element is 2, or index number 5 where the peak element is 6.
- nums[i] != nums[i + 1] for all valid i.


Solution
- if left == right, then dont loop again as we have found the peek element
- if slope is increasing.. mid < mid+1 .. then peek on the right side [mid+1,right]
- if slope is decreasing.. mid < mid+1 .. then peek on left side as mid potentially can be the peek [left,mid] 


```java

class Solution {
    public int findPeakElement(int[] nums) {
        if(nums.length == 1){
            return 0;
        }

        //closed interval
        int left = 0;
        int right = nums.length -1;
    
        while(left<right){
            int mid = (right-left)/2 + left;

            if(nums[mid+1]>nums[mid]){
                //slope increasing
                left = mid + 1; //go right and discard mid
            }else{
                right = mid; //go left and keep mid to check
            }
        }

        //when left == right then we have found a solution
        return left;
    }
}

```

Example: 

Search in Rotated Sorted Array .. There is an integer array nums sorted in ascending order (with distinct values).nums is possibly left rotated at an unknown index k such that the resulting array is [nums[k], nums[k+1], ..., nums[n-1], nums[0], nums[1], ..., nums[k-1]] .. nums [0,1,2,4,5,6,7] might be left rotated by 3 indices and become [4,5,6,7,0,1,2].


- Input: nums = [4,5,6,7,0,1,2], target = 0
- Output: 4

```java

class Solution {
    public int search(int[] nums, int target) {
        int left = 0;
        int right = nums.length -1;
        
        while(left<=right){
            int mid = (right-left)/2 +left;
            
            //found 
            if(nums[mid] == target){
                return mid;        
            }
            
            //if left half is ascending
            if(nums[mid]>=nums[left]){
                if(target>=nums[left] && target<nums[mid]){
                    //if target exist on the left
                    right = mid - 1;
                }else{
                    left = mid + 1;
                }
            }else{
                if(target>nums[mid] && target<=nums[right]){
                    //if target exist on the right
                    left = mid + 1;
                }else{
                    right = mid -1;
                }
            }
        }
        
        //couldnt find answer
        return -1;
    }
}

```
---