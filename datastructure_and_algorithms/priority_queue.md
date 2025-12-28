# Priority Queue

### Heap
It’s a type of tree, in which we are always have the 
- ability to reach the maximum value of all the elements with O(1) complexity .. MAX HEAP
- ability to reach the minimum value of all elements with O(1) complexity .. Min Heap

### PriorityQueue

- is the data structure implementation of heap
- since it follow queue interface it uses offer/poll way 

### Min-Heap

smallest element is always at the root.

```text

       1
      / \
     3   2
    / \
   5   4

```

```java

PriorityQueue<Integer> minHeap = new PriorityQueue<>();
minHeap.offer(5);
minHeap.offer(1);
minHeap.offer(3);

System.out.println(minHeap.poll()); // 1 (smallest)
System.out.println(minHeap.peek()); // 3 (next smallest)

```

---

### Max-Heap

Property: largest element is always at the root.

```text
       10
      /  \
     7    9
    / \
   5   6

```

```java

PriorityQueue<Integer> maxHeap = new PriorityQueue<>(Collections.reverseOrder());
maxHeap.offer(5);
maxHeap.offer(1);
maxHeap.offer(3);

System.out.println(maxHeap.poll()); // 5 (largest)
System.out.println(maxHeap.peek()); // 3 (next largest)

```
---

### Complexity

| Operation             | Min/Max Heap Complexity |
| --------------------  | ----------------------- |
| add                   | O(log n)                |
| Remove                | O(log n)                |
| Peek                  | O(1)                    |
| Build heap One By One | O(nlogn)                |
| Build heap From Array | O(n)                    |

---

### Top K Element

Example: Given an integer array nums and an integer k, return the k most frequent elements. You may return the answer in any order.

- Input: nums = [1,1,1,2,2,3,3], k = 2
- Output: [1,2]


Solution:

```java

// HashMap + PriorityQueue
public class TopKFrequentPQ {
    public static int[] topKFrequent(int[] nums, int k) {
        // Count frequency
        Map<Integer, Integer> freqMap = new HashMap<>();
        for (int num : nums) {
            freqMap.put(num, freqMap.getOrDefault(num, 0) + 1);
        }

        // Create a min-heap based on frequency
        PriorityQueue<Integer> minHeap = new PriorityQueue<>(
            (a, b) -> freqMap.get(a) - freqMap.get(b)
        );

        // Add elements to the heap
        // O(n * log(k))
        for (int num : freqMap.keySet()) {
            minHeap.offer(num); // O(log(heapsize)), and since heapsize is not going to be more than k.then its O(logk)
            if (minHeap.size() > k) {
                minHeap.poll(); // O(log(heapsize)), and since heapsize is not going to be more than k.then its O(logk)
            }
        }

        // Extract elements from heap
        // O(k * log(k))
        int[] result = new int[k];
        for (int i = k - 1; i >= 0; i--) {
            result[i] = minHeap.poll();
        }

        return result;
    }
}


```


```java
// HashMap + Bucket Sort (O(n))
public class TopKFrequentBucketSort {
    public static int[] topKFrequent(int[] nums, int k) {
        // Count frequency of each number
        // 1 -> 3
        // 2 -> 2
        // 3 -> 2
        Map<Integer, Integer> freqMap = new HashMap<>();
        for (int num : nums) {
            freqMap.put(num, freqMap.getOrDefault(num, 0) + 1);
        }

        // to find top k frequency, we need to sort the values of hashmap then we get the top kth element
        // min possible value of duplicates = 1
        // max possible value of duplicates = n
        // by using above facts, we can improve sorting using buckets technique by creating n buckets
        // then traverse buckets starting from end till we fill the required top elements

        // Create buckets
        // 0 -> empty always
        // 1 -> empty 
        // 2 -> 2,3
        // 3 -> 1
        // 4 -> empty
        // 5 -> empty
        // 6 -> empty
        List<Integer>[] buckets = new List[nums.length + 1];
        for (int key : freqMap.keySet()) {
            int freq = freqMap.get(key);
            if (buckets[freq] == null) {
                buckets[freq] = new ArrayList<>();
            }
            buckets[freq].add(key);
        }

        int f = 0;
        int[] ans = new int[k];
        for(int i=buckets.length-1; i>=0 ; i--){
            if(buckets[i] == null){
                //empty
                continue;
            }

            for(int x : buckets[i]){
                ans[f++] = x;

                //if we have populated all the ans we need
                if(f>=ans.length){
                    return ans;
                }
            }
        }

        return ans;
    }
}
```