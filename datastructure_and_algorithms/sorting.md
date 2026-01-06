# Sorting

## Bubble Sort

```java

int[] arr = new int[]{1,5,11,5,6,7,8,12,2};

boolean swapped = true;
int n = arr.length;

//O(n^2)
while(swapped){
    swapped = false
    for(int i=1;i<n;i++){
        if(arr[i] < arr[i-1]){ //ascending
            int temp = arr[i];
            arr[i] = arr[i-1];
            arr[i-1] = temp;
            swapped = true;
        }
    }
    n--;
}

```

--- 

## Merge Sort

- If we have two sorted arrays [1,2,3] and [4,5,6] .. we can combine them to a sorted array using two pointers technique in O(n)
- the two arrays have to be sorted individually 

- for every step, you divide the array to left section and right section, sort left then sort right then merge them


```java


class MergeSort{

    public int[] performMergeSort(){
        int[] arr = new int[]{1,5,11,5,6,7,8,12,2};
        this.recursiveMerge(arr,0,arr.length-1);
        return arr;
    }

    //O(nlogn)
    private void recursiveMerge(int[] arr, int left, int right){
        //base case
        if(left>=right){
            return;
        }

        int mid = (right-left)/2 + left; //instead of (left+right)/2 as its safer for big left and right

        //sort left
        this.recursiveMerge(arr,left,mid);

        //sort right
        this.recursiveMerge(arr,mid+1,right);

        // Merge the two halves
        this.merge(arr, left, mid, right);
    }

    private void merge(int[] arr,int left, int mid, int right){
        int i = left;
        int j = mid+1;
        int k = 0;

        //build temp array sorted
        int[] temp = new int[mid-left+1 + right-mid];

        while(i<=mid && j<=right){
            if(arr[i]<arr[j]){
                temp[k++] = arr[i++];
            }else{
                temp[k++] = arr[j++];
            }
        }

        while(i<=mid){
            temp[k++] = arr[i++];
        }

        while(j<=right){
            temp[k++] = arr[j++];
        }

        //copy data from temp into main array
        i = left;
        k = 0;
        while(i<=right){
            arr[i++] = temp[k++];
        }
    }
}

```

---

## Heap Sort


[12 11 13 5 6 7]

        12
  11         13
5    6     7

## imagine array as binary tree

Idea is:
Maintain max heap which holds the maximum value of the arr

Given complete binary tree:
- parent elements indexes starts from 0 to n/2 - 1  inclusively
- for node i, left is 2i+1 and right is 2i+2

steps:
- build max heap by taking each parent and heapify arr to put this parent in its correct position
- for each step, take the max value and put it at the end of the array and heapify the array without the last element
- now maximum value will be at arr[0]
- so we will swap arr[0] with arr[i], then heapify i 


Complexity:
- Time Complexity O(nlogn)
- Memory Complexity 
    - Iterative heapify: O(1) auxiliary space.
    - Recursive heapify: O(log n) auxiliary space. like my example below

Tip:
- memory complexity measures maximum simultaneous extra memory, not the total number of allocations over time.
- Even if the loop runs n times, at any moment you are holding just 1 temp, not n temps.
Therefore, the extra memory is still O(1) (constant).
- But in recursive heapify, when call reaches the last stack, total memory consumed simulataneously is O(logn)



```java

int[] arr = {12, 11, 13, 5, 6, 7};
int n = arr.length;


//build max heap
//Complexity:
//O(n)
//mathematically its O(n/2 * logn), so it should be O(nlogn).. But in reality, building the heap is only O(n)
//suppose tree is 5 levels, - most of nodes is 5th level which we wont even traverse 
//4th level (which is 2nd largest level) can do only one swap
//3rd level is only 2 swaps max, etc.. 
// so in worst case you can approximate it to O(n) rather than O(nlogn)
for(int i=n/2-1;i>=0;i--){
    heapify(arr,n,i);
}

//reaching here, then arr is re-represented as maxHeap like 5,4,3,2,1
//so now extract the parent at index 0 and heapify again 
//Complexity:
//O(n log n)
for(int i=n-1;i>0;i--){
    //swap first node which is the maximum with i
    int temp = arr[0];
    arr[0] = arr[i];
    arr[i] = temp;

    //now heapify the array from index 0->i-1 inclusively
    heapify(arr,i,0); 
}


// heapify by always make sure parent is bigger than children
//if parent is less then swap
heapify(arr,n,i){
    int largest = i;
    int left = 2*i+1;
    int right = 2*i+2;

    if(left<n && arr[left]>arr[largest]){
        largest = left;
    }

    if(right<n && arr[right]>arr[largest]){
        largest = right;
    }

    //swap need to happen between parent and either left or right
    if(largest != i){
        int temp = arr[i];
        arr[i] = arr[largest];
        arr[largest] = temp;

        heapify(arr,n,largest);
    }
}

```

