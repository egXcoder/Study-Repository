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

### DFS

Tip: In many problems, the type of DFS used doesn't even matter, it's just important that all nodes are visited. Knowing the differences between the three types of DFS is mostly good for trivia.

Tip: if we traverse a valid binary search tree in-order, it should give sorted nums asc 

```text
      A
     / \
    B   C
   / \
  D   E

pre-order: A → B → D → E → C
in-order: D → B → E → A → C
post-order: D → E → B → C → A
```

```java

public void preorderDfs(Node node) {
    if (node == null) {
        return;
    }

    //logic in node is here
    System.out.println(node.val);

    preorderDfs(node.left);
    preorderDfs(node.right);
}

// D → B → E → A → C
public void inorderDfs(Node node) {
    if (node == null) {
        return;
    }

    preorderDfs(node.left);

    //logic in node is here
    System.out.println(node.val);

    preorderDfs(node.right);
}

public void postorderDfs(Node node) {
    if (node == null) {
        return;
    }


    preorderDfs(node.left);
    preorderDfs(node.right);
    
    //logic in node is here
    System.out.println(node.val);
}

//Iterative Approach
Deque<TreeNode> stack = new ArrayDeque<>();
stack.push(root);

while (!stack.empty()) {
    TreeNode node = stack.pop();

    if (node.left != null) {
        stack.push(node.left);
    }
    if (node.right != null) {
        stack.push(node.right);
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

### Construct Binary Tree from Preorder and Inorder Traversal
- Input: preorder = [3,9,20,15,7], inorder = [9,3,15,20,7]
- Output: [3,9,20,null,null,15,7]

Solution:
[pre order array]
- take pre-order array [3,9,20,15,7] .. node -> left -> right
- 3 is always going to be root
- one possibility of left/right can be [9],[20,15,7]
- if we can know length of left branch and length of right branch. then we can solve it using divide and conquer

[in-order array]
- [9,3,15,20,7] its always left -> node -> right 
- you can see node is always in the middle between left and right. 
- so if we locate index of 3 for example. we would know left length is [9] and right length is [15,20,7]

```java
class Solution {
    private Map<Integer, Integer> inMap = new HashMap<>();

    public TreeNode buildTree(int[] preorder, int[] inorder) {
        for (int i = 0; i < inorder.length; i++) {
            inMap.put(inorder[i], i);
        }

        return build(preorder, 0, preorder.length - 1, 0, inorder.length-1);
    }

    private TreeNode build(int[] preorder, int p_left, int p_right, int i_left, int i_right) {
        if(p_left>p_right || i_left>i_right){
            return null;
        }
        
        //first from pre_order array is the root
        TreeNode root = new TreeNode(preorder[p_left]);
        
        int index = inMap.get(root.val);
        
        int leftSize = index - i_left;
        
        root.left = build(preorder , p_left+1 , p_left+leftSize , i_left, i_left+leftSize);
        
        root.right = build(preorder , p_left+leftSize+1 , p_right , index+1 , i_right);
        
        return root;
    }
}

```

