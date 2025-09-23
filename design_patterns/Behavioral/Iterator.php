<?php


// Imagine you are working with an API that returns users in pages.
// Instead of fetching all pages at once and exposing the internal pagination logic, we can use the Iterator pattern to traverse all users transparently.

class User {
    public $id;
    public $name;
    
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}


class UserApiClient {
    private array $data;

    public function __construct() {
        // Simulate paginated API data
        $this->data = [
            1 => [new User(1, 'Alice'), new User(2, 'Bob')],
            2 => [new User(3, 'Charlie'), new User(4, 'Diana')],
            3 => [new User(5, 'Eve')]
        ];
    }

    public function getPage(int $page): array {
        return $this->data[$page] ?? [];
    }

    public function getTotalPages(): int {
        return count($this->data);
    }
}

class UserIterator implements Iterator {
    private UserApiClient $client;
    private int $page = 1;
    private int $position = 0; //holds which element you are pointing to in this page
    private array $currentPageData = [];

    public function __construct(UserApiClient $client) {
        $this->client = $client;
        $this->loadPage();
    }

    private function loadPage() {
        $this->currentPageData = $this->client->getPage($this->page);
        $this->position = 0;
    }

    public function current() {
        return $this->currentPageData[$this->position];
    }

    public function key() {
        return (($this->page - 1) * count($this->currentPageData)) + $this->position;
    }

    public function next() {
        $this->position++;
        if ($this->position >= count($this->currentPageData)) {
            $this->page++;
            if ($this->page <= $this->client->getTotalPages()) {
                $this->loadPage();
            }
        }
    }

    public function rewind() {
        $this->page = 1;
        $this->loadPage();
    }

    public function valid() {
        return $this->position < count($this->currentPageData);
    }
}

//client code
$apiClient = new UserApiClient();
$iterator = new UserIterator($apiClient);

foreach ($iterator as $user) {
    echo "User: {$user->id} - {$user->name}\n";
}