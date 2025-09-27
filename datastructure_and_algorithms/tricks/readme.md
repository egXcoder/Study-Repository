# Algorithms and DataStructure Tricks

- any number XOR with itself cancels out 5^6^5 = 6 .. you can refer back to problem 1


# Triangles

- valid triangle a,b,c is which a+b>c && a+c>b && b+c>a

- Area = .5 * a * H

- sin(x) = b / H , then H = b * sin(x)

- Area = .5 * a * b * sin(x) .. where x is the angle between a and b

- sin(x) = sqrt(1-cos^2(x)) .. since sin^2 + cos^2 = 1

- cos(x) = (a^2 + b^2 - c^2) / (2 * a * b) .. where is x is the angle between a and b

- Area Using Heron Formula If we have the tree lengths ..
s = (a + b + c) / 2
A = sqrt( s * (s-a) * (s-b) * (s-c) )









Problems:
1- Given an integer array, every element appears twice except for one. Find that single one.
Input: nums = [2,2,1]
Output: 1