---

## Counting Sort

```java
// complexity O(n+k) 
// space O(k)
// given n is the number of elements
// given k is the maximum value

public class CountingSort {
    public static void countingSort(int[] arr) {
        if (arr.length == 0) return;

        // 1. Find the maximum value in the array O(n)
        int max = arr[0];
        for (int num : arr) {
            if (num > max) max = num;
        }

        // 2. Initialize count array
        int[] count = new int[max + 1]; // counts from 0 to max

        // 3. Count occurrences O(n)
        for (int num : arr) {
            count[num]++;
        }

        // 4. Reconstruct the sorted array O(k+n)
        int k = 0;
        for (int i = 0; i < count.length; i++) {
            while (count[i] > 0) {
                arr[k++] = i;
                count[i]--;
            }
        }
    }
}

```
---

## Bucket Sort

- it works when the input has some known structure, for example: Numbers are in a small range [0,1]
- its best when elements are evenly distributed across buckets, as if all elements lies on one bucket, you are not going to have any benefit


Why it works
- Each bucket has few elements, so sorting each bucket is cheap
- Total time is linear on average, because the array is evenly distributed across buckets


Example:
- nums = [0.42, 0.32, 0.23, 0.52, 0.25, 0.47, 0.51, 0.11, 0.67, 0.89]
- Distribute elements into buckets
    - bucket_index=int(num∗10) which stay in range [0,9] since all elements between 0 and 1
    - Bucket 1: [0.11]
    - Bucket 2: [0.23,0.25]
    - Bucket 3: [0.32]
    - Bucket 4: [0.42,0.47]
    - Bucket 5: [0.51,0.52]
    - Bucket 6: [0.67]
    - Bucket 8: [0.89]

- Sort Each Bucket Individually
    - Bucket 1: [0.11] → [0.11]
    - Bucket 2: [0.23,0.25] → [0.23,0.25]
    - Bucket 3: [0.32] → [0.32]
    - Bucket 4: [0.42,0.47] → [0.42,0.47]
    - Bucket 5: [0.51,0.52] → [0.51,0.52]
    - Bucket 6: [0.67] → [0.67]
    - Bucket 8: [0.89] → [0.89]

- Concatenate all buckets
    - sorted = [0.11, 0.23, 0.25, 0.32, 0.42, 0.47, 0.51, 0.52, 0.67, 0.89]


Sort: nums = [0.42, 0.32, 0.23, 0.52, 0.25, 0.47, 0.51]



```java

import java.util.*;

public class BucketSortExample {
    public static void bucketSort(double[] nums) {
        int n = nums.length;
        if (n <= 0) return;

        // Create n empty buckets
        List<Double>[] buckets = new List[n];
        for (int i = 0; i < n; i++) {
            buckets[i] = new ArrayList<>();
        }

        // Put array elements in different buckets (using scaling)
        // below code assume num is between 0 and 1.. thats why it scaled up by magnitude of 10
        for (double num : nums) {
            int idx = (int)(num * n); // num in [0,1), scale to bucket index
            if (idx == n) idx = n - 1; // edge case for num=1
            buckets[idx].add(num);
        }

        // Sort individual buckets
        for (List<Double> bucket : buckets) {
            Collections.sort(bucket);
        }

        // 4️⃣ Concatenate all buckets into original array
        int index = 0;
        for (List<Double> bucket : buckets) {
            for (double num : bucket) {
                nums[index++] = num;
            }
        }
    }
}
```