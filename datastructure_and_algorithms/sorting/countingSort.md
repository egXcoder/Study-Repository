# counting sort


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