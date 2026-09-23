<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('partials.pagination');

        // شبكة الجامعة قد تجمع عشرات المنسوبين خلف عنوان IP واحد،
        // لذلك نحدّ لكل جلسة بدقة، ونترك حدًا واسعًا لكل IP.
        RateLimiter::for('participate', function (Request $request) {
            return [
                Limit::perMinutes(10, 3)->by('s:'.$request->session()->getId())
                    ->response(function (Request $request) {
                        $message = 'أرسلت عدة مشاركات خلال وقت قصير. حاول بعد دقائق.';

                        if ($request->wantsJson()) {
                            return response()->json(['message' => $message], 429);
                        }

                        return back()->withInput()->withErrors(['form' => $message]);
                    }),
                Limit::perMinute(40)->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip())
                ->response(fn () => back()->withInput($request->only('email'))->withErrors(['email' => 'محاولات كثيرة. حاول بعد دقيقة.']));
        });

        RateLimiter::for('feed', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
    }
}
