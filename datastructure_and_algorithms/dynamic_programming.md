# Dynamic Programming

dynamic programming is just optimized recursion

Complexity analysis for DP algorithms is very easy. Like with trees/graphs, we calculate each state only once. Therefore, if there are N possible states, and the work done at each state is F, then your time complexity will be O(N * F)



### Fibonachi Problem:

The Fibonacci numbers, commonly denoted F(n) form a sequence, called the Fibonacci sequence, such that each number is the sum of the two preceding ones, starting from 0 and 1. That is,

F(0) = 0, F(1) = 1
F(n) = F(n - 1) + F(n - 2), for n > 1.

- Input: n = 4
- Output: 3
- Explanation: F(4) = F(3) + F(2) = 2 + 1 = 3. as fibonacci series 0,1,2,3,5,8...


#### Solution Without DP:

```java
public int fibonacci(int n) {
    if (n == 0) {
        return 0;
    }
    if (n == 1) {
        return 1;
    }

    return fibonacci(n - 1) + fibonacci(n - 2);
}
```

```text

                           fib(5)
                          /      \
                     fib(4)        fib(3)
                     /    \        /    \
                fib(3)  fib(2)  fib(2)  fib(1)
                /   \    /   \   /   \
           fib(2) fib(1) f(1) f(0) f(1) f(0)
           /   \
      fib(1) fib(0)


```

| Level | Calls |
| ----- | ----- |
| 0     | 1     |
| 1     | 2     |
| 2     | 4     |
| 3     | 8     |
| ...   | ...   |
| n     | ~2ⁿ   |

then complexity is O(2^n)


#### Solution With Memoization (top-down)

```java

Map<Integer, Integer> memo = new HashMap<>();

public int fibonacci(int n) {
    if (n == 0) {
        return 0;
    }
    if (n == 1) {
        return 1;
    }

    if (memo.containsKey(n)) {
        return memo.get(n);
    }

    memo.put(n, fibonacci(n - 1) + fibonacci(n - 2));

    return memo.get(n);
}

```

This improves our time complexity to O(n) and space is O(n)


#### Solution With bottom-Up Tabulation

```java

public int fibonacci(int n) {
    int[] arr = new int[n + 1];
    // base case - the second Fibonacci number is 1
    arr[1] = 1;
    for (int i = 2; i <= n; i++) {
        arr[i] = arr[i - 1] + arr[i - 2];
    }
    
    return arr[n];
}
```

its same time and space complexity of memoization however there are pros and cons to both

- Usually, a bottom-up implementation is faster. This is because iteration has less overhead than recursion, although this is less impactful if your language implements tail recursion.

- a top-down approach is usually easier to write. With recursion, the order that we visit states does not matter. With iteration, if we have a multidimensional problem, it can sometimes be difficult figuring out the correct configuration of your for loops.

---

### DP vs Divide and Conquer

Divide & Conquer:
- Break problem into smaller subproblems (independent subproblems,i.e no-overlap)
- Solve each separately
- Combine results
- Complexity: Time: O(n(log(n))) .. Space: recursion stack

Dynamic Programming:
- Break problem into smaller subproblems (overlapping subproblems)
- Solve each subproblem once
- Store result and reuse it
- Complexity = O(n*F) .. given n is the number of nodes and f is the work per each node

Dynamic Programming is NOT an alternative to Divide & Conquer. It is an OPTIMIZATION of Divide & Conquer when overlap exists

Fibonacci starts as Divide & Conquer → becomes Dynamic Programming when we cache.

Practically, divide-and-conquer doesnt recompute because subproblems don’t repeat, while dynamic programming trades memory for speed by caching repeated subproblem results.

Tip: Independent subproblems (no-overlapping) = the same subproblem will not appear again later
---

### Example: Jump Game

You are given an integer array nums. You are initially positioned at the array's first index, and each element in the array represents your maximum jump length at that position.

Return true if you can reach the last index, or false otherwise.

Input: nums = [2,3,1,1,4]
Output: true
Explanation: Jump 1 step from index 0 to 1, then 3 steps to the last index.


#### DP Solution:

```java

Boolean[] memo;

boolean canJump(int[] nums) {
    memo = new Boolean[nums.length];
    return dfs(0, nums);
}

boolean dfs(int i, int[] nums) {
    //base case if reached end of array
    if (i >= nums.length - 1){
        return true;
    }

    //if cached
    if (memo[i] != null){
        return memo[i];
    }

    int furthest = Math.min(nums.length - 1, i + nums[i]);
    for (int j = i + 1; j <= furthest; j++) {
        if (dfs(j, nums)) {
            return memo[i] = true;
        }
    }

    return memo[i] = false;
}

```

Complexity is O(n^2). because in the worst case scenario we will have every element of array can jump to every other element such as this array [5,4,3,2,1,0]. so we have n elements and for each element it can jump n times.

```text
Jump Game Decision Tree (nums = [2,3,1,1,4])

                          index0
                   /                 \
                index1              index2
           /       \      \             /       
       index2     index3  index4      index3
      /     \      |          |         |
  index3  index4  index4     Goal   index4
    |       |      |                    |
 index4   Goal   Goal                  Goal
   |
  Goal

```

#### Greedy Solution O(n)

its idea is very simple.. iterate through the array and see is any of the index cannot be reached

```java

boolean canJump(int[] nums) {
    int farthest = 0;

    for (int i = 0; i < nums.length; i++) {
        if (i > farthest){
            return false; // cannot reach this index
        }

        farthest = Math.max(farthest, i + nums[i]);
    }

    return true;
}
```

---

### example: Unique Paths

There is a robot on an m x n grid. The robot is initially located at the top-left corner (i.e., grid[0][0]). The robot tries to move to the bottom-right corner (i.e., grid[m - 1][n - 1]). The robot can only move either down or right at any point in time.

Given the two integers m and n, return the number of possible unique paths that the robot can take to reach the bottom-right corner.

- Input: m = 3, n = 7
- Output: 28

```java

class Solution {
    int m;
    int n;
    int[][] memo;
    
    public int uniquePaths(int m, int n) {
        this.m = m;
        this.n = n;
        memo = new int[m][n];
        for(int i=0;i<m;i++){
            Arrays.fill(memo[i],-1);
        }
        
        return this.dfs(0,0);
    }
    
    private int dfs(int i, int j){
        if(i == m-1 && j==n-1){
            //if reached
            return 1;
        }
        
        if(i>m-1 || j>n-1){
            //if out of board
            return 0;
        }
        
        if(memo[i][j] != -1){
            return memo[i][j];
        }
        
        int count = 0;
        
        //count of unique paths if we go right
        count += this.dfs(i,j+1);
        
        
        //count of unique paths if we go bottom
        count += this.dfs(i+1,j);
        
        return memo[i][j] = count;
    }
}
```