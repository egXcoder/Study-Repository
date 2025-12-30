# Sorting And Searching


Example: merge intervals .. Given an array of intervals where intervals[i] = [starti, endi], merge all overlapping intervals, and return an array of the non-overlapping intervals that cover all the intervals in the input.

- Input: intervals = [[1,3],[2,6],[8,10],[15,18]]
- Output: [[1,6],[8,10],[15,18]]

Solution:
- interval happens between any two arrays when [1,3] [2,6]
```text
    1 ________ 3
         2 _____________6


    1 ___________________________ 8
         2______________6

first.end >= second.start .. given first start has to be lower than second start

overlap will be = [first.start,max(first.end,second.end)]
```
- to compare intervals, you have to make sure they are sorted by their start times ascending

```java

class Solution {
    public int[][] merge(int[][] intervals) {
        if(intervals.length == 1){
            return intervals;
        }
        
        //sort array by its start time asending
        Arrays.sort(intervals,(a,b)->{
            return a[0] - b[0];
        });
        
        List<int[]> result = new ArrayList<>();

        int[] current = intervals[0];
        for(int j=1;j<intervals.length;j++){
            if(current[1]>=intervals[j][0]){
                //there is overlap
                current[1] = Math.max(current[1],intervals[j][1]);
            
                //we have reached end and overlap exist
                if(j == intervals.length-1){
                    result.add(current);
                }
            }
            else{
                //no overlap
                result.add(current);
                current = intervals[j];
                
                //we have reached end and no overlap
                if(j == intervals.length-1){
                    result.add(intervals[j]);
                }
            }
        }
        
        //convert list into array and give it the array structure it should fill the list into
        return result.toArray(new int[result.size()][]);
    }
}
```