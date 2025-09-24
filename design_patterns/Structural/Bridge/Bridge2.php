<?php


abstract class Report {
    protected ReportOutput $output;

    public function __construct(ReportOutput $output) {
        $this->output = $output;
    }

    abstract public function generate(): string;

    public function deliver(): void {
        $content = $this->generate();
        $this->output->send($content);
    }
}

class PdfReport extends Report {
    public function generate(): string {
        return "[PDF Report] Sales data here...";
    }
}

class CsvReport extends Report {
    public function generate(): string {
        return "id,name,amount\n1,Ahmed,100\n2,Ibrahim,200";
    }
}

class JsonReport extends Report {
    public function generate(): string {
        return json_encode([
            ['id' => 1, 'name' => 'Ahmed', 'amount' => 100],
            ['id' => 2, 'name' => 'Ibrahim', 'amount' => 200]
        ]);
    }
}

interface ReportOutput {
    public function send(string $content): void;
}

class DownloadOutput implements ReportOutput {
    public function send(string $content): void {
        echo "Downloading report...\n";
        echo $content . "\n";
    }
}

class EmailOutput implements ReportOutput {
    public function send(string $content): void {
        echo "Sending report via email...\n";
        // Example: mail() or Laravel Mail
        echo $content . "\n";
    }
}

class CloudStorageOutput implements ReportOutput {
    public function send(string $content): void {
        echo "Uploading report to cloud...\n";
        echo $content . "\n";
    }
}

// Generate PDF and download
$report = new PdfReport(new DownloadOutput());
$report->deliver();


//Q: Bridge and Strategy Look similar, what is the difference?
// difference is the intent of it
// Strategy-> one class can swap one of family classes
// Bridge → one of family can swap one of family (its like strategy but on steroids)