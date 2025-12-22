# LinkedList


### grade-school addition

This is useful, if you are going to have very two long numbers that dont fit in normal long variable and you want to add them

Example: You are given two non-empty linked lists representing two non-negative integers.

- Input: l1 = [2,4,3], l2 = [5,6,4]
- Output: [7,0,8]
- Explanation: 342 + 465 = 807.

Tip: module always give you number between 0 and 10 ... 2%10 = 2 , 12%10 = 2
Tip: 2%10 gives 2 .. not 0

```java
public ListNode addTwoNumbers(ListNode l1, ListNode l2) {
    ListNode dummy = new ListNode(0);
    ListNode curr = dummy;
    int carry = 0;

    while (l1 != null || l2 != null || carry != 0) {
        int x = (l1 != null) ? l1.val : 0;
        int y = (l2 != null) ? l2.val : 0;

        int sum = x + y + carry;
        carry = sum / 10;

        curr.next = new ListNode(sum % 10);
        curr = curr.next;

        if (l1 != null) l1 = l1.next;
        if (l2 != null) l2 = l2.next;
    }

    return dummy.next;
}

```

---

### Split into two chains while traversing once

Example: Given the head of a singly linked list, group all the nodes with odd indices together followed by the nodes with even indices, and return the reordered list.

- Input: head = [1,2,3,4,5]
- Output: [1,3,5,2,4]

```java

public ListNode oddEvenList(ListNode head) {
    if (head == null) return null;

    ListNode odd = head;
    ListNode even = head.next;
    ListNode evenHead = even;

    while (even != null && even.next != null) {
        odd.next = even.next;
        odd = odd.next;

        even.next = odd.next;
        even = even.next;
    }

    odd.next = evenHead;
    return head;
}

```

---

###  Intersection of Two Linked Lists

Example: Find Intersection (meeting point either value if intersection or null if no intersection)

- Input: listA = [4,1,8,4,5], listB = [5,6,1,8,4,5]
- Output: Intersected at '8'

Idea:
- If you let two pointers traverse both lists, switching to the other list’s head when reaching the end, they will:
    - either meet at the intersection node
    - or both reach null at the same time (no intersection)

- Suppose Intersection:
    - a1 -> a2 ->       c1 -> c2 -> c3
    - b1 -> b2 -> b3 -> c1 -> c2 -> c3
    - x1 represents a1,a2
    - x2 represents b1,b2,b3
    - x3 represents c1,c2,c3
    - they will meet at intersection as a/b pointer moves respectively x1 + x3 + x2 = x2 + x3 + x1

- suppose no intersection
    - they will meet when a/b both = null as x1 + x2 = x2 + x1 

```java

public ListNode getIntersectionNode(ListNode headA, ListNode headB) {
    if (headA == null || headB == null) return null;

    ListNode pA = headA;
    ListNode pB = headB;

    while (pA != pB) {
        pA = (pA == null) ? headB : pA.next;
        pB = (pB == null) ? headA : pB.next;
    }

    return pA;
}

```

--- 

### Merge k Sorted Lists

Example: You are given an array of k linked-lists lists, each linked-list is sorted in ascending order. Merge all the linked-lists into one sorted linked-list and return it.

- Input: lists = [[1,4,5],[1,3,4],[2,6]]
- Output: [1,1,2,3,4,4,5,6]

Solution:

Using Divide and Conquer technique
- Merge list 0 & 1
- Merge list 2 & 3
- Merge results
- Repeat until one list remains


Tip: we use formula int mid = (right - left)/2 + left; instead of mid = (left+right)/2 to be safe if left + right exceeds int byte size 

Tip: complexity below is O(k * log(n)) 
- given n is the list length
- give k is the count of all nodes
- we traverse it recursively having log(n) level and each level have work of k

```java
class Solution {
    public ListNode mergeKLists(ListNode[] lists) {
        //early exit
        if(lists == null || lists.length == 0){
            return null;
        }

        //early exit
        if(lists.length == 1){
            return lists[0];
        }

        return mergeK(lists,0,lists.length-1);
    }

    protected ListNode mergeK(ListNode[] lists,int left, int right){
        if(left == right){
            return lists[left];
        }

        int mid = (right - left)/2 + left;

        return mergeBoth(mergeK(lists,left,mid),mergeK(lists,mid+1,right));
    }

    protected ListNode mergeBoth(ListNode node1, ListNode node2){
        ListNode dummy = new ListNode(0);
        ListNode curr = dummy;
        
        while(node1!=null && node2!=null){
            if(node1.val<node2.val){
                curr.next = node1;
                node1 = node1.next;
            }else{
                curr.next = node2;
                node2 = node2.next;
            }

            curr = curr.next;
        }

        //if node1 and node2 were different in lengths
        if(node1!=null){
            curr.next = node1;
        }
        
        if(node2 !=null){
            curr.next = node2;
        }

        return dummy.next;
    }
}

```

---

### Find Middle Of LinkedList

Tip: slow and fast start from same position this would get the right middle given even array length
Tip: typically you would need left middle on most of your alogirthms

```java

private ListNode getMid(ListNode head) {
    ListNode slow = head;
    ListNode fast = head.next;

    while (fast != null && fast.next != null) {
        slow = slow.next;
        fast = fast.next.next;
    }

    return slow;
}

```

---

### Sort LinkedList

Example: Given the head of a linked list, return the list after sorting it in ascending order.

- Input: head = [4,2,1,3]
- Output: [1,2,3,4]

Solution: merge Sort O(nlog(n))

```java

class Solution {
    public ListNode sortList(ListNode head) {
        if(head == null || head.next == null){
            return head;
        }
        
        ListNode mid = getMid(head);
    
        //right half
        ListNode rightHalf = mid.next;
    
        //left half
        mid.next = null;
        ListNode leftHalf = head;
        
        
        leftHalf = sortList(head);
        rightHalf = sortList(rightHalf);
        
        return merge(leftHalf,rightHalf);
    }
    
    protected ListNode getMid(ListNode node){
        ListNode slow = node;
        ListNode fast = node.next;
        
        while(fast!=null && fast.next!=null){
            slow = slow.next;
            fast = fast.next.next;
        }
        
        return slow;
    }
    
    protected ListNode merge(ListNode left, ListNode right){
        ListNode dummy= new ListNode(0);
        ListNode curr = dummy;
        
        while(left!=null && right!=null){
            if(left.val<right.val){
                curr.next = left;
                left = left.next;
            }else{
                curr.next = right;
                right = right.next;
            }
            
            curr = curr.next;
        }
        
        if(left!=null){
            curr.next = left;
        }
        
        if(right!=null){
            curr.next = right;
        }
        
        return dummy.next;
    }
}
```

---

### Floyd Detect Cycle

- slow is turtoise and fast is hare
- turtoise and hare start from same position as they are racing
- iterate till hare reaches the end of race
- if turtoise can catch hare then there is a cycle
- you can find cycle start by making another turtoise start from beginning and existing turtoise move as well.. position where they meet is the cycle start


```java
ListNode slow = head;
ListNode fast = head;

while (fast != null && fast.next != null) {
    slow = slow.next;
    fast = fast.next.next;

    if (slow == fast) {
        // cycle detected
        // Step 2: find cycle start
        ListNode p1 = head;
        ListNode p2 = slow;

        while (p1 != p2) {
            p1 = p1.next;
            p2 = p2.next;
        }
        return p1;
    }
}

return null; //no cycle detected
```