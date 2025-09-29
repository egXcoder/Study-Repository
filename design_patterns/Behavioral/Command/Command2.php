<?php

//Receiver (actual object doing the work)
class BankAccount {
    private string $owner;
    private float $balance;

    public function __construct(string $owner, float $balance) {
        $this->owner = $owner;
        $this->balance = $balance;
    }

    public function deposit(float $amount) {
        $this->balance += $amount;
        echo "{$this->owner} deposited {$amount}. Balance: {$this->balance}\n";
    }

    public function withdraw(float $amount) {
        $this->balance -= $amount;
        echo "{$this->owner} withdrew {$amount}. Balance: {$this->balance}\n";
    }
}

interface Command {
    public function execute();
    public function undo();
}

//command
class TransferMoneyCommand implements Command {
    private BankAccount $fromAccount;
    private BankAccount $toAccount;
    private float $amount;

    public function __construct(BankAccount $from, BankAccount $to, float $amount) {
        $this->fromAccount = $from;
        $this->toAccount = $to;
        $this->amount = $amount;
    }

    public function execute() {
        echo "Executing Transfer: {$this->amount}\n";
        $this->fromAccount->withdraw($this->amount);
        $this->toAccount->deposit($this->amount);
    }

    public function undo() {
        echo "Undoing Transfer: {$this->amount}\n";
        $this->toAccount->withdraw($this->amount);
        $this->fromAccount->deposit($this->amount);
    }
}

//invoker
class CommandInvoker {
    private array $history = [];

    public function executeCommand(Command $command) {
        $command->execute();
        $this->history[] = $command;
    }

    public function undoLast() {
        $command = array_pop($this->history);
        if ($command) {
            $command->undo();
        }
    }
}