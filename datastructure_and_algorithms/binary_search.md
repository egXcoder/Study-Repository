# Binary Search


### Exact Match

```java
public int binarySearch(int[] arr, int target) {
    //search inclusive
    int left = 0;
    int right = arr.length - 1;
 
    //if left==right .. one element to be checked .. then loop again and check it
    while (left <= right) {
        int mid = left + (right - left) / 2;

        if (arr[mid] == target) {
            //exact match found
            return mid;
        }

        if (arr[mid] > target) {
            right = mid - 1; //go left and discard mid .. shrink
        } else {
            left = mid + 1; //go right and discard mid .. shrink
        }
    }

    // target is not in arr, but left is at the insertion point
    return left;
}
```

---

Example: Given a sorted array of distinct integers and a target value, return the index if the target is found. If not, return the index where it would be if it were inserted in order.

- Input: nums = [1,3,5,6], target = 5
- Output: 2

```java

class Solution {
    public int searchInsert(int[] nums, int target) {
        int left = 0;
        int right = nums.length -1;
        while(left<=right){
            int mid = (left + right) / 2;
      
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

Example: Find First Bad Version. given all the versions after a bad version are also bad.

- Input: n = 5, bad = 4
- Output: 4


Solution
- I will use Closed Interval [left,right] inclusive.. since its easier for me to understand
- if we reached left == right means we have one element to check [element] .. this should be the desired element
- At any point in the range [left, right]:
    - If isBadVersion(mid) is true, the first bad version is at mid or to the left
    - If isBadVersion(mid) is false, the first bad version is to the right

```java
public class Solution extends VersionControl {
    public int firstBadVersion(int n) {
        //closed interval
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


Example: Find Any Peak Element

- Input: nums = [1,2,1,3,5,6,4]
- Output: 5
- Explanation: Your function can return either index number 1 where the peak element is 2, or index number 5 where the peak element is 6.
- nums[i] != nums[i + 1] for all valid i.


Solution
- i will choose closed interval [left,right]
- when checking elements is only two elements [left,right] .. either pick left or pick right and terminate
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

            if(nums[mid]<nums[mid+1]){
                //slope increasing
                left = mid + 1; //go left and discard mid
            }else{
                right = mid; //go right and keep mid to check
            }
        }

        //when left == right then we have found a solution
        return left;
    }
}

```

---

Example: Find First and Last Position of Element in Sorted Array

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
            //upper middle is critical to avoid infinite loop and always try to get the mid as second element
            int mid = left + (right - left + 1) / 2; // upper mid

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



### Closed interval [left, right]

- Both ends are included in the search space.

- In a closed interval, both left and right are valid positions that could contain the answer.
So when left == right, there’s still one element to check — you must enter the loop. while(left<=right)

- after we check mid, we can go left or go right. since we already have checked mid we shouldnt try to check it again and because we are using [left,right] inclusive .. so if we put the mid in the left or the right this means we are going to check it again. which we dont want and this even can lead to when left = right, mid will be left and again we try to check mid again etc.. and you will have infinite loop .. so typically picking left or right should exclude middle
    - right = mid-1; //pick left side
    - left = mid + 1; //pick right side

```java
int left = 0;
int right = arr.length-1;

while(left<right){
    int mid = (right-left)/2 + left;

    if (target <= arr[mid]) { 
        right = mid; //pick left side
    } else {
        left = mid + 1; //pick right side
    }
}

return left;
```


### Half-open (or half-closed) interval [left, right)

Left end included, right end excluded.


- In a half-open interval, the right end is exclusive. Meaning arr[right] is not part of the search space. so When left == right, the loop stops → search space is empty. while(left<right)

- after we check mid, we can go left or go right. since we already have checked mid we shouldnt try to check it again and because we are using [left,right) right exclusive .. so we should make left [left,mid] .. this means check elements left -> mid-1
    - right = mid; //pick left side
    - left = mid + 1; //pick right side

```java
int left = 0;
int right = arr.length;

while(left<right){
    //do your logic
    int mid = (right-left)/2 + left;

    if (target <= arr[mid]) { 
        right = mid; //pick left side
    } else {
        left = mid + 1; //pick right side
    }
}

return left;

```
---

### My Picking

Personally i would pick closed interval [left,right] as i understand it clearer

---