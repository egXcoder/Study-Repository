<?php


//Facade: Provides a simplified interface to a complex subsystem
//SubSystem: The complex internal classes that the Facade delegates to

// Imagine an e-commerce system.
// To send an order confirmation email, you need to:
// Get the user from the database.
// Generate an invoice.
// Format an email.
// Use a mailer service to send.
// This involves multiple subsystems → a Facade can simplify it.


class UserRepository {
    public function getUserById(int $id): string {
        return "ahmed@example.com"; // pretend database call
    }
}

class InvoiceService {
    public function generateInvoice(int $orderId): string {
        return "Invoice for order #$orderId";
    }
}

class MailService {
    public function sendEmail(string $to, string $subject, string $body): void {
        echo "Sending email to $to\nSubject: $subject\nBody: $body\n";
    }
}


class OrderFacade {
    private UserRepository $userReporUserRepository;
    private InvoiceService $invoiceService;
    private MailService $mailService;

    public function __construct() {
        $this->userReporUserRepository = new UserRepository();
        $this->invoiceService = new InvoiceService();
        $this->mailService = new MailService();
    }

    public function sendOrderConfirmation(int $userId, int $orderId): void {
        $userEmail = $this->userService->getUserById($userId);
        $invoice = $this->invoiceService->generateInvoice($orderId);
        $this->mailService->sendEmail($userEmail, "Your Order Confirmation", $invoice);
    }
}