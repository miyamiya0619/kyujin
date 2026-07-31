<?php

namespace Tests\Feature\Seeker;

use App\Models\JobSeeker;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_求職者が会員登録できる(): void
    {
        $this->post(route('seeker.register.store'), [
            'name' => '山田花子',
            'email' => 'hanako@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('seeker.mypage'));

        $jobSeeker = JobSeeker::where('email', 'hanako@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($jobSeeker, 'seeker');
    }

    public function test_メールアドレスが重複していると登録できない(): void
    {
        JobSeeker::factory()->create(['email' => 'dup@example.com']);

        $this->post(route('seeker.register.store'), [
            'name' => '山田花子',
            'email' => 'dup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_パスワード確認が一致しないと登録できない(): void
    {
        $this->post(route('seeker.register.store'), [
            'name' => '山田花子',
            'email' => 'hanako@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('job_seekers', ['email' => 'hanako@example.com']);
    }

    public function test_会員機能が_of_fのとき会員登録画面は404になる(): void
    {
        SiteSetting::current()->update(['enables_member' => false]);

        $this->get(route('seeker.register'))->assertNotFound();
        $this->post(route('seeker.register.store'), [
            'name' => '山田花子',
            'email' => 'hanako@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();
    }

    public function test_会員機能が_of_fでもログイン画面自体は表示される(): void
    {
        SiteSetting::current()->update(['enables_member' => false]);

        $this->get(route('seeker.login'))->assertOk();
    }

    public function test_会員機能が_o_nのときログイン画面に登録導線が出る(): void
    {
        SiteSetting::current()->update(['enables_member' => true]);

        $this->get(route('seeker.login'))->assertSee('会員登録がまだの方はこちら');
    }

    public function test_会員機能が_of_fのときログイン画面に登録導線が出ない(): void
    {
        SiteSetting::current()->update(['enables_member' => false]);

        $this->get(route('seeker.login'))->assertDontSee('会員登録がまだの方はこちら');
    }
}
