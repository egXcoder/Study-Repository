<?php


// idea: defines the skeleton (template) of an algorithm in a base class and let subclasses override certain steps of the algorithm without changing its overall structure.

// template method hooks is like afterFechingData, 
// methods can be overrided in subclasses to do extra within the algorithm or it can be empty as it doesnt affect the core algorithm

// Imagine you have to generate reports, but the output format differs (PDF, CSV, etc.).
// The overall algorithm for generating a report is always the same:
// - Fetch data
// - Format data
// - Export the report

// But each step can vary depending on the report type.

abstract class ReportGenerator {
    // Template method (final to prevent overriding)
    public final function generateReport(): void {
        $data = $this->fetchData();
        $formatted = $this->formatData($data);
        $this->export($formatted);
    }

    // Steps that subclasses must implement
    abstract protected function fetchData(): array;
    abstract protected function formatData(array $data): string;
    abstract protected function export(string $formatted): void;
}

class PDFReport extends ReportGenerator {
    protected function fetchData(): array {
        return ['sales' => 1200, 'profit' => 300];
    }

    protected function formatData(array $data): string {
        return "PDF FORMAT: Sales={$data['sales']}, Profit={$data['profit']}";
    }

    protected function export(string $formatted): void {
        echo "Exporting PDF Report: {$formatted}\n";
    }
}

class CSVReport extends ReportGenerator {
    protected function fetchData(): array {
        return ['sales' => 1200, 'profit' => 300];
    }

    protected function formatData(array $data): string {
        return "Sales,Profit\n{$data['sales']},{$data['profit']}";
    }

    protected function export(string $formatted): void {
        echo "Exporting CSV Report:\n{$formatted}\n";
    }
}

//client code
$pdf = new PDFReport();
$pdf->generateReport();

$csv = new CSVReport();
$csv->generateReport();



//Laravel uses similar concepts in Illuminate\Console\Command where the handle() is your custom step, but the framework controls the template lifecycle.
abstract class Command {
    public function run() {
        $this->prepare();
        $this->handle(); // you implement this
        $this->finish();
    }

    abstract public function handle();
}

abstract class TestCase {
    public function run() {
        $this->setUp();
        $this->testSomething(); // subclass defines
        $this->tearDown();
    }
}



//Q: i feel the template method is widely used, i feel like most of the inheritance would be template method dp?
// Many cases of inheritance look like or actually are Template Method in disguise.
// Inheritance naturally leads to reuse of algorithm skeletons.
// Whenever a base class defines an algorithm with steps left to subclasses, that’s essentially the Template Method pattern.
// ✅ So yes — your feeling is correct: a large portion of framework-level inheritance = Template Method.
//⚠️ Caveat
// If you have too many Template Methods within the class, you get rigid class hierarchies.
// Sometimes it’s cleaner to use Strategy or Hooks/Events instead, because they let you swap behavior at runtime, not just via subclassing.