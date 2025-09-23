# Heap Sort = O(n log n)


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
- now maximum value will be at arr[0]
- so we will swap arr[0] with arr[i], then heapify i 


Complexity:
- Time Complexity O(nlogn)
- Memory Complexity 
    - Iterative heapify: O(1) auxiliary space.
    - Recursive heapify: O(log n) auxiliary space. like my example below

Notice:
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