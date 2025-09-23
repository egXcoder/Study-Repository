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