# Bucket Sort

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