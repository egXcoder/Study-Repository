<?php

// laravel example
//
// - abstract State class which will implement all the available actions and all actions return with error by default
// - define a class for each state and override the actions that can be done
// - OrderStateManager will take the order and try to perform action on it 

abstract class AbstractSalesOrderState
{
    protected string $stateName;
    protected array $availableActions = []; //can be used for ui buttons
    protected bool $canEdit = false;
    protected string $statusColor = 'gray';

    public function confirm(SalesOrder $order): array{
        return $this->createErrorResponse('confirm', $order);
    }

    public function process(SalesOrder $order): array{
        return $this->createErrorResponse('process', $order);
    }

    public function ship(SalesOrder $order): array{
        return $this->createErrorResponse('ship', $order);
    }

    public function deliver(SalesOrder $order): array{
        return $this->createErrorResponse('deliver', $order);
    }

    public function cancel(SalesOrder $order): array{
        return $this->createErrorResponse('cancel', $order);
    }

    public function getStateName(): string{
        return $this->stateName;
    }

    public function canExecuteAction($action): bool{
        return isset($this->availableActions[$action]);
    }

    public function canEdit(): bool{
        return $this->canEdit;
    }

    public function getStatusColor(): string{
        return $this->statusColor;
    }

    protected function createErrorResponse(string $action, SalesOrder $order): array
    {
        return [
            'ok' => false,
            'message' => "Cannot {$action} order #{$order->order_number} in {$this->stateName} status."
        ];
    }

    protected function createSuccessResponse(string $action, SalesOrder $order, string $newStatus): array
    {
        $order->status = $newStatus;
        $order->save();

        return [
            'ok' => true,
            'message' => "Order #{$order->order_number} has been {$action}d successfully."
        ];
    }
}


class DraftState extends AbstractSalesOrderState
{
    protected string $stateName = 'Draft';
    protected array $availableActions = ['confirm', 'cancel'];
    protected bool $canEdit = true;
    protected string $statusColor = 'gray';

    public function confirm(SalesOrder $order): array{
        return $this->createSuccessResponse('confirm', $order, 'confirmed');
    }

    public function cancel(SalesOrder $order): array{
        return $this->createSuccessResponse('cancel', $order, 'cancelled');
    }
}

// app/States/ConfirmedState.php
class ConfirmedState extends AbstractSalesOrderState
{
    protected string $stateName = 'Confirmed';
    protected array $availableActions = ['process', 'cancel'];
    protected bool $canEdit = false;
    protected string $statusColor = 'blue';

    public function process(SalesOrder $order): array{
        return $this->createSuccessResponse('process', $order, 'processing');
    }

    public function cancel(SalesOrder $order): array{
        return $this->createSuccessResponse('cancel', $order, 'cancelled');
    }
}

// app/States/ProcessingState.php
class ProcessingState extends AbstractSalesOrderState
{
    protected string $stateName = 'Processing';
    protected array $availableActions = ['ship'];
    protected bool $canEdit = false;
    protected string $statusColor = 'yellow';

    public function ship(SalesOrder $order): array{
        return $this->createSuccessResponse('ship', $order, 'shipped');
    }
}

class ShippedState extends AbstractSalesOrderState
{
    protected string $stateName = 'Shipped';
    protected array $availableActions = ['deliver'];
    protected bool $canEdit = false;
    protected string $statusColor = 'purple';

    public function deliver(SalesOrder $order): array{
        return $this->createSuccessResponse('deliver', $order, 'delivered');
    }
}

// app/States/DeliveredState.php
class DeliveredState extends AbstractSalesOrderState
{
    protected string $stateName = 'Delivered';
    protected array $availableActions = [];
    protected bool $canEdit = false;
    protected string $statusColor = 'green';
}

// app/States/CancelledState.php
class CancelledState extends AbstractSalesOrderState
{
    protected string $stateName = 'Cancelled';
    protected array $availableActions = [];
    protected bool $canEdit = false;
    protected string $statusColor = 'red';
}

class SalesOrderStateFactory
{
    private static array $states = [
        'draft' => DraftState::class,
        'confirmed' => ConfirmedState::class,
        'processing' => ProcessingState::class,
        'shipped' => ShippedState::class,
        'delivered' => DeliveredState::class,
        'cancelled' => CancelledState::class,
    ];

    public static function create(string $status): AbstractSalesOrderState
    {
        $stateClass = self::$states[$status] ?? DraftState::class;
        return new $stateClass();
    }
}

//this class is used by controller to change order from a state to another
// by calling for example $salesOrderStateManager->confirm($order) .. delegate the work to OrderState class and see what will happen
class SalesOrderStateManager
{
    public function __construct(
        private SalesOrderStateFactory $stateFactory,
    ) {}

    public function confirm(SalesOrder $order): array
    {
        return $this->executeAction($order, 'confirm');
    }

    public function process(SalesOrder $order): array
    {
        return $this->executeAction($order, 'process');
    }

    public function ship(SalesOrder $order): array
    {
        return $this->executeAction($order, 'ship');
    }

    public function deliver(SalesOrder $order): array
    {
        return $this->executeAction($order, 'deliver');
    }

    public function cancel(SalesOrder $order): array
    {
        return $this->executeAction($order, 'cancel');
    }

    public function getStateName(SalesOrder $order): string
    {
        return $this->getState($order)->getStateName();
    }


    public function canEdit(SalesOrder $order): bool
    {
        return $this->getState($order)->canEdit();
    }

    public function getStatusColor(SalesOrder $order): string
    {
        return $this->getState($order)->getStatusColor();
    }

    private function executeAction(SalesOrder $order, string $action): array
    {
        return $this->getState($order)->$action($order);
    }

    public function getState(SalesOrder $order): AbstractSalesOrderState
    {
        return $this->stateFactory->create($order->status);
    }
}