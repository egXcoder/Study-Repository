# Tree

### Declare Stack and Queue in java

```java

//stack (push,pop)
Deque<Integer> stack = new ArrayDeque<>();
stack.push(10);
stack.pop();
stack.peek();



//queue (offer,poll)
Deque<Integer> queue = new ArrayDeque<>();
queue.offer(1);
queue.poll();
queue.peek();
```

---

### Symmetric Tree

Given the root of a binary tree, check whether it is a mirror of itself (i.e., symmetric around its center).

- Input: root = [1,2,2,3,4,4,3]
- Output: true

```java
class Solution {
    public boolean isSymmetric(TreeNode root) {
        //early exit
        if(root == null){
            return  true;
        }

        return this.isSymmetric(root.left,root.right);
    }

    protected boolean isSymmetric(TreeNode left, TreeNode right){
        if(left == null && right == null){
            return true;
        }

        if(left == null || right == null){
            return false;
        }

        if(left.val != right.val){
            return false;
        }

        return isSymmetric(left.left,right.right) && isSymmetric(left.right,right.left);
    }
}

```

---

### BFS Template (level by level)

```java
Deque<TreeNode> queue = new ArrayDeque<>();
queue.offer(root);

while(!queue.isEmpty()){
    int size = queue.size();
    for(int i=0;i<size;i++){
        TreeNode node = queue.poll();        
        
        //do some logic with node

        if(node.left!=null){
            queue.offer(node.left);
        }
        
        if(node.right!=null){
            queue.offer(node.right);
        }
    }
}
```

--- 

### Convert Sorted Array to binary search tree.

- Input: nums = [-10,-3,0,5,9]
- Output: [0,-3,9,-10,null,5]
            0
      -3         9
   -10   null   5  

Standard Approach: Pick the middle element as the root 
- → left subarray becomes left subtree, 
- → right subarray becomes right subtree.
- This guarantees balance, because you split the array roughly in half at every level.

```java

class Solution {
    public TreeNode sortedArrayToBST(int[] nums) {
        return this.sort(nums,0,nums.length-1);
    }
    
    protected TreeNode sort(int[] nums, int left, int right){
        //base case is when left>= right.. 
        //for left > right is straight forward as below
        //for left==right, doing calculation below node.left = null and node.right=right and it will return with the mid node
        //so no need to write base case for left == right 
        if(left>right){
            return null;
        }

        int mid = (right-left)/2 + left;
        
        TreeNode node = new TreeNode(nums[mid]);
        node.left = sort(nums,left,mid-1);
        node.right = sort(nums,mid+1,right);
        return node;
    }
}
```
---