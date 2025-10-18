<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 테이블이 이미 존재하면 스킵
        if (Schema::hasTable('auth_banks')) {
            return;
        }

        Schema::create('auth_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('은행명');
            $table->string('code', 10)->unique()->nullable()->comment('은행 코드');
            $table->string('country', 2)->default('KR')->comment('국가 코드 (ISO 3166-1 alpha-2)');
            $table->string('swift_code', 11)->nullable()->comment('SWIFT 코드');
            $table->string('logo')->nullable()->comment('은행 로고 경로');
            $table->string('website')->nullable()->comment('은행 웹사이트');
            $table->string('phone')->nullable()->comment('고객센터 전화번호');
            $table->text('description')->nullable()->comment('은행 설명');
            $table->boolean('enable')->default(true)->comment('활성화 여부');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->timestamps();

            $table->index(['country', 'enable']);
            $table->index(['enable', 'sort_order']);
        });

        // 기본 은행 데이터 삽입
        $this->insertDefaultBanks();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_banks');
    }

    /**
     * 기본 은행 데이터 삽입
     */
    private function insertDefaultBanks(): void
    {
        $banks = [
            // 한국 은행들
            ['name' => 'KB국민은행', 'code' => 'KB', 'country' => 'KR', 'swift_code' => 'CZNBKRSE', 'website' => 'https://www.kbstar.com', 'phone' => '1588-9999', 'sort_order' => 1],
            ['name' => '신한은행', 'code' => 'SH', 'country' => 'KR', 'swift_code' => 'SHBKKRSE', 'website' => 'https://www.shinhan.com', 'phone' => '1599-8000', 'sort_order' => 2],
            ['name' => '우리은행', 'code' => 'WR', 'country' => 'KR', 'swift_code' => 'HVBKKRSE', 'website' => 'https://www.wooribank.com', 'phone' => '1599-5000', 'sort_order' => 3],
            ['name' => '하나은행', 'code' => 'HN', 'country' => 'KR', 'swift_code' => 'HNBNKRSE', 'website' => 'https://www.kebhana.com', 'phone' => '1599-1111', 'sort_order' => 4],
            ['name' => '기업은행', 'code' => 'IBK', 'country' => 'KR', 'swift_code' => 'IBKOKRSE', 'website' => 'https://www.ibk.co.kr', 'phone' => '1566-2566', 'sort_order' => 5],
            ['name' => 'NH농협은행', 'code' => 'NH', 'country' => 'KR', 'swift_code' => 'NACFKRSE', 'website' => 'https://banking.nonghyup.com', 'phone' => '1588-2100', 'sort_order' => 6],
            ['name' => '수협은행', 'code' => 'SUHYUP', 'country' => 'KR', 'swift_code' => 'SHFCKRSE', 'website' => 'https://www.suhyup-bank.com', 'phone' => '1588-1515', 'sort_order' => 7],
            ['name' => 'SC제일은행', 'code' => 'SC', 'country' => 'KR', 'swift_code' => 'SCBLKRSE', 'website' => 'https://www.scfirstbank.com', 'phone' => '1588-1599', 'sort_order' => 8],
            ['name' => '시티은행', 'code' => 'CITI', 'country' => 'KR', 'swift_code' => 'CITIKRSE', 'website' => 'https://www.citibank.co.kr', 'phone' => '1588-2588', 'sort_order' => 9],
            ['name' => '경남은행', 'code' => 'KNB', 'country' => 'KR', 'swift_code' => 'KYNBKRSE', 'website' => 'https://www.knbank.co.kr', 'phone' => '1588-0505', 'sort_order' => 10],
            ['name' => '광주은행', 'code' => 'GJB', 'country' => 'KR', 'swift_code' => 'GJBKKRSE', 'website' => 'https://www.kjbank.com', 'phone' => '1588-3388', 'sort_order' => 11],
            ['name' => '대구은행', 'code' => 'DGB', 'country' => 'KR', 'swift_code' => 'DAEBKRSE', 'website' => 'https://www.dgb.co.kr', 'phone' => '1566-3737', 'sort_order' => 12],
            ['name' => '부산은행', 'code' => 'BNK', 'country' => 'KR', 'swift_code' => 'PSBKKRSE', 'website' => 'https://www.busanbank.co.kr', 'phone' => '1588-6200', 'sort_order' => 13],
            ['name' => '전북은행', 'code' => 'JBB', 'country' => 'KR', 'swift_code' => 'JBVLKRSE', 'website' => 'https://www.jbbank.co.kr', 'phone' => '1588-7000', 'sort_order' => 14],
            ['name' => '제주은행', 'code' => 'JJB', 'country' => 'KR', 'swift_code' => 'JEJUKRSE', 'website' => 'https://www.e-jejubank.com', 'phone' => '1588-0079', 'sort_order' => 15],
            ['name' => '카카오뱅크', 'code' => 'KAKAO', 'country' => 'KR', 'swift_code' => null, 'website' => 'https://www.kakaobank.com', 'phone' => '1599-3333', 'sort_order' => 16],
            ['name' => '케이뱅크', 'code' => 'KBANK', 'country' => 'KR', 'swift_code' => null, 'website' => 'https://www.kbanknow.com', 'phone' => '1522-1000', 'sort_order' => 17],
            ['name' => '토스뱅크', 'code' => 'TOSS', 'country' => 'KR', 'swift_code' => null, 'website' => 'https://www.tossbank.com', 'phone' => '1661-7654', 'sort_order' => 18],

            // 미국 은행들
            ['name' => 'Bank of America', 'code' => 'BOA', 'country' => 'US', 'swift_code' => 'BOFAUS3N', 'website' => 'https://www.bankofamerica.com', 'phone' => '+1-800-432-1000', 'sort_order' => 101],
            ['name' => 'JPMorgan Chase', 'code' => 'CHASE', 'country' => 'US', 'swift_code' => 'CHASUS33', 'website' => 'https://www.chase.com', 'phone' => '+1-800-935-9935', 'sort_order' => 102],
            ['name' => 'Wells Fargo', 'code' => 'WF', 'country' => 'US', 'swift_code' => 'WFBIUS6S', 'website' => 'https://www.wellsfargo.com', 'phone' => '+1-800-869-3557', 'sort_order' => 103],
            ['name' => 'Citibank', 'code' => 'CITI', 'country' => 'US', 'swift_code' => 'CITIUS33', 'website' => 'https://www.citibank.com', 'phone' => '+1-800-374-9700', 'sort_order' => 104],

            // 일본 은행들
            ['name' => 'MUFG Bank', 'code' => 'MUFG', 'country' => 'JP', 'swift_code' => 'BOTKJPJT', 'website' => 'https://www.bk.mufg.jp', 'phone' => '+81-3-3240-1111', 'sort_order' => 201],
            ['name' => 'Mizuho Bank', 'code' => 'MIZUHO', 'country' => 'JP', 'swift_code' => 'MHBKJPJT', 'website' => 'https://www.mizuhobank.com', 'phone' => '+81-3-3596-1111', 'sort_order' => 202],
            ['name' => 'Sumitomo Mitsui Banking', 'code' => 'SMBC', 'country' => 'JP', 'swift_code' => 'SMBCJPJT', 'website' => 'https://www.smbc.co.jp', 'phone' => '+81-3-3282-8111', 'sort_order' => 203],

            // 중국 은행들
            ['name' => 'Bank of China', 'code' => 'BOC', 'country' => 'CN', 'swift_code' => 'BKCHCNBJ', 'website' => 'https://www.boc.cn', 'phone' => '+86-95566', 'sort_order' => 301],
            ['name' => 'Industrial and Commercial Bank of China', 'code' => 'ICBC', 'country' => 'CN', 'swift_code' => 'ICBKCNBJ', 'website' => 'https://www.icbc.com.cn', 'phone' => '+86-95588', 'sort_order' => 302],
            ['name' => 'China Construction Bank', 'code' => 'CCB', 'country' => 'CN', 'swift_code' => 'PCBCCNBJ', 'website' => 'https://www.ccb.com', 'phone' => '+86-95533', 'sort_order' => 303],

            // 기타 국가
            ['name' => 'HSBC', 'code' => 'HSBC', 'country' => 'GB', 'swift_code' => 'HBUKGB4B', 'website' => 'https://www.hsbc.com', 'phone' => '+44-345-740-4404', 'sort_order' => 401],
            ['name' => 'Standard Chartered', 'code' => 'SCB', 'country' => 'GB', 'swift_code' => 'SCBLGB2L', 'website' => 'https://www.sc.com', 'phone' => '+44-345-600-6161', 'sort_order' => 402],
        ];

        foreach ($banks as $bank) {
            $bank['created_at'] = now();
            $bank['updated_at'] = now();
        }

        DB::table('auth_banks')->insert($banks);
    }
};