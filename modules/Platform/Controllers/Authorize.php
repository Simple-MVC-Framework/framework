<?php
/**
 * Authorize - A Controller for managing the User Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Modules\Platform\Controllers;

use Nova\Http\Request;
use Nova\Support\Facades\App;
use Nova\Support\Facades\Auth;
use Nova\Support\Facades\Config;
use Nova\Support\Facades\Hash;
use Nova\Support\Facades\Redirect;
use Nova\Support\Facades\Response;
use Nova\Support\Facades\Validator;

use Shared\Support\Facades\Password;
use Shared\Support\ReCaptcha;

use Modules\Platform\Controllers\BaseController;
use Modules\Platform\Models\LoginToken;
use Modules\Platform\Notifications\AuthenticationToken as TokenNotification;
use Modules\Users\Models\User;

use Carbon\Carbon;

use InvalidArgumentException;


class Authorize extends BaseController
{
    /**
     * The currently used Layout.
     *
     * @var mixed
     */
    protected $layout = 'Default';


    protected function validator(Request $request)
    {
        $rules = array(
            'email'                => 'required|valid_email',
            'g-recaptcha-response' => 'required|min:1|recaptcha'
        );

        $messages = array(
                'valid_email' => __d('platform', 'The :attribute field is not a valid email address.'),
                'recaptcha'   => __d('platform', 'The reCaptcha verification failed.'),
        );

        $attributes = array(
            'email'                => __d('platform', 'E-mail'),
            'g-recaptcha-response' => __d('platform', 'ReCaptcha'),
        );

        // Create a Validator instance.
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        // Add the custom Validation Rule commands.
        $validator->addExtension('valid_email', function($attribute, $value, $parameters)
        {
            return User::where('activated', 1)->where('email', $value)->exists();
        });

        $validator->addExtension('recaptcha', function($attribute, $value, $parameters) use ($request)
        {
            return ReCaptcha::check($value, $request->ip());
        });

        return $validator;
    }

    /**
     * Display the login view.
     *
     * @return \Nova\View\View
     */
    public function login()
    {
        return $this->createView()
            ->shares('title', __d('platform', 'User Login'));
    }

    /**
     * Handle a POST request to login the User.
     *
     * @return \Nova\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        // Verify the submitted reCAPTCHA
        if(! ReCaptcha::check($request->input('g-recaptcha-response'), $request->ip())) {
            return Redirect::back()->with('danger', __d('platform', 'The reCaptcha verification failed.'));
        }

        // Retrieve the Authentication credentials.
        $credentials = $request->only('username', 'password');

        // Make an attempt to login the Guest with the given credentials.
        if(! Auth::attempt($credentials, $request->has('remember'))) {
            return Redirect::back()->with('danger', __d('platform', 'Wrong username or password.'));
        }

        // The User is authenticated now; retrieve his Model instance.
        $user = Auth::user();

        if ($user->activated == 0) {
            Auth::logout();

            // User not activated; logout and redirect him to account activation page.
            return Redirect::to('register/verify')
                ->withInput(array('email' => $user->email))
                ->with('danger', __d('platform', 'Please activate your Account!'));
        }

        // If the User's password needs rehash.
        else if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($credentials['password']);

            $user->save();
        }

        // Redirect to the User's Dashboard.
        return Redirect::intended('dashboard')
            ->with('success', __d('platform', '<b>{0}</b>, you have successfully logged in.', $user->username));
    }

    /**
     * Handle a GET request to logout the current User.
     *
     * @return \Nova\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();

        // Redirect to the login page.
        $guard = Config::get('auth.defaults.guard', 'web');

        $uri = Config::get("auth.guards.{$guard}.authorize", 'login');

        return Redirect::to($uri)->with('success', __d('platform', 'You have successfully logged out.'));
    }

    /**
     * Display the token request view.
     *
     * @return \Nova\View\View
     */
    public function tokenRequest()
    {
        return $this->createView()
            ->shares('title', __d('platform', 'One-Time Login'))
            ->shares('guard', 'web');
    }

    /**
     * Handle a POST request to token request.
     *
     * @return \Nova\Http\RedirectResponse
     */
    public function tokenProcess(Request $request)
    {
        $remoteIp = $request->ip();

        // Get the limiter constraints.
        $maxAttempts = Config::get('platform::throttle.maxAttempts', 5);
        $lockoutTime = Config::get('platform::throttle.lockoutTime', 1); // In minutes.

        // Compute the throttle key.
        $throttleKey = 'users.tokenProcess|' .$remoteIp;

        // Make a Rate Limiter instance, via Container.
        $limiter = App::make('Nova\Cache\RateLimiter');

        if ($limiter->tooManyAttempts($throttleKey, $maxAttempts, $lockoutTime)) {
            $seconds = $limiter->availableIn($throttleKey);

            return Redirect::to('authorize')
                ->with('danger', __d('platform', 'Too many attempts, please try again in {0} seconds.', $seconds));
        }

        // Create a Validator instance.
        $validator = $this->validator($request);

        if ($validator->fails()) {
            $limiter->hit($throttleKey, $lockoutTime);

            return Redirect::back()->withErrors($validator->errors());
        }

        $limiter->clear($throttleKey);

        $loginToken = LoginToken::create(array(
            'email' => $email = $request->input('email'),

            // We will use an unique token.
            'token' => $token = LoginToken::uniqueToken(),
        ));

        $hashKey = Config::get('app.key');

        $timestamp = dechex(time());

        $hash = hash_hmac('sha256', $token .'|' .$remoteIp .'|' .$timestamp, $hashKey);

        //
        $user = User::where('email', $email)->first();

        $user->notify(new TokenNotification($hash, $timestamp, $token));

        return Redirect::back()
            ->with('success', __d('platform', 'Login instructions have been sent to your email address.'));
    }

    /**
     * Handle a login on token request.
     *
     * @return \Nova\Http\RedirectResponse
     */
    public function tokenLogin(Request $request, $hash, $timestamp, $token)
    {
        $remoteIp = $request->ip();

        // Get the limiter constraints.
        $maxAttempts = Config::get('platform::throttle.maxAttempts', 5);
        $lockoutTime = Config::get('platform::throttle.lockoutTime', 1); // In minutes.

        // Compute the throttle key.
        $throttleKey = 'users.tokenLogin|' .$remoteIp;

        // Make a Rate Limiter instance, via Container.
        $limiter = App::make('Nova\Cache\RateLimiter');

        if ($limiter->tooManyAttempts($throttleKey, $maxAttempts, $lockoutTime)) {
            $seconds = $limiter->availableIn($throttleKey);

            return Redirect::to('authorize')
                ->with('danger', __d('platform', 'Too many login attempts, please try again in {0} seconds.', $seconds));
        }

        $validity = Config::get('platform::tokens.login.validity', 15); // In minutes.

        $oldest = Carbon::parse('-' .$validity .' minutes');

        //
        $hashKey = Config::get('app.key');

        $data = $token .'|' .$remoteIp .'|' .$timestamp;

        try {
            if ($oldest->timestamp > hexdec($timestamp))) {
                throw new InvalidArgumentException('The timestamp is too old');
            } else if (! hash_equals($hash, hash_hmac('sha256', $data, $hashKey)) {
                throw new InvalidArgumentException('Invalid authorization hash');
            }

            $loginToken = LoginToken::with('user')->whereHas('user', function ($query)
            {
                $query->where('activated', 1);

            })->where('token', $token)->where('created_at', '>', $oldest)->firstOrFail();
        }

        // Catch the exceptions.
        catch (InvalidArgumentException | ModelNotFoundException $e) {
            $limiter->hit($throttleKey, $lockoutTime);

            return Redirect::to('authorize')
                ->with('danger', __d('platform', 'Link is invalid, please request a new link.'));
        }

        $limiter->clear($throttleKey);

        // Delete all stored login Tokens for this User.
        LoginToken::where('email', $loginToken->email)->delete();

        // Authenticate the User instance from login Token.
        Auth::login($user = $loginToken->user, false /* do not remember this login */);

        return Redirect::to('dashboard')
            ->with('success', __d('platform', '<b>{0}</b>, you have successfully logged in.', $user->username));
    }
}
