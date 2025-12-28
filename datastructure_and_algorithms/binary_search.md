# Binary Search

### Closed interval [left, right]

- Both ends are included in the search space.

- In a closed interval, both left and right are valid positions that could contain the answer.
So when left == right, there’s still one element to check — you must enter the loop. while(left<=right)

- after we check mid, we can go left or go right. since we already have checked mid we shouldnt try to check it again and because we are using [left,right] inclusive .. so if we put the mid in the left or the right this means we are going to check it again. and this even can lead to when left = right, mid will be left and again we try to check mid again etc.. and you will have infinite loop .. so typically picking left or right should exclude middle
    - right = mid-1; //pick left side
    - left = mid + 1; //pick right side

```java
int left = 0;
int right = arr.length-1;

while(left<=right){
    int mid = (right-left)/2 + left;

    if (target <= arr[mid]) { 
        right = mid - 1; //pick left side
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

### Find Left-Most Position vs Right-Most Position

```java
//find first position
public int binarySearch(int[] arr, int target) {
    int left = 0;
    int right = arr.length -1;
    while (left <= right) {
        int mid = (right-left)/2 + left;

        //we want first position, so if we found element then pick left side and finally return left;
        if (target <= arr[mid]) { 
            right = mid - 1; //pick left side
        } else {
            left = mid + 1;
        }
    }

    //left is first element or the left-most insertion point
    return left;
}

//find last position
public int binarySearch(int[] arr, int target) {
    int left = 0;
    int right = arr.length -1;
    while (left <= right) {
        int mid = (right-left)/2 + left;

        //we want last position, then if we found element then pick right side and finally return right
        if (target >= arr[mid]) { 
            left = mid + 1; //pick right side
        } else {
            right = mid - 1;
        }
    }

    //right is last element or the right-most insertion point
    return right;
}

```