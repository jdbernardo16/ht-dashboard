<?php

namespace App\Http\Requests\Auth;

use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    use AdministrativeAlertsTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Get the current attempt count
            $attempts = RateLimiter::attempts($this->throttleKey()) + 1;
            
            // Determine if this is suspicious (multiple failed attempts)
            $isSuspicious = $attempts >= 5;
            
            // Get location data (simplified - in production you might use a geolocation service)
            $location = $this->getLocationData();
            
            // Trigger the failed login alert
            $this->triggerFailedLoginAlert(
                $this->input('email'),
                $this->ip(),
                $this->userAgent(),
                $attempts,
                $isSuspicious,
                $location
            );

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Get basic location data for the request
     * In production, you might want to use a proper geolocation service
     *
     * @return array|null
     */
    protected function getLocationData(): ?array
    {
        // This is a simplified version - in production you'd use a service like
        // MaxMind GeoIP, IP-API, or similar
        $ip = $this->ip();
        
        // Skip for localhost/internal IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return null;
        }
        
        // For demonstration, return basic info
        // In production, you'd make an API call to get real location data
        return [
            'ip' => $ip,
            'city' => 'Unknown',
            'country' => 'Unknown',
            'isp' => 'Unknown',
        ];
    }
}
