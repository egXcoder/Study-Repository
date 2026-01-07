# Heap


heap is binary tree used to find max value or min value quickly

- in max heap: always parent ≥ children
    - means root is the biggest element in the heap
    - level 1 elements are always bigger than level 2 elements etc..
    - level 2 is bigger than level 3

```text

[10, 8, 9, 3, 5, 6]

         10
      /        \
     8         9 
   /   \      /
3      5     6 

```

in any binary tree for index i
- left   = 2i + 1;
- right  = 2i + 2;
- parent = (i-1)/2;

```java

class MaxHeap{
    int[] arr;
    int size;


    public MaxHeap(int capacity){
        this.arr = new int[capacity];
        this.size = 0;
    }

    /**
    * take the input array and heapify it O(n) operation
    */
    public MaxHeap(int[] arr){
        //i did copy of then if i amended array inside heap structure that wont affect array on the outside world
        this.arr = Arrays.copyOf(arr, arr.length);
        this.size = arr.length;

        //consider last element is the right child ..
        //we need to reach to last element parent since this is the node we will start heapifying array at it
        //parent of any element = (i-1)/2
        //parent of last element = (n-2)/2 = .5n-1

        // for(int i=.5*size-1;i>=0;i--) //this is incorrect because .5*size will cause double conversion and this is not allowed
        
        for(int i=size/2-1;i>=0;i--){
            this.siftDown(i);
        }
    }

    private void siftDown(int i){
        int left = 2*i+1;
        int right = 2*i+2;

        int largest = i;

        if(left<size && this.arr[left]>this.arr[largest]){
            largest = left;
        }

        if(right<size && this.arr[right]>this.arr[largest]){
            largest = right;
        }

        if(largest != i){
            //left or right is the biggest
            //swap parent with left or right, then heapify again at node left or right
            int temp = this.arr[i];
            this.arr[i] = this.arr[largest];
            this.arr[largest] = temp;

            this.siftDown(largest);
        }
    }

    public void offer(int val){
        if (size == arr.length) {
            arr = Arrays.copyOf(arr, arr.length * 2);
        }

        arr[this.size] = val;
        this.size++; //has to be here, then on siftUp it would consider the last element which we have just inserted 
        this.siftUp(this.size);
    }

    private void siftUp(int i){
        if(i==0){
            return;
        }

        int parent = (i-1)/2;

        if(this.arr[i]>this.arr[parent]){
            int temp = this.arr[i];
            this.arr[i] = this.arr[parent];
            this.arr[parent] = temp;
            this.siftUp(parent);
        }
    }

    public int poll(){
        if (size == 0) {
            throw new NoSuchElementException("Heap is empty");
        }

        int val = this.arr[0];

        this.arr[0] = this.arr[size-1];
        this.arr[size-1] = 0; //then no memory leak
        this.size--; //it has to be here, so that on swiftDown it won't consider the last element anymore
        this.siftDown(0);

        return val;
    }
}

```

Tip: in production, iterative is more prefered over recursion as iterative is 0 stack growth and no risk of stack overflow