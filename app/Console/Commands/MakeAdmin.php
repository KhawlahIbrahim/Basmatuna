<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class MakeAdmin extends Command
{
    protected $signature = 'basmah:admin {email} {--name=مسؤول الفعالية}';

    protected $description = 'إنشاء حساب مسؤول للوحة التحكم أو تحديث كلمة مروره';

    public function handle(): int
    {
        $email = mb_strtolower($this->argument('email'));
        $password = $this->secret('كلمة المرور (12 حرفًا على الأقل)');

        $v = Validator::make(compact('email', 'password'), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:12'],
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->exists ? $user->name : $this->option('name');
        $user->password = $password;
        $user->is_admin = true;
        $user->save();

        $this->info('تم حفظ حساب المسؤول: '.$email);

        return self::SUCCESS;
    }
}
