namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AutoLoginTestCase
{
    public function handle($request, Closure $next)
    {
        // Hanya jalankan kalau di local/testing
        if (app()->environment(['local', 'testing'])) {

            $email = env('TEST_CASE');
            $password = env('PASSWORD_TEST');

            if (!Auth::check() && $email && $password) {
                $user = User::where('email', $email)->first();

                if ($user && Hash::check($password, $user->password)) {
                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }
}
