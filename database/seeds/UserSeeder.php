<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->name = 'admin';
        $user->account = '123';
        $user->email = 'admin@test.com';
        $user->password = Hash::make('secret');
        $user->save();
        
        $user = new User;
        $user->name = 'User 1';
        $user->account = '1234';
        $user->email = 'user1@test.com';
        $user->password = Hash::make('secret');
        $user->save();
        
        $user = new User;
        $user->name = 'User 2';
        $user->account = '12345';
        $user->email = 'user2@test.com';
        $user->password = Hash::make('secret');
        $user->save();
    }
}
