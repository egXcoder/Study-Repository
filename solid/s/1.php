<?php

// 👉 Bad Example (violating SRP):
// This controller has too many responsibilities: validation, persistence, email sending, logging.
class UserController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
        ]);

        // 2. Create user
        $user = User::create($validated);

        // 3. Send welcome email
        Mail::to($user->email)->send(new WelcomeMail($user));

        // 4. Log activity
        Log::info('New user created: ' . $user->id);

        return response()->json($user);
    }
}



// 👉 Better Example but still have issues
// Validation → handled by UserRequest (FormRequest)
// Business logic → handled by UserService
// Emails/logging → also inside mailing service
// Now the controller is clean and only coordinates requests and responses.
// Issues:
// now controller is instantiating the classeses manually, what if we wanted to create another userservice or mailingservice
// through your application, you will have to go to each controller and manually change this hardcoded instantiating
// better approach will be using laravel ioc so that laravel will auto instantiate the class for you using container and service providers
class UserController extends Controller
{
    private UserService $userService = new UserService();
    private MailingService $mailingService = new MailingService();

    public function store(UserRequest $request)
    {
        $validatedRequest = $request->validated();

        $user = $this->userService->createUser($validatedRequest);

        $this->mailingService->sendWelcomeEmailToUser($user);

        return response()->json($user);
    }
}

//👉 Better Example and respect RSP
//used laravel ioc and inject the services so laravel will auto instantiate them for you
//not sure if UserService and MailingService will be used globally in UserController so we can move them into class constructor
//but i think for now we can keep it like that into the method
// Balanced Rule of Thumb
// Method injection → When the dependency is used only in one method.
// Constructor injection → When the dependency is used across multiple methods. 
//
// Another Suggestion: can improve design
// thinking about below design, i think we can encapsulate createUser and sendingWelcomeEmail into one class UserRegistrationService
// since well its always when you create a user you need to send a welcome email so it will be good to have a unit which do both
// so that we can reuse it in the application going forward
class UserController extends Controller
{
    public function store(UserRequest $request,UserService $userService,MailingService $mailingService)
    {
        $validatedRequest = $request->validated();

        $user = $userService->createUser($validatedRequest);

        $mailingService->sendWelcomeEmailToUser($user);

        return response()->json($user);
    }
}


//👉 Final One
class UserController extends Controller
{
    public function store(UserRequest $request, UserRegistrationService $registrationService)
    {
        $user = $registrationService->register($request->validated());

        return response()->json($user);
    }
}

class UserRegistrationService
{
    private UserService $userService;
    private MailingService $mailingService;
    
    public function __construct(UserService $userService, MailingService $mailingService) {
        $this->userService = $userService;
        $this->mailingService = $mailingService;
    }

    public function register(array $data): User
    {
        $user = $this->userService->createUser($data);

        $this->mailingService->sendWelcomeEmailToUser($user);

        return $user;
    }
}
