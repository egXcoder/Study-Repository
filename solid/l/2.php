<?php


interface ReportExporter {
    public function export(Collection $data, string $filename): void;
}


// CsvReportExporter → saves CSV.
// ExcelReportExporter → saves XLSX.
// ✅ Both can be substituted in GenerateReportJob without changing its logic.
// ❌ If ExcelReportExporter decides “I don’t support empty data, I’ll throw”, 
// but CsvReportExporter just writes an empty file, then callers must now handle special cases → LSP violation.


abstract class Notification {
    abstract public function toMail($notifiable): MailMessage;
}


// If you make a subclass:
// ✅ WelcomeNotification → always returns a valid MailMessage.
// ❌ SmsOnlyNotification → overrides toMail() and throws “Not Supported”.
// That breaks LSP, because Laravel expects any Notification to safely return a MailMessage when asked.