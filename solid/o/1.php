<?php

// ❌ Bad (violates OCP — every new format requires modifying this class)
class ReportExporter
{
    public function export($data, string $type)
    {
        if ($type === 'csv') {
            return $this->toCsv($data);
        } elseif ($type === 'excel') {
            return $this->toExcel($data);
        }
        // Tomorrow: PDF? JSON? Keep adding...
    }

    private function toCsv($data) { /* ... */ }
    private function toExcel($data) { /* ... */ }
}


// ✅ Good (follows OCP — open for extension, closed for modification):
interface Exporter {
    public function export($data): string;
}

class CsvExporter implements Exporter {
    public function export($data): string {
        // Convert to CSV
    }
}

class ExcelExporter implements Exporter {
    public function export($data): string {
        // Convert to Excel
    }
}

class ReportService
{
    public function __construct(private Exporter $exporter) {}

    public function generate($data): string {
        return $this->exporter->export($data);
    }
}