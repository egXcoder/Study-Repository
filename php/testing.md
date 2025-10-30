# Testing

Testing is the process of verifying that your PHP code works correctly — both now and after future changes. It helps you:

- Catch bugs early
- Prevent regressions
- Refactor code confidently
- Ensure predictable behavior

Tip: regressions: “Something that used to work has stopped working after a change.”



## Unit Testing

- Tests small one class or one function.
- Focus: logic correctness
- Very fast — hundreds per second

```php

use PHPUnit\Framework\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    public function testTotalCalculation()
    {
        $calc = new InvoiceCalculator();
        $calc->addItem(100);
        $calc->addItem(50, 2);

        $this->assertEquals(200, $calc->total());
    }

    public function testTotalWithTax()
    {
        $calc = new InvoiceCalculator();
        $calc->addItem(100);
        $this->assertEquals(110, $calc->totalWithTax(10));
    }

    public function testProcessOrderChargesGateway()
    {
        $gateway = $this->createMock(PaymentGateway::class);

        // Expect 'charge' to be called once with argument 100
        $gateway->expects($this->once())
                ->method('charge')
                ->with(100)
                ->willReturn(true);

        $orderService = new OrderService($gateway);
        $result = $orderService->processOrder(100);

        $this->assertTrue($result);
    }

    public function testDoesNotAllowNegativeDiscounts()
    {
        $service = new DiscountService();
        $this->expectException(\InvalidArgumentException::class);
        $service->apply(100, -5);
    }
}

```

## Integration Testing

Tests how different components work together It’s not about the user though — it’s about system components cooperating properly. — e.g., your service class + database. It’s less isolated and may involve real data connections (or test doubles).

```php

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanBeCreatedAndFound()
    {
        $repo = new UserRepository();

        $repo->create([
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => bcrypt('secret'),
        ]);

        $user = $repo->findByEmail('ahmed@example.com');

        $this->assertNotNull($user);
        $this->assertEquals('Ahmed', $user->name);
    }
}

// tests/Integration/InvoiceServiceTest.php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\InvoiceService;
use App\Models\Invoice;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateInvoiceStoresDataInDatabase()
    {
        $service = new InvoiceService();

        $invoice = $service->createInvoice(1, 1000);

        $this->assertDatabaseHas('invoices', [
            'customer_id' => 1,
            'amount' => 1000,
            'status' => 'pending'
        ]);
    }

    public function testMarkAsPaidUpdatesStatus()
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);

        $service = new InvoiceService();
        $service->markAsPaid($invoice->id);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid'
        ]);
    }
}

// tests/Integration/WeatherServiceTest.php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\WeatherService;

class WeatherServiceTest extends TestCase
{
    public function testItFetchesTemperatureFromApi()
    {
        Http::fake([
            'https://api.weather.com/*' => Http::response(['temperature' => 25], 200)
        ]);

        $service = new WeatherService();
        $temp = $service->getCurrentTemperature('Cairo');

        $this->assertEquals(25, $temp);
    }
}

```

## Feature / Functional Testing

Test the whole feature from the perspective of a user or api call..

```php

class LoginTest extends TestCase
{
    use RefreshDatabase; // refresh DB between tests

    public function testUserCanRegister()
    {
        $response = $this->post('/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'secret'
        ]);

        $response->assertRedirect('/home');
        $this->assertDatabaseHas('users', ['email' => 'ahmed@example.com']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }
}

class GetUsersAPIEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_list_of_users()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email']
                ]
            ]);
    }
}

public function test_user_gets_welcome_email_after_registration()
{
    Mail::fake();

    $response = $this->post('/register', [
        'name' => 'Ahmed',
        'email' => 'ahmed@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Mail::assertSent(WelcomeMail::class);
}


```

## End To End

End-to-End (E2E) / Browser Testing

Uses a real browser or simulated browser to test user flows.

Tools:

- Laravel Dusk
- Codeception
- Panther (Symfony)
- Cypress (JS tool that can test PHP backends)


## Mocking / Test Doubles

When you don’t want to hit a real external dependency (like API, DB, or email service), you “mock” it.

```php

$api = $this->createMock(PaymentGateway::class);
$api->method('charge')->willReturn(true);

$order = new OrderService($api);
$this->assertTrue($order->processPayment(100));

```


💡 Best Practices
- Name tests clearly: testUserCannotLoginWithWrongPassword()
- Keep tests independent (no shared state)
- Use factories or seeders to generate data
- Test both happy path and edge cases
- Run tests in CI (e.g., GitHub Actions, GitLab CI)
- Use code coverage tools to see what’s untested


## Q: if i am working on startup where requirements change rapidly. when i write the feature and its testings.. then next day its going to change, what is the common way developers handle such case?

Don’t try to test everything.
- Focus on parts that are unlikely to change, such as:
- Critical business rules (e.g., tax calculation, discount logic)
- Integrations that must always work (e.g., payment gateway, accounting sync)
- Core data flows (e.g., “creating an order should deduct stock”)


Write Unit Tests for Logic — Avoid UI or Flow Tests Early: UI flows (feature tests) are the first to break when things change.
If your product is still pivoting, minimize feature tests — focus on unit and integration tests for the reusable logic behind the scenes.


Use Mocks and Fakes for External Dependencies When working with things like APIs or email: 
- Use Http::fake(), Mail::fake(), etc. 
- This avoids breaking tests when the external behavior changes slightly.

Adopt a “Testing Pyramid”
        [Feature tests]    → Few (only core flows)
    [Integration tests]   → Some (key connections)
[Unit tests]             → Many (core logic)


When Things Move Fast — Use Smoke Tests
- When requirements are too unstable, you can replace full tests with quick smoke tests:
- Just check endpoints return 200
- Or check a response has basic expected fields
- Don’t verify every detail yet


Iterate Testing Strategy Over Time
- Early stage → prioritize speed and loose testing
- Mid stage → start testing core logic
- Mature product → add end-to-end and regression tests


## Q: what is regression?

| Term                   | Meaning                                                  |
| ---------------------- | -------------------------------------------------------- |
| **Regression bug**     | A feature that worked before now fails after new changes |
| **Regression testing** | Re-running all tests to detect such breakages            |
| **Purpose**            | Ensure new changes don’t break old functionality         |
| **Common method**      | Automated test suites (unit, integration, feature tests) |



## Q: some tests involves talking with database or external api. i am not sure what is the common way i would do this?

| Environment             | DB Engine for tests | Why                                |
| ----------------------- | ------------------- | ---------------------------------- |
| **Local development**   | SQLite (in-memory)  | Fast feedback for logic testing    |
| **Pre-deployment / CI** | MySQL (real)        | Validates type and schema behavior |

sqlite is fast for logic testing, however it may have false positive. if sqlite pass the test while mysql fails it. so to be reliable you would need to test on mysql test database. however its very slow to run the test because for every class it has to tear down database and build it and seed if necessary

so commonly, on local tests are run with sqlite. however on ci its tested with mysql to be more reliable before deployment