<?php



//Element: is the object which is going to be visited
//Visitor: is guest expert who have knowledge how to do different operations for each class of family

//without visitor
interface Shape {
    public function draw();
}

class Circle implements Shape {
    public function draw() {
        echo "Drawing Circle\n";
    }
}

class Rectangle implements Shape {
    public function draw() {
        echo "Drawing Rectangle\n";
    }
}

//problem here: 
// - if we wan to add a new method which is different from shape to another such as AreaCalculator
// you have to amend all shape classes which is too much work and violating (OCP)
// - keep adding operations which is different from shape to another, you will end up with big classes and much effort implementation them all even if you dont need them


// visitor typically solve the problem of keep adding operation to a set of shapes, so if you are not going to keep adding operations frequently
// then no need for visitor


// with visitor

//shapes are elements
interface Shape {
    public function accept(ShapeVisitor $visitor);
}

class Circle implements Shape {
    public $radius;

    public function __construct($radius) {
        $this->radius = $radius;
    }

    public function accept(ShapeVisitor $visitor) {
        $visitor->visitCircle($this);
    }
}

class Rectangle implements Shape {
    public $width;
    public $height;

    public function __construct($w, $h) {
        $this->width = $w;
        $this->height = $h;
    }

    public function accept(ShapeVisitor $visitor) {
        $visitor->visitRectangle($this);
    }
}


interface ShapeVisitor {
    public function visitCircle(Circle $circle);
    public function visitRectangle(Rectangle $rectangle);
}

class ShapeDrawer implements ShapeVisitor {
    public function visitCircle(Circle $circle) {
        echo "Draw Circle";
    }

    public function visitRectangle(Rectangle $rectangle) {
        echo "Draw Rectangle";
    }
}

//now if we will add a new operation, we add a new class like AreaCalculator which respects OCP and more flexible



// Q: when would i use visitor pattern?
// - when you have family of classes, and you will frequently add operations (which is different from class to another)
// notice: if the added operations is common between family, then you would just use inheritance


// Q: how can i imagine visitor?
// you have a construction site, working site, finished site (element) .. and you have visitor (engineer) .. each visitor knows what he will do
// - there is a visitor (architect) who will draw the architect for construction site, monitor progress of working site, receive finished job from finished site
// - there is a visitor (worker) who will get sand and iron into construction site, monitor suppliers for working site, take his money on finished job site
// sites just need to accept them, once accepted the visitor will do his job


//Q: visitor pattern look similar to command pattern?
// - yes and no..
// - command is to encapsulate request focuses on when/how to execute it (queue it,undo it, log it,etc..)
// - visitor is to encapsulate set of operations that apply to different classes (each visitor knows how to visit construction site and how to visit working site and how to visit finished site,etc..)
// - Visitor is like a guest expert who comes and applies knowledge (e.g., “I know how to calculate area for Circle and Rectangle”) to different hosts.