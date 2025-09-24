# counting sort


```java

// complexity O(n+k) when array is [99,99,99,99,99..] so it will loop from 0->99 then at 99 it will loop n times till it put them all into the array 
// space O(k)
public class CountingSort {
    public static void countingSort(int[] arr) {
        if (arr.length == 0) return;

        // 1. Find the maximum value in the array
        int max = arr[0];
        for (int num : arr) {
            if (num > max) max = num;
        }

        // 2. Initialize count array
        int[] count = new int[max + 1]; // counts from 0 to max

        // 3. Count occurrences
        for (int num : arr) {
            count[num]++;
        }

        // 4. Reconstruct the sorted array
        int index = 0;
        for (int i = 0; i <= max; i++) {
            while (count[i] > 0) {
                arr[index++] = i;
                count[i]--;
            }
        }
    }
}


```