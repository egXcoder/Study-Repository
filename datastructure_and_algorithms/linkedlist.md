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