<?php
/**
 * Authorize - A Controller for managing the User Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Modules\Platform\Controllers;

use Nova\Database\ORM\ModelNotFoundException;
use Nova\Http\Request;
use Nova\Support\Facades\App;
use Nova\Support\Facades\Auth;
use Nova\Support\Facades\Config;
use Nova\Support\Facades\Hash;
use Nova\Support\Facades\Input;
use Nova\Support\Facades\Redirect;
use Nova\Support\Facades\Validator;
use Nova\Support\Str;

use Shared\Support\ReCaptcha;

use Modules\Platform\Notifications\AccountActivation as AccountActivationNotification;
use Modules\Roles\Models\Role;
use Modules\Users\Models\User;
use Modules\Users\Models\UserMeta;

use Modules\Platform\Controllers\BaseController;


class Registrar extends BaseController
{
    protected $layout = 'Default';


    protected function validator(array $data, $remoteIp)
    {
        // Validation rules.
        $rules = array(
            'email'                => 'required|email|unique:users',
            'password'             => 'required|confirmed|strong_password',
            'g-recaptcha-response' => 'required|min:1|recaptcha'
        );

        $messages = array(
            'recaptcha'       => __d('platform', 'The reCaptcha verification failed.'),
            'valid_name'      => __d('platform', 'The :attribute field is not a valid name.'),
            'strong_password' => __d('platform', 'The :attribute field is not strong enough.'),
        );

        $attributes = array(
            'username'             => __d('platform', 'Username'),
            'email'                => __d('platform', 'E-mail'),
            'password'             => __d('platform', 'Password'),
            'g-recaptcha-response' => __d('platform', 'ReCaptcha'),
        );

        // Create a Validator instance.
        $validator = Validator::make($data, $rules, $messages, $attributes);

        // Add the custom Validation Rule commands.
        $validator->addExtension('recaptcha', function($attribute, $value, $parameters) use ($remoteIp)
        {
            return ReCaptcha::check($value, $remoteIp);
        });

        $validator->addExtension('strong_password', function($attribute, $value, $parameters)
        {
            $pattern = "/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/";

            return (preg_match($pattern, $value) === 1);
        });

        return $validator;
    }

    /**
     * Display the register view.
     *
     * @return \Nova\View\View
     */
    public function create()
    {
        return $this->createView()
            ->shares('title', __d('platform', 'User Registration'));
    }

    /**
     * Handle a POST request to login the User.
     *
     * @return \Nova\Http\RedirectResponse
     *
     * @throws \RuntimeException
     */
    public function store(Request $request)
    {
        $input = $request->only(
            'username', 'email', 'password', 'password_confirmation', 'g-recaptcha-response'
        );

        // Create a Validator instance.
        $validator = $this->validator($input, $request->ip());

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        // Encrypt the given Password.
        $password = Hash::make($input['password']);

        // Create the User record.
        $user = User::create(array(
            'username' => $input['username'],
            'email'    => $input['email'],
            'password' => $password,
        ));

        // Retrieve the default 'user' Role.
        $role = Role::where('slug', 'user')->firstOrFail();

        // Update the user's associated Roles.
        $user->roles()->attach($role);

        // Create a new activation token.
        $token = $this->createNewToken();

        // Handle the meta-data.
        $user->saveMeta(array(
            'activated'       => 0,
            'activation_code' => $token,
        ));

        // Send the associated Activation Notification.
        $hashKey = Config::get('app.key');

        $hash = hash_hmac('sha256', $token, $hashKey);

        $user->notify(new AccountActivationNotification($hash, $token));

        return Redirect::to('register/status')
            ->with('success', __d('platform', 'Your Account has been created. Activation instructions have been sent to your email address.'));
    }

    /**
     * Display the email verification view.
     *
     * @return \Nova\View\View
     */
    public function verify()
    {
        return $this->createView()
            ->shares('title', __d('platform', 'Account Verification'));
    }

    /**
     * Process the verification token.
     *
     * @return \Nova\Http\RedirectResponse
     */
    public function verifyPost(Request $request)
    {
        // Create a Validator instance.
        $validator = Validator::make(
            $request->only('email', 'g-recaptcha-response'),
            array(
                'email'                => 'required|email|exists:users,email',
                'g-recaptcha-response' => 'required|min:1|recaptcha'
            ),
            array(
                'recaptcha' => __d('platform', 'The reCaptcha verification failed.'),
            ),
            array(
                'email'                => __d('platform', 'E-mail'),
                'g-recaptcha-response' => __d('platform', 'ReCaptcha'),
            )
        );

        // Add the custom Validation Rule commands.
        $validator->addExtension('recaptcha', function($attribute, $value, $parameters) use ($request)
        {
            return ReCaptcha::check($value, $request->ip());
        });

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $email = $request->input('email');

        try {
            $user = User::where('email', $email)->hasMeta('activated', 0)->firstOrFail();
        }
        catch (ModelNotFoundException $e) {
            return Redirect::back()
                ->onlyInput('email')
                ->with('danger', __d('platform', 'The selected email cannot receive Account Activation links.'));
        }

        $token = $this->createNewToken();

        $user->saveMeta('activation_code', $token);

        $user->save();

        // Send the associated Activation Notification.
        $hashKey = Config::get('app.key');

        $hash = hash_hmac('sha256', $token, $hashKey);

        $user->notify(new AccountActivationNotification($hash, $token));

        return Redirect::to('register/verify')
            ->with('success', __d('platform', 'Activation instructions have been sent to your email address.'));
    }

    /**
     * Process the verification token.
     *
     * @return \Nova\Http\RedirectResponse
     */
    public function tokenVerify(Request $request, $hash, $token)
    {
        $remoteIp = $request->ip();

        // Get the limiter constraints.
        $maxAttempts = Config::get('platform::throttle.maxAttempts', 5);
        $lockoutTime = Config::get('platform::throttle.lockoutTime', 1); // In minutes.

        // Compute the throttle key.
        $throttleKey = 'registrar.verify|' .$remoteIp;

        // Make a Rate Limiter instance, via Container.
        $limiter = App::make('Nova\Cache\RateLimiter');

       if ($limiter->tooManyAttempts($throttleKey, $maxAttempts, $lockoutTime)) {
            $seconds = $limiter->availableIn($throttleKey);

            return Redirect::to('register/status')
                ->with('danger', __d('platform', 'Too many verification attempts, please try again in {0} seconds.', $seconds));
        }

        $hashKey = Config::get('app.key');

        if (! hash_equals($hash, hash_hmac('sha256', $token, $hashKey))) {
            $limiter->hit($throttleKey, $lockoutTime);

            return Redirect::to('register/status')
                ->with('danger', __d('platform', 'Link is invalid, please request a new link.'));
        }

        try {
            $user = User::whereHas('meta', function ($query) use ($token)
            {
                return $query->where(function ($query)
                {
                    return $query->where('key', 'activated')->where('value', 0);

                })->where(function ($query) use ($token)
                {
                    return $query->where('key', 'activation_code')->where('value', $token);
                });

            })->firstOrFail();
        }
        catch (ModelNotFoundException $e) {
            $limiter->hit($throttleKey, $lockoutTime);

            return Redirect::to('password/remind')
                ->with('danger', __d('platform', 'Link is invalid, please request a new link.'));
        }

        $user->saveMeta(array(
            'activated'       => 1,
            'activation_code' => null,
        ));

        // Redirect to the login page.
        $guard = Config::get('auth.defaults.guard', 'web');

        $uri = Config::get("auth.guards.{$guard}.authorize", 'login');

        return Redirect::to($uri)
            ->with('success', __d('platform', 'Activated! You can now Sign in!'));
    }

    /**
     * Display the registration status.
     *
     * @return \Nova\View\View
     */
    public function status()
    {
        return $this->createView()
            ->shares('title', __d('platform', 'Registration Status'));
    }

    /**
     * Create a new unique Token for the User.
     *
     * @return string
     */
    public function createNewToken()
    {
        $tokens = UserMeta::where('key', 'activation_code')
            ->whereNotNull('value')
            ->lists('value');

        do {
            $token = Str::random(100);
        }
        while (in_array($token, $tokens));

        return $token;
    }
}
