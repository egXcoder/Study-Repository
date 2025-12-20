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

Example: Find Intersection

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
    - a/b pointer moves respectively x1 + x3 + x2 = x2 + x3 + x1
    - they will meet at intersection when a and b equal each other

- suppose no intersection
    - a/b pointer moves respectively x1 + x2 = x2 + x1
    - they will meet when a/b both = null

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