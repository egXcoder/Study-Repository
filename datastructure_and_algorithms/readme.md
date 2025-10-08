# Datastructure And Algorithms




# Dynamic Programming

Dynamic Programming (DP) is a technique used to optimize recursion problems by remembering and reusing the results.

- Built on top of recursion
    - as recursion is used to solve complex problems by breaking them into smaller subproblems
    - but using recursion sometimes is redundant calculation because same subproblem may be recalculated
    - so dp technique is to remember the results then recursion is optimized (memoization or tabulation)
    - to remember the results, you have to extract the states which can be used as memo[state1][state2][state3]
    - if one of states is array or arraylist then dynamic programming is not applicable
    - you can think of states as the parameters to recursion
    - for time complexity.. recursion will be like O(2^n) while after memoization memo[m][n] .. then its O(m*n)


- Practical Steps
    - is BFS can solve it quicker then go for it without analyzing for dyanmic programming
    - is solveable with normal recursion? where recursion function have parameters and return result, so its not void
    - check time complexity: see for every node, how many nodes can be traversed. for example if node can traverse to 3 nodes then it is O(3^n)
    - see, what are the parameters to the recursion which are the states memo[i][j][k]
    - if one of parameters is dynamic datastructure like arraylist then it can't be optimized by dyanmic programming
    - check time complexity after memoization if memo[i][j][k] then it is O(m*n*k)


Tip: it can be approached from bottom up as well, using tabulation technique to build dp[state1][state2] and build dp[] till you reach your desired goal, but i like to think recursion as it is more obviouse

- Ex: Fibonacci Numbers 1,2,3,5,8,13,21,...

    - recursion solving:
    - f(x) = f(x-1) + f(x-2)
    ```java
        public int fib(int n) {
            if (n <= 1){
                return n;
            }

            return fib(n - 1) + fib(n - 2);
        }
    ```
    - every node can visit two nodes so its O(2^n)
    - if you notice recursion is done while have state (parameter) = n , then you can do memoization to save state
    ```java
        int[] memo;
        public int fib(int n) {
            if (n <= 1){
                return n;
            }

            if(memo[n]){
                return memo[n];
            }

            int solve = fib(n - 1) + fib(n - 2);

            memo[n] = solve;

            return memo[n];
        }
    ```