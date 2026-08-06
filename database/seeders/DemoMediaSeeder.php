<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Company;
use App\Models\CompanyPlanAssignment;
use App\Models\CompanyUser;
use App\Models\EmploymentType;
use App\Models\FacilityType;
use App\Models\JobCategory;
use App\Models\JobFeature;
use App\Models\JobPosting;
use App\Models\PostingPlan;
use App\Models\Prefecture;
use App\Models\Qualification;
use App\Models\Workplace;
use App\Services\PostingPlanLimitService;
use Illuminate\Database\Seeder;

/**
 * デモメディア(T-19)専用の架空データ投入。
 *
 * ⚠ **`DatabaseSeeder` からは絶対に呼ばない。** 全環境が実行する `php artisan db:seed`
 *   に混ざると、顧客環境に架空の企業・求人が入ってしまう(CLAUDE.md 3.1)。
 *   デモメディア環境でだけ、個別に実行する。
 *
 *     php artisan db:seed --class=DemoMediaSeeder
 *
 * 会社名・求人内容はすべて架空。実在の介護事業者を示すものではない。
 * 掲載企業ログインのパスワードは全社共通で `demo-pass-2026`(社内の営業デモ専用)。
 */
class DemoMediaSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'demo-pass-2026';

    /** @var array<string, int> */
    private array $facilityTypes;

    /** @var array<string, int> */
    private array $jobCategories;

    /** @var array<string, int> */
    private array $employmentTypes;

    /** @var array<string, int> */
    private array $qualifications;

    /** @var array<string, int> */
    private array $jobFeatures;

    /** @var array<string, int> */
    private array $prefectures;

    public function run(): void
    {
        $this->facilityTypes = FacilityType::query()->pluck('id', 'code')->all();
        $this->jobCategories = JobCategory::query()->pluck('id', 'code')->all();
        $this->employmentTypes = EmploymentType::query()->pluck('id', 'code')->all();
        $this->qualifications = Qualification::query()->pluck('id', 'code')->all();
        $this->jobFeatures = JobFeature::query()->pluck('id', 'code')->all();
        $this->prefectures = Prefecture::query()->pluck('id', 'name')->all();

        $plans = $this->createPlans();

        foreach ($this->companyBlueprints() as $blueprint) {
            $this->createCompany($blueprint, $plans);
        }
    }

    /**
     * @return array<string, PostingPlan>
     */
    private function createPlans(): array
    {
        $definitions = [
            'basic' => ['name' => 'ベーシック', 'max_job_postings' => 5, 'max_workplaces' => 2, 'posting_duration_days' => 60, 'is_featured' => false, 'monthly_price' => 50000],
            'standard' => ['name' => 'スタンダード', 'max_job_postings' => 15, 'max_workplaces' => 5, 'posting_duration_days' => 90, 'is_featured' => false, 'monthly_price' => 80000],
            'premium' => ['name' => 'プレミアム', 'max_job_postings' => null, 'max_workplaces' => null, 'posting_duration_days' => null, 'is_featured' => true, 'monthly_price' => 150000],
        ];

        $plans = [];
        foreach ($definitions as $key => $attributes) {
            $plans[$key] = PostingPlan::query()->firstOrCreate(
                ['name' => $attributes['name']],
                $attributes + ['sort_order' => array_search($key, array_keys($definitions), true)]
            );
        }

        return $plans;
    }

    /**
     * @param  array<string, PostingPlan>  $plans
     */
    private function createCompany(array $blueprint, array $plans): void
    {
        $prefectureId = $this->prefectures[$blueprint['prefecture']] ?? null;

        $company = Company::query()->firstOrCreate(
            ['name' => $blueprint['name']],
            [
                'name_kana' => $blueprint['name_kana'],
                'prefecture_id' => $prefectureId,
                'tel' => $blueprint['tel'],
                'description' => $blueprint['description'],
                'status' => Company::STATUS_ACTIVE,
            ]
        );

        CompanyUser::query()->firstOrCreate(
            ['email' => $blueprint['login_email']],
            [
                'company_id' => $company->id,
                'name' => $blueprint['login_name'],
                'password' => self::DEMO_PASSWORD,
                'role' => CompanyUser::ROLE_OWNER,
                'is_active' => true,
            ]
        );

        if (! $company->planAssignments()->exists()) {
            CompanyPlanAssignment::create([
                'company_id' => $company->id,
                'posting_plan_id' => $plans[$blueprint['plan']]->id,
                'starts_at' => now()->subMonths(3),
                'ends_at' => null,
            ]);
        }

        foreach ($blueprint['workplaces'] as $workplaceBlueprint) {
            $this->createWorkplace($company, $prefectureId, $workplaceBlueprint);
        }
    }

    private function createWorkplace(Company $company, ?int $prefectureId, array $blueprint): void
    {
        $cityId = $prefectureId
            ? City::query()->where('prefecture_id', $prefectureId)->inRandomOrder()->value('id')
            : null;

        $workplace = Workplace::query()->firstOrCreate(
            ['company_id' => $company->id, 'name' => $blueprint['name']],
            [
                'facility_type_id' => $this->facilityTypes[$blueprint['facility']] ?? null,
                'prefecture_id' => $prefectureId,
                'city_id' => $cityId,
                'address' => $blueprint['address'],
                'access' => $blueprint['access'],
                'description' => $blueprint['description'],
            ]
        );

        if ($workplace->jobPostings()->exists()) {
            return;
        }

        $limits = app(PostingPlanLimitService::class);
        $publishAttributes = $limits->publishAttributes($company);

        foreach ($blueprint['postings'] as $postingKey) {
            $this->createJobPosting($company, $workplace, $this->postingTemplates()[$postingKey], $publishAttributes);
        }
    }

    private function createJobPosting(Company $company, Workplace $workplace, array $template, array $publishAttributes): void
    {
        $jobPosting = JobPosting::create([
            'company_id' => $company->id,
            'workplace_id' => $workplace->id,
            'title' => str_replace('{workplace}', $workplace->name, $template['title']),
            'job_category_id' => $this->jobCategories[$template['job_category']] ?? null,
            'employment_type_id' => $this->employmentTypes[$template['employment_type']] ?? null,
            'salary_type' => $template['salary_type'],
            'salary_min' => $template['salary_min'],
            'salary_max' => $template['salary_max'],
            'working_hours' => $template['working_hours'],
            'holidays' => $template['holidays'],
            'benefits' => $template['benefits'],
            'description' => $template['description'],
            'has_night_shift' => $template['has_night_shift'],
            'allow_external_feed' => true,
            'status' => $publishAttributes['status'],
            'published_at' => $publishAttributes['published_at']->clone()->subDays(random_int(0, 20)),
            'expires_at' => $publishAttributes['expires_at'],
            'is_featured' => $publishAttributes['is_featured'],
        ]);

        $qualificationIds = array_values(array_filter(array_map(
            fn (string $code) => $this->qualifications[$code] ?? null,
            $template['qualifications']
        )));
        $featureIds = array_values(array_filter(array_map(
            fn (string $code) => $this->jobFeatures[$code] ?? null,
            $template['features']
        )));

        $jobPosting->qualifications()->sync($qualificationIds);
        $jobPosting->features()->sync($featureIds);
    }

    /**
     * 求人テンプレート。`{workplace}` はタイトル生成時に事業所名へ置換する。
     *
     * @return array<string, array<string, mixed>>
     */
    private function postingTemplates(): array
    {
        return [
            'kaigo_seishain_yakin' => [
                'title' => '介護職員(正社員・夜勤あり)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 230000, 'salary_max' => 290000,
                'working_hours' => "シフト制(日勤 8:30〜17:30 / 早番 7:00〜16:00 / 遅番 10:30〜19:30 / 夜勤 16:30〜翌9:30)\n夜勤は月4〜5回程度",
                'holidays' => "シフト制月9日休み\n年間休日108日、有給休暇あり",
                'benefits' => "社会保険完備、夜勤手当、資格取得支援制度、退職金制度(勤続3年以上)",
                'description' => "利用者様の日常生活の介助(食事・入浴・排泄・移動など)全般を担当します。未経験の方には先輩職員がマンツーマンで指導しますので、安心して始められます。",
                'has_night_shift' => true,
                'qualifications' => ['shoninsha_kenshu'],
                'features' => ['kenshu_jujitsu', 'shikaku_shien', 'shakai_hoken', 'shoyo_ari'],
            ],
            'kaigo_part_yakin' => [
                'title' => '介護職員(パート・夜勤専従)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1300, 'salary_max' => 1600,
                'working_hours' => '夜勤専従 16:30〜翌9:30(月4〜8回、応相談)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、夜勤手当、車通勤可',
                'description' => '夜間の見守り・介助を中心に担当していただきます。日中は別のお仕事をされている方や、まとめて稼ぎたい方に人気の勤務形態です。',
                'has_night_shift' => true,
                'qualifications' => ['shoninsha_kenshu', Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['yakin_senju', 'mikeiken_ok', 'kotsuhi_shikyu', 'kuruma_tsukin'],
            ],
            'kangoshi_seishain' => [
                'title' => '看護師(正社員)',
                'job_category' => 'nurse', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 280000, 'salary_max' => 350000,
                'working_hours' => '日勤 8:30〜17:30(オンコール対応あり、手当別途支給)',
                'holidays' => '週休2日制、年間休日110日',
                'benefits' => '社会保険完備、資格手当、オンコール手当、退職金制度',
                'description' => '利用者様の健康管理・服薬管理・医療的処置を担当します。介護職員と連携しながら、日々のバイタルチェックや急変時対応を行います。',
                'has_night_shift' => false,
                'qualifications' => ['kangoshi', 'jun_kangoshi'],
                'features' => ['shakai_hoken', 'shoyo_ari', 'nikkin_nomi'],
            ],
            'seikatsu_soudanin' => [
                'title' => '生活相談員(正社員)',
                'job_category' => 'seikatsu_soudanin', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 240000, 'salary_max' => 300000,
                'working_hours' => '日勤 8:30〜17:30',
                'holidays' => '週休2日制、年間休日108日',
                'benefits' => '社会保険完備、資格手当、退職金制度',
                'description' => '利用者様・ご家族様との面談、ケアマネジャーとの連絡調整、入退所の手続きなど、施設と地域をつなぐ窓口を担当します。',
                'has_night_shift' => false,
                'qualifications' => ['shakai_fukushishi', 'shakai_fukushi_shuji'],
                'features' => ['nikkin_nomi', 'doniti_yasumi', 'shakai_hoken'],
            ],
            'care_manager' => [
                'title' => 'ケアマネジャー(正社員)',
                'job_category' => 'care_manager', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 260000, 'salary_max' => 330000,
                'working_hours' => '日勤 8:30〜17:30',
                'holidays' => '週休2日制、年間休日110日',
                'benefits' => '社会保険完備、資格手当、車通勤可、退職金制度',
                'description' => 'ケアプランの作成・見直し、サービス事業者との調整、モニタリングを担当します。既存の利用者様を中心に、無理のない件数で対応いただきます。',
                'has_night_shift' => false,
                'qualifications' => ['care_manager'],
                'features' => ['nikkin_nomi', 'doniti_yasumi', 'kuruma_tsukin', 'shakai_hoken'],
            ],
            'kaigo_day_seishain' => [
                'title' => '{workplace}の介護職員(正社員・未経験歓迎)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 220000, 'salary_max' => 270000,
                'working_hours' => '8:30〜17:30(日勤のみ・夜勤なし)',
                'holidays' => '週休2日制、年間休日110日、年末年始休暇あり',
                'benefits' => '社会保険完備、資格取得支援制度、マイカー通勤可',
                'description' => 'デイサービスでのご利用者様の送迎・入浴・レクリエーションの企画運営などを担当します。夜勤がなく、生活リズムを整えやすい職場です。',
                'has_night_shift' => false,
                'qualifications' => ['shoninsha_kenshu', Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['yakin_nashi', 'mikeiken_ok', 'shikaku_shien', 'doniti_yasumi'],
            ],
            'kaigo_day_part' => [
                'title' => '{workplace}の介護職員(パート・週2日〜)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1100, 'salary_max' => 1300,
                'working_hours' => '9:00〜16:00の間で応相談(週2日〜)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、扶養内勤務可、マイカー通勤可',
                'description' => '家庭やご都合に合わせて働ける時間帯を相談できます。ブランクのある方、育児中の方も歓迎です。',
                'has_night_shift' => false,
                'qualifications' => [Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['mikeiken_ok', 'blank_ok', 'shu1_ok', 'fuyonai_ok', 'yakin_nashi'],
            ],
            'soutai_driver' => [
                'title' => '送迎ドライバー(パート)',
                'job_category' => 'soutai_driver', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1050, 'salary_max' => 1200,
                'working_hours' => '7:30〜9:30 / 16:00〜18:00(午前・午後のみの勤務も可)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、無事故手当',
                'description' => 'ご利用者様の自宅から施設までの送迎(普通自動車)を担当します。介護業務はありません。',
                'has_night_shift' => false,
                'qualifications' => ['futsu_menkyo'],
                'features' => ['mikeiken_ok', 'shu1_ok', 'kuruma_tsukin', 'fuyonai_ok'],
            ],
            'kinou_kunren_part' => [
                'title' => '機能訓練指導員(パート)',
                'job_category' => 'kinou_kunren', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1400, 'salary_max' => 1800,
                'working_hours' => '9:00〜16:00(週2〜3日、応相談)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、資格手当',
                'description' => 'ご利用者様一人ひとりに合わせた機能訓練メニューの作成・実施を担当します。',
                'has_night_shift' => false,
                'qualifications' => ['pt', 'ot', 'st', 'judo_seifukushi', 'kangoshi'],
                'features' => ['shu1_ok', 'nikkin_nomi', 'fuyonai_ok'],
            ],
            'homon_helper' => [
                'title' => '訪問介護ヘルパー(登録・パート)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1200, 'salary_max' => 1600,
                'working_hours' => '1件30分〜、1日1件からの勤務も可(スキマ時間に登録)',
                'holidays' => 'シフト制',
                'benefits' => '移動時間・交通費支給、直行直帰OK',
                'description' => '利用者様のご自宅を訪問し、食事・入浴・排泄などの介助や生活援助を行います。空いた時間だけの登録も歓迎です。',
                'has_night_shift' => false,
                'qualifications' => ['shoninsha_kenshu', 'jitsumusha_kenshu'],
                'features' => ['shu1_ok', 'fukugyo_ok', 'fuyonai_ok', 'kuruma_tsukin'],
            ],
            'service_teikyo' => [
                'title' => 'サービス提供責任者(正社員)',
                'job_category' => 'service_teikyo', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 250000, 'salary_max' => 310000,
                'working_hours' => '8:30〜17:30',
                'holidays' => '週休2日制、年間休日108日',
                'benefits' => '社会保険完備、資格手当、車通勤可',
                'description' => '訪問介護計画の作成、ヘルパーのシフト調整・同行指導、ご利用者様・ご家族様との連絡調整を担当します。',
                'has_night_shift' => false,
                'qualifications' => ['jitsumusha_kenshu', 'kaigo_fukushishi'],
                'features' => ['nikkin_nomi', 'doniti_yasumi', 'kuruma_tsukin', 'shakai_hoken'],
            ],
            'homon_kango_seishain' => [
                'title' => '訪問看護師(正社員)',
                'job_category' => 'nurse', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 300000, 'salary_max' => 380000,
                'working_hours' => '8:30〜17:30(オンコールあり、当番制)',
                'holidays' => '週休2日制、年間休日110日',
                'benefits' => '社会保険完備、オンコール手当、車通勤可、退職金制度',
                'description' => '在宅の利用者様を訪問し、健康管理・医療処置・ご家族への助言を行います。1日4〜6件を目安に訪問します。',
                'has_night_shift' => false,
                'qualifications' => ['kangoshi', 'hokenshi'],
                'features' => ['nikkin_nomi', 'shakai_hoken', 'shoyo_ari', 'kuruma_tsukin'],
            ],
            'homon_rehab' => [
                'title' => '訪問リハビリスタッフ(理学療法士・作業療法士)',
                'job_category' => 'kinou_kunren', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 270000, 'salary_max' => 340000,
                'working_hours' => '8:30〜17:30(訪問件数1日5〜6件を目安)',
                'holidays' => '週休2日制、年間休日110日',
                'benefits' => '社会保険完備、車通勤可、学会参加支援',
                'description' => '在宅の利用者様を訪問し、身体機能の維持・向上を目的としたリハビリテーションを提供します。',
                'has_night_shift' => false,
                'qualifications' => ['pt', 'ot', 'st'],
                'features' => ['nikkin_nomi', 'kuruma_tsukin', 'shakai_hoken'],
            ],
            'group_home_kaigo' => [
                'title' => '{workplace}の介護職員(正社員)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 220000, 'salary_max' => 270000,
                'working_hours' => 'シフト制(早番・日勤・遅番・夜勤)',
                'holidays' => 'シフト制月9日休み',
                'benefits' => '社会保険完備、夜勤手当、資格取得支援制度',
                'description' => '9名程度の少人数のご利用者様と、家庭的な雰囲気の中で生活を共にしながら介護を行います。',
                'has_night_shift' => true,
                'qualifications' => ['shoninsha_kenshu'],
                'features' => ['kenshu_jujitsu', 'shikaku_shien', 'shakai_hoken'],
            ],
            'group_home_kanrishoku' => [
                'title' => '{workplace}の管理者候補(正社員)',
                'job_category' => 'kanrishoku', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 280000, 'salary_max' => 350000,
                'working_hours' => '8:30〜17:30(施設運営に応じて調整)',
                'holidays' => '週休2日制',
                'benefits' => '社会保険完備、管理職手当、退職金制度',
                'description' => '施設運営全般、職員のシフト管理、行政・地域とのやりとりを担当します。将来の施設長候補としての採用です。',
                'has_night_shift' => false,
                'qualifications' => ['kaigo_fukushishi', 'care_manager'],
                'features' => ['doniti_yasumi', 'shakai_hoken', 'shoyo_ari'],
            ],
            'chouri' => [
                'title' => '調理補助(パート)',
                'job_category' => 'chouri', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1050, 'salary_max' => 1200,
                'working_hours' => '7:00〜14:00 または 11:00〜18:00(応相談)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、まかない補助',
                'description' => '施設内の厨房で、盛り付け・配膳・洗浄などの調理補助業務を担当します。調理師資格は不要です。',
                'has_night_shift' => false,
                'qualifications' => [Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['mikeiken_ok', 'fuyonai_ok', 'shu1_ok'],
            ],
            'jimu' => [
                'title' => '事務・受付(パート)',
                'job_category' => 'jimu', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1050, 'salary_max' => 1250,
                'working_hours' => '9:00〜16:00(週3日〜)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、扶養内勤務可',
                'description' => '電話・来客対応、請求関連の事務作業、備品管理などを担当します。介護業界での事務未経験の方も歓迎です。',
                'has_night_shift' => false,
                'qualifications' => [Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['mikeiken_ok', 'fuyonai_ok', 'shu1_ok', 'nikkin_nomi'],
            ],
            'shogaisha_shien' => [
                'title' => '{workplace}の生活支援員(正社員)',
                'job_category' => 'kaigo_helper', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 220000, 'salary_max' => 270000,
                'working_hours' => '8:30〜17:30(施設により早番・遅番あり)',
                'holidays' => '週休2日制、年間休日108日',
                'benefits' => '社会保険完備、資格取得支援制度',
                'description' => '障害のある利用者様の日常生活支援(食事・入浴・移動介助など)や、社会参加に向けたサポートを行います。',
                'has_night_shift' => false,
                'qualifications' => ['shoninsha_kenshu', Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['mikeiken_ok', 'kenshu_jujitsu', 'shakai_hoken'],
            ],
            'houkago_day_jido' => [
                'title' => '{workplace}の児童指導員(正社員)',
                'job_category' => 'jido_shidouin', 'employment_type' => 'seishain',
                'salary_type' => 'monthly', 'salary_min' => 220000, 'salary_max' => 260000,
                'working_hours' => '10:00〜19:00(平日)、8:00〜17:00(土曜・長期休暇中)',
                'holidays' => '週休2日制(日曜・祝日)',
                'benefits' => '社会保険完備、資格取得支援制度、車通勤可',
                'description' => '放課後や長期休暇中の障害のあるお子さまをお預かりし、療育プログラムの実施や送迎を担当します。',
                'has_night_shift' => false,
                'qualifications' => ['hoikushi', 'youchien_kyouyu', 'shakai_fukushishi'],
                'features' => ['nikkin_nomi', 'kuruma_tsukin', 'shakai_hoken'],
            ],
            'houkago_day_part' => [
                'title' => '{workplace}の児童指導員(パート)',
                'job_category' => 'jido_shidouin', 'employment_type' => 'part',
                'salary_type' => 'hourly', 'salary_min' => 1100, 'salary_max' => 1350,
                'working_hours' => '13:00〜19:00(平日、週3日〜)',
                'holidays' => 'シフト制',
                'benefits' => '交通費支給、扶養内勤務可',
                'description' => '放課後の時間帯を中心に、お子さまの見守りや療育プログラムのサポートを行います。',
                'has_night_shift' => false,
                'qualifications' => ['hoikushi', Qualification::CODE_NO_QUALIFICATION_REQUIRED],
                'features' => ['mikeiken_ok', 'fuyonai_ok', 'shu1_ok'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function companyBlueprints(): array
    {
        return [
            [
                'name' => 'さくら介護グループ株式会社',
                'name_kana' => 'さくらかいごぐるーぷ',
                'prefecture' => '東京都',
                'tel' => '03-1234-5601',
                'description' => '東京都内で特別養護老人ホーム・デイサービスを複数運営する介護事業者です。',
                'plan' => 'premium',
                'login_email' => 'demo-sakura@jobcone.example.com',
                'login_name' => '採用担当(さくら介護グループ)',
                'workplaces' => [
                    [
                        'name' => 'さくら介護グループ 目黒特別養護老人ホーム',
                        'facility' => 'tokuyo',
                        'address' => '目黒区内(架空の所在地)',
                        'access' => '東急目黒線 各駅からバス10分',
                        'description' => '入所定員80名の特別養護老人ホーム。',
                        'postings' => ['kaigo_seishain_yakin', 'kaigo_part_yakin', 'kangoshi_seishain', 'seikatsu_soudanin', 'care_manager'],
                    ],
                    [
                        'name' => 'さくら介護グループ 渋谷デイサービスセンター',
                        'facility' => 'day_service',
                        'address' => '渋谷区内(架空の所在地)',
                        'access' => 'JR渋谷駅からバス8分',
                        'description' => '定員35名のデイサービス。送迎あり。',
                        'postings' => ['kaigo_day_seishain', 'kaigo_day_part', 'soutai_driver', 'kinou_kunren_part'],
                    ],
                ],
            ],
            [
                'name' => 'ひまわりケアサービス株式会社',
                'name_kana' => 'ひまわりけあさーびす',
                'prefecture' => '神奈川県',
                'tel' => '045-234-5602',
                'description' => '神奈川県内で訪問介護・訪問看護を展開する介護事業者です。',
                'plan' => 'standard',
                'login_email' => 'demo-himawari@jobcone.example.com',
                'login_name' => '採用担当(ひまわりケアサービス)',
                'workplaces' => [
                    [
                        'name' => 'ひまわりケアサービス 横浜訪問介護ステーション',
                        'facility' => 'homon_kaigo',
                        'address' => '横浜市内(架空の所在地)',
                        'access' => 'JR横浜駅から徒歩12分',
                        'description' => '登録ヘルパー中心に運営する訪問介護事業所。',
                        'postings' => ['homon_helper', 'service_teikyo'],
                    ],
                    [
                        'name' => 'ひまわりケアサービス 川崎訪問看護ステーション',
                        'facility' => 'homon_kango',
                        'address' => '川崎市内(架空の所在地)',
                        'access' => 'JR川崎駅から徒歩10分',
                        'description' => '在宅療養を支える訪問看護ステーション。',
                        'postings' => ['homon_kango_seishain', 'homon_rehab', 'kinou_kunren_part'],
                    ],
                ],
            ],
            [
                'name' => 'みどりの丘有料老人ホーム株式会社',
                'name_kana' => 'みどりのおかゆうりょうろうじんほーむ',
                'prefecture' => '大阪府',
                'tel' => '06-345-5603',
                'description' => '大阪府内で有料老人ホームを運営する介護事業者です。',
                'plan' => 'standard',
                'login_email' => 'demo-midori@jobcone.example.com',
                'login_name' => '採用担当(みどりの丘)',
                'workplaces' => [
                    [
                        'name' => 'みどりの丘有料老人ホーム 堺',
                        'facility' => 'yuryo_rojin_home',
                        'address' => '堺市内(架空の所在地)',
                        'access' => '南海高野線 各駅からバス5分',
                        'description' => '入居定員60名の介護付き有料老人ホーム。',
                        'postings' => ['kaigo_seishain_yakin', 'kaigo_part_yakin', 'kangoshi_seishain', 'chouri', 'jimu'],
                    ],
                ],
            ],
            [
                'name' => 'なごみグループホーム株式会社',
                'name_kana' => 'なごみぐるーぷほーむ',
                'prefecture' => '愛知県',
                'tel' => '052-456-5604',
                'description' => '愛知県内で認知症対応型グループホームを複数運営しています。',
                'plan' => 'basic',
                'login_email' => 'demo-nagomi@jobcone.example.com',
                'login_name' => '採用担当(なごみグループホーム)',
                'workplaces' => [
                    [
                        'name' => 'なごみグループホーム 名古屋東',
                        'facility' => 'group_home',
                        'address' => '名古屋市内(架空の所在地)',
                        'access' => '地下鉄東山線 各駅から徒歩7分',
                        'description' => '9名定員2ユニットのグループホーム。',
                        'postings' => ['group_home_kaigo', 'group_home_kanrishoku'],
                    ],
                    [
                        'name' => 'なごみ小規模多機能ホーム 名古屋西',
                        'facility' => 'shokibo_takinou',
                        'address' => '名古屋市内(架空の所在地)',
                        'access' => '地下鉄桜通線 各駅から徒歩9分',
                        'description' => '「通い」「泊まり」「訪問」を組み合わせた小規模多機能型居宅介護。',
                        'postings' => ['kaigo_day_part', 'homon_helper'],
                    ],
                ],
            ],
            [
                'name' => 'はまなす訪問看護ステーション株式会社',
                'name_kana' => 'はまなすほうもんかんごすてーしょん',
                'prefecture' => '北海道',
                'tel' => '011-567-5605',
                'description' => '北海道内で訪問看護・訪問リハビリを提供しています。',
                'plan' => 'standard',
                'login_email' => 'demo-hamanasu@jobcone.example.com',
                'login_name' => '採用担当(はまなす訪問看護)',
                'workplaces' => [
                    [
                        'name' => 'はまなす訪問看護ステーション 札幌',
                        'facility' => 'homon_kango',
                        'address' => '札幌市内(架空の所在地)',
                        'access' => '地下鉄南北線 各駅から徒歩8分',
                        'description' => '24時間対応の訪問看護ステーション。',
                        'postings' => ['homon_kango_seishain', 'homon_rehab', 'kinou_kunren_part', 'jimu'],
                    ],
                ],
            ],
            [
                'name' => 'そよかぜ福祉会',
                'name_kana' => 'そよかぜふくしかい',
                'prefecture' => '福岡県',
                'tel' => '092-678-5606',
                'description' => '福岡県内で障害福祉サービス・放課後等デイサービスを運営する社会福祉法人です。',
                'plan' => 'basic',
                'login_email' => 'demo-soyokaze@jobcone.example.com',
                'login_name' => '採用担当(そよかぜ福祉会)',
                'workplaces' => [
                    [
                        'name' => 'そよかぜ障害者支援センター 福岡',
                        'facility' => 'shogaisha_shien',
                        'address' => '福岡市内(架空の所在地)',
                        'access' => '地下鉄空港線 各駅からバス6分',
                        'description' => '利用定員40名の障害者支援施設。',
                        'postings' => ['shogaisha_shien', 'jimu'],
                    ],
                    [
                        'name' => 'そよかぜ放課後等デイサービス 博多',
                        'facility' => 'houkago_day',
                        'address' => '福岡市内(架空の所在地)',
                        'access' => 'JR博多駅からバス10分',
                        'description' => '定員10名の放課後等デイサービス。',
                        'postings' => ['houkago_day_jido', 'houkago_day_part'],
                    ],
                ],
            ],
        ];
    }
}
