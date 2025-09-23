
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

        int mid = left - (left-right)/2; //instead of (left+right)/2 as its safer for big left and right

        //sort left
        this.recursiveMerge(arr,left,mid);

        //sort right
        this.recursiveMerge(arr,mid+1,right);

        // Merge the two halves
        this.merge(arr, left, mid, right);
    }

    private void merge(int[] arr,int left, int mid, int right){
        int n1 = mid - left + 1;
        int n2 = right - mid;

        // Temporary arrays
        int[] L = new int[n1];
        int[] R = new int[n2];

        // Copy data
        for (int i = 0; i < n1; i++){
            L[i] = arr[left + i];
        }

        for (int j = 0; j < n2; j++){
            R[j] = arr[mid + 1 + j];
        }

        // Merge temporary arrays back into arr[left..right] .. two pointers technique
        int i = 0;
        int j = 0;
        int k = left;

        while (i < n1 && j < n2) {
            if (L[i] <= R[j]) {   // ascending order
                arr[k++] = L[i++];
            } else {
                arr[k++] = R[j++];
            }
        }

        // Copy remaining elements of L[]
        while (i < n1) {
            arr[k++] = L[i++];
        }

        // Copy remaining elements of R[]
        while (j < n2) {
            arr[k++] = R[j++];
        }
    }
}

// 1,5,11,5,6,7,8,12,2



```