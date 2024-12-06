<x-www-app>
    <x-www-layout>
        <x-www-main>

            <x-flex-between>
                <div class="page-title-box">
                    <x-flex class="align-items-center gap-2">
                        <h1 class="align-middle h3 d-inline">
                            적립금 내역
                        </h1>
                    </x-flex>
                    <p>
                        보유한 적립금을 확인합니다.
                    </p>
                </div>

                <div class="page-title-box">
                    <x-breadcrumb-item>
                        {{ $actions['route']['uri'] }}
                    </x-breadcrumb-item>

                </div>
            </x-flex-between>

            @livewire('site-myuser-emoney')


            
            <x-flex class="justify-content-center gap-2">
                <a class="btn btn-primary" href="/home/emoney/deposit">
                    적립금 충전
                </a>

                <a class="btn btn-info" href="/home/emoney/withdraw">
                    적립금 출금
                </a>


            </x-flex>

        </x-www-main>
    </x-www-layout>
</x-www-app>
