<?php

// 👉 Bad:
class GenerateReportJob implements ShouldQueue
{
    public function handle()
    {
        // Generate report
        $data = Report::all();

        // Save as CSV
        Storage::disk('local')->put('report.csv', $this->toCsv($data));

        // Email it
        Mail::to('admin@example.com')->send(new ReportReadyMail('report.csv'));
    }

    private function toCsv($data) { /* ... */ }
}

//Trial .. Better
//issues:
//1- csv formatting is inside the job, i think it can be encapsulated in another object
//2- class name need to be more expressive such as GenerateSaveThenNotifyReport
//3- report.csv and admin@example.com is hardcoded into the job, we should allow the class to be able to change that if needed
class GenerateReportJob implements ShouldQueue
{
    public function handle(ReportRepository $reportRepository,ReportSaver $reportSaver,ReportMailingService $reportMailingService)
    {
        // Generate report
        $data = $reportRepository->all();

        //report saver
        $reportSaver->save('report.csv',$this->toCsv($data));

        // Email it
        $reportMailingService->notifyReportIsReady('admin@example.com','report.csv');
    }

    private function toCsv($data) { /* ... */ }
}


//final .. Respect SRP
class GenerateSaveThenNotifyReport implements ShouldQueue
{
    public function __construct(
        private string $filename = 'report.csv',
        private string $recipient = 'admin@example.com'
    ) {}

    public function handle(
        ReportRepository $reportRepository,
        CsvExporter $csvExporter,
        ReportSaver $reportSaver,
        ReportMailingService $reportMailingService
    ) {
        // Fetch report data
        $data = $reportRepository->all();

        // Convert to CSV
        $csv = $csvExporter->export($data);

        // Save the file
        $reportSaver->save($this->filename, $csv);

        // Notify recipient
        $reportMailingService->notifyReportIsReady($this->recipient, $this->filename);
    }
}


//even if i don't use report repository its fine
//I think that’s a very fair trade-off 👍. You don’t always need a repository layer in Laravel — especially if:
// You’re tied to Eloquent and don’t plan on swapping the persistence layer.
// Your queries are straightforward (Report::all(), some where, etc.).
// You don’t want an extra abstraction that just forwards to Eloquent anyway.
class GenerateSaveThenNotifyReport implements ShouldQueue
{
    public function __construct(
        private string $filename = 'report.csv',
        private string $recipient = 'admin@example.com'
    ) {}

    public function handle(
        CsvExporter $csvExporter,
        ReportSaver $reportSaver,
        ReportMailingService $reportMailingService
    ) {
        // Possible refinement without going full repository, even Report::where('status', 'approved') work as well
        $data = Report::forExport()->get();

        // Convert to CSV
        $csv = $csvExporter->export($data);

        // Save the file
        $reportSaver->save($this->filename, $csv);

        // Notify recipient
        $reportMailingService->notifyReportIsReady($this->recipient, $this->filename);
    }
}

// Possible refinement without going full repository
// If you want a middle ground, you could extract queries into query scopes or custom query builder classes instead of a repository. For example:
class Report extends Model
{
    public function scopeForExport($query)
    {
        return $query->where('status', 'approved');
    }
}