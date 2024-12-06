<?php
namespace Jiny\Users\Emoney;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;

use Illuminate\Routing\Router;

class JinyUserEmoneyServiceProvider extends ServiceProvider
{
    private $package = "jiny-users-emoney";
    public function boot()
    {
        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', $this->package);

        // 데이터베이스
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function register()
    {
        /* 라이브와이어 컴포넌트 등록 */
        $this->app->afterResolving(BladeCompiler::class, function () {
            Livewire::component('admin-user-emoney',
                \Jiny\Users\Emoney\Http\Livewire\AdminUserEmoney::class);

            Livewire::component('admin-user-emoney-withdraw',
                \Jiny\Users\Emoney\Http\Livewire\AdminUserEmoneyWithdraw::class);

            Livewire::component('admin-user-emoney-deposit',
                \Jiny\Users\Emoney\Http\Livewire\AdminUserEmoneyDeposit::class);

            Livewire::component('site-myuser-emoney',
                \Jiny\Users\Emoney\Http\Livewire\SiteMyUserEmoney::class);

            Livewire::component('site-myuser-emoney-withdraw',
                \Jiny\Users\Emoney\Http\Livewire\SiteMyUserEmoneyWithdraw::class);

            Livewire::component('site-myuser-emoney-deposit',
                \Jiny\Users\Emoney\Http\Livewire\SiteMyUserEmoneyDeposit::class);
        });
    }

}